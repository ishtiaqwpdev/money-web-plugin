/**
 * Gospel Music Mastery — Teacher search / filter / sort / pagination (AJAX)
 * Preserves frozen teachers marketplace markup.
 */
(function (window, document) {
	'use strict';

	var cfg = window.GMM_TEACHER_SEARCH || {};
	var state = {
		page: 1,
		pending: null,
		timer: null
	};

	function $(id) {
		return document.getElementById(id);
	}

	function collectFilters() {
		var instruments = [];
		document.querySelectorAll('input[name="instrument"]:checked').forEach(function (el) {
			instruments.push(el.value);
		});

		var level = '';
		var levelEl = document.querySelector('input[name="level"]:checked');
		if (levelEl) {
			level = levelEl.value || '';
		}

		var rating = '';
		var ratingEl = document.querySelector('input[name="rating"]:checked');
		if (ratingEl) {
			rating = ratingEl.value || '';
		}

		var priceMax = '';
		var priceRange = $('tm-price-range');
		if (priceRange) {
			priceMax = priceRange.value;
		}

		var sort = 'newest';
		var sortSelect = $('tm-sort-select');
		if (sortSelect) {
			sort = sortSelect.value || 'newest';
		}

		var search = '';
		var searchInput = $('tm-search-input');
		if (searchInput) {
			search = searchInput.value || '';
		}

		return {
			search: search,
			instruments: instruments,
			level: level,
			rating: rating,
			price_max: priceMax,
			sort: sort,
			page: state.page,
			per_page: cfg.perPage || 8
		};
	}

	function request(params) {
		if (state.pending && state.pending.abort) {
			try {
				state.pending.abort();
			} catch (e) {
				/* ignore */
			}
		}

		var controller = window.AbortController ? new window.AbortController() : null;
		state.pending = controller;

		var fd = new window.FormData();
		fd.append('action', cfg.action || 'gmm_teacher_search');
		fd.append(cfg.nonceField || 'gmm_teacher_search_nonce', cfg.nonce || '');

		Object.keys(params || {}).forEach(function (key) {
			var val = params[key];
			if (Array.isArray(val)) {
				fd.append(key, val.join(','));
				if (key === 'instruments') {
					fd.append('instrument', val.join(','));
				}
			} else if (val !== null && typeof val !== 'undefined' && val !== '') {
				fd.append(key, val);
			}
		});

		return window.fetch(cfg.ajaxUrl || (window.GMM_DATA && window.GMM_DATA.ajax_url) || '', {
			method: 'POST',
			credentials: 'same-origin',
			body: fd,
			signal: controller ? controller.signal : undefined
		}).then(function (res) {
			return res.json();
		});
	}

	function updateUrl(params) {
		if (!window.history || !window.history.replaceState) {
			return;
		}
		var url = new window.URL(window.location.href);
		['search', 'sort', 'rating', 'level', 'page', 'price_max', 'instrument'].forEach(function (k) {
			url.searchParams.delete(k);
		});
		url.searchParams.delete('instruments');
		if (params.search) {
			url.searchParams.set('search', params.search);
		}
		if (params.sort) {
			url.searchParams.set('sort', params.sort);
		}
		if (params.rating) {
			url.searchParams.set('rating', params.rating);
		}
		if (params.level) {
			url.searchParams.set('level', params.level);
		}
		if (params.price_max) {
			url.searchParams.set('price_max', params.price_max);
		}
		if (params.instruments && params.instruments.length) {
			url.searchParams.set('instrument', params.instruments.join(','));
		}
		if (params.page && params.page > 1) {
			url.searchParams.set('page', String(params.page));
		}
		window.history.replaceState({}, '', url.toString());
	}

	function applyResult(json) {
		var data = (json && json.data) || {};
		var grid = $('tm-grid');
		var empty = $('tm-empty');
		var count = $('tm-results-count');
		var pagWrap = document.querySelector('.tm-listings');

		if (count) {
			count.textContent = String(data.total || 0);
		}

		if (grid) {
			grid.innerHTML = data.html || '';
		}

		if (empty) {
			empty.hidden = !!(data.items && data.items.length);
			if (!data.total) {
				empty.hidden = false;
			}
		}

		var existingNav = $('tm-pagination');
		if (existingNav) {
			existingNav.remove();
		}
		if (pagWrap && data.pagination) {
			pagWrap.insertAdjacentHTML('beforeend', data.pagination);
			wirePagination();
		}
	}

	function runSearch(resetPage) {
		if (resetPage) {
			state.page = 1;
		}
		var params = collectFilters();
		params.page = state.page;
		updateUrl(params);

		request(params)
			.then(function (json) {
				if (!json || !json.success) {
					throw new Error((json && json.data && json.data.message) || (cfg.i18n && cfg.i18n.error));
				}
				applyResult(json);
			})
			.catch(function (err) {
				if (err && err.name === 'AbortError') {
					return;
				}
				window.console && window.console.warn(err);
			});
	}

	function debounceSearch() {
		if (state.timer) {
			window.clearTimeout(state.timer);
		}
		state.timer = window.setTimeout(function () {
			runSearch(true);
		}, 350);
	}

	function wirePagination() {
		var nav = $('tm-pagination');
		if (!nav) {
			return;
		}
		nav.querySelectorAll('a.page-link[data-page]').forEach(function (a) {
			a.addEventListener('click', function (e) {
				e.preventDefault();
				var li = a.closest('.page-item');
				if (li && li.classList.contains('disabled')) {
					return;
				}
				var page = parseInt(a.getAttribute('data-page') || '1', 10);
				if (!page || page === state.page) {
					return;
				}
				state.page = page;
				runSearch(false);
				var listings = document.querySelector('.tm-listings');
				if (listings && listings.scrollIntoView) {
					listings.scrollIntoView({ behavior: 'smooth', block: 'start' });
				}
			});
		});
	}

	function closeFilters() {
		var layout = document.querySelector('.tm-layout');
		var filterToggle = $('tm-filter-toggle');
		var backdrop = $('tm-filters-backdrop');
		if (!layout) {
			return;
		}
		layout.classList.remove('is-filters-open');
		if (filterToggle) {
			filterToggle.setAttribute('aria-expanded', 'false');
		}
		if (backdrop) {
			backdrop.hidden = true;
		}
	}

	function openFilters() {
		var layout = document.querySelector('.tm-layout');
		var filterToggle = $('tm-filter-toggle');
		var backdrop = $('tm-filters-backdrop');
		if (!layout) {
			return;
		}
		layout.classList.add('is-filters-open');
		if (filterToggle) {
			filterToggle.setAttribute('aria-expanded', 'true');
		}
		if (backdrop) {
			backdrop.hidden = false;
		}
	}

	function clearFilters() {
		document.querySelectorAll('input[name="instrument"]').forEach(function (el) {
			el.checked = true;
		});
		document.querySelectorAll('input[name="level"]').forEach(function (el) {
			el.checked = el.value === '';
		});
		document.querySelectorAll('input[name="rating"]').forEach(function (el) {
			el.checked = el.value === '';
		});
		document.querySelectorAll('input[name="availability"]').forEach(function (el) {
			el.checked = false;
		});
		var priceRange = $('tm-price-range');
		var priceValue = $('tm-price-value');
		if (priceRange) {
			priceRange.value = '100';
		}
		if (priceValue) {
			priceValue.textContent = 'Up to $100';
		}
		var searchInput = $('tm-search-input');
		if (searchInput) {
			searchInput.value = '';
		}
		var sortSelect = $('tm-sort-select');
		if (sortSelect) {
			sortSelect.value = 'newest';
		}
		runSearch(true);
	}

	function boot() {
		if (!$('tm-grid') || !$('tm-search-form')) {
			return;
		}

		var pageParam = new window.URL(window.location.href).searchParams.get('page');
		state.page = pageParam ? Math.max(1, parseInt(pageParam, 10) || 1) : 1;

		var filterToggle = $('tm-filter-toggle');
		var backdrop = $('tm-filters-backdrop');
		var layout = document.querySelector('.tm-layout');

		if (filterToggle) {
			filterToggle.addEventListener('click', function () {
				if (layout && layout.classList.contains('is-filters-open')) {
					closeFilters();
				} else {
					openFilters();
				}
			});
		}
		if (backdrop) {
			backdrop.addEventListener('click', closeFilters);
		}

		var priceRange = $('tm-price-range');
		var priceValue = $('tm-price-value');
		if (priceRange && priceValue) {
			priceRange.addEventListener('input', function () {
				priceValue.textContent = 'Up to $' + priceRange.value;
			});
		}

		var searchForm = $('tm-search-form');
		if (searchForm) {
			searchForm.addEventListener('submit', function (e) {
				e.preventDefault();
				runSearch(true);
			});
		}
		var searchInput = $('tm-search-input');
		if (searchInput) {
			searchInput.addEventListener('input', debounceSearch);
		}

		var applyBtn = $('tm-apply-filters');
		if (applyBtn) {
			applyBtn.addEventListener('click', function () {
				closeFilters();
				runSearch(true);
			});
		}

		var clearBtn = $('tm-clear-filters');
		if (clearBtn) {
			clearBtn.addEventListener('click', clearFilters);
		}
		var emptyReset = $('tm-empty-reset');
		if (emptyReset) {
			emptyReset.addEventListener('click', clearFilters);
		}

		var sortSelect = $('tm-sort-select');
		if (sortSelect) {
			sortSelect.addEventListener('change', function () {
				runSearch(true);
			});
		}

		wirePagination();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})(window, document);
