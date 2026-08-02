/**
 * Gospel Music Mastery — Student booking flow
 * Teachers / classes / slots / create via AJAX. Frozen booking UI preserved.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_BOOKING_FLOW || {};
	var state = {
		teacherId: parseInt(cfg.teacherId || 0, 10) || 0,
		classId: parseInt(cfg.classId || 0, 10) || 0,
		duration: parseInt(cfg.duration || 60, 10) || 60,
		availableDates: {},
		selectedDateIso: '',
		selectedTimeIso: '',
		viewYear: 0,
		viewMonth: 0,
		pending: null
	};

	var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
	var dayNames = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

	function $(id) {
		return document.getElementById(id);
	}

	function i18n(key, fallback) {
		return (cfg.i18n && cfg.i18n[key]) || fallback || '';
	}

	function ajax(action, fields) {
		var fd = new window.FormData();
		fd.append('action', action);
		fd.append(cfg.nonceField || 'gmm_booking_flow_nonce', cfg.nonce || '');
		Object.keys(fields || {}).forEach(function (key) {
			var val = fields[key];
			if (val === undefined || val === null) {
				return;
			}
			fd.append(key, String(val));
		});

		if (state.pending && state.pending.abort) {
			try {
				state.pending.abort();
			} catch (e) {
				/* ignore */
			}
		}
		var controller = window.AbortController ? new window.AbortController() : null;
		state.pending = controller;

		return window.fetch(cfg.ajaxUrl || '', {
			method: 'POST',
			credentials: 'same-origin',
			body: fd,
			signal: controller ? controller.signal : undefined
		}).then(function (res) {
			return res.json();
		});
	}

	function showError(msg) {
		var box = $('bk-error');
		var text = $('bk-error-text');
		var success = $('bk-success');
		if (success) {
			success.hidden = true;
		}
		if (text) {
			text.textContent = msg || i18n('error', 'Something went wrong.');
		}
		if (box) {
			box.hidden = false;
			box.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}
	}

	function showSuccess(msg) {
		var box = $('bk-success');
		var text = $('bk-success-text');
		var err = $('bk-error');
		if (err) {
			err.hidden = true;
		}
		if (text) {
			text.textContent = msg || i18n('created', 'Booking created.');
		}
		if (box) {
			box.hidden = false;
			box.scrollIntoView({ behavior: 'smooth', block: 'center' });
		}
	}

	function pad(n) {
		return n < 10 ? '0' + n : String(n);
	}

	function dateKey(y, m, d) {
		return y + '-' + pad(m + 1) + '-' + pad(d);
	}

	function formatDisplayDate(y, m, d) {
		return dayNames[new Date(y, m, d).getDay()] + ', ' + monthNames[m] + ' ' + d + ', ' + y;
	}

	function getSelectedOption() {
		var select = $('bk-select-class');
		if (!select || !select.value) {
			return null;
		}
		var opt = select.options[select.selectedIndex];
		return {
			id: parseInt(opt.value, 10) || 0,
			short: opt.getAttribute('data-short') || opt.textContent || '',
			duration: opt.getAttribute('data-duration') || '60 Minutes',
			durationMins: parseInt(opt.getAttribute('data-duration-mins') || '60', 10) || 60,
			level: opt.getAttribute('data-level') || '',
			price: opt.getAttribute('data-price') || '0'
		};
	}

	function setText(id, text) {
		var el = $(id);
		if (el) {
			el.textContent = text;
		}
	}

	function updateLessonDetails() {
		var data = getSelectedOption();
		if (!data) {
			return;
		}
		state.classId = data.id;
		state.duration = data.durationMins;
		var classIdInput = $('bk-class-id');
		var durationInput = $('bk-duration');
		if (classIdInput) {
			classIdInput.value = String(data.id);
		}
		if (durationInput) {
			durationInput.value = String(data.durationMins);
		}
		setText('bk-detail-duration', data.duration);
		setText('bk-detail-level', data.level || '—');
		setText('bk-detail-price', '$' + data.price);
		setText('bk-sum-class', data.short);
		setText('bk-sum-duration', data.duration);
		setText('bk-sum-total', '$' + data.price);
		updateStepper();
		updateButtonState();
		loadMonthDates();
	}

	function updateSummaryDateTime() {
		var dateInput = $('bk-selected-date');
		var timeInput = $('bk-selected-time');
		setText('bk-sum-date', (dateInput && dateInput.value) || 'Select a date');
		setText('bk-sum-time', (timeInput && timeInput.value) || 'Select a time');
		updateStepper();
		updateButtonState();
	}

	function updateStepper() {
		var stepper = $('bk-stepper');
		if (!stepper) {
			return;
		}
		var classSelect = $('bk-select-class');
		var dateInput = $('bk-selected-date');
		var timeInput = $('bk-selected-time');
		var hasClass = !!(classSelect && classSelect.value);
		var hasSchedule = !!(dateInput && dateInput.value && timeInput && timeInput.value);
		stepper.querySelectorAll('.bk-step').forEach(function (step) {
			step.classList.remove('is-active', 'is-complete');
			var n = parseInt(step.getAttribute('data-step'), 10);
			if (n === 1) {
				step.classList.add(hasClass ? 'is-complete' : 'is-active');
			} else if (n === 2) {
				if (hasSchedule) {
					step.classList.add('is-complete');
				} else if (hasClass) {
					step.classList.add('is-active');
				}
			} else if (n === 3) {
				if (hasSchedule && hasClass) {
					step.classList.add('is-active');
				}
			}
		});
	}

	function updateButtonState() {
		var btn = $('bk-proceed-btn');
		var classSelect = $('bk-select-class');
		var dateIso = $('bk-selected-date-iso');
		var timeIso = $('bk-selected-time-iso');
		if (!btn) {
			return;
		}
		var ready = classSelect && classSelect.value && dateIso && dateIso.value && timeIso && timeIso.value && state.teacherId;
		btn.disabled = !ready;
	}

	function renderCalendar() {
		var calGrid = $('bk-cal-grid');
		var calMonth = $('bk-cal-month');
		if (!calGrid || !calMonth) {
			return;
		}
		calMonth.textContent = monthNames[state.viewMonth] + ' ' + state.viewYear;
		calGrid.innerHTML = '';

		var firstDay = new Date(state.viewYear, state.viewMonth, 1).getDay();
		var daysInMonth = new Date(state.viewYear, state.viewMonth + 1, 0).getDate();
		var i;
		for (i = 0; i < firstDay; i++) {
			var empty = document.createElement('span');
			empty.className = 'bk-cal-day is-empty';
			empty.setAttribute('aria-hidden', 'true');
			calGrid.appendChild(empty);
		}

		for (var day = 1; day <= daysInMonth; day++) {
			var iso = dateKey(state.viewYear, state.viewMonth, day);
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'bk-cal-day';
			btn.textContent = String(day);
			btn.setAttribute('data-key', iso);

			if (state.availableDates[iso]) {
				btn.classList.add('is-available');
				btn.setAttribute('aria-label', 'Available ' + formatDisplayDate(state.viewYear, state.viewMonth, day));
				if (state.selectedDateIso === iso) {
					btn.classList.add('is-selected');
				}
				btn.addEventListener('click', (function (y, m, d, k) {
					return function () {
						state.selectedDateIso = k;
						state.selectedTimeIso = '';
						var dateInput = $('bk-selected-date');
						var dateIso = $('bk-selected-date-iso');
						var timeInput = $('bk-selected-time');
						var timeIso = $('bk-selected-time-iso');
						if (dateInput) {
							dateInput.value = formatDisplayDate(y, m, d);
						}
						if (dateIso) {
							dateIso.value = k;
						}
						if (timeInput) {
							timeInput.value = '';
						}
						if (timeIso) {
							timeIso.value = '';
						}
						renderCalendar();
						updateSummaryDateTime();
						loadSlotsForDate(k);
					};
				})(state.viewYear, state.viewMonth, day, iso));
			} else {
				btn.classList.add('is-disabled');
				btn.disabled = true;
				btn.setAttribute('aria-label', 'Unavailable');
			}
			calGrid.appendChild(btn);
		}
	}

	function renderSlots(slots) {
		var wrap = $('bk-time-slots');
		if (!wrap) {
			return;
		}
		wrap.innerHTML = '';
		if (!slots || !slots.length) {
			var hint = document.createElement('p');
			hint.className = 'bk-cal-hint';
			hint.id = 'bk-slots-hint';
			hint.textContent = i18n('noSlots', 'No time slots available for this date.');
			wrap.appendChild(hint);
			return;
		}

		slots.forEach(function (slot) {
			var btn = document.createElement('button');
			btn.type = 'button';
			btn.className = 'bk-time-slot';
			btn.setAttribute('data-time', slot.time_label || slot.display || '');
			btn.setAttribute('data-time-iso', slot.time || '');
			btn.textContent = slot.time_label || slot.display || slot.time || '';
			btn.addEventListener('click', function () {
				wrap.querySelectorAll('.bk-time-slot').forEach(function (s) {
					s.classList.remove('is-selected');
				});
				btn.classList.add('is-selected');
				state.selectedTimeIso = slot.time || '';
				var timeInput = $('bk-selected-time');
				var timeIso = $('bk-selected-time-iso');
				if (timeInput) {
					timeInput.value = slot.time_label || slot.display || '';
				}
				if (timeIso) {
					timeIso.value = slot.time || '';
				}
				updateSummaryDateTime();
			});
			wrap.appendChild(btn);
		});
	}

	function loadMonthDates() {
		if (!state.teacherId) {
			state.availableDates = {};
			renderCalendar();
			return;
		}
		ajax(cfg.actions && cfg.actions.slots || 'gmm_booking_flow_slots', {
			teacher_id: state.teacherId,
			year: state.viewYear,
			month: state.viewMonth + 1,
			duration: state.duration
		}).then(function (json) {
			state.availableDates = {};
			if (json && json.success && json.data && json.data.dates) {
				(json.data.dates || []).forEach(function (d) {
					state.availableDates[d] = true;
				});
			}
			renderCalendar();
			if (state.selectedDateIso && state.availableDates[state.selectedDateIso]) {
				loadSlotsForDate(state.selectedDateIso);
			} else {
				renderSlots([]);
			}
		}).catch(function () {
			state.availableDates = {};
			renderCalendar();
		});
	}

	function loadSlotsForDate(iso) {
		if (!state.teacherId || !iso) {
			renderSlots([]);
			return;
		}
		ajax(cfg.actions && cfg.actions.slots || 'gmm_booking_flow_slots', {
			teacher_id: state.teacherId,
			date: iso,
			year: state.viewYear,
			month: state.viewMonth + 1,
			duration: state.duration
		}).then(function (json) {
			if (json && json.success && json.data) {
				renderSlots(json.data.slots || []);
			} else {
				renderSlots([]);
			}
		}).catch(function () {
			renderSlots([]);
		});
	}

	function applyTeacher(teacher) {
		if (!teacher) {
			return;
		}
		state.teacherId = parseInt(teacher.id, 10) || 0;
		var tid = $('bk-teacher-id');
		if (tid) {
			tid.value = String(state.teacherId);
		}
		setText('bk-teacher-name', teacher.name || '');
		setText('bk-teacher-role', teacher.role || '');
		setText('bk-sum-teacher', teacher.name || '');
		var avatar = $('bk-teacher-avatar');
		if (avatar && teacher.image_url) {
			avatar.src = teacher.image_url;
			avatar.alt = teacher.name || '';
		}
		var link = $('bk-teacher-profile-link');
		var back = $('bk-back-link');
		if (link && teacher.profile_url) {
			link.href = teacher.profile_url;
		}
		if (back && teacher.profile_url) {
			back.href = teacher.profile_url;
		}
	}

	function fillClasses(classes, preferredId) {
		var select = $('bk-select-class');
		if (!select) {
			return;
		}
		select.innerHTML = '<option value="">Choose a class</option>';
		(classes || []).forEach(function (c) {
			var opt = document.createElement('option');
			opt.value = String(c.id);
			opt.textContent = c.title || '';
			opt.setAttribute('data-short', c.short || c.title || '');
			opt.setAttribute('data-duration', c.duration_label || ((c.duration || 60) + ' Minutes'));
			opt.setAttribute('data-duration-mins', String(c.duration || 60));
			opt.setAttribute('data-level', c.difficulty || '');
			opt.setAttribute('data-price', String(c.price != null ? c.price : 0));
			if (preferredId && parseInt(preferredId, 10) === parseInt(c.id, 10)) {
				opt.selected = true;
			}
			select.appendChild(opt);
		});
		if (!select.value && select.options.length > 1) {
			select.selectedIndex = 1;
		}
		updateLessonDetails();
	}

	function loadClassesForTeacher(teacherId, preferredClassId) {
		if (!teacherId) {
			return;
		}
		ajax(cfg.actions && cfg.actions.classes || 'gmm_booking_flow_classes', {
			teacher_id: teacherId
		}).then(function (json) {
			if (!json || !json.success) {
				showError((json && json.data && json.data.message) || i18n('error'));
				return;
			}
			applyTeacher(json.data.teacher || { id: teacherId });
			fillClasses(json.data.classes || [], preferredClassId || state.classId);
		}).catch(function () {
			showError(i18n('error'));
		});
	}

	function submitBooking(e) {
		e.preventDefault();
		var classSelect = $('bk-select-class');
		var dateIso = $('bk-selected-date-iso');
		var timeIso = $('bk-selected-time-iso');
		var notes = $('bk-notes');
		var btn = $('bk-proceed-btn');

		if (!state.teacherId || !classSelect || !classSelect.value || !dateIso || !dateIso.value || !timeIso || !timeIso.value) {
			showError(i18n('selectClass', 'Please select a class, date, and time.'));
			return;
		}

		if (btn) {
			btn.disabled = true;
			btn.innerHTML = '<i class="far fa-spinner fa-spin"></i> Processing…';
		}

		ajax(cfg.actions && cfg.actions.create || 'gmm_booking_flow_create', {
			teacher_id: state.teacherId,
			class_id: classSelect.value,
			booking_date: dateIso.value,
			booking_time: timeIso.value,
			duration: state.duration,
			notes: notes ? notes.value : ''
		}).then(function (json) {
			if (!json || !json.success) {
				showError((json && json.data && json.data.message) || i18n('error'));
				if (btn) {
					btn.disabled = false;
					btn.innerHTML = '<i class="far fa-credit-card"></i> Proceed To Payment';
				}
				return;
			}

			var b = json.data.booking || {};
			var msg = 'Booking #' + (json.data.booking_id || '') + ' created';
			if (b.teacher) {
				msg += ' — ' + b.teacher;
			}
			if (b.class) {
				msg += ' · ' + b.class;
			}
			if (b.date_label) {
				msg += ' · ' + b.date_label;
			}
			if (b.time_label) {
				msg += ' at ' + b.time_label;
			}
			msg += ' · Status: ' + (b.status || 'pending') + ' (payment pending).';
			showSuccess(msg);

			var redirect = json.data.redirect || (cfg.urls && cfg.urls.bookings) || '';
			window.setTimeout(function () {
				if (redirect) {
					window.location.href = redirect;
				} else if (btn) {
					btn.disabled = false;
					btn.innerHTML = '<i class="far fa-credit-card"></i> Proceed To Payment';
				}
			}, 1200);
		}).catch(function () {
			showError(i18n('error'));
			if (btn) {
				btn.disabled = false;
				btn.innerHTML = '<i class="far fa-credit-card"></i> Proceed To Payment';
			}
		});
	}

	function init() {
		var now = new Date();
		state.viewYear = now.getFullYear();
		state.viewMonth = now.getMonth();

		var root = $('gmm-booking-flow');
		if (root) {
			var tid = parseInt(root.getAttribute('data-teacher-id') || '0', 10);
			var cid = parseInt(root.getAttribute('data-class-id') || '0', 10);
			if (tid) {
				state.teacherId = tid;
			}
			if (cid) {
				state.classId = cid;
			}
		}

		var teacherSelect = $('bk-select-teacher');
		if (teacherSelect) {
			teacherSelect.addEventListener('change', function () {
				var id = parseInt(teacherSelect.value || '0', 10);
				state.teacherId = id;
				state.selectedDateIso = '';
				state.selectedTimeIso = '';
				loadClassesForTeacher(id, 0);
			});
		}

		var classSelect = $('bk-select-class');
		if (classSelect) {
			classSelect.addEventListener('change', function () {
				updateLessonDetails();
			});
		}

		var calPrev = $('bk-cal-prev');
		var calNext = $('bk-cal-next');
		if (calPrev) {
			calPrev.addEventListener('click', function () {
				state.viewMonth -= 1;
				if (state.viewMonth < 0) {
					state.viewMonth = 11;
					state.viewYear -= 1;
				}
				loadMonthDates();
			});
		}
		if (calNext) {
			calNext.addEventListener('click', function () {
				state.viewMonth += 1;
				if (state.viewMonth > 11) {
					state.viewMonth = 0;
					state.viewYear += 1;
				}
				loadMonthDates();
			});
		}

		var form = $('bk-booking-form');
		if (form) {
			form.addEventListener('submit', submitBooking);
		}

		if (state.teacherId) {
			if (classSelect && classSelect.options.length > 1) {
				updateLessonDetails();
			} else {
				loadClassesForTeacher(state.teacherId, state.classId);
			}
		} else {
			renderCalendar();
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(window, document);
