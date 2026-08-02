/**
 * Gospel Music Mastery — Teacher Booking Management
 * Confirm / cancel / complete via AJAX. Frozen table UI preserved.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_TEACHER_BOOKINGS || {};
	var pendingCancelRow = null;
	var currentFilter = 'all';
	var dateFilter = 'all';
	var classFilter = '0';

	function ajax(action, formData) {
		formData = formData || new window.FormData();
		formData.append('action', action);
		formData.append(cfg.nonceField || 'gmm_teacher_booking_nonce', cfg.nonce || '');
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

	function updateStats(stats) {
		if (!stats) {
			return;
		}
		var map = {
			pending: 'summary-pending',
			upcoming: 'summary-upcoming',
			completed: 'summary-completed',
			students: 'summary-students'
		};
		Object.keys(map).forEach(function (key) {
			var el = document.getElementById(map[key]);
			if (el && typeof stats[key] !== 'undefined') {
				el.textContent = String(stats[key]);
			}
		});
	}

	function inDateFilter(dateKey) {
		if (!dateKey || dateFilter === 'all') {
			return true;
		}
		var parts = dateKey.split('-');
		if (parts.length < 3) {
			return true;
		}
		var d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
		var today = new Date();
		today.setHours(0, 0, 0, 0);
		d.setHours(0, 0, 0, 0);

		if (dateFilter === 'today') {
			return d.getTime() === today.getTime();
		}
		if (dateFilter === 'week') {
			var day = (today.getDay() + 6) % 7;
			var monday = new Date(today);
			monday.setDate(today.getDate() - day);
			var sunday = new Date(monday);
			sunday.setDate(monday.getDate() + 6);
			return d >= monday && d <= sunday;
		}
		if (dateFilter === 'month') {
			return d.getFullYear() === today.getFullYear() && d.getMonth() === today.getMonth();
		}
		return true;
	}

	function applyFilter() {
		var tbody = document.getElementById('bookings-tbody');
		var emptyState = document.getElementById('bookings-empty');
		var tableWrap = document.querySelector('#bookings-table')
			? document.querySelector('#bookings-table').closest('.table-responsive')
			: null;
		if (!tbody) {
			return;
		}
		var rows = tbody.querySelectorAll('tr');
		var visible = 0;
		rows.forEach(function (row) {
			var status = row.getAttribute('data-status') || '';
			var classId = row.getAttribute('data-class-id') || '0';
			var dateKey = row.getAttribute('data-date') || '';
			var show = currentFilter === 'all' || status === currentFilter;
			if (show && classFilter && classFilter !== '0') {
				show = String(classId) === String(classFilter);
			}
			if (show) {
				show = inDateFilter(dateKey);
			}
			row.hidden = !show;
			if (show) {
				visible += 1;
			}
		});
		if (emptyState) {
			emptyState.hidden = visible > 0;
		}
		if (tableWrap) {
			tableWrap.hidden = visible === 0;
		}
	}

	function actionsHtml(rowData) {
		if (!rowData) {
			return '';
		}
		if (rowData.can_confirm) {
			return '<div class="booking-actions">' +
				'<button type="button" class="theme-btn td-action-btn booking-confirm-btn">Confirm</button>' +
				'<button type="button" class="theme-btn theme-btn-outline td-action-btn booking-decline-btn">Decline</button>' +
				'</div>';
		}
		if (rowData.can_complete) {
			return '<div class="booking-actions">' +
				detailsBtn(rowData) +
				'<button type="button" class="theme-btn td-action-btn booking-complete-btn">Complete</button>' +
				'<button type="button" class="theme-btn theme-btn-outline td-action-btn booking-decline-btn">Cancel</button>' +
				'</div>';
		}
		if (rowData.can_review) {
			return '<div class="booking-actions">' +
				'<button type="button" class="theme-btn theme-btn-outline td-action-btn booking-review-btn">View Review</button>' +
				'</div>';
		}
		return '<div class="booking-actions">' + detailsBtn(rowData) + '</div>';
	}

	function detailsBtn(rowData) {
		return '<button type="button" class="theme-btn theme-btn-outline td-action-btn booking-details-btn"' +
			' data-student="' + escapeAttr(rowData.student_name) + '"' +
			' data-image="' + escapeAttr(rowData.student_image) + '"' +
			' data-lesson="' + escapeAttr(rowData.class_name) + '"' +
			' data-date="' + escapeAttr(rowData.date_label) + '"' +
			' data-time="' + escapeAttr(rowData.time_label) + '"' +
			' data-duration="' + escapeAttr(rowData.duration_label) + '"' +
			' data-payment="' + escapeAttr(rowData.payment_label) + '"' +
			' data-notes="' + escapeAttr(rowData.notes) + '">View Details</button>';
	}

	function buildRowHtml(rowData) {
		if (!rowData || !rowData.id) {
			return '';
		}
		return '<tr data-status="' + escapeAttr(rowData.status) + '" data-booking-id="' + escapeAttr(rowData.id) +
			'" data-class-id="' + escapeAttr(rowData.class_id) + '" data-date="' + escapeAttr(rowData.booking_date) + '">' +
			'<td data-label="Student"><strong>' + escapeHtml(rowData.student_name) + '</strong></td>' +
			'<td data-label="Lesson">' + escapeHtml(rowData.class_name) + '</td>' +
			'<td data-label="Date">' + escapeHtml(rowData.date_label) + '</td>' +
			'<td data-label="Time">' + escapeHtml(rowData.time_label) + '</td>' +
			'<td data-label="Duration">' + escapeHtml(rowData.duration_label) + '</td>' +
			'<td data-label="Status"><span class="td-badge ' + escapeAttr(rowData.badge_class) + ' booking-status">' +
			escapeHtml(rowData.status_label) + '</span></td>' +
			'<td data-label="Action">' + actionsHtml(rowData) + '</td></tr>';
	}

	function upsertRow(rowData) {
		var tbody = document.getElementById('bookings-tbody');
		if (!tbody || !rowData) {
			return;
		}
		var existing = tbody.querySelector('tr[data-booking-id="' + rowData.id + '"]');
		var html = buildRowHtml(rowData);
		if (existing) {
			existing.outerHTML = html;
		} else {
			tbody.insertAdjacentHTML('afterbegin', html);
		}
		applyFilter();
	}

	function refreshList() {
		var fd = new window.FormData();
		fd.append('gmm_booking_status', currentFilter);
		fd.append('gmm_booking_date', dateFilter);
		fd.append('gmm_booking_class', classFilter);
		return ajax(actionName('list') || 'gmm_teacher_booking_list', fd).then(function (json) {
			if (!json || !json.success) {
				return;
			}
			var tbody = document.getElementById('bookings-tbody');
			if (tbody && json.data && Array.isArray(json.data.rows)) {
				tbody.innerHTML = json.data.rows.map(buildRowHtml).join('');
			}
			updateStats(json.data && json.data.stats);
			applyFilter();
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		var tbody = document.getElementById('bookings-tbody');
		if (!tbody) {
			return;
		}

		var tabs = document.querySelectorAll('.booking-tab');
		var detailsModalEl = document.getElementById('booking-details-modal');
		var reviewModalEl = document.getElementById('booking-review-modal');
		var cancelModalEl = document.getElementById('booking-cancel-modal');
		var detailsModal = detailsModalEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(detailsModalEl) : null;
		var reviewModal = reviewModalEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(reviewModalEl) : null;
		var cancelModal = cancelModalEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(cancelModalEl) : null;

		tabs.forEach(function (tab) {
			if (tab.classList.contains('active')) {
				currentFilter = tab.getAttribute('data-filter') || 'all';
			}
			tab.addEventListener('click', function () {
				tabs.forEach(function (t) {
					t.classList.remove('active');
					t.setAttribute('aria-selected', 'false');
				});
				tab.classList.add('active');
				tab.setAttribute('aria-selected', 'true');
				currentFilter = tab.getAttribute('data-filter') || 'all';
				applyFilter();
			});
		});

		var dateSelect = document.getElementById('gmm-booking-date-filter');
		var classSelect = document.getElementById('gmm-booking-class-filter');
		if (dateSelect) {
			dateFilter = dateSelect.value || 'all';
			dateSelect.addEventListener('change', function () {
				dateFilter = dateSelect.value || 'all';
				refreshList();
			});
		}
		if (classSelect) {
			classFilter = classSelect.value || '0';
			classSelect.addEventListener('change', function () {
				classFilter = classSelect.value || '0';
				refreshList();
			});
		}

		tbody.addEventListener('click', function (e) {
			var row = e.target.closest('tr');
			if (!row) {
				return;
			}
			var bookingId = row.getAttribute('data-booking-id') || '';
			var confirmBtn = e.target.closest('.booking-confirm-btn');
			var declineBtn = e.target.closest('.booking-decline-btn');
			var completeBtn = e.target.closest('.booking-complete-btn');
			var detailsBtn = e.target.closest('.booking-details-btn');
			var reviewBtn = e.target.closest('.booking-review-btn');

			if (confirmBtn && bookingId) {
				confirmBtn.disabled = true;
				var fd = new window.FormData();
				fd.append('booking_id', bookingId);
				ajax(actionName('confirm') || 'gmm_teacher_booking_confirm', fd)
					.then(function (json) {
						if (!json || !json.success) {
							throw new Error((json && json.data && json.data.message) || i18n('error'));
						}
						if (json.data && json.data.row) {
							upsertRow(json.data.row);
						}
						updateStats(json.data && json.data.stats);
					})
					.catch(function (err) {
						window.alert((err && err.message) || i18n('error'));
						confirmBtn.disabled = false;
					});
				return;
			}

			if (completeBtn && bookingId) {
				completeBtn.disabled = true;
				var fdComplete = new window.FormData();
				fdComplete.append('booking_id', bookingId);
				ajax(actionName('complete') || 'gmm_teacher_booking_complete', fdComplete)
					.then(function (json) {
						if (!json || !json.success) {
							throw new Error((json && json.data && json.data.message) || i18n('error'));
						}
						if (json.data && json.data.row) {
							upsertRow(json.data.row);
						}
						updateStats(json.data && json.data.stats);
					})
					.catch(function (err) {
						window.alert((err && err.message) || i18n('error'));
						completeBtn.disabled = false;
					});
				return;
			}

			if (declineBtn && bookingId) {
				pendingCancelRow = row;
				var nameEl = row.querySelector('[data-label="Student"] strong') || row.querySelector('[data-label="Student"]');
				var cancelName = document.getElementById('booking-cancel-name');
				if (cancelName) {
					cancelName.textContent = (nameEl && nameEl.textContent) ? nameEl.textContent.trim() : 'this booking';
				}
				var reason = document.getElementById('booking-cancel-reason');
				if (reason) {
					reason.value = '';
				}
				if (cancelModal) {
					cancelModal.show();
				} else if (window.confirm(i18n('confirmCancel'))) {
					doCancel(bookingId, '');
				}
				return;
			}

			if (detailsBtn && detailsModal) {
				var fillFromBtn = function () {
					document.getElementById('modal-student-name').textContent = detailsBtn.getAttribute('data-student') || 'Student';
					document.getElementById('modal-student-image').src = detailsBtn.getAttribute('data-image') || '';
					document.getElementById('modal-student-image').alt = detailsBtn.getAttribute('data-student') || 'Student';
					document.getElementById('modal-lesson-name').textContent = detailsBtn.getAttribute('data-lesson') || '';
					document.getElementById('modal-date').textContent = detailsBtn.getAttribute('data-date') || '—';
					document.getElementById('modal-time').textContent = detailsBtn.getAttribute('data-time') || '—';
					document.getElementById('modal-duration').textContent = detailsBtn.getAttribute('data-duration') || '—';
					document.getElementById('modal-payment').textContent = detailsBtn.getAttribute('data-payment') || '—';
					document.getElementById('modal-notes').textContent = detailsBtn.getAttribute('data-notes') || '—';
					detailsModal.show();
				};

				if (bookingId) {
					var fdDetails = new window.FormData();
					fdDetails.append('booking_id', bookingId);
					ajax(actionName('details') || 'gmm_teacher_booking_details', fdDetails)
						.then(function (json) {
							if (!json || !json.success || !json.data || !json.data.details) {
								fillFromBtn();
								return;
							}
							var d = json.data.details;
							document.getElementById('modal-student-name').textContent = d.student_name || 'Student';
							document.getElementById('modal-student-image').src = d.student_image || '';
							document.getElementById('modal-student-image').alt = d.student_name || 'Student';
							document.getElementById('modal-lesson-name').textContent = d.class_name || '';
							document.getElementById('modal-date').textContent = d.date_label || '—';
							document.getElementById('modal-time').textContent = d.time_label || '—';
							document.getElementById('modal-duration').textContent = d.duration_label || '—';
							document.getElementById('modal-payment').textContent = d.payment_label || '—';
							document.getElementById('modal-notes').textContent = d.notes || d.student_learning || '—';
							detailsModal.show();
						})
						.catch(fillFromBtn);
				} else {
					fillFromBtn();
				}
				return;
			}

			if (reviewBtn && reviewModal && bookingId) {
				var fdReview = new window.FormData();
				fdReview.append('booking_id', bookingId);
				ajax(actionName('review') || 'gmm_teacher_booking_review', fdReview)
					.then(function (json) {
						var stars = document.getElementById('modal-review-stars');
						var text = document.getElementById('modal-review-text');
						if (json && json.success && json.data && json.data.review) {
							if (stars) {
								stars.textContent = json.data.review.stars || '—';
							}
							if (text) {
								text.textContent = json.data.review.comment
									? ('“' + json.data.review.comment + '”')
									: i18n('noReview');
							}
						} else {
							if (stars) {
								stars.textContent = '—';
							}
							if (text) {
								text.textContent = i18n('noReview');
							}
						}
						reviewModal.show();
					})
					.catch(function () {
						var starsEl = document.getElementById('modal-review-stars');
						var textEl = document.getElementById('modal-review-text');
						if (starsEl) {
							starsEl.textContent = '—';
						}
						if (textEl) {
							textEl.textContent = i18n('noReview');
						}
						reviewModal.show();
					});
			}
		});

		function doCancel(bookingId, reason) {
			var fd = new window.FormData();
			fd.append('booking_id', bookingId);
			fd.append('confirm', '1');
			if (reason) {
				fd.append('reason', reason);
			}
			ajax(actionName('cancel') || 'gmm_teacher_booking_cancel', fd)
				.then(function (json) {
					if (!json || !json.success) {
						throw new Error((json && json.data && json.data.message) || i18n('error'));
					}
					if (json.data && json.data.row) {
						upsertRow(json.data.row);
					}
					updateStats(json.data && json.data.stats);
					pendingCancelRow = null;
					if (cancelModal) {
						cancelModal.hide();
					}
				})
				.catch(function (err) {
					window.alert((err && err.message) || i18n('error'));
				});
		}

		var cancelConfirm = document.getElementById('booking-cancel-confirm');
		if (cancelConfirm) {
			cancelConfirm.addEventListener('click', function () {
				if (!pendingCancelRow) {
					return;
				}
				var id = pendingCancelRow.getAttribute('data-booking-id') || '';
				var reasonEl = document.getElementById('booking-cancel-reason');
				doCancel(id, reasonEl ? reasonEl.value : '');
			});
		}

		// Sidebar.
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

		applyFilter();
		updateStats(cfg.stats || {});

		window.GMMTeacherBookings = {
			refresh: refreshList
		};
	});
})(window, document);
