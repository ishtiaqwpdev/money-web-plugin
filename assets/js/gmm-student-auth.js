/**
 * Gospel Music Mastery — Student Auth AJAX preparation
 * Register / login via admin-ajax when available; forms still post to admin-post.php.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_STUDENT_AUTH || {};
	var ajaxMode = !!(cfg.ajaxUrl && cfg.nonce);

	function showError(formId, message) {
		var map = {
			'student-register-form': { box: 'register-error', text: 'register-error-text', success: 'register-success' },
			'student-login-form': { box: 'student-login-error', text: 'student-login-error-text', success: null }
		};
		var ids = map[formId];
		if (!ids) {
			return;
		}
		var box = document.getElementById(ids.box);
		var text = document.getElementById(ids.text);
		var ok = ids.success ? document.getElementById(ids.success) : null;
		if (ok) {
			ok.hidden = true;
		}
		if (box) {
			box.hidden = false;
		}
		if (text) {
			text.textContent = message || (cfg.i18n && cfg.i18n.error) || 'Something went wrong.';
		}
	}

	function showSuccess(message) {
		var box = document.getElementById('register-success');
		var err = document.getElementById('register-error');
		if (err) {
			err.hidden = true;
		}
		if (box) {
			box.hidden = false;
			var span = box.querySelector('span');
			if (span) {
				span.textContent = message || (cfg.i18n && cfg.i18n.success) || 'Registration successful.';
			}
		}
	}

	function post(action, form) {
		var fd = new window.FormData(form);
		fd.set('action', action);
		if (!fd.get(cfg.nonceField || 'gmm_auth_nonce')) {
			fd.set(cfg.nonceField || 'gmm_auth_nonce', cfg.nonce || '');
		}
		return window.fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			body: fd
		}).then(function (res) {
			return res.json();
		});
	}

	function bindForm(formId, actionKey) {
		var form = document.getElementById(formId);
		if (!form || !ajaxMode) {
			return;
		}
		form.addEventListener('submit', function (e) {
			// Allow native post if fetch unavailable.
			if (!window.fetch) {
				return;
			}
			e.preventDefault();
			var btn = form.querySelector('button[type="submit"]');
			if (btn) {
				btn.disabled = true;
			}
			var action = (cfg.actions && cfg.actions[actionKey]) || actionKey;
			post(action, form)
				.then(function (json) {
					if (!json || !json.success) {
						var msg = (json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error);
						showError(formId, msg);
						return;
					}
					if (actionKey === 'register') {
						showSuccess((json.data && json.data.message) || '');
					}
					var redirect = json.data && json.data.redirect;
					if (redirect) {
						window.location.href = redirect;
					}
				})
				.catch(function () {
					showError(formId, cfg.i18n && cfg.i18n.error);
				})
				.finally(function () {
					if (btn) {
						btn.disabled = false;
					}
				});
		});
	}

	function init() {
		bindForm('student-register-form', 'register');
		bindForm('student-login-form', 'login');

		// Password toggles (frozen markup).
		document.querySelectorAll('.password-toggle').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var targetId = btn.getAttribute('data-target') || 'student-password';
				var input = document.getElementById(targetId) || document.getElementById('student-password');
				if (!input) {
					return;
				}
				var show = input.type === 'password';
				input.type = show ? 'text' : 'password';
				var icon = btn.querySelector('i');
				if (icon) {
					icon.className = show ? 'far fa-eye-slash' : 'far fa-eye';
				}
			});
		});
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(window, document);
