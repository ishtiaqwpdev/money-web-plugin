/**
 * Gospel Music Mastery — Admin bookings management
 * Filters, AJAX status/payment updates, detail modal, calendar from rows.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_ADMIN_BOOKINGS || {};
	var ajaxUrl = cfg.ajaxUrl || (window.GMM_DATA && window.GMM_DATA.ajax_url) || '';
	var nonce = cfg.nonce || (window.GMM_DATA && window.GMM_DATA.nonce) || '';
	var i18n = cfg.i18n || {};

	var STATUS_MAP = {
		pending: { label: 'Pending', className: 'is-pending' },
		confirmed: { label: 'Confirmed', className: 'is-confirmed' },
		completed: { label: 'Completed', className: 'is-completed' },
		cancelled: { label: 'Cancelled', className: 'is-cancelled' }
	};

	var PAYMENT_MAP = {
		paid: { label: 'Paid', className: 'is-confirmed' },
		pending: { label: 'Pending', className: 'is-pending' },
		failed: { label: 'Failed', className: 'is-cancelled' },
		refunded: { label: 'Refunded', className: 'is-inactive' }
	};

	function showToast(message, isError) {
		var toast = document.getElementById('ab-toast');
		var toastText = document.getElementById('ab-toast-text');
		if (!toast) {
			return;
		}
		if (toastText) {
			toastText.textContent = message;
		}
		toast.classList.toggle('gospel-alert-error', !!isError);
		toast.classList.toggle('gospel-alert-success', !isError);
		var icon = toast.querySelector('i');
		if (icon) {
			icon.className = isError ? 'far fa-circle-exclamation' : 'far fa-circle-check';
		}
		toast.hidden = false;
		window.setTimeout(function () {
			toast.hidden = true;
		}, 3200);
	}

	function postAction(action, data) {
		var body = new window.FormData();
		body.append('action', action);
		body.append('nonce', nonce);
		Object.keys(data || {}).forEach(function (key) {
			var val = data[key];
			if (val !== undefined && val !== null) {
				body.append(key, val);
			}
		});
		return window
			.fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
			.then(function (res) {
				return res.json();
			});
	}

	function getBookingId(row) {
		if (!row) {
			return 0;
		}
		var raw = row.getAttribute('data-booking-id') || row.getAttribute('data-id') || '';
		var digits = String(raw).replace(/\D+/g, '');
		return parseInt(digits, 10) || 0;
	}

	function setRowStatus(row, status) {
		var meta = STATUS_MAP[status];
		if (!meta || !row) {
			return;
		}
		row.setAttribute('data-status', status);
		var badge = row.querySelector('.ab-status');
		if (badge) {
			badge.className = 'sb-badge ab-status ' + meta.className;
			badge.textContent = meta.label;
		}
	}

	function setRowPayment(row, payment) {
		var meta = PAYMENT_MAP[payment];
		if (!meta || !row) {
			return;
		}
		row.setAttribute('data-payment', payment);
		var badge = row.querySelector('.ab-payment');
		if (badge) {
			badge.className = 'sb-badge ab-payment ' + meta.className;
			badge.textContent = meta.label;
		}
	}

	var activeRow = null;
	var modalEl = document.getElementById('ab-booking-modal');
	var modal = modalEl && window.bootstrap ? new window.bootstrap.Modal(modalEl) : null;

	function setText(id, value) {
		var el = document.getElementById(id);
		if (el) {
			el.textContent = value || '';
		}
	}

	function fillModalFromRow(row) {
		activeRow = row;
		var sImg = document.getElementById('ab-modal-student-img');
		var tImg = document.getElementById('ab-modal-teacher-img');
		if (sImg) {
			sImg.src = row.getAttribute('data-student-img') || '';
			sImg.alt = row.getAttribute('data-student') || 'Student';
		}
		if (tImg) {
			tImg.src = row.getAttribute('data-teacher-img') || '';
			tImg.alt = row.getAttribute('data-teacher') || 'Teacher';
		}
		setText('ab-modal-student', row.getAttribute('data-student'));
		setText('ab-modal-email', row.getAttribute('data-email'));
		setText('ab-modal-teacher', row.getAttribute('data-teacher'));
		setText('ab-modal-phone', row.getAttribute('data-phone') || row.getAttribute('data-teacher-phone'));
		setText('ab-modal-id', row.getAttribute('data-id'));
		setText('ab-modal-class', row.getAttribute('data-class'));
		setText('ab-modal-date', row.getAttribute('data-date'));
		setText('ab-modal-time', row.getAttribute('data-time'));
		setText('ab-modal-duration', row.getAttribute('data-duration'));
		setText('ab-modal-amount', row.getAttribute('data-amount'));
		setText('ab-modal-notes', row.getAttribute('data-notes') || '—');

		var pay = row.getAttribute('data-payment');
		var st = row.getAttribute('data-status');
		var payMeta = PAYMENT_MAP[pay];
		var stMeta = STATUS_MAP[st];
		var payEl = document.getElementById('ab-modal-payment');
		var stEl = document.getElementById('ab-modal-status');
		if (payEl && payMeta) {
			payEl.className = 'sb-badge ' + payMeta.className;
			payEl.textContent = payMeta.label;
		}
		if (stEl && stMeta) {
			stEl.className = 'sb-badge ' + stMeta.className;
			stEl.textContent = stMeta.label;
		}
	}

	function enrichModal(profile) {
		if (!profile) {
			return;
		}
		var booking = profile.booking || {};
		var student = profile.student || {};
		var teacher = profile.teacher || {};
		var klass = profile.class || {};
		var payment = profile.payment || {};
		var formatted = profile.formatted || {};

		if (student.first_name || student.last_name) {
			setText('ab-modal-student', ((student.first_name || '') + ' ' + (student.last_name || '')).trim());
		}
		if (student.email) {
			setText('ab-modal-email', student.email);
		}
		if (teacher.first_name || teacher.last_name) {
			setText('ab-modal-teacher', ((teacher.first_name || '') + ' ' + (teacher.last_name || '')).trim());
		}
		if (teacher.phone) {
			setText('ab-modal-phone', teacher.phone);
		}
		if (klass.title) {
			setText('ab-modal-class', klass.title);
		}
		if (booking.id) {
			setText('ab-modal-id', 'BK-' + booking.id);
		}
		if (formatted.date) {
			setText('ab-modal-date', formatted.date);
		}
		if (formatted.time) {
			setText('ab-modal-time', formatted.time);
		}
		if (formatted.duration_label) {
			setText('ab-modal-duration', formatted.duration_label);
		}
		if (formatted.amount_label) {
			setText('ab-modal-amount', formatted.amount_label);
		}
		if (booking.notes) {
			setText('ab-modal-notes', booking.notes);
		}

		var timeline = profile.timeline || [];
		if (timeline.length && !booking.notes) {
			var lines = timeline.map(function (item) {
				return (item.label || '') + (item.time ? ' — ' + item.time : '');
			});
			setText('ab-modal-notes', lines.join('\n'));
		}

		if (payment.payment_status && activeRow) {
			var pui = payment.payment_status === 'completed' ? 'paid' : payment.payment_status;
			setRowPayment(activeRow, pui);
			fillModalFromRow(activeRow);
		}
	}

	function openProfile(row) {
		fillModalFromRow(row);
		if (modal) {
			modal.show();
		}
		var id = getBookingId(row);
		if (!id) {
			return;
		}
		postAction('gmm_get_booking_profile', { booking_id: id })
			.then(function (json) {
				if (json && json.success && json.data && json.data.profile) {
					enrichModal(json.data.profile);
				}
			})
			.catch(function () {
				/* keep row data */
			});
	}

	function runStatus(row, action, successKey) {
		var id = getBookingId(row);
		if (!id) {
			return;
		}
		if (action === 'gmm_cancel_booking') {
			if (!window.confirm(i18n.confirmCancel || 'Cancel this booking?')) {
				return;
			}
		}
		postAction(action, { booking_id: id, confirm: 1 })
			.then(function (json) {
				if (json && json.success) {
					var map = {
						gmm_confirm_booking: 'confirmed',
						gmm_complete_booking: 'completed',
						gmm_cancel_booking: 'cancelled'
					};
					if (map[action]) {
						setRowStatus(row, map[action]);
						if (activeRow === row) {
							fillModalFromRow(row);
						}
					}
					showToast((json.data && json.data.message) || i18n[successKey] || 'Updated.');
					window.setTimeout(function () {
						window.location.reload();
					}, 600);
				} else {
					showToast((json && json.data && json.data.message) || i18n.error, true);
				}
			})
			.catch(function () {
				showToast(i18n.error || 'Failed.', true);
			});
	}

	function runPayment(row, status) {
		var id = getBookingId(row);
		if (!id) {
			return;
		}
		if (status === 'refunded' && !window.confirm(i18n.confirmRefund || 'Mark payment as refunded?')) {
			return;
		}
		postAction('gmm_admin_update_booking_payment', { booking_id: id, status: status })
			.then(function (json) {
				if (json && json.success) {
					setRowPayment(row, status === 'completed' ? 'paid' : status);
					if (activeRow === row) {
						fillModalFromRow(row);
					}
					showToast((json.data && json.data.message) || i18n.paymentUpdated);
					window.setTimeout(function () {
						window.location.reload();
					}, 600);
				} else {
					showToast((json && json.data && json.data.message) || i18n.error, true);
				}
			})
			.catch(function () {
				showToast(i18n.error || 'Failed.', true);
			});
	}

	function editPayment(row) {
		var current = row.getAttribute('data-payment') || 'pending';
		var next = window.prompt('Payment status (pending / paid / failed / refunded)', current);
		if (next === null) {
			return;
		}
		next = String(next).toLowerCase().trim();
		if (['pending', 'paid', 'failed', 'refunded', 'completed'].indexOf(next) === -1) {
			showToast(i18n.error || 'Invalid status.', true);
			return;
		}
		runPayment(row, next);
	}

	function bindRow(row) {
		var viewBtn = row.querySelector('.ab-view-btn');
		var studentBtn = row.querySelector('.ab-student-btn');
		var teacherBtn = row.querySelector('.ab-teacher-btn');
		var completeBtn = row.querySelector('.ab-complete-btn');
		var cancelBtn = row.querySelector('.ab-cancel-btn');
		var refundBtn = row.querySelector('.ab-refund-btn');
		var payBadge = row.querySelector('.ab-payment');

		if (viewBtn) {
			viewBtn.addEventListener('click', function () {
				openProfile(row);
			});
		}
		if (studentBtn) {
			studentBtn.addEventListener('click', function () {
				var url = row.getAttribute('data-student-url') || '';
				if (url) {
					window.location.href = url;
				} else {
					openProfile(row);
				}
			});
		}
		if (teacherBtn) {
			teacherBtn.addEventListener('click', function () {
				var url = row.getAttribute('data-teacher-url') || '';
				if (url) {
					window.location.href = url;
				} else {
					openProfile(row);
				}
			});
		}
		if (completeBtn) {
			completeBtn.addEventListener('click', function () {
				runStatus(row, 'gmm_complete_booking', 'completed');
			});
		}
		if (cancelBtn) {
			cancelBtn.addEventListener('click', function () {
				runStatus(row, 'gmm_cancel_booking', 'cancelled');
			});
		}
		if (refundBtn) {
			refundBtn.addEventListener('click', function () {
				runPayment(row, 'refunded');
			});
		}
		if (payBadge) {
			payBadge.style.cursor = 'pointer';
			payBadge.title = 'Click to update payment status';
			payBadge.addEventListener('click', function (e) {
				e.preventDefault();
				editPayment(row);
			});
		}
	}

	function bindAllRows() {
		Array.prototype.forEach.call(document.querySelectorAll('.ab-row'), bindRow);
	}

	function bindModalActions() {
		var confirmBtn = document.getElementById('ab-modal-confirm');
		var completeBtn = document.getElementById('ab-modal-complete');
		var cancelBtn = document.getElementById('ab-modal-cancel');
		if (confirmBtn) {
			confirmBtn.addEventListener('click', function () {
				if (activeRow) {
					runStatus(activeRow, 'gmm_confirm_booking', 'confirmed');
				}
			});
		}
		if (completeBtn) {
			completeBtn.addEventListener('click', function () {
				if (activeRow) {
					runStatus(activeRow, 'gmm_complete_booking', 'completed');
				}
			});
		}
		if (cancelBtn) {
			cancelBtn.addEventListener('click', function () {
				if (activeRow) {
					runStatus(activeRow, 'gmm_cancel_booking', 'cancelled');
				}
			});
		}
	}

	function collectCalendarBookings() {
		var items = [];
		Array.prototype.forEach.call(document.querySelectorAll('.ab-row'), function (row) {
			var dateRaw = row.getAttribute('data-date-raw') || '';
			if (!dateRaw) {
				return;
			}
			items.push({
				date: dateRaw,
				status: row.getAttribute('data-status') || 'pending',
				student: row.getAttribute('data-student') || '',
				className: row.getAttribute('data-class') || '',
				time: row.getAttribute('data-time') || ''
			});
		});
		return items;
	}

	function renderCalendar() {
		var daysEl = document.getElementById('ab-cal-days');
		var selectedEl = document.getElementById('ab-cal-selected');
		if (!daysEl) {
			return;
		}
		var bookings = collectCalendarBookings();
		var now = new Date();
		var year = now.getFullYear();
		var month = now.getMonth();
		var first = new Date(year, month, 1);
		var startPad = first.getDay();
		var daysInMonth = new Date(year, month + 1, 0).getDate();
		var byDate = {};
		bookings.forEach(function (b) {
			if (!byDate[b.date]) {
				byDate[b.date] = [];
			}
			byDate[b.date].push(b);
		});

		daysEl.innerHTML = '';
		var i;
		for (i = 0; i < startPad; i++) {
			var empty = document.createElement('span');
			empty.className = 'ab-cal-day is-empty';
			daysEl.appendChild(empty);
		}
		for (i = 1; i <= daysInMonth; i++) {
			var mm = month + 1;
			var key =
				year +
				'-' +
				(mm < 10 ? '0' : '') +
				mm +
				'-' +
				(i < 10 ? '0' : '') +
				i;
			var day = document.createElement('button');
			day.type = 'button';
			day.className = 'ab-cal-day';
			day.textContent = String(i);
			var list = byDate[key] || [];
			if (list.length) {
				var hasPending = list.some(function (x) {
					return x.status === 'pending';
				});
				day.classList.add(hasPending ? 'is-pending' : 'is-confirmed');
			}
			day.addEventListener('click', function (listCopy, keyCopy) {
				return function () {
					Array.prototype.forEach.call(daysEl.querySelectorAll('.ab-cal-day'), function (el) {
						el.classList.remove('is-selected');
					});
					this.classList.add('is-selected');
					if (!selectedEl) {
						return;
					}
					if (!listCopy.length) {
						selectedEl.innerHTML = '<strong>Select a date</strong><p>No lessons on this day.</p>';
						return;
					}
					var html = '<strong>' + keyCopy + '</strong><ul>';
					listCopy.forEach(function (item) {
						html += '<li>' + item.time + ' — ' + item.student + ' / ' + item.className + '</li>';
					});
					html += '</ul>';
					selectedEl.innerHTML = html;
				};
			}(list, key));
			daysEl.appendChild(day);
		}
	}

	bindAllRows();
	bindModalActions();
	renderCalendar();
})(window, document);
