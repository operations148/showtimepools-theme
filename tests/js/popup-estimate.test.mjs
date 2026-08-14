/**
 * Delayed "Get a Free Estimate" popup — behaviour spec.
 *
 * Runs the REAL page in headless Chrome, with window.setTimeout replaced by a
 * controllable fake clock installed before any page script executes. The 15s
 * delay is therefore proven by advancing virtual time, not by sleeping, so the
 * suite stays fast and deterministic.
 *
 * Chrome comes from the local puppeteer cache; no browser is downloaded.
 *
 * Run:  node --test tests/js/
 *       CHROME_PATH=... SHOWTIME_BASE_URL=... node --test tests/js/
 */

import test from 'node:test';
import assert from 'node:assert/strict';
import fs from 'node:fs';
import path from 'node:path';
import os from 'node:os';
import puppeteer from 'puppeteer-core';

const BASE = process.env.SHOWTIME_BASE_URL || 'http://localhost/showtimepools/wp';
const GHL_URL = 'https://app.showtimepoolmechanics.com/widget/booking/KkBpnBMhT5QXn8YtTsDb';
const TEL_URL = 'tel:+13238252099';
const DELAY_MS = 15000;

/** Newest Chrome in the puppeteer cache, unless CHROME_PATH overrides it. */
function chromePath() {
	if (process.env.CHROME_PATH) return process.env.CHROME_PATH;
	const root = path.join(os.homedir(), '.cache', 'puppeteer', 'chrome');
	if (!fs.existsSync(root)) throw new Error(`no Chrome cache at ${root}; set CHROME_PATH`);
	const builds = fs.readdirSync(root).sort();
	for (const b of builds.reverse()) {
		for (const rel of ['chrome-win64/chrome.exe', 'chrome-linux64/chrome', 'chrome-mac-x64/Google Chrome for Testing.app/Contents/MacOS/Google Chrome for Testing']) {
			const p = path.join(root, b, rel);
			if (fs.existsSync(p)) return p;
		}
	}
	throw new Error('no Chrome binary found in the puppeteer cache; set CHROME_PATH');
}

/**
 * Fake timers, installed before page scripts run. Captures every setTimeout
 * the page schedules so the spec can advance a virtual clock by hand.
 * requestAnimationFrame is flushed synchronously so open/close transitions
 * settle within a tick.
 */
const FAKE_CLOCK = `
(() => {
  const timers = new Map();
  let nextId = 1;
  let now = 0;
  const realSetTimeout = window.setTimeout.bind(window);
  window.setTimeout = (fn, delay = 0, ...args) => {
    const id = nextId++;
    timers.set(id, { fn, at: now + Number(delay || 0), args });
    return id;
  };
  window.clearTimeout = (id) => { timers.delete(id); };
  window.requestAnimationFrame = (fn) => { fn(now); return 0; };
  window.__clock = {
    now: () => now,
    pending: () => timers.size,
    tick(ms) {
      const target = now + Number(ms);
      let guard = 0;
      for (;;) {
        let due = null;
        for (const [id, t] of timers) {
          if (t.at <= target && (due === null || t.at < timers.get(due).at)) due = id;
        }
        if (due === null || guard++ > 5000) break;
        const t = timers.get(due);
        timers.delete(due);
        now = t.at;
        try { t.fn(...t.args); } catch (e) { /* surface via assertions, not here */ }
      }
      now = target;
    },
  };
  window.__realSetTimeout = realSetTimeout;
})();
`;

let browser;

test.before(async () => {
	browser = await puppeteer.launch({
		executablePath: chromePath(),
		headless: true,
		args: ['--no-sandbox', '--disable-dev-shm-usage'],
	});
});

test.after(async () => {
	if (browser) await browser.close();
});

/**
 * Opens a fresh page (fresh session storage) at `url` with fake timers armed.
 * Returns the page plus any console errors it emitted.
 */
