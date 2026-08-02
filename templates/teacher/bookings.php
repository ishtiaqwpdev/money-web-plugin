<?php
/**
 * Template: teacher-bookings
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_availability' ) ); ?>" class="td-nav-link" data-nav="availability"><i class="far fa-calendar-days"></i> Availability</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_bookings' ) ); ?>" class="td-nav-link active" data-nav="bookings"><i class="far fa-calendar-check"></i> My Bookings</a></li>
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

                        <!-- page header -->
                        <section class="td-card">
                            <div class="td-card-head">
                                <div>
                                    <span class="login-portal-badge">Booking Management</span>
                                    <h3>Your Lesson Bookings</h3>
                                    <p>Manage your upcoming lessons, confirm student requests, and keep track of completed sessions.</p>
                                </div>
                            </div>
                        </section>

                        <!-- quick summary -->
                        <section class="td-stats-grid bookings-summary-grid">
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value" id="summary-pending">12</span>
                                    <span class="td-stat-title">Pending Requests</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-calendar-check"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value" id="summary-upcoming">8</span>
                                    <span class="td-stat-title">Upcoming Lessons</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value" id="summary-completed">45</span>
                                    <span class="td-stat-title">Completed Lessons</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-users"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value" id="summary-students">25</span>
                                    <span class="td-stat-title">Total Students</span>
                                </div>
                            </div>
                        </section>

                        <!-- bookings table -->
                        <section class="td-card">
                            <div class="td-card-head">
                                <div>
                                    <h3>Bookings</h3>
                                    <p>Filter by status and manage each lesson request.</p>
                                </div>
                            </div>

                            <div class="booking-tabs" role="tablist" aria-label="Booking status filters">
                                <button type="button" class="booking-tab active" data-filter="all" role="tab" aria-selected="true">All</button>
                                <button type="button" class="booking-tab" data-filter="pending" role="tab" aria-selected="false">Pending</button>
                                <button type="button" class="booking-tab" data-filter="confirmed" role="tab" aria-selected="false">Confirmed</button>
                                <button type="button" class="booking-tab" data-filter="completed" role="tab" aria-selected="false">Completed</button>
                                <button type="button" class="booking-tab" data-filter="cancelled" role="tab" aria-selected="false">Cancelled</button>
                            </div>

                            <div class="table-responsive">
                                <table class="table td-table" id="bookings-table">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>Lesson</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="bookings-tbody">
                                        <tr data-status="pending" data-booking-id="1">
                                            <td data-label="Student"><strong>Sarah Johnson</strong></td>
                                            <td data-label="Lesson">Beginner Gospel Piano</td>
                                            <td data-label="Date">March 15, 2026</td>
                                            <td data-label="Time">10:00 AM</td>
                                            <td data-label="Duration">60 Minutes</td>
                                            <td data-label="Status"><span class="td-badge is-pending booking-status">Pending</span></td>
                                            <td data-label="Action">
                                                <div class="booking-actions">
                                                    <button type="button" class="theme-btn td-action-btn booking-confirm-btn">Confirm</button>
                                                    <button type="button" class="theme-btn theme-btn-outline td-action-btn booking-decline-btn">Decline</button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr data-status="confirmed" data-booking-id="2">
                                            <td data-label="Student"><strong>Michael Brown</strong></td>
                                            <td data-label="Lesson">Vocal Training</td>
                                            <td data-label="Date">March 18, 2026</td>
                                            <td data-label="Time">02:00 PM</td>
                                            <td data-label="Duration">45 Minutes</td>
                                            <td data-label="Status"><span class="td-badge is-confirmed booking-status">Confirmed</span></td>
                                            <td data-label="Action">
                                                <div class="booking-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline td-action-btn booking-details-btn"
                                                        data-student="Michael Brown"
                                                        data-image="assets/img/team/02.jpg"
                                                        data-lesson="Vocal Training"
                                                        data-date="March 18, 2026"
                                                        data-time="02:00 PM"
                                                        data-duration="45 Minutes"
                                                        data-payment="Paid"
                                                        data-notes="Focus on breath support and worship phrasing.">View Details</button>
                                                    <button type="button" class="theme-btn theme-btn-outline td-action-btn booking-message-btn">Message Student</button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr data-status="pending" data-booking-id="3">
                                            <td data-label="Student"><strong>Emily Davis</strong></td>
                                            <td data-label="Lesson">Beginner Gospel Piano</td>
                                            <td data-label="Date">March 20, 2026</td>
                                            <td data-label="Time">11:00 AM</td>
                                            <td data-label="Duration">60 Minutes</td>
                                            <td data-label="Status"><span class="td-badge is-pending booking-status">Pending</span></td>
                                            <td data-label="Action">
                                                <div class="booking-actions">
                                                    <button type="button" class="theme-btn td-action-btn booking-confirm-btn">Confirm</button>
                                                    <button type="button" class="theme-btn theme-btn-outline td-action-btn booking-decline-btn">Decline</button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr data-status="confirmed" data-booking-id="4">
                                            <td data-label="Student"><strong>David Wilson</strong></td>
                                            <td data-label="Lesson">Worship Leadership</td>
                                            <td data-label="Date">March 22, 2026</td>
                                            <td data-label="Time">04:00 PM</td>
                                            <td data-label="Duration">90 Minutes</td>
                                            <td data-label="Status"><span class="td-badge is-confirmed booking-status">Confirmed</span></td>
                                            <td data-label="Action">
                                                <div class="booking-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline td-action-btn booking-details-btn"
                                                        data-student="David Wilson"
                                                        data-image="assets/img/team/03.jpg"
                                                        data-lesson="Worship Leadership"
                                                        data-date="March 22, 2026"
                                                        data-time="04:00 PM"
                                                        data-duration="90 Minutes"
                                                        data-payment="Paid"
                                                        data-notes="Review Sunday setlist and team cues.">View Details</button>
                                                    <button type="button" class="theme-btn theme-btn-outline td-action-btn booking-message-btn">Message Student</button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr data-status="completed" data-booking-id="5">
                                            <td data-label="Student"><strong>Jessica Lee</strong></td>
                                            <td data-label="Lesson">Gospel Piano Basics</td>
                                            <td data-label="Date">March 5, 2026</td>
                                            <td data-label="Time">01:00 PM</td>
                                            <td data-label="Duration">60 Minutes</td>
                                            <td data-label="Status"><span class="td-badge is-completed booking-status">Completed</span></td>
                                            <td data-label="Action">
                                                <div class="booking-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline td-action-btn booking-review-btn">View Review</button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr data-status="cancelled" data-booking-id="6">
                                            <td data-label="Student"><strong>Chris Taylor</strong></td>
                                            <td data-label="Lesson">Hammond Organ Intro</td>
                                            <td data-label="Date">March 2, 2026</td>
                                            <td data-label="Time">03:00 PM</td>
                                            <td data-label="Duration">45 Minutes</td>
                                            <td data-label="Status"><span class="td-badge is-cancelled booking-status">Cancelled</span></td>
                                            <td data-label="Action">
                                                <div class="booking-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline td-action-btn booking-details-btn"
                                                        data-student="Chris Taylor"
                                                        data-image="assets/img/team/04.jpg"
                                                        data-lesson="Hammond Organ Intro"
                                                        data-date="March 2, 2026"
                                                        data-time="03:00 PM"
                                                        data-duration="45 Minutes"
                                                        data-payment="Refunded"
                                                        data-notes="Student cancelled due to schedule conflict.">View Details</button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <p class="td-empty-state" id="bookings-empty" hidden>
                                No bookings available yet.<br>
                                Your scheduled lessons will appear here.
                            </p>
                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- teacher dashboard end -->

    

<!-- booking details modal -->
    <div class="modal fade gospel-demo-modal" id="booking-details-modal" tabindex="-1" aria-labelledby="booking-details-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="booking-details-title">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body booking-modal-body">
                    <div class="booking-modal-profile">
                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="Student" id="modal-student-image">
                        <div>
                            <h4 id="modal-student-name">Student Name</h4>
                            <p id="modal-lesson-name">Lesson Name</p>
                        </div>
                    </div>
                    <ul class="booking-modal-list">
                        <li><span>Date</span><strong id="modal-date">—</strong></li>
                        <li><span>Time</span><strong id="modal-time">—</strong></li>
                        <li><span>Duration</span><strong id="modal-duration">—</strong></li>
                        <li><span>Payment Status</span><strong id="modal-payment">—</strong></li>
                        <li><span>Notes</span><strong id="modal-notes">—</strong></li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- review modal -->
    <div class="modal fade gospel-demo-modal" id="booking-review-modal" tabindex="-1" aria-labelledby="booking-review-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="booking-review-title">Student Review</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="td-rating mb-2">★★★★★</div>
                    <p>“Great gospel piano lesson. Clear instruction and very encouraging.”</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<!-- js -->
</div><!-- .gmm-wrapper -->

