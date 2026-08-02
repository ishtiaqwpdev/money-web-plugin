/**
 * Gospel Music Mastery — Student profile interactions
 * AJAX: profile update, image upload/remove, password, preferences.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_STUDENT_PROFILE || {};

	function ajax(action, formData) {
		formData = formData || new window.FormData();
		formData.append('action', action);
		formData.append(cfg.nonceField || 'gmm_student_profile_nonce', cfg.nonce || '');
		return window.fetch(cfg.ajaxUrl || (window.GMM_DATA && window.GMM_DATA.ajax_url) || '', {
			method: 'POST',
			credentials: 'same-origin',
			body: formData
		}).then(function (res) {
			return res.json();
		});
	}

	function showAlert(type, message, errorId, successId) {
		var err = document.getElementById(errorId || 'profile-error');
		var ok = document.getElementById(successId || 'profile-success');
		var errText = document.getElementById('profile-error-text') || (err && err.querySelector('span'));
		var okText = ok && ok.querySelector('span');
		var ssErr = document.getElementById('ss-error');
		var ssOk = document.getElementById('ss-success');
		var ssErrText = document.getElementById('ss-error-text');
		var ssOkText = document.getElementById('ss-success-text');

		if (err) {
			err.hidden = type !== 'error';
			if (type === 'error' && errText && message) {
				errText.textContent = message;
			}
		}
		if (ok) {
			ok.hidden = type !== 'success';
			if (type === 'success' && okText && message) {
				okText.textContent = message;
			}
			if (type === 'success') {
				window.setTimeout(function () {
					ok.hidden = true;
				}, 4000);
			}
		}
		if (ssErr) {
			ssErr.hidden = type !== 'error';
			if (type === 'error' && ssErrText && message) {
				ssErrText.textContent = message;
			}
		}
		if (ssOk) {
			ssOk.hidden = type !== 'success';
			if (type === 'success' && ssOkText && message) {
				ssOkText.textContent = message;
			}
			if (type === 'success') {
				window.setTimeout(function () {
					ssOk.hidden = true;
				}, 4000);
			}
		}
	}

	function setImages(url) {
		['sd-header-avatar', 'sd-profile-photo-img', 'ss-header-avatar', 'ss-profile-photo-img'].forEach(function (id) {
			var el = document.getElementById(id);
			if (el && url) {
				el.src = url;
			}
		});
	}

	function wireBioCounter() {
		var bio = document.getElementById('about-me');
		var counter = document.getElementById('about-counter');
		if (!bio || !counter) {
			return;
		}
		function update() {
			counter.textContent = (bio.value || '').length + ' / 500';
		}
		bio.addEventListener('input', update);
		update();
	}

	function wireImageUpload(inputId, removeId) {
		var file = document.getElementById(inputId);
		if (file) {
			file.addEventListener('change', function () {
				if (!file.files || !file.files[0]) {
					return;
				}
				var fd = new window.FormData();
				fd.append('profile_photo', file.files[0]);
				ajax((cfg.actions && cfg.actions.image) || 'gmm_student_profile_image', fd)
					.then(function (json) {
						if (!json || !json.success) {
							throw new Error((json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error));
						}
						if (json.data && json.data.url) {
							setImages(json.data.url);
						}
						showAlert('success', (json.data && json.data.message) || (cfg.i18n && cfg.i18n.image));
					})
					.catch(function (err) {
						showAlert('error', (err && err.message) || (cfg.i18n && cfg.i18n.error));
					});
			});
		}

		var removeBtn = document.getElementById(removeId);
		if (removeBtn) {
			removeBtn.addEventListener('click', function () {
				ajax((cfg.actions && cfg.actions.imageRemove) || 'gmm_student_profile_image_remove', new window.FormData())
					.then(function (json) {
						if (!json || !json.success) {
							throw new Error((json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error));
						}
						if (json.data && json.data.url) {
							setImages(json.data.url);
						}
						showAlert('success', (json.data && json.data.message) || 'Profile photo removed.');
					})
					.catch(function (err) {
						showAlert('error', (err && err.message) || (cfg.i18n && cfg.i18n.error));
					});
			});
		}
	}

	function wireProfileForm(formId) {
		var form = document.getElementById(formId);
		if (!form) {
			return;
		}
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var fd = new window.FormData(form);
			ajax((cfg.actions && cfg.actions.update) || 'gmm_student_profile_update', fd)
				.then(function (json) {
					if (!json || !json.success) {
						throw new Error((json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error));
					}
					showAlert('success', (json.data && json.data.message) || (cfg.i18n && cfg.i18n.saved));
					if (json.data && json.data.profile) {
						var name = json.data.profile.display_name || '';
						var h2 = document.querySelector('.sd-profile-meta h2');
						var h4 = document.querySelector('.sd-photo-meta h4');
						if (h2 && name) {
							h2.textContent = name;
						}
						if (h4 && name) {
							h4.textContent = name;
						}
						if (json.data.profile.image_url) {
							setImages(json.data.profile.image_url);
						}
					}
				})
				.catch(function (err) {
					showAlert('error', (err && err.message) || (cfg.i18n && cfg.i18n.error));
				});
		});
	}

	function wirePasswordForm(formId) {
		var form = document.getElementById(formId);
		if (!form) {
			return;
		}
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var fd = new window.FormData(form);
			ajax((cfg.actions && cfg.actions.password) || 'gmm_student_password_update', fd)
				.then(function (json) {
					if (!json || !json.success) {
						throw new Error((json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error));
					}
					form.reset();
					showAlert('success', (json.data && json.data.message) || (cfg.i18n && cfg.i18n.password));
				})
				.catch(function (err) {
					showAlert('error', (err && err.message) || (cfg.i18n && cfg.i18n.error));
				});
		});
	}

	function wirePreferencesForm() {
		var form = document.getElementById('ss-notifications-form');
		if (!form) {
			return;
		}
		form.addEventListener('submit', function (e) {
			e.preventDefault();
			var fd = new window.FormData();
			['email_notifications', 'lesson_reminders', 'booking_updates', 'teacher_messages', 'payment_alerts'].forEach(function (name) {
				var input = form.querySelector('[name="' + name + '"]');
				if (input && input.checked) {
					fd.append(name, '1');
				}
			});
			ajax((cfg.actions && cfg.actions.preferences) || 'gmm_student_preferences_update', fd)
				.then(function (json) {
					if (!json || !json.success) {
						throw new Error((json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error));
					}
					showAlert('success', (json.data && json.data.message) || (cfg.i18n && cfg.i18n.prefs));
				})
				.catch(function (err) {
					showAlert('error', (err && err.message) || (cfg.i18n && cfg.i18n.error));
				});
		});
	}

	window.GMMStudentProfile = {
		update: function (extra) {
			var form = document.getElementById('student-profile-form') || document.getElementById('ss-profile-form');
			var fd = form ? new window.FormData(form) : new window.FormData();
			Object.keys(extra || {}).forEach(function (k) {
				fd.append(k, extra[k]);
			});
			return ajax((cfg.actions && cfg.actions.update) || 'gmm_student_profile_update', fd);
		},
		uploadImage: function (file) {
			var fd = new window.FormData();
			fd.append('profile_photo', file);
			return ajax((cfg.actions && cfg.actions.image) || 'gmm_student_profile_image', fd);
		},
		updatePassword: function (payload) {
			var fd = new window.FormData();
			Object.keys(payload || {}).forEach(function (k) {
				fd.append(k, payload[k]);
			});
			return ajax((cfg.actions && cfg.actions.password) || 'gmm_student_password_update', fd);
		}
	};

	function boot() {
		wireBioCounter();
		wireImageUpload('sd-profile-photo', 'sd-remove-photo');
		wireImageUpload('ss-profile-photo', 'ss-remove-photo');
		wireProfileForm('student-profile-form');
		wireProfileForm('ss-profile-form');
		wirePasswordForm('gmm-student-password-form');
		wirePasswordForm('ss-password-form');
		wirePreferencesForm();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})(window, document);
