/**
 * Before/After project comparison — progressive enhancement.
 *
 * The server ships two ordinary <figure> elements side by side. Both images
 * are already in the HTML and are fully usable with JS disabled; this file
 * only promotes a *compatible* pair (marked [data-proj-compare], meaning the
 * two photos share an aspect ratio) into a draggable overlay.
 *
 * The control is a real <input type="range">, not a synthetic div, so mouse,
 * touch, and keyboard all work for free and screen readers announce it as a
 * slider. It is opacity:0 rather than display:none — hiding it would remove
 * it from the accessibility tree and break keyboard access entirely.
 *
 * Vanilla ES2020+, no build step, no dependency.
 */

(function () {
	'use strict';

	const ready = (fn) => {
		if (document.readyState !== 'loading') return fn();
		document.addEventListener('DOMContentLoaded', fn, { once: true });
	};

	ready(() => {
		const widgets = document.querySelectorAll('[data-proj-compare]');
		if (!widgets.length) return;

		widgets.forEach((media) => {
			const before = media.querySelector('.proj-compare__frame--before');
			const after = media.querySelector('.proj-compare__frame--after');
			if (!before || !after) return; // Nothing to overlay — leave as-is.

			const setPos = (pct) => {
				const clamped = Math.max(0, Math.min(100, pct));
				media.style.setProperty('--proj-compare-pos', clamped + '%');
			};

			// Accessible label, tied to the section heading when one exists.
			const section = media.closest('.proj-compare');
			const headingText = section
				? (section.querySelector('h2') || {}).textContent || ''
				: '';

			const range = document.createElement('input');
			range.type = 'range';
			range.min = '0';
			range.max = '100';
			range.value = '50';
			range.step = '1';
			range.className = 'proj-compare__range';
			range.setAttribute(
				'aria-label',
				headingText.trim()
					? 'Reveal slider: ' + headingText.trim()
					: 'Before and after reveal slider'
			);
			range.setAttribute('aria-orientation', 'horizontal');
			range.setAttribute('aria-valuetext', '50% revealed');

			const divider = document.createElement('span');
			divider.className = 'proj-compare__divider';
			divider.setAttribute('aria-hidden', 'true');

			range.addEventListener('input', () => {
				const v = Number(range.value);
				setPos(v);
				range.setAttribute('aria-valuetext', v + '% revealed');
			});

			media.appendChild(divider);
			media.appendChild(range);
			setPos(50);
			media.classList.add('is-slider');
		});
	});
})();
