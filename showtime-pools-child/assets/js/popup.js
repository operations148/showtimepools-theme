/**
 * Sitewide "Weekly Maintenance" popup (GHL form).
 *
 * Loaded deferred in the footer; does nothing at initial paint beyond binding
 * triggers. The GHL <iframe> + form_embed.js are injected only on first open,
 * so the popup never costs LCP or a request until the visitor sees it.
 *
 * Trigger: exit-intent on desktop, 30s dwell on mobile, plus any
 * [data-open-weekly-popup] button (which always opens, ignoring suppression).
 * Dismissal suppresses re-show for 7 days via localStorage. Accessible: focus
 * trap, ESC to close, focus returned to the opener, reduced-motion aware.
 */

(function () {
	'use strict';

	const popup = document.getElementById('stp-popup-weekly');
	if (!popup) return;

	const STORE_KEY  = 'stp_popup_weekly_until';
	const SEVEN_DAYS = 7 * 24 * 60 * 60 * 1000;
	const MOBILE_MQ  = '(max-width: 768px)';
	const DWELL_MS   = 30000;

	const dialog  = popup.querySelector('.stp-popup__dialog');
	const embed   = popup.querySelector('[data-popup-embed]');
	const closers = popup.querySelectorAll('[data-popup-close]');
	const openers = document.querySelectorAll('[data-open-weekly-popup]');
	const FOCUSABLE = 'a[href], button:not([disabled]), input, select, textarea, iframe, [tabindex]:not([tabindex="-1"])';

	let injected = false;
	let isOpen   = false;
	let lastFocus = null;

	function suppressed() {
		try {
			const until = parseInt(localStorage.getItem(STORE_KEY) || '0', 10);
			return until > 0 && Date.now() < until;
		} catch (e) { return false; }
	}
	function suppress() {
		try { localStorage.setItem(STORE_KEY, String(Date.now() + SEVEN_DAYS)); } catch (e) {}
	}

	function injectIframe() {
		if (injected || !embed) return;
		injected = true;
		const src = embed.getAttribute('data-src');
		if (!src) return;
		const iframe = document.createElement('iframe');
		iframe.src = src;
		iframe.id = 'inline-' + (embed.getAttribute('data-form-id') || 'weekly');
		iframe.title = embed.getAttribute('data-title') || 'Form';
		iframe.setAttribute('scrolling', 'no');
		iframe.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
		embed.appendChild(iframe);
		// GHL resize helper, loaded once, only after the iframe exists.
		if (!document.getElementById('stp-ghl-embed-js')) {
			const s = document.createElement('script');
			s.id = 'stp-ghl-embed-js';
			s.src = 'https://app.showtimepoolmechanics.com/js/form_embed.js';
			s.async = true;
			document.body.appendChild(s);
		}
	}

	function onKeydown(e) {
		if (e.key === 'Escape') { e.preventDefault(); close(); return; }
		if (e.key !== 'Tab' || !dialog) return;
		const f = Array.prototype.slice
			.call(dialog.querySelectorAll(FOCUSABLE))
			.filter((el) => el.offsetParent !== null || el.tagName === 'IFRAME');
		if (!f.length) return;
		const first = f[0], last = f[f.length - 1], active = document.activeElement;
		if (e.shiftKey && (active === first || !dialog.contains(active))) {
			e.preventDefault(); last.focus();
		} else if (!e.shiftKey && active === last) {
			e.preventDefault(); first.focus();
		}
	}

	function open() {
		if (isOpen) return;
		isOpen = true;
		lastFocus = document.activeElement;
		injectIframe();
		popup.hidden = false;
		// next frame so the transition runs from the hidden state
		requestAnimationFrame(() => popup.classList.add('is-open'));
		document.body.classList.add('stp-popup-lock');
		const closeBtn = popup.querySelector('.stp-popup__close');
		if (closeBtn) closeBtn.focus();
		document.addEventListener('keydown', onKeydown);
	}

	function close() {
		if (!isOpen) return;
		isOpen = false;
		popup.classList.remove('is-open');
		document.body.classList.remove('stp-popup-lock');
		document.removeEventListener('keydown', onKeydown);
		suppress();
		let done = false;
		const finish = () => {
			if (done) return;
			done = true;
			popup.hidden = true;
			popup.removeEventListener('transitionend', finish);
		};
		popup.addEventListener('transitionend', finish);
		setTimeout(finish, 400); // fallback if no transitionend
		if (lastFocus && typeof lastFocus.focus === 'function') lastFocus.focus();
	}

	closers.forEach((el) => el.addEventListener('click', (e) => { e.preventDefault(); close(); }));
	// Manual openers always work, even inside the 7-day suppression window.
	openers.forEach((el) => el.addEventListener('click', (e) => { e.preventDefault(); open(); }));

	// Auto-trigger only when not suppressed.
	if (suppressed()) return;

	if (window.matchMedia && window.matchMedia(MOBILE_MQ).matches) {
		window.setTimeout(() => { if (!suppressed()) open(); }, DWELL_MS);
	} else {
		const onLeave = (e) => {
			// Pointer left through the top of the viewport → likely leaving.
			if (e.clientY <= 0) {
				document.removeEventListener('mouseout', onLeave);
				if (!suppressed()) open();
			}
		};
		document.addEventListener('mouseout', onLeave);
	}
})();
