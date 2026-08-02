/**
 * Gospel Music Mastery — Teacher dashboard
 * Applies real Chart.js data + AJAX refresh hooks. Design unchanged.
 */
(function (window, document) {
	'use strict';

	var dash = window.GMM_TEACHER_DASH || {};
	var charts = dash.charts || {};

	function applyRealCharts() {
		if (!window.GMMCharts) {
			return;
		}

		var earnings = charts.earnings || {};
		var lessons = charts.lessons || {};
		var students = charts.students || {};

		if (earnings.labels && earnings.datasets && earnings.datasets[0] && document.getElementById('gmm-teacher-earnings')) {
			if (typeof window.GMMCharts.buildLineChart === 'function') {
				window.GMMCharts.buildLineChart(
					'gmm-teacher-earnings',
					earnings.labels,
					[
						{
							label: earnings.datasets[0].label || 'Monthly Earnings',
							data: earnings.datasets[0].data || [],
							color: '#FFA500'
						}
					],
					true
				);
			}
		}

		if (lessons.labels && lessons.datasets && lessons.datasets[0] && document.getElementById('gmm-teacher-lessons')) {
			if (typeof window.GMMCharts.buildDoughnutChart === 'function') {
				window.GMMCharts.buildDoughnutChart(
					'gmm-teacher-lessons',
					lessons.labels,
					lessons.datasets[0].data || [],
					['#22C55E', '#B45309', '#C0392B'],
					'Lessons'
				);
			}
		}

		if (students.labels && students.datasets && students.datasets[0] && document.getElementById('gmm-teacher-students')) {
			if (typeof window.GMMCharts.buildBarChart === 'function') {
				window.GMMCharts.buildBarChart('gmm-teacher-students', students.labels, [
					{
						label: students.datasets[0].label || 'New Students',
						data: students.datasets[0].data || [],
						color: '#FFA500'
					}
				]);
			}
		}
	}

	function ajaxAction(action) {
		var body = new window.FormData();
		body.append('action', action);
		body.append('nonce', dash.nonce || (window.GMM_DATA && window.GMM_DATA.nonce) || '');
		return window.fetch(dash.ajaxUrl || (window.GMM_DATA && window.GMM_DATA.ajax_url) || '', {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		}).then(function (res) {
			return res.json();
		});
	}

	/**
	 * Prepared refresh helpers for future live updates.
	 */
	window.GMMTeacherDash = {
		refreshStats: function () {
			var action = (dash.actions && dash.actions.stats) || 'gmm_teacher_dashboard_stats';
			return ajaxAction(action).then(function (json) {
				if (json && json.success && json.data) {
					if (json.data.charts) {
						charts = json.data.charts;
						dash.charts = charts;
						applyRealCharts();
					}
					if (json.data.stats) {
						dash.stats = json.data.stats;
					}
				}
				return json;
			});
		},
		refreshBookings: function () {
			var action = (dash.actions && dash.actions.bookings) || 'gmm_teacher_dashboard_bookings';
			return ajaxAction(action);
		},
		refreshEarnings: function () {
			var action = (dash.actions && dash.actions.earnings) || 'gmm_teacher_dashboard_earnings';
			return ajaxAction(action);
		},
		applyCharts: applyRealCharts
	};

	function boot() {
		/* Wait for demo autoInit intersection observers, then overwrite with real data. */
		window.setTimeout(applyRealCharts, 50);
		window.setTimeout(applyRealCharts, 400);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})(window, document);
