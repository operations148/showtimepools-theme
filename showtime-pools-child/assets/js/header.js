/**
 * Header behavior — sticky scroll-state, mobile drawer toggle, focus trap,
 * Escape-to-close, body-scroll lock.
 *
 * Vanilla JS, deferred. Idempotent: calling init() twice is a no-op.
 */

(function () {
	'use strict';

	const SCROLL_TRIGGER = 80;

	function initStickyState() {
		const header = document.querySelector('.js-site-header');
		if (!header) return;

		let scrolled = false;
		const onScroll = () => {
			const next = window.scrollY > SCROLL_TRIGGER;
			if (next !== scrolled) {
				scrolled = next;
				header.dataset.scrolled = scrolled ? 'true' : 'false';
			}
		};
		onScroll();
		window.addEventListener('scroll', onScroll, { passive: true });
	}

	// (Mobile drawer open/close + focus-trap is owned by main.js — having
	// two click handlers attached to .js-mobile-toggle was double-firing
	// state changes (one set body.dataset.mobileOpen, the other set
	// body.classList.is-drawer-open) and reading as flicker. Single owner
	// in main.js, single source of truth.)

	const ready = (fn) => {
		if (document.readyState !== 'loading') return fn();
		document.addEventListener('DOMContentLoaded', fn, { once: true });
	};

	ready(() => {
		initStickyState();
	});
})();
