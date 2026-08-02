/**
 * Gospel Music Mastery — Core JS namespace
 */
(function (window) {
	'use strict';

	window.GMM = window.GMM || {};

	window.GMM.config = window.GMM.config || {};

	if (typeof window.GMM_DATA === 'object' && window.GMM_DATA !== null) {
		window.GMM.config = window.GMM_DATA;
	}

	window.GMM.ready = function (callback) {
		if (typeof callback !== 'function') {
			return;
		}
		if (document.readyState === 'loading') {
			document.addEventListener('DOMContentLoaded', callback);
		} else {
			callback();
		}
	};
})(window);
