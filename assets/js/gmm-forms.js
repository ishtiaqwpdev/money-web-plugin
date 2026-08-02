/**
 * Gospel Music Mastery — Form helpers (namespace only; no AJAX yet)
 */
(function (window) {
	'use strict';

	window.GMM = window.GMM || {};
	window.GMM.forms = window.GMM.forms || {};

	window.GMM.forms.init = function () {
		// Future: validation, AJAX submit, nonce handling.
	};

	window.GMM.ready(function () {
		if (document.querySelector('.gmm-form, .gmm-plugin form')) {
			window.GMM.forms.init();
		}
	});
})(window);
