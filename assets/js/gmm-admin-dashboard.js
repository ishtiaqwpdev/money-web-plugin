/**
 * Gospel Music Mastery — Admin dashboard interactions
 * Counters, booking donut, approval AJAX. Design unchanged.
 */
(function (window, document) {
	'use strict';

	var dash = window.GMM_ADMIN_DASH || {};
	var charts = dash.charts || {};

	function showToast(message, isError) {
		var toast = document.getElementById('ad-toast');
		var toastText = document.getElementById('ad-toast-text');
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

	function formatValue(value, format) {
		var text = Number(value || 0).toLocaleString('en-US');
		return format === 'currency' ? '$' + text : text;
	}

	function runCounter(el) {
		var target = parseInt(el.getAttribute('data-count'), 10) || 0;
		var format = el.getAttribute('data-format');
		var duration = 1200;
		var startTime = null;

		function step(timestamp) {
			if (startTime === null) {
				startTime = timestamp;
			}
			var progress = Math.min((timestamp - startTime) / duration, 1);
			var eased = 1 - Math.pow(1 - progress, 3);
			el.textContent = formatValue(Math.round(target * eased), format);
			if (progress < 1) {
				window.requestAnimationFrame(step);
			}
		}
		window.requestAnimationFrame(step);
	}

	function animateDonut(donut) {
		var cEnd = parseFloat(donut.getAttribute('data-completed-end') || '0');
		var pEnd = parseFloat(donut.getAttribute('data-pending-end') || '0');
		cEnd = Math.max(0, Math.min(100, cEnd));
		pEnd = Math.max(cEnd, Math.min(100, pEnd));
		donut.style.background =
			'conic-gradient(var(--gospel-orange) 0 ' +
			cEnd +
			'%, #f0b429 ' +
			cEnd +
			'% ' +
			pEnd +
			'%, #c0392b ' +
			pEnd +
			'% 100%)';
		donut.classList.add('is-visible');
	}

	function initCountersAndDonut() {
		var animated = typeof WeakSet !== 'undefined' ? new WeakSet() : null;

		function reveal(el) {
			if (animated) {
				if (animated.has(el)) {
					return;
				}
				animated.add(el);
			}
			if (el.classList.contains('ad-counter')) {
				runCounter(el);
			} else if (el.classList.contains('ad-donut')) {
				animateDonut(el);
			}
		}

		var targets = document.querySelectorAll('.gmm-wrapper .ad-counter, .gmm-wrapper .ad-donut');
		if ('IntersectionObserver' in window) {
			var observer = new IntersectionObserver(
				function (entries) {
					entries.forEach(function (entry) {
						if (entry.isIntersecting) {
							reveal(entry.target);
							observer.unobserve(entry.target);
						}
					});
				},
				{ threshold: 0.25 }
			);
			targets.forEach(function (el) {
				observer.observe(el);
			});
		} else {
			targets.forEach(reveal);
		}
	}

	function applyRealCharts() {
		if (!window.GMMCharts) {
			return;
		}

		var revenue = charts.revenue || {};
		var growth = charts.growth || {};
		var platform = charts.platform || {};

		if (revenue.labels && revenue.datasets && revenue.datasets[0] && document.getElementById('gmm-admin-revenue')) {
			if (typeof window.GMMCharts.buildAreaChart === 'function') {
				window.GMMCharts.buildAreaChart(
					'gmm-admin-revenue',
					revenue.labels,
					revenue.datasets[0].data || [],
					revenue.datasets[0].label || 'Monthly Revenue',
					true
				);
			}
		}

		if (growth.labels && growth.datasets && document.getElementById('gmm-admin-user-growth')) {
			if (typeof window.GMMCharts.buildBarChart === 'function') {
				var sets = (growth.datasets || []).map(function (ds, idx) {
					return {
						label: ds.label || 'Series ' + (idx + 1),
						data: ds.data || [],
						color: idx === 0 ? '#FFA500' : '#E08E00'
					};
				});
				window.GMMCharts.buildBarChart('gmm-admin-user-growth', growth.labels, sets);
			}
		}

		if (platform.labels && platform.datasets && platform.datasets[0] && document.getElementById('gmm-admin-platform')) {
			if (typeof window.GMMCharts.buildDoughnutChart === 'function') {
				window.GMMCharts.buildDoughnutChart(
					'gmm-admin-platform',
					platform.labels,
					platform.datasets[0].data || [],
					['#FFA500', '#E08E00', 'rgba(255,165,0,0.45)'],
					'Total Records'
				);
			}
		}

		var totalEl = document.querySelector('.ad-chart-card .ad-chart-total');
		if (totalEl && typeof revenue.total !== 'undefined') {
			totalEl.textContent = '$' + Number(revenue.total || 0).toLocaleString('en-US');
		}
	}

	function ajaxAction(action, payload) {
		var body = new window.FormData();
		body.append('action', action);
		body.append('nonce', dash.nonce || (window.GMM_DATA && window.GMM_DATA.nonce) || '');
		Object.keys(payload || {}).forEach(function (key) {
			body.append(key, payload[key]);
		});

		return window.fetch(dash.ajaxUrl || (window.GMM_DATA && window.GMM_DATA.ajax_url) || '', {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		}).then(function (res) {
			return res.json();
		});
	}

	function resolveRow(row, isApproved) {
		var badge = row.querySelector('.sb-badge');
		var name = row.getAttribute('data-name') || 'Submission';
		var type = row.getAttribute('data-type');
		var id = row.getAttribute('data-id');
		var action =
			type === 'teacher'
				? isApproved
					? 'gmm_approve_teacher'
					: 'gmm_reject_teacher'
				: isApproved
					? 'gmm_approve_class'
					: 'gmm_reject_class';
		var payload =
			type === 'teacher'
				? { teacher_id: id }
				: { class_id: id };

		row.querySelectorAll('.ad-approve-btn, .ad-reject-btn').forEach(function (btn) {
			btn.disabled = true;
		});

		ajaxAction(action, payload)
			.then(function (json) {
				if (!json || !json.success) {
					throw new Error((json && json.data && json.data.message) || (dash.i18n && dash.i18n.error));
				}
				if (badge) {
					badge.classList.remove('is-pending');
					badge.classList.add(isApproved ? 'is-confirmed' : 'is-cancelled');
					badge.textContent = isApproved ? 'Approved' : 'Rejected';
				}
				row.classList.add('is-resolved');
				showToast(
					name + ' ' + (isApproved ? (dash.i18n && dash.i18n.approved) || 'approved.' : (dash.i18n && dash.i18n.rejected) || 'rejected.'),
					!isApproved
				);
			})
			.catch(function (err) {
				row.querySelectorAll('.ad-approve-btn, .ad-reject-btn').forEach(function (btn) {
					btn.disabled = false;
				});
				showToast((err && err.message) || (dash.i18n && dash.i18n.error) || 'Error', true);
			});
	}

	function initApprovals() {
		var filterTabs = document.querySelectorAll('.ad-filter-tabs .sl-tab');
		var rows = document.querySelectorAll('#ad-approvals-body .ad-row');
		var emptyState = document.getElementById('ad-approvals-empty');

		function applyFilter(filter) {
			var visible = 0;
			rows.forEach(function (row) {
				if (row.classList.contains('is-resolved')) {
					row.hidden = true;
					return;
				}
				var match = filter === 'all' || row.getAttribute('data-type') === filter;
				row.hidden = !match;
				if (match) {
					visible++;
				}
			});
			if (emptyState) {
				emptyState.hidden = visible > 0;
			}
		}

		filterTabs.forEach(function (tab) {
			tab.addEventListener('click', function () {
				filterTabs.forEach(function (t) {
					t.classList.remove('is-active');
					t.setAttribute('aria-selected', 'false');
				});
				tab.classList.add('is-active');
				tab.setAttribute('aria-selected', 'true');
				applyFilter(tab.getAttribute('data-filter'));
			});
		});

		var reviewModalEl = document.getElementById('ad-review-modal');
		var reviewModal = reviewModalEl && window.bootstrap ? new window.bootstrap.Modal(reviewModalEl) : null;
		var reviewName = document.getElementById('ad-review-name');
		var reviewType = document.getElementById('ad-review-type');

		rows.forEach(function (row) {
			var approve = row.querySelector('.ad-approve-btn');
			var reject = row.querySelector('.ad-reject-btn');
			var view = row.querySelector('.ad-view-btn');

			if (approve) {
				approve.addEventListener('click', function () {
					resolveRow(row, true);
				});
			}
			if (reject) {
				reject.addEventListener('click', function () {
					resolveRow(row, false);
				});
			}
			if (view) {
				view.addEventListener('click', function () {
					if (reviewName) {
						reviewName.textContent = row.getAttribute('data-name') || '';
					}
					var typeCell = row.querySelector('td[data-label="Type"]');
					if (reviewType && typeCell) {
						reviewType.textContent = typeCell.textContent.trim();
					}
					var url = row.getAttribute('data-view-url');
					if (url && !reviewModal) {
						window.location.href = url;
						return;
					}
					if (reviewModal) {
						reviewModal.show();
					} else if (url) {
						window.location.href = url;
					}
				});
			}
		});
	}

	function boot() {
		if (!document.querySelector('.gmm-wrapper.gmm-admin')) {
			return;
		}
		initCountersAndDonut();
		initApprovals();
		// Prefer live data after Chart.js auto-init demo pass.
		window.setTimeout(applyRealCharts, 50);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})(window, document);
