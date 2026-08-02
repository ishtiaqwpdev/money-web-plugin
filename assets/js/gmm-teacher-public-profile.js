/**
 * Gospel Music Mastery — Public teacher profile interactions
 * Favourite toggle, review submit, booking handoff. Design unchanged.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_TEACHER_PUBLIC || {};

	function ajax(action, data) {
		var fd = new window.FormData();
		fd.append('action', action);
		fd.append(cfg.nonceField || 'gmm_teacher_public_nonce', cfg.nonce || '');
		Object.keys(data || {}).forEach(function (k) {
			fd.append(k, data[k]);
		});
		return window.fetch(cfg.ajaxUrl || (window.GMM_DATA && window.GMM_DATA.ajax_url) || '', {
			method: 'POST',
			credentials: 'same-origin',
			body: fd
		}).then(function (res) {
			return res.json();
		});
	}

	function wireFavourite() {
		var btn = document.getElementById('tp-favourite-btn');
		if (!btn) {
			return;
		}
		btn.addEventListener('click', function () {
			var teacherId = cfg.teacherId || (document.querySelector('.gmm-wrapper') && document.querySelector('.gmm-wrapper').getAttribute('data-teacher-id'));
			ajax((cfg.actions && cfg.actions.favourite) || 'gmm_public_teacher_favourite', {
				teacher_id: teacherId || 0
			}).then(function (json) {
				if (!json || !json.success) {
					throw new Error((json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error));
				}
				var on = !!(json.data && json.data.favourite);
				btn.setAttribute('data-favourite', on ? '1' : '0');
				var icon = btn.querySelector('i');
				var span = btn.querySelector('span');
				if (icon) {
					icon.className = (on ? 'fas' : 'far') + ' fa-heart';
				}
				if (span) {
					span.textContent = on
						? ((cfg.i18n && cfg.i18n.favRemove) || 'Remove Favourite')
						: ((cfg.i18n && cfg.i18n.favAdd) || 'Add Favourite');
				}
			}).catch(function (err) {
				window.alert((err && err.message) || (cfg.i18n && cfg.i18n.error));
			});
		});
	}

	function wireReviewForm() {
		var form = document.getElementById('tp-review-form');
		if (!form) {
			return;
		}
		var success = document.getElementById('tp-review-success');
		var error = document.getElementById('tp-review-error');
		var errorText = document.getElementById('tp-review-error-text');

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			if (success) {
				success.hidden = true;
			}
			if (error) {
				error.hidden = true;
			}

			var classSelect = document.getElementById('tp-review-class');
			var rating = document.getElementById('tp-review-rating');
			var comment = document.getElementById('tp-review-comment');
			var opt = classSelect && classSelect.options[classSelect.selectedIndex];
			var bookingId = opt ? (opt.getAttribute('data-booking') || '0') : '0';

			ajax((cfg.actions && cfg.actions.review) || 'gmm_public_teacher_review_submit', {
				teacher_id: cfg.teacherId || 0,
				class_id: classSelect ? classSelect.value : 0,
				booking_id: bookingId,
				rating: rating ? rating.value : 5,
				comment: comment ? comment.value : ''
			}).then(function (json) {
				if (!json || !json.success) {
					throw new Error((json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error));
				}
				if (success) {
					success.hidden = false;
				}
				form.reset();
				form.hidden = true;
			}).catch(function (err) {
				if (errorText) {
					errorText.textContent = (err && err.message) || (cfg.i18n && cfg.i18n.error);
				}
				if (error) {
					error.hidden = false;
				}
			});
		});
	}

	function wireBookingForm() {
		var form = document.getElementById('stp-booking-form');
		var classSelect = document.getElementById('stp-select-class');
		var priceEl = document.getElementById('stp-booking-price');
		var successBox = document.getElementById('stp-booking-success');
		var errorBox = document.getElementById('stp-booking-error');
		var errorText = document.getElementById('stp-booking-error-text');

		function updatePrice() {
			if (!classSelect || !priceEl) {
				return;
			}
			var option = classSelect.options[classSelect.selectedIndex];
			var price = option && option.getAttribute('data-price') ? option.getAttribute('data-price') : '0';
			priceEl.textContent = '$' + price;
		}

		if (classSelect) {
			classSelect.addEventListener('change', updatePrice);
			updatePrice();
		}

		document.querySelectorAll('.stp-book-slot').forEach(function (btn) {
			btn.addEventListener('click', function () {
				var date = btn.getAttribute('data-date') || '';
				var time = btn.getAttribute('data-time') || '';
				var dateSelect = document.getElementById('stp-select-date');
				var timeSelect = document.getElementById('stp-select-time');
				if (dateSelect && date) {
					dateSelect.value = date;
				}
				if (timeSelect && time) {
					timeSelect.value = time;
				}
				var booking = document.getElementById('book-lesson');
				if (booking) {
					booking.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}
			});
		});

		if (form) {
			form.addEventListener('submit', function (e) {
				e.preventDefault();
				if (successBox) {
					successBox.hidden = true;
				}
				if (errorBox) {
					errorBox.hidden = true;
				}
				if (!classSelect || !classSelect.value) {
					if (errorText) {
						errorText.textContent = 'Please select a class.';
					}
					if (errorBox) {
						errorBox.hidden = false;
					}
					return;
				}
				if (successBox) {
					successBox.hidden = false;
				}
				var url = new window.URL(form.getAttribute('action') || cfg.bookingUrl || window.location.href, window.location.origin);
				url.searchParams.set('teacher_id', String(cfg.teacherId || ''));
				url.searchParams.set('class_id', classSelect.value);
				var dateSelect = document.getElementById('stp-select-date');
				var timeSelect = document.getElementById('stp-select-time');
				if (dateSelect && dateSelect.value) {
					url.searchParams.set('date', dateSelect.value);
				}
				if (timeSelect && timeSelect.value) {
					url.searchParams.set('time', timeSelect.value);
				}
				window.setTimeout(function () {
					window.location.href = url.toString();
				}, 400);
			});
		}
	}

	function wireVideo() {
		var trigger = document.getElementById('stp-video-trigger');
		var modalEl = document.getElementById('stp-video-modal');
		var player = document.getElementById('stp-video-player');
		if (!trigger || !modalEl || !window.bootstrap) {
			return;
		}
		var modal = new window.bootstrap.Modal(modalEl);
		trigger.addEventListener('click', function () {
			var src = trigger.getAttribute('data-video-url') || '';
			if (player && src) {
				var source = player.querySelector('source');
				if (source) {
					source.src = src;
				} else {
					player.src = src;
				}
				player.load();
			}
			modal.show();
		});
		modalEl.addEventListener('hidden.bs.modal', function () {
			if (player) {
				player.pause();
			}
		});
	}

	function boot() {
		wireFavourite();
		wireReviewForm();
		wireBookingForm();
		wireVideo();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})(window, document);
