/**
 * Global frontend JS. Vanilla ES2020+, no frameworks, no build step.
 * Loaded with defer + footer; do not assume render order vs the DOM.
 *
 * Responsibilities at this phase:
 *   - smooth-scroll for in-page anchors that respect reduced-motion
 *   - intersection-observer reveal for `.js-reveal` elements
 *   - external link safety (rel=noopener)
 *
 * Page-specific JS lives next to the template (assets/js/home.js, etc.)
 * and gets enqueued conditionally.
 */

(function () {
	'use strict';

	const ready = (fn) => {
		if (document.readyState !== 'loading') return fn();
		document.addEventListener('DOMContentLoaded', fn, { once: true });
	};

	ready(() => {
		// External link hardening — any <a target="_blank"> gets rel=noopener.
		document.querySelectorAll('a[target="_blank"]').forEach((a) => {
			const rel = (a.getAttribute('rel') || '').split(/\s+/);
			if (!rel.includes('noopener')) rel.push('noopener');
			if (!rel.includes('noreferrer')) rel.push('noreferrer');
			a.setAttribute('rel', rel.join(' ').trim());
		});

		// (Sticky-header [data-scrolled] state is owned by assets/js/header.js
		// with an 80px threshold. Don't duplicate it here — having two
		// conflicting trackers caused the transparent header to flip to its
		// solid state on micro-scrolls and on restored scroll positions.)

		// Back-to-top: fade in after 500px scroll, scroll to top on click.
		const btt = document.querySelector('.js-back-to-top');
		if (btt) {
			const showAt = 500;
			const toggle = () => {
				btt.classList.toggle('is-visible', window.scrollY > showAt);
			};
			toggle();
			window.addEventListener('scroll', toggle, { passive: true });
			btt.addEventListener('click', () => {
				const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
				window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' });
			});
		}

		// Mobile menu toggle (drawer open/close).
		const toggleBtn = document.querySelector('.js-mobile-toggle');
		const drawer    = document.getElementById('mobile-drawer');
		if (toggleBtn && drawer) {
			const setOpen = (open) => {
				toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
				drawer.classList.toggle('is-open', open);
				document.body.classList.toggle('is-drawer-open', open);
			};
			toggleBtn.addEventListener('click', () => {
				setOpen(toggleBtn.getAttribute('aria-expanded') !== 'true');
			});
			drawer.querySelectorAll('[data-drawer-close], a').forEach((el) => {
				el.addEventListener('click', () => setOpen(false));
			});
			document.addEventListener('keydown', (e) => {
				if (e.key === 'Escape') setOpen(false);
			});
		}

		// Scroll progress aqua line — thin 2px bar at top of viewport.
		const progress = document.createElement('div');
		progress.className = 'scroll-progress';
		document.body.appendChild(progress);
		const onProgress = () => {
			const h = document.documentElement;
			const max = h.scrollHeight - h.clientHeight;
			const pct = max > 0 ? (h.scrollTop / max) * 100 : 0;
			progress.style.setProperty('--scroll-progress', pct + '%');
		};
		onProgress();
		window.addEventListener('scroll', onProgress, { passive: true });

		// Reveal-on-scroll. Add `data-reveal` to opt in. Toggles the `is-revealed` class.
		const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
		const targets = document.querySelectorAll('[data-reveal]');
		if (targets.length && !reduced && 'IntersectionObserver' in window) {
			const io = new IntersectionObserver(
				(entries) => {
					entries.forEach((e) => {
						if (e.isIntersecting) {
							e.target.classList.add('is-revealed');
							io.unobserve(e.target);
						}
					});
				},
				{ rootMargin: '0px 0px -10% 0px', threshold: 0.1 }
			);
			targets.forEach((el) => io.observe(el));
		} else {
			// Reduced motion or no IO — show immediately.
			targets.forEach((el) => el.classList.add('is-revealed'));
		}
	});

	// Expose a tiny namespace for page-scoped scripts to hang helpers on.
	window.Showtime = window.Showtime || {};
	window.Showtime.config = window.ShowtimeConfig || {};
})();
