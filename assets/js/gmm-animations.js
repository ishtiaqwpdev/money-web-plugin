/**
 * Gospel Music Mastery — Light motion helpers (namespace only)
 */
(function (window) {
	'use strict';

	window.GMM = window.GMM || {};
	window.GMM.animations = window.GMM.animations || {};

	window.GMM.animations.init = function () {
		// Future: entrance transitions scoped to .gmm-wrapper only.
	};

	window.GMM.ready(function () {
		if (document.querySelector('.gmm-wrapper, .gmm-frontend')) {
			window.GMM.animations.init();
		}
	});
})(window);
