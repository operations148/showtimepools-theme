/*
 * affiliate.js — Partner Program signup form, posts to
 * /wp-json/showtime/v1/affiliate. No deps. Renders inline field errors and a
 * top-level alert. Disables the submit button while in flight. Mirrors
 * contact.js so both forms behave identically.
 */

(function () {
	'use strict';

	const form = document.getElementById('showtime-affiliate-form');
	if (!form) return;

	const cfg = window.ShowtimeConfig || {};
	if (!cfg.restUrl || !cfg.nonce) {
		console.warn('[Showtime] Affiliate form: ShowtimeConfig missing restUrl/nonce');
		return;
	}

	const submitBtn = form.querySelector('button[type="submit"]');
	const okAlert   = form.querySelector('[data-status="success"]');
	const errAlert  = form.querySelector('[data-status="error"]');

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
			full_name: String(fd.get('full_name') || '').trim(),
			email:     String(fd.get('email')     || '').trim(),
			phone:     String(fd.get('phone')     || '').trim(),
			website:   String(fd.get('website')   || '').trim(),
			promote:   fd.getAll('promote[]').map(v => String(v).trim()).filter(Boolean),
			consent:   fd.get('consent') === '1',
			loaded_at: parseInt(String(fd.get('loaded_at') || '0'), 10) || 0,
			hp_url:    String(fd.get('hp_url')    || ''),
			page_url:  window.location.href,
		};

		const defaultLabel = submitBtn ? submitBtn.dataset.defaultLabel || submitBtn.textContent : '';
		if (submitBtn) {
			submitBtn.disabled = true;
			submitBtn.setAttribute('aria-busy', 'true');
			submitBtn.textContent = 'Submitting…';
		}

		try {
			const res = await fetch(cfg.restUrl + 'affiliate', {
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

			showTopAlert('ok', data.message || 'Thanks! We will review your application and send your partner link shortly.');
			form.querySelectorAll('input:not([type="hidden"]):not([type="checkbox"]), textarea, select').forEach(el => { el.value = ''; });
			form.querySelectorAll('input[type="checkbox"]').forEach(el => { el.checked = false; });

		} catch (err) {
			console.error('[Showtime] Affiliate submit failed', err);
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
