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

		// ── Lazy third-party review widgets (Trustindex) ───────────────
		// The Trustindex embed loads 30+ JS files via its loader script and
		// always sits below the fold. reviews-widget.php parks the exact
		// shortcode markup inside an inert <template>; here we inject it and
		// re-execute its <script> tags only when the section nears the
		// viewport, so it costs nothing at initial paint. Same defer-until-
		// needed approach as the popup iframe — the live pull is unchanged.
		const lazyReviews = document.querySelectorAll('[data-trustindex-lazy]');
		if (lazyReviews.length) {
			// P0-3: a server-rendered curated block sits above the widget so
			// no-JS crawlers see real review text. It must stay visible until
			// the live widget has genuinely hydrated — mounting alone is not
			// proof, since Trustindex parks its own markup in a hidden
			// <pre><template> that stays invisible if its loader never runs.
			// Only real, *visible* review items count as success.
			const hasVisibleReviews = (holder) => {
				const items = holder.querySelectorAll(
					'.ti-review-item, [class*="ti-review-item"]'
				);
				for (let i = 0; i < items.length; i++) {
					const el = items[i];
					// offsetParent is null for anything display:none (or an
					// ancestor that is), which is exactly the un-hydrated state.
					if (el.offsetParent !== null && el.textContent.trim().length > 0) {
						return true;
					}
				}
				return false;
			};

			// The curated block this widget would replace, if any. Scoped to the
			// widget's own stack so multiple instances never touch each other.
			const staticFor = (container) => {
				const stack = container.closest('[data-reviews-stack]');
				return stack ? stack.querySelector('[data-reviews-static]') : null;
			};

			// Hide the static block from sight AND from the accessibility tree
			// so the same reviews aren't announced twice. Never removed from
			// the DOM — if the widget is later torn down, this can come back.
			const retireStatic = (staticBlock) => {
				if (!staticBlock || staticBlock.hidden) return;
				staticBlock.hidden = true;
				staticBlock.setAttribute('aria-hidden', 'true');
			};

			// Watch a mounted widget for successful hydration. Gives up after a
			// bounded window; on give-up the static block simply stays put.
			const watchHydration = (container, holder) => {
				const staticBlock = staticFor(container);
				// No curated block on this page (the common case until reviews
				// are curated) — there is nothing to retire, so don't spin an
				// observer and a poll timer for ten seconds to no purpose.
				if (!staticBlock) return;

				if (hasVisibleReviews(holder)) {
					retireStatic(staticBlock);
					return;
				}
				let settled = false;
				const finish = () => {
					if (settled) return;
					settled = true;
					if (obs) obs.disconnect();
					clearInterval(poll);
					clearTimeout(bail);
				};
				const check = () => {
					if (settled) return;
					if (hasVisibleReviews(holder)) {
						retireStatic(staticBlock);
						finish();
					}
				};
				const obs =
					'MutationObserver' in window
						? new MutationObserver(check)
						: null;
				if (obs) obs.observe(holder, { childList: true, subtree: true });
				// Trustindex can swap content without mutating `holder` itself
				// (e.g. unhiding via style), so poll as a safety net too.
				const poll = setInterval(check, 400);
				const bail = setTimeout(finish, 10000);
			};

			const mountReviews = (container) => {
				if (container.dataset.mounted) return;
				const tpl = container.querySelector('template[data-trustindex-markup]');
				if (!tpl) return;
				container.dataset.mounted = '1';
				const holder = document.createElement('div');
				holder.className = 'reviews-widget__mount';
				holder.innerHTML = tpl.innerHTML; // inert: innerHTML never runs scripts
				tpl.remove();
				container.appendChild(holder);
				// innerHTML-parsed scripts don't execute — recreate each so the
				// loader (and any inline init) actually runs. Covers src + inline.
				holder.querySelectorAll('script').forEach((old) => {
					const s = document.createElement('script');
					Array.prototype.forEach.call(old.attributes, (a) => s.setAttribute(a.name, a.value));
					if (old.textContent) s.textContent = old.textContent;
					old.parentNode.replaceChild(s, old);
				});
				watchHydration(container, holder);
			};

			if ('IntersectionObserver' in window) {
				const rio = new IntersectionObserver(
					(entries, obs) => {
						entries.forEach((e) => {
							if (!e.isIntersecting) return;
							mountReviews(e.target);
							obs.unobserve(e.target);
						});
					},
					{ rootMargin: '300px 0px' } // begin loading just before it's seen
				);
				lazyReviews.forEach((el) => rio.observe(el));
			} else {
				lazyReviews.forEach(mountReviews); // no IO — mount immediately
			}
		}
	});

	// Expose a tiny namespace for page-scoped scripts to hang helpers on.
	window.Showtime = window.Showtime || {};
	window.Showtime.config = window.ShowtimeConfig || {};
})();
