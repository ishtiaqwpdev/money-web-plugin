/**
 * Gospel Music Mastery — animation engine.
 *
 * Loaded from <head> so `.gmm-anim` lands on <html> before first paint;
 * without it gospel-animations.css leaves every element fully visible,
 * which keeps the pages usable when JS is blocked.
 *
 * Markup is never edited by hand: reveal classes are applied here from a
 * selector map, so a new page only needs the CSS + this file.
 */
(function (window, document) {
  'use strict';

  var root = document.documentElement;
  var reduceMotion = window.matchMedia
    ? window.matchMedia('(prefers-reduced-motion: reduce)').matches
    : false;

  root.classList.add('gmm-anim');

  /* Elements inside a closed modal never intersect the viewport, so a
     reveal class would leave them invisible when the modal opens. */
  function isAnimatable(el) {
    return !el.closest('.modal');
  }

  function alreadyTagged(el) {
    return (
      el.classList.contains('animate-fade') ||
      el.classList.contains('animate-slide-up') ||
      el.classList.contains('animate-scale')
    );
  }

  /* ---------- 1. Scroll reveal ---------- */

  var REVEAL_MAP = [
    { selector: '.sd-stat-card, .td-stat-card, .ad-stat-card', effect: 'animate-scale', stagger: 60 },
    { selector: '.sd-quick-card, .td-quick-card', effect: 'animate-scale', stagger: 50 },
    {
      selector:
        '.tm-card, .apr-card, .sf-card, .sl-lesson-card, .tp-class-card, .tp-info-card,' +
        '.tp-review-card, .stp-related-card, .class-manage-card, .bk-card, .pay-card,' +
        '.abl-featured-card, .ac-featured-card, .ap-earn-card, .sd-teacher-card, .sd-lesson-card',
      effect: 'animate-slide-up',
      stagger: 70
    },
    { selector: '.gmm-chart-card', effect: 'animate-slide-up', stagger: 90 },
    {
      selector:
        '.sd-welcome-card, .tm-hero-card, .tp-hero-card, .cd-hero-card,' +
        '.sd-completion-card, .td-completion-card',
      effect: 'animate-fade',
      stagger: 0
    },
    {
      selector:
        '.bk-summary-card, .availability-calendar-card, .availability-slots-card,' +
        '.class-section-card, .as-activity-card, .ab-calendar-card, .aset-danger-card,' +
        '.ss-danger-card, .payment-connect-card, .payment-status-card, .withdrawal-equal-card',
      effect: 'animate-slide-up',
      stagger: 60
    },
    { selector: '.sd-card, .td-card', effect: 'animate-slide-up', stagger: 0 },
    { selector: '.table-responsive, .at-filter-bar', effect: 'animate-fade', stagger: 0 },
    { selector: '.login-form, .portal-intro-card', effect: 'animate-slide-up', stagger: 0 }
  ];

  function tagReveals() {
    REVEAL_MAP.forEach(function (rule) {
      var seenPerParent = [];
      var counts = [];

      Array.prototype.forEach.call(document.querySelectorAll(rule.selector), function (el) {
        if (!isAnimatable(el) || alreadyTagged(el)) return;

        el.classList.add(rule.effect);

        if (!rule.stagger) return;

        /* Stagger siblings so a grid ripples in rather than popping at once. */
        var parent = el.parentNode;
        var slot = seenPerParent.indexOf(parent);

        if (slot === -1) {
          slot = seenPerParent.push(parent) - 1;
          counts[slot] = 0;
        }

        var delay = Math.min(counts[slot]++ * rule.stagger, 300);
        if (delay) el.style.setProperty('--gmm-delay', delay + 'ms');
      });
    });
  }

  function revealAll() {
    Array.prototype.forEach.call(
      document.querySelectorAll('.animate-fade, .animate-slide-up, .animate-scale'),
      function (el) { el.classList.add('is-revealed'); }
    );
  }

  function observeReveals() {
    var targets = document.querySelectorAll('.animate-fade, .animate-slide-up, .animate-scale');
    if (!targets.length) return;

    if (!('IntersectionObserver' in window)) {
      revealAll();
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          entry.target.classList.add('is-revealed');
          observer.unobserve(entry.target);
        });
      },
      { root: null, rootMargin: '0px 0px -8% 0px', threshold: 0 }
    );

    Array.prototype.forEach.call(targets, function (el) { observer.observe(el); });
  }

  /* ---------- 2. Number counters ---------- */

  /* Pages that ship `data-count` already run their own counter; this only
     picks up the statistics that were plain static text. */
  var COUNTER_SELECTOR =
    '.sd-stat-value:not([data-count]), .td-stat-value:not([data-count]), .ad-chart-total';

  var NUMBER_PATTERN = /^(\D*?)([\d,]+(?:\.\d+)?)(.*)$/;

  function easeOutQuart(t) {
    return 1 - Math.pow(1 - t, 4);
  }

  function runCounter(el) {
    var parsed = el.getAttribute('data-gmm-parts');
    if (!parsed) return;

    parsed = JSON.parse(parsed);
    var target = parsed.value;
    var decimals = parsed.decimals;
    var start = null;
    var duration = 1100;

    function frame(now) {
      if (start === null) start = now;
      var progress = Math.min((now - start) / duration, 1);
      var current = target * easeOutQuart(progress);

      el.textContent =
        parsed.prefix +
        current.toLocaleString('en-US', {
          minimumFractionDigits: decimals,
          maximumFractionDigits: decimals
        }) +
        parsed.suffix;

      if (progress < 1) window.requestAnimationFrame(frame);
    }

    window.requestAnimationFrame(frame);
  }

  function setupCounters() {
    var targets = [];

    Array.prototype.forEach.call(document.querySelectorAll(COUNTER_SELECTOR), function (el) {
      if (!isAnimatable(el) || el.hasAttribute('data-gmm-parts')) return;

      var match = NUMBER_PATTERN.exec(el.textContent.trim());
      if (!match) return;

      var raw = match[2].replace(/,/g, '');
      var value = parseFloat(raw);
      if (!isFinite(value) || value === 0) return;

      var dot = raw.indexOf('.');
      el.setAttribute(
        'data-gmm-parts',
        JSON.stringify({
          prefix: match[1],
          suffix: match[3],
          value: value,
          decimals: dot === -1 ? 0 : raw.length - dot - 1
        })
      );

      targets.push(el);
    });

    if (!targets.length) return;

    if (reduceMotion || !('IntersectionObserver' in window)) return;

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          runCounter(entry.target);
          observer.unobserve(entry.target);
        });
      },
      { threshold: 0.4 }
    );

    targets.forEach(function (el) { observer.observe(el); });
  }

  /* ---------- 3. Image fade-in ---------- */

  function setupImages() {
    Array.prototype.forEach.call(document.images, function (img) {
      /* Cached images are already painted — fading them would only flicker. */
      if (img.complete || img.classList.contains('gmm-img')) return;

      img.classList.add('gmm-img');

      function done() {
        img.classList.add('is-loaded');
        img.removeEventListener('load', done);
        img.removeEventListener('error', done);
      }

      img.addEventListener('load', done);
      img.addEventListener('error', done);
    });
  }

  /* ---------- 4. Submit button loading state ---------- */

  var LOADING_FORMS =
    '.login-form form, form.teacher-register-form, form.student-agreement-form,' +
    'form.teacher-agreement-form, form.teacher-onboarding-form, form.teacher-profile-form,' +
    'form.student-profile-form, form.settings-password-form, form.teacher-class-form,' +
    'form.teacher-availability-form, form.teacher-payment-form, form.student-login-form';

  function setupFormLoading() {
    Array.prototype.forEach.call(document.querySelectorAll(LOADING_FORMS), function (form) {
      form.addEventListener('submit', function () {
        var btn = form.querySelector('button[type="submit"], input[type="submit"]');
        if (!btn || btn.classList.contains('is-loading')) return;

        btn.classList.add('is-loading');
        window.setTimeout(function () { btn.classList.remove('is-loading'); }, 1000);
      });
    });
  }

  /* ---------- 5. Boot ---------- */

  function init() {
    tagReveals();

    if (reduceMotion) {
      revealAll();
    } else {
      observeReveals();
      setupImages();
    }

    setupCounters();
    setupFormLoading();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  window.GMMAnimations = {
    init: init,
    revealAll: revealAll,
    setLoading: function (btn, state) {
      if (btn) btn.classList.toggle('is-loading', !!state);
    }
  };
})(window, document);
