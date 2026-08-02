/**
 * Gospel Music Mastery — Teacher profile interactions
 * Avatar click → image upload; AJAX helpers prepared. Design unchanged.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_TEACHER_PROFILE || {};

	function ajax(action, formData) {
		formData = formData || new window.FormData();
		formData.append('action', action);
		formData.append(cfg.nonceField || 'gmm_teacher_profile_nonce', cfg.nonce || '');
		return window.fetch(cfg.ajaxUrl || (window.GMM_DATA && window.GMM_DATA.ajax_url) || '', {
			method: 'POST',
			credentials: 'same-origin',
			body: formData
		}).then(function (res) {
			return res.json();
		});
	}

	function showAlert(type, message) {
		var success = document.getElementById('profile-success');
		if (!success) {
			return;
		}
		var span = success.querySelector('span');
		if (span && message) {
			span.textContent = message;
		}
		success.hidden = type !== 'success';
		success.classList.toggle('gospel-alert-success', type === 'success');
		success.classList.toggle('gospel-alert-error', type === 'error');
		if (type === 'success') {
			success.hidden = false;
			window.setTimeout(function () {
				success.hidden = true;
			}, 4000);
		}
	}

	function wireBioCounter() {
		var bio = document.getElementById('profile-bio');
		var counter = document.getElementById('bio-counter');
		if (!bio || !counter) {
			return;
		}
		function update() {
			counter.textContent = (bio.value || '').length + ' / 500';
		}
		bio.addEventListener('input', update);
		update();
	}

	function wireAvatarUpload() {
		var file = document.getElementById('profile-photo');
		var avatar = document.querySelector('.td-profile-avatar img');
		if (!file || !avatar) {
			return;
		}

		avatar.style.cursor = 'pointer';
		avatar.title = 'Click to change profile photo';
		avatar.addEventListener('click', function () {
			file.click();
		});

		file.addEventListener('change', function () {
			if (!file.files || !file.files[0]) {
				return;
			}
			var fd = new window.FormData();
			fd.append('profile_image', file.files[0]);
			ajax((cfg.actions && cfg.actions.image) || 'gmm_teacher_profile_image', fd)
				.then(function (json) {
					if (!json || !json.success) {
						throw new Error((json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error));
					}
					if (json.data && json.data.url) {
						avatar.src = json.data.url;
					}
					showAlert('success', (json.data && json.data.message) || (cfg.i18n && cfg.i18n.image));
				})
				.catch(function (err) {
					showAlert('error', (err && err.message) || (cfg.i18n && cfg.i18n.error));
				});
		});
	}

	window.GMMTeacherProfile = {
		update: function (extra) {
			var form = document.getElementById('teacher-profile-form');
			var fd = form ? new window.FormData(form) : new window.FormData();
			Object.keys(extra || {}).forEach(function (k) {
				fd.append(k, extra[k]);
			});
			return ajax((cfg.actions && cfg.actions.update) || 'gmm_teacher_profile_update', fd);
		},
		uploadImage: function (file) {
			var fd = new window.FormData();
			fd.append('profile_image', file);
			return ajax((cfg.actions && cfg.actions.image) || 'gmm_teacher_profile_image', fd);
		},
		updatePassword: function (payload) {
			var fd = new window.FormData();
			Object.keys(payload || {}).forEach(function (k) {
				fd.append(k, payload[k]);
			});
			return ajax((cfg.actions && cfg.actions.password) || 'gmm_teacher_password_update', fd);
		}
	};

	function boot() {
		wireBioCounter();
		wireAvatarUpload();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})(window, document);
