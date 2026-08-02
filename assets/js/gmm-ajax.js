/**
 * Gospel Music Mastery — AJAX helper (foundation)
 *
 * Depends on GMM_DATA from wp_localize_script:
 * { ajax_url, nonce, current_user_id / user_id }
 */
(function (window) {
	'use strict';

	window.GMM = window.GMM || {};
	window.GMM.ajax = window.GMM.ajax || {};

	var config = window.GMM.config || window.GMM_DATA || {};
	var pending = {};

	function getAjaxUrl() {
		return config.ajax_url || (window.GMM_DATA && window.GMM_DATA.ajax_url) || '';
	}

	function getNonce() {
		return config.nonce || (window.GMM_DATA && window.GMM_DATA.nonce) || '';
	}

	/**
	 * Set button loading state without changing design classes beyond disabled + data attr.
	 *
	 * @param {HTMLElement|null} button
	 * @param {boolean} isLoading
	 */
	window.GMM.ajax.setLoading = function (button, isLoading) {
		if (!button) {
			return;
		}
		if (isLoading) {
			button.setAttribute('disabled', 'disabled');
			button.setAttribute('aria-busy', 'true');
			button.setAttribute('data-gmm-loading', '1');
		} else {
			button.removeAttribute('disabled');
			button.removeAttribute('aria-busy');
			button.removeAttribute('data-gmm-loading');
		}
	};

	/**
	 * Show a message on an existing alert node if present (no new design).
	 *
	 * @param {string} selector
	 * @param {string} message
	 * @param {boolean} isError
	 */
	window.GMM.ajax.notify = function (selector, message, isError) {
		var el = selector ? document.querySelector(selector) : null;
		if (!el) {
			return;
		}
		el.hidden = false;
		var text = el.querySelector('[id$="-text"], span');
		if (text) {
			text.textContent = message || '';
		} else {
			el.textContent = message || '';
		}
		if (typeof isError === 'boolean') {
			el.classList.toggle('gospel-alert-error', isError);
			el.classList.toggle('gospel-alert-success', !isError);
		}
	};

	/**
	 * POST to admin-ajax.php
	 *
	 * @param {string} action WP action without prefix duplication (e.g. gmm_search_teachers)
	 * @param {Object} data Extra fields
	 * @param {Object} options { button, dedupeKey }
	 * @returns {Promise<Object>}
	 */
	window.GMM.ajax.request = function (action, data, options) {
		options = options || {};
		data = data || {};

		var key = options.dedupeKey || action;
		if (pending[key]) {
			return pending[key];
		}

		var url = getAjaxUrl();
		if (!url) {
			return Promise.reject(new Error('AJAX URL missing'));
		}

		var body = new FormData();
		body.append('action', action);
		body.append('nonce', getNonce());

		Object.keys(data).forEach(function (k) {
			if (data[k] === undefined || data[k] === null) {
				return;
			}
			body.append(k, data[k]);
		});

		window.GMM.ajax.setLoading(options.button || null, true);

		pending[key] = fetch(url, {
			method: 'POST',
			credentials: 'same-origin',
			body: body
		})
			.then(function (res) {
				return res.json().then(function (json) {
					return { ok: res.ok, json: json };
				});
			})
			.then(function (payload) {
				var json = payload.json || {};
				if (json.success) {
					return json.data || {};
				}
				var msg =
					(json.data && json.data.message) ||
					json.message ||
					'Request failed';
				var err = new Error(msg);
				err.payload = json;
				throw err;
			})
			.finally(function () {
				delete pending[key];
				window.GMM.ajax.setLoading(options.button || null, false);
			});

		return pending[key];
	};

	/** Convenience wrappers */
	window.GMM.ajax.searchTeachers = function (params, options) {
		return window.GMM.ajax.request('gmm_search_teachers', params || {}, options);
	};

	window.GMM.ajax.searchStudents = function (params, options) {
		return window.GMM.ajax.request('gmm_search_students', params || {}, options);
	};

	window.GMM.ajax.searchClasses = function (params, options) {
		return window.GMM.ajax.request('gmm_search_classes', params || {}, options);
	};

	window.GMM.ajax.toggleFavourite = function (teacherId, remove, options) {
		return window.GMM.ajax.request(
			'gmm_toggle_favourite',
			{ teacher_id: teacherId, remove: remove ? 1 : 0 },
			options
		);
	};

	window.GMM.ajax.createBooking = function (payload, options) {
		return window.GMM.ajax.request('gmm_create_booking', payload || {}, options);
	};

	window.GMM.ajax.cancelBooking = function (bookingId, options) {
		return window.GMM.ajax.request(
			'gmm_cancel_booking',
			{ booking_id: bookingId },
			options
		);
	};
})(window);
