<?php
/**
 * Template: student-bookings
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

        <!-- student bookings -->
        <div class="student-dashboard-area py-120">
            <div class="container">

                <!-- dashboard profile header -->
                <div class="sd-profile-header">
                    <div class="sd-profile-main">
                        <div class="sd-profile-avatar">
                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="<?php echo esc_attr( $user_name ); ?>">
                        </div>
                        <div class="sd-profile-meta">
                            <h2><?php echo esc_html( $user_name ); ?></h2>
                            <span class="sd-role">Music Student</span>
                            <div class="sd-profile-stats">
                                <span class="sd-stat-item"><i class="far fa-signal"></i> Learning Level: Intermediate</span>
                                <span class="sd-stat-item"><i class="far fa-music"></i> Gospel Piano</span>
                            </div>
                        </div>
                    </div>
                    <div class="sd-profile-actions">
                        <a href="teachers.html" class="theme-btn"><i class="far fa-calendar-plus"></i> Book a Lesson</a>
                    </div>
                </div>

                <div class="sd-shell">
                    <button type="button" class="sd-sidebar-toggle theme-btn theme-btn-outline" id="sd-sidebar-toggle"
                        aria-expanded="false" aria-controls="sd-sidebar">
                        <i class="far fa-bars"></i> Menu
                    </button>

                    <!-- sidebar -->
                    <aside class="sd-sidebar" id="sd-sidebar" aria-label="Student navigation">
                        <nav class="sd-nav">
                            <ul>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_dashboard' ) ); ?>" class="sd-nav-link" data-nav="dashboard"><i class="far fa-grid-2"></i> Dashboard</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_profile' ) ); ?>" class="sd-nav-link" data-nav="profile"><i class="far fa-user"></i> My Profile</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_lessons' ) ); ?>" class="sd-nav-link" data-nav="lessons"><i class="far fa-book-open"></i> My Lessons</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_bookings' ) ); ?>" class="sd-nav-link active" data-nav="bookings"><i class="far fa-calendar-check"></i> My Bookings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_dashboard' ) ); ?>" class="sd-nav-link" data-nav="messages"><i class="far fa-comments"></i> Messages</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_favourites' ) ); ?>" class="sd-nav-link" data-nav="favourites"><i class="far fa-heart"></i> Favourite Teachers</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_payments' ) ); ?>" class="sd-nav-link" data-nav="payments"><i class="far fa-credit-card"></i> Payments</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_settings' ) ); ?>" class="sd-nav-link" data-nav="settings"><i class="far fa-gear"></i> Settings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_login' ) ); ?>" class="sd-nav-link sd-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
                            </ul>
                        </nav>
                    </aside>
                    <div class="sd-sidebar-backdrop" id="sd-sidebar-backdrop" hidden></div>

                    <!-- main content -->
                    <div class="sd-main">

                        <!-- page header -->
                        <section class="sd-card sd-welcome-card">
                            <div class="sd-card-head">
                                <div>
                                    <span class="login-portal-badge">Booking History</span>
                                    <h3>My Bookings</h3>
                                    <p>Track your lesson requests, confirmed sessions, and previous bookings.</p>
                                </div>
                                <a href="teachers.html" class="theme-btn"><i class="far fa-plus"></i> New Booking</a>
                            </div>
                        </section>

                        <!-- summary stats -->
                        <section class="sd-stats-grid">
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-calendar-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="15">0</span>
                                    <span class="sd-stat-title">Total Bookings</span>
                                </div>
                            </div>
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="5">0</span>
                                    <span class="sd-stat-title">Upcoming</span>
                                </div>
                            </div>
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="8">0</span>
                                    <span class="sd-stat-title">Completed</span>
                                </div>
                            </div>
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-xmark"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="2">0</span>
                                    <span class="sd-stat-title">Cancelled</span>
                                </div>
                            </div>
                        </section>

                        <!-- bookings table -->
                        <section class="sd-card">
                            <div class="sl-tabs sb-tabs" role="tablist" aria-label="Booking status filters">
                                <button type="button" class="sl-tab is-active" data-filter="all" role="tab" aria-selected="true">All</button>
                                <button type="button" class="sl-tab" data-filter="pending" role="tab" aria-selected="false">Pending</button>
                                <button type="button" class="sl-tab" data-filter="confirmed" role="tab" aria-selected="false">Confirmed</button>
                                <button type="button" class="sl-tab" data-filter="completed" role="tab" aria-selected="false">Completed</button>
                                <button type="button" class="sl-tab" data-filter="cancelled" role="tab" aria-selected="false">Cancelled</button>
                            </div>

                            <div class="sl-empty" id="sb-empty" hidden>
                                <i class="far fa-calendar-xmark"></i>
                                <h3>No bookings available.</h3>
                                <p>Book your first gospel music lesson today.</p>
                                <a href="teachers.html" class="theme-btn"><i class="far fa-users"></i> Browse Teachers</a>
                            </div>

                            <div class="table-responsive td-table-wrap" id="sb-table-wrap">
                                <table class="table td-table sb-table">
                                    <thead>
                                        <tr>
                                            <th>Teacher</th>
                                            <th>Class</th>
                                            <th>Date</th>
                                            <th>Time</th>
                                            <th>Duration</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="sb-table-body">

                                        <tr class="sb-row" data-status="confirmed"
                                            data-teacher="John Smith"
                                            data-image="assets/img/team/01.jpg"
                                            data-class="Beginner Gospel Piano"
                                            data-date="March 20, 2026"
                                            data-time="10:00 AM"
                                            data-duration="60 Minutes"
                                            data-price="$40"
                                            data-notes="Confirmed booking for worship piano fundamentals.">
                                            <td data-label="Teacher">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="John Smith">
                                                    <strong>John Smith</strong>
                                                </div>
                                            </td>
                                            <td data-label="Class">Beginner Gospel Piano</td>
                                            <td data-label="Date">March 20, 2026</td>
                                            <td data-label="Time">10:00 AM</td>
                                            <td data-label="Duration">60 Minutes</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed">Confirmed</span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
                                                    <a href="<?php echo esc_url( gmm_get_page_link( 'student_lessons' ) ); ?>" class="theme-btn theme-btn-outline sd-action-btn">View Lesson</a>
                                                    <a href="<?php echo esc_url( gmm_get_page_link( 'student_dashboard' ) ); ?>" class="theme-btn theme-btn-outline sd-action-btn" data-gmm-message="student" data-teacher-name="John Smith" data-teacher-id="john-smith" data-student-name="Sarah Johnson" data-student-id="sarah-johnson">Message Teacher</a>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="sb-row" data-status="pending"
                                            data-teacher="Emily Davis"
                                            data-image="assets/img/team/02.jpg"
                                            data-class="Vocal Training"
                                            data-date="March 25, 2026"
                                            data-time="02:00 PM"
                                            data-duration="45 Minutes"
                                            data-price="$45"
                                            data-notes="Awaiting teacher confirmation for vocal coaching.">
                                            <td data-label="Teacher">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="Emily Davis">
                                                    <strong>Emily Davis</strong>
                                                </div>
                                            </td>
                                            <td data-label="Class">Vocal Training</td>
                                            <td data-label="Date">March 25, 2026</td>
                                            <td data-label="Time">02:00 PM</td>
                                            <td data-label="Duration">45 Minutes</td>
                                            <td data-label="Status"><span class="sb-badge is-pending">Pending</span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sb-open-details">View Details</button>
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sb-cancel-btn">Cancel Request</button>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="sb-row" data-status="completed"
                                            data-teacher="Michael Brown"
                                            data-image="assets/img/team/03.jpg"
                                            data-class="Guitar Basics"
                                            data-date="March 10, 2026"
                                            data-time="11:00 AM"
                                            data-duration="60 Minutes"
                                            data-price="$40"
                                            data-notes="Completed gospel guitar session. Practice chord transitions.">
                                            <td data-label="Teacher">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/03.jpg' ) ); ?>" alt="Michael Brown">
                                                    <strong>Michael Brown</strong>
                                                </div>
                                            </td>
                                            <td data-label="Class">Guitar Basics</td>
                                            <td data-label="Date">March 10, 2026</td>
                                            <td data-label="Time">11:00 AM</td>
                                            <td data-label="Duration">60 Minutes</td>
                                            <td data-label="Status"><span class="sb-badge is-completed">Completed</span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sb-review-btn">Leave Review</button>
                                                    <a href="booking.html" class="theme-btn theme-btn-outline sd-action-btn">Book Again</a>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="sb-row" data-status="confirmed"
                                            data-teacher="David Wilson"
                                            data-image="assets/img/team/04.jpg"
                                            data-class="Worship Leadership"
                                            data-date="March 28, 2026"
                                            data-time="04:00 PM"
                                            data-duration="60 Minutes"
                                            data-price="$55"
                                            data-notes="Confirmed worship leadership coaching session.">
                                            <td data-label="Teacher">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/04.jpg' ) ); ?>" alt="David Wilson">
                                                    <strong>David Wilson</strong>
                                                </div>
                                            </td>
                                            <td data-label="Class">Worship Leadership</td>
                                            <td data-label="Date">March 28, 2026</td>
                                            <td data-label="Time">04:00 PM</td>
                                            <td data-label="Duration">60 Minutes</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed">Confirmed</span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
                                                    <a href="<?php echo esc_url( gmm_get_page_link( 'student_lessons' ) ); ?>" class="theme-btn theme-btn-outline sd-action-btn">View Lesson</a>
                                                    <a href="<?php echo esc_url( gmm_get_page_link( 'student_dashboard' ) ); ?>" class="theme-btn theme-btn-outline sd-action-btn" data-gmm-message="student" data-teacher-name="John Smith" data-teacher-id="john-smith" data-student-name="Sarah Johnson" data-student-id="sarah-johnson">Message Teacher</a>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="sb-row" data-status="pending"
                                            data-teacher="Olivia Harris"
                                            data-image="assets/img/team/07.jpg"
                                            data-class="Music Theory"
                                            data-date="April 2, 2026"
                                            data-time="09:00 AM"
                                            data-duration="90 Minutes"
                                            data-price="$38"
                                            data-notes="Pending theory lesson request.">
                                            <td data-label="Teacher">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/07.jpg' ) ); ?>" alt="Olivia Harris">
                                                    <strong>Olivia Harris</strong>
                                                </div>
                                            </td>
                                            <td data-label="Class">Music Theory</td>
                                            <td data-label="Date">April 2, 2026</td>
                                            <td data-label="Time">09:00 AM</td>
                                            <td data-label="Duration">90 Minutes</td>
                                            <td data-label="Status"><span class="sb-badge is-pending">Pending</span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sb-open-details">View Details</button>
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sb-cancel-btn">Cancel Request</button>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="sb-row" data-status="completed"
                                            data-teacher="John Smith"
                                            data-image="assets/img/team/01.jpg"
                                            data-class="Beginner Gospel Piano"
                                            data-date="March 5, 2026"
                                            data-time="10:00 AM"
                                            data-duration="60 Minutes"
                                            data-price="$40"
                                            data-notes="Completed — great progress on left-hand patterns.">
                                            <td data-label="Teacher">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="John Smith">
                                                    <strong>John Smith</strong>
                                                </div>
                                            </td>
                                            <td data-label="Class">Beginner Gospel Piano</td>
                                            <td data-label="Date">March 5, 2026</td>
                                            <td data-label="Time">10:00 AM</td>
                                            <td data-label="Duration">60 Minutes</td>
                                            <td data-label="Status"><span class="sb-badge is-completed">Completed</span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sb-review-btn">Leave Review</button>
                                                    <a href="booking.html" class="theme-btn theme-btn-outline sd-action-btn">Book Again</a>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="sb-row" data-status="cancelled"
                                            data-teacher="Sophia Martinez"
                                            data-image="assets/img/team/05.jpg"
                                            data-class="Drums Foundations"
                                            data-date="March 8, 2026"
                                            data-time="03:00 PM"
                                            data-duration="45 Minutes"
                                            data-price="$42"
                                            data-notes="Cancelled by student — schedule conflict.">
                                            <td data-label="Teacher">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/05.jpg' ) ); ?>" alt="Sophia Martinez">
                                                    <strong>Sophia Martinez</strong>
                                                </div>
                                            </td>
                                            <td data-label="Class">Drums Foundations</td>
                                            <td data-label="Date">March 8, 2026</td>
                                            <td data-label="Time">03:00 PM</td>
                                            <td data-label="Duration">45 Minutes</td>
                                            <td data-label="Status"><span class="sb-badge is-cancelled">Cancelled</span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sb-open-details">View Details</button>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="sb-row" data-status="cancelled"
                                            data-teacher="James Lee"
                                            data-image="assets/img/team/06.jpg"
                                            data-class="Hammond Organ Intro"
                                            data-date="February 28, 2026"
                                            data-time="01:00 PM"
                                            data-duration="60 Minutes"
                                            data-price="$50"
                                            data-notes="Cancelled — teacher unavailable, refund issued (demo).">
                                            <td data-label="Teacher">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/06.jpg' ) ); ?>" alt="James Lee">
                                                    <strong>James Lee</strong>
                                                </div>
                                            </td>
                                            <td data-label="Class">Hammond Organ Intro</td>
                                            <td data-label="Date">February 28, 2026</td>
                                            <td data-label="Time">01:00 PM</td>
                                            <td data-label="Duration">60 Minutes</td>
                                            <td data-label="Status"><span class="sb-badge is-cancelled">Cancelled</span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sb-open-details">View Details</button>
                                                </div>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- student bookings end -->

    

<!-- booking details modal -->
    <div class="modal fade gospel-demo-modal" id="sb-details-modal" tabindex="-1" aria-labelledby="sb-details-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sb-details-title">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="sb-modal-teacher">
                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="Teacher" id="sb-modal-image">
                        <div>
                            <h4 id="sb-modal-teacher">—</h4>
                            <span class="sb-badge" id="sb-modal-status-badge">—</span>
                        </div>
                    </div>
                    <ul class="booking-modal-list">
                        <li><span>Class</span><strong id="sb-modal-class">—</strong></li>
                        <li><span>Date</span><strong id="sb-modal-date">—</strong></li>
                        <li><span>Time</span><strong id="sb-modal-time">—</strong></li>
                        <li><span>Duration</span><strong id="sb-modal-duration">—</strong></li>
                        <li><span>Price</span><strong id="sb-modal-price">—</strong></li>
                        <li><span>Status</span><strong id="sb-modal-status">—</strong></li>
                    </ul>
                    <div class="sl-modal-notes">
                        <h6>Notes</h6>
                        <p id="sb-modal-notes">—</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Close</button>
                    <a href="booking.html" class="theme-btn" id="sb-modal-book-again">
                        <i class="far fa-calendar-plus"></i> Book Again
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="gospel-alert gospel-alert-success sl-toast" id="sb-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="sb-toast-text">Action completed (demo).</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