async function freshPage(url = BASE + '/') {
	const context = await browser.createBrowserContext();
	const page = await context.newPage();
	const consoleErrors = [];
	page.on('console', (m) => { if (m.type() === 'error') consoleErrors.push(m.text()); });
	page.on('pageerror', (e) => consoleErrors.push(String(e)));
	await page.evaluateOnNewDocument(FAKE_CLOCK);
	await page.goto(url, { waitUntil: 'domcontentloaded' });
	// The script is deferred: wait until it has bound and armed its timer.
	await page.waitForFunction('!!document.getElementById("stp-estimate-popup")');
	await page.waitForFunction('!!document.getElementById("stp-estimate-popup").stpEstimate');
	return { context, page, consoleErrors };
}

const isVisible = (page) => page.evaluate(() => {
	const el = document.getElementById('stp-estimate-popup');
	return !!el && !el.hidden && el.classList.contains('is-open');
});

test('is absent before 15 seconds, and appears exactly at 15 seconds', async () => {
	const { context, page } = await freshPage();

	assert.equal(await isVisible(page), false, 'popup must not be visible at load');

	await page.evaluate('window.__clock.tick(14999)');
	assert.equal(await isVisible(page), false, 'popup must still be hidden at 14999ms');

	await page.evaluate('window.__clock.tick(1)');
	assert.equal(await isVisible(page), true, 'popup must be visible at exactly 15000ms');

	const delay = await page.evaluate(() => document.getElementById('stp-estimate-popup').stpEstimate.delay);
	assert.equal(delay, DELAY_MS, 'configured delay must be exactly 15000ms');

	await context.close();
});

test('the GHL booking URL and telephone URL are exact, and open in the same tab', async () => {
	const { context, page } = await freshPage();
	await page.evaluate('window.__clock.tick(15000)');

	const cta = await page.evaluate(() => {
		const a = document.querySelector('.stp-estimate__cta');
		return { href: a.getAttribute('href'), target: a.getAttribute('target'), text: a.textContent.trim() };
	});
	assert.equal(cta.href, GHL_URL);
	assert.equal(cta.target, null, 'primary CTA must stay in the same tab (no target attribute)');
	assert.equal(cta.text, 'Book your Service Appointment');

	const call = await page.evaluate(() => {
		const a = document.querySelector('.stp-estimate__call');
		return { href: a.getAttribute('href'), text: a.textContent.replace(/\s+/g, ' ').trim() };
	});
	assert.equal(call.href, TEL_URL);
	assert.equal(call.text, 'Call (323) 825–2099');

	await context.close();
});

test('the GHL calendar is not requested before the CTA is clicked', async () => {
	const context = await browser.createBrowserContext();
	const page = await context.newPage();
	const ghlHits = [];
	await page.setRequestInterception(true);
	page.on('request', (r) => {
		if (r.url().includes('showtimepoolmechanics.com')) ghlHits.push(r.url());
		r.continue();
	});
	await page.evaluateOnNewDocument(FAKE_CLOCK);
	await page.goto(BASE + '/', { waitUntil: 'networkidle2' });
	await page.waitForFunction('!!document.getElementById("stp-estimate-popup")');
	await page.evaluate('window.__clock.tick(15000)');

	assert.equal(await isVisible(page), true, 'popup should be open');
	assert.deepEqual(ghlHits, [], 'no GHL request may be made before the visitor clicks the CTA');
	assert.equal(
		await page.evaluate(() => !!document.querySelector('.stp-estimate iframe')),
		false,
		'no iframe may be embedded in the modal'
	);

	await context.close();
});

