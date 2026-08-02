/**
 * Gospel Music Mastery — Admin teachers management
 * Search/filter form, AJAX approve/reject/suspend/delete/bulk, profile modal.
 * Design markup unchanged.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_ADMIN_TEACHERS || {};
	var ajaxUrl = cfg.ajaxUrl || (window.GMM_DATA && window.GMM_DATA.ajax_url) || '';
	var nonce = cfg.nonce || (window.GMM_DATA && window.GMM_DATA.nonce) || '';
	var i18n = cfg.i18n || {};

	var STATUS_MAP = {
		pending: { label: 'Pending', className: 'is-pending' },
		approved: { label: 'Approved', className: 'is-confirmed' },
		rejected: { label: 'Rejected', className: 'is-cancelled' },
		suspended: { label: 'Suspended', className: 'is-suspended' }
	};

	function showToast(message, isError) {
		var toast = document.getElementById('at-toast');
		var toastText = document.getElementById('at-toast-text');
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
			.fetch(ajaxUrl, {
				method: 'POST',
				credentials: 'same-origin',
				body: body
			})
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
		var badge = row.querySelector('.at-status');
		if (badge) {
			badge.className = 'sb-badge at-status ' + meta.className;
			badge.textContent = meta.label;
		}
	}

	function getTeacherId(row) {
		return parseInt(row.getAttribute('data-teacher-id') || '0', 10) || 0;
	}

	function getSelectedIds() {
		var boxes = document.querySelectorAll('.at-row-check:checked');
		var ids = [];
		boxes.forEach(function (box) {
			var id = parseInt(box.value, 10);
			if (id) {
				ids.push(id);
			}
		});
		return ids;
	}

	var activeRow = null;
	var modalEl = document.getElementById('at-teacher-modal');
	var modal = modalEl && window.bootstrap ? new window.bootstrap.Modal(modalEl) : null;

	function setText(id, value) {
		var el = document.getElementById(id);
		if (el) {
			el.textContent = value || '';
		}
	}

	function fillModalFromRow(row) {
		activeRow = row;
		var img = document.getElementById('at-modal-image');
		if (img) {
			img.src = row.getAttribute('data-image') || '';
			img.alt = row.getAttribute('data-name') || 'Teacher';
		}
		setText('at-modal-name', row.getAttribute('data-name'));
		var specCell = row.querySelector('td[data-label="Specialization"]');
		setText('at-modal-specialty', specCell ? specCell.textContent.trim() : '');
		setText('at-modal-rating', '★★★★★ ' + (row.getAttribute('data-rating') || ''));
		setText('at-modal-email', row.getAttribute('data-email'));
		setText('at-modal-phone', row.getAttribute('data-phone'));
		setText('at-modal-experience', row.getAttribute('data-experience'));
		setText('at-modal-classes', row.getAttribute('data-classes'));
		setText('at-modal-students', row.getAttribute('data-students'));
		setText('at-modal-joined', row.getAttribute('data-joined'));
		setText('at-modal-bio', row.getAttribute('data-bio'));

		var status = row.getAttribute('data-status');
		var badge = document.getElementById('at-modal-status');
		var meta = STATUS_MAP[status];
		if (badge && meta) {
			badge.className = 'sb-badge ' + meta.className;
			badge.textContent = meta.label;
		}
	}

	function enrichModal(profile) {
		if (!profile || !profile.teacher) {
			return;
		}
		var t = profile.teacher;
		var earnings = profile.earnings || {};
		var bookings = profile.bookings || [];
		var reviews = profile.reviews || [];
		var classes = profile.classes || [];

		var extra =
			'\n\n— Admin summary —\n' +
			'Classes: ' +
			classes.length +
			' | Bookings: ' +
			bookings.length +
			' | Reviews: ' +
			reviews.length +
			'\nEarnings: $' +
			Number(earnings.total || 0).toFixed(2) +
			' (pending $' +
			Number(earnings.pending || 0).toFixed(2) +
			')';

		var bio = (t.bio || '') + extra;
		setText('at-modal-bio', bio.trim());
		if (t.experience) {
			setText('at-modal-experience', t.experience);
		}
		if (t.classes !== undefined) {
			setText('at-modal-classes', String(t.classes));
		}
		if (t.students !== undefined) {
			setText('at-modal-students', String(t.students));
		}
	}

	function openProfile(row) {
		fillModalFromRow(row);
		if (modal) {
			modal.show();
		}
		var id = getTeacherId(row);
		if (!id || !ajaxUrl) {
			return;
		}
		postAction('gmm_get_teacher_profile', { teacher_id: id })
			.then(function (json) {
				if (json && json.success && json.data && json.data.profile) {
					enrichModal(json.data.profile);
				}
			})
			.catch(function () {
				/* keep row data */
			});
	}

	function runStatusAction(row, action, uiStatus, successMsg, extraData) {
		var id = getTeacherId(row);
		if (!id) {
			showToast(i18n.error || 'Action failed.', true);
			return;
		}
		var payload = Object.assign({ teacher_id: id }, extraData || {});
		postAction(action, payload)
			.then(function (json) {
				if (json && json.success) {
					setRowStatus(row, uiStatus);
					showToast(
						(json.data && json.data.message) || successMsg || 'Done.',
						uiStatus === 'rejected' || uiStatus === 'suspended'
					);
				} else {
					showToast(
						(json && json.data && json.data.message) || i18n.error || 'Action failed.',
						true
					);
				}
			})
			.catch(function () {
				showToast(i18n.error || 'Action failed.', true);
			});
	}

	function bindRow(row) {
		var name = row.getAttribute('data-name') || 'Teacher';
		var viewBtn = row.querySelector('.at-view-btn');
		var approveBtn = row.querySelector('.at-approve-btn');
		var rejectBtn = row.querySelector('.at-reject-btn');
		var suspendBtn = row.querySelector('.at-suspend-btn');
		var deleteBtn = row.querySelector('.at-delete-btn');
		var editBtn = row.querySelector('.at-edit-btn');

		if (viewBtn) {
			viewBtn.addEventListener('click', function () {
				openProfile(row);
			});
		}
		if (approveBtn) {
			approveBtn.addEventListener('click', function () {
				runStatusAction(row, 'gmm_approve_teacher', 'approved', i18n.approved);
			});
		}
		if (rejectBtn) {
			rejectBtn.addEventListener('click', function () {
				var reason = window.prompt(i18n.rejectPrompt || 'Optional rejection reason:', '') || '';
				runStatusAction(row, 'gmm_reject_teacher', 'rejected', i18n.rejected, {
					reason: reason
				});
			});
		}
		if (suspendBtn) {
			suspendBtn.addEventListener('click', function () {
				runStatusAction(row, 'gmm_suspend_teacher', 'suspended', i18n.suspended);
			});
		}
		if (deleteBtn) {
			deleteBtn.addEventListener('click', function () {
				if (!window.confirm(i18n.confirmDelete || 'Delete this teacher?')) {
					return;
				}
				var id = getTeacherId(row);
				postAction('gmm_delete_teacher', { teacher_id: id, confirm: '1' })
					.then(function (json) {
						if (json && json.success) {
							row.classList.add('is-deleted');
							row.hidden = true;
							showToast((json.data && json.data.message) || i18n.deleted, true);
						} else {
							showToast(
								(json && json.data && json.data.message) || i18n.error || 'Failed.',
								true
							);
						}
					})
					.catch(function () {
						showToast(i18n.error || 'Action failed.', true);
					});
			});
		}
		if (editBtn) {
			editBtn.addEventListener('click', function () {
				openProfile(row);
			});
		}
	}

	function initBulkBar() {
		var bar = document.getElementById('at-bulk-bar');
		if (!bar) {
			return;
		}
		bar.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-bulk-action]');
			if (!btn) {
				return;
			}
			var action = btn.getAttribute('data-bulk-action');
			var ids = getSelectedIds();
			if (!ids.length) {
				showToast('Select at least one teacher.', true);
				return;
			}
			var reason = '';
			if (action === 'reject') {
				reason = window.prompt(i18n.rejectPrompt || 'Optional rejection reason:', '') || '';
			}
			postAction('gmm_bulk_teacher_action', {
				bulk_action: action,
				teacher_ids: ids,
				reason: reason
			})
				.then(function (json) {
					if (json && json.success) {
						showToast((json.data && json.data.message) || i18n.bulkDone || 'Done.');
						window.setTimeout(function () {
							window.location.reload();
						}, 600);
					} else {
						showToast(
							(json && json.data && json.data.message) || i18n.error || 'Failed.',
							true
						);
					}
				})
				.catch(function () {
					showToast(i18n.error || 'Action failed.', true);
				});
		});
	}

	function initModalActions() {
		var modalApprove = document.getElementById('at-modal-approve');
		var modalReject = document.getElementById('at-modal-reject');
		if (modalApprove) {
			modalApprove.addEventListener('click', function () {
				if (!activeRow) {
					return;
				}
				runStatusAction(activeRow, 'gmm_approve_teacher', 'approved', i18n.approved);
			});
		}
		if (modalReject) {
			modalReject.addEventListener('click', function () {
				if (!activeRow) {
					return;
				}
				var reason = window.prompt(i18n.rejectPrompt || 'Optional rejection reason:', '') || '';
				runStatusAction(activeRow, 'gmm_reject_teacher', 'rejected', i18n.rejected, {
					reason: reason
				});
			});
		}
	}

	function initCounters() {
		document.querySelectorAll('.gmm-wrapper .ad-counter').forEach(function (el) {
			var target = parseInt(el.getAttribute('data-count'), 10) || 0;
			var duration = 900;
			var start = null;
			function step(ts) {
				if (start === null) {
					start = ts;
				}
				var p = Math.min((ts - start) / duration, 1);
				var eased = 1 - Math.pow(1 - p, 3);
				el.textContent = Math.round(target * eased).toLocaleString('en-US');
				if (p < 1) {
					window.requestAnimationFrame(step);
				}
			}
			window.requestAnimationFrame(step);
		});
	}

	function init() {
		if (!document.getElementById('at-table-body')) {
			return;
		}
		document.querySelectorAll('.at-row').forEach(bindRow);
		initModalActions();
		initBulkBar();
		initCounters();

		var selectAll = document.getElementById('at-select-all');
		if (selectAll) {
			selectAll.addEventListener('change', function () {
				document.querySelectorAll('.at-row-check').forEach(function (box) {
					box.checked = selectAll.checked;
				});
			});
		}
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(window, document);
