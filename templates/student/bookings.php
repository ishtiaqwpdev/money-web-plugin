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

$booking_rows  = ( isset( $booking_rows ) && is_array( $booking_rows ) ) ? $booking_rows : array();
$booking_stats = ( isset( $booking_stats ) && is_array( $booking_stats ) ) ? $booking_stats : array(
	'total'     => 0,
	'upcoming'  => 0,
	'completed' => 0,
	'cancelled' => 0,
);
$user_avatar   = ! empty( $user_avatar ) ? (string) $user_avatar : gmm_design_asset_url( 'assets/img/team/02.jpg' );
$teachers_url  = ! empty( $teachers_url ) ? (string) $teachers_url : ( function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teachers' ) : '#' );
$booking_form_url = ! empty( $booking_form_url ) ? (string) $booking_form_url : ( function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'booking_form' ) : '#' );
$lessons_url   = ! empty( $lessons_url ) ? (string) $lessons_url : ( function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'student_lessons' ) : '#' );
$has_rows      = ! empty( $booking_rows );
?>
<div class="gmm-wrapper gmm-dashboard" id="gmm-student-bookings">

        <!-- student bookings -->
        <div class="student-dashboard-area py-120">
            <div class="container">

                <!-- dashboard profile header -->
                <div class="sd-profile-header">
                    <div class="sd-profile-main">
                        <div class="sd-profile-avatar">
                            <img src="<?php echo esc_url( $user_avatar ); ?>" alt="<?php echo esc_attr( $user_name ); ?>">
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
                        <a href="<?php echo esc_url( $teachers_url ); ?>" class="theme-btn"><i class="far fa-calendar-plus"></i> Book a Lesson</a>
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
                                <a href="<?php echo esc_url( $teachers_url ); ?>" class="theme-btn"><i class="far fa-plus"></i> New Booking</a>
                            </div>
                        </section>

                        <!-- summary stats -->
                        <section class="sd-stats-grid">
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-calendar-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="<?php echo esc_attr( (string) (int) $booking_stats['total'] ); ?>" id="sb-stat-total"><?php echo esc_html( (string) (int) $booking_stats['total'] ); ?></span>
                                    <span class="sd-stat-title">Total Bookings</span>
                                </div>
                            </div>
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="<?php echo esc_attr( (string) (int) $booking_stats['upcoming'] ); ?>" id="sb-stat-upcoming"><?php echo esc_html( (string) (int) $booking_stats['upcoming'] ); ?></span>
                                    <span class="sd-stat-title">Upcoming</span>
                                </div>
                            </div>
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="<?php echo esc_attr( (string) (int) $booking_stats['completed'] ); ?>" id="sb-stat-completed"><?php echo esc_html( (string) (int) $booking_stats['completed'] ); ?></span>
                                    <span class="sd-stat-title">Completed</span>
                                </div>
                            </div>
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-xmark"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="<?php echo esc_attr( (string) (int) $booking_stats['cancelled'] ); ?>" id="sb-stat-cancelled"><?php echo esc_html( (string) (int) $booking_stats['cancelled'] ); ?></span>
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

                            <div class="sl-empty" id="sb-empty" <?php echo $has_rows ? 'hidden' : ''; ?>>
                                <i class="far fa-calendar-xmark"></i>
                                <h3>No bookings available.</h3>
                                <p>Book your first gospel music lesson today.</p>
                                <a href="<?php echo esc_url( $teachers_url ); ?>" class="theme-btn"><i class="far fa-users"></i> Browse Teachers</a>
                            </div>

                            <div class="table-responsive td-table-wrap" id="sb-table-wrap" <?php echo $has_rows ? '' : 'hidden'; ?>>
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
										<?php foreach ( $booking_rows as $row ) :
											$status   = isset( $row['status'] ) ? (string) $row['status'] : 'pending';
											$badge    = isset( $row['badge_class'] ) ? (string) $row['badge_class'] : ( 'is-' . $status );
											$teacher  = isset( $row['teacher_name'] ) ? (string) $row['teacher_name'] : '';
											$image    = isset( $row['teacher_image'] ) ? (string) $row['teacher_image'] : gmm_design_asset_url( 'assets/img/team/01.jpg' );
											$class_n  = isset( $row['class_name'] ) ? (string) $row['class_name'] : '';
											$date_l   = isset( $row['date_label'] ) ? (string) $row['date_label'] : '';
											$time_l   = isset( $row['time_label'] ) ? (string) $row['time_label'] : '';
											$dur_l    = isset( $row['duration_label'] ) ? (string) $row['duration_label'] : '';
											$price_l  = isset( $row['amount_label'] ) ? (string) $row['amount_label'] : '';
											$notes    = isset( $row['notes'] ) ? (string) $row['notes'] : '';
											$can_cancel = ! empty( $row['can_cancel'] );
											$book_again = ! empty( $row['book_again_url'] ) ? (string) $row['book_again_url'] : $booking_form_url;
											$bid = isset( $row['id'] ) ? (int) $row['id'] : 0;
											?>
                                        <tr class="sb-row" data-status="<?php echo esc_attr( $status ); ?>"
											data-booking-id="<?php echo esc_attr( (string) $bid ); ?>"
                                            data-teacher="<?php echo esc_attr( $teacher ); ?>"
                                            data-image="<?php echo esc_attr( $image ); ?>"
                                            data-class="<?php echo esc_attr( $class_n ); ?>"
                                            data-date="<?php echo esc_attr( $date_l ); ?>"
                                            data-time="<?php echo esc_attr( $time_l ); ?>"
                                            data-duration="<?php echo esc_attr( $dur_l ); ?>"
                                            data-price="<?php echo esc_attr( $price_l ); ?>"
                                            data-notes="<?php echo esc_attr( $notes ); ?>">
                                            <td data-label="Teacher">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $teacher ); ?>">
                                                    <strong><?php echo esc_html( $teacher ); ?></strong>
                                                </div>
                                            </td>
                                            <td data-label="Class"><?php echo esc_html( $class_n ); ?></td>
                                            <td data-label="Date"><?php echo esc_html( $date_l ); ?></td>
                                            <td data-label="Time"><?php echo esc_html( $time_l ); ?></td>
                                            <td data-label="Duration"><?php echo esc_html( $dur_l ); ?></td>
                                            <td data-label="Status"><span class="sb-badge <?php echo esc_attr( $badge ); ?>"><?php echo esc_html( isset( $row['status_label'] ) ? (string) $row['status_label'] : ucfirst( $status ) ); ?></span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
													<?php if ( 'confirmed' === $status ) : ?>
                                                    <a href="<?php echo esc_url( $lessons_url ); ?>" class="theme-btn theme-btn-outline sd-action-btn">View Lesson</a>
													<?php else : ?>
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sb-open-details">View Details</button>
													<?php endif; ?>
													<?php if ( $can_cancel ) : ?>
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sb-cancel-btn" data-booking-id="<?php echo esc_attr( (string) $bid ); ?>">Cancel Request</button>
													<?php elseif ( 'completed' === $status ) : ?>
                                                    <a href="<?php echo esc_url( $book_again ); ?>" class="theme-btn theme-btn-outline sd-action-btn">Book Again</a>
													<?php endif; ?>
                                                </div>
                                            </td>
                                        </tr>
										<?php endforeach; ?>
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
                    <a href="<?php echo esc_url( $booking_form_url ); ?>" class="theme-btn" id="sb-modal-book-again">
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

