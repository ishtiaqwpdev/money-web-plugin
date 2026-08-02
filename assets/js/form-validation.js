/**
 * Gospel Music Mastery — reusable frontend form validation.
 * Capture-phase submit: blocks invalid forms before page scripts run;
 * valid forms fall through to existing demo handlers (redirects/toasts).
 */
(function (window, document) {
  'use strict';

  var EMAIL_RE = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  var URL_RE = /^(https?:\/\/)?([\w-]+\.)+[\w-]{2,}(\/\S*)?$/i;
  var VIDEO_URL_RE = /(youtube\.com|youtu\.be|vimeo\.com)/i;
  var PHONE_RE = /^[\d\s+\-().]{7,20}$/;

  var MSG = {
    required: 'This field is required.',
    email: 'Please enter a valid email address.',
    emailOrUser: 'Please enter your email or username.',
    min: function (n) { return 'Must be at least ' + n + ' characters.'; },
    match: 'Passwords do not match.',
    agree: 'You must accept the agreement to continue.',
    phone: 'Please enter a valid phone number.',
    number: 'Please enter a valid number.',
    minNum: function (n) { return 'Must be at least ' + n + '.'; },
    url: 'Please enter a valid URL.',
    videoUrl: 'Enter a valid YouTube or Vimeo URL.',
    fileType: 'Invalid file type.',
    fileSize: function (mb) { return 'File must be ' + mb + 'MB or smaller.'; },
    fileOrUrl: 'Upload a video file or provide a video URL.',
    checkboxGroup: 'Please select at least one option.',
    formError: 'Please complete all required fields.',
    formSuccess: 'Saved successfully',
    signature: 'Please provide your signature.',
    payment: 'Please connect your payment account to continue.',
    slots: 'Please add at least one availability time slot.',
    booking: 'Please select a class, date, and time slot.',
    paymentMethod: 'Please select a payment method.'
  };

  /* ---------- Form rule registry ---------- */

  var FORM_RULES = {
    'student-login-form': {
      fields: {
        'student-username': { required: true, emailOrUsername: true },
        'student-password': { required: true, minLength: 6 }
      }
    },
    'student-register-form': {
      fields: {
        'first-name': { required: true },
        'last-name': { required: true },
        'reg-username': { required: true, minLength: 3 },
        'reg-email': { required: true, email: true },
        'reg-password': { required: true, minLength: 6, strength: true },
        'reg-confirm-password': { required: true, match: 'reg-password' },
        'agree-agreement': { required: true, checkbox: true, message: MSG.agree }
      }
    },
    'student-agreement-form': {
      fields: {
        'student-name': { required: true },
        'student-email': { required: true, email: true },
        'effective-date': { required: true },
        'confirm-agreement': { required: true, checkbox: true, message: MSG.agree }
      },
      signature: { canvas: '#signature-pad', wrap: '.signature-pad-wrap', message: MSG.signature }
    },
    'teacher-login-form': {
      fields: {
        'teacher-username': { required: true, emailOrUsername: true },
        'teacher-password': { required: true, minLength: 6 }
      }
    },
    'teacher-register-form': {
      fields: {
        'first-name': { required: true },
        'last-name': { required: true },
        'reg-username': { required: true, minLength: 3 },
        'reg-email': { required: true, email: true },
        'reg-password': { required: true, minLength: 6, strength: true },
        'reg-confirm-password': { required: true, match: 'reg-password' },
        'agree-agreement': { required: true, checkbox: true, message: MSG.agree }
      }
    },
    'teacher-profile-form': {
      fields: {
        'profile-first-name': { required: true },
        'profile-last-name': { required: true },
        'profile-email': { required: true, email: true, optionalIfMissing: true },
        'profile-phone': { required: true, phone: true },
        'profile-username': { required: false, minLength: 3, optionalIfMissing: true },
        'profile-display-name': { required: true, optionalIfMissing: true },
        'profile-skill': { required: true, optionalIfMissing: true },
        'profile-timezone': { required: true, optionalIfMissing: true },
        'profile-bio': { required: true, minLength: 20, optionalIfMissing: true },
        'profile-photo': {
          file: true,
          types: ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'],
          maxMB: 5,
          optional: true
        }
      }
    },
    'settings-profile-form': {
      fields: {
        'settings-first-name': { required: true },
        'settings-last-name': { required: true },
        'settings-username': { required: true, minLength: 3 },
        'settings-phone': { required: true, phone: true },
        'settings-skill': { required: true },
        'settings-timezone': { required: true },
        'settings-display-name': { required: true },
        'settings-bio': { required: true, minLength: 20 }
      }
    },
    'settings-password-form': {
      fields: {
        'current-password': { required: true },
        'new-password': { required: true, minLength: 8, strength: true },
        'confirm-password': { required: true, match: 'new-password' }
      }
    },
    'settings-billing-form': {
      fields: {
        'billing-name': { required: true },
        'billing-country': { required: true },
        'billing-address': { required: true },
        'billing-city': { required: true },
        'billing-state': { required: true },
        'billing-zip': { required: true }
      }
    },
    'teacher-class-form': {
      fields: {
        'class-title': { required: true },
        'class-category': { required: true },
        'class-description': { required: true, minLength: 20 },
        'class-price': { required: true, number: true, min: 1 },
        'class-duration': { required: true },
        'class-difficulty': { required: true, optionalIfMissing: true },
        'class-image': {
          file: true,
          types: ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'],
          maxMB: 5,
          optional: true
        }
      }
    },
    'teacher-video-form': {
      fields: {
        'intro-video': {
          file: true,
          types: ['video/mp4', 'video/quicktime', 'video/webm'],
          maxMB: 50,
          optional: true
        },
        'video-url': { url: true, videoUrl: true, optional: true }
      },
      custom: 'videoOrFile'
    },
    'student-profile-form': {
      fields: {
        'first-name': { required: true },
        'last-name': { required: true },
        'username': { required: true, minLength: 3 },
        'email': { required: true, email: true },
        'phone': { phone: true, optional: true },
        'country': { required: true },
        'learning-goals': { required: true },
        'sd-profile-photo': {
          file: true,
          types: ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'],
          maxMB: 5,
          optional: true
        }
      },
      checkboxGroup: { name: 'instruments[]', message: MSG.checkboxGroup }
    },
    'ss-profile-form': {
      fields: {
        'ss-first-name': { required: true },
        'ss-last-name': { required: true },
        'ss-username': { required: true, minLength: 3 },
        'ss-email': { required: true, email: true },
        'ss-phone': { phone: true, optional: true },
        'ss-country': { required: true },
        'ss-goals': { required: true, optionalIfMissing: true },
        'ss-profile-photo': {
          file: true,
          types: ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'],
          maxMB: 5,
          optional: true
        }
      }
    },
    'ss-password-form': {
      fields: {
        'ss-current-password': { required: true },
        'ss-new-password': { required: true, minLength: 8, strength: true },
        'ss-confirm-password': { required: true, match: 'ss-new-password' }
      }
    },
    'ss-billing-form': {
      fields: {
        'ss-bill-name': { required: true },
        'ss-bill-email': { required: true, email: true },
        'ss-bill-country': { required: true },
        'ss-bill-address': { required: true },
        'ss-bill-city': { required: true },
        'ss-bill-state': { required: true },
        'ss-bill-zip': { required: true }
      }
    },
    'bk-booking-form': {
      fields: {
        'bk-select-class': { required: true },
        'bk-selected-date': { required: true, message: 'Please select a date.' },
        'bk-selected-time': { required: true, message: 'Please select a time slot.' }
      },
      formMessage: MSG.booking
    },
    'pay-checkout-form': {
      fields: {
        'pay-full-name': { required: true },
        'pay-email': { required: true, email: true },
        'pay-country': { required: true },
        'pay-address': { required: true },
        'pay-city': { required: true },
        'pay-zip': { required: true }
      },
      custom: 'paymentMethod'
    },
    'aset-profile-form': {
      fields: {
        'aset-full-name': { required: true },
        'aset-username': { required: true, minLength: 3 },
        'aset-email': { required: true, email: true },
        'aset-phone': { phone: true, optional: true },
        'aset-profile-photo': {
          file: true,
          types: ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'],
          maxMB: 5,
          optional: true
        }
      }
    },
    'aset-password-form': {
      fields: {
        'aset-current-password': { required: true },
        'aset-new-password': { required: true, minLength: 8, strength: true },
        'aset-confirm-password': { required: true, match: 'aset-new-password' }
      }
    },
    'aset-website-form': {
      fields: {
        'aset-site-name': { required: true },
        'aset-contact-email': { required: true, email: true },
        'aset-contact-phone': { phone: true, optional: true },
        'aset-logo-file': {
          file: true,
          types: ['image/jpeg', 'image/png', 'image/webp', 'image/svg+xml', 'image/x-icon'],
          maxMB: 2,
          optional: true
        },
        'aset-favicon-file': {
          file: true,
          types: ['image/png', 'image/x-icon', 'image/vnd.microsoft.icon', 'image/jpeg'],
          maxMB: 1,
          optional: true
        }
      }
    },
    'aset-payment-form': {
      fields: {
        'aset-commission': { required: true, number: true, min: 0 },
        'aset-currency': { required: true },
        'aset-min-withdrawal': { required: true, number: true, min: 1 }
      }
    },
    'aset-email-form': {
      fields: {
        'aset-sender-name': { required: true },
        'aset-sender-email': { required: true, email: true },
        'aset-smtp-host': { required: true },
        'aset-smtp-port': { required: true, number: true, min: 1 }
      }
    },
    'apr-program-form': {
      fields: {
        'apr-form-name': { required: true },
        'apr-form-category': { required: true },
        'apr-form-description': { required: true, minLength: 10 },
        'apr-form-duration': { required: true, optionalIfMissing: true },
        'apr-form-difficulty': { required: true, optionalIfMissing: true },
        'apr-form-status': { required: true, optionalIfMissing: true },
        'apr-form-image': {
          file: true,
          types: ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'],
          maxMB: 5,
          optional: true
        }
      }
    },
    'abl-blog-form': {
      fields: {
        'abl-form-title-input': { required: true },
        'abl-form-category': { required: true },
        'abl-form-content': { required: true, minLength: 20 },
        'abl-form-author': { required: true, optionalIfMissing: true },
        'abl-form-short': { required: true, optionalIfMissing: true },
        'abl-form-status': { required: true, optionalIfMissing: true },
        'abl-form-image': {
          file: true,
          types: ['image/jpeg', 'image/png', 'image/webp', 'image/jpg'],
          maxMB: 5,
          optional: true
        }
      }
    }
  };

  /* ---------- Helpers ---------- */

  function $(sel, root) {
    return (root || document).querySelector(sel);
  }

  function trim(v) {
    return (v == null ? '' : String(v)).trim();
  }

  function strengthScore(password) {
    var p = password || '';
    var score = 0;
    if (p.length >= 6) score++;
    if (p.length >= 10) score++;
    if (/[A-Z]/.test(p) && /[a-z]/.test(p)) score++;
    if (/\d/.test(p) && /[^A-Za-z0-9]/.test(p)) score++;
    return Math.min(score, 4);
  }

  function strengthLabel(level) {
    return ['', 'Weak', 'Fair', 'Good', 'Strong'][level] || '';
  }

  function getFeedbackEl(field) {
    if (!field || !field.id) return null;
    var byData = document.querySelector('.field-feedback[data-for="' + field.id + '"]');
    if (byData) return byData;
    var byId = document.getElementById(field.id + '-feedback');
    if (byId) return byId;
    var next = field.parentNode && field.parentNode.querySelector('.field-feedback');
    if (next) return next;
    var group = field.closest('.form-group, .mb-3, .mb-4, .password-field') || field.parentNode;
    if (!group) return null;
    var existing = group.querySelector('.gmm-field-error, .field-feedback');
    if (existing) return existing;
    var span = document.createElement('span');
    span.className = 'field-feedback gmm-field-error';
    span.setAttribute('data-for', field.id);
    if (field.classList.contains('form-check-input') && field.parentNode) {
      field.parentNode.parentNode
        ? field.parentNode.parentNode.appendChild(span)
        : field.parentNode.appendChild(span);
    } else {
      group.appendChild(span);
    }
    return span;
  }

  function setFieldState(field, valid, message) {
    if (!field) return;
    field.classList.remove('is-valid', 'is-invalid');
    var wrap = field.closest('.password-field, .gmm-file-wrap, .signature-pad-wrap');
    if (wrap) wrap.classList.remove('is-valid', 'is-invalid');

    var feedback = getFeedbackEl(field);
    if (valid === null) {
      if (feedback) {
        feedback.textContent = '';
        feedback.classList.remove('is-invalid-feedback', 'is-valid-feedback');
      }
      return;
    }

    if (valid) {
      field.classList.add('is-valid');
      if (wrap) wrap.classList.add('is-valid');
      if (feedback) {
        feedback.textContent = '';
        feedback.classList.remove('is-invalid-feedback');
        feedback.classList.add('is-valid-feedback');
      }
    } else {
      field.classList.add('is-invalid');
      if (wrap) wrap.classList.add('is-invalid');
      if (feedback) {
        feedback.textContent = message || MSG.required;
        feedback.classList.add('is-invalid-feedback');
        feedback.classList.remove('is-valid-feedback');
      }
    }
  }

  function ensureFormAlert(form) {
    var existing = form.querySelector('.gmm-form-alert');
    if (existing) return existing;
    var alert = document.createElement('div');
    alert.className = 'gmm-form-alert';
    alert.hidden = true;
    alert.setAttribute('role', 'alert');
    form.insertBefore(alert, form.firstChild);
    return alert;
  }

  function showFormAlert(form, type, message) {
    var alert = ensureFormAlert(form);
    alert.className = 'gmm-form-alert is-' + type;
    alert.innerHTML = '<i class="far ' +
      (type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation') +
      '"></i><span></span>';
    alert.querySelector('span').textContent = message;
    alert.hidden = false;
  }

  function hideFormAlert(form) {
    var alert = form.querySelector('.gmm-form-alert');
    if (alert) alert.hidden = true;
  }

  function setButtonState(btn, state, label) {
    if (!btn) return;
    if (!btn.getAttribute('data-gmm-btn-label')) {
      btn.setAttribute('data-gmm-btn-label', btn.innerHTML);
    }
    btn.classList.remove('is-loading', 'is-success', 'is-error');

    if (state === 'loading') {
      btn.classList.add('is-loading');
      if (btn.tagName === 'BUTTON' || btn.tagName === 'INPUT') btn.disabled = true;
      btn.innerHTML = label || 'Saving...';
    } else if (state === 'success') {
      btn.classList.add('is-success');
      if (btn.tagName === 'BUTTON' || btn.tagName === 'INPUT') btn.disabled = true;
      btn.innerHTML = label || 'Saved ✓';
      window.setTimeout(function () {
        btn.classList.remove('is-success');
        if (btn.tagName === 'BUTTON' || btn.tagName === 'INPUT') btn.disabled = false;
        btn.innerHTML = btn.getAttribute('data-gmm-btn-label');
      }, 1600);
    } else if (state === 'error') {
      btn.classList.add('is-error');
      if (btn.tagName === 'BUTTON' || btn.tagName === 'INPUT') btn.disabled = false;
      btn.innerHTML = label || 'Try again';
      window.setTimeout(function () {
        btn.classList.remove('is-error');
        btn.innerHTML = btn.getAttribute('data-gmm-btn-label');
      }, 1600);
    } else {
      if (btn.tagName === 'BUTTON' || btn.tagName === 'INPUT') btn.disabled = false;
      btn.innerHTML = btn.getAttribute('data-gmm-btn-label');
    }
  }

  /* ---------- Field validators ---------- */

  function validateField(field, rule, form) {
    if (!field) {
      if (rule.optionalIfMissing) return { valid: true };
      return { valid: false, message: MSG.required };
    }

    if (rule.optionalIfMissing && !document.getElementById(field.id)) {
      return { valid: true };
    }

    var value = field.type === 'file' ? '' : trim(field.value);
    var isCheck = field.type === 'checkbox' || rule.checkbox;

    if (isCheck) {
      if (rule.required && !field.checked) {
        return { valid: false, message: rule.message || MSG.agree };
      }
      return { valid: true };
    }

    if (rule.file) {
      var file = field.files && field.files[0];
      if (!file) {
        if (rule.optional || !rule.required) return { valid: true };
        return { valid: false, message: MSG.required };
      }
      if (rule.types && rule.types.indexOf(file.type) === -1) {
        var okExt = rule.types.some(function (t) {
          return file.type === t || (t.indexOf('jpeg') !== -1 && /\.jpe?g$/i.test(file.name));
        });
        if (!okExt && file.type) {
          return { valid: false, message: MSG.fileType };
        }
      }
      if (rule.maxMB && file.size > rule.maxMB * 1024 * 1024) {
        return { valid: false, message: MSG.fileSize(rule.maxMB) };
      }
      return { valid: true };
    }

    if (!value) {
      if (rule.optional && !rule.required) return { valid: true };
      if (rule.required) return { valid: false, message: rule.message || MSG.required };
      return { valid: true };
    }

    if (rule.email && !EMAIL_RE.test(value)) {
      return { valid: false, message: MSG.email };
    }

    if (rule.emailOrUsername) {
      if (value.indexOf('@') !== -1 && !EMAIL_RE.test(value)) {
        return { valid: false, message: MSG.email };
      }
      if (!value) return { valid: false, message: MSG.emailOrUser };
    }

    if (rule.minLength && value.length < rule.minLength) {
      return { valid: false, message: MSG.min(rule.minLength) };
    }

    if (rule.phone && !PHONE_RE.test(value)) {
      return { valid: false, message: MSG.phone };
    }

    if (rule.number) {
      var num = parseFloat(value);
      if (!isFinite(num)) return { valid: false, message: MSG.number };
      if (rule.min != null && num < rule.min) {
        return { valid: false, message: MSG.minNum(rule.min) };
      }
    }

    if (rule.url && value && !URL_RE.test(value)) {
      return { valid: false, message: MSG.url };
    }

    if (rule.videoUrl && value && !VIDEO_URL_RE.test(value)) {
      return { valid: false, message: MSG.videoUrl };
    }

    if (rule.match) {
      var other = form.querySelector('#' + rule.match) || document.getElementById(rule.match);
      if (other && trim(other.value) !== value) {
        return { valid: false, message: MSG.match };
      }
    }

    return { valid: true };
  }

  function validateSignature(config) {
    if (!config) return { valid: true };
    var canvas = $(config.canvas);
    if (!canvas) return { valid: true };
    var ctx = canvas.getContext('2d');
    if (!ctx) return { valid: false, message: config.message || MSG.signature };
    var blank = document.createElement('canvas');
    blank.width = canvas.width;
    blank.height = canvas.height;
    var empty = canvas.toDataURL() === blank.toDataURL();
    var wrap = $(config.wrap);
    if (empty) {
      if (wrap) wrap.classList.add('is-invalid');
      return { valid: false, message: config.message || MSG.signature };
    }
    if (wrap) {
      wrap.classList.remove('is-invalid');
      wrap.classList.add('is-valid');
    }
    return { valid: true };
  }

  function validateForm(form, rules) {
    var ok = true;
    var firstInvalid = null;
    var fields = rules.fields || {};
    var id;

    hideFormAlert(form);

    for (id in fields) {
      if (!Object.prototype.hasOwnProperty.call(fields, id)) continue;
      var rule = fields[id];
      var field = form.querySelector('#' + id) || document.getElementById(id);
      if (!field && rule.optionalIfMissing) continue;
      if (!field && rule.optional) continue;
      if (!field) {
        ok = false;
        continue;
      }

      var result = validateField(field, rule, form);
      setFieldState(field, result.valid, result.message);
      if (!result.valid) {
        ok = false;
        if (!firstInvalid) firstInvalid = field;
      }
    }

    if (rules.checkboxGroup) {
      var boxes = form.querySelectorAll('input[name="' + rules.checkboxGroup.name + '"]');
      var any = false;
      Array.prototype.forEach.call(boxes, function (b) { if (b.checked) any = true; });
      if (!any) {
        ok = false;
        var groupKey = rules.checkboxGroup.name.replace(/\[\]$/, '');
        var groupFb = form.querySelector('.field-feedback[data-for="' + groupKey + '"]') ||
          form.querySelector('.field-feedback[data-for="' + rules.checkboxGroup.name + '"]');
        if (groupFb) {
          groupFb.textContent = rules.checkboxGroup.message || MSG.checkboxGroup;
          groupFb.classList.add('is-invalid-feedback');
        }
        var first = boxes[0];
        if (first) {
          first.classList.add('is-invalid');
          if (!firstInvalid) firstInvalid = first;
        }
      } else {
        var groupKeyOk = rules.checkboxGroup.name.replace(/\[\]$/, '');
        var groupFbOk = form.querySelector('.field-feedback[data-for="' + groupKeyOk + '"]');
        if (groupFbOk) {
          groupFbOk.textContent = '';
          groupFbOk.classList.remove('is-invalid-feedback');
        }
        Array.prototype.forEach.call(boxes, function (b) { b.classList.remove('is-invalid'); });
      }
    }

    if (rules.signature) {
      var sig = validateSignature(rules.signature);
      if (!sig.valid) {
        ok = false;
        var fb = document.querySelector('.field-feedback[data-for="signature-pad"]');
        if (fb) {
          fb.textContent = sig.message;
          fb.classList.add('is-invalid-feedback');
        }
      }
    }

    if (rules.custom === 'videoOrFile') {
      var fileInput = form.querySelector('#intro-video');
      var urlInput = form.querySelector('#video-url');
      var hasFile = fileInput && fileInput.files && fileInput.files.length;
      var hasUrl = urlInput && trim(urlInput.value);
      if (!hasFile && !hasUrl) {
        ok = false;
        if (fileInput) setFieldState(fileInput, false, MSG.fileOrUrl);
        if (urlInput) setFieldState(urlInput, false, MSG.fileOrUrl);
        if (!firstInvalid) firstInvalid = fileInput || urlInput;
      }
    }

    if (rules.custom === 'paymentMethod') {
      var method = form.querySelector('input[name="payment_method"]:checked');
      if (!method) {
        ok = false;
        showFormAlert(form, 'error', MSG.paymentMethod);
      } else if (method.value === 'card') {
        ['pay-card-number', 'pay-card-expiry', 'pay-card-cvc'].forEach(function (cid) {
          var el = form.querySelector('#' + cid);
          if (!el) return;
          var r = validateField(el, { required: true }, form);
          setFieldState(el, r.valid, r.message);
          if (!r.valid) {
            ok = false;
            if (!firstInvalid) firstInvalid = el;
          }
        });
      }
    }

    if (!ok) {
      showFormAlert(form, 'error', rules.formMessage || MSG.formError);
      if (firstInvalid && typeof firstInvalid.focus === 'function') {
        try { firstInvalid.focus(); } catch (err) { /* ignore */ }
      }
    }

    return ok;
  }

  /* ---------- Password UX ---------- */

  function wirePasswordToggle(btn) {
    if (!btn || btn.__gmmToggleBound) return;
    btn.__gmmToggleBound = true;
    btn.addEventListener('click', function () {
      var id = btn.getAttribute('data-target');
      var input = id
        ? document.getElementById(id)
        : (btn.closest('.password-field') || document).querySelector('input[type="password"], input[type="text"]');
      if (!input) {
        if (btn.id === 'password-toggle') {
          input = document.getElementById('student-password') ||
            document.getElementById('teacher-password');
        }
      }
      if (!input) return;
      var show = input.type === 'password';
      input.type = show ? 'text' : 'password';
      btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
      btn.title = show ? 'Hide password' : 'Show password';
      var icon = btn.querySelector('i');
      if (icon) {
        icon.classList.toggle('fa-eye', !show);
        icon.classList.toggle('fa-eye-slash', show);
      }
    });
  }

  function ensureStrengthMeter(input) {
    if (!input || input.__gmmStrength) return;
    input.__gmmStrength = true;
    /* Never append inside .password-field — that breaks the eye toggle centering */
    var wrap = input.closest('.password-field');
    var host = input.closest('.form-group, .mb-3, .mb-4') || (wrap && wrap.parentNode) || input.parentNode;
    var meter = document.createElement('div');
    meter.className = 'gmm-password-strength';
    meter.setAttribute('data-level', '0');
    meter.innerHTML =
      '<div class="gmm-password-strength-bar"><span></span><span></span><span></span><span></span></div>' +
      '<div class="gmm-password-strength-label">Password strength</div>';

    if (wrap && wrap.parentNode === host) {
      if (wrap.nextSibling) host.insertBefore(meter, wrap.nextSibling);
      else host.appendChild(meter);
    } else {
      host.appendChild(meter);
    }

    input.addEventListener('input', function () {
      var level = strengthScore(input.value);
      meter.setAttribute('data-level', String(level));
      meter.querySelector('.gmm-password-strength-label').textContent =
        input.value ? ('Strength: ' + strengthLabel(level)) : 'Password strength';
    });
  }

  function ensureMatchHint(confirmInput, sourceId) {
    if (!confirmInput || confirmInput.__gmmMatch) return;
    confirmInput.__gmmMatch = true;
    var host = confirmInput.closest('.form-group, .mb-3') || confirmInput.parentNode;
    var hint = document.createElement('span');
    hint.className = 'gmm-password-match';
    host.appendChild(hint);

    function check() {
      var source = document.getElementById(sourceId);
      var a = trim(confirmInput.value);
      var b = source ? trim(source.value) : '';
      hint.classList.remove('is-match', 'is-mismatch');
      if (!a) { hint.textContent = ''; return; }
      if (a === b) {
        hint.textContent = 'Passwords match';
        hint.classList.add('is-match');
      } else {
        hint.textContent = 'Passwords do not match';
        hint.classList.add('is-mismatch');
      }
    }

    confirmInput.addEventListener('input', check);
    var source = document.getElementById(sourceId);
    if (source) source.addEventListener('input', check);
  }

  /* ---------- File preview ---------- */

  function wireFileInput(input, rule) {
    if (!input || input.__gmmFileBound) return;
    input.__gmmFileBound = true;

    var host = input.closest('.form-group, .photo-upload, .class-image-panel, .mb-3') || input.parentNode;
    var preview = host.querySelector('.gmm-file-preview');
    if (!preview) {
      preview = document.createElement('div');
      preview.className = 'gmm-file-preview';
      preview.innerHTML = '<div class="gmm-file-media"></div><div class="gmm-file-meta"></div>';
      host.appendChild(preview);
    }

    input.addEventListener('change', function () {
      var file = input.files && input.files[0];
      var media = preview.querySelector('.gmm-file-media');
      var meta = preview.querySelector('.gmm-file-meta');
      media.innerHTML = '';
      if (!file) {
        preview.classList.remove('is-visible');
        setFieldState(input, null);
        return;
      }

      var result = validateField(input, rule || { file: true, optional: true }, input.form || document);
      setFieldState(input, result.valid, result.message);
      if (!result.valid) {
        preview.classList.remove('is-visible');
        return;
      }

      meta.textContent = file.name + ' · ' + (file.size / 1024 / 1024).toFixed(2) + ' MB';
      if (file.type.indexOf('image/') === 0) {
        var img = document.createElement('img');
        img.alt = 'Preview';
        img.src = URL.createObjectURL(file);
        media.appendChild(img);
      } else if (file.type.indexOf('video/') === 0) {
        var vid = document.createElement('video');
        vid.controls = true;
        vid.src = URL.createObjectURL(file);
        media.appendChild(vid);
      }
      preview.classList.add('is-visible');
    });
  }

  /* ---------- Bind a form ---------- */

  function bindForm(form) {
    if (!form || form.__gmmValidateBound) return;
    var rules = FORM_RULES[form.id];
    if (!rules) return;

    form.__gmmValidateBound = true;
    form.setAttribute('novalidate', 'novalidate');

    var id;
    for (id in rules.fields) {
      if (!Object.prototype.hasOwnProperty.call(rules.fields, id)) continue;
      var rule = rules.fields[id];
      var field = form.querySelector('#' + id) || document.getElementById(id);
      if (!field) continue;

      if (rule.strength) ensureStrengthMeter(field);
      if (rule.match) ensureMatchHint(field, rule.match);
      if (rule.file) wireFileInput(field, rule);

      field.addEventListener('blur', function (fid, frule) {
        return function () {
          var el = document.getElementById(fid);
          if (!el) return;
          if (!trim(el.value) && el.type !== 'file' && !el.checked && !frule.required) {
            setFieldState(el, null);
            return;
          }
          var r = validateField(el, frule, form);
          setFieldState(el, r.valid, r.message);
        };
      }(id, rule));

      field.addEventListener('input', function () {
        if (this.classList.contains('is-invalid') || this.classList.contains('is-valid')) {
          var fr = rules.fields[this.id];
          if (!fr) return;
          var r = validateField(this, fr, form);
          setFieldState(this, r.valid, r.message);
        }
      });
    }

    form.addEventListener('submit', function (e) {
      var valid = validateForm(form, rules);
      var submitBtn = form.querySelector('[type="submit"], button.theme-btn:not([type="button"])');

      if (!valid) {
        e.preventDefault();
        e.stopImmediatePropagation();
        setButtonState(submitBtn, 'error', 'Try again');
        return;
      }

      /* Valid: brief loading/success feedback, then allow other handlers */
      setButtonState(submitBtn, 'loading', 'Saving...');
      showFormAlert(form, 'success', '✓ ' + MSG.formSuccess);

      window.setTimeout(function () {
        setButtonState(submitBtn, 'success', 'Saved ✓');
      }, 450);
    }, true);
  }

  /* ---------- Non-form demos ---------- */

  function wirePaymentConnect() {
    var btn = document.getElementById('stripe-connect-btn');
    if (!btn) return;

    var panel = document.querySelector('.stripe-connect-area') || btn.parentNode;
    if (panel && !panel.querySelector('.gmm-validate-panel')) {
      var wrap = document.createElement('div');
      wrap.className = 'gmm-validate-panel';
      wrap.innerHTML = '<div class="gmm-form-alert is-error" hidden role="alert"></div>';
      panel.appendChild(wrap);
    }

    var next = document.getElementById('continue-class-btn');
    if (!next) return;

    next.addEventListener('click', function (e) {
      var connected = btn.classList.contains('is-connected') ||
        document.body.classList.contains('is-stripe-connected');
      if (connected) return;

      e.preventDefault();
      e.stopImmediatePropagation();
      var alert = panel.querySelector('.gmm-form-alert');
      if (alert) {
        alert.hidden = false;
        alert.className = 'gmm-form-alert is-error';
        alert.innerHTML = '<i class="far fa-circle-exclamation"></i><span>' + MSG.payment + '</span>';
      }
      setButtonState(next, 'error', 'Connect first');
    }, true);
  }

  function wireAvailabilityComplete() {
    var btn = document.getElementById('complete-setup-btn');
    if (!btn || btn.__gmmAvailBound) return;
    btn.__gmmAvailBound = true;

    btn.addEventListener('click', function (e) {
      var list = document.getElementById('added-slots-list');
      var hasSlots = !!(list && list.querySelectorAll('.slot-card').length);
      if (typeof window.gmmHasAvailabilitySlots === 'function') {
        hasSlots = window.gmmHasAvailabilitySlots();
      }

      if (hasSlots) return;

      e.preventDefault();
      e.stopImmediatePropagation();

      var host = btn.closest('.onboarding-actions, .availability-slots-card, .td-card') || btn.parentNode;
      var alert = host.querySelector('.gmm-form-alert');
      if (!alert) {
        alert = document.createElement('div');
        alert.className = 'gmm-form-alert is-error';
        host.insertBefore(alert, btn);
      }
      alert.hidden = false;
      alert.innerHTML = '<i class="far fa-circle-exclamation"></i><span>' + MSG.slots + '</span>';
      setButtonState(btn, 'error', 'Add a slot');
    }, true);
  }

  /* ---------- Boot ---------- */

  function init() {
    Array.prototype.forEach.call(document.querySelectorAll('form[id]'), function (form) {
      if (FORM_RULES[form.id]) bindForm(form);
    });

    Array.prototype.forEach.call(document.querySelectorAll('.password-toggle, #password-toggle'), wirePasswordToggle);

    wirePaymentConnect();
    wireAvailabilityComplete();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

  window.GMMValidate = {
    init: init,
    validateForm: function (formOrId) {
      var form = typeof formOrId === 'string' ? document.getElementById(formOrId) : formOrId;
      if (!form || !FORM_RULES[form.id]) return false;
      return validateForm(form, FORM_RULES[form.id]);
    },
    setButtonState: setButtonState,
    showFormAlert: showFormAlert,
    rules: FORM_RULES
  };
})(window, document);
