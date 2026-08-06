/**
 * Header scroll state.
 *
 * The header is CSS-fixed and CSS-styled; the only thing this file decides is
 * which of the two states it is in, published as data-scrolled on the header
 * element. Everything visual — transparency, frosted background, nav colour,
 * contrast scrim — is keyed off that one attribute in header.css.
 *
 * The mobile drawer (open/close, focus trap, Escape, body-scroll lock) is
 * owned by main.js. Two click handlers on .js-mobile-toggle used to double-fire
 * and read as flicker, so there is a single owner and this file stays out of it.
 *
 * Vanilla JS, deferred, no dependencies.
 */

(function () {
	'use strict';

	// Flip as soon as the page has moved off the very top. Low enough that the
	// header commits to a readable surface the instant the hero starts to slide
	// under it, high enough to ignore sub-pixel and elastic-scroll jitter.
	const SCROLL_TRIGGER = 20;

	function initScrollState() {
		const header = document.querySelector('.js-site-header');
		if (!header) return;

		// Read once, from the server-rendered attribute, so the first frame
		// never disagrees with the markup and there is no colour flash.
		let scrolled = header.dataset.scrolled === 'true';
		let ticking = false;

		const apply = () => {
			ticking = false;
			// scrollY is a cheap read and is the only measurement taken —
			// no getBoundingClientRect, no layout is forced on scroll.
			const next = window.scrollY > SCROLL_TRIGGER;
			if (next === scrolled) return;
			scrolled = next;
			header.dataset.scrolled = next ? 'true' : 'false';
		};

		const onScroll = () => {
			if (ticking) return;
			ticking = true;
			window.requestAnimationFrame(apply);
		};

		// Sync immediately: a reload that restores a mid-page scroll position,
		// or an in-page anchor, must land in the correct state right away.
		apply();

		window.addEventListener('scroll', onScroll, { passive: true });
		// Height changes (orientation, dvh address-bar collapse) can move the
		// page without a scroll event firing.
		window.addEventListener('resize', onScroll, { passive: true });
		// Back/forward cache restores skip DOMContentLoaded entirely.
		window.addEventListener('pageshow', apply);
	}

	const ready = (fn) => {
		if (document.readyState !== 'loading') return fn();
		document.addEventListener('DOMContentLoaded', fn, { once: true });
	};

	ready(() => {
		initScrollState();
	});
})();
