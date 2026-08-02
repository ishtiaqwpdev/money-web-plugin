<?php
/**
 * Template: admin-bookings
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $gmm_admin_denied ) ) {
	echo '<div class="gmm-wrapper"><p>' . esc_html__( 'You do not have permission to manage bookings.', 'gospel-music-mastery' ) . '</p></div>';
	return;
}

if ( ! isset( $user_name ) ) {
	$user_name = 'Guest';
}
if ( ! isset( $user_first_name ) ) {
	$user_first_name = $user_name;
}
if ( ! isset( $bookings ) || ! is_array( $bookings ) ) {
	$bookings = array();
}
if ( ! isset( $booking_stats ) || ! is_array( $booking_stats ) ) {
	$booking_stats = array(
		'total'     => 0,
		'upcoming'  => 0,
		'completed' => 0,
		'cancelled' => 0,
		'pending'   => 0,
	);
}
if ( ! isset( $filters ) || ! is_array( $filters ) ) {
	$filters = array(
		'search'  => '',
		'status'  => 'all',
		'payment' => 'all',
		'period'  => 'all',
		'page'    => 1,
	);
}
if ( ! isset( $pagination ) || ! is_array( $pagination ) ) {
	$pagination = array(
		'page'        => 1,
		'total'       => 0,
		'total_pages' => 0,
		'has_prev'    => false,
		'has_next'    => false,
		'prev_page'   => null,
		'next_page'   => null,
	);
}
if ( ! isset( $booking_activity ) || ! is_array( $booking_activity ) ) {
	$booking_activity = array();
}
if ( ! isset( $logout_url ) ) {
	$logout_url = function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) );
}
if ( ! isset( $last_login_label ) ) {
	$last_login_label = __( 'Last login: Today', 'gospel-music-mastery' );
}

$filter_search  = isset( $filters['search'] ) ? (string) $filters['search'] : '';
$filter_status  = isset( $filters['status'] ) ? (string) $filters['status'] : 'all';
$filter_payment = isset( $filters['payment'] ) ? (string) $filters['payment'] : 'all';
$filter_period  = isset( $filters['period'] ) ? (string) $filters['period'] : 'all';
$result_total   = isset( $pagination['total'] ) ? absint( $pagination['total'] ) : count( $bookings );
$total_pages    = isset( $pagination['total_pages'] ) ? absint( $pagination['total_pages'] ) : 1;
$current_page   = isset( $pagination['page'] ) ? absint( $pagination['page'] ) : 1;
$stat_total     = isset( $booking_stats['total'] ) ? absint( $booking_stats['total'] ) : 0;
$stat_upcoming  = isset( $booking_stats['upcoming'] ) ? absint( $booking_stats['upcoming'] ) : 0;
$stat_completed = isset( $booking_stats['completed'] ) ? absint( $booking_stats['completed'] ) : 0;
$stat_cancelled = isset( $booking_stats['cancelled'] ) ? absint( $booking_stats['cancelled'] ) : 0;
$stat_pending   = isset( $booking_stats['pending'] ) ? absint( $booking_stats['pending'] ) : 0;
?>
<div class="gmm-wrapper gmm-dashboard gmm-admin">

        <!-- admin bookings -->
        <div class="admin-dashboard-area py-120">
            <div class="container">

                <!-- admin top header -->
                <header class="sd-profile-header ad-topbar">
                    <div class="sd-profile-main ad-topbar-main">
                        <div class="sd-profile-avatar ad-topbar-avatar">
                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="<?php echo esc_attr( $user_name ); ?>">
                        </div>
                        <div class="sd-profile-meta">
                            <h2><?php echo esc_html( $user_name ); ?></h2>
                            <span class="sd-role">Platform Admin</span>
                            <div class="sd-profile-stats">
                                <span class="sd-stat-item"><i class="far fa-shield-check"></i> Full Access</span>
                                <span class="sd-stat-item"><i class="far fa-clock"></i> <?php echo esc_html( $last_login_label ); ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="ad-topbar-actions">
                        <div class="dropdown ad-icon-dropdown">
                            <button class="ad-icon-btn" type="button" id="ad-notifications" data-bs-toggle="dropdown"
                                aria-expanded="false" aria-label="Notifications">
                                <i class="far fa-bell"></i>
                                <span class="ad-icon-badge">4</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end ad-dropdown" aria-labelledby="ad-notifications">
                                <h6 class="ad-dropdown-title">Notifications</h6>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_teachers' ) ); ?>"><i class="far fa-user-plus"></i> <span><strong>John Smith</strong> applied as a teacher</span></a>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_classes' ) ); ?>"><i class="far fa-chalkboard"></i> <span>New class submitted for review</span></a>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_payments' ) ); ?>"><i class="far fa-credit-card"></i> <span>Payment of <strong>$40</strong> received</span></a>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_teachers' ) ); ?>"><i class="far fa-triangle-exclamation"></i> <span>15 approvals are pending</span></a>
                            </div>
                        </div>

                        <div class="dropdown ad-profile-dropdown">
                            <button class="ad-profile-btn" type="button" id="ad-profile-menu" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="<?php echo esc_attr( $user_name ); ?>">
                                <span class="ad-profile-btn-text">
                                    <strong>Administrator</strong>
                                    <small>Platform Admin</small>
                                </span>
                                <i class="far fa-angle-down"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end ad-dropdown" aria-labelledby="ad-profile-menu">
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_settings' ) ); ?>"><i class="far fa-user"></i> <span>My Profile</span></a>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_settings' ) ); ?>"><i class="far fa-gear"></i> <span>Settings</span></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item ad-dropdown-item is-logout" href="<?php echo esc_url( $logout_url ); ?>"><i class="far fa-right-from-bracket"></i> <span>Logout</span></a>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="sd-shell ad-shell">
                    <button type="button" class="sd-sidebar-toggle theme-btn theme-btn-outline" id="sd-sidebar-toggle"
                        aria-expanded="false" aria-controls="sd-sidebar">
                        <i class="far fa-bars"></i> Menu
                    </button>

                    <aside class="sd-sidebar ad-sidebar" id="sd-sidebar" aria-label="Admin navigation">
                        <nav class="sd-nav ad-nav">
                            <ul>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_dashboard' ) ); ?>" class="sd-nav-link" data-nav="dashboard"><i class="far fa-grid-2"></i> Dashboard</a></li>
                                <li class="ad-nav-group is-open" id="ad-users-group">
                                    <button type="button" class="sd-nav-link ad-nav-parent" id="ad-users-toggle"
                                        aria-expanded="true" aria-controls="ad-users-submenu">
                                        <i class="far fa-users"></i> Users
                                        <i class="far fa-angle-down ad-nav-caret"></i>
                                    </button>
                                    <ul class="ad-submenu" id="ad-users-submenu">
                                        <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_teachers' ) ); ?>" class="sd-nav-link ad-sub-link" data-nav="teachers"><i class="far fa-chalkboard-user"></i> Teachers</a></li>
                                        <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_students' ) ); ?>" class="sd-nav-link ad-sub-link" data-nav="students"><i class="far fa-graduation-cap"></i> Students</a></li>
                                    </ul>
                                </li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_classes' ) ); ?>" class="sd-nav-link" data-nav="classes"><i class="far fa-chalkboard"></i> Classes</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_bookings' ) ); ?>" class="sd-nav-link active" data-nav="bookings"><i class="far fa-calendar-check"></i> Bookings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_payments' ) ); ?>" class="sd-nav-link" data-nav="payments"><i class="far fa-credit-card"></i> Payments</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_programs' ) ); ?>" class="sd-nav-link" data-nav="programs"><i class="far fa-music"></i> Music Programs</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_settings' ) ); ?>" class="sd-nav-link" data-nav="settings"><i class="far fa-gear"></i> Settings</a></li>
                                <li><a href="<?php echo esc_url( $logout_url ); ?>" class="sd-nav-link sd-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
                            </ul>
                        </nav>
                    </aside>
                    <div class="sd-sidebar-backdrop" id="sd-sidebar-backdrop" hidden></div>

                    <div class="sd-main ad-main">

                        <section class="sd-card sd-welcome-card">
                            <div class="sd-card-head">
                                <div>
                                    <span class="login-portal-badge">Booking Management</span>
                                    <h3>Manage Bookings</h3>
                                    <p>Monitor student lesson bookings, teacher schedules, payment status, and lesson progress.</p>
                                </div>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'admin_dashboard' ) ); ?>" class="theme-btn theme-btn-outline"><i class="far fa-grid-2"></i> Dashboard</a>
                            </div>
                        </section>

                        <!-- stats -->
                        <section class="sd-stats-grid ab-stats-grid">
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-calendar-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) $stat_total ); ?>">0</span>
                                    <span class="sd-stat-title">Total Bookings</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-calendar-days"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) $stat_upcoming ); ?>">0</span>
                                    <span class="sd-stat-title">Upcoming Lessons</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) $stat_completed ); ?>">0</span>
                                    <span class="sd-stat-title">Completed Lessons</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-xmark"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) $stat_cancelled ); ?>">0</span>
                                    <span class="sd-stat-title">Cancelled</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card is-pending">
                                <div class="sd-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) $stat_pending ); ?>">0</span>
                                    <span class="sd-stat-title">Pending</span>
                                </div>
                            </div>
                        </section>

                        <!-- analytics charts -->
                        <div class="gmm-chart-grid">
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Booking Analytics</h3>
                                        <p>Lesson bookings created each month.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-ab-analytics" aria-label="Booking analytics line chart"></canvas>
                                </div>
                            </section>
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Booking Status</h3>
                                        <p>Distribution of booking statuses.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap is-doughnut">
                                    <canvas id="gmm-ab-status" aria-label="Booking status doughnut chart"></canvas>
                                </div>
                            </section>
                        </div>

                        <div class="ab-content-grid">

                            <!-- bookings table -->
                            <section class="sd-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>All Bookings</h3>
                                        <p>Search and manage student lesson bookings.</p>
                                    </div>
                                    <span class="sf-count-pill" id="ab-result-count"><i class="far fa-calendar-check"></i> <strong><?php echo esc_html( (string) $result_total ); ?></strong> Shown</span>
                                </div>

                                <form class="at-filter-bar" id="ab-filter-form" action="" method="get">
                                    <?php if ( is_admin() ) : ?>
                                        <input type="hidden" name="page" value="gmm-bookings">
                                    <?php endif; ?>
                                    <div class="at-search-field">
                                        <i class="far fa-search" aria-hidden="true"></i>
                                        <input type="search" class="form-control" id="ab-search" name="ab_search"
                                            value="<?php echo esc_attr( $filter_search ); ?>"
                                            placeholder="Search by student, teacher, or class..." autocomplete="off">
                                    </div>
                                    <div class="at-filter-selects">
                                        <div class="form-group mb-0">
                                            <label for="ab-status" class="visually-hidden">Booking Status</label>
                                            <select class="form-control form-select" id="ab-status" name="ab_status">
                                                <option value="all" <?php selected( $filter_status, 'all' ); ?>>All Booking Status</option>
                                                <option value="pending" <?php selected( $filter_status, 'pending' ); ?>>Pending</option>
                                                <option value="confirmed" <?php selected( $filter_status, 'confirmed' ); ?>>Confirmed</option>
                                                <option value="completed" <?php selected( $filter_status, 'completed' ); ?>>Completed</option>
                                                <option value="cancelled" <?php selected( $filter_status, 'cancelled' ); ?>>Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="ab-payment" class="visually-hidden">Payment Status</label>
                                            <select class="form-control form-select" id="ab-payment" name="ab_payment">
                                                <option value="all" <?php selected( $filter_payment, 'all' ); ?>>All Payment Status</option>
                                                <option value="paid" <?php selected( $filter_payment, 'paid' ); ?>>Paid</option>
                                                <option value="pending" <?php selected( $filter_payment, 'pending' ); ?>>Pending</option>
                                                <option value="failed" <?php selected( $filter_payment, 'failed' ); ?>>Failed</option>
                                                <option value="refunded" <?php selected( $filter_payment, 'refunded' ); ?>>Refunded</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="ab-date" class="visually-hidden">Date</label>
                                            <select class="form-control form-select" id="ab-date" name="ab_date">
                                                <option value="all" <?php selected( $filter_period, 'all' ); ?>>All Dates</option>
                                                <option value="today" <?php selected( $filter_period, 'today' ); ?>>Today</option>
                                                <option value="week" <?php selected( $filter_period, 'week' ); ?>>This Week</option>
                                                <option value="month" <?php selected( $filter_period, 'month' ); ?>>This Month</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="theme-btn"><i class="far fa-filter"></i> Filter</button>
                                    </div>
                                </form>

                                <div class="table-responsive td-table-wrap" id="ab-table-wrap" <?php echo empty( $bookings ) ? 'hidden' : ''; ?>>
                                    <table class="table td-table sb-table at-table">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Teacher</th>
                                                <th>Class</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Payment</th>
                                                <th>Booking Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ab-table-body">
                                        <?php if ( empty( $bookings ) ) : ?>
                                        <?php else : ?>
                                            <?php foreach ( $bookings as $booking ) : ?>
                                                <?php
                                                $bid = isset( $booking['id'] ) ? absint( $booking['id'] ) : 0;
                                                $bcode = isset( $booking['code'] ) ? (string) $booking['code'] : ( 'BK-' . $bid );
                                                $bstudent = isset( $booking['student'] ) ? (string) $booking['student'] : '';
                                                $bteacher = isset( $booking['teacher'] ) ? (string) $booking['teacher'] : '';
                                                $bclass = isset( $booking['class'] ) ? (string) $booking['class'] : '';
                                                $bdate = isset( $booking['date'] ) ? (string) $booking['date'] : '';
                                                $bdate_raw = isset( $booking['date_raw'] ) ? (string) $booking['date_raw'] : '';
                                                $btime = isset( $booking['time'] ) ? (string) $booking['time'] : '';
                                                $bduration = isset( $booking['duration_label'] ) ? (string) $booking['duration_label'] : '';
                                                $bamount = isset( $booking['amount_label'] ) ? (string) $booking['amount_label'] : '';
                                                $bpayment = isset( $booking['payment'] ) ? (string) $booking['payment'] : 'pending';
                                                $bpayment_label = isset( $booking['payment_label'] ) ? (string) $booking['payment_label'] : $bpayment;
                                                $bpayment_class = isset( $booking['payment_class'] ) ? (string) $booking['payment_class'] : 'is-pending';
                                                $bstatus = isset( $booking['status'] ) ? (string) $booking['status'] : 'pending';
                                                $bstatus_label = isset( $booking['status_label'] ) ? (string) $booking['status_label'] : $bstatus;
                                                $bstatus_class = isset( $booking['status_class'] ) ? (string) $booking['status_class'] : 'is-pending';
                                                $bperiod = isset( $booking['period'] ) ? (string) $booking['period'] : 'all';
                                                $bnotes = isset( $booking['notes'] ) ? (string) $booking['notes'] : '';
                                                $bsimg = isset( $booking['student_image'] ) ? (string) $booking['student_image'] : '';
                                                $btimg = isset( $booking['teacher_image'] ) ? (string) $booking['teacher_image'] : '';
                                                $bphone = isset( $booking['teacher_phone'] ) ? (string) $booking['teacher_phone'] : ( isset( $booking['student_phone'] ) ? (string) $booking['student_phone'] : '' );
                                                $bemail = isset( $booking['student_email'] ) ? (string) $booking['student_email'] : '';
                                                $bstudent_id = isset( $booking['student_id'] ) ? absint( $booking['student_id'] ) : 0;
                                                $bteacher_id = isset( $booking['teacher_id'] ) ? absint( $booking['teacher_id'] ) : 0;
                                                $bstudent_url = ( $bstudent_id && function_exists( 'gmm_get_page_link' ) ) ? add_query_arg( 'as_search', $bstudent, gmm_get_page_link( 'admin_students' ) ) : '';
                                                $bteacher_url = ( $bteacher_id && function_exists( 'gmm_get_page_link' ) ) ? add_query_arg( 'at_search', $bteacher, gmm_get_page_link( 'admin_teachers' ) ) : '';
                                                ?>
                                            <tr class="ab-row"
                                                data-id="<?php echo esc_attr( $bcode ); ?>"
                                                data-booking-id="<?php echo esc_attr( (string) $bid ); ?>"
                                                data-student="<?php echo esc_attr( $bstudent ); ?>"
                                                data-teacher="<?php echo esc_attr( $bteacher ); ?>"
                                                data-class="<?php echo esc_attr( $bclass ); ?>"
                                                data-date="<?php echo esc_attr( $bdate ); ?>"
                                                data-date-raw="<?php echo esc_attr( $bdate_raw ); ?>"
                                                data-time="<?php echo esc_attr( $btime ); ?>"
                                                data-duration="<?php echo esc_attr( $bduration ); ?>"
                                                data-amount="<?php echo esc_attr( $bamount ); ?>"
                                                data-payment="<?php echo esc_attr( $bpayment ); ?>"
                                                data-status="<?php echo esc_attr( $bstatus ); ?>"
                                                data-period="<?php echo esc_attr( $bperiod ); ?>"
                                                data-notes="<?php echo esc_attr( $bnotes ); ?>"
                                                data-student-img="<?php echo esc_url( $bsimg ); ?>"
                                                data-teacher-img="<?php echo esc_url( $btimg ); ?>"
                                                data-phone="<?php echo esc_attr( $bphone ); ?>"
                                                data-email="<?php echo esc_attr( $bemail ); ?>"
                                                data-student-url="<?php echo esc_url( $bstudent_url ); ?>"
                                                data-teacher-url="<?php echo esc_url( $bteacher_url ); ?>">
                                                <td data-label="Student">
                                                    <div class="sb-teacher-cell">
                                                        <img src="<?php echo esc_url( $bsimg ); ?>" alt="<?php echo esc_attr( $bstudent ); ?>">
                                                        <strong><?php echo esc_html( $bstudent ); ?></strong>
                                                    </div>
                                                </td>
                                                <td data-label="Teacher"><?php echo esc_html( $bteacher ); ?></td>
                                                <td data-label="Class"><?php echo esc_html( $bclass ); ?></td>
                                                <td data-label="Date"><?php echo esc_html( $bdate ); ?></td>
                                                <td data-label="Time"><?php echo esc_html( $btime ); ?></td>
                                                <td data-label="Payment"><span class="sb-badge <?php echo esc_attr( $bpayment_class ); ?> ab-payment"><?php echo esc_html( $bpayment_label ); ?></span></td>
                                                <td data-label="Booking Status"><span class="sb-badge <?php echo esc_attr( $bstatus_class ); ?> ab-status"><?php echo esc_html( $bstatus_label ); ?></span></td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-view-btn"><i class="far fa-eye"></i> <span>View Booking</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-student-btn"><i class="far fa-graduation-cap"></i> <span>View Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-teacher-btn"><i class="far fa-chalkboard-user"></i> <span>View Teacher</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-complete-btn"><i class="far fa-circle-check"></i> <span>Mark Completed</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-cancel-btn"><i class="far fa-circle-xmark"></i> <span>Cancel Booking</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-refund-btn"><i class="far fa-rotate-left"></i> <span>Refund Payment</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                        </tbody>
                                    </table>
                                </div>

                                <div class="sl-empty" id="ab-empty" <?php echo empty( $bookings ) ? '' : 'hidden'; ?>>
                                    <i class="far fa-calendar-xmark"></i>
                                    <h3>No bookings available.</h3>
                                    <p>Try adjusting your search or filter options.</p>
                                </div>

                                <?php
                                $show_pagination = $total_pages > 1;
                                $prev_disabled   = empty( $pagination['has_prev'] );
                                $next_disabled   = empty( $pagination['has_next'] );
                                $prev_url = ( ! $prev_disabled && ! empty( $pagination['prev_page'] ) && function_exists( 'gmm_admin_bookings_page_url' ) )
                                    ? gmm_admin_bookings_page_url( (int) $pagination['prev_page'], $filters )
                                    : '#';
                                $next_url = ( ! $next_disabled && ! empty( $pagination['next_page'] ) && function_exists( 'gmm_admin_bookings_page_url' ) )
                                    ? gmm_admin_bookings_page_url( (int) $pagination['next_page'], $filters )
                                    : '#';
                                ?>
                                <nav class="at-pagination" id="ab-pagination" aria-label="Bookings pagination" <?php echo $show_pagination ? '' : 'hidden'; ?>>
                                    <ul class="pagination justify-content-center mb-0">
                                        <li class="page-item<?php echo $prev_disabled ? ' disabled' : ''; ?>" id="ab-page-prev">
                                            <a class="page-link" href="<?php echo esc_url( $prev_url ); ?>" data-page="prev" aria-label="Previous"><i class="far fa-angle-left"></i> Previous</a>
                                        </li>
                                        <?php
                                        $start_p = max( 1, $current_page - 2 );
                                        $end_p   = min( $total_pages, $start_p + 4 );
                                        $start_p = max( 1, $end_p - 4 );
                                        for ( $p = $start_p; $p <= $end_p; $p++ ) :
                                            $p_url = function_exists( 'gmm_admin_bookings_page_url' ) ? gmm_admin_bookings_page_url( $p, $filters ) : '#';
                                            ?>
                                        <li class="page-item<?php echo ( $p === $current_page ) ? ' active' : ''; ?>"><a class="page-link" href="<?php echo esc_url( $p_url ); ?>" data-page="<?php echo esc_attr( (string) $p ); ?>"><?php echo esc_html( (string) $p ); ?></a></li>
                                        <?php endfor; ?>
                                        <li class="page-item<?php echo $next_disabled ? ' disabled' : ''; ?>" id="ab-page-next">
                                            <a class="page-link" href="<?php echo esc_url( $next_url ); ?>" data-page="next" aria-label="Next">Next <i class="far fa-angle-right"></i></a>
                                        </li>
                                    </ul>
                                </nav>
                            </section>

                            <!-- side column -->
                            <aside class="ab-side-col">
                                <section class="sd-card ab-calendar-card">
                                    <div class="sd-card-head">
                                        <div>
                                            <h3>Booking Calendar</h3>
                                            <p>March 2026 scheduled lessons.</p>
                                        </div>
                                    </div>
                                    <div class="ab-calendar" id="ab-calendar" role="group" aria-label="Booking calendar demo">
                                        <div class="ab-cal-weekdays">
                                            <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                        </div>
                                        <div class="ab-cal-days" id="ab-cal-days">
                                            <!-- filled by JS -->
                                        </div>
                                    </div>
                                    <ul class="ab-cal-legend">
                                        <li><span class="ab-dot is-confirmed"></span> Confirmed</li>
                                        <li><span class="ab-dot is-pending"></span> Pending</li>
                                        <li><span class="ab-dot is-selected"></span> Selected</li>
                                    </ul>
                                    <div class="ab-cal-selected" id="ab-cal-selected">
                                        <strong>Select a date</strong>
                                        <p>Click a highlighted day to preview scheduled lessons.</p>
                                    </div>
                                </section>

                                <section class="sd-card">
                                    <div class="sd-card-head">
                                        <div>
                                            <h3>Recent Booking Activity</h3>
                                            <p>Latest booking events.</p>
                                        </div>
                                    </div>
                                    <ul class="ad-timeline ab-activity-timeline">
                                        <?php if ( empty( $booking_activity ) ) : ?>
                                        <li class="ad-timeline-item is-class">
                                            <span class="ad-timeline-icon"><i class="far fa-calendar-check"></i></span>
                                            <div class="ad-timeline-body">
                                                <h4><?php echo esc_html__( 'No recent activity', 'gospel-music-mastery' ); ?></h4>
                                                <p><?php echo esc_html__( 'Booking events will appear here.', 'gospel-music-mastery' ); ?></p>
                                            </div>
                                            <span class="ad-timeline-time">—</span>
                                        </li>
                                        <?php else : ?>
                                            <?php foreach ( $booking_activity as $activity ) : ?>
                                                <?php
                                                $a_title = isset( $activity['title'] ) ? (string) $activity['title'] : '';
                                                $a_meta  = isset( $activity['meta'] ) ? (string) $activity['meta'] : '';
                                                $a_time  = isset( $activity['time'] ) ? (string) $activity['time'] : '';
                                                $a_icon  = isset( $activity['icon'] ) ? (string) $activity['icon'] : 'far fa-calendar-check';
                                                $a_css   = isset( $activity['css'] ) ? (string) $activity['css'] : 'is-class';
                                                ?>
                                        <li class="ad-timeline-item <?php echo esc_attr( $a_css ); ?>">
                                            <span class="ad-timeline-icon"><i class="<?php echo esc_attr( $a_icon ); ?>"></i></span>
                                            <div class="ad-timeline-body">
                                                <h4><?php echo esc_html( $a_title ); ?></h4>
                                                <p><?php echo esc_html( $a_meta ); ?></p>
                                            </div>
                                            <span class="ad-timeline-time"><?php echo esc_html( $a_time ); ?></span>
                                        </li>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </ul>
                                </section>
                            </aside>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- admin bookings end -->

    

<!-- booking detail modal -->
    <div class="modal fade gospel-demo-modal" id="ab-booking-modal" tabindex="-1" aria-labelledby="ab-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ab-modal-title">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body at-modal-body">
                    <div class="ab-modal-parties">
                        <div class="ab-modal-party">
                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="Student" id="ab-modal-student-img">
                            <div>
                                <small>Student</small>
                                <strong id="ab-modal-student">Sarah Johnson</strong>
                                <span id="ab-modal-email">sarah@email.com</span>
                            </div>
                        </div>
                        <div class="ab-modal-party">
                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="Teacher" id="ab-modal-teacher-img">
                            <div>
                                <small>Teacher</small>
                                <strong id="ab-modal-teacher">John Smith</strong>
                                <span id="ab-modal-phone">+1 555 123456</span>
                            </div>
                        </div>
                    </div>
                    <ul class="booking-modal-list at-modal-list">
                        <li><span>Booking ID</span><strong id="ab-modal-id">BK-1042</strong></li>
                        <li><span>Class Details</span><strong id="ab-modal-class">Beginner Gospel Piano</strong></li>
                        <li><span>Date</span><strong id="ab-modal-date">March 20, 2026</strong></li>
                        <li><span>Time</span><strong id="ab-modal-time">10:00 AM</strong></li>
                        <li><span>Duration</span><strong id="ab-modal-duration">60 Minutes</strong></li>
                        <li><span>Payment Amount</span><strong id="ab-modal-amount">$40</strong></li>
                        <li><span>Payment Status</span><strong id="ab-modal-payment-wrap"><span class="sb-badge is-confirmed" id="ab-modal-payment">Paid</span></strong></li>
                        <li><span>Booking Status</span><strong id="ab-modal-status-wrap"><span class="sb-badge is-confirmed" id="ab-modal-status">Confirmed</span></strong></li>
                    </ul>
                    <div class="at-modal-bio">
                        <h5>Notes</h5>
                        <p id="ab-modal-notes">Booking notes will appear here.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" id="ab-modal-cancel">
                        <i class="far fa-circle-xmark"></i> Cancel Booking
                    </button>
                    <button type="button" class="theme-btn theme-btn-outline" id="ab-modal-confirm">
                        <i class="far fa-check"></i> Confirm Booking
                    </button>
                    <button type="button" class="theme-btn" id="ab-modal-complete">
                        <i class="far fa-circle-check"></i> Mark Complete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="gospel-alert gospel-alert-success sl-toast" id="ab-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="ab-toast-text">Action completed (demo).</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

