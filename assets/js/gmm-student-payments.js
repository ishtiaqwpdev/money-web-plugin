/**
 * Gospel Music Mastery — Student payment history
 * Status/date filters + receipt/details via AJAX. Frozen UI preserved.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_STUDENT_PAYMENTS || {};
	var state = {
		status: 'all',
		period: 'all',
		pending: null
	};

	function $(id) {
		return document.getElementById(id);
	}

	function i18n(key, fallback) {
		return (cfg.i18n && cfg.i18n[key]) || fallback || '';
	}

	function ajax(action, fields) {
		var fd = new window.FormData();
		fd.append('action', action);
		fd.append(cfg.nonceField || 'gmm_student_payments_nonce', cfg.nonce || '');
		Object.keys(fields || {}).forEach(function (key) {
			fd.append(key, String(fields[key]));
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

	function toast(msg) {
		var box = $('sp-toast');
		var text = $('sp-toast-text');
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

	function updateStats(stats) {
		if (!stats) {
			return;
		}
		var spent = Math.round(Number(stats.total_spent || 0));
		var map = {
			'sp-stat-spent': spent,
			'sp-stat-completed': stats.completed_count,
			'sp-stat-pending': stats.pending_count,
			'sp-stat-refunds': stats.refund_count
		};
		Object.keys(map).forEach(function (id) {
			var el = $(id);
			if (el && typeof map[id] !== 'undefined') {
				el.textContent = String(map[id]);
				el.setAttribute('data-count', String(map[id]));
			}
		});
	}

	function setEmpty(isEmpty) {
		var empty = $('sp-empty');
		var wrap = $('sp-table-wrap');
		if (empty) {
			empty.hidden = !isEmpty;
		}
		if (wrap) {
			wrap.hidden = isEmpty;
		}
	}

	function loadList() {
		var body = $('sp-table-body');
		if (body) {
			body.innerHTML = '<tr><td colspan="7">' + i18n('loading', 'Loading…') + '</td></tr>';
		}

		ajax(cfg.actions && cfg.actions.list || 'gmm_student_payments_list', {
			status: state.status,
			period: state.period,
			limit: 100
		}).then(function (json) {
			if (!json || !json.success) {
				toast((json && json.data && json.data.message) || i18n('error'));
				setEmpty(true);
				if (body) {
					body.innerHTML = '';
				}
				return;
			}
			var html = (json.data && json.data.html) || '';
			var rows = (json.data && json.data.rows) || [];
			if (body) {
				body.innerHTML = html;
			}
			setEmpty(!rows.length);
			updateStats(json.data.stats);
		}).catch(function () {
			toast(i18n('error'));
			setEmpty(true);
		});
	}

	function fillReceiptFromRow(row) {
		if (!row) {
			return;
		}
		var map = {
			'sp-modal-id': 'data-id',
			'sp-modal-date': 'data-date',
			'sp-modal-student': 'data-student',
			'sp-modal-teacher': 'data-teacher',
			'sp-modal-lesson': 'data-lesson',
			'sp-modal-amount': 'data-amount',
			'sp-modal-method': 'data-method',
			'sp-modal-booking': 'data-booking-id',
			'sp-modal-booking-status': 'data-booking-status',
			'sp-modal-refund': 'data-refund'
		};
		Object.keys(map).forEach(function (id) {
			var el = $(id);
			if (el) {
				var val = row.getAttribute(map[id]) || '—';
				el.textContent = val === '0' ? '—' : val;
			}
		});
		var statusEl = $('sp-modal-status');
		if (statusEl) {
			var st = row.getAttribute('data-status') || '';
			statusEl.textContent = st ? st.charAt(0).toUpperCase() + st.slice(1) : '—';
		}
	}

	function fillTimeline(timeline) {
		var wrap = $('sp-modal-timeline-wrap');
		var list = $('sp-modal-timeline');
		if (!list || !wrap) {
			return;
		}
		list.innerHTML = '';
		if (!timeline || !timeline.length) {
			wrap.hidden = true;
			return;
		}
		wrap.hidden = false;
		timeline.forEach(function (item) {
			var li = document.createElement('li');
			var span = document.createElement('span');
			span.textContent = item.label || '';
			var strong = document.createElement('strong');
			strong.textContent = item.date || '—';
			li.appendChild(span);
			li.appendChild(strong);
			list.appendChild(li);
		});
	}

	function openReceipt(paymentId, row) {
		fillReceiptFromRow(row);

		ajax(cfg.actions && cfg.actions.details || 'gmm_student_payments_details', {
			payment_id: paymentId
		}).then(function (json) {
			if (json && json.success && json.data && json.data.transaction) {
				var tx = json.data.transaction;
				var receipt = tx.receipt || {};
				var payment = tx.payment || {};
				var booking = tx.booking || {};

				var set = function (id, val) {
					var el = $(id);
					if (el) {
						el.textContent = val || '—';
					}
				};

				set('sp-modal-id', receipt.transaction_id || payment.transaction_id);
				set('sp-modal-booking', booking.id ? String(booking.id) : '—');
				set('sp-modal-date', receipt.date_label || '');
				set('sp-modal-student', receipt.student_name || '');
				set('sp-modal-teacher', receipt.teacher_name || '');
				set('sp-modal-lesson', receipt.class_name || '');
				set('sp-modal-amount', receipt.amount_label || '');
				set('sp-modal-method', receipt.payment_method || payment.payment_method || '');
				set('sp-modal-status', payment.payment_status || '');
				set('sp-modal-booking-status', booking.booking_status || '');
				set('sp-modal-refund', (tx.refund && tx.refund.label) || '—');
				fillTimeline(tx.timeline || []);
			}
		}).catch(function () {
			/* row data already shown */
		});

		var modalEl = $('sp-receipt-modal');
		if (modalEl && window.bootstrap && window.bootstrap.Modal) {
			window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
		} else if (modalEl) {
			modalEl.classList.add('show');
			modalEl.style.display = 'block';
		}
	}

	function initTabs() {
		document.querySelectorAll('.sp-tabs .sl-tab').forEach(function (tab) {
			tab.addEventListener('click', function () {
				document.querySelectorAll('.sp-tabs .sl-tab').forEach(function (t) {
					t.classList.remove('is-active');
					t.setAttribute('aria-selected', 'false');
				});
				tab.classList.add('is-active');
				tab.setAttribute('aria-selected', 'true');
				state.status = tab.getAttribute('data-filter') || 'all';
				loadList();
			});
		});
	}

	function initDateFilter() {
		var select = $('sp-date-filter');
		if (!select) {
			return;
		}
		if (cfg.filters && cfg.filters.period) {
			select.value = cfg.filters.period;
			state.period = cfg.filters.period;
		}
		select.addEventListener('change', function () {
			state.period = select.value || 'all';
			loadList();
		});
	}

	function initActions() {
		document.addEventListener('click', function (e) {
			var receiptBtn = e.target.closest('.sp-view-receipt');
			if (receiptBtn) {
				var row = receiptBtn.closest('.sp-row');
				var id = parseInt(receiptBtn.getAttribute('data-payment-id') || (row && row.getAttribute('data-payment-id')) || '0', 10);
				if (id) {
					openReceipt(id, row);
				}
				return;
			}

			var invoiceBtn = e.target.closest('.sp-download-invoice');
			if (invoiceBtn) {
				var row2 = invoiceBtn.closest('.sp-row');
				var pid = parseInt(invoiceBtn.getAttribute('data-payment-id') || (row2 && row2.getAttribute('data-payment-id')) || '0', 10);
				if (pid) {
					openReceipt(pid, row2);
					toast(i18n('receipt', 'Receipt ready (print available). PDF export coming later.'));
				}
			}
		});

		var printBtn = $('sp-print-receipt');
		if (printBtn) {
			printBtn.addEventListener('click', function () {
				window.print();
			});
		}

		var manageBtn = $('sp-manage-method');
		if (manageBtn) {
			manageBtn.addEventListener('click', function () {
				toast('Payment method management will connect when the gateway is enabled.');
			});
		}

		var billingForm = $('sp-billing-form');
		if (billingForm) {
			billingForm.addEventListener('submit', function (e) {
				e.preventDefault();
				var success = $('sp-billing-success');
				var error = $('sp-billing-error');
				if (error) {
					error.hidden = true;
				}
				if (success) {
					success.hidden = false;
				}
				toast('Billing details are display-only until payment gateway setup.');
			});
		}
	}

	function init() {
		initTabs();
		initDateFilter();
		initActions();
		setEmpty(!document.querySelectorAll('#sp-table-body .sp-row').length);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(window, document);
