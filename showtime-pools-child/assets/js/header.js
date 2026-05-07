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

	function initMobileDrawer() {
		const drawer = document.getElementById('mobile-drawer');
		if (!drawer) return;

		const toggleButtons = document.querySelectorAll('.js-mobile-toggle');
		const body = document.body;
		let lastFocused = null;

		const open = () => {
			drawer.hidden = false;
			// Force reflow before flipping the data attribute so the transition runs.
			void drawer.offsetWidth;
			body.dataset.mobileOpen = 'true';
			toggleButtons.forEach((b) => b.setAttribute('aria-expanded', 'true'));
			lastFocused = document.activeElement;
			const closeBtn = drawer.querySelector('.mobile-drawer__close');
			if (closeBtn) closeBtn.focus();
		};

		const close = () => {
			body.dataset.mobileOpen = 'false';
			toggleButtons.forEach((b) => b.setAttribute('aria-expanded', 'false'));
			// Wait for animation, then hide for a11y.
			window.setTimeout(() => {
				drawer.hidden = true;
				if (lastFocused && lastFocused.focus) lastFocused.focus();
			}, 240);
		};

		toggleButtons.forEach((btn) => {
			btn.addEventListener('click', (e) => {
				e.preventDefault();
				if (body.dataset.mobileOpen === 'true') close();
				else open();
			});
		});

		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && body.dataset.mobileOpen === 'true') {
				close();
			}
		});

		// Focus trap — Tab cycles within the drawer when open.
		drawer.addEventListener('keydown', (e) => {
			if (e.key !== 'Tab' || body.dataset.mobileOpen !== 'true') return;
			const focusables = drawer.querySelectorAll(
				'a[href], button:not([disabled]), input:not([disabled]), [tabindex]:not([tabindex="-1"])'
			);
			if (!focusables.length) return;
			const first = focusables[0];
			const last = focusables[focusables.length - 1];
			if (e.shiftKey && document.activeElement === first) { e.preventDefault(); last.focus(); }
			else if (!e.shiftKey && document.activeElement === last) { e.preventDefault(); first.focus(); }
		});
	}

	const ready = (fn) => {
		if (document.readyState !== 'loading') return fn();
		document.addEventListener('DOMContentLoaded', fn, { once: true });
	};

	ready(() => {
		initStickyState();
		initMobileDrawer();
	});
})();
