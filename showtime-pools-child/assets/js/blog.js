/**
 * Blog scripts — TOC builder + scroll-spy for single posts.
 *
 * Builds the table-of-contents nav from the article's <h2> headings,
 * assigns deterministic ids when missing, and highlights the active
 * heading as the reader scrolls. Pure vanilla, no deps. Degrades
 * cleanly when no H2s are present (TOC stays hidden).
 */
(function () {
	'use strict';

	if (typeof document === 'undefined') return;

	const reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

	function slugify(s) {
		return String(s)
			.toLowerCase()
			.replace(/[^a-z0-9\s-]/g, '')
			.trim()
			.replace(/\s+/g, '-')
			.slice(0, 80) || 'section';
	}

	function buildToc() {
		const prose = document.querySelector('.post-prose');
		const tocEl = document.querySelector('[data-toc]');
		const listEl = document.querySelector('[data-toc-list]');
		if (!prose || !tocEl || !listEl) return;

		const headings = prose.querySelectorAll('h2');
		if (!headings.length) {
			tocEl.setAttribute('data-empty', '1');
			return;
		}

		const used = new Set();
		const items = [];

		headings.forEach((h) => {
			let id = h.id;
			if (!id) {
				let base = slugify(h.textContent || '');
				id = base;
				let n = 2;
				while (used.has(id)) {
					id = base + '-' + n;
					n++;
				}
				h.id = id;
			}
			used.add(id);
			items.push({ id: id, text: (h.textContent || '').trim(), el: h });
		});

		items.forEach((item) => {
			const li = document.createElement('li');
			const a = document.createElement('a');
			a.href = '#' + item.id;
			a.textContent = item.text;
			a.addEventListener('click', function (e) {
				e.preventDefault();
				const target = document.getElementById(item.id);
				if (target) {
					target.scrollIntoView({ behavior: reduceMotion ? 'auto' : 'smooth', block: 'start' });
					history.replaceState(null, '', '#' + item.id);
				}
			});
			li.appendChild(a);
			listEl.appendChild(li);
		});

		// Scroll-spy.
		const linkById = {};
		listEl.querySelectorAll('a').forEach((a) => {
			linkById[a.getAttribute('href').slice(1)] = a;
		});

		function spy() {
			const top = window.scrollY + 140;
			let current = items[0].id;
			for (let i = 0; i < items.length; i++) {
				if (items[i].el.offsetTop <= top) {
					current = items[i].id;
				} else {
					break;
				}
			}
			Object.keys(linkById).forEach((id) => {
				linkById[id].classList.toggle('is-current', id === current);
			});
		}

		let scrollTimer = null;
		window.addEventListener('scroll', function () {
			if (scrollTimer) return;
			scrollTimer = window.requestAnimationFrame(function () {
				spy();
				scrollTimer = null;
			});
		}, { passive: true });

		spy();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', buildToc);
	} else {
		buildToc();
	}
})();
