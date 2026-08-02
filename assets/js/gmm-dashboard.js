/**
 * Gospel Music Mastery — Dashboard helpers (namespace only; no UI yet)
 */
(function (window) {
	'use strict';

	window.GMM = window.GMM || {};
	window.GMM.dashboard = window.GMM.dashboard || {};

	window.GMM.dashboard.init = function () {
		// Future: dashboard widgets, Chart.js init, etc.
	};

	window.GMM.ready(function () {
		if (document.querySelector('.gmm-dashboard')) {
			window.GMM.dashboard.init();
		}
	});
})(window);
