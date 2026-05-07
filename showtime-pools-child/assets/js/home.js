/**
 * Homepage-only JS — reviews carousel.
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

	const ready = (fn) => {
		if (document.readyState !== 'loading') return fn();
		document.addEventListener('DOMContentLoaded', fn, { once: true });
	};

	ready(initReviewsCarousel);
})();
