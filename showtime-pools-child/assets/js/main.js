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

		// Mobile drawer — single source of truth. Pure class toggle.
		// .mobile-drawer lives in the DOM at all times; CSS handles all
		// visibility (opacity + translate transition). Attaches click
		// handlers to EVERY .js-mobile-toggle element (hamburger, close X)
		// so any of them flips state.
		const drawer = document.getElementById('mobile-drawer');
		if (drawer) {
			const toggles = document.querySelectorAll('.js-mobile-toggle');
			// The hamburger lives in the header; remember it so we can
			// return focus to it when the drawer closes (a11y).
			const hamburger = document.querySelector('.site-header__menu-toggle.js-mobile-toggle');
			const isOpen = () => drawer.classList.contains('is-open');

			// Selector for everything inside the drawer that can take focus.
			const FOCUSABLE = 'a[href], button:not([disabled]), summary, [tabindex]:not([tabindex="-1"])';
			const getFocusable = () =>
				Array.from(drawer.querySelectorAll(FOCUSABLE)).filter(
					(el) => !el.hasAttribute('hidden') && el.offsetParent !== null
				);

			const setOpen = (open) => {
				toggles.forEach((b) => b.setAttribute('aria-expanded', open ? 'true' : 'false'));
				drawer.classList.toggle('is-open', open);
				drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
				document.body.classList.toggle('is-drawer-open', open);

				if (open) {
					// Land focus on the first nav link after the open
					// transition starts — feels intentional, not jumpy.
					setTimeout(() => {
						const firstLink = drawer.querySelector('.mobile-drawer__list a, .mobile-drawer__list summary');
						if (firstLink) firstLink.focus();
					}, 60);
				} else if (hamburger) {
					// Return focus to the trigger so keyboard users don't
					// get stranded at <body> when the drawer closes.
					hamburger.focus();
				}
			};

			toggles.forEach((btn) => {
				btn.addEventListener('click', (e) => {
					e.preventDefault();
					setOpen(!isOpen());
				});
			});

			// Closing on any in-drawer link click means menu navigation
			// doesn't leave a stuck panel mid-transition.
			drawer.querySelectorAll('a[href]').forEach((link) => {
				link.addEventListener('click', () => setOpen(false));
			});

			// Keyboard handling: Escape closes; Tab is trapped so focus
			// can't escape into the page beneath the overlay.
			document.addEventListener('keydown', (e) => {
				if (!isOpen()) return;
				if (e.key === 'Escape') {
					e.preventDefault();
					setOpen(false);
					return;
				}
				if (e.key !== 'Tab') return;

				const focusable = getFocusable();
				if (focusable.length === 0) return;
				const first = focusable[0];
				const last = focusable[focusable.length - 1];
				const active = document.activeElement;

				if (e.shiftKey && (active === first || !drawer.contains(active))) {
					e.preventDefault();
					last.focus();
				} else if (!e.shiftKey && active === last) {
					e.preventDefault();
					first.focus();
				}
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

		// ── Scroll reveal + stagger + counter animations ───────────────
		// data-reveal   → fades/scales in when entering viewport
		// data-stagger  → children animate in sequence (--i CSS var)
		// data-count    → number counts up when first visible

		const reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;

		// Stamp --i on stagger children so CSS delay works.
		document.querySelectorAll('[data-stagger]').forEach((container) => {
			Array.from(container.children).forEach((child, i) => {
				child.style.setProperty('--i', i);
			});
		});

		// Counter animation — counts from 0 to data-count value.
		function runCounter(el) {
			const raw    = el.dataset.count || el.textContent;
			const target = parseFloat(raw.replace(/[^0-9.]/g, ''));
			const suffix = raw.replace(/[0-9.,]/g, '').trim(); // e.g. "+", "★"
			if (!target) return;
			const duration = 1800;
			const start    = performance.now();
			const isFloat  = target % 1 !== 0;
			(function tick(now) {
				const progress = Math.min((now - start) / duration, 1);
				const ease     = 1 - Math.pow(1 - progress, 3); // cubic ease-out
				const val      = isFloat
					? (ease * target).toFixed(1)
					: Math.round(ease * target).toLocaleString();
				el.textContent = val + suffix;
				if (progress < 1) requestAnimationFrame(tick);
			})(start);
		}

		const allTargets = [
			...document.querySelectorAll('[data-reveal]'),
			...document.querySelectorAll('[data-stagger]'),
		];

		if (allTargets.length && 'IntersectionObserver' in window) {
			const io = new IntersectionObserver(
				(entries) => {
					entries.forEach((e) => {
						if (!e.isIntersecting) return;
						e.target.classList.add('is-revealed');
						// Run counter on any [data-count] children
						e.target.querySelectorAll('[data-count]').forEach(runCounter);
						io.unobserve(e.target);
					});
				},
				{ rootMargin: '0px 0px -8% 0px', threshold: 0.08 }
			);
			allTargets.forEach((el) => io.observe(el));
		} else {
			// No IO support or reduced motion — show everything immediately.
			allTargets.forEach((el) => el.classList.add('is-revealed'));
		}
	});

	// Expose a tiny namespace for page-scoped scripts to hang helpers on.
	window.Showtime = window.Showtime || {};
	window.Showtime.config = window.ShowtimeConfig || {};
})();
