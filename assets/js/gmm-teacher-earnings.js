/**
 * Gospel Music Mastery — Teacher Earnings & Withdrawals
 * Request withdrawals / refresh balances via AJAX. Frozen UI preserved.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_TEACHER_EARNINGS || {};
	var currentFilter = 'all';

	function ajax(action, formData) {
		formData = formData || new window.FormData();
		formData.append('action', action);
		formData.append(cfg.nonceField || 'gmm_teacher_earnings_nonce', cfg.nonce || '');
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

	function money(val) {
		var n = Number(val);
		if (window.isNaN(n)) {
			n = 0;
		}
		return '$' + n.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

	function showAlert(type, message) {
		var err = document.getElementById('withdrawal-error');
		var ok = document.getElementById('withdrawal-success');
		var errText = document.getElementById('withdrawal-error-text');
		var okText = document.getElementById('withdrawal-success-text');
		if (err) {
			err.hidden = type !== 'error';
		}
		if (ok) {
			ok.hidden = type !== 'success';
		}
		if (type === 'error' && errText) {
			errText.textContent = message || i18n('error');
		}
		if (type === 'success' && okText) {
			okText.textContent = message || i18n('success');
		}
	}

	function updateEarnings(earnings) {
		if (!earnings) {
			return;
		}
		cfg.earnings = earnings;
		var map = {
			'total-earnings-display': earnings.total_earnings,
			'available-balance-display': earnings.available_balance,
			'withdrawn-amount-display': earnings.withdrawn_amount,
			'pending-withdrawals-display': earnings.pending_withdrawals
		};
		Object.keys(map).forEach(function (id) {
			var el = document.getElementById(id);
			if (el && typeof map[id] !== 'undefined') {
				el.textContent = money(map[id]);
			}
		});
		var amountInput = document.getElementById('withdrawal-amount');
		if (amountInput && typeof earnings.available_balance !== 'undefined') {
			amountInput.setAttribute('max', String(earnings.available_balance));
		}
		if (amountInput && typeof earnings.min_withdrawal !== 'undefined') {
			amountInput.setAttribute('min', String(earnings.min_withdrawal));
		}
	}

	function renderHistory(rows) {
		var tbody = document.getElementById('withdrawal-tbody');
		var empty = document.getElementById('withdrawal-empty');
		var wrap = document.getElementById('withdrawal-table-wrap');
		if (!tbody) {
			return;
		}
		rows = Array.isArray(rows) ? rows : [];
		if (!rows.length) {
			tbody.innerHTML = '';
			if (empty) {
				empty.hidden = false;
			}
			if (wrap) {
				wrap.hidden = true;
			}
			return;
		}
		if (wrap) {
			wrap.hidden = false;
		}
		if (empty) {
			empty.hidden = true;
		}
		tbody.innerHTML = rows.map(function (wd) {
			var filter = wd.ui_filter || 'pending';
			var badge = wd.badge_class || 'is-pending';
			var date = wd.date_label || '';
			var amount = wd.amount_label || money(wd.amount);
			var method = wd.payment_method || '';
			var status = wd.status_label || '';
			var note = wd.admin_response || wd.admin_note || '';
			return (
				'<tr data-status="' + escapeAttr(filter) + '" data-id="' + escapeAttr(wd.id || 0) + '">' +
				'<td data-label="Date">' + escapeHtml(date) + '</td>' +
				'<td data-label="Amount"><strong>' + escapeHtml(amount) + '</strong></td>' +
				'<td data-label="Payment Method">' + escapeHtml(method) + '</td>' +
				'<td data-label="Status"><span class="td-badge ' + escapeAttr(badge) + '">' + escapeHtml(status) + '</span></td>' +
				'<td data-label="Action">' +
				'<button type="button" class="theme-btn theme-btn-outline td-action-btn withdrawal-view-btn"' +
				' data-date="' + escapeAttr(date) + '"' +
				' data-amount="' + escapeAttr(amount) + '"' +
				' data-method="' + escapeAttr(method) + '"' +
				' data-status="' + escapeAttr(status) + '"' +
				' data-note="' + escapeAttr(note) + '">View</button>' +
				'</td></tr>'
			);
		}).join('');
		applyFilter(currentFilter);
	}

	function applyFilter(filter) {
		currentFilter = filter || 'all';
		var rows = document.querySelectorAll('#withdrawal-tbody tr');
		var visible = 0;
		rows.forEach(function (row) {
			var status = row.getAttribute('data-status') || '';
			var show = currentFilter === 'all' || status === currentFilter;
			row.hidden = !show;
			if (show) {
				visible += 1;
			}
		});
		var empty = document.getElementById('withdrawal-empty');
		var wrap = document.getElementById('withdrawal-table-wrap');
		if (!rows.length) {
			if (empty) {
				empty.hidden = false;
			}
			if (wrap) {
				wrap.hidden = true;
			}
			return;
		}
		if (wrap) {
			wrap.hidden = visible === 0;
		}
		if (empty) {
			empty.hidden = visible !== 0;
			if (visible === 0) {
				empty.textContent = i18n('empty');
			}
		}
	}

	function openModal(btn) {
		var dateEl = document.getElementById('modal-wd-date');
		var amountEl = document.getElementById('modal-wd-amount');
		var methodEl = document.getElementById('modal-wd-method');
		var statusEl = document.getElementById('modal-wd-status');
		var noteEl = document.getElementById('modal-wd-note');
		if (dateEl) {
			dateEl.textContent = btn.getAttribute('data-date') || '—';
		}
		if (amountEl) {
			amountEl.textContent = btn.getAttribute('data-amount') || '—';
		}
		if (methodEl) {
			methodEl.textContent = btn.getAttribute('data-method') || '—';
		}
		if (statusEl) {
			statusEl.textContent = btn.getAttribute('data-status') || '—';
		}
		if (noteEl) {
			noteEl.textContent = btn.getAttribute('data-note') || '—';
		}
		var modalEl = document.getElementById('withdrawal-details-modal');
		if (modalEl && window.bootstrap && window.bootstrap.Modal) {
			window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}
	}

	function onSubmit(e) {
		e.preventDefault();
		var form = e.target;
		var amountInput = document.getElementById('withdrawal-amount');
		var methodInput = document.getElementById('withdrawal-method');
		var accountInput = document.getElementById('withdrawal-account-details');
		var amount = amountInput ? parseFloat(amountInput.value, 10) : 0;
		var min = typeof cfg.minAmount !== 'undefined' ? Number(cfg.minAmount) : 50;
		var available = cfg.earnings && typeof cfg.earnings.available_balance !== 'undefined'
			? Number(cfg.earnings.available_balance)
			: Infinity;

		if (!amount || amount <= 0 || window.isNaN(amount)) {
			showAlert('error', i18n('invalid'));
			return;
		}
		if (amount < min) {
			showAlert('error', i18n('invalid'));
			return;
		}
		if (amount > available) {
			showAlert('error', i18n('invalid'));
			return;
		}

		var btn = document.getElementById('request-withdrawal-btn');
		if (btn) {
			btn.disabled = true;
		}

		var fd = new window.FormData(form);
		if (accountInput && !fd.get('account_details')) {
			fd.append('account_details', accountInput.value || '');
		}
		if (methodInput && !fd.get('payment_method')) {
			fd.append('payment_method', methodInput.value || 'Stripe');
		}

		ajax(actionName('request'), fd)
			.then(function (json) {
				if (!json || !json.success) {
					var msg = (json && json.data && json.data.message) || i18n('error');
					showAlert('error', msg);
					return;
				}
				showAlert('success', (json.data && json.data.message) || i18n('success'));
				if (json.data && json.data.earnings) {
					updateEarnings(json.data.earnings);
				}
				if (json.data && json.data.withdrawal_history) {
					renderHistory(json.data.withdrawal_history);
				}
				form.reset();
				if (methodInput) {
					methodInput.value = 'Stripe';
				}
			})
			.catch(function () {
				showAlert('error', i18n('error'));
			})
			.finally(function () {
				if (btn) {
					btn.disabled = false;
				}
			});
	}

	function refresh() {
		ajax(actionName('refresh'), new window.FormData())
			.then(function (json) {
				if (!json || !json.success || !json.data) {
					return;
				}
				if (json.data.earnings) {
					updateEarnings(json.data.earnings);
				}
				if (json.data.withdrawal_history) {
					renderHistory(json.data.withdrawal_history);
				}
			})
			.catch(function () {
				/* silent refresh failure */
			});
	}

	function bind() {
		var form = document.getElementById('withdrawal-request-form');
		if (form) {
			form.addEventListener('submit', onSubmit);
		}

		document.querySelectorAll('.booking-tab[data-filter]').forEach(function (tab) {
			tab.addEventListener('click', function () {
				document.querySelectorAll('.booking-tab[data-filter]').forEach(function (t) {
					t.classList.remove('active');
					t.setAttribute('aria-selected', 'false');
				});
				tab.classList.add('active');
				tab.setAttribute('aria-selected', 'true');
				applyFilter(tab.getAttribute('data-filter') || 'all');
			});
		});

		document.addEventListener('click', function (e) {
			var btn = e.target.closest('.withdrawal-view-btn');
			if (btn) {
				e.preventDefault();
				openModal(btn);
			}
		});

		if (cfg.earnings) {
			updateEarnings(cfg.earnings);
		}
		applyFilter('all');
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bind);
	} else {
		bind();
	}

	window.GMMTeacherEarnings = {
		refresh: refresh,
		updateEarnings: updateEarnings,
		renderHistory: renderHistory
	};
})(window, document);
