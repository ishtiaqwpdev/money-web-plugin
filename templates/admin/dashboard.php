<?php
/**
 * Template: admin-dashboard
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $gmm_admin_denied ) ) {
	echo '<div class="gmm-wrapper gmm-dashboard gmm-admin"><div class="container py-120"><div class="sd-card"><div class="sd-card-head"><h3>' . esc_html__( 'Access Denied', 'gospel-music-mastery' ) . '</h3><p>' . esc_html__( 'You do not have permission to view the admin dashboard.', 'gospel-music-mastery' ) . '</p></div></div></div></div>';
	return;
}

if ( ! isset( $user_name ) ) {
	$user_name = 'Guest';
}
if ( ! isset( $user_first_name ) ) {
	$user_first_name = $user_name;
}

$stats = ( isset( $stats ) && is_array( $stats ) ) ? $stats : array();
$stats = wp_parse_args(
	$stats,
	array(
		'total_students'    => 0,
		'total_teachers'    => 0,
		'total_classes'     => 0,
		'total_bookings'    => 0,
		'total_revenue'     => 0,
		'pending_approvals' => 0,
	)
);

$booking_analytics = ( isset( $booking_analytics ) && is_array( $booking_analytics ) ) ? $booking_analytics : array();
$booking_analytics = wp_parse_args(
	$booking_analytics,
	array(
		'total'          => 0,
		'completed'      => 0,
		'pending'        => 0,
		'cancelled'      => 0,
		'pct_completed'  => 0,
		'pct_pending'    => 0,
		'pct_cancelled'  => 0,
		'gradient_stops' => array(
			'completed_end' => 0,
			'pending_end'   => 0,
		),
	)
);

$activity       = ( isset( $activity ) && is_array( $activity ) ) ? $activity : array();
$approvals      = ( isset( $approvals ) && is_array( $approvals ) ) ? $approvals : array();
$notifications  = ( isset( $notifications ) && is_array( $notifications ) ) ? $notifications : array();
$charts         = ( isset( $charts ) && is_array( $charts ) ) ? $charts : array();
$logout_url     = isset( $logout_url ) ? $logout_url : ( function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ) );
$last_login_label = isset( $last_login_label ) ? $last_login_label : __( 'Last login: Today', 'gospel-music-mastery' );
$revenue_total  = isset( $charts['revenue']['total'] ) ? (float) $charts['revenue']['total'] : (float) $stats['total_revenue'];
?>
<div class="gmm-wrapper gmm-dashboard gmm-admin">

        <!-- admin dashboard -->
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
                        <!-- notifications -->
                        <div class="dropdown ad-icon-dropdown">
                            <button class="ad-icon-btn" type="button" id="ad-notifications" data-bs-toggle="dropdown"
                                aria-expanded="false" aria-label="Notifications">
                                <i class="far fa-bell"></i>
                                <?php if ( ! empty( $notifications ) ) : ?>
                                <span class="ad-icon-badge"><?php echo esc_html( count( $notifications ) ); ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end ad-dropdown" aria-labelledby="ad-notifications">
                                <h6 class="ad-dropdown-title">Notifications</h6>
                                <?php if ( empty( $notifications ) ) : ?>
                                <span class="dropdown-item ad-dropdown-item"><i class="far fa-bell"></i><span><?php esc_html_e( 'No new notifications', 'gospel-music-mastery' ); ?></span></span>
                                <?php else : ?>
									<?php foreach ( $notifications as $note ) : ?>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( isset( $note['url'] ) ? $note['url'] : '#' ); ?>">
                                    <i class="<?php echo esc_attr( isset( $note['icon'] ) ? $note['icon'] : 'far fa-bell' ); ?>"></i>
                                    <span><?php echo esc_html( isset( $note['html'] ) ? $note['html'] : '' ); ?></span>
                                </a>
									<?php endforeach; ?>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- profile dropdown -->
                        <div class="dropdown ad-profile-dropdown">
                            <button class="ad-profile-btn" type="button" id="ad-profile-menu" data-bs-toggle="dropdown"
                                aria-expanded="false">
                                <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="<?php echo esc_attr( $user_name ); ?>">
                                <span class="ad-profile-btn-text">
                                    <strong><?php echo esc_html( $user_name ); ?></strong>
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

                    <!-- admin sidebar -->
                    <aside class="sd-sidebar ad-sidebar" id="sd-sidebar" aria-label="Admin navigation">
                        <nav class="sd-nav ad-nav">
                            <ul>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_dashboard' ) ); ?>" class="sd-nav-link active" data-nav="dashboard"><i class="far fa-grid-2"></i> Dashboard</a></li>

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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_payments' ) ); ?>" class="sd-nav-link" data-nav="payments"><i class="far fa-credit-card"></i> Payments</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_programs' ) ); ?>" class="sd-nav-link" data-nav="programs"><i class="far fa-music"></i> Music Programs</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_settings' ) ); ?>" class="sd-nav-link" data-nav="settings"><i class="far fa-gear"></i> Settings</a></li>
                                <li><a href="<?php echo esc_url( $logout_url ); ?>" class="sd-nav-link sd-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
                            </ul>
                        </nav>
                    </aside>
                    <div class="sd-sidebar-backdrop" id="sd-sidebar-backdrop" hidden></div>

                    <!-- main content -->
                    <div class="sd-main ad-main">

                        <!-- welcome -->
                        <section class="sd-card sd-welcome-card">
                            <div class="sd-card-head">
                                <div>
                                    <span class="login-portal-badge">Admin Control Center</span>
                                    <h3><?php echo esc_html( sprintf( /* translators: %s: first name */ __( 'Welcome Back, %s!', 'gospel-music-mastery' ), $user_first_name ) ); ?></h3>
                                    <p>Manage your Gospel Music Mastery platform, instructors, students, classes and payments from one place.</p>
                                </div>
                                <a href="#ad-approvals" class="theme-btn"><i class="far fa-clipboard-check"></i> Review Approvals</a>
                            </div>
                        </section>

                        <!-- statistics cards -->
                        <section class="sd-stats-grid ad-stats-grid">
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-graduation-cap"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $stats['total_students'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Total Students</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-chalkboard-user"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $stats['total_teachers'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Total Teachers</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-chalkboard"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $stats['total_classes'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Total Classes</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-calendar-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $stats['total_bookings'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Total Bookings</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-sack-dollar"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( round( (float) $stats['total_revenue'] ) ) ); ?>" data-format="currency">$0</span>
                                    <span class="sd-stat-title">Total Revenue</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card is-pending">
                                <div class="sd-stat-icon"><i class="far fa-clipboard-list-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $stats['pending_approvals'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Pending Approvals</span>
                                </div>
                            </div>
                        </section>

                        <div class="gmm-chart-grid is-triple">

                            <!-- revenue chart -->
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Revenue Overview</h3>
                                        <p>Monthly revenue across the full calendar year.</p>
                                    </div>
                                    <span class="ad-chart-total">$<?php echo esc_html( number_format_i18n( $revenue_total, 0 ) ); ?></span>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-admin-revenue" aria-label="Monthly revenue area chart"></canvas>
                                </div>
                            </section>

                            <!-- user growth chart -->
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>User Growth</h3>
                                        <p>New students and teachers joining each month.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-admin-user-growth" aria-label="User growth bar chart"></canvas>
                                </div>
                            </section>

                            <!-- platform distribution -->
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Platform Distribution</h3>
                                        <p>Share of students, teachers, and classes.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap is-doughnut">
                                    <canvas id="gmm-admin-platform" aria-label="Platform distribution doughnut chart"></canvas>
                                </div>
                            </section>
                        </div>

                        <!-- booking analytics -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Booking Analytics</h3>
                                    <p><?php echo esc_html( sprintf( /* translators: %d: booking count */ __( 'Breakdown of all %d bookings on the platform.', 'gospel-music-mastery' ), absint( $booking_analytics['total'] ) ) ); ?></p>
                                </div>
                            </div>
                            <div class="ad-booking-analytics">
                                <div class="ad-donut" id="ad-booking-donut" role="img"
                                    data-completed-end="<?php echo esc_attr( (string) absint( $booking_analytics['gradient_stops']['completed_end'] ) ); ?>"
                                    data-pending-end="<?php echo esc_attr( (string) absint( $booking_analytics['gradient_stops']['pending_end'] ) ); ?>"
                                    aria-label="<?php echo esc_attr( sprintf( 'Donut chart: %1$d completed, %2$d pending, %3$d cancelled bookings', absint( $booking_analytics['completed'] ), absint( $booking_analytics['pending'] ), absint( $booking_analytics['cancelled'] ) ) ); ?>">
                                    <div class="ad-donut-center">
                                        <strong><?php echo esc_html( (string) absint( $booking_analytics['total'] ) ); ?></strong>
                                        <small>Total</small>
                                    </div>
                                </div>
                                <ul class="ad-booking-list">
                                    <li>
                                        <span class="ad-legend-dot is-completed"></span>
                                        <div>
                                            <strong class="ad-counter" data-count="<?php echo esc_attr( (string) absint( $booking_analytics['completed'] ) ); ?>" data-format="number">0</strong>
                                            <small>Completed Bookings</small>
                                        </div>
                                        <span class="sb-badge is-confirmed"><?php echo esc_html( absint( $booking_analytics['pct_completed'] ) ); ?>%</span>
                                    </li>
                                    <li>
                                        <span class="ad-legend-dot is-pending"></span>
                                        <div>
                                            <strong class="ad-counter" data-count="<?php echo esc_attr( (string) absint( $booking_analytics['pending'] ) ); ?>" data-format="number">0</strong>
                                            <small>Pending Bookings</small>
                                        </div>
                                        <span class="sb-badge is-pending"><?php echo esc_html( absint( $booking_analytics['pct_pending'] ) ); ?>%</span>
                                    </li>
                                    <li>
                                        <span class="ad-legend-dot is-cancelled"></span>
                                        <div>
                                            <strong class="ad-counter" data-count="<?php echo esc_attr( (string) absint( $booking_analytics['cancelled'] ) ); ?>" data-format="number">0</strong>
                                            <small>Cancelled Bookings</small>
                                        </div>
                                        <span class="sb-badge is-cancelled"><?php echo esc_html( absint( $booking_analytics['pct_cancelled'] ) ); ?>%</span>
                                    </li>
                                </ul>
                            </div>
                        </section>

                        <!-- recent activity -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Recent Activity</h3>
                                    <p>Latest platform events across teachers, students and payments.</p>
                                </div>
                            </div>
                            <ul class="ad-timeline">
                                <?php if ( empty( $activity ) ) : ?>
                                <li class="ad-timeline-item is-class">
                                    <span class="ad-timeline-icon"><i class="far fa-bell"></i></span>
                                    <div class="ad-timeline-body">
                                        <h4><?php esc_html_e( 'No recent activity yet', 'gospel-music-mastery' ); ?></h4>
                                        <p><?php esc_html_e( 'New registrations, bookings, and payments will appear here.', 'gospel-music-mastery' ); ?></p>
                                    </div>
                                    <span class="ad-timeline-time"><?php esc_html_e( 'Now', 'gospel-music-mastery' ); ?></span>
                                </li>
                                <?php else : ?>
									<?php foreach ( $activity as $item ) : ?>
                                <li class="ad-timeline-item <?php echo esc_attr( isset( $item['css'] ) ? $item['css'] : '' ); ?>">
                                    <span class="ad-timeline-icon"><i class="<?php echo esc_attr( isset( $item['icon'] ) ? $item['icon'] : 'far fa-bell' ); ?>"></i></span>
                                    <div class="ad-timeline-body">
                                        <h4><?php echo esc_html( isset( $item['title'] ) ? $item['title'] : '' ); ?></h4>
                                        <p><?php echo esc_html( isset( $item['description'] ) ? $item['description'] : '' ); ?></p>
                                    </div>
                                    <span class="ad-timeline-time"><?php echo esc_html( isset( $item['time'] ) ? $item['time'] : '' ); ?></span>
                                </li>
									<?php endforeach; ?>
                                <?php endif; ?>
                            </ul>
                        </section>

                        <!-- pending approvals -->
                        <section class="sd-card" id="ad-approvals">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Pending Approvals</h3>
                                    <p>Review teacher applications and submitted classes.</p>
                                </div>
                                <span class="sf-count-pill"><i class="far fa-clipboard-list-check"></i> <?php echo esc_html( (string) absint( $stats['pending_approvals'] ) ); ?> Pending</span>
                            </div>

                            <div class="sl-tabs ad-filter-tabs" role="tablist" aria-label="Filter approvals">
                                <button type="button" class="sl-tab is-active" data-filter="all" role="tab" aria-selected="true">All</button>
                                <button type="button" class="sl-tab" data-filter="teacher" role="tab" aria-selected="false">Teacher Applications</button>
                                <button type="button" class="sl-tab" data-filter="class" role="tab" aria-selected="false">New Classes</button>
                            </div>

                            <div class="table-responsive td-table-wrap">
                                <table class="table td-table sb-table ad-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Name</th>
                                            <th>Date</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ad-approvals-body">
                                        <?php foreach ( $approvals as $row ) : ?>
                                        <tr class="ad-row" data-type="<?php echo esc_attr( $row['type'] ); ?>" data-name="<?php echo esc_attr( $row['name'] ); ?>" data-id="<?php echo esc_attr( (string) absint( $row['id'] ) ); ?>" data-view-url="<?php echo esc_url( $row['view_url'] ); ?>">
                                            <td data-label="Type"><?php echo esc_html( $row['type_label'] ); ?></td>
                                            <td data-label="Name">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( $row['image'] ); ?>" alt="<?php echo esc_attr( $row['name'] ); ?>">
                                                    <strong><?php echo esc_html( $row['name'] ); ?></strong>
                                                </div>
                                            </td>
                                            <td data-label="Date"><?php echo esc_html( $row['date_label'] ); ?></td>
                                            <td data-label="Status"><span class="sb-badge is-pending">Pending</span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
                                                    <button type="button" class="theme-btn sd-action-btn ad-approve-btn">Approve</button>
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn ad-reject-btn">Reject</button>
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn ad-view-btn">View</button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <div class="sl-empty" id="ad-approvals-empty" <?php echo empty( $approvals ) ? '' : 'hidden'; ?>>
                                <i class="far fa-clipboard-check"></i>
                                <h3>No approvals in this category.</h3>
                                <p>Try a different filter to see pending items.</p>
                            </div>
                        </section>

                        <!-- quick actions -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <h3>Quick Actions</h3>
                            </div>
                            <div class="sd-quick-grid">
                                <a href="<?php echo esc_url( gmm_get_page_link( 'admin_teachers' ) ); ?>" class="sd-quick-card">
                                    <i class="far fa-chalkboard-user"></i>
                                    <span>Manage Teachers</span>
                                </a>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'admin_students' ) ); ?>" class="sd-quick-card">
                                    <i class="far fa-graduation-cap"></i>
                                    <span>Manage Students</span>
                                </a>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'admin_classes' ) ); ?>" class="sd-quick-card">
                                    <i class="far fa-clipboard-check"></i>
                                    <span>Review Classes</span>
                                </a>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'admin_payments' ) ); ?>" class="sd-quick-card">
                                    <i class="far fa-credit-card"></i>
                                    <span>View Payments</span>
                                </a>
                            </div>
                        </section>

                        <!-- dashboard footer -->

                    </div>
                </div>
            </div>
        </div>
        <!-- admin dashboard end -->

    

<!-- approval review modal -->
    <div class="modal fade gospel-demo-modal" id="ad-review-modal" tabindex="-1" aria-labelledby="ad-review-title"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ad-review-title">Review Submission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-2"><strong id="ad-review-name">John Smith</strong></p>
                    <p id="ad-review-type">Teacher Application</p>
                    <p class="mb-0"><?php esc_html_e( 'Review this submission, then approve or reject from the table actions.', 'gospel-music-mastery' ); ?></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="theme-btn" data-bs-dismiss="modal">
                        <i class="far fa-check"></i> Mark Reviewed
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- toast -->
    <div class="gospel-alert gospel-alert-success sl-toast" id="ad-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="ad-toast-text"><?php esc_html_e( 'Action completed.', 'gospel-music-mastery' ); ?></span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

