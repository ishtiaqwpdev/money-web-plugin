<?php
/**
 * Template: booking-form
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="gmm-wrapper gmm-frontend"><!-- booking area -->
        <div class="booking-page-area py-120">
            <div class="container">

                <!-- progress stepper -->
                <ol class="bk-stepper" id="bk-stepper" aria-label="Booking progress">
                    <li class="bk-step is-complete" data-step="1">
                        <span class="bk-step-num">1</span>
                        <span class="bk-step-label">Select Lesson</span>
                    </li>
                    <li class="bk-step is-active" data-step="2">
                        <span class="bk-step-num">2</span>
                        <span class="bk-step-label">Choose Schedule</span>
                    </li>
                    <li class="bk-step" data-step="3">
                        <span class="bk-step-num">3</span>
                        <span class="bk-step-label">Confirm Booking</span>
                    </li>
                </ol>

                <div class="gospel-alert gospel-alert-error" id="bk-error" hidden>
                    <i class="far fa-circle-exclamation"></i>
                    <span id="bk-error-text">Please select a class, date, and time.</span>
                </div>
                <div class="gospel-alert gospel-alert-success" id="bk-success" hidden>
                    <i class="far fa-circle-check"></i>
                    <span>Booking confirmed (demo). Redirecting to payment…</span>
                </div>

                <form action="#" method="post" id="bk-booking-form" novalidate>
                    <div class="bk-layout">

                        <!-- left column -->
                        <div class="bk-main-col">

                            <!-- lesson selection -->
                            <section class="td-card bk-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Choose Your Lesson</h3>
                                        <p>Select the class you want to book with your instructor.</p>
                                    </div>
                                </div>

                                <div class="bk-teacher-row">
                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="John Smith" class="bk-teacher-avatar">
                                    <div>
                                        <h4>John Smith</h4>
                                        <span class="bk-teacher-role">Gospel Piano Instructor</span>
                                        <a href="student-teacher-profile.html" class="td-link">View Profile</a>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="bk-select-class">Select Class</label>
                                    <select class="form-control form-select" id="bk-select-class" required>
                                        <option value="">Choose a class</option>
                                        <option value="Beginner Gospel Piano Worship Lessons" data-short="Beginner Gospel Piano" data-duration="60 Minutes" data-level="Beginner" data-price="40" selected>
                                            Beginner Gospel Piano Worship Lessons
                                        </option>
                                        <option value="Advanced Gospel Piano Techniques" data-short="Advanced Gospel Piano" data-duration="60 Minutes" data-level="Advanced" data-price="55">
                                            Advanced Gospel Piano Techniques
                                        </option>
                                        <option value="Worship Keyboard Training" data-short="Worship Keyboard Training" data-duration="45 Minutes" data-level="Intermediate" data-price="45">
                                            Worship Keyboard Training
                                        </option>
                                    </select>
                                </div>

                                <div class="bk-lesson-details" id="bk-lesson-details">
                                    <div class="bk-detail-item">
                                        <span>Duration</span>
                                        <strong id="bk-detail-duration">60 Minutes</strong>
                                    </div>
                                    <div class="bk-detail-item">
                                        <span>Level</span>
                                        <strong id="bk-detail-level">Beginner</strong>
                                    </div>
                                    <div class="bk-detail-item">
                                        <span>Price</span>
                                        <strong id="bk-detail-price">$40</strong>
                                    </div>
                                </div>
                            </section>

                            <!-- schedule -->
                            <section class="td-card bk-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Choose Date &amp; Time</h3>
                                        <p>Pick an available date, then select a time slot.</p>
                                    </div>
                                </div>

                                <div class="bk-calendar-wrap">
                                    <div class="bk-calendar-header">
                                        <button type="button" class="bk-cal-nav" id="bk-cal-prev" aria-label="Previous month">
                                            <i class="far fa-angle-left"></i>
                                        </button>
                                        <strong id="bk-cal-month">March 2026</strong>
                                        <button type="button" class="bk-cal-nav" id="bk-cal-next" aria-label="Next month">
                                            <i class="far fa-angle-right"></i>
                                        </button>
                                    </div>
                                    <div class="bk-cal-weekdays" aria-hidden="true">
                                        <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                    </div>
                                    <div class="bk-cal-grid" id="bk-cal-grid" role="grid" aria-label="Select an available date"></div>
                                    <p class="bk-cal-hint"><i class="far fa-circle-info"></i> Orange dates are available for booking (demo).</p>
                                </div>

                                <div class="bk-slots-block">
                                    <h4>Available Time Slots</h4>
                                    <div class="bk-time-slots" role="group" aria-label="Available time slots">
                                        <button type="button" class="bk-time-slot" data-time="09:00 AM">09:00 AM</button>
                                        <button type="button" class="bk-time-slot" data-time="10:00 AM">10:00 AM</button>
                                        <button type="button" class="bk-time-slot" data-time="11:00 AM">11:00 AM</button>
                                        <button type="button" class="bk-time-slot" data-time="02:00 PM">02:00 PM</button>
                                        <button type="button" class="bk-time-slot" data-time="03:00 PM">03:00 PM</button>
                                    </div>
                                    <input type="hidden" id="bk-selected-date" name="booking_date" value="">
                                    <input type="hidden" id="bk-selected-time" name="booking_time" value="">
                                </div>
                            </section>

                            <!-- notes -->
                            <section class="td-card bk-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Special Notes For Teacher</h3>
                                        <p>Optional message for your instructor before the lesson.</p>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="bk-notes" class="visually-hidden">Special Notes For Teacher</label>
                                    <textarea class="form-control" id="bk-notes" name="notes" rows="4"
                                        placeholder="Tell your instructor about your learning goals or requirements."></textarea>
                                </div>
                            </section>

                        </div>

                        <!-- right column summary -->
                        <aside class="bk-side-col">
                            <div class="td-card bk-summary-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Booking Summary</h3>
                                        <p>Review your lesson details before payment.</p>
                                    </div>
                                </div>

                                <ul class="bk-summary-list">
                                    <li>
                                        <span>Teacher</span>
                                        <strong>John Smith</strong>
                                    </li>
                                    <li>
                                        <span>Class</span>
                                        <strong id="bk-sum-class">Beginner Gospel Piano</strong>
                                    </li>
                                    <li>
                                        <span>Date</span>
                                        <strong id="bk-sum-date">Select a date</strong>
                                    </li>
                                    <li>
                                        <span>Time</span>
                                        <strong id="bk-sum-time">Select a time</strong>
                                    </li>
                                    <li>
                                        <span>Duration</span>
                                        <strong id="bk-sum-duration">60 Minutes</strong>
                                    </li>
                                    <li class="bk-summary-total">
                                        <span>Total</span>
                                        <strong id="bk-sum-total">$40</strong>
                                    </li>
                                </ul>

                                <button type="submit" class="theme-btn w-100" id="bk-proceed-btn" disabled>
                                    <i class="far fa-credit-card"></i> Proceed To Payment
                                </button>
                                <a href="student-teacher-profile.html" class="bk-back-link">
                                    <i class="far fa-arrow-left"></i> Back to Teacher Profile
                                </a>
                            </div>
                        </aside>

                    </div>
                </form>

            </div>
        </div>
        <!-- booking area end -->

    <!-- booking page interactions (frontend demo) -->
    <script>
        (function () {
            var classSelect = document.getElementById('bk-select-class');
            var dateInput = document.getElementById('bk-selected-date');
            var timeInput = document.getElementById('bk-selected-time');
            var proceedBtn = document.getElementById('bk-proceed-btn');
            var form = document.getElementById('bk-booking-form');
            var errorBox = document.getElementById('bk-error');
            var errorText = document.getElementById('bk-error-text');
            var successBox = document.getElementById('bk-success');
            var stepper = document.getElementById('bk-stepper');
            var slots = document.querySelectorAll('.bk-time-slot');
            var calGrid = document.getElementById('bk-cal-grid');
            var calMonth = document.getElementById('bk-cal-month');
            var calPrev = document.getElementById('bk-cal-prev');
            var calNext = document.getElementById('bk-cal-next');

            var viewYear = 2026;
            var viewMonth = 2; /* March (0-index) */
            var selectedDateKey = '';
            var monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

            /* Demo available dates in March 2026 */
            var availableDates = {
                '2026-3-16': true,
                '2026-3-18': true,
                '2026-3-20': true,
                '2026-3-23': true,
                '2026-3-25': true,
                '2026-3-27': true,
                '2026-4-1': true,
                '2026-4-3': true,
                '2026-4-6': true
            };

            function pad(n) { return n < 10 ? '0' + n : String(n); }

            function formatDisplayDate(y, m, d) {
                var date = new Date(y, m, d);
                var days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                return days[date.getDay()] + ', ' + monthNames[m] + ' ' + d + ', ' + y;
            }

            function getOptionData() {
                if (!classSelect || !classSelect.value) return null;
                var opt = classSelect.options[classSelect.selectedIndex];
                return {
                    full: opt.value,
                    short: opt.getAttribute('data-short') || opt.value,
                    duration: opt.getAttribute('data-duration') || '60 Minutes',
                    level: opt.getAttribute('data-level') || 'Beginner',
                    price: opt.getAttribute('data-price') || '40'
                };
            }

            function updateLessonDetails() {
                var data = getOptionData();
                if (!data) return;
                var setText = function (id, text) {
                    var el = document.getElementById(id);
                    if (el) el.textContent = text;
                };
                setText('bk-detail-duration', data.duration);
                setText('bk-detail-level', data.level);
                setText('bk-detail-price', '$' + data.price);
                setText('bk-sum-class', data.short);
                setText('bk-sum-duration', data.duration);
                setText('bk-sum-total', '$' + data.price);
                updateStepper();
                updateButtonState();
            }

            function updateSummaryDateTime() {
                var dateEl = document.getElementById('bk-sum-date');
                var timeEl = document.getElementById('bk-sum-time');
                if (dateEl) dateEl.textContent = dateInput.value || 'Select a date';
                if (timeEl) timeEl.textContent = timeInput.value || 'Select a time';
                updateStepper();
                updateButtonState();
            }

            function updateStepper() {
                if (!stepper) return;
                var steps = stepper.querySelectorAll('.bk-step');
                var hasClass = !!(classSelect && classSelect.value);
                var hasSchedule = !!(dateInput.value && timeInput.value);
                steps.forEach(function (step) {
                    step.classList.remove('is-active', 'is-complete');
                    var n = parseInt(step.getAttribute('data-step'), 10);
                    if (n === 1) {
                        step.classList.add(hasClass ? 'is-complete' : 'is-active');
                    } else if (n === 2) {
                        if (hasSchedule) step.classList.add('is-complete');
                        else if (hasClass) step.classList.add('is-active');
                    } else if (n === 3) {
                        if (hasSchedule && hasClass) step.classList.add('is-active');
                    }
                });
            }

            function updateButtonState() {
                if (!proceedBtn) return;
                var ready = classSelect && classSelect.value && dateInput.value && timeInput.value;
                proceedBtn.disabled = !ready;
            }

            function renderCalendar() {
                if (!calGrid || !calMonth) return;
                calMonth.textContent = monthNames[viewMonth] + ' ' + viewYear;
                calGrid.innerHTML = '';

                var firstDay = new Date(viewYear, viewMonth, 1).getDay();
                var daysInMonth = new Date(viewYear, viewMonth + 1, 0).getDate();

                for (var i = 0; i < firstDay; i++) {
                    var empty = document.createElement('span');
                    empty.className = 'bk-cal-day is-empty';
                    empty.setAttribute('aria-hidden', 'true');
                    calGrid.appendChild(empty);
                }

                for (var day = 1; day <= daysInMonth; day++) {
                    var key = viewYear + '-' + (viewMonth + 1) + '-' + day;
                    var btn = document.createElement('button');
                    btn.type = 'button';
                    btn.className = 'bk-cal-day';
                    btn.textContent = String(day);
                    btn.setAttribute('data-key', key);

                    if (availableDates[key]) {
                        btn.classList.add('is-available');
                        btn.setAttribute('aria-label', 'Available ' + formatDisplayDate(viewYear, viewMonth, day));
                        if (selectedDateKey === key) btn.classList.add('is-selected');
                        btn.addEventListener('click', (function (y, m, d, k) {
                            return function () {
                                selectedDateKey = k;
                                dateInput.value = formatDisplayDate(y, m, d);
                                if (errorBox) errorBox.hidden = true;
                                renderCalendar();
                                updateSummaryDateTime();
                            };
                        })(viewYear, viewMonth, day, key));
                    } else {
                        btn.classList.add('is-disabled');
                        btn.disabled = true;
                        btn.setAttribute('aria-label', 'Unavailable');
                    }

                    calGrid.appendChild(btn);
                }
            }

            if (calPrev) {
                calPrev.addEventListener('click', function () {
                    viewMonth -= 1;
                    if (viewMonth < 0) {
                        viewMonth = 11;
                        viewYear -= 1;
                    }
                    renderCalendar();
                });
            }

            if (calNext) {
                calNext.addEventListener('click', function () {
                    viewMonth += 1;
                    if (viewMonth > 11) {
                        viewMonth = 0;
                        viewYear += 1;
                    }
                    renderCalendar();
                });
            }

            if (classSelect) {
                classSelect.addEventListener('change', function () {
                    if (errorBox) errorBox.hidden = true;
                    updateLessonDetails();
                });
            }

            slots.forEach(function (slot) {
                slot.addEventListener('click', function () {
                    slots.forEach(function (s) { s.classList.remove('is-selected'); });
                    slot.classList.add('is-selected');
                    timeInput.value = slot.getAttribute('data-time') || '';
                    if (errorBox) errorBox.hidden = true;
                    updateSummaryDateTime();
                });
            });

            if (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    if (successBox) successBox.hidden = true;
                    if (errorBox) errorBox.hidden = true;

                    if (!classSelect.value || !dateInput.value || !timeInput.value) {
                        if (errorText) errorText.textContent = 'Please select a class, date, and time.';
                        if (errorBox) {
                            errorBox.hidden = false;
                            errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        return;
                    }

                    if (successBox) {
                        successBox.hidden = false;
                        successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    if (proceedBtn) {
                        proceedBtn.disabled = true;
                        proceedBtn.innerHTML = '<i class="far fa-spinner fa-spin"></i> Processing…';
                    }

                    window.setTimeout(function () {
                        window.location.href = 'payment.html';
                    }, 1000);
                });
            }

            updateLessonDetails();
            renderCalendar();
            updateSummaryDateTime();
        })();
    </script>

</div><!-- .gmm-wrapper -->
