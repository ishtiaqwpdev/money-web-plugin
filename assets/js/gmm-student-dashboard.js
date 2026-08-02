/**
 * Gospel Music Mastery — Student dashboard
 * Applies real Chart.js data + AJAX refresh hooks. Design unchanged.
 */
(function (window, document) {
	'use strict';

	var dash = window.GMM_STUDENT_DASH || {};
	var charts = dash.charts || {};

	function applyRealCharts() {
		if (!window.GMMCharts) {
			return;
		}

		var learning = charts.learning || charts.monthly || {};
		var lessons = charts.lessons || {};
		var practice = charts.practice || {};

		if (learning.labels && learning.datasets && learning.datasets[0] && document.getElementById('gmm-student-learning')) {
			if (typeof window.GMMCharts.buildLineChart === 'function') {
				window.GMMCharts.buildLineChart(
					'gmm-student-learning',
					learning.labels,
					[
						{
							label: learning.datasets[0].label || 'Learning Activity',
							data: learning.datasets[0].data || [],
							color: '#FFA500'
						}
					]
				);
			}
		}

		if (lessons.labels && lessons.datasets && lessons.datasets[0] && document.getElementById('gmm-student-lesson-status')) {
			if (typeof window.GMMCharts.buildDoughnutChart === 'function') {
				window.GMMCharts.buildDoughnutChart(
					'gmm-student-lesson-status',
					lessons.labels,
					lessons.datasets[0].data || [],
					['#22C55E', '#B45309', '#3B82F6'],
					'Lessons'
				);
			}
		}

		if (practice.labels && practice.datasets && practice.datasets[0] && document.getElementById('gmm-student-practice')) {
			if (typeof window.GMMCharts.buildBarChart === 'function') {
				window.GMMCharts.buildBarChart('gmm-student-practice', practice.labels, [
					{
						label: practice.datasets[0].label || 'Practice Hours',
						data: practice.datasets[0].data || [],
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

	window.GMMStudentDash = {
		refreshStats: function () {
			var action = (dash.actions && dash.actions.stats) || 'gmm_student_dashboard_stats';
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
			var action = (dash.actions && dash.actions.bookings) || 'gmm_student_dashboard_bookings';
			return ajaxAction(action);
		},
		refreshFavourites: function () {
			var action = (dash.actions && dash.actions.favourites) || 'gmm_student_dashboard_favourites';
			return ajaxAction(action);
		},
		applyCharts: applyRealCharts
	};

	function boot() {
		window.setTimeout(applyRealCharts, 50);
		window.setTimeout(applyRealCharts, 400);
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})(window, document);
