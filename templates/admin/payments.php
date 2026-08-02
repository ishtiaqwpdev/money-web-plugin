<?php
/**
 * Template: admin-payments
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $gmm_admin_denied ) ) {
	echo '<div class="gmm-wrapper"><p>' . esc_html__( 'You do not have permission to manage payments.', 'gospel-music-mastery' ) . '</p></div>';
	return;
}

if ( ! isset( $user_name ) ) {
	$user_name = 'Guest';
}
if ( ! isset( $user_first_name ) ) {
	$user_first_name = $user_name;
}
if ( ! isset( $payments ) || ! is_array( $payments ) ) {
	$payments = array();
}
if ( ! isset( $payment_stats ) || ! is_array( $payment_stats ) ) {
	$payment_stats = function_exists( 'gmm_get_admin_revenue' ) ? gmm_get_admin_revenue() : array();
}
if ( ! isset( $filters ) || ! is_array( $filters ) ) {
	$filters = array(
		'search' => '',
		'status' => 'all',
		'type'   => 'all',
		'method' => 'all',
		'period' => 'all',
		'page'   => 1,
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
if ( ! isset( $refund_requests ) || ! is_array( $refund_requests ) ) {
	$refund_requests = array();
}
if ( ! isset( $teacher_earnings_list ) || ! is_array( $teacher_earnings_list ) ) {
	$teacher_earnings_list = array();
}
if ( ! isset( $logout_url ) ) {
	$logout_url = function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) );
}
if ( ! isset( $last_login_label ) ) {
	$last_login_label = __( 'Last login: Today', 'gospel-music-mastery' );
}

$filter_search  = isset( $filters['search'] ) ? (string) $filters['search'] : '';
$filter_status  = isset( $filters['status'] ) ? (string) $filters['status'] : 'all';
$filter_type    = isset( $filters['type'] ) ? (string) $filters['type'] : 'all';
$filter_period  = isset( $filters['period'] ) ? (string) $filters['period'] : 'all';
$result_total   = isset( $pagination['total'] ) ? absint( $pagination['total'] ) : count( $payments );
$total_pages    = isset( $pagination['total_pages'] ) ? absint( $pagination['total_pages'] ) : 1;
$current_page   = isset( $pagination['page'] ) ? absint( $pagination['page'] ) : 1;

$stat_revenue   = isset( $payment_stats['total_revenue'] ) ? (float) $payment_stats['total_revenue'] : 0;
$stat_commission = isset( $payment_stats['platform_commission'] ) ? (float) $payment_stats['platform_commission'] : 0;
$stat_teacher   = isset( $payment_stats['teacher_earnings'] ) ? (float) $payment_stats['teacher_earnings'] : 0;
$stat_pending   = isset( $payment_stats['pending_payouts'] ) ? (float) $payment_stats['pending_payouts'] : 0;
$stat_completed = isset( $payment_stats['completed_count'] ) ? absint( $payment_stats['completed_count'] ) : 0;
$stat_refunds   = isset( $payment_stats['refund_count'] ) ? absint( $payment_stats['refund_count'] ) : 0;
$stat_percent   = isset( $payment_stats['commission_percent'] ) ? (float) $payment_stats['commission_percent'] : 10;
?>
<div class="gmm-wrapper gmm-dashboard gmm-admin">

        <!-- admin payments -->
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_bookings' ) ); ?>" class="sd-nav-link" data-nav="bookings"><i class="far fa-calendar-check"></i> Bookings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_payments' ) ); ?>" class="sd-nav-link active" data-nav="payments"><i class="far fa-credit-card"></i> Payments</a></li>
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
                                    <span class="login-portal-badge">Financial Management</span>
                                    <h3>Payments &amp; Earnings</h3>
                                    <p>Monitor platform revenue, teacher payouts, student payments and financial transactions.</p>
                                </div>
                                <div class="ap-export-actions">
                                    <button type="button" class="theme-btn theme-btn-outline" id="ap-export-csv">
                                        <i class="far fa-file-csv"></i> Export CSV
                                    </button>
                                    <button type="button" class="theme-btn" id="ap-generate-report">
                                        <i class="far fa-file-chart-column"></i> Generate Report
                                    </button>
                                </div>
                            </div>
                        </section>

                        <!-- revenue stats -->
                        <section class="sd-stats-grid ad-stats-grid">
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-dollar-sign"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) round( $stat_revenue ) ); ?>" data-format="currency">$0</span>
                                    <span class="sd-stat-title">Total Revenue</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-percent"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) round( $stat_commission ) ); ?>" data-format="currency">$0</span>
                                    <span class="sd-stat-title">Platform Commission</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-chalkboard-user"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) round( $stat_teacher ) ); ?>" data-format="currency">$0</span>
                                    <span class="sd-stat-title">Teacher Earnings</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card is-pending">
                                <div class="sd-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) round( $stat_pending ) ); ?>" data-format="currency">$0</span>
                                    <span class="sd-stat-title">Pending Payouts</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-receipt"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) $stat_completed ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Completed Transactions</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-rotate-left"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) $stat_refunds ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Refunds</span>
                                </div>
                            </div>
                        </section>

                        <!-- earnings summary -->
                        <div class="ap-earn-grid">
                            <section class="sd-card ap-earn-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Teacher Earnings</h3>
                                        <p>Paid and pending teacher payouts.</p>
                                    </div>
                                    <span class="sd-stat-icon ap-earn-icon"><i class="far fa-chalkboard-user"></i></span>
                                </div>
                                <ul class="ap-earn-list">
                                    <li>
                                        <span>Total Paid</span>
                                        <strong><?php echo esc_html( '$' . number_format_i18n( $stat_teacher, 0 ) ); ?></strong>
                                    </li>
                                    <li>
                                        <span>Pending</span>
                                        <strong class="is-pending"><?php echo esc_html( '$' . number_format_i18n( $stat_pending, 0 ) ); ?></strong>
                                    </li>
                                </ul>
                            </section>

                            <section class="sd-card ap-earn-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Platform Earnings</h3>
                                        <p>Commission retained by the platform.</p>
                                    </div>
                                    <span class="sd-stat-icon ap-earn-icon"><i class="far fa-building-columns"></i></span>
                                </div>
                                <ul class="ap-earn-list">
                                    <li>
                                        <span>Commission</span>
                                        <strong><?php echo esc_html( '$' . number_format_i18n( $stat_commission, 0 ) ); ?></strong>
                                    </li>
                                    <li>
                                        <span>Share of Revenue</span>
                                        <strong><?php echo esc_html( number_format_i18n( $stat_percent, 0 ) . '%' ); ?></strong>
                                    </li>
                                </ul>
                            </section>
                        </div>

                        <!-- charts -->
                        <div class="ap-top-grid gmm-chart-grid">
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Revenue Chart</h3>
                                        <p>Monthly platform revenue for the year.</p>
                                    </div>
                                    <span class="ad-chart-total"><?php echo esc_html( '$' . number_format_i18n( $stat_revenue, 0 ) ); ?></span>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-ap-revenue" aria-label="Revenue area chart"></canvas>
                                </div>
                            </section>

                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Payment Status</h3>
                                        <p>Completed, pending, failed, and refunded.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap is-doughnut">
                                    <canvas id="gmm-ap-status" aria-label="Payment status doughnut chart"></canvas>
                                </div>
                            </section>
                        </div>

                        <!-- transactions -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Transactions</h3>
                                    <p>Search and filter all platform payment activity.</p>
                                </div>
                                <span class="sf-count-pill" id="ap-result-count"><i class="far fa-receipt"></i> <strong><?php echo esc_html( (string) $result_total ); ?></strong> Shown</span>
                            </div>

                            <form class="at-filter-bar" id="ap-filter-form" action="" method="get">
                                <?php if ( is_admin() ) : ?>
                                    <input type="hidden" name="page" value="gmm-payments">
                                <?php endif; ?>
                                <div class="at-search-field">
                                    <i class="far fa-search" aria-hidden="true"></i>
                                    <input type="search" class="form-control" id="ap-search" name="ap_search"
                                        value="<?php echo esc_attr( $filter_search ); ?>"
                                        placeholder="Search transaction ID or user..." autocomplete="off">
                                </div>
                                <div class="at-filter-selects">
                                    <div class="form-group mb-0">
                                        <label for="ap-status" class="visually-hidden">Payment Status</label>
                                        <select class="form-control form-select" id="ap-status" name="ap_status">
                                            <option value="all" <?php selected( $filter_status, 'all' ); ?>>All Payment Status</option>
                                            <option value="completed" <?php selected( $filter_status, 'completed' ); ?>>Completed</option>
                                            <option value="pending" <?php selected( $filter_status, 'pending' ); ?>>Pending</option>
                                            <option value="failed" <?php selected( $filter_status, 'failed' ); ?>>Failed</option>
                                            <option value="refunded" <?php selected( $filter_status, 'refunded' ); ?>>Refunded</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="ap-type" class="visually-hidden">Payment Type</label>
                                        <select class="form-control form-select" id="ap-type" name="ap_type">
                                            <option value="all" <?php selected( $filter_type, 'all' ); ?>>All Payment Types</option>
                                            <option value="lesson" <?php selected( $filter_type, 'lesson' ); ?>>Lesson Payment</option>
                                            <option value="payout" <?php selected( $filter_type, 'payout' ); ?>>Teacher Payout</option>
                                            <option value="refund" <?php selected( $filter_type, 'refund' ); ?>>Refund</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="ap-date" class="visually-hidden">Date</label>
                                        <select class="form-control form-select" id="ap-date" name="ap_date">
                                            <option value="all" <?php selected( $filter_period, 'all' ); ?>>All Dates</option>
                                            <option value="today" <?php selected( $filter_period, 'today' ); ?>>Today</option>
                                            <option value="week" <?php selected( $filter_period, 'week' ); ?>>This Week</option>
                                            <option value="month" <?php selected( $filter_period, 'month' ); ?>>This Month</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="theme-btn"><i class="far fa-filter"></i> Filter</button>
                                </div>
                            </form>

                            <div class="table-responsive td-table-wrap" id="ap-table-wrap" <?php echo empty( $payments ) ? 'hidden' : ''; ?>>
                                <table class="table td-table sb-table at-table">
                                    <thead>
                                        <tr>
                                            <th>Transaction ID</th>
                                            <th>User</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Payment Method</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                        <tbody id="ap-table-body">
                                        <?php if ( empty( $payments ) ) : ?>
                                        <?php else : ?>
                                            <?php foreach ( $payments as $payment ) : ?>
                                                <?php
                                                $pid = isset( $payment['id'] ) ? absint( $payment['id'] ) : 0;
                                                $ptxn = isset( $payment['txn_code'] ) ? (string) $payment['txn_code'] : ( 'TXN-' . $pid );
                                                $puser = isset( $payment['user'] ) ? (string) $payment['user'] : '';
                                                $ptype = isset( $payment['type'] ) ? (string) $payment['type'] : 'lesson';
                                                $ptype_label = isset( $payment['type_label'] ) ? (string) $payment['type_label'] : '';
                                                $pamount = isset( $payment['amount_label'] ) ? (string) $payment['amount_label'] : '$0';
                                                $pmethod = isset( $payment['method_label'] ) ? (string) $payment['method_label'] : '';
                                                $pstatus = isset( $payment['status'] ) ? (string) $payment['status'] : 'pending';
                                                $pstatus_label = isset( $payment['status_label'] ) ? (string) $payment['status_label'] : $pstatus;
                                                $pstatus_class = isset( $payment['status_class'] ) ? (string) $payment['status_class'] : 'is-pending';
                                                $pdate = isset( $payment['date'] ) ? (string) $payment['date'] : '';
                                                $pperiod = isset( $payment['period'] ) ? (string) $payment['period'] : 'all';
                                                $pemail = isset( $payment['user_email'] ) ? (string) $payment['user_email'] : '';
                                                $pimg = isset( $payment['user_image'] ) ? (string) $payment['user_image'] : '';
                                                $pbooking = isset( $payment['booking_code'] ) ? (string) $payment['booking_code'] : '—';
                                                $pcommission = isset( $payment['commission_label'] ) ? (string) $payment['commission_label'] : '';
                                                $pearnings = isset( $payment['teacher_earnings_label'] ) ? (string) $payment['teacher_earnings_label'] : '';
                                                $pteacher = isset( $payment['teacher'] ) ? (string) $payment['teacher'] : '';
                                                $pstudent = isset( $payment['student'] ) ? (string) $payment['student'] : '';
                                                $puser_url = ( $ptype === 'payout' && function_exists( 'gmm_get_page_link' ) )
                                                    ? add_query_arg( 'at_search', $pteacher, gmm_get_page_link( 'admin_teachers' ) )
                                                    : ( function_exists( 'gmm_get_page_link' ) ? add_query_arg( 'as_search', $pstudent, gmm_get_page_link( 'admin_students' ) ) : '' );
                                                $pbooking_url = ( ! empty( $payment['booking_id'] ) && function_exists( 'gmm_get_page_link' ) )
                                                    ? add_query_arg( 'ab_search', $pbooking, gmm_get_page_link( 'admin_bookings' ) )
                                                    : '';
                                                ?>
                                        <tr class="ap-row"
                                            data-id="<?php echo esc_attr( $ptxn ); ?>"
                                            data-payment-id="<?php echo esc_attr( (string) $pid ); ?>"
                                            data-user="<?php echo esc_attr( $puser ); ?>"
                                            data-type="<?php echo esc_attr( $ptype ); ?>"
                                            data-type-label="<?php echo esc_attr( $ptype_label ); ?>"
                                            data-amount="<?php echo esc_attr( $pamount ); ?>"
                                            data-method="<?php echo esc_attr( $pmethod ); ?>"
                                            data-status="<?php echo esc_attr( $pstatus ); ?>"
                                            data-date="<?php echo esc_attr( $pdate ); ?>"
                                            data-period="<?php echo esc_attr( $pperiod ); ?>"
                                            data-email="<?php echo esc_attr( $pemail ); ?>"
                                            data-user-img="<?php echo esc_url( $pimg ); ?>"
                                            data-booking="<?php echo esc_attr( $pbooking ); ?>"
                                            data-commission="<?php echo esc_attr( $pcommission ); ?>"
                                            data-teacher-earnings="<?php echo esc_attr( $pearnings ); ?>"
                                            data-teacher="<?php echo esc_attr( $pteacher ); ?>"
                                            data-student="<?php echo esc_attr( $pstudent ); ?>"
                                            data-user-url="<?php echo esc_url( $puser_url ); ?>"
                                            data-booking-url="<?php echo esc_url( $pbooking_url ); ?>">
                                            <td data-label="Transaction ID"><strong><?php echo esc_html( $ptxn ); ?></strong></td>
                                            <td data-label="User">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( $pimg ); ?>" alt="<?php echo esc_attr( $puser ); ?>">
                                                    <strong><?php echo esc_html( $puser ); ?></strong>
                                                </div>
                                            </td>
                                            <td data-label="Type"><?php echo esc_html( $ptype_label ); ?></td>
                                            <td data-label="Amount"><strong><?php echo esc_html( $pamount ); ?></strong></td>
                                            <td data-label="Payment Method"><?php echo esc_html( $pmethod ); ?></td>
                                            <td data-label="Status"><span class="sb-badge <?php echo esc_attr( $pstatus_class ); ?> ap-status"><?php echo esc_html( $pstatus_label ); ?></span></td>
                                            <td data-label="Date"><?php echo esc_html( $pdate ); ?></td>
                                            <td data-label="Action">
                                                <button type="button" class="at-action-btn ap-view-btn" aria-label="View transaction">
                                                    <i class="far fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                        </tbody>
                                </table>
                            </div>

                            <div class="sl-empty" id="ap-empty" <?php echo empty( $payments ) ? '' : 'hidden'; ?>>
                                <i class="far fa-receipt"></i>
                                <h3>No transactions found.</h3>
                                <p>Try adjusting your search or filter options.</p>
                            </div>

                            <?php
                            $show_pagination = $total_pages > 1;
                            $prev_disabled   = empty( $pagination['has_prev'] );
                            $next_disabled   = empty( $pagination['has_next'] );
                            $prev_url = ( ! $prev_disabled && ! empty( $pagination['prev_page'] ) && function_exists( 'gmm_admin_payments_page_url' ) )
                                ? gmm_admin_payments_page_url( (int) $pagination['prev_page'], $filters )
                                : '#';
                            $next_url = ( ! $next_disabled && ! empty( $pagination['next_page'] ) && function_exists( 'gmm_admin_payments_page_url' ) )
                                ? gmm_admin_payments_page_url( (int) $pagination['next_page'], $filters )
                                : '#';
                            ?>
                            <nav class="at-pagination" id="ap-pagination" aria-label="Transactions pagination" <?php echo $show_pagination ? '' : 'hidden'; ?>>
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item<?php echo $prev_disabled ? ' disabled' : ''; ?>" id="ap-page-prev">
                                        <a class="page-link" href="<?php echo esc_url( $prev_url ); ?>" data-page="prev" aria-label="Previous"><i class="far fa-angle-left"></i> Previous</a>
                                    </li>
                                    <?php
                                    $start_p = max( 1, $current_page - 2 );
                                    $end_p   = min( $total_pages, $start_p + 4 );
                                    $start_p = max( 1, $end_p - 4 );
                                    for ( $p = $start_p; $p <= $end_p; $p++ ) :
                                        $p_url = function_exists( 'gmm_admin_payments_page_url' ) ? gmm_admin_payments_page_url( $p, $filters ) : '#';
                                        ?>
                                    <li class="page-item<?php echo ( $p === $current_page ) ? ' active' : ''; ?>"><a class="page-link" href="<?php echo esc_url( $p_url ); ?>" data-page="<?php echo esc_attr( (string) $p ); ?>"><?php echo esc_html( (string) $p ); ?></a></li>
                                    <?php endfor; ?>
                                    <li class="page-item<?php echo $next_disabled ? ' disabled' : ''; ?>" id="ap-page-next">
                                        <a class="page-link" href="<?php echo esc_url( $next_url ); ?>" data-page="next" aria-label="Next">Next <i class="far fa-angle-right"></i></a>
                                    </li>
                                </ul>
                            </nav>
                        </section>

                        <!-- refund management -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Recent Refund Requests</h3>
                                    <p>Review and process student refund requests.</p>
                                </div>
                            </div>

                            <div class="table-responsive td-table-wrap">
                                <table class="table td-table sb-table at-table">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Amount</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                                                        <tbody id="ap-refund-body">
                                        <?php if ( empty( $refund_requests ) ) : ?>
                                        <tr>
                                            <td colspan="5"><?php echo esc_html__( 'No pending refund requests.', 'gospel-music-mastery' ); ?></td>
                                        </tr>
                                        <?php else : ?>
                                            <?php foreach ( $refund_requests as $refund ) : ?>
                                                <?php
                                                $rid = isset( $refund['id'] ) ? (string) $refund['id'] : '';
                                                $ruser = isset( $refund['user'] ) ? (string) $refund['user'] : '';
                                                $ramount = isset( $refund['amount_label'] ) ? (string) $refund['amount_label'] : '$0';
                                                $rreason = isset( $refund['reason'] ) ? (string) $refund['reason'] : '';
                                                $rimg = isset( $refund['user_image'] ) ? (string) $refund['user_image'] : '';
                                                $rpay = isset( $refund['payment_id'] ) ? absint( $refund['payment_id'] ) : 0;
                                                $ridx = isset( $refund['index'] ) ? (int) $refund['index'] : -1;
                                                ?>
                                        <tr class="ap-refund-row" data-id="<?php echo esc_attr( $rid ); ?>" data-payment-id="<?php echo esc_attr( (string) $rpay ); ?>" data-index="<?php echo esc_attr( (string) $ridx ); ?>" data-user="<?php echo esc_attr( $ruser ); ?>" data-amount="<?php echo esc_attr( $ramount ); ?>">
                                            <td data-label="User">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( $rimg ); ?>" alt="<?php echo esc_attr( $ruser ); ?>">
                                                    <strong><?php echo esc_html( $ruser ); ?></strong>
                                                </div>
                                            </td>
                                            <td data-label="Amount"><strong><?php echo esc_html( $ramount ); ?></strong></td>
                                            <td data-label="Reason"><?php echo esc_html( $rreason ); ?></td>
                                            <td data-label="Status"><span class="sb-badge is-pending ap-refund-status">Pending</span></td>
                                            <td data-label="Action">
                                                <div class="ap-refund-actions">
                                                    <button type="button" class="theme-btn ap-approve-refund"><i class="far fa-check"></i> Approve Refund</button>
                                                    <button type="button" class="theme-btn theme-btn-outline ap-reject-refund"><i class="far fa-xmark"></i> Reject Refund</button>
                                                </div>
                                            </td>
                                        </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- admin payments end -->

    

<!-- transaction detail modal -->
    <div class="modal fade gospel-demo-modal" id="ap-txn-modal" tabindex="-1" aria-labelledby="ap-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ap-modal-title">Transaction Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body at-modal-body">
                    <div class="ab-modal-parties ap-modal-user">
                        <div class="ab-modal-party">
                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="User" id="ap-modal-user-img">
                            <div>
                                <small>User Details</small>
                                <strong id="ap-modal-user">Sarah Johnson</strong>
                                <span id="ap-modal-email">sarah@email.com</span>
                            </div>
                        </div>
                    </div>
                    <ul class="booking-modal-list at-modal-list">
                        <li><span>Transaction ID</span><strong id="ap-modal-id">TXN-1001</strong></li>
                        <li><span>Payment Type</span><strong id="ap-modal-type">Lesson Payment</strong></li>
                        <li><span>Amount</span><strong id="ap-modal-amount">$40</strong></li>
                        <li><span>Payment Method</span><strong id="ap-modal-method">Stripe</strong></li>
                        <li><span>Date</span><strong id="ap-modal-date">March 20, 2026</strong></li>
                        <li><span>Related Booking</span><strong id="ap-modal-booking">BK-1042</strong></li>
                        <li><span>Status</span><strong><span class="sb-badge is-confirmed" id="ap-modal-status">Completed</span></strong></li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" id="ap-modal-user-btn">
                        <i class="far fa-user"></i> View User
                    </button>
                    <button type="button" class="theme-btn theme-btn-outline" id="ap-modal-booking-btn">
                        <i class="far fa-calendar-check"></i> View Booking
                    </button>
                    <button type="button" class="theme-btn" id="ap-modal-refund-btn">
                        <i class="far fa-rotate-left"></i> Process Refund
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="gospel-alert gospel-alert-success sl-toast" id="ap-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="ap-toast-text">Action completed (demo).</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

