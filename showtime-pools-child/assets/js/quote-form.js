/*
 * Homepage quick-quote form — lazy iframe loader.
 *
 * The GHL <iframe> is injected only when the section scrolls near the viewport
 * (IntersectionObserver, 400px rootMargin), so nothing GHL-related loads at
 * initial paint and the homepage LCP work is untouched. The GHL resize helper
 * (form_embed.js) loads once, after the iframe exists. Mirrors popup.js.
 */
(function () {
	'use strict';

	var embed = document.querySelector('[data-quote-embed]');
	if (!embed) { return; }

	var injected = false;

	function injectIframe() {
		if (injected) { return; }
		var src = embed.getAttribute('data-src');
		if (!src) { return; }
		injected = true;

		var iframe = document.createElement('iframe');
		iframe.src = src;
		iframe.id = 'stp-quote-iframe';
		iframe.title = embed.getAttribute('data-title') || 'Quote form';
		iframe.setAttribute('scrolling', 'no');
		iframe.setAttribute('referrerpolicy', 'no-referrer-when-downgrade');
		iframe.style.width = '100%';
		iframe.style.minHeight = '560px';
		iframe.style.border = '0';

		var placeholder = embed.querySelector('.home-quote__placeholder');
		iframe.addEventListener('load', function () {
			embed.classList.add('is-loaded');
			if (placeholder) { placeholder.remove(); }
		});

		embed.appendChild(iframe);

		// GHL resize helper, loaded once, only after the iframe exists.
		if (!document.getElementById('stp-ghl-embed-js')) {
			var s = document.createElement('script');
			s.id = 'stp-ghl-embed-js';
			s.src = 'https://app.showtimepoolmechanics.com/js/form_embed.js';
			s.async = true;
			document.body.appendChild(s);
		}
	}

	if ('IntersectionObserver' in window) {
		var io = new IntersectionObserver(function (entries) {
			if (entries[0] && entries[0].isIntersecting) {
				io.disconnect();
				injectIframe();
			}
		}, { rootMargin: '400px 0px' });
		io.observe(embed);
	} else {
		// No IO support — load on first interaction or after idle as a fallback.
		injectIframe();
	}
}());
