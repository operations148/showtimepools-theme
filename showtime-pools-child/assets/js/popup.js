/**
 * Sitewide delayed "Get a Free Estimate" popup.
 *
 * Loaded deferred in the footer and enqueued exactly once (inc/popup.php), so
 * the listeners below are bound one time per page — never per template.
 *
 * Behaviour
 *   - Opens exactly 15000ms after the initial page load. Never immediately.
 *   - Shows at most once per browser session, tracked in sessionStorage.
 *   - Closing it, or following either CTA, records the dismissal so it cannot
 *     reopen for the rest of that session.
 *   - Embeds nothing: the primary CTA is a same-tab link, so the GHL booking
 *     calendar is only ever requested after a click.
 *
 * Accessibility
 *   - Focus moves into the dialog on open, is trapped while open, and returns
 *     to whatever held it before.
 *   - Escape, the close button and a backdrop click all dismiss it.
 *   - Background scrolling is locked while open.
 *   - prefers-reduced-motion suppresses the transition (handled in CSS).
 */

(function () {
	'use strict';

	var popup = document.getElementById('stp-estimate-popup');
	if (!popup) { return; }

	var DELAY_MS  = 15000;
	var STORE_KEY = 'stp_estimate_popup_dismissed';
	var FOCUSABLE = 'a[href], button:not([disabled]), input:not([disabled]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])';

	var dialog  = popup.querySelector('.stp-estimate__dialog');
	var closers = popup.querySelectorAll('[data-estimate-close]');
	var ctas    = popup.querySelectorAll('[data-estimate-cta]');

	var isOpen    = false;
	var lastFocus = null;
	var timer     = null;

	/** Session dismissal. Storage access is guarded — Safari private mode throws. */
	function dismissed() {
		try {
			return window.sessionStorage.getItem(STORE_KEY) === '1';
		} catch (e) {
			return false;
		}
	}

	function recordDismissal() {
		try {
			window.sessionStorage.setItem(STORE_KEY, '1');
		} catch (e) { /* storage unavailable — popup simply isn't suppressed */ }
	}

	function focusable() {
		if (!dialog) { return []; }
		return Array.prototype.slice
			.call(dialog.querySelectorAll(FOCUSABLE))
			.filter(function (el) {
				return el.offsetWidth > 0 || el.offsetHeight > 0 || el === document.activeElement;
			});
	}

	function onKeydown(e) {
		if (e.key === 'Escape' || e.key === 'Esc') {
			e.preventDefault();
			close();
			return;
		}
		if (e.key !== 'Tab') { return; }

		var f = focusable();
		if (!f.length) { return; }

		var first  = f[0];
		var last   = f[f.length - 1];
		var active = document.activeElement;

		if (e.shiftKey && (active === first || !dialog.contains(active))) {
			e.preventDefault();
			last.focus();
		} else if (!e.shiftKey && active === last) {
			e.preventDefault();
			first.focus();
		}
	}

	function open() {
		if (isOpen || dismissed()) { return; }
		isOpen = true;
		lastFocus = document.activeElement;

		popup.hidden = false;
		popup.removeAttribute('aria-hidden');
		// Next frame, so the transition runs from the hidden state.
		window.requestAnimationFrame(function () {
			popup.classList.add('is-open');
		});

		document.body.classList.add('stp-estimate-lock');
		document.addEventListener('keydown', onKeydown);

		var f = focusable();
		if (f.length) { f[0].focus(); }
	}

	/**
	 * @param {boolean} [keepFocus] True when the visitor is navigating away via a
	 *   CTA — returning focus then would fight the navigation.
	 */
	function close(keepFocus) {
		if (!isOpen) { return; }
		isOpen = false;

		if (timer) { window.clearTimeout(timer); timer = null; }

		popup.classList.remove('is-open');
		document.body.classList.remove('stp-estimate-lock');
		document.removeEventListener('keydown', onKeydown);
		recordDismissal();

		var done = false;
		var finish = function () {
			if (done) { return; }
			done = true;
			popup.hidden = true;
			popup.setAttribute('aria-hidden', 'true');
			popup.removeEventListener('transitionend', finish);
		};
		popup.addEventListener('transitionend', finish);
		window.setTimeout(finish, 400); // fallback when no transition runs

		// Focus must not be left stranded on a control that is about to be
		// hidden. Blur whatever the dialog still holds first, then hand focus
		// back to the opener when there is a real one to return it to (on the
		// timed open there usually is not — the page had focus on <body>).
		if (!keepFocus) {
			var active = document.activeElement;
			if (dialog && active && dialog.contains(active) && typeof active.blur === 'function') {
				active.blur();
			}
			if (lastFocus
				&& lastFocus !== document.body
				&& typeof lastFocus.focus === 'function'
				&& document.contains(lastFocus)) {
				lastFocus.focus();
			}
		}
	}

	Array.prototype.forEach.call(closers, function (el) {
		el.addEventListener('click', function (e) {
			e.preventDefault();
			close();
		});
	});

	// Following a CTA counts as a dismissal, but must not cancel the navigation.
	Array.prototype.forEach.call(ctas, function (el) {
		el.addEventListener('click', function () {
			recordDismissal();
			close(true);
		});
	});

	// Already seen this session — never arm the timer.
	if (dismissed()) { return; }

	timer = window.setTimeout(open, DELAY_MS);

	// Exposed for tests only: lets a spec drive the timer and inspect state
	// without reaching into module internals.
	popup.stpEstimate = { open: open, close: close, delay: DELAY_MS, storeKey: STORE_KEY };
})();
