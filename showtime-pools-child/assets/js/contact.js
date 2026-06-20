/*
 * contact.js — native contact form, posts to /wp-json/showtime/v1/contact.
 * No deps. Renders inline field errors and a top-level alert. Disables the
 * submit button while in flight to prevent double-submits.
 */

(function () {
	'use strict';

	const form = document.getElementById('showtime-contact-form');
	if (!form) return;

	const cfg = window.ShowtimeConfig || {};
	if (!cfg.restUrl || !cfg.nonce) {
		console.warn('[Showtime] Contact form: ShowtimeConfig missing restUrl/nonce');
		return;
	}

	const submitBtn = form.querySelector('button[type="submit"]');
	const okAlert   = form.querySelector('[data-status="success"]');
	const errAlert  = form.querySelector('[data-status="error"]');

	// UTM attribution: start from the CMS-default hidden fields, then let any
	// real ?utm_* in the visitor's URL override them. Synced back onto the
	// hidden inputs so a no-JS fallback (native POST) would still carry defaults.
	const UTM_KEYS = ['utm_source', 'utm_medium', 'utm_campaign', 'utm_content'];
	function collectUtm() {
		const params = new URLSearchParams(window.location.search);
		const utm = {};
		UTM_KEYS.forEach((key) => {
			const input = form.querySelector(`[data-utm="${key}"]`);
			const fromUrl = params.get(key);
			const value = (fromUrl != null && fromUrl !== '') ? fromUrl : (input ? input.value : '');
			if (input && fromUrl != null && fromUrl !== '') input.value = fromUrl;
			if (value) utm[key] = value;
		});
		return utm;
	}

	function clearErrors() {
		form.querySelectorAll('.form-error').forEach(el => {
			el.hidden = true;
			el.textContent = '';
		});
		form.querySelectorAll('[aria-invalid="true"]').forEach(el => el.removeAttribute('aria-invalid'));
		if (okAlert)  { okAlert.hidden  = true; okAlert.textContent  = ''; }
		if (errAlert) { errAlert.hidden = true; errAlert.textContent = ''; }
	}

	function showFieldErrors(errors) {
		Object.entries(errors).forEach(([field, msg]) => {
			const target = form.querySelector(`[data-field="${field}"]`);
			const input  = form.querySelector(`[name="${field}"]`);
			if (target) {
				target.hidden = false;
				target.textContent = msg;
			}
			if (input) input.setAttribute('aria-invalid', 'true');
		});
	}

	function showTopAlert(kind, msg) {
		const el = kind === 'ok' ? okAlert : errAlert;
		if (!el) return;
		el.hidden = false;
		el.textContent = msg;
	}

	form.addEventListener('submit', async function (e) {
		e.preventDefault();
		clearErrors();

		const fd = new FormData(form);
		const payload = {
			name:      String(fd.get('name')      || '').trim(),
			email:     String(fd.get('email')     || '').trim(),
			phone:     String(fd.get('phone')     || '').trim(),
			service:   String(fd.get('service')   || '').trim(),
			message:   String(fd.get('message')   || '').trim(),
			consent:   fd.get('consent') === '1',
			loaded_at: parseInt(String(fd.get('loaded_at') || '0'), 10) || 0,
			hp_url:    String(fd.get('hp_url')    || ''),
			page_url:  window.location.href,
			turnstile_token: String(fd.get('cf-turnstile-response') || ''),
			utm:       collectUtm(),
		};

		const defaultLabel = submitBtn ? submitBtn.dataset.defaultLabel || submitBtn.textContent : '';
		if (submitBtn) {
			submitBtn.disabled = true;
			submitBtn.setAttribute('aria-busy', 'true');
			submitBtn.textContent = 'Sending…';
		}

		try {
			const res = await fetch(cfg.restUrl + 'contact', {
				method: 'POST',
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/json',
					'X-WP-Nonce': cfg.nonce,
				},
				body: JSON.stringify(payload),
			});

			const data = await res.json().catch(() => ({}));

			if (res.status === 422 && data.errors) {
				showFieldErrors(data.errors);
				showTopAlert('err', 'Please fix the highlighted fields and try again.');
				return;
			}

			if (res.status === 429) {
				showTopAlert('err', data.message || 'Too many submissions. Try again in an hour or call us.');
				return;
			}

			if (!res.ok || !data.ok) {
				showTopAlert('err', "Something went wrong. Try again, or call (323) 825-2099.");
				return;
			}

			showTopAlert('ok', data.message || 'Thanks. We will follow up within one business day.');
			form.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]), textarea, select').forEach(el => { el.value = ''; });

		} catch (err) {
			console.error('[Showtime] Contact submit failed', err);
			showTopAlert('err', 'Network hiccup. Try again, or call (323) 825-2099.');
		} finally {
			if (submitBtn) {
				submitBtn.disabled = false;
				submitBtn.removeAttribute('aria-busy');
				submitBtn.textContent = defaultLabel;
			}
		}
	});
})();
