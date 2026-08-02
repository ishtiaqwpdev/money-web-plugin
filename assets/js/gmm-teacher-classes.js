/**
 * Gospel Music Mastery — Teacher Class Management
 * Filters, create/edit/delete/duplicate via AJAX. Design unchanged.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_TEACHER_CLASSES || {};
	var pendingDeleteCard = null;
	var pendingViewCard = null;
	var currentFilter = 'all';
	var formMode = 'create';

	function ajax(action, formData) {
		formData = formData || new window.FormData();
		formData.append('action', action);
		formData.append(cfg.nonceField || 'gmm_teacher_class_nonce', cfg.nonce || '');
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

	function qs(sel, root) {
		return (root || document).querySelector(sel);
	}

	function qsa(sel, root) {
		return Array.prototype.slice.call((root || document).querySelectorAll(sel));
	}

	function showFormError(message) {
		var box = qs('#gmm-class-form-error');
		var text = qs('#gmm-class-form-error-text');
		if (!box) {
			window.alert(message || (cfg.i18n && cfg.i18n.error));
			return;
		}
		if (text) {
			text.textContent = message || (cfg.i18n && cfg.i18n.error) || 'Error';
		}
		box.hidden = !message;
	}

	function updateStats(stats) {
		if (!stats) {
			return;
		}
		Object.keys(stats).forEach(function (key) {
			var el = qs('[data-gmm-stat="' + key + '"]');
			if (el) {
				el.textContent = String(stats[key]);
			}
		});
		var books = qs('.td-profile-stats .td-stat-item:last-child');
		if (books && typeof stats.total !== 'undefined') {
			books.innerHTML = '<i class="far fa-books"></i> ' + stats.total + ' Classes';
		}
	}

	function applyFilter() {
		var grid = qs('#class-cards-grid');
		var emptyState = qs('#classes-empty');
		if (!grid) {
			return;
		}
		var cards = qsa('.class-manage-card', grid);
		var visible = 0;
		var categoryFilter = (qs('.gmm-wrapper') && qs('.gmm-wrapper').getAttribute('data-gmm-class-category')) || '';
		var searchFilter = ((qs('.gmm-wrapper') && qs('.gmm-wrapper').getAttribute('data-gmm-class-search')) || '').toLowerCase();

		cards.forEach(function (card) {
			var status = card.getAttribute('data-status') || '';
			var category = card.getAttribute('data-category') || '';
			var title = (card.getAttribute('data-title') || '').toLowerCase();
			var show = currentFilter === 'all' || status === currentFilter;
			if (show && categoryFilter) {
				show = category === categoryFilter;
			}
			if (show && searchFilter) {
				show = title.indexOf(searchFilter) !== -1 || category.toLowerCase().indexOf(searchFilter) !== -1;
			}
			card.hidden = !show;
			if (show) {
				visible += 1;
			}
		});
		if (emptyState) {
			emptyState.hidden = visible > 0;
		}
	}

	function buildCardHtml(card) {
		if (!card || !card.id) {
			return '';
		}
		return (
			'<article class="class-manage-card" data-class-id="' + card.id + '" data-status="' + escapeAttr(card.ui_status) + '" data-category="' + escapeAttr(card.category) + '" data-title="' + escapeAttr(card.title) + '" data-view-text="' + escapeAttr(card.view_text) + '">' +
			'<div class="class-manage-media">' +
			'<img src="' + escapeAttr(card.image_url) + '" alt="' + escapeAttr(card.title) + '">' +
			'<span class="td-badge ' + escapeAttr(card.badge_class) + ' class-manage-badge">' + escapeHtml(card.status_label) + '</span>' +
			'</div>' +
			'<div class="class-manage-body">' +
			'<span class="class-manage-category">' + escapeHtml(card.category) + '</span>' +
			'<h4>' + escapeHtml(card.title) + '</h4>' +
			'<ul class="class-manage-meta">' +
			'<li><i class="far fa-clock"></i> ' + escapeHtml(card.duration_label) + '</li>' +
			'<li><i class="far fa-dollar-sign"></i> ' + escapeHtml(card.price_label) + '</li>' +
			'<li><i class="far fa-users"></i> ' + escapeHtml(card.students_label) + '</li>' +
			'</ul>' +
			'<div class="class-manage-footer">' +
			'<span class="td-rating">' + escapeHtml(card.rating_display) + '</span>' +
			'<div class="dropdown">' +
			'<button class="class-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Class actions"><i class="far fa-ellipsis-vertical"></i></button>' +
			'<ul class="dropdown-menu dropdown-menu-end">' +
			'<li><button type="button" class="dropdown-item class-view-btn">View</button></li>' +
			'<li><button type="button" class="dropdown-item class-edit-btn" data-gmm-class-action="edit">Edit</button></li>' +
			'<li><button type="button" class="dropdown-item class-duplicate-btn">Duplicate</button></li>' +
			'<li><hr class="dropdown-divider"></li>' +
			'<li><button type="button" class="dropdown-item text-danger class-delete-btn">Delete</button></li>' +
			'</ul></div></div></div></article>'
		);
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

	function upsertCard(card) {
		var grid = qs('#class-cards-grid');
		if (!grid || !card) {
			return;
		}
		var existing = qs('.class-manage-card[data-class-id="' + card.id + '"]', grid);
		var html = buildCardHtml(card);
		if (existing) {
			existing.outerHTML = html;
		} else {
			grid.insertAdjacentHTML('afterbegin', html);
		}
		applyFilter();
	}

	function removeCard(classId) {
		var card = qs('.class-manage-card[data-class-id="' + classId + '"]');
		if (card) {
			card.remove();
		}
		applyFilter();
	}

	function resetForm() {
		var form = qs('#gmm-teacher-class-form');
		if (form) {
			form.reset();
		}
		var idField = qs('#gmm-class-id');
		var imageId = qs('#gmm-class-image-id');
		var preview = qs('#gmm-class-image-preview');
		var title = qs('#class-form-title');
		if (idField) {
			idField.value = '';
		}
		if (imageId) {
			imageId.value = '';
		}
		if (preview) {
			preview.hidden = true;
			preview.removeAttribute('src');
		}
		if (title) {
			title.textContent = formMode === 'edit' ? 'Edit Class' : 'Create New Class';
		}
		showFormError('');
	}

	function openCreateModal() {
		formMode = 'create';
		resetForm();
		var modalEl = qs('#class-form-modal');
		if (modalEl && window.bootstrap) {
			window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
		}
	}

	function openEditModal(classId) {
		formMode = 'edit';
		resetForm();
		var fd = new window.FormData();
		fd.append('class_id', classId);
		ajax(actionName('get') || 'gmm_teacher_class_get', fd)
			.then(function (json) {
				if (!json || !json.success || !json.data || !json.data.raw) {
					throw new Error((json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error));
				}
				var raw = json.data.raw;
				var card = json.data.card || {};
				qs('#gmm-class-id').value = String(raw.id || '');
				qs('#gmm-class-title').value = raw.title || '';
				qs('#gmm-class-category').value = raw.category || '';
				qs('#gmm-class-description').value = raw.description || '';
				qs('#gmm-class-price').value = raw.price != null ? raw.price : '';
				qs('#gmm-class-duration').value = raw.duration ? String(raw.duration) : '';
				qs('#gmm-class-difficulty').value = raw.difficulty || '';
				qs('#gmm-class-image-id').value = raw.image || '';
				qs('#gmm-class-featured-request').checked = !!raw.featured_request;
				var preview = qs('#gmm-class-image-preview');
				if (preview && card.image_url) {
					preview.src = card.image_url;
					preview.hidden = false;
				}
				var title = qs('#class-form-title');
				if (title) {
					title.textContent = 'Edit Class';
				}
				var modalEl = qs('#class-form-modal');
				if (modalEl && window.bootstrap) {
					window.bootstrap.Modal.getOrCreateInstance(modalEl).show();
				}
			})
			.catch(function (err) {
				window.alert((err && err.message) || (cfg.i18n && cfg.i18n.error));
			});
	}

	function wireTabs() {
		var tabs = qsa('.class-tab');
		tabs.forEach(function (tab) {
			if (tab.classList.contains('active')) {
				currentFilter = tab.getAttribute('data-filter') || 'all';
			}
			tab.addEventListener('click', function () {
				tabs.forEach(function (t) {
					t.classList.remove('active');
					t.setAttribute('aria-selected', 'false');
				});
				tab.classList.add('active');
				tab.setAttribute('aria-selected', 'true');
				currentFilter = tab.getAttribute('data-filter') || 'all';
				applyFilter();
			});
		});
	}

	function wireGrid() {
		var grid = qs('#class-cards-grid');
		var deleteModalEl = qs('#class-delete-modal');
		var viewModalEl = qs('#class-view-modal');
		var deleteModal = deleteModalEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(deleteModalEl) : null;
		var viewModal = viewModalEl && window.bootstrap ? window.bootstrap.Modal.getOrCreateInstance(viewModalEl) : null;
		var deleteConfirm = qs('#class-delete-confirm');
		var deleteName = qs('#class-delete-name');
		var viewText = qs('#class-view-text');

		if (grid) {
			grid.addEventListener('click', function (e) {
				var card = e.target.closest('.class-manage-card');
				if (!card) {
					return;
				}
				var title = card.getAttribute('data-title') || 'this class';
				var classId = card.getAttribute('data-class-id') || '';
				var viewBtn = e.target.closest('.class-view-btn');
				var editBtn = e.target.closest('.class-edit-btn');
				var duplicateBtn = e.target.closest('.class-duplicate-btn');
				var deleteBtn = e.target.closest('.class-delete-btn');

				if (viewBtn && viewModal) {
					pendingViewCard = card;
					if (viewText) {
						viewText.textContent = card.getAttribute('data-view-text') || title;
					}
					viewModal.show();
					return;
				}

				if (editBtn && classId) {
					openEditModal(classId);
					return;
				}

				if (duplicateBtn && classId) {
					var fd = new window.FormData();
					fd.append('class_id', classId);
					ajax(actionName('duplicate') || 'gmm_teacher_class_duplicate', fd)
						.then(function (json) {
							if (!json || !json.success) {
								throw new Error((json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error));
							}
							if (json.data.card) {
								upsertCard(json.data.card);
							}
							updateStats(json.data.stats);
						})
						.catch(function (err) {
							window.alert((err && err.message) || (cfg.i18n && cfg.i18n.error));
						});
					return;
				}

				if (deleteBtn && deleteModal) {
					pendingDeleteCard = card;
					if (deleteName) {
						deleteName.textContent = title;
					}
					deleteModal.show();
				}
			});
		}

		if (deleteConfirm) {
			deleteConfirm.addEventListener('click', function () {
				if (!pendingDeleteCard) {
					if (deleteModal) {
						deleteModal.hide();
					}
					return;
				}
				var classId = pendingDeleteCard.getAttribute('data-class-id') || '';
				var fd = new window.FormData();
				fd.append('class_id', classId);
				fd.append('confirm', '1');
				ajax(actionName('delete') || 'gmm_teacher_class_delete', fd)
					.then(function (json) {
						if (!json || !json.success) {
							throw new Error((json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error));
						}
						removeCard(classId);
						updateStats(json.data && json.data.stats);
						pendingDeleteCard = null;
						if (deleteModal) {
							deleteModal.hide();
						}
					})
					.catch(function (err) {
						window.alert((err && err.message) || (cfg.i18n && cfg.i18n.error));
					});
			});
		}

		var viewEdit = qs('#class-view-edit');
		if (viewEdit) {
			viewEdit.addEventListener('click', function () {
				var card = pendingViewCard;
				var classId = card ? card.getAttribute('data-class-id') : '';
				if (viewModal) {
					viewModal.hide();
				}
				if (classId) {
					openEditModal(classId);
				}
			});
		}
	}

	function wireCreateButtons() {
		document.addEventListener('click', function (e) {
			var btn = e.target.closest('[data-gmm-class-action="create"]');
			if (!btn) {
				return;
			}
			e.preventDefault();
			openCreateModal();
		});
	}

	function wireForm() {
		var form = qs('#gmm-teacher-class-form');
		if (!form) {
			return;
		}

		var imageInput = qs('#gmm-class-image');
		var preview = qs('#gmm-class-image-preview');
		if (imageInput && preview) {
			imageInput.addEventListener('change', function () {
				if (!imageInput.files || !imageInput.files[0]) {
					return;
				}
				var url = window.URL.createObjectURL(imageInput.files[0]);
				preview.src = url;
				preview.hidden = false;
			});
		}

		form.addEventListener('submit', function (e) {
			e.preventDefault();
			showFormError('');
			var fd = new window.FormData(form);
			var classId = (qs('#gmm-class-id') && qs('#gmm-class-id').value) || '';
			var action = classId
				? (actionName('update') || 'gmm_teacher_class_update')
				: (actionName('create') || 'gmm_teacher_class_create');
			var submitBtn = qs('#gmm-class-form-submit');
			if (submitBtn) {
				submitBtn.disabled = true;
			}
			ajax(action, fd)
				.then(function (json) {
					if (!json || !json.success) {
						throw new Error((json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error));
					}
					if (json.data && json.data.card) {
						upsertCard(json.data.card);
					}
					updateStats(json.data && json.data.stats);
					var modalEl = qs('#class-form-modal');
					if (modalEl && window.bootstrap) {
						window.bootstrap.Modal.getOrCreateInstance(modalEl).hide();
					}
				})
				.catch(function (err) {
					showFormError((err && err.message) || (cfg.i18n && cfg.i18n.error));
				})
				.then(function () {
					if (submitBtn) {
						submitBtn.disabled = false;
					}
				});
		});
	}

	function wireSidebar() {
		var shell = qs('.td-shell');
		var toggle = qs('#td-sidebar-toggle');
		var backdrop = qs('#td-sidebar-backdrop');
		if (!shell) {
			return;
		}
		function closeSidebar() {
			shell.classList.remove('is-sidebar-open');
			if (toggle) {
				toggle.setAttribute('aria-expanded', 'false');
			}
			if (backdrop) {
				backdrop.hidden = true;
			}
		}
		function openSidebar() {
			shell.classList.add('is-sidebar-open');
			if (toggle) {
				toggle.setAttribute('aria-expanded', 'true');
			}
			if (backdrop) {
				backdrop.hidden = false;
			}
		}
		if (toggle) {
			toggle.addEventListener('click', function () {
				if (shell.classList.contains('is-sidebar-open')) {
					closeSidebar();
				} else {
					openSidebar();
				}
			});
		}
		if (backdrop) {
			backdrop.addEventListener('click', closeSidebar);
		}
	}

	function refreshList() {
		var fd = new window.FormData();
		var wrap = qs('.gmm-wrapper');
		if (wrap) {
			fd.append('gmm_class_search', wrap.getAttribute('data-gmm-class-search') || '');
			fd.append('gmm_class_category', wrap.getAttribute('data-gmm-class-category') || '');
		}
		fd.append('gmm_class_status', currentFilter);
		return ajax(actionName('list') || 'gmm_teacher_class_list', fd).then(function (json) {
			if (!json || !json.success) {
				return;
			}
			var grid = qs('#class-cards-grid');
			if (grid && json.data && Array.isArray(json.data.cards)) {
				grid.innerHTML = json.data.cards.map(buildCardHtml).join('');
			}
			updateStats(json.data && json.data.stats);
			applyFilter();
		});
	}

	document.addEventListener('DOMContentLoaded', function () {
		if (!qs('#class-cards-grid') && !qs('#gmm-teacher-class-form')) {
			return;
		}
		wireSidebar();
		wireTabs();
		wireGrid();
		wireCreateButtons();
		wireForm();
		applyFilter();
	});

	window.GMMTeacherClasses = {
		refresh: refreshList,
		openCreate: openCreateModal,
		openEdit: openEditModal
	};
})(window, document);
