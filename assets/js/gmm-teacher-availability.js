/**
 * Gospel Music Mastery — Teacher Availability Calendar
 * Wired to AJAX; frozen calendar markup/behavior preserved.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_TEACHER_AVAILABILITY || {};

	function ajax(action, formData) {
		formData = formData || new window.FormData();
		formData.append('action', action);
		formData.append(cfg.nonceField || 'gmm_teacher_availability_nonce', cfg.nonce || '');
		return window.fetch(cfg.ajaxUrl || (window.GMM_DATA && window.GMM_DATA.ajax_url) || '', {
			method: 'POST',
			credentials: 'same-origin',
			body: formData
		}).then(function (res) {
			return res.json();
		});
	}

	function actionName(key) {
		return (cfg.actions && cfg.actions[key]) || '';
	}

	function i18n(key, fallback) {
		return (cfg.i18n && cfg.i18n[key]) || fallback || '';
	}

	document.addEventListener('DOMContentLoaded', function () {
		var calendarEl = document.getElementById('availability-calendar');
		var monthLabel = document.getElementById('cal-month-label');
		var selectedDisplay = document.getElementById('selected-date-display');
		var slotsList = document.getElementById('added-slots-list');
		var slotsEmpty = document.getElementById('slots-empty');
		var startSelect = document.getElementById('slot-start-time');
		var endSelect = document.getElementById('slot-end-time');
		var addBtn = document.getElementById('add-time-slot');
		var saveBtn = document.getElementById('save-availability-btn');
		var errorBox = document.getElementById('availability-error');
		var errorText = document.getElementById('availability-error-text');
		var successBox = document.getElementById('availability-success');
		var successText = successBox ? successBox.querySelector('span') : null;
		var repeatToggle = document.getElementById('repeat-weekly');

		if (!calendarEl || !addBtn || !saveBtn) {
			return;
		}

		var viewDate = new Date();
		viewDate.setDate(1);
		viewDate.setHours(0, 0, 0, 0);

		var selectedDate = null;
		var slots = Array.isArray(cfg.slots) ? cfg.slots.slice() : [];
		var bookedDates = Array.isArray(cfg.bookedDates) ? cfg.bookedDates.slice() : [];

		if (repeatToggle && cfg.repeatWeekly) {
			repeatToggle.checked = true;
		}

		function showError(message) {
			if (successBox) {
				successBox.hidden = true;
			}
			if (errorText) {
				errorText.textContent = message || i18n('error');
			}
			if (errorBox) {
				errorBox.hidden = false;
				errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
			}
		}

		function showSuccess(message) {
			if (errorBox) {
				errorBox.hidden = true;
			}
			if (successText && message) {
				successText.textContent = message;
			}
			if (successBox) {
				successBox.hidden = false;
				successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
			}
		}

		function hideAlerts() {
			if (errorBox) {
				errorBox.hidden = true;
			}
			if (successBox) {
				successBox.hidden = true;
			}
		}

		function pad(n) {
			return String(n).padStart(2, '0');
		}

		function dateKey(d) {
			return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
		}

		function formatLongDate(d) {
			return d.toLocaleDateString('en-US', {
				weekday: 'long',
				month: 'long',
				day: 'numeric'
			});
		}

		function parseTimeToMinutes(label) {
			var match = String(label).match(/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i);
			if (!match) {
				return null;
			}
			var hours = parseInt(match[1], 10);
			var mins = parseInt(match[2], 10);
			var period = match[3].toUpperCase();
			if (period === 'PM' && hours !== 12) {
				hours += 12;
			}
			if (period === 'AM' && hours === 12) {
				hours = 0;
			}
			return hours * 60 + mins;
		}

		function availableKeysMap() {
			var map = {};
			slots.forEach(function (slot) {
				if (slot && slot.dateKey) {
					map[slot.dateKey] = true;
				}
			});
			return map;
		}

		function renderCalendar() {
			var year = viewDate.getFullYear();
			var month = viewDate.getMonth();
			if (monthLabel) {
				monthLabel.textContent = viewDate.toLocaleDateString('en-US', {
					month: 'long',
					year: 'numeric'
				});
			}

			var availableKeys = availableKeysMap();
			var firstDay = (new Date(year, month, 1).getDay() + 6) % 7;
			var daysInMonth = new Date(year, month + 1, 0).getDate();
			var today = new Date();
			today.setHours(0, 0, 0, 0);

			var html = '';
			var weekdays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
			weekdays.forEach(function (day) {
				html += '<div class="cal-weekday">' + day + '</div>';
			});

			for (var i = 0; i < firstDay; i++) {
				html += '<div class="cal-day is-empty"></div>';
			}

			for (var day = 1; day <= daysInMonth; day++) {
				var current = new Date(year, month, day);
				var key = dateKey(current);
				var classes = ['cal-day'];
				var status = 'available';

				if (bookedDates.indexOf(key) !== -1) {
					classes.push('is-booked');
					status = 'booked';
				} else if (availableKeys[key]) {
					classes.push('is-available');
					status = 'available';
				} else if (current < today) {
					classes.push('is-unavailable');
					status = 'unavailable';
				} else {
					classes.push('is-available');
					status = 'available';
				}

				if (selectedDate && dateKey(selectedDate) === key) {
					classes.push('is-selected');
				}
				if (dateKey(today) === key) {
					classes.push('is-today');
				}

				var disabled = status === 'booked' || status === 'unavailable';
				html += '<button type="button" class="' + classes.join(' ') + '" data-date="' + key + '"' +
					(disabled ? ' disabled' : '') +
					' aria-label="' + formatLongDate(current) + ', ' + status + '">' +
					'<span class="cal-day-num">' + day + '</span>' +
					'<span class="cal-day-status"></span>' +
					'</button>';
			}

			calendarEl.innerHTML = html;
		}

		function statusClass(status) {
			status = status || 'available';
			if (status === 'booked') {
				return 'is-booked';
			}
			if (status === 'blocked' || status === 'closed' || status === 'unavailable') {
				return 'is-unavailable';
			}
			return 'is-available';
		}

		function renderSlots() {
			if (!slotsList) {
				return;
			}
			var cards = slotsList.querySelectorAll('.slot-card');
			cards.forEach(function (card) {
				card.remove();
			});

			if (!slots.length) {
				if (slotsEmpty) {
					slotsEmpty.hidden = false;
					slotsEmpty.textContent = i18n('empty', slotsEmpty.textContent);
				}
				return;
			}

			if (slotsEmpty) {
				slotsEmpty.hidden = true;
			}

			slots.forEach(function (slot) {
				var card = document.createElement('div');
				card.className = 'slot-card';
				card.setAttribute('data-id', String(slot.id));
				card.innerHTML =
					'<div class="slot-card-info">' +
					'<strong>' + escapeHtml(slot.dayName || slot.dateLabel || slot.dateKey) + '</strong>' +
					'<span>' + escapeHtml(slot.start) + ' - ' + escapeHtml(slot.end) + '</span>' +
					'<em class="slot-status ' + statusClass(slot.status) + '">' + escapeHtml(slot.statusLabel || i18n('available', 'Available')) + '</em>' +
					'</div>' +
					'<button type="button" class="theme-btn theme-btn-outline slot-remove-btn" data-id="' + escapeAttr(slot.id) + '">' +
					'<i class="far fa-trash-alt"></i> Remove</button>';
				slotsList.appendChild(card);
			});
		}

		function escapeHtml(str) {
			return String(str == null ? '' : str)
				.replace(/&/g, '&amp;')
				.replace(/</g, '&lt;')
				.replace(/>/g, '&gt;')
				.replace(/"/g, '&quot;');
		}

		function escapeAttr(str) {
			return escapeHtml(str).replace(/'/g, '&#39;');
		}

		function upsertSlot(slot) {
			if (!slot || !slot.id) {
				return;
			}
			var found = false;
			slots = slots.map(function (s) {
				if (String(s.id) === String(slot.id)) {
					found = true;
					return slot;
				}
				return s;
			});
			if (!found) {
				slots.push(slot);
			}
			slots.sort(function (a, b) {
				if (a.dateKey === b.dateKey) {
					return String(a.start_time || a.start).localeCompare(String(b.start_time || b.start));
				}
				return String(a.dateKey).localeCompare(String(b.dateKey));
			});
			renderSlots();
			renderCalendar();
		}

		function removeSlotLocal(id) {
			slots = slots.filter(function (slot) {
				return String(slot.id) !== String(id);
			});
			renderSlots();
			renderCalendar();
		}

		var calPrev = document.getElementById('cal-prev');
		var calNext = document.getElementById('cal-next');
		if (calPrev) {
			calPrev.addEventListener('click', function () {
				viewDate.setMonth(viewDate.getMonth() - 1);
				renderCalendar();
			});
		}
		if (calNext) {
			calNext.addEventListener('click', function () {
				viewDate.setMonth(viewDate.getMonth() + 1);
				renderCalendar();
			});
		}

		calendarEl.addEventListener('click', function (e) {
			var btn = e.target.closest('.cal-day');
			if (!btn || btn.disabled || btn.classList.contains('is-empty')) {
				return;
			}
			var parts = btn.getAttribute('data-date').split('-');
			selectedDate = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
			if (selectedDisplay) {
				selectedDisplay.textContent = formatLongDate(selectedDate);
			}
			hideAlerts();
			renderCalendar();
		});

		addBtn.addEventListener('click', function () {
			hideAlerts();

			if (!selectedDate) {
				showError(i18n('needDate', 'Please select a date from the calendar.'));
				return;
			}

			var start = startSelect ? startSelect.value : '';
			var end = endSelect ? endSelect.value : '';

			if (!start || !end) {
				showError(i18n('needTimes', 'Please select both start and end times.'));
				return;
			}

			var startMins = parseTimeToMinutes(start);
			var endMins = parseTimeToMinutes(end);
			if (startMins === null || endMins === null || endMins <= startMins) {
				showError(i18n('timeOrder', 'End time must be after start time.'));
				return;
			}

			addBtn.disabled = true;
			var fd = new window.FormData();
			fd.append('date', dateKey(selectedDate));
			fd.append('start_time', start);
			fd.append('end_time', end);

			ajax(actionName('add') || 'gmm_teacher_availability_add', fd)
				.then(function (json) {
					if (!json || !json.success) {
						throw new Error((json && json.data && json.data.message) || i18n('error'));
					}
					if (json.data && json.data.slot) {
						upsertSlot(json.data.slot);
					}
					if (startSelect) {
						startSelect.value = '';
					}
					if (endSelect) {
						endSelect.value = '';
					}
					showSuccess((json.data && json.data.message) || i18n('added'));
				})
				.catch(function (err) {
					showError((err && err.message) || i18n('error'));
				})
				.then(function () {
					addBtn.disabled = false;
				});
		});

		if (slotsList) {
			slotsList.addEventListener('click', function (e) {
				var btn = e.target.closest('.slot-remove-btn');
				if (!btn) {
					return;
				}
				var id = btn.getAttribute('data-id');
				if (!id) {
					return;
				}
				if (!window.confirm(i18n('confirm', 'Remove this time slot?'))) {
					return;
				}

				var fd = new window.FormData();
				fd.append('availability_id', id);
				fd.append('confirm', '1');
				btn.disabled = true;

				ajax(actionName('delete') || 'gmm_teacher_availability_delete', fd)
					.then(function (json) {
						if (!json || !json.success) {
							throw new Error((json && json.data && json.data.message) || i18n('error'));
						}
						removeSlotLocal(id);
						showSuccess((json.data && json.data.message) || i18n('deleted'));
					})
					.catch(function (err) {
						showError((err && err.message) || i18n('error'));
						btn.disabled = false;
					});
			});
		}

		saveBtn.addEventListener('click', function () {
			hideAlerts();

			if (!slots.length) {
				showError(i18n('needSlots', 'Please add at least one available time slot before saving.'));
				return;
			}

			saveBtn.disabled = true;
			var fd = new window.FormData();
			if (repeatToggle && repeatToggle.checked) {
				fd.append('repeat_weekly', '1');
			}

			ajax(actionName('save') || 'gmm_teacher_availability_save', fd)
				.then(function (json) {
					if (!json || !json.success) {
						throw new Error((json && json.data && json.data.message) || i18n('error'));
					}
					if (json.data && Array.isArray(json.data.slots)) {
						slots = json.data.slots.slice();
						renderSlots();
						renderCalendar();
					}
					showSuccess((json.data && json.data.message) || i18n('saved'));
				})
				.catch(function (err) {
					showError((err && err.message) || i18n('error'));
				})
				.then(function () {
					saveBtn.disabled = false;
				});
		});

		// Sidebar toggle (same as frozen page).
		(function () {
			var shell = document.querySelector('.td-shell');
			var toggle = document.getElementById('td-sidebar-toggle');
			var backdrop = document.getElementById('td-sidebar-backdrop');
			if (!shell) {
				return;
			}
			function closeSidebar() {
				shell.classList.remove('is-sidebar-open');
				if (toggle) {
					toggle.setAttribute('aria-expanded', 'false');
				}
				if (backdrop) {
					backdrop.hidden = true;
				}
			}
			function openSidebar() {
				shell.classList.add('is-sidebar-open');
				if (toggle) {
					toggle.setAttribute('aria-expanded', 'true');
				}
				if (backdrop) {
					backdrop.hidden = false;
				}
			}
			if (toggle) {
				toggle.addEventListener('click', function () {
					if (shell.classList.contains('is-sidebar-open')) {
						closeSidebar();
					} else {
						openSidebar();
					}
				});
			}
			if (backdrop) {
				backdrop.addEventListener('click', closeSidebar);
			}
		})();

		renderCalendar();
		renderSlots();

		window.GMMTeacherAvailability = {
			refresh: function () {
				var fd = new window.FormData();
				return ajax(actionName('list') || 'gmm_teacher_availability_list', fd).then(function (json) {
					if (!json || !json.success) {
						return;
					}
					if (json.data && Array.isArray(json.data.slots)) {
						slots = json.data.slots.slice();
					}
					if (json.data && Array.isArray(json.data.booked_dates)) {
						bookedDates = json.data.booked_dates.slice();
					}
					renderSlots();
					renderCalendar();
				});
			}
		};
	});
})(window, document);
