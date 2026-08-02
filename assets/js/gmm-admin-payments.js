/**
 * Gospel Music Mastery — Admin payments management
 * Filters, AJAX status/refund, detail modal, CSV export prep.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_ADMIN_PAYMENTS || {};
	var ajaxUrl = cfg.ajaxUrl || (window.GMM_DATA && window.GMM_DATA.ajax_url) || '';
	var nonce = cfg.nonce || (window.GMM_DATA && window.GMM_DATA.nonce) || '';
	var i18n = cfg.i18n || {};
	var exportRows = cfg.export || [];

	var STATUS_MAP = {
		pending: { label: 'Pending', className: 'is-pending' },
		completed: { label: 'Completed', className: 'is-confirmed' },
		failed: { label: 'Failed', className: 'is-failed' },
		refunded: { label: 'Refunded', className: 'is-inactive' }
	};

	function showToast(message, isError) {
		var toast = document.getElementById('ap-toast');
		var toastText = document.getElementById('ap-toast-text');
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

	function getPaymentId(row) {
		if (!row) {
			return 0;
		}
		return parseInt(row.getAttribute('data-payment-id') || '0', 10) || 0;
	}

	function setRowStatus(row, status) {
		var meta = STATUS_MAP[status];
		if (!meta || !row) {
			return;
		}
		row.setAttribute('data-status', status);
		var badge = row.querySelector('.ap-status');
		if (badge) {
			badge.className = 'sb-badge ap-status ' + meta.className;
			badge.textContent = meta.label;
		}
	}

	var activeRow = null;
	var modalEl = document.getElementById('ap-txn-modal');
	var modal = modalEl && window.bootstrap ? new window.bootstrap.Modal(modalEl) : null;

	function setText(id, value) {
		var el = document.getElementById(id);
		if (el) {
			el.textContent = value || '';
		}
	}

	function fillModalFromRow(row) {
		activeRow = row;
		var img = document.getElementById('ap-modal-user-img');
		if (img) {
			img.src = row.getAttribute('data-user-img') || '';
			img.alt = row.getAttribute('data-user') || 'User';
		}
		setText('ap-modal-user', row.getAttribute('data-user'));
		setText('ap-modal-email', row.getAttribute('data-email'));
		setText('ap-modal-id', row.getAttribute('data-id'));
		setText('ap-modal-type', row.getAttribute('data-type-label'));
		setText('ap-modal-amount', row.getAttribute('data-amount'));
		setText('ap-modal-method', row.getAttribute('data-method'));
		setText('ap-modal-date', row.getAttribute('data-date'));
		setText('ap-modal-booking', row.getAttribute('data-booking'));

		var st = row.getAttribute('data-status');
		var stMeta = STATUS_MAP[st];
		var stEl = document.getElementById('ap-modal-status');
		if (stEl && stMeta) {
			stEl.className = 'sb-badge ' + stMeta.className;
			stEl.textContent = stMeta.label;
		}
	}

	function enrichModal(profile) {
		if (!profile || !profile.formatted) {
			return;
		}
		var f = profile.formatted;
		var student = profile.student || {};
		var teacher = profile.teacher || {};
		var split = profile.split || {};

		if (student.name) {
			setText('ap-modal-user', student.name);
		}
		if (student.email) {
			setText('ap-modal-email', student.email);
		}
		if (f.txn_code) {
			setText('ap-modal-id', f.txn_code);
		}
		if (f.type_label) {
			setText('ap-modal-type', f.type_label);
		}
		if (f.amount_label) {
			setText('ap-modal-amount', f.amount_label);
		}
		if (f.method_label) {
			setText('ap-modal-method', f.method_label);
		}
		if (f.date) {
			setText('ap-modal-date', f.date);
		}
		if (f.booking_code) {
			setText('ap-modal-booking', f.booking_code);
		}

		var notes = [];
		if (teacher.name) {
			notes.push('Teacher: ' + teacher.name);
		}
		if (split.commission !== undefined) {
			notes.push('Commission: $' + Number(split.commission).toFixed(2));
		}
		if (split.teacher_earnings !== undefined) {
			notes.push('Teacher earnings: $' + Number(split.teacher_earnings).toFixed(2));
		}
		var timeline = profile.timeline || [];
		timeline.forEach(function (item) {
			if (item && item.label) {
				notes.push(item.label);
			}
		});
		if (notes.length) {
			setText('ap-modal-email', (student.email || '') + (notes.length ? ' · ' + notes.join(' · ') : ''));
		}
	}

	function openProfile(row) {
		fillModalFromRow(row);
		if (modal) {
			modal.show();
		}
		var id = getPaymentId(row);
		if (!id) {
			return;
		}
		postAction('gmm_get_payment_profile', { payment_id: id })
			.then(function (json) {
				if (json && json.success && json.data && json.data.profile) {
					enrichModal(json.data.profile);
				}
			})
			.catch(function () {
				/* keep row data */
			});
	}

	function runStatus(row, status, successKey) {
		var id = getPaymentId(row);
		if (!id) {
			return;
		}
		if (status === 'refunded' && !window.confirm(i18n.confirmRefund || 'Process refund?')) {
			return;
		}
		postAction('gmm_admin_update_payment_status', { payment_id: id, status: status })
			.then(function (json) {
				if (json && json.success) {
					setRowStatus(row, status);
					if (activeRow === row) {
						fillModalFromRow(row);
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

	function editStatus(row) {
		var current = row.getAttribute('data-status') || 'pending';
		var next = window.prompt('Payment status (pending / completed / failed / refunded)', current);
		if (next === null) {
			return;
		}
		next = String(next).toLowerCase().trim();
		if (['pending', 'completed', 'failed', 'refunded'].indexOf(next) === -1) {
			showToast(i18n.error || 'Invalid status.', true);
			return;
		}
		runStatus(row, next, next);
	}

	function bindRow(row) {
		var viewBtn = row.querySelector('.ap-view-btn');
		var statusBadge = row.querySelector('.ap-status');
		if (viewBtn) {
			viewBtn.addEventListener('click', function () {
				openProfile(row);
			});
		}
		if (statusBadge) {
			statusBadge.style.cursor = 'pointer';
			statusBadge.title = 'Click to update payment status';
			statusBadge.addEventListener('click', function (e) {
				e.preventDefault();
				editStatus(row);
			});
		}
	}

	function bindRefundRows() {
		Array.prototype.forEach.call(document.querySelectorAll('.ap-refund-row'), function (row) {
			var approve = row.querySelector('.ap-approve-refund');
			var reject = row.querySelector('.ap-reject-refund');
			var paymentId = parseInt(row.getAttribute('data-payment-id') || '0', 10) || 0;
			var index = parseInt(row.getAttribute('data-index') || '-1', 10);

			if (approve) {
				approve.addEventListener('click', function () {
					if (!window.confirm(i18n.confirmApprove || 'Approve refund?')) {
						return;
					}
					postAction('gmm_admin_approve_refund', {
						payment_id: paymentId,
						request_index: index,
						confirm: 1
					})
						.then(function (json) {
							if (json && json.success) {
								showToast((json.data && json.data.message) || i18n.refundApproved);
								window.setTimeout(function () {
									window.location.reload();
								}, 500);
							} else {
								showToast((json && json.data && json.data.message) || i18n.error, true);
							}
						})
						.catch(function () {
							showToast(i18n.error || 'Failed.', true);
						});
				});
			}
			if (reject) {
				reject.addEventListener('click', function () {
					if (!window.confirm(i18n.confirmReject || 'Reject refund?')) {
						return;
					}
					postAction('gmm_admin_reject_refund', {
						payment_id: paymentId,
						request_index: index,
						confirm: 1
					})
						.then(function (json) {
							if (json && json.success) {
								showToast((json.data && json.data.message) || i18n.refundRejected);
								window.setTimeout(function () {
									window.location.reload();
								}, 500);
							} else {
								showToast((json && json.data && json.data.message) || i18n.error, true);
							}
						})
						.catch(function () {
							showToast(i18n.error || 'Failed.', true);
						});
				});
			}
		});
	}

	function bindModal() {
		var refundBtn = document.getElementById('ap-modal-refund-btn');
		var userBtn = document.getElementById('ap-modal-user-btn');
		var bookingBtn = document.getElementById('ap-modal-booking-btn');

		if (refundBtn) {
			refundBtn.addEventListener('click', function () {
				if (activeRow) {
					runStatus(activeRow, 'refunded', 'refunded');
				}
			});
		}
		if (userBtn) {
			userBtn.addEventListener('click', function () {
				if (!activeRow) {
					return;
				}
				var url = activeRow.getAttribute('data-user-url') || '';
				if (url) {
					window.location.href = url;
				}
			});
		}
		if (bookingBtn) {
			bookingBtn.addEventListener('click', function () {
				if (!activeRow) {
					return;
				}
				var url = activeRow.getAttribute('data-booking-url') || '';
				if (url) {
					window.location.href = url;
				}
			});
		}
	}

	function downloadCsv(rows) {
		if (!rows || !rows.length) {
			showToast(i18n.error || 'No rows to export.', true);
			return;
		}
		var headers = Object.keys(rows[0]);
		var lines = [headers.join(',')];
		rows.forEach(function (row) {
			lines.push(
				headers
					.map(function (key) {
						var val = row[key] == null ? '' : String(row[key]);
						return '"' + val.replace(/"/g, '""') + '"';
					})
					.join(',')
			);
		});
		var blob = new window.Blob([lines.join('\n')], { type: 'text/csv;charset=utf-8;' });
		var url = window.URL.createObjectURL(blob);
		var a = document.createElement('a');
		a.href = url;
		a.download = 'gmm-payments-export.csv';
		document.body.appendChild(a);
		a.click();
		document.body.removeChild(a);
		window.URL.revokeObjectURL(url);
		showToast(i18n.exportReady || 'Export prepared.');
	}

	var exportBtn = document.getElementById('ap-export-csv');
	var reportBtn = document.getElementById('ap-generate-report');
	if (exportBtn) {
		exportBtn.addEventListener('click', function () {
			downloadCsv(exportRows);
		});
	}
	if (reportBtn) {
		reportBtn.addEventListener('click', function () {
			postAction('gmm_admin_payment_export', {})
				.then(function (json) {
					if (json && json.success && json.data && json.data.rows) {
						downloadCsv(json.data.rows);
					} else {
						downloadCsv(exportRows);
					}
				})
				.catch(function () {
					downloadCsv(exportRows);
				});
		});
	}

	Array.prototype.forEach.call(document.querySelectorAll('.ap-row'), bindRow);
	bindRefundRows();
	bindModal();
})(window, document);
