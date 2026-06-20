/**
 * Lightweight, dependency-free carousel controller.
 *
 * Progressive enhancement over a native horizontal scroll track with CSS
 * scroll-snap: swipe/trackpad already work without JS; this adds prev/next
 * buttons, end-state disabling, and arrow-key support. Loaded deferred; it does
 * nothing until the user interacts.
 *
 * Markup contract:
 *   [data-carousel]
 *     [data-carousel-track]   — the scrolling flex row of slides
 *     [data-carousel-prev]    — optional previous button
 *     [data-carousel-next]    — optional next button
 */

(function () {
	'use strict';

	function initCarousel(root) {
		const track = root.querySelector('[data-carousel-track]');
		if (!track) return;
		const prev = root.querySelector('[data-carousel-prev]');
		const next = root.querySelector('[data-carousel-next]');

		// One "page" = the width of a slide + the track gap.
		const step = () => {
			const card = track.children[0];
			if (!card) return track.clientWidth;
			const styles = window.getComputedStyle(track);
			const gap = parseFloat(styles.columnGap || styles.gap) || 0;
			return card.getBoundingClientRect().width + gap;
		};

		const update = () => {
			const max = track.scrollWidth - track.clientWidth;
			const atStart = track.scrollLeft <= 2;
			const atEnd = track.scrollLeft >= max - 2;
			if (prev) prev.disabled = atStart;
			if (next) next.disabled = atEnd;
			// Hide the whole nav if everything already fits (no overflow).
			root.classList.toggle('is-static', max <= 2);
		};

		if (prev) prev.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
		if (next) next.addEventListener('click', () => track.scrollBy({ left:  step(), behavior: 'smooth' }));
		track.addEventListener('scroll', update, { passive: true });
		window.addEventListener('resize', update);
		update();

		// Arrow keys when focus is anywhere inside the carousel.
		root.addEventListener('keydown', (e) => {
			if (e.key === 'ArrowLeft' && prev)  { e.preventDefault(); prev.click(); }
			if (e.key === 'ArrowRight' && next) { e.preventDefault(); next.click(); }
		});
	}

	const ready = (fn) => {
		if (document.readyState !== 'loading') return fn();
		document.addEventListener('DOMContentLoaded', fn, { once: true });
	};

	ready(() => document.querySelectorAll('[data-carousel]').forEach(initCarousel));
})();