test('closing it records the session dismissal and it does not reopen in the same session', async () => {
	const { context, page } = await freshPage();
	await page.evaluate('window.__clock.tick(15000)');
	assert.equal(await isVisible(page), true);

	await page.click('.stp-estimate__close');
	await page.evaluate('window.__clock.tick(500)'); // let the close transition fallback run
	assert.equal(await isVisible(page), false, 'popup must be closed');

	const stored = await page.evaluate(() => window.sessionStorage.getItem('stp_estimate_popup_dismissed'));
	assert.equal(stored, '1', 'dismissal must be recorded in sessionStorage');

	// It must not come back later in the same page view...
	await page.evaluate('window.__clock.tick(60000)');
	assert.equal(await isVisible(page), false, 'popup must not reopen after dismissal');

	// ...nor on the next page in the same session. sessionStorage is scoped to
	// the tab, so the session is continued by navigating THIS page, which is
	// exactly what a visitor clicking through the site does.
	await page.goto(BASE + '/about/', { waitUntil: 'networkidle2' });
	await page.waitForFunction('!!document.getElementById("stp-estimate-popup")');
	assert.equal(
		await page.evaluate(() => window.sessionStorage.getItem('stp_estimate_popup_dismissed')),
		'1',
		'the dismissal must survive navigation within the session'
	);
	// Already dismissed, so the script returns before arming anything: no timer
	// is scheduled at all, which is stronger than "the timer fired harmlessly".
	assert.equal(
		await page.evaluate(() => !document.getElementById('stp-estimate-popup').stpEstimate),
		true,
		'a dismissed session must not arm the timer again'
	);
	await page.evaluate('window.__clock.tick(20000)');
	assert.equal(await isVisible(page), false, 'popup must stay dismissed across pages in the same session');

	await context.close();
});

test('a fresh session shows it again', async () => {
	const a = await freshPage();
	await a.page.evaluate('window.__clock.tick(15000)');
	await a.page.click('.stp-estimate__close');
	await a.context.close();

	// A new browser context is a new session: sessionStorage starts empty.
	const b = await freshPage();
	await b.page.evaluate('window.__clock.tick(15000)');
	assert.equal(await isVisible(b.page), true, 'a new session must see the popup again');
	await b.context.close();
});

test('Escape closes it and records the dismissal', async () => {
	const { context, page } = await freshPage();
	await page.evaluate('window.__clock.tick(15000)');
	await page.keyboard.press('Escape');
	await page.evaluate('window.__clock.tick(500)');

	assert.equal(await isVisible(page), false, 'Escape must close the popup');
	assert.equal(
		await page.evaluate(() => window.sessionStorage.getItem('stp_estimate_popup_dismissed')),
		'1'
	);
	await context.close();
});

test('a backdrop click closes it', async () => {
	const { context, page } = await freshPage();
	await page.evaluate('window.__clock.tick(15000)');
	await page.evaluate(() => document.querySelector('.stp-estimate__backdrop').click());
	await page.evaluate('window.__clock.tick(500)');
	assert.equal(await isVisible(page), false, 'a backdrop click must close the popup');
	await context.close();
});

test('following a CTA records the dismissal', async () => {
	const { context, page } = await freshPage();
	await page.evaluate('window.__clock.tick(15000)');
	// Dispatch the click without navigating away, so the handler is observed.
	await page.evaluate(() => {
		const a = document.querySelector('.stp-estimate__cta');
		a.addEventListener('click', (e) => e.preventDefault(), { once: true });
		a.click();
	});
	assert.equal(
		await page.evaluate(() => window.sessionStorage.getItem('stp_estimate_popup_dismissed')),
		'1',
		'following the estimate CTA must record the dismissal'
	);
	await context.close();
});

test('it is semantic, labelled and keyboard-accessible', async () => {
	const { context, page, consoleErrors } = await freshPage();
	await page.evaluate('window.__clock.tick(15000)');

	const a11y = await page.evaluate(() => {
		const dlg = document.querySelector('.stp-estimate__dialog');
		const labelledby = dlg.getAttribute('aria-labelledby');
		const describedby = dlg.getAttribute('aria-describedby');
		return {
			role: dlg.getAttribute('role'),
			modal: dlg.getAttribute('aria-modal'),
			labelledby,
			labelText: (document.getElementById(labelledby) || {}).textContent,
			describedbyResolves: !!document.getElementById(describedby),
			closeLabel: document.querySelector('.stp-estimate__close').getAttribute('aria-label'),
			focusInside: dlg.contains(document.activeElement),
			bodyLocked: getComputedStyle(document.body).overflow === 'hidden',
		};
	});

	assert.equal(a11y.role, 'dialog');
	assert.equal(a11y.modal, 'true');
	assert.equal(a11y.labelText.trim(), 'Get a Free Estimate', 'heading association must resolve to the title');
	assert.equal(a11y.describedbyResolves, true, 'aria-describedby must resolve to a real element');
	assert.ok(a11y.closeLabel && a11y.closeLabel.length > 5, 'close button needs a descriptive label');
	assert.equal(a11y.focusInside, true, 'focus must move into the dialog on open');
	assert.equal(a11y.bodyLocked, true, 'background scrolling must be locked while open');

	// Focus trap: tabbing past the last control wraps back inside the dialog.
	const focusables = await page.evaluate(() =>
		document.querySelectorAll('.stp-estimate__dialog a[href], .stp-estimate__dialog button').length
	);
	for (let i = 0; i < focusables + 2; i++) await page.keyboard.press('Tab');
	assert.equal(
		await page.evaluate(() => document.querySelector('.stp-estimate__dialog').contains(document.activeElement)),
		true,
		'focus must stay trapped inside the dialog'
	);

	// Focus returns to where it was once the dialog closes.
	await page.keyboard.press('Escape');
	await page.evaluate('window.__clock.tick(500)');
	assert.equal(
		await page.evaluate(() => document.querySelector('.stp-estimate__dialog').contains(document.activeElement)),
		false,
		'focus must leave the dialog after closing'
	);

	assert.deepEqual(consoleErrors, [], 'no console errors');
	await context.close();
});

