/**
 * Gospel Music Mastery — Student booking history
 * Filter tabs, details modal, cancel pending via AJAX.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_STUDENT_BOOKINGS || {};
	var currentFilter = 'all';

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
			fd.append(key, String(fields[key]));
		});
		return window.fetch(cfg.ajaxUrl || '', {
			method: 'POST',
			credentials: 'same-origin',
			body: fd
		}).then(function (res) {
			return res.json();
		});
	}

	function toast(msg) {
		var box = $('sb-toast');
		var text = $('sb-toast-text');
		if (text) {
			text.textContent = msg || '';
		}
		if (box) {
			box.hidden = false;
			window.setTimeout(function () {
				box.hidden = true;
			}, 2800);
		}
	}

	function applyFilter(filter) {
		currentFilter = filter || 'all';
		var rows = document.querySelectorAll('#sb-table-body .sb-row');
		var visible = 0;
		rows.forEach(function (row) {
			var status = row.getAttribute('data-status') || '';
			var show = currentFilter === 'all' || status === currentFilter;
			row.hidden = !show;
			if (show) {
				visible += 1;
			}
		});
		var empty = $('sb-empty');
		var wrap = $('sb-table-wrap');
		if (empty) {
			empty.hidden = visible > 0;
		}
		if (wrap) {
			wrap.hidden = visible === 0;
		}
	}

	function openDetails(row) {
		if (!row) {
			return;
		}
		var map = {
			'sb-modal-teacher': 'data-teacher',
			'sb-modal-class': 'data-class',
			'sb-modal-date': 'data-date',
			'sb-modal-time': 'data-time',
			'sb-modal-duration': 'data-duration',
			'sb-modal-price': 'data-price',
			'sb-modal-notes': 'data-notes'
		};
		Object.keys(map).forEach(function (id) {
			var el = $(id);
			if (el) {
				el.textContent = row.getAttribute(map[id]) || '—';
			}
		});
		var status = row.getAttribute('data-status') || '';
		var badge = $('sb-modal-status-badge');
		var statusEl = $('sb-modal-status');
		if (badge) {
			badge.className = 'sb-badge is-' + status;
			badge.textContent = status ? status.charAt(0).toUpperCase() + status.slice(1) : '—';
		}
		if (statusEl) {
			statusEl.textContent = status ? status.charAt(0).toUpperCase() + status.slice(1) : '—';
		}
		var img = $('sb-modal-image');
		if (img) {
			img.src = row.getAttribute('data-image') || img.src;
			img.alt = row.getAttribute('data-teacher') || 'Teacher';
		}

		var modalEl = $('sb-details-modal');
		if (modalEl && window.bootstrap && window.bootstrap.Modal) {
			window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
		} else if (modalEl) {
			modalEl.classList.add('show');
			modalEl.style.display = 'block';
		}
	}

	function cancelBooking(btn) {
		var row = btn.closest('.sb-row');
		var id = parseInt(btn.getAttribute('data-booking-id') || (row && row.getAttribute('data-booking-id')) || '0', 10);
		if (!id) {
			return;
		}
		if (!window.confirm(i18n('confirm', 'Cancel this pending booking request?'))) {
			return;
		}
		btn.disabled = true;
		ajax(cfg.actions && cfg.actions.cancel || 'gmm_booking_flow_cancel', {
			booking_id: id
		}).then(function (json) {
			if (!json || !json.success) {
				toast((json && json.data && json.data.message) || i18n('error'));
				btn.disabled = false;
				return;
			}
			toast(i18n('cancelled', 'Booking cancelled.'));
			if (row) {
				row.setAttribute('data-status', 'cancelled');
				var badge = row.querySelector('.sb-badge');
				if (badge) {
					badge.className = 'sb-badge is-cancelled';
					badge.textContent = 'Cancelled';
				}
				var actions = row.querySelector('.sb-actions');
				if (actions) {
					actions.innerHTML = '<button type="button" class="theme-btn theme-btn-outline sd-action-btn sb-open-details">View Details</button>';
				}
			}
			applyFilter(currentFilter);
			refreshStats();
		}).catch(function () {
			toast(i18n('error'));
			btn.disabled = false;
		});
	}

	function refreshStats() {
		ajax(cfg.actions && cfg.actions.history || 'gmm_booking_flow_history', {}).then(function (json) {
			if (!json || !json.success || !json.data || !json.data.stats) {
				return;
			}
			var s = json.data.stats;
			var map = {
				'sb-stat-total': s.total,
				'sb-stat-upcoming': s.upcoming,
				'sb-stat-completed': s.completed,
				'sb-stat-cancelled': s.cancelled
			};
			Object.keys(map).forEach(function (id) {
				var el = $(id);
				if (el && typeof map[id] !== 'undefined') {
					el.textContent = String(map[id]);
					el.setAttribute('data-count', String(map[id]));
				}
			});
		}).catch(function () {
			/* ignore */
		});
	}

	function init() {
		document.querySelectorAll('.sb-tabs .sl-tab').forEach(function (tab) {
			tab.addEventListener('click', function () {
				document.querySelectorAll('.sb-tabs .sl-tab').forEach(function (t) {
					t.classList.remove('is-active');
					t.setAttribute('aria-selected', 'false');
				});
				tab.classList.add('is-active');
				tab.setAttribute('aria-selected', 'true');
				applyFilter(tab.getAttribute('data-filter') || 'all');
			});
		});

		document.addEventListener('click', function (e) {
			var detailsBtn = e.target.closest('.sb-open-details');
			if (detailsBtn) {
				openDetails(detailsBtn.closest('.sb-row'));
				return;
			}
			var cancelBtn = e.target.closest('.sb-cancel-btn');
			if (cancelBtn) {
				cancelBooking(cancelBtn);
			}
		});

		applyFilter('all');
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(window, document);
