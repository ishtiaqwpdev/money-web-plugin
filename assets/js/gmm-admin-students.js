/**
 * Gospel Music Mastery — Admin students management
 * Search/filter form, AJAX status/edit/delete/bulk, profile modal.
 * Design markup unchanged.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_ADMIN_STUDENTS || {};
	var ajaxUrl = cfg.ajaxUrl || (window.GMM_DATA && window.GMM_DATA.ajax_url) || '';
	var nonce = cfg.nonce || (window.GMM_DATA && window.GMM_DATA.nonce) || '';
	var i18n = cfg.i18n || {};

	var STATUS_MAP = {
		active: { label: 'Active', className: 'is-confirmed' },
		inactive: { label: 'Inactive', className: 'is-inactive' },
		suspended: { label: 'Suspended', className: 'is-suspended' },
		pending: { label: 'Pending', className: 'is-pending' }
	};

	function showToast(message, isError) {
		var toast = document.getElementById('as-toast');
		var toastText = document.getElementById('as-toast-text');
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
			if (Array.isArray(val)) {
				val.forEach(function (item) {
					body.append(key + '[]', item);
				});
			} else if (val !== undefined && val !== null) {
				body.append(key, val);
			}
		});
		return window
			.fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
			.then(function (res) {
				return res.json();
			});
	}

	function setRowStatus(row, status) {
		var meta = STATUS_MAP[status];
		if (!meta || !row) {
			return;
		}
		row.setAttribute('data-status', status);
		var badge = row.querySelector('.as-status');
		if (badge) {
			badge.className = 'sb-badge as-status ' + meta.className;
			badge.textContent = meta.label;
		}
	}

	function getStudentId(row) {
		return parseInt(row.getAttribute('data-student-id') || '0', 10) || 0;
	}

	var activeRow = null;
	var modalEl = document.getElementById('as-student-modal');
	var modal = modalEl && window.bootstrap ? new window.bootstrap.Modal(modalEl) : null;

	function setText(id, value) {
		var el = document.getElementById(id);
		if (el) {
			el.textContent = value || '';
		}
	}

	function fillModalFromRow(row) {
		activeRow = row;
		var img = document.getElementById('as-modal-image');
		if (img) {
			img.src = row.getAttribute('data-image') || '';
			img.alt = row.getAttribute('data-name') || 'Student';
		}
		setText('as-modal-name', row.getAttribute('data-name'));
		setText('as-modal-level', row.getAttribute('data-level-label') || row.getAttribute('data-level'));
		setText('as-modal-email', row.getAttribute('data-email'));
		setText('as-modal-phone', row.getAttribute('data-phone'));
		setText('as-modal-level-detail', row.getAttribute('data-level-label') || row.getAttribute('data-level'));
		setText('as-modal-instruments', row.getAttribute('data-instruments'));
		setText('as-modal-classes', row.getAttribute('data-classes'));
		setText('as-modal-completed', row.getAttribute('data-completed'));
		setText('as-modal-joined', row.getAttribute('data-joined'));
		setText('as-modal-bio', row.getAttribute('data-bio'));

		var status = row.getAttribute('data-status');
		var badge = document.getElementById('as-modal-status');
		var meta = STATUS_MAP[status];
		if (badge && meta) {
			badge.className = 'sb-badge ' + meta.className;
			badge.textContent = meta.label;
		}

		var actList = document.getElementById('as-modal-activity-list');
		if (actList) {
			var raw = row.getAttribute('data-activity') || '';
			var parts = raw.split('|').filter(Boolean);
			actList.innerHTML = '';
			parts.forEach(function (p) {
				var li = document.createElement('li');
				li.textContent = p;
				actList.appendChild(li);
			});
		}
	}

	function enrichModal(profile) {
		if (!profile || !profile.student) {
			return;
		}
		var s = profile.student;
		var bookings = profile.bookings || [];
		var payments = profile.payments || [];
		var favourites = profile.favourites || [];
		var reviews = profile.reviews || [];

		var summary =
			(s.bio || '') +
			'\n\n— Learning —\nGoals: ' +
			(s.learning_goals || '—') +
			'\n\n— Admin summary —\nBookings: ' +
			bookings.length +
			' | Payments: ' +
			payments.length +
			' | Favourites: ' +
			favourites.length +
			' | Reviews: ' +
			reviews.length;

		setText('as-modal-bio', summary.trim());

		var actList = document.getElementById('as-modal-activity-list');
		if (actList) {
			actList.innerHTML = '';
			bookings.slice(0, 4).forEach(function (b) {
				var li = document.createElement('li');
				li.textContent =
					'Booking #' +
					(b.id || '') +
					' · ' +
					(b.booking_status || '') +
					' · ' +
					(b.booking_date || '');
				actList.appendChild(li);
			});
			payments.slice(0, 2).forEach(function (p) {
				var li = document.createElement('li');
				li.textContent =
					'Payment $' +
					Number(p.amount || 0).toFixed(2) +
					' · ' +
					(p.payment_status || '');
				actList.appendChild(li);
			});
			favourites.slice(0, 2).forEach(function (f) {
				var li = document.createElement('li');
				var name = ((f.first_name || '') + ' ' + (f.last_name || '')).trim() || 'Teacher';
				li.textContent = 'Favourite: ' + name;
				actList.appendChild(li);
			});
			reviews.slice(0, 2).forEach(function (r) {
				var li = document.createElement('li');
				li.textContent = 'Review ★' + (r.rating || '') + ' · ' + (r.status || '');
				actList.appendChild(li);
			});
		}
	}

	function openProfile(row) {
		fillModalFromRow(row);
		if (modal) {
			modal.show();
		}
		var id = getStudentId(row);
		if (!id || !ajaxUrl) {
			return;
		}
		postAction('gmm_get_student_profile', { student_id: id })
			.then(function (json) {
				if (json && json.success && json.data && json.data.profile) {
					enrichModal(json.data.profile);
				}
			})
			.catch(function () {});
	}

	function runStatus(row, status, message) {
		var id = getStudentId(row);
		if (!id) {
			showToast(i18n.error || 'Failed.', true);
			return;
		}
		postAction('gmm_update_student_status', { student_id: id, status: status })
			.then(function (json) {
				if (json && json.success) {
					setRowStatus(row, status);
					showToast((json.data && json.data.message) || message || 'Done.', status !== 'active');
				} else {
					showToast((json && json.data && json.data.message) || i18n.error || 'Failed.', true);
				}
			})
			.catch(function () {
				showToast(i18n.error || 'Failed.', true);
			});
	}

	function editStudent(row) {
		var id = getStudentId(row);
		if (!id) {
			return;
		}
		var first = window.prompt('First name', row.getAttribute('data-first-name') || '') ;
		if (first === null) {
			return;
		}
		var last = window.prompt('Last name', row.getAttribute('data-last-name') || '');
		if (last === null) {
			return;
		}
		var email = window.prompt('Email', row.getAttribute('data-email') || '');
		if (email === null) {
			return;
		}
		var phone = window.prompt('Phone', row.getAttribute('data-phone') || '');
		if (phone === null) {
			return;
		}
		var level = window.prompt('Learning level (beginner/intermediate/advanced)', row.getAttribute('data-level') || '');
		if (level === null) {
			return;
		}
		var goals = window.prompt('Learning goals', row.getAttribute('data-goals') || '');
		if (goals === null) {
			return;
		}
		var status = window.prompt('Status (active/inactive/suspended)', row.getAttribute('data-status') || 'active');
		if (status === null) {
			return;
		}

		postAction('gmm_admin_edit_student', {
			student_id: id,
			first_name: first,
			last_name: last,
			email: email,
			phone: phone,
			learning_level: level,
			learning_goals: goals,
			status: status
		})
			.then(function (json) {
				if (json && json.success) {
					showToast((json.data && json.data.message) || i18n.updated || 'Updated.');
					window.setTimeout(function () {
						window.location.reload();
					}, 500);
				} else {
					showToast((json && json.data && json.data.message) || i18n.error || 'Failed.', true);
				}
			})
			.catch(function () {
				showToast(i18n.error || 'Failed.', true);
			});
	}

	function bindRow(row) {
		var viewBtn = row.querySelector('.as-view-btn');
		var editBtn = row.querySelector('.as-edit-btn');
		var suspendBtn = row.querySelector('.as-suspend-btn');
		var deleteBtn = row.querySelector('.as-delete-btn');
		var lessonsBtn = row.querySelector('.as-lessons-btn');
		var paymentsBtn = row.querySelector('.as-payments-btn');

		if (viewBtn) {
			viewBtn.addEventListener('click', function () {
				openProfile(row);
			});
		}
		if (editBtn) {
			editBtn.addEventListener('click', function () {
				editStudent(row);
			});
		}
		if (lessonsBtn) {
			lessonsBtn.addEventListener('click', function () {
				openProfile(row);
			});
		}
		if (paymentsBtn) {
			paymentsBtn.addEventListener('click', function () {
				openProfile(row);
			});
		}
		if (suspendBtn) {
			suspendBtn.addEventListener('click', function () {
				var current = row.getAttribute('data-status');
				if (current === 'suspended') {
					runStatus(row, 'active', i18n.activated);
				} else if (current === 'inactive') {
					runStatus(row, 'active', i18n.activated);
				} else {
					runStatus(row, 'suspended', i18n.suspended);
				}
			});
		}
		if (deleteBtn) {
			deleteBtn.addEventListener('click', function () {
				if (!window.confirm(i18n.confirmDelete || 'Delete this student?')) {
					return;
				}
				postAction('gmm_delete_student', { student_id: getStudentId(row), confirm: '1' })
					.then(function (json) {
						if (json && json.success) {
							row.classList.add('is-deleted');
							row.hidden = true;
							showToast((json.data && json.data.message) || i18n.deleted, true);
						} else {
							showToast((json && json.data && json.data.message) || i18n.error || 'Failed.', true);
						}
					})
					.catch(function () {
						showToast(i18n.error || 'Failed.', true);
					});
			});
		}
	}

	function initModalActions() {
		var edit = document.getElementById('as-modal-edit');
		var suspend = document.getElementById('as-modal-suspend');
		if (edit) {
			edit.addEventListener('click', function () {
				if (activeRow) {
					editStudent(activeRow);
				}
			});
		}
		if (suspend) {
			suspend.addEventListener('click', function () {
				if (!activeRow) {
					return;
				}
				var current = activeRow.getAttribute('data-status');
				if (current === 'suspended') {
					runStatus(activeRow, 'active', i18n.activated);
				} else {
					runStatus(activeRow, 'suspended', i18n.suspended);
				}
			});
		}
	}

	function initCounters() {
		document.querySelectorAll('.gmm-wrapper .ad-counter').forEach(function (el) {
			var target = parseInt(el.getAttribute('data-count'), 10) || 0;
			var start = null;
			function step(ts) {
				if (start === null) {
					start = ts;
				}
				var p = Math.min((ts - start) / 900, 1);
				el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString('en-US');
				if (p < 1) {
					window.requestAnimationFrame(step);
				}
			}
			window.requestAnimationFrame(step);
		});
	}

	function init() {
		if (!document.getElementById('as-table-body')) {
			return;
		}
		document.querySelectorAll('.as-row').forEach(bindRow);
		initModalActions();
		initCounters();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(window, document);