test('the copy is exactly as specified', async () => {
	const { context, page } = await freshPage();
	await page.evaluate('window.__clock.tick(15000)');

	const copy = await page.evaluate(() => ({
		eyebrow: document.querySelector('.stp-estimate__eyebrow').textContent.trim(),
		title: document.querySelector('.stp-estimate__title').textContent.trim(),
		lede: document.querySelector('.stp-estimate__lede').textContent.replace(/\s+/g, ' ').trim(),
		list: [...document.querySelectorAll('.stp-estimate__list-item span')].map((n) => n.textContent.trim()),
		or: document.querySelector('.stp-estimate__or').textContent.trim(),
		reassure: [...document.querySelectorAll('.stp-estimate__reassure li')].map((n) => n.textContent.trim()),
	}));

	assert.equal(copy.eyebrow, 'LOS ANGELES POOL EXPERTS');
	assert.equal(copy.title, 'Get a Free Estimate');
	assert.equal(copy.lede, 'Tell us about your pool. We’ll follow up with a clear, no-pressure quote.');
	assert.deepEqual(copy.list, [
		'Free, no-obligation estimate',
		'Response within 1 business day',
		'Upfront pricing before any work begins',
		'Serving 50+ Los Angeles communities',
	]);
	assert.equal(copy.or, 'or');
	assert.deepEqual(copy.reassure, ['No spam', 'No pressure', 'Fast response']);

	await context.close();
});

test('it fits the 390px mobile viewport without horizontal overflow', async () => {
	const context = await browser.createBrowserContext();
	const page = await context.newPage();
	await page.setViewport({ width: 390, height: 844 });
	await page.evaluateOnNewDocument(FAKE_CLOCK);
	await page.goto(BASE + '/', { waitUntil: 'domcontentloaded' });
	await page.waitForFunction('!!document.getElementById("stp-estimate-popup").stpEstimate');
	await page.evaluate('window.__clock.tick(15000)');

	const box = await page.evaluate(() => {
		const d = document.querySelector('.stp-estimate__dialog').getBoundingClientRect();
		return { left: d.left, right: d.right, width: d.width, vw: window.innerWidth, docW: document.documentElement.scrollWidth };
	});
	assert.ok(box.left >= 0, `dialog overflows left edge (${box.left})`);
	assert.ok(box.right <= box.vw + 1, `dialog overflows right edge (${box.right} > ${box.vw})`);
	assert.ok(box.docW <= box.vw + 1, `page scrolls horizontally (${box.docW} > ${box.vw})`);

	await context.close();
});

test('it is not rendered in feeds or the XML sitemap', async () => {
	const context = await browser.createBrowserContext();
	const page = await context.newPage();

	for (const p of ['/feed/', '/wp-sitemap.xml']) {
		const res = await page.goto(BASE + p, { waitUntil: 'domcontentloaded' });
		const body = await res.text();
		assert.ok(!body.includes('stp-estimate'), `popup markup leaked into ${p}`);
	}
	await context.close();
});
