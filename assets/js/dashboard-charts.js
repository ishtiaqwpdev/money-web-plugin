/**
 * Gospel Music Mastery — Dashboard Charts
 * Chart.js helpers + auto-init for dashboard canvases.
 * Demo data only. Requires Chart.js (chart.umd.min.js).
 */
(function (window, document) {
  'use strict';

  if (!window.Chart) {
    console.warn('GMMCharts: Chart.js is not loaded.');
    return;
  }

  var COLORS = {
    orange: '#FFA500',
    orangeDeep: '#B45309',
    orangeLight: '#FFCE73',
    blue: '#3B82F6',
    green: '#22C55E',
    red: '#C0392B',
    gray: '#94A3B8',
    label: '#1F2937',
    tick: '#64748B',
    grid: 'rgba(148, 163, 184, 0.20)',
    surface: '#FFFFFF',
    white: '#FFFFFF'
  };

  var MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
  var MONTHS_FULL = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  var WEEKDAYS = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

  var REDUCE_MOTION = window.matchMedia
    ? window.matchMedia('(prefers-reduced-motion: reduce)').matches
    : false;

  Chart.defaults.font.family = "'Roboto', system-ui, -apple-system, 'Segoe UI', sans-serif";
  Chart.defaults.font.size = 12;
  Chart.defaults.color = COLORS.tick;
  Chart.defaults.animation.duration = REDUCE_MOTION ? 0 : 900;
  Chart.defaults.animation.easing = 'easeOutQuart';

  /* Draws the line left-to-right instead of fading the whole path in.
     Chart.js reads unknown keys on `animation` as per-property configs. */
  function progressiveLineAnimation(pointCount) {
    if (REDUCE_MOTION) return { duration: 0 };

    var step = 900 / Math.max(pointCount, 1);

    function previousY(ctx) {
      if (ctx.index === 0) {
        return ctx.chart.scales.y ? ctx.chart.scales.y.getPixelForValue(0) : 0;
      }
      var points = ctx.chart.getDatasetMeta(ctx.datasetIndex).data;
      return points[ctx.index - 1].getProps(['y'], true).y;
    }

    function stagger(flag) {
      return function (ctx) {
        if (ctx.type !== 'data' || ctx[flag]) return 0;
        ctx[flag] = true;
        return ctx.index * step;
      };
    }

    return {
      duration: step,
      easing: 'linear',
      x: { type: 'number', easing: 'linear', duration: step, from: NaN, delay: stagger('xStarted') },
      y: { type: 'number', easing: 'linear', duration: step, from: previousY, delay: stagger('yStarted') }
    };
  }

  /* Soft bloom around line strokes, tinted with each series colour. */
  Chart.register({
    id: 'gmmGlow',
    beforeDatasetDraw: function (chart, args) {
      if (chart.config.type !== 'line') return;
      var stroke = args.meta.dataset && args.meta.dataset.options
        ? args.meta.dataset.options.borderColor
        : COLORS.orange;

      chart.ctx.save();
      chart.ctx.shadowColor = typeof stroke === 'string' ? stroke : COLORS.orange;
      chart.ctx.shadowBlur = 12;
    },
    afterDatasetDraw: function (chart) {
      if (chart.config.type !== 'line') return;
      chart.ctx.restore();
    }
  });

  /* Vertical dashed guide line following the tooltip on line charts. */
  Chart.register({
    id: 'gmmCrosshair',
    afterDatasetsDraw: function (chart) {
      if (chart.config.type !== 'line') return;
      var active = chart.tooltip && chart.tooltip.getActiveElements
        ? chart.tooltip.getActiveElements()
        : [];
      if (!active.length) return;

      var ctx = chart.ctx;
      var area = chart.chartArea;
      var x = active[0].element.x;

      ctx.save();
      ctx.beginPath();
      ctx.setLineDash([4, 4]);
      ctx.lineWidth = 1;
      ctx.strokeStyle = 'rgba(31, 41, 55, 0.28)';
      ctx.moveTo(x, area.top);
      ctx.lineTo(x, area.bottom);
      ctx.stroke();
      ctx.restore();
    }
  });

  /* Total + caption rendered in the doughnut hole. */
  Chart.register({
    id: 'gmmDoughnutCenter',
    afterDatasetsDraw: function (chart, args, opts) {
      if (chart.config.type !== 'doughnut' || !opts || !opts.label) return;

      var meta = chart.getDatasetMeta(0);
      if (!meta || !meta.data.length) return;

      var total = chart.data.datasets[0].data.reduce(function (sum, value) {
        return sum + (Number(value) || 0);
      }, 0);

      var ctx = chart.ctx;
      var cx = meta.data[0].x;
      var cy = meta.data[0].y;

      ctx.save();
      ctx.textAlign = 'center';
      ctx.textBaseline = 'middle';
      ctx.fillStyle = COLORS.label;
      ctx.font = "800 26px 'Yantramanav', 'Roboto', sans-serif";
      ctx.fillText(total.toLocaleString('en-US'), cx, cy - 8);
      ctx.fillStyle = COLORS.tick;
      ctx.font = "600 11px 'Roboto', sans-serif";
      ctx.fillText(String(opts.label).toUpperCase(), cx, cy + 15);
      ctx.restore();
    }
  });

  /* Bars grow from the baseline one after another. */
  function barDelay(ctx) {
    if (REDUCE_MOTION || ctx.type !== 'data' || ctx.mode !== 'default') return 0;
    return ctx.dataIndex * 45 + ctx.datasetIndex * 90;
  }

  var chartInstances = {};

  function withAlpha(hex, alpha) {
    var value = hex.replace('#', '');
    if (value.length === 3) {
      value = value[0] + value[0] + value[1] + value[1] + value[2] + value[2];
    }
    var num = parseInt(value, 16);
    return 'rgba(' + ((num >> 16) & 255) + ', ' + ((num >> 8) & 255) + ', ' + (num & 255) + ', ' + alpha + ')';
  }

  /* Scriptable fill: needs chartArea, which is undefined on the first layout pass. */
  function verticalGradient(hex, topAlpha, bottomAlpha) {
    return function (context) {
      var chart = context.chart;
      var area = chart.chartArea;
      if (!area) return withAlpha(hex, topAlpha);

      var gradient = chart.ctx.createLinearGradient(0, area.top, 0, area.bottom);
      gradient.addColorStop(0, withAlpha(hex, topAlpha));
      gradient.addColorStop(1, withAlpha(hex, bottomAlpha));
      return gradient;
    };
  }

  function getCanvas(id) {
    return document.getElementById(id);
  }

  function destroyIfExists(id) {
    if (chartInstances[id]) {
      chartInstances[id].destroy();
      delete chartInstances[id];
    }
  }

  function moneyTick(value) {
    if (value >= 1000) {
      return '$' + (value / 1000).toFixed(value % 1000 === 0 ? 0 : 1) + 'k';
    }
    return '$' + value;
  }

  function sharedTooltip(isMoney) {
    return {
      backgroundColor: COLORS.white,
      titleColor: COLORS.tick,
      bodyColor: COLORS.label,
      borderColor: COLORS.orange,
      borderWidth: 1,
      padding: { top: 10, right: 14, bottom: 10, left: 14 },
      cornerRadius: 10,
      caretSize: 6,
      caretPadding: 10,
      titleFont: { size: 11, weight: '600' },
      bodyFont: { size: 13, weight: '700' },
      bodySpacing: 6,
      displayColors: true,
      usePointStyle: true,
      boxWidth: 8,
      boxHeight: 8,
      boxPadding: 6,
      callbacks: {
        label: function (context) {
          var value = context.parsed.y !== undefined && context.parsed.y !== null
            ? context.parsed.y
            : context.parsed;
          var formatted = isMoney ? moneyTick(value) : Number(value).toLocaleString('en-US');
          return ' ' + context.dataset.label + ': ' + formatted;
        }
      }
    };
  }

  function sharedLegend(show) {
    return {
      display: !!show,
      position: 'bottom',
      align: 'center',
      labels: {
        color: COLORS.label,
        usePointStyle: true,
        pointStyle: 'circle',
        boxWidth: 8,
        boxHeight: 8,
        padding: 18,
        font: { size: 12, weight: '600' }
      }
    };
  }

  function upperCaseTick(value) {
    return String(this.getLabelForValue(value)).toUpperCase();
  }

  function sharedScales(isMoney) {
    return {
      x: {
        border: { display: false },
        grid: { color: COLORS.grid, drawTicks: false, lineWidth: 1 },
        ticks: {
          color: COLORS.tick,
          padding: 10,
          font: { size: 10, weight: '700' },
          maxRotation: 0,
          autoSkipPadding: 10,
          callback: upperCaseTick
        }
      },
      y: {
        beginAtZero: true,
        border: { display: false },
        grid: { color: COLORS.grid, drawTicks: false, lineWidth: 1 },
        ticks: {
          color: COLORS.tick,
          padding: 12,
          maxTicksLimit: 5,
          font: { size: 10, weight: '700' },
          callback: isMoney
            ? function (value) { return moneyTick(value); }
            : function (value) { return Number(value).toLocaleString('en-US'); }
        }
      }
    };
  }

  function lineAreaDefaults(opts) {
    var settings = opts || {};
    return {
      responsive: true,
      maintainAspectRatio: false,
      animation: progressiveLineAnimation(settings.points || 12),
      layout: { padding: { top: 10, right: 8, bottom: 0, left: 0 } },
      interaction: { mode: 'index', intersect: false },
      hover: { mode: 'index', intersect: false },
      plugins: {
        legend: sharedLegend(settings.legend),
        tooltip: sharedTooltip(!!settings.money)
      },
      scales: sharedScales(!!settings.money),
      elements: {
        line: { tension: 0.4, borderWidth: 3, borderCapStyle: 'round', borderJoinStyle: 'round' },
        point: {
          radius: 0,
          hitRadius: 14,
          hoverRadius: 6,
          hoverBorderWidth: 3,
          backgroundColor: COLORS.surface
        }
      }
    };
  }

  function createChart(id, config) {
    var canvas = getCanvas(id);
    if (!canvas) return null;
    destroyIfExists(id);

    config = config || {};
    config.options = config.options || {};
    config.options.responsive = config.options.responsive !== false;
    config.options.maintainAspectRatio = false;
    config.options.devicePixelRatio = Math.max(window.devicePixelRatio || 1, 1);

    var chart = new Chart(canvas.getContext('2d'), config);
    chartInstances[id] = chart;

    // Re-measure after layout so DPR / container size stay sharp
    requestAnimationFrame(function () {
      if (chartInstances[id]) chartInstances[id].resize();
    });
    window.setTimeout(function () {
      if (chartInstances[id]) chartInstances[id].resize();
    }, 120);

    return chart;
  }

  /* ---------- Shared builders ---------- */

  function buildAreaChart(id, labels, data, datasetLabel, money) {
    return createChart(id, {
      type: 'line',
      data: {
        labels: labels,
        datasets: [{
          label: datasetLabel,
          data: data,
          borderColor: COLORS.orange,
          backgroundColor: verticalGradient(COLORS.orange, 0.35, 0),
          fill: true,
          pointBackgroundColor: COLORS.surface,
          pointBorderColor: COLORS.orange,
          pointHoverBackgroundColor: COLORS.white,
          pointHoverBorderColor: COLORS.orange
        }]
      },
      options: lineAreaDefaults({ money: !!money, legend: false, points: labels.length })
    });
  }

  function buildLineChart(id, labels, datasets, money) {
    var single = datasets.length === 1;
    var mapped = datasets.map(function (ds) {
      var color = ds.color || COLORS.orange;
      return {
        label: ds.label,
        data: ds.data,
        borderColor: color,
        backgroundColor: single ? verticalGradient(color, 0.32, 0) : withAlpha(color, 0.1),
        fill: single,
        pointBackgroundColor: COLORS.surface,
        pointBorderColor: color,
        pointHoverBackgroundColor: COLORS.white,
        pointHoverBorderColor: color
      };
    });
    return createChart(id, {
      type: 'line',
      data: { labels: labels, datasets: mapped },
      options: lineAreaDefaults({ money: !!money, legend: !single, points: labels.length })
    });
  }

  function buildBarChart(id, labels, datasets, money) {
    var mapped = datasets.map(function (ds) {
      var color = ds.color || COLORS.orange;
      return {
        label: ds.label,
        data: ds.data,
        backgroundColor: verticalGradient(color, 1, 0.45),
        hoverBackgroundColor: color,
        borderRadius: { topLeft: 10, topRight: 10, bottomLeft: 0, bottomRight: 0 },
        borderSkipped: false,
        maxBarThickness: 26,
        categoryPercentage: 0.65,
        barPercentage: 0.85
      };
    });
    return createChart(id, {
      type: 'bar',
      data: { labels: labels, datasets: mapped },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { duration: REDUCE_MOTION ? 0 : 900, easing: 'easeOutQuart', delay: barDelay },
        layout: { padding: { top: 10, right: 8, bottom: 0, left: 0 } },
        interaction: { mode: 'index', intersect: false },
        plugins: {
          legend: sharedLegend(datasets.length > 1),
          tooltip: sharedTooltip(!!money)
        },
        scales: sharedScales(!!money)
      }
    });
  }

  function buildDoughnutChart(id, labels, data, colors, centerLabel) {
    return createChart(id, {
      type: 'doughnut',
      data: {
        labels: labels,
        datasets: [{
          data: data,
          backgroundColor: colors,
          borderColor: COLORS.white,
          borderWidth: 3,
          borderRadius: 8,
          spacing: 3,
          hoverOffset: 10
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        animation: { animateRotate: true, animateScale: true, duration: REDUCE_MOTION ? 0 : 1000, easing: 'easeOutQuart' },
        cutout: '72%',
        layout: { padding: 6 },
        plugins: {
          gmmDoughnutCenter: { label: centerLabel || 'Total' },
          legend: {
            position: 'bottom',
            align: 'center',
            labels: {
              color: COLORS.label,
              usePointStyle: true,
              pointStyle: 'circle',
              boxWidth: 8,
              boxHeight: 8,
              padding: 14,
              font: { size: 12, weight: '600' }
            }
          },
          tooltip: {
            backgroundColor: COLORS.white,
            titleColor: COLORS.tick,
            bodyColor: COLORS.label,
            borderColor: COLORS.orange,
            borderWidth: 1,
            padding: { top: 10, right: 14, bottom: 10, left: 14 },
            cornerRadius: 10,
            caretSize: 6,
            titleFont: { size: 11, weight: '600' },
            bodyFont: { size: 13, weight: '700' },
            usePointStyle: true,
            boxWidth: 8,
            boxHeight: 8,
            boxPadding: 6,
            callbacks: {
              label: function (context) {
                var total = context.dataset.data.reduce(function (sum, value) {
                  return sum + (Number(value) || 0);
                }, 0);
                var value = Number(context.parsed) || 0;
                var share = total ? Math.round((value / total) * 100) : 0;
                return ' ' + value.toLocaleString('en-US') + ' (' + share + '%)';
              }
            }
          }
        }
      }
    });
  }

  /* ---------- Named initializers ---------- */

  function initRevenueChart() {
    return buildAreaChart(
      'gmm-admin-revenue',
      MONTHS_FULL,
      [2800, 3400, 3900, 4600, 5100, 5200, 5800, 6100, 6400, 7000, 7600, 8200],
      'Monthly Revenue',
      true
    );
  }

  function initUserGrowthChart() {
    return buildBarChart('gmm-admin-user-growth', MONTHS_SHORT.slice(0, 6), [
      { label: 'Students Growth', data: [80, 110, 140, 180, 210, 250], color: COLORS.orange },
      { label: 'Teachers Growth', data: [6, 8, 10, 12, 14, 16], color: COLORS.orangeDeep }
    ]);
  }

  function initPlatformDistributionChart() {
    return buildDoughnutChart(
      'gmm-admin-platform',
      ['Students', 'Teachers', 'Classes'],
      [1250, 85, 320],
      [COLORS.orange, COLORS.orangeDeep, COLORS.orangeLight],
      'Total Records'
    );
  }

  function initTeacherEarningsChart() {
    return buildLineChart(
      'gmm-teacher-earnings',
      MONTHS_SHORT,
      [{ label: 'Monthly Earnings', data: [120, 180, 210, 260, 300, 340, 380, 420, 390, 450, 480, 520], color: COLORS.orange }],
      true
    );
  }

  function initLessonChart() {
    return buildDoughnutChart(
      'gmm-teacher-lessons',
      ['Completed Lessons', 'Upcoming Lessons', 'Cancelled Lessons'],
      [45, 12, 5],
      [COLORS.green, COLORS.orangeDeep, COLORS.red],
      'Lessons'
    );
  }

  function initTeacherStudentGrowthChart() {
    return buildBarChart('gmm-teacher-students', MONTHS_SHORT.slice(0, 6), [
      { label: 'New Students', data: [2, 3, 4, 5, 3, 6], color: COLORS.orange }
    ]);
  }

  function initStudentLearningChart() {
    return buildLineChart(
      'gmm-student-learning',
      MONTHS_SHORT,
      [{ label: 'Learning Activity', data: [4, 6, 8, 7, 10, 12, 9, 11, 13, 12, 14, 16], color: COLORS.orange }]
    );
  }

  function initStudentLessonStatusChart() {
    return buildDoughnutChart(
      'gmm-student-lesson-status',
      ['Completed', 'Upcoming', 'Remaining'],
      [24, 3, 8],
      [COLORS.green, COLORS.orangeDeep, COLORS.blue],
      'Lessons'
    );
  }

  function initStudentPracticeChart() {
    return buildBarChart('gmm-student-practice', WEEKDAYS, [
      { label: 'Practice Hours', data: [1.5, 2, 1, 2.5, 2, 3, 1.5], color: COLORS.orange }
    ]);
  }

  function initTeacherRegistrationChart() {
    return buildLineChart(
      'gmm-at-registration',
      MONTHS_SHORT,
      [{ label: 'New Teachers', data: [4, 6, 5, 8, 7, 9, 11, 8, 10, 12, 9, 14], color: COLORS.orange }]
    );
  }

  function initTeacherStatusChart() {
    return buildDoughnutChart(
      'gmm-at-status',
      ['Approved', 'Pending', 'Rejected', 'Suspended'],
      [52, 18, 8, 7],
      [COLORS.green, COLORS.orangeDeep, COLORS.red, COLORS.gray],
      'Teachers'
    );
  }

  function initStudentRegistrationChart() {
    return buildLineChart(
      'gmm-as-registration',
      MONTHS_SHORT,
      [{ label: 'New Students', data: [40, 55, 62, 70, 85, 90, 100, 110, 95, 120, 130, 145], color: COLORS.orange }]
    );
  }

  function initStudentLevelChart() {
    return buildDoughnutChart(
      'gmm-as-level',
      ['Beginner', 'Intermediate', 'Advanced'],
      [520, 430, 300],
      [COLORS.orange, COLORS.orangeDeep, COLORS.orangeLight],
      'Students'
    );
  }

  function initClassesCreatedChart() {
    return buildLineChart(
      'gmm-ac-created',
      MONTHS_SHORT,
      [{ label: 'Classes Created', data: [12, 18, 22, 28, 30, 35, 32, 40, 38, 42, 45, 50], color: COLORS.orange }]
    );
  }

  function initClassCategoryChart() {
    return buildDoughnutChart(
      'gmm-ac-category',
      ['Piano', 'Vocals', 'Guitar', 'Drums', 'Theory'],
      [90, 75, 55, 40, 60],
      [COLORS.orange, COLORS.orangeDeep, COLORS.orangeLight, COLORS.green, COLORS.blue],
      'Classes'
    );
  }

  function initBookingChart() {
    return buildLineChart(
      'gmm-ab-analytics',
      MONTHS_SHORT,
      [{ label: 'Bookings', data: [28, 34, 40, 48, 52, 60, 58, 66, 70, 74, 80, 85], color: COLORS.orange }]
    );
  }

  function initBookingStatusChart() {
    return buildDoughnutChart(
      'gmm-ab-status',
      ['Confirmed', 'Pending', 'Completed', 'Cancelled'],
      [120, 30, 350, 40],
      [COLORS.green, COLORS.orangeDeep, COLORS.orange, COLORS.red],
      'Bookings'
    );
  }

  function initPaymentChart() {
    return buildAreaChart(
      'gmm-ap-revenue',
      MONTHS_SHORT,
      [6200, 7100, 8300, 9200, 9800, 9400, 10100, 10800, 11200, 11800, 12400, 13000],
      'Monthly Revenue',
      true
    );
  }

  function initPaymentStatusChart() {
    return buildDoughnutChart(
      'gmm-ap-status',
      ['Completed', 'Pending', 'Failed', 'Refunded'],
      [680, 90, 35, 45],
      [COLORS.green, COLORS.orangeDeep, COLORS.red, COLORS.gray],
      'Transactions'
    );
  }

  function initProgramEnrollmentChart() {
    return buildBarChart('gmm-apr-enrollment', ['Piano', 'Vocals', 'Guitar', 'Drums', 'Theory', 'Worship'], [
      { label: 'Enrollments', data: [250, 320, 180, 145, 210, 95], color: COLORS.orange }
    ]);
  }

  function initProgramCategoryChart() {
    return buildDoughnutChart(
      'gmm-apr-category',
      ['Piano', 'Vocals', 'Guitar', 'Drums', 'Theory', 'Worship'],
      [2, 2, 1, 1, 1, 1],
      [COLORS.orange, COLORS.orangeDeep, COLORS.orangeLight, COLORS.green, COLORS.blue, COLORS.gray],
      'Programs'
    );
  }

  function initBlogViewsChart() {
    return buildLineChart(
      'gmm-abl-views',
      MONTHS_SHORT,
      [{ label: 'Article Views', data: [420, 510, 580, 640, 720, 800, 760, 880, 940, 1020, 1100, 1250], color: COLORS.orange }]
    );
  }

  function initBlogCategoryChart() {
    return buildDoughnutChart(
      'gmm-abl-category',
      ['Music Education', 'Piano', 'Vocals', 'Worship', 'Teacher Tips'],
      [28, 24, 22, 26, 20],
      [COLORS.orange, COLORS.orangeDeep, COLORS.orangeLight, COLORS.green, COLORS.gray],
      'Posts'
    );
  }

  function autoInit() {
    var map = [
      ['gmm-admin-revenue', initRevenueChart],
      ['gmm-admin-user-growth', initUserGrowthChart],
      ['gmm-admin-platform', initPlatformDistributionChart],
      ['gmm-teacher-earnings', initTeacherEarningsChart],
      ['gmm-teacher-lessons', initLessonChart],
      ['gmm-teacher-students', initTeacherStudentGrowthChart],
      ['gmm-student-learning', initStudentLearningChart],
      ['gmm-student-lesson-status', initStudentLessonStatusChart],
      ['gmm-student-practice', initStudentPracticeChart],
      ['gmm-at-registration', initTeacherRegistrationChart],
      ['gmm-at-status', initTeacherStatusChart],
      ['gmm-as-registration', initStudentRegistrationChart],
      ['gmm-as-level', initStudentLevelChart],
      ['gmm-ac-created', initClassesCreatedChart],
      ['gmm-ac-category', initClassCategoryChart],
      ['gmm-ab-analytics', initBookingChart],
      ['gmm-ab-status', initBookingStatusChart],
      ['gmm-ap-revenue', initPaymentChart],
      ['gmm-ap-status', initPaymentStatusChart],
      ['gmm-apr-enrollment', initProgramEnrollmentChart],
      ['gmm-apr-category', initProgramCategoryChart],
      ['gmm-abl-views', initBlogViewsChart],
      ['gmm-abl-category', initBlogCategoryChart]
    ];

    var pending = map.filter(function (item) { return getCanvas(item[0]); });
    if (!pending.length) return;

    /* Build each chart as it scrolls in: the entry animation is actually
       seen, and off-screen canvases cost nothing on first paint. */
    if (!('IntersectionObserver' in window)) {
      pending.forEach(function (item) { item[1](); });
      return;
    }

    var observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (!entry.isIntersecting) return;
          observer.unobserve(entry.target);

          var match = pending.filter(function (item) { return item[0] === entry.target.id; })[0];
          if (match) match[1]();
        });
      },
      { rootMargin: '120px 0px', threshold: 0.01 }
    );

    pending.forEach(function (item) { observer.observe(getCanvas(item[0])); });
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', autoInit);
  } else {
    autoInit();
  }

  window.GMMCharts = {
    initRevenueChart: initRevenueChart,
    initUserGrowthChart: initUserGrowthChart,
    initPlatformDistributionChart: initPlatformDistributionChart,
    initTeacherEarningsChart: initTeacherEarningsChart,
    initLessonChart: initLessonChart,
    initTeacherStudentGrowthChart: initTeacherStudentGrowthChart,
    initStudentLearningChart: initStudentLearningChart,
    initStudentLessonStatusChart: initStudentLessonStatusChart,
    initStudentPracticeChart: initStudentPracticeChart,
    initTeacherRegistrationChart: initTeacherRegistrationChart,
    initTeacherStatusChart: initTeacherStatusChart,
    initStudentRegistrationChart: initStudentRegistrationChart,
    initStudentLevelChart: initStudentLevelChart,
    initClassesCreatedChart: initClassesCreatedChart,
    initClassCategoryChart: initClassCategoryChart,
    initBookingChart: initBookingChart,
    initBookingStatusChart: initBookingStatusChart,
    initPaymentChart: initPaymentChart,
    initPaymentStatusChart: initPaymentStatusChart,
    initProgramEnrollmentChart: initProgramEnrollmentChart,
    initProgramCategoryChart: initProgramCategoryChart,
    initBlogViewsChart: initBlogViewsChart,
    initBlogCategoryChart: initBlogCategoryChart,
    buildAreaChart: buildAreaChart,
    buildBarChart: buildBarChart,
    buildDoughnutChart: buildDoughnutChart,
    buildLineChart: buildLineChart,
    autoInit: autoInit
  };
})(window, document);
