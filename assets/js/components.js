/**
 * Gospel Music Mastery — shared dashboard component helpers.
 * Loading states, empty states, danger-button tagging.
 * Safe to load only on admin / teacher / student system pages.
 */
(function (window, document) {
  'use strict';

  var REDUCE = window.matchMedia
    ? window.matchMedia('(prefers-reduced-motion: reduce)').matches
    : false;

  /* ---------- Buttons ---------- */

  function setLoading(el, state) {
    if (!el) return;
    var on = state !== false;
    el.classList.toggle('is-loading', on);
    if (on) {
      if (!el.getAttribute('data-gmm-label')) {
        el.setAttribute('data-gmm-label', el.innerHTML);
      }
      el.setAttribute('aria-busy', 'true');
      el.disabled = true;
    } else {
      el.removeAttribute('aria-busy');
      el.disabled = false;
      var prev = el.getAttribute('data-gmm-label');
      if (prev !== null) el.innerHTML = prev;
    }
  }

  /* Tag danger actions that still use plain theme-btn / outline */
  function tagDangerButtons(root) {
    var scope = root || document;
      var dangerRe = /\b(delete|reject|suspend)\b/i;

    Array.prototype.forEach.call(scope.querySelectorAll('.theme-btn, .theme-btn-outline'), function (btn) {
      var label = (btn.textContent || '').replace(/\s+/g, ' ').trim();
      var id = btn.id || '';
      var forced = btn.hasAttribute('data-gmm-danger') ||
        btn.classList.contains('aset-danger-btn') ||
        btn.classList.contains('theme-btn-danger');

      if (!forced && !dangerRe.test(label) && !dangerRe.test(id)) return;

      btn.classList.remove('theme-btn-outline');
      btn.classList.add('theme-btn', 'theme-btn-danger');
    });
  }

  /* ---------- Empty states ---------- */

  function buildEmpty(options) {
    options = options || {};
    var wrap = document.createElement('div');
    wrap.className = 'gmm-empty';
    if (options.id) wrap.id = options.id;

    if (options.icon !== false) {
      var icon = document.createElement('span');
      icon.className = 'gmm-empty-icon';
      icon.setAttribute('aria-hidden', 'true');
      icon.innerHTML = '<i class="' + (options.icon || 'far fa-inbox') + '"></i>';
      wrap.appendChild(icon);
    }

    if (options.title) {
      var title = document.createElement('h3');
      title.className = 'gmm-empty-title';
      title.textContent = options.title;
      wrap.appendChild(title);
    }

    if (options.text) {
      var text = document.createElement('p');
      text.className = 'gmm-empty-text';
      text.textContent = options.text;
      wrap.appendChild(text);
    }

    if (options.actionHref || options.actionLabel) {
      var link = document.createElement(options.actionHref ? 'a' : 'button');
      link.className = 'theme-btn';
      if (options.actionHref) link.href = options.actionHref;
      else link.type = 'button';
      link.innerHTML = options.actionLabel || 'Get started';
      wrap.appendChild(link);
    }

    return wrap;
  }

  function enhanceEmptyStates(root) {
    var scope = root || document;
    Array.prototype.forEach.call(scope.querySelectorAll('.td-empty-state, .sl-empty'), function (el) {
      if (el.querySelector('.gmm-empty-icon') || el.classList.contains('gmm-empty-ready')) return;

      el.classList.add('gmm-empty', 'gmm-empty-ready');

      if (!el.querySelector('i') && !el.querySelector('.gmm-empty-icon')) {
        var icon = document.createElement('span');
        icon.className = 'gmm-empty-icon';
        icon.setAttribute('aria-hidden', 'true');
        icon.innerHTML = '<i class="far fa-inbox"></i>';
        el.insertBefore(icon, el.firstChild);
      }
    });
  }

  /* ---------- Skeleton / table loading ---------- */

  function setCardSkeleton(el, state) {
    if (!el) return;
    el.classList.toggle('gmm-skeleton', state !== false);
  }

  function setTableLoading(table, state, rows) {
    if (!table) return;
    var tbody = table.tBodies[0] || table.querySelector('tbody');
    if (!tbody) return;

    if (state === false) {
      var loading = tbody.querySelectorAll('tr.gmm-table-loading');
      Array.prototype.forEach.call(loading, function (tr) { tr.remove(); });
      tbody.classList.remove('is-gmm-loading');
      return;
    }

    if (tbody.classList.contains('is-gmm-loading')) return;
    tbody.classList.add('is-gmm-loading');

    var colCount = table.querySelectorAll('thead th').length || 4;
    var count = rows || 4;
    var i;
    var tr;
    var td;
    var line;

    for (i = 0; i < count; i++) {
      tr = document.createElement('tr');
      tr.className = 'gmm-table-loading';
      td = document.createElement('td');
      td.colSpan = colCount;
      line = document.createElement('span');
      line.className = 'gmm-skeleton-line';
      td.appendChild(line);
      tr.appendChild(td);
      tbody.appendChild(tr);
    }
  }

  /* ---------- Demo: brief loading on data-gmm-loading buttons ---------- */

  function wireDemoLoading(root) {
    var scope = root || document;
    Array.prototype.forEach.call(scope.querySelectorAll('[data-gmm-loading]'), function (btn) {
      if (btn.__gmmLoadingBound) return;
      btn.__gmmLoadingBound = true;
      btn.addEventListener('click', function () {
        if (btn.classList.contains('is-loading')) return;
        setLoading(btn, true);
        window.setTimeout(function () { setLoading(btn, false); }, 900);
      });
    });
  }

  /* ---------- Boot ---------- */

  function init(root) {
    tagDangerButtons(root);
    enhanceEmptyStates(root);
    wireDemoLoading(root);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () { init(); });
  } else {
    init();
  }

  window.GMMComponents = {
    init: init,
    setLoading: setLoading,
    setCardSkeleton: setCardSkeleton,
    setTableLoading: setTableLoading,
    buildEmpty: buildEmpty,
    enhanceEmptyStates: enhanceEmptyStates,
    tagDangerButtons: tagDangerButtons,
    reduceMotion: REDUCE
  };
})(window, document);
