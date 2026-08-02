<?php
/**
 * Template: teacher-availability
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $user_name ) ) {
	$user_name = 'Guest';
}
if ( ! isset( $user_first_name ) ) {
	$user_first_name = $user_name;
}
?>
<div class="gmm-wrapper gmm-dashboard">

<!-- teacher dashboard -->
        <div class="teacher-dashboard-area py-120">
            <div class="container">

                <!-- dashboard profile header -->
                <div class="td-profile-header">
                    <div class="td-profile-main">
                        <div class="td-profile-avatar">
                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="<?php echo esc_attr( $user_name ); ?>">
                        </div>
                        <div class="td-profile-meta">
                            <h2><?php echo esc_html( $user_name ); ?></h2>
                            <span class="td-role">Gospel Music Instructor</span>
                            <div class="td-profile-stats">
                                <span class="td-stat-item"><i class="fas fa-star"></i> 4.9</span>
                                <span class="td-stat-item"><i class="far fa-users"></i> 25 Students</span>
                                <span class="td-stat-item"><i class="far fa-books"></i> 12 Classes</span>
                            </div>
                        </div>
                    </div>
                    <div class="td-profile-actions">
                        <a href="teacher-onboarding-class.html" class="theme-btn"><i class="far fa-plus"></i> Create New Class</a>
                    </div>
                </div>

                <div class="td-shell">
                    <button type="button" class="td-sidebar-toggle theme-btn theme-btn-outline" id="td-sidebar-toggle" aria-expanded="false" aria-controls="td-sidebar">
                        <i class="far fa-bars"></i> Menu
                    </button>

                    <!-- sidebar -->
                    <aside class="td-sidebar" id="td-sidebar" aria-label="Instructor navigation">
                        <nav class="td-nav">
                            <ul>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_dashboard' ) ); ?>" class="td-nav-link" data-nav="dashboard"><i class="far fa-grid-2"></i> Dashboard</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_profile' ) ); ?>" class="td-nav-link" data-nav="profile"><i class="far fa-user"></i> My Profile</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_availability' ) ); ?>" class="td-nav-link active" data-nav="availability"><i class="far fa-calendar-days"></i> Availability</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_bookings' ) ); ?>" class="td-nav-link" data-nav="bookings"><i class="far fa-calendar-check"></i> My Bookings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>" class="td-nav-link" data-nav="classes"><i class="far fa-chalkboard"></i> My Classes</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_dashboard' ) ); ?>" class="td-nav-link" data-nav="messages"><i class="far fa-comments"></i> Messages</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_withdrawals' ) ); ?>" class="td-nav-link" data-nav="withdrawals"><i class="far fa-wallet"></i> Withdrawals</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_settings' ) ); ?>" class="td-nav-link" data-nav="settings"><i class="far fa-gear"></i> Settings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_login' ) ); ?>" class="td-nav-link td-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
                            </ul>
                        </nav>
                    </aside>
                    <div class="td-sidebar-backdrop" id="td-sidebar-backdrop" hidden></div>

                    <!-- main content -->
                    <div class="td-main">

                        <div class="gospel-alert gospel-alert-error" id="availability-error" hidden>
                            <i class="far fa-circle-exclamation"></i>
                            <span id="availability-error-text">Please select a date and add at least one time slot.</span>
                        </div>
                        <div class="gospel-alert gospel-alert-success" id="availability-success" hidden>
                            <i class="far fa-circle-check"></i>
                            <span>Availability saved successfully (demo).</span>
                        </div>

                        <!-- page header -->
                        <section class="td-card">
                            <div class="td-card-head">
                                <div>
                                    <span class="login-portal-badge">Availability Management</span>
                                    <h3>Manage Your Teaching Schedule</h3>
                                    <p>Set your available hours so students can easily book your gospel music lessons.</p>
                                </div>
                            </div>
                        </section>

                        <div class="availability-layout">
                            <div class="availability-calendar-card">
                                <div class="calendar-header">
                                    <button type="button" class="calendar-nav-btn" id="cal-prev" aria-label="Previous month">
                                        <i class="far fa-chevron-left"></i>
                                    </button>
                                    <h3 class="calendar-month-label" id="cal-month-label">January 2026</h3>
                                    <button type="button" class="calendar-nav-btn" id="cal-next" aria-label="Next month">
                                        <i class="far fa-chevron-right"></i>
                                    </button>
                                </div>

                                <div class="calendar-scroll">
                                    <div class="availability-calendar" id="availability-calendar" role="grid" aria-label="Availability calendar"></div>
                                </div>

                                <div class="calendar-legend">
                                    <span class="legend-item"><span class="legend-dot is-available"></span> Available Time</span>
                                    <span class="legend-item"><span class="legend-dot is-booked"></span> Booked Lesson</span>
                                    <span class="legend-item"><span class="legend-dot is-unavailable"></span> Unavailable</span>
                                </div>
                            </div>

                            <div class="availability-slots-card">
                                <div class="selected-date-block">
                                    <span class="selected-date-label">Selected Date</span>
                                    <strong id="selected-date-display">Select a date from the calendar</strong>
                                </div>

                                <div class="time-slot-form">
                                    <div class="row">
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="slot-start-time">Start Time</label>
                                                <select class="form-control form-select" id="slot-start-time">
                                                    <option value="">Select start time</option>
                                                    <option value="09:00 AM">09:00 AM</option>
                                                    <option value="10:00 AM">10:00 AM</option>
                                                    <option value="11:00 AM">11:00 AM</option>
                                                    <option value="01:00 PM">01:00 PM</option>
                                                    <option value="02:00 PM">02:00 PM</option>
                                                    <option value="03:00 PM">03:00 PM</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-sm-6">
                                            <div class="form-group">
                                                <label for="slot-end-time">End Time</label>
                                                <select class="form-control form-select" id="slot-end-time">
                                                    <option value="">Select end time</option>
                                                    <option value="10:00 AM">10:00 AM</option>
                                                    <option value="11:00 AM">11:00 AM</option>
                                                    <option value="12:00 PM">12:00 PM</option>
                                                    <option value="02:00 PM">02:00 PM</option>
                                                    <option value="03:00 PM">03:00 PM</option>
                                                    <option value="04:00 PM">04:00 PM</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <button type="button" class="theme-btn" id="add-time-slot">
                                        <i class="far fa-plus"></i> Add Time Slot
                                    </button>
                                </div>

                                <div class="added-slots-block">
                                    <h5>Current Availability</h5>
                                    <div class="added-slots-list" id="added-slots-list">
                                        <p class="slots-empty" id="slots-empty">No time slots added yet. Select a date and add your available hours.</p>
                                    </div>
                                </div>

                                <div class="repeat-weekly-block">
                                    <div class="repeat-toggle-row">
                                        <div class="repeat-copy">
                                            <strong>Repeat this schedule weekly</strong>
                                            <p>Automatically apply these available hours every week.</p>
                                        </div>
                                        <label class="gospel-switch" for="repeat-weekly">
                                            <input type="checkbox" id="repeat-weekly" name="repeat_weekly">
                                            <span class="gospel-switch-slider" aria-hidden="true"></span>
                                            <span class="visually-hidden">Repeat this schedule weekly</span>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="td-card-actions td-profile-form-actions">
                            <a href="<?php echo esc_url( gmm_get_page_link( 'teacher_dashboard' ) ); ?>" class="theme-btn theme-btn-outline"><i class="far fa-times"></i> Cancel</a>
                            <button type="button" class="theme-btn" id="save-availability-btn"><i class="far fa-check"></i> Save Availability</button>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- teacher dashboard end -->

    
</div><!-- .gmm-wrapper -->

