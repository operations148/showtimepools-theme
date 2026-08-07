/**
 * Projects archive slider — dependency-free, progressive enhancement.
 *
 * The server renders every project card inside stacked slides. Without this
 * script the CSS leaves them stacked as a normal readable grid and the nav
 * stays hidden, so all cards remain visible, linked and keyboard reachable.
 * This script ONLY paginates what is already in the DOM: it never creates,
 * fetches, injects or removes a card.
 *
 * Markup contract:
 *   [data-proj-slider]
 *     [data-proj-slider-viewport]
 *       [data-proj-slider-track]
 *         [data-proj-slider-slide="0..n"]
 *     [data-proj-slider-nav]            — starts with the `hidden` attribute
 *       [data-proj-slider-prev]
 *       [data-proj-slider-dot="0..n"]
 *       [data-proj-slider-next]
 *     [data-proj-slider-status]         — aria-live announcement target
 *
 * Optional [data-proj-slider-label] on the root sets the noun used in the live
 * announcement ("Slide 2 of 3" by default, "Gallery page 2 of 2" for the
 * project gallery). Everything else is identical, so both components share one
 * implementation of paging, keyboard, swipe and end-state handling.
 *
 * Interaction: prev/next buttons, dots, ArrowLeft/ArrowRight/Home/End,
 * and horizontal touch swipe. No autoplay — the slide never changes on its own.
 */

(function () {
	'use strict';

	var SWIPE_THRESHOLD = 45; // px of horizontal travel before it counts
	var SWIPE_SLOP      = 60; // px of vertical travel that cancels the swipe

	function initSlider(root) {
		var track  = root.querySelector('[data-proj-slider-track]');
		var slides = Array.prototype.slice.call(root.querySelectorAll('[data-proj-slider-slide]'));
		if (!track || slides.length < 2) return;

		var viewport = root.querySelector('[data-proj-slider-viewport]');
		var nav      = root.querySelector('[data-proj-slider-nav]');
		var prev     = root.querySelector('[data-proj-slider-prev]');
		var next     = root.querySelector('[data-proj-slider-next]');
		var dots     = Array.prototype.slice.call(root.querySelectorAll('[data-proj-slider-dot]'));
		var status   = root.querySelector('[data-proj-slider-status]');
		var index    = 0;
		var total    = slides.length;
		var label    = root.getAttribute('data-proj-slider-label') || 'Slide';
		var supportsInert = 'inert' in HTMLElement.prototype;

		// Take over layout only once we know scripting works.
		root.classList.add('is-enhanced');
		if (nav) nav.removeAttribute('hidden');

		// Keep off-screen slides out of the tab order so focus can never scroll
		// the viewport sideways and desync it from the transform. `inert` does
		// this and hides them from AT in one step; the fallback does both by hand.
		function setSlideActive(slide, active) {
			if (supportsInert) {
				slide.inert = !active;
			} else {
				var focusables = slide.querySelectorAll('a[href], button, input, select, textarea, [tabindex]');
				Array.prototype.forEach.call(focusables, function (el) {
					if (active) {
						if (el.hasAttribute('data-slider-tabindex')) {
							var previous = el.getAttribute('data-slider-tabindex');
							if (previous === '') el.removeAttribute('tabindex');
							else el.setAttribute('tabindex', previous);
							el.removeAttribute('data-slider-tabindex');
						}
					} else if (!el.hasAttribute('data-slider-tabindex')) {
						el.setAttribute('data-slider-tabindex', el.getAttribute('tabindex') || '');
						el.setAttribute('tabindex', '-1');
					}
				});
			}
			slide.setAttribute('aria-hidden', active ? 'false' : 'true');
		}

		function render(announce) {
			track.style.transform = 'translate3d(' + (-100 * index) + '%, 0, 0)';

			slides.forEach(function (slide, i) {
				setSlideActive(slide, i === index);
			});

			dots.forEach(function (dot, i) {
				var on = i === index;
				dot.classList.toggle('is-active', on);
				if (on) dot.setAttribute('aria-current', 'true');
				else dot.removeAttribute('aria-current');
			});

			// Non-looping: the ends are disabled rather than wrapping, so the
			// control state always matches what activating it would do.
			if (prev) prev.disabled = index === 0;
			if (next) next.disabled = index === total - 1;

			// Only announce on user-driven changes, never on first paint.
			if (announce && status) {
				status.textContent = label + ' ' + (index + 1) + ' of ' + total;
			}
		}

		function goTo(i, announce) {
			var clamped = Math.max(0, Math.min(total - 1, i));
			if (clamped === index) return;
			index = clamped;
			render(announce !== false);
		}

		if (prev) prev.addEventListener('click', function () { goTo(index - 1); });
		if (next) next.addEventListener('click', function () { goTo(index + 1); });

		dots.forEach(function (dot) {
			dot.addEventListener('click', function () {
				goTo(parseInt(dot.getAttribute('data-proj-slider-dot'), 10) || 0);
			});
		});

		// Keyboard: only when focus is inside the slider, and never when the
		// user is typing in a field.
		root.addEventListener('keydown', function (e) {
			var t = e.target;
			if (t && (t.tagName === 'INPUT' || t.tagName === 'TEXTAREA' || t.isContentEditable)) return;
			if (e.key === 'ArrowLeft')      { e.preventDefault(); goTo(index - 1); }
			else if (e.key === 'ArrowRight'){ e.preventDefault(); goTo(index + 1); }
			else if (e.key === 'Home')      { e.preventDefault(); goTo(0); }
			else if (e.key === 'End')       { e.preventDefault(); goTo(total - 1); }
		});

		// Touch swipe. Passive listeners — we decide direction from the deltas
		// instead of calling preventDefault, so vertical page scrolling is
		// never blocked.
		var startX = 0, startY = 0, tracking = false;
		if (viewport) {
			viewport.addEventListener('touchstart', function (e) {
				if (e.touches.length !== 1) { tracking = false; return; }
				startX = e.touches[0].clientX;
				startY = e.touches[0].clientY;
				tracking = true;
			}, { passive: true });

			viewport.addEventListener('touchend', function (e) {
				if (!tracking) return;
				tracking = false;
				var touch = e.changedTouches && e.changedTouches[0];
				if (!touch) return;
				var dx = touch.clientX - startX;
				var dy = touch.clientY - startY;
				if (Math.abs(dy) > SWIPE_SLOP) return;      // vertical scroll wins
				if (Math.abs(dx) < SWIPE_THRESHOLD) return; // too small to count
				goTo(dx < 0 ? index + 1 : index - 1);
			}, { passive: true });

			// Belt and braces against horizontal drift: if anything ever scrolls
			// the viewport, snap it back so the transform stays authoritative.
			viewport.addEventListener('scroll', function () {
				if (viewport.scrollLeft !== 0) viewport.scrollLeft = 0;
			}, { passive: true });
		}

		render(false);
	}

	function ready(fn) {
		if (document.readyState !== 'loading') return fn();
		document.addEventListener('DOMContentLoaded', fn, { once: true });
	}

	ready(function () {
		Array.prototype.forEach.call(
			document.querySelectorAll('[data-proj-slider]'),
			initSlider
		);
	});
})();
