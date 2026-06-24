/*
 * Cookie consent banner controller + Google Consent Mode v2 update.
 *
 * The Consent Mode *default* (denied) and the replay of any stored choice run
 * inline in <head> (inc/consent.php) before GTM. This deferred script only
 * handles the interactive case: showing the banner when there is no stored
 * choice, and pushing a `consent` `update` + `stp_consent_update` dataLayer
 * event when the visitor decides.
 *
 * Cookie `stp_consent` = JSON {a:0|1, m:0|1, v:<version>, t:<unix>}.
 *   a = analytics, m = marketing. Necessary is always on.
 */
(function () {
	'use strict';

	var CFG = window.ShowtimeConsent || {};
	var NAME = CFG.cookie || 'stp_consent';
	var DAYS = CFG.days || 180;
	var VERSION = CFG.version || 1;

	var root = document.getElementById('stp-consent');
	if (!root) { return; }

	var prefs = document.getElementById('stp-consent-prefs');
	var prefsBtn = root.querySelector('[data-stp-consent="prefs"]');
	var lastFocus = null;

	window.dataLayer = window.dataLayer || [];
	function gtag() {
		if (typeof window.gtag === 'function') { window.gtag.apply(window, arguments); }
		else { window.dataLayer.push(arguments); }
	}

	// ── Storage ────────────────────────────────────────────────────────────
	function readChoice() {
		try {
			var m = document.cookie.match(new RegExp('(?:^|;\\s*)' + NAME + '=([^;]+)'));
			if (m) { return JSON.parse(decodeURIComponent(m[1])); }
			var ls = window.localStorage.getItem(NAME);
			if (ls) { return JSON.parse(ls); }
		} catch (e) {}
		return null;
	}

	function writeChoice(a, m) {
		var payload = JSON.stringify({ a: a ? 1 : 0, m: m ? 1 : 0, v: VERSION, t: Math.floor(Date.now() / 1000) });
		try {
			var d = new Date();
			d.setTime(d.getTime() + DAYS * 86400000);
			var secure = location.protocol === 'https:' ? ';Secure' : '';
			document.cookie = NAME + '=' + encodeURIComponent(payload) + ';expires=' + d.toUTCString() + ';path=/;SameSite=Lax' + secure;
			window.localStorage.setItem(NAME, payload);
		} catch (e) {}
	}

	// ── Consent Mode update + GTM trigger event ─────────────────────────────
	function applyConsent(a, m, source) {
		gtag('consent', 'update', {
			'analytics_storage': a ? 'granted' : 'denied',
			'ad_storage': m ? 'granted' : 'denied',
			'ad_user_data': m ? 'granted' : 'denied',
			'ad_personalization': m ? 'granted' : 'denied'
		});
		window.dataLayer.push({
			event: 'stp_consent_update',
			stp_consent: { analytics: !!a, marketing: !!m, source: source || 'banner' }
		});
	}

	// ── Banner visibility ───────────────────────────────────────────────────
	function show() {
		root.hidden = false;
		// Reveal on the next frame so the slide-in transition runs.
		requestAnimationFrame(function () { root.classList.add('is-visible'); });
	}

	function hide() {
		root.classList.remove('is-visible');
		closePrefs();
		var done = function () {
			root.hidden = true;
			root.removeEventListener('transitionend', done);
		};
		root.addEventListener('transitionend', done);
		// Fallback if no transition (reduced motion).
		setTimeout(function () { root.hidden = true; }, 400);
	}

	function decide(a, m, source) {
		writeChoice(a, m);
		applyConsent(a, m, source);
		hide();
	}

	// ── Preferences panel (focus-managed) ───────────────────────────────────
	function openPrefs() {
		var stored = readChoice();
		root.querySelectorAll('[data-stp-cat]').forEach(function (el) {
			var key = el.getAttribute('data-stp-cat');
			el.checked = !!(stored && stored[key === 'analytics' ? 'a' : 'm']);
		});
		lastFocus = document.activeElement;
		prefs.hidden = false;
		if (prefsBtn) { prefsBtn.setAttribute('aria-expanded', 'true'); }
		var first = prefs.querySelector('[data-stp-cat]');
		if (first) { first.focus(); }
		document.addEventListener('keydown', onKeydown, true);
	}

	function closePrefs() {
		if (prefs.hidden) { return; }
		prefs.hidden = true;
		if (prefsBtn) { prefsBtn.setAttribute('aria-expanded', 'false'); }
		document.removeEventListener('keydown', onKeydown, true);
		if (lastFocus && typeof lastFocus.focus === 'function') { lastFocus.focus(); }
	}

	function onKeydown(e) {
		if (e.key === 'Escape') { e.preventDefault(); closePrefs(); return; }
		if (e.key !== 'Tab') { return; }
		// Light focus trap within the open preferences dialog.
		var focusables = prefs.querySelectorAll('input:not([disabled]), button');
		if (!focusables.length) { return; }
		var first = focusables[0];
		var last = focusables[focusables.length - 1];
		if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
		else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
	}

	// ── Wiring ───────────────────────────────────────────────────────────────
	root.addEventListener('click', function (e) {
		var btn = e.target.closest('[data-stp-consent]');
		if (!btn) { return; }
		switch (btn.getAttribute('data-stp-consent')) {
			case 'accept': decide(true, true, 'accept_all'); break;
			case 'reject': decide(false, false, 'reject_all'); break;
			case 'prefs': prefs.hidden ? openPrefs() : closePrefs(); break;
			case 'save':
				var a = !!root.querySelector('[data-stp-cat="analytics"]').checked;
				var m = !!root.querySelector('[data-stp-cat="marketing"]').checked;
				decide(a, m, 'save_prefs');
				break;
		}
	});

	// Allow any element to reopen the banner later (e.g. a footer
	// "Cookie settings" link with data-stp-consent="open"), so visitors can
	// withdraw or change consent — a GDPR requirement.
	document.addEventListener('click', function (e) {
		var opener = e.target.closest('[data-stp-consent="open"]');
		if (!opener) { return; }
		e.preventDefault();
		show();
		openPrefs();
	});

	window.stpConsent = { open: function () { show(); openPrefs(); } };

	// Show the banner only when there is no stored choice.
	if (!readChoice()) { show(); }
}());
