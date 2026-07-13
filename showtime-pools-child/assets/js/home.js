/**
 * Homepage-only JS — reviews carousel + interactive pool visualizer.
 * Loaded via enqueue.php only on is_front_page().
 */

(function () {
	'use strict';

	function initReviewsCarousel() {
		const carousel = document.querySelector('.js-reviews-carousel');
		if (!carousel) return;

		const track = carousel.querySelector('.js-reviews-track');
		const prev = carousel.querySelector('.js-reviews-prev');
		const next = carousel.querySelector('.js-reviews-next');
		if (!track || !prev || !next) return;

		const step = () => {
			const card = track.querySelector('.review-card');
			if (!card) return 320;
			// Snap by one card width + gap
			const styles = window.getComputedStyle(track);
			const gap = parseInt(styles.gap, 10) || 0;
			return card.getBoundingClientRect().width + gap;
		};

		const updateButtons = () => {
			const max = track.scrollWidth - track.clientWidth;
			prev.disabled = track.scrollLeft <= 2;
			next.disabled = track.scrollLeft >= max - 2;
		};

		prev.addEventListener('click', () => track.scrollBy({ left: -step(), behavior: 'smooth' }));
		next.addEventListener('click', () => track.scrollBy({ left:  step(), behavior: 'smooth' }));
		track.addEventListener('scroll', updateButtons, { passive: true });
		window.addEventListener('resize', updateButtons);
		updateButtons();

		// Keyboard support — left/right arrows when carousel is focused
		carousel.addEventListener('keydown', (e) => {
			if (e.key === 'ArrowLeft')  { e.preventDefault(); prev.click(); }
			if (e.key === 'ArrowRight') { e.preventDefault(); next.click(); }
		});
	}

	function initInteractivePool() {
		const root = document.querySelector('.js-interactive-pool');
		if (!root) return;

		const media = root.querySelector('.js-ip-media');
		const img = root.querySelector('.js-ip-img');
		const badge = root.querySelector('.js-ip-badge');
		const toggle = root.querySelector('.js-ip-toggle');
		const featureButtons = root.querySelectorAll('.js-ip-feature');
		const baseSrc = root.dataset.ipBase || (img ? img.src : '');

		// Day/Night — real filter transition + illustrated glow layer applied
		// to whichever photo is currently showing (base or a feature photo).
		if (toggle && media) {
			toggle.addEventListener('click', (e) => {
				const btn = e.target.closest('[data-ip-mode]');
				if (!btn) return;
				const mode = btn.dataset.ipMode;
				toggle.querySelectorAll('[data-ip-mode]').forEach((b) => {
					const active = b === btn;
					b.classList.toggle('is-active', active);
					b.setAttribute('aria-pressed', String(active));
				});
				media.classList.toggle('is-night', mode === 'night');
			});
		}

		// Feature buttons — one at a time. Clicking a feature swaps the main
		// photo to a real composite of the same pool with that feature
		// actually added (assets/img/add_*.webp), with a quick crossfade.
		// Clicking the active feature again returns to the base photo.
		// Clicking a different feature swaps directly to its photo instead.
		let activeBtn = null;

		const swapTo = (src, title) => {
			if (!img || !src) return;
			img.classList.add('is-swapping');
			window.setTimeout(() => {
				img.src = src;
				img.classList.remove('is-swapping');
			}, 160);
			if (badge) {
				badge.textContent = title ? title + ' added' : '';
			}
		};

		featureButtons.forEach((btn) => {
			btn.addEventListener('click', () => {
				const isSameActive = btn === activeBtn;

				featureButtons.forEach((b) => {
					b.classList.remove('is-active');
					b.setAttribute('aria-pressed', 'false');
				});

				if (isSameActive) {
					activeBtn = null;
					swapTo(baseSrc, '');
					return;
				}

				activeBtn = btn;
				btn.classList.add('is-active');
				btn.setAttribute('aria-pressed', 'true');
				swapTo(btn.dataset.ipFeatureImage || '', btn.dataset.ipFeatureTitle || '');
			});
		});
	}

	const ready = (fn) => {
		if (document.readyState !== 'loading') return fn();
		document.addEventListener('DOMContentLoaded', fn, { once: true });
	};

	ready(initReviewsCarousel);
	ready(initInteractivePool);
})();
