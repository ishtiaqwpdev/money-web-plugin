<?php
/**
 * Template: teacher-bookings
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $gmm_teacher_pending ) || ! empty( $gmm_teacher_denied ) ) {
	$msg = ! empty( $gmm_teacher_pending )
		? __( 'Your account is waiting for approval.', 'gospel-music-mastery' )
		: __( 'You do not have permission to manage bookings.', 'gospel-music-mastery' );
	echo '<div class="gmm-wrapper gmm-dashboard"><div class="teacher-dashboard-area py-120"><div class="container"><div class="td-card"><div class="td-card-head"><h3>' . esc_html( $msg ) . '</h3></div></div></div></div></div>';
	return;
}

$booking_rows  = ( isset( $booking_rows ) && is_array( $booking_rows ) ) ? $booking_rows : array();
$booking_stats = ( isset( $booking_stats ) && is_array( $booking_stats ) ) ? $booking_stats : array(
	'pending'   => 0,
	'upcoming'  => 0,
	'completed' => 0,
	'students'  => 0,
);
$profile_summary = ( isset( $profile_summary ) && is_array( $profile_summary ) ) ? $profile_summary : array();
$profile_stats   = ( isset( $profile_stats ) && is_array( $profile_stats ) ) ? $profile_stats : array(
	'rating'   => 0,
	'students' => 0,
	'classes'  => 0,
);
$logout_url = isset( $logout_url ) ? $logout_url : ( function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ) );
$links      = ( isset( $links ) && is_array( $links ) ) ? $links : array();

if ( ! isset( $user_name ) || '' === $user_name ) {
	$user_name = ! empty( $profile_summary['name'] ) ? $profile_summary['name'] : 'Guest';
}
if ( ! isset( $user_first_name ) ) {
	$user_first_name = ! empty( $profile_summary['first_name'] ) ? $profile_summary['first_name'] : $user_name;
}

$avatar_url = ! empty( $profile_summary['image_url'] )
	? $profile_summary['image_url']
	: gmm_design_asset_url( 'assets/img/team/01.jpg' );
$role_label = ! empty( $profile_summary['specialization'] )
	? $profile_summary['specialization']
	: 'Gospel Music Instructor';
$rating_val  = isset( $profile_stats['rating'] ) ? (float) $profile_stats['rating'] : 0;
$rating_disp = $rating_val > 0 ? number_format_i18n( $rating_val, 1 ) : '—';
$link_classes = ! empty( $links['classes'] ) ? $links['classes'] : gmm_get_page_link( 'teacher_classes' );
$has_rows     = ! empty( $booking_rows );
?>
<div class="gmm-wrapper gmm-dashboard">

<!-- teacher dashboard -->
        <div class="teacher-dashboard-area py-120">
            <div class="container">

                <!-- dashboard profile header -->
                <div class="td-profile-header">
                    <div class="td-profile-main">
                        <div class="td-profile-avatar">
                            <img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $user_name ); ?>">
                        </div>
                        <div class="td-profile-meta">
                            <h2><?php echo esc_html( $user_name ); ?></h2>
                            <span class="td-role"><?php echo esc_html( $role_label ); ?></span>
                            <div class="td-profile-stats">
                                <span class="td-stat-item"><i class="fas fa-star"></i> <?php echo esc_html( $rating_disp ); ?></span>
                                <span class="td-stat-item"><i class="far fa-users"></i> <?php echo esc_html( (int) $booking_stats['students'] ); ?> Students</span>
                                <span class="td-stat-item"><i class="far fa-books"></i> <?php echo esc_html( (int) $profile_stats['classes'] ); ?> Classes</span>
                            </div>
                        </div>
                    </div>
                    <div class="td-profile-actions">
                        <a href="<?php echo esc_url( $link_classes ); ?>" class="theme-btn"><i class="far fa-plus"></i> Create New Class</a>
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
                                <li><a href="<?php echo esc_url( $logout_url ); ?>" class="td-nav-link td-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
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
                                    <span class="td-stat-value" id="summary-pending"><?php echo esc_html( (int) $booking_stats['pending'] ); ?></span>
                                    <span class="td-stat-title">Pending Requests</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-calendar-check"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value" id="summary-upcoming"><?php echo esc_html( (int) $booking_stats['upcoming'] ); ?></span>
                                    <span class="td-stat-title">Upcoming Lessons</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value" id="summary-completed"><?php echo esc_html( (int) $booking_stats['completed'] ); ?></span>
                                    <span class="td-stat-title">Completed Lessons</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-users"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value" id="summary-students"><?php echo esc_html( (int) $booking_stats['students'] ); ?></span>
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

                            <div class="table-responsive"<?php echo $has_rows ? '' : ' hidden'; ?>>
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
<?php foreach ( $booking_rows as $row ) : ?>
                                        <tr data-status="<?php echo esc_attr( $row['status'] ); ?>" data-booking-id="<?php echo esc_attr( (string) $row['id'] ); ?>" data-class-id="<?php echo esc_attr( (string) $row['class_id'] ); ?>" data-date="<?php echo esc_attr( $row['booking_date'] ); ?>">
                                            <td data-label="Student"><strong><?php echo esc_html( $row['student_name'] ); ?></strong></td>
                                            <td data-label="Lesson"><?php echo esc_html( $row['class_name'] ); ?></td>
                                            <td data-label="Date"><?php echo esc_html( $row['date_label'] ); ?></td>
                                            <td data-label="Time"><?php echo esc_html( $row['time_label'] ); ?></td>
                                            <td data-label="Duration"><?php echo esc_html( $row['duration_label'] ); ?></td>
                                            <td data-label="Status"><span class="td-badge <?php echo esc_attr( $row['badge_class'] ); ?> booking-status"><?php echo esc_html( $row['status_label'] ); ?></span></td>
                                            <td data-label="Action">
                                                <div class="booking-actions">
<?php if ( ! empty( $row['can_confirm'] ) ) : ?>
                                                    <button type="button" class="theme-btn td-action-btn booking-confirm-btn">Confirm</button>
                                                    <button type="button" class="theme-btn theme-btn-outline td-action-btn booking-decline-btn">Decline</button>
<?php elseif ( ! empty( $row['can_complete'] ) ) : ?>
                                                    <button type="button" class="theme-btn theme-btn-outline td-action-btn booking-details-btn"
                                                        data-student="<?php echo esc_attr( $row['student_name'] ); ?>"
                                                        data-image="<?php echo esc_url( $row['student_image'] ); ?>"
                                                        data-lesson="<?php echo esc_attr( $row['class_name'] ); ?>"
                                                        data-date="<?php echo esc_attr( $row['date_label'] ); ?>"
                                                        data-time="<?php echo esc_attr( $row['time_label'] ); ?>"
                                                        data-duration="<?php echo esc_attr( $row['duration_label'] ); ?>"
                                                        data-payment="<?php echo esc_attr( $row['payment_label'] ); ?>"
                                                        data-notes="<?php echo esc_attr( $row['notes'] ); ?>">View Details</button>
                                                    <button type="button" class="theme-btn td-action-btn booking-complete-btn">Complete</button>
                                                    <button type="button" class="theme-btn theme-btn-outline td-action-btn booking-decline-btn">Cancel</button>
<?php elseif ( ! empty( $row['can_review'] ) ) : ?>
                                                    <button type="button" class="theme-btn theme-btn-outline td-action-btn booking-review-btn">View Review</button>
<?php else : ?>
                                                    <button type="button" class="theme-btn theme-btn-outline td-action-btn booking-details-btn"
                                                        data-student="<?php echo esc_attr( $row['student_name'] ); ?>"
                                                        data-image="<?php echo esc_url( $row['student_image'] ); ?>"
                                                        data-lesson="<?php echo esc_attr( $row['class_name'] ); ?>"
                                                        data-date="<?php echo esc_attr( $row['date_label'] ); ?>"
                                                        data-time="<?php echo esc_attr( $row['time_label'] ); ?>"
                                                        data-duration="<?php echo esc_attr( $row['duration_label'] ); ?>"
                                                        data-payment="<?php echo esc_attr( $row['payment_label'] ); ?>"
                                                        data-notes="<?php echo esc_attr( $row['notes'] ); ?>">View Details</button>
<?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
<?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <p class="td-empty-state" id="bookings-empty"<?php echo $has_rows ? ' hidden' : ''; ?>>
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
                    <div class="td-rating mb-2" id="modal-review-stars">★★★★★</div>
                    <p id="modal-review-text">“Great gospel piano lesson. Clear instruction and very encouraging.”</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<!-- js -->
</div><!-- .gmm-wrapper -->
