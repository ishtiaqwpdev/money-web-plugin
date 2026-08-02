/**
 * Gospel Music Mastery — Admin classes management
 * Filters, AJAX approve/reject/edit/feature/delete, profile modal, featured grid.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_ADMIN_CLASSES || {};
	var ajaxUrl = cfg.ajaxUrl || (window.GMM_DATA && window.GMM_DATA.ajax_url) || '';
	var nonce = cfg.nonce || (window.GMM_DATA && window.GMM_DATA.nonce) || '';
	var i18n = cfg.i18n || {};

	var STATUS_MAP = {
		draft: { label: 'Draft', className: 'is-inactive' },
		pending: { label: 'Pending', className: 'is-pending' },
		approved: { label: 'Approved', className: 'is-confirmed' },
		rejected: { label: 'Rejected', className: 'is-cancelled' }
	};

	function showToast(message, isError) {
		var toast = document.getElementById('ac-toast');
		var toastText = document.getElementById('ac-toast-text');
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

	function postAction(action, data) {
		var body = new window.FormData();
		body.append('action', action);
		body.append('nonce', nonce);
		Object.keys(data || {}).forEach(function (key) {
			var val = data[key];
			if (Array.isArray(val)) {
				val.forEach(function (item) {
					body.append(key + '[]', item);
				});
			} else if (val !== undefined && val !== null) {
				body.append(key, val);
			}
		});
		return window
			.fetch(ajaxUrl, { method: 'POST', credentials: 'same-origin', body: body })
			.then(function (res) {
				return res.json();
			});
	}

	function setRowStatus(row, status) {
		var meta = STATUS_MAP[status];
		if (!meta || !row) {
			return;
		}
		row.setAttribute('data-status', status);
		var badge = row.querySelector('.ac-status');
		if (badge) {
			badge.className = 'sb-badge ac-status ' + meta.className;
			badge.textContent = meta.label;
		}
	}

	function getClassId(row) {
		return parseInt(row.getAttribute('data-class-id') || '0', 10) || 0;
	}

	var activeRow = null;
	var modalEl = document.getElementById('ac-class-modal');
	var modal = modalEl && window.bootstrap ? new window.bootstrap.Modal(modalEl) : null;

	function setText(id, value) {
		var el = document.getElementById(id);
		if (el) {
			el.textContent = value || '';
		}
	}

	function fillModalFromRow(row) {
		activeRow = row;
		var img = document.getElementById('ac-modal-image');
		if (img) {
			img.src = row.getAttribute('data-image') || '';
			img.alt = row.getAttribute('data-title') || 'Class';
		}
		setText('ac-modal-name', row.getAttribute('data-title'));
		setText('ac-modal-teacher', row.getAttribute('data-teacher'));
		setText('ac-modal-rating', '★★★★★ ' + (row.getAttribute('data-rating') || ''));
		setText('ac-modal-category', row.getAttribute('data-category-label') || row.getAttribute('data-category'));
		setText('ac-modal-duration', row.getAttribute('data-duration'));
		setText('ac-modal-difficulty', row.getAttribute('data-difficulty-label') || row.getAttribute('data-difficulty'));
		setText('ac-modal-price', row.getAttribute('data-price'));
		setText('ac-modal-students', row.getAttribute('data-students'));
		setText('ac-modal-created', row.getAttribute('data-created'));
		setText('ac-modal-description', row.getAttribute('data-description'));

		var status = row.getAttribute('data-status');
		var badge = document.getElementById('ac-modal-status');
		var meta = STATUS_MAP[status];
		if (badge && meta) {
			badge.className = 'sb-badge ' + meta.className;
			badge.textContent = meta.label;
		}
	}

	function enrichModal(profile) {
		if (!profile || !profile.class) {
			return;
		}
		var c = profile.class;
		var bookings = profile.bookings || [];
		var reviews = profile.reviews || [];
		var revenue = profile.revenue || 0;
		var teacher = profile.teacher || {};
		var teacherName =
			((teacher.first_name || '') + ' ' + (teacher.last_name || '')).trim() || c.teacher;

		var desc =
			(c.description || '') +
			'\n\n— Admin summary —\nTeacher: ' +
			teacherName +
			(teacher.email ? ' (' + teacher.email + ')' : '') +
			'\nBookings: ' +
			bookings.length +
			' | Reviews: ' +
			reviews.length +
			' | Revenue: $' +
			Number(revenue).toFixed(2);

		setText('ac-modal-description', desc.trim());
		if (c.students !== undefined) {
			setText('ac-modal-students', String(c.students));
		}
		if (c.price_label) {
			setText('ac-modal-price', c.price_label);
		}
	}

	function openProfile(row) {
		fillModalFromRow(row);
		if (modal) {
			modal.show();
		}
		var id = getClassId(row);
		if (!id || !ajaxUrl) {
			return;
		}
		postAction('gmm_get_class_profile', { class_id: id })
			.then(function (json) {
				if (json && json.success && json.data && json.data.profile) {
					enrichModal(json.data.profile);
				}
			})
			.catch(function () {});
	}

	function runApprove(row) {
		postAction('gmm_approve_class', { class_id: getClassId(row) })
			.then(function (json) {
				if (json && json.success) {
					setRowStatus(row, 'approved');
					showToast((json.data && json.data.message) || i18n.approved);
				} else {
					showToast((json && json.data && json.data.message) || i18n.error, true);
				}
			})
			.catch(function () {
				showToast(i18n.error || 'Failed.', true);
			});
	}

	function runReject(row) {
		var reason = window.prompt(i18n.rejectPrompt || 'Optional rejection reason:', '') || '';
		postAction('gmm_reject_class', { class_id: getClassId(row), reason: reason })
			.then(function (json) {
				if (json && json.success) {
					setRowStatus(row, 'rejected');
					showToast((json.data && json.data.message) || i18n.rejected, true);
				} else {
					showToast((json && json.data && json.data.message) || i18n.error, true);
				}
			})
			.catch(function () {
				showToast(i18n.error || 'Failed.', true);
			});
	}

	function runFeature(row) {
		var featured = row.getAttribute('data-featured') === 'true' ? 0 : 1;
		postAction('gmm_toggle_class_featured', { class_id: getClassId(row), featured: featured })
			.then(function (json) {
				if (json && json.success) {
					row.setAttribute('data-featured', featured ? 'true' : 'false');
					showToast((json.data && json.data.message) || i18n.featured);
					window.setTimeout(function () {
						window.location.reload();
					}, 500);
				} else {
					showToast((json && json.data && json.data.message) || i18n.error, true);
				}
			})
			.catch(function () {
				showToast(i18n.error || 'Failed.', true);
			});
	}

	function editClass(row) {
		var id = getClassId(row);
		if (!id) {
			return;
		}
		var title = window.prompt('Title', row.getAttribute('data-title') || '');
		if (title === null) {
			return;
		}
		var description = window.prompt('Description', row.getAttribute('data-description') || '');
		if (description === null) {
			return;
		}
		var category = window.prompt('Category', row.getAttribute('data-category') || '');
		if (category === null) {
			return;
		}
		var difficulty = window.prompt('Difficulty (beginner/intermediate/advanced)', row.getAttribute('data-difficulty') || '');
		if (difficulty === null) {
			return;
		}
		var duration = window.prompt('Duration (minutes)', (row.getAttribute('data-duration-mins') || '60'));
		if (duration === null) {
			return;
		}
		var price = window.prompt('Price', (row.getAttribute('data-price-raw') || '0'));
		if (price === null) {
			return;
		}
		var image = window.prompt('Image URL', row.getAttribute('data-image') || '');
		if (image === null) {
			return;
		}
		var status = window.prompt('Status (draft/pending/approved/rejected)', row.getAttribute('data-status') || 'pending');
		if (status === null) {
			return;
		}

		postAction('gmm_admin_edit_class', {
			class_id: id,
			title: title,
			description: description,
			category: category,
			difficulty: difficulty,
			duration: duration,
			price: price,
			image: image,
			status: status
		})
			.then(function (json) {
				if (json && json.success) {
					showToast((json.data && json.data.message) || i18n.updated);
					window.setTimeout(function () {
						window.location.reload();
					}, 500);
				} else {
					showToast((json && json.data && json.data.message) || i18n.error, true);
				}
			})
			.catch(function () {
				showToast(i18n.error || 'Failed.', true);
			});
	}

	function bindRow(row) {
		var viewBtn = row.querySelector('.ac-view-btn');
		var approveBtn = row.querySelector('.ac-approve-btn');
		var rejectBtn = row.querySelector('.ac-reject-btn');
		var editBtn = row.querySelector('.ac-edit-btn');
		var featureBtn = row.querySelector('.ac-feature-btn');
		var deleteBtn = row.querySelector('.ac-delete-btn');

		if (viewBtn) {
			viewBtn.addEventListener('click', function () {
				openProfile(row);
			});
		}
		if (approveBtn) {
			approveBtn.addEventListener('click', function () {
				runApprove(row);
			});
		}
		if (rejectBtn) {
			rejectBtn.addEventListener('click', function () {
				runReject(row);
			});
		}
		if (editBtn) {
			editBtn.addEventListener('click', function () {
				editClass(row);
			});
		}
		if (featureBtn) {
			featureBtn.addEventListener('click', function () {
				runFeature(row);
			});
		}
		if (deleteBtn) {
			deleteBtn.addEventListener('click', function () {
				if (!window.confirm(i18n.confirmDelete || 'Delete this class?')) {
					return;
				}
				postAction('gmm_delete_class', { class_id: getClassId(row), confirm: '1' })
					.then(function (json) {
						if (json && json.success) {
							row.classList.add('is-deleted');
							row.hidden = true;
							showToast((json.data && json.data.message) || i18n.deleted, true);
						} else {
							showToast((json && json.data && json.data.message) || i18n.error, true);
						}
					})
					.catch(function () {
						showToast(i18n.error || 'Failed.', true);
					});
			});
		}
	}

	function renderFeaturedFromRows() {
		var grid = document.getElementById('ac-featured-grid');
		var empty = document.getElementById('ac-featured-empty');
		if (!grid) {
			return;
		}
		var serverCards = grid.querySelectorAll('.ac-featured-card');
		if (serverCards.length) {
			if (empty) {
				empty.hidden = true;
			}
			return;
		}
		var featured = Array.prototype.slice.call(document.querySelectorAll('.ac-row[data-featured="true"]'));
		grid.innerHTML = '';
		if (!featured.length) {
			if (empty) {
				empty.hidden = false;
			}
			return;
		}
		if (empty) {
			empty.hidden = true;
		}
		featured.slice(0, 6).forEach(function (row) {
			var card = document.createElement('article');
			card.className = 'ac-featured-card';
			card.innerHTML =
				'<img src="' +
				(row.getAttribute('data-image') || '') +
				'" alt="">' +
				'<div class="ac-featured-body"><h4></h4><p></p></div>';
			card.querySelector('h4').textContent = row.getAttribute('data-title') || '';
			card.querySelector('p').textContent = row.getAttribute('data-teacher') || '';
			grid.appendChild(card);
		});
	}

	function initModalActions() {
		var approve = document.getElementById('ac-modal-approve');
		var reject = document.getElementById('ac-modal-reject');
		if (approve) {
			approve.addEventListener('click', function () {
				if (activeRow) {
					runApprove(activeRow);
				}
			});
		}
		if (reject) {
			reject.addEventListener('click', function () {
				if (activeRow) {
					runReject(activeRow);
				}
			});
		}
	}

	function initCounters() {
		document.querySelectorAll('.gmm-wrapper .ad-counter').forEach(function (el) {
			var target = parseInt(el.getAttribute('data-count'), 10) || 0;
			var start = null;
			function step(ts) {
				if (start === null) {
					start = ts;
				}
				var p = Math.min((ts - start) / 900, 1);
				el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString('en-US');
				if (p < 1) {
					window.requestAnimationFrame(step);
				}
			}
			window.requestAnimationFrame(step);
		});
	}

	function init() {
		if (!document.getElementById('ac-table-body')) {
			return;
		}
		document.querySelectorAll('.ac-row').forEach(bindRow);
		initModalActions();
		initCounters();
		renderFeaturedFromRows();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', init);
	} else {
		init();
	}
})(window, document);
