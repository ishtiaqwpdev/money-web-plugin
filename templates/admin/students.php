<?php
/**
 * Template: admin-students
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $gmm_admin_denied ) ) {
	echo '<div class="gmm-wrapper"><p>' . esc_html__( 'You do not have permission to manage students.', 'gospel-music-mastery' ) . '</p></div>';
	return;
}

if ( ! isset( $user_name ) ) {
	$user_name = 'Guest';
}
if ( ! isset( $user_first_name ) ) {
	$user_first_name = $user_name;
}
if ( ! isset( $students ) || ! is_array( $students ) ) {
	$students = array();
}
if ( ! isset( $student_stats ) || ! is_array( $student_stats ) ) {
	$student_stats = array(
		'total'     => 0,
		'active'    => 0,
		'new'       => 0,
		'suspended' => 0,
	);
}
if ( ! isset( $filters ) || ! is_array( $filters ) ) {
	$filters = array(
		'search' => '',
		'status' => 'all',
		'level'  => 'all',
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
if ( ! isset( $student_activity ) || ! is_array( $student_activity ) ) {
	$student_activity = array();
}
if ( ! isset( $logout_url ) ) {
	$logout_url = function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) );
}
if ( ! isset( $last_login_label ) ) {
	$last_login_label = __( 'Last login: Today', 'gospel-music-mastery' );
}

$filter_search = isset( $filters['search'] ) ? (string) $filters['search'] : '';
$filter_status = isset( $filters['status'] ) ? (string) $filters['status'] : 'all';
$filter_level  = isset( $filters['level'] ) ? (string) $filters['level'] : 'all';
$filter_period = isset( $filters['period'] ) ? (string) $filters['period'] : 'all';
$result_total  = isset( $pagination['total'] ) ? absint( $pagination['total'] ) : count( $students );
$total_pages   = isset( $pagination['total_pages'] ) ? absint( $pagination['total_pages'] ) : 1;
$current_page  = isset( $pagination['page'] ) ? absint( $pagination['page'] ) : 1;
?>
<div class="gmm-wrapper gmm-dashboard gmm-admin">

        <!-- admin students -->
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
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_teachers' ) ); ?>">
                                    <i class="far fa-user-plus"></i>
                                    <span><strong>John Smith</strong> applied as a teacher</span>
                                </a>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_classes' ) ); ?>">
                                    <i class="far fa-chalkboard"></i>
                                    <span>New class submitted for review</span>
                                </a>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_payments' ) ); ?>">
                                    <i class="far fa-credit-card"></i>
                                    <span>Payment of <strong>$40</strong> received</span>
                                </a>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_teachers' ) ); ?>">
                                    <i class="far fa-triangle-exclamation"></i>
                                    <span>15 approvals are pending</span>
                                </a>
                            </div>
                        </div>

                        <div class="dropdown ad-profile-dropdown">
                            <button class="ad-profile-btn" type="button" id="ad-profile-menu" data-bs-toggle="dropdown"
                                aria-expanded="false">
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

                    <!-- admin sidebar -->
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
                                        <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_students' ) ); ?>" class="sd-nav-link ad-sub-link active" data-nav="students"><i class="far fa-graduation-cap"></i> Students</a></li>
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

                        <!-- page header -->
                        <section class="sd-card sd-welcome-card">
                            <div class="sd-card-head">
                                <div>
                                    <span class="login-portal-badge">User Management</span>
                                    <h3>Manage Students</h3>
                                    <p>View student accounts, monitor learning activity, and manage platform users.</p>
                                </div>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'admin_dashboard' ) ); ?>" class="theme-btn theme-btn-outline"><i class="far fa-grid-2"></i> Dashboard</a>
                            </div>
                        </section>

                        <!-- summary cards -->
                        <section class="sd-stats-grid ad-stats-grid at-stats-grid">
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-graduation-cap"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $student_stats['total'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Total Students</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $student_stats['active'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Active Students</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-user-plus"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $student_stats['new'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">New Registrations</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card is-pending">
                                <div class="sd-stat-icon"><i class="far fa-ban"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $student_stats['suspended'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Suspended Accounts</span>
                                </div>
                            </div>
                        </section>

                        <!-- analytics charts -->
                        <div class="gmm-chart-grid">
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Student Registration Growth</h3>
                                        <p>New student registrations each month.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-as-registration" aria-label="Student registration line chart"></canvas>
                                </div>
                            </section>
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Student Level Distribution</h3>
                                        <p>Students by learning level.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap is-doughnut">
                                    <canvas id="gmm-as-level" aria-label="Student level doughnut chart"></canvas>
                                </div>
                            </section>
                        </div>

                        <div class="as-content-grid">

                            <!-- students table -->
                            <section class="sd-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>All Students</h3>
                                        <p>Search, filter, and manage student accounts.</p>
                                    </div>
                                    <span class="sf-count-pill" id="as-result-count"><i class="far fa-graduation-cap"></i> <strong><?php echo esc_html( (string) $result_total ); ?></strong> Shown</span>
                                </div>

                                <!-- search & filters -->
                                <form class="at-filter-bar" id="as-filter-form" action="" method="get">
                                    <?php if ( is_admin() ) : ?>
                                        <input type="hidden" name="page" value="gmm-students">
                                    <?php endif; ?>
                                    <div class="at-search-field">
                                        <i class="far fa-search" aria-hidden="true"></i>
                                        <input type="search" class="form-control" id="as-search" name="as_search"
                                            value="<?php echo esc_attr( $filter_search ); ?>"
                                            placeholder="Search students by name or email..." autocomplete="off">
                                    </div>
                                    <div class="at-filter-selects">
                                        <div class="form-group mb-0">
                                            <label for="as-status" class="visually-hidden">Status</label>
                                            <select class="form-control form-select" id="as-status" name="as_status">
                                                <option value="all" <?php selected( $filter_status, 'all' ); ?>>All Status</option>
                                                <option value="active" <?php selected( $filter_status, 'active' ); ?>>Active</option>
                                                <option value="inactive" <?php selected( $filter_status, 'inactive' ); ?>>Inactive</option>
                                                <option value="suspended" <?php selected( $filter_status, 'suspended' ); ?>>Suspended</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="as-level" class="visually-hidden">Learning Level</label>
                                            <select class="form-control form-select" id="as-level" name="as_level">
                                                <option value="all" <?php selected( $filter_level, 'all' ); ?>>All Levels</option>
                                                <option value="beginner" <?php selected( $filter_level, 'beginner' ); ?>>Beginner</option>
                                                <option value="intermediate" <?php selected( $filter_level, 'intermediate' ); ?>>Intermediate</option>
                                                <option value="advanced" <?php selected( $filter_level, 'advanced' ); ?>>Advanced</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="as-period" class="visually-hidden">Registration Date</label>
                                            <select class="form-control form-select" id="as-period" name="as_period">
                                                <option value="all" <?php selected( $filter_period, 'all' ); ?>>All Time</option>
                                                <option value="today" <?php selected( $filter_period, 'today' ); ?>>Today</option>
                                                <option value="month" <?php selected( $filter_period, 'month' ); ?>>This Month</option>
                                                <option value="year" <?php selected( $filter_period, 'year' ); ?>>This Year</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="theme-btn" id="as-filter-btn">
                                            <i class="far fa-filter"></i> Filter
                                        </button>
                                    </div>
                                </form>

                                <div class="table-responsive td-table-wrap" id="as-table-wrap" <?php echo empty( $students ) ? 'hidden' : ''; ?>>
                                    <table class="table td-table sb-table at-table">
                                        <thead>
                                            <tr>
                                                <th>Profile</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Learning Level</th>
                                                <th>Enrolled Classes</th>
                                                <th>Status</th>
                                                <th>Joined Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="as-table-body">
                                            <?php if ( empty( $students ) ) : ?>
                                            <?php else : ?>
                                                <?php foreach ( $students as $student ) : ?>
                                                    <?php
                                                    $sid   = isset( $student['id'] ) ? absint( $student['id'] ) : 0;
                                                    $sname = isset( $student['name'] ) ? (string) $student['name'] : '';
                                                    $semail = isset( $student['email'] ) ? (string) $student['email'] : '';
                                                    $sphone = isset( $student['phone'] ) ? (string) $student['phone'] : '';
                                                    $sstatus = isset( $student['status'] ) ? (string) $student['status'] : 'active';
                                                    $slevel = isset( $student['level'] ) ? (string) $student['level'] : 'all';
                                                    $slevel_label = isset( $student['level_label'] ) ? (string) $student['level_label'] : '';
                                                    $speriod = isset( $student['period'] ) ? (string) $student['period'] : 'year';
                                                    $sinstruments = isset( $student['instruments'] ) ? (string) $student['instruments'] : '';
                                                    $sclasses = isset( $student['classes'] ) ? absint( $student['classes'] ) : 0;
                                                    $slessons = isset( $student['lessons'] ) ? absint( $student['lessons'] ) : 0;
                                                    $sbookings = isset( $student['bookings'] ) ? absint( $student['bookings'] ) : 0;
                                                    $sjoined = isset( $student['joined'] ) ? (string) $student['joined'] : '';
                                                    $sbio = isset( $student['bio'] ) ? (string) $student['bio'] : '';
                                                    $sgoals = isset( $student['learning_goals'] ) ? (string) $student['learning_goals'] : '';
                                                    $simg = isset( $student['image'] ) ? (string) $student['image'] : '';
                                                    $sstatus_label = isset( $student['status_label'] ) ? (string) $student['status_label'] : '';
                                                    $sstatus_class = isset( $student['status_class'] ) ? (string) $student['status_class'] : 'is-confirmed';
                                                    $sfirst = isset( $student['first_name'] ) ? (string) $student['first_name'] : '';
                                                    $slast = isset( $student['last_name'] ) ? (string) $student['last_name'] : '';
                                                    $sactivity = sprintf(
                                                        'Total bookings: %1$d|Completed lessons: %2$d|Learning level: %3$s|Status: %4$s',
                                                        $sbookings,
                                                        $slessons,
                                                        $slevel_label,
                                                        $sstatus_label
                                                    );
                                                    ?>
                                            <tr class="as-row"
                                                data-student-id="<?php echo esc_attr( (string) $sid ); ?>"
                                                data-name="<?php echo esc_attr( $sname ); ?>"
                                                data-first-name="<?php echo esc_attr( $sfirst ); ?>"
                                                data-last-name="<?php echo esc_attr( $slast ); ?>"
                                                data-email="<?php echo esc_attr( $semail ); ?>"
                                                data-phone="<?php echo esc_attr( $sphone ); ?>"
                                                data-status="<?php echo esc_attr( $sstatus ); ?>"
                                                data-level="<?php echo esc_attr( $slevel ); ?>"
                                                data-level-label="<?php echo esc_attr( $slevel_label ); ?>"
                                                data-period="<?php echo esc_attr( $speriod ); ?>"
                                                data-instruments="<?php echo esc_attr( $sinstruments ); ?>"
                                                data-classes="<?php echo esc_attr( (string) $sclasses ); ?>"
                                                data-completed="<?php echo esc_attr( (string) $slessons ); ?>"
                                                data-bookings="<?php echo esc_attr( (string) $sbookings ); ?>"
                                                data-joined="<?php echo esc_attr( $sjoined ); ?>"
                                                data-bio="<?php echo esc_attr( $sbio ); ?>"
                                                data-goals="<?php echo esc_attr( $sgoals ); ?>"
                                                data-image="<?php echo esc_url( $simg ); ?>"
                                                data-activity="<?php echo esc_attr( $sactivity ); ?>">
                                                <td data-label="Profile"><img class="at-avatar" src="<?php echo esc_url( $simg ); ?>" alt="<?php echo esc_attr( $sname ); ?>"></td>
                                                <td data-label="Name"><strong><?php echo esc_html( $sname ); ?></strong></td>
                                                <td data-label="Email"><?php echo esc_html( $semail ); ?></td>
                                                <td data-label="Phone"><?php echo esc_html( $sphone ? $sphone : '—' ); ?></td>
                                                <td data-label="Learning Level"><?php echo esc_html( $slevel_label ); ?></td>
                                                <td data-label="Enrolled Classes"><?php echo esc_html( (string) $sclasses ); ?> Classes</td>
                                                <td data-label="Status"><span class="sb-badge <?php echo esc_attr( $sstatus_class ); ?> as-status"><?php echo esc_html( $sstatus_label ); ?></span></td>
                                                <td data-label="Joined Date"><?php echo esc_html( $sjoined ); ?></td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="<?php echo esc_attr( sprintf( 'Actions for %s', $sname ) ); ?>">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-edit-btn"><i class="far fa-pen"></i> <span>Edit Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-lessons-btn"><i class="far fa-book-open"></i> <span>View Lessons</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-payments-btn"><i class="far fa-credit-card"></i> <span>View Payments</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-suspend-btn"><i class="far fa-ban"></i> <span>Suspend Account</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item is-logout as-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>
                                                <?php endforeach; ?>
                                            <?php endif; ?>

                                        </tbody>
                                    </table>
                                </div>

                                <div class="sl-empty" id="as-empty" <?php echo empty( $students ) ? '' : 'hidden'; ?>>
                                    <i class="far fa-graduation-cap"></i>
                                    <h3>No students found.</h3>
                                    <p>Try adjusting your search or filter options.</p>
                                </div>

                                <?php
                                $show_pagination = $total_pages > 1;
                                $prev_disabled   = empty( $pagination['has_prev'] );
                                $next_disabled   = empty( $pagination['has_next'] );
                                $prev_url = ( ! $prev_disabled && ! empty( $pagination['prev_page'] ) && function_exists( 'gmm_admin_students_page_url' ) )
                                    ? gmm_admin_students_page_url( (int) $pagination['prev_page'], $filters )
                                    : '#';
                                $next_url = ( ! $next_disabled && ! empty( $pagination['next_page'] ) && function_exists( 'gmm_admin_students_page_url' ) )
                                    ? gmm_admin_students_page_url( (int) $pagination['next_page'], $filters )
                                    : '#';
                                ?>
                                <nav class="at-pagination" id="as-pagination" aria-label="Students pagination" <?php echo $show_pagination ? '' : 'hidden'; ?>>
                                    <ul class="pagination justify-content-center mb-0">
                                        <li class="page-item<?php echo $prev_disabled ? ' disabled' : ''; ?>" id="as-page-prev">
                                            <a class="page-link" href="<?php echo esc_url( $prev_url ); ?>" data-page="prev" aria-label="Previous"><i class="far fa-angle-left"></i> Previous</a>
                                        </li>
                                        <?php
                                        $start_p = max( 1, $current_page - 2 );
                                        $end_p   = min( $total_pages, $start_p + 4 );
                                        $start_p = max( 1, $end_p - 4 );
                                        for ( $p = $start_p; $p <= $end_p; $p++ ) :
                                            $p_url = function_exists( 'gmm_admin_students_page_url' ) ? gmm_admin_students_page_url( $p, $filters ) : '#';
                                            ?>
                                        <li class="page-item<?php echo ( $p === $current_page ) ? ' active' : ''; ?>"><a class="page-link" href="<?php echo esc_url( $p_url ); ?>" data-page="<?php echo esc_attr( (string) $p ); ?>"><?php echo esc_html( (string) $p ); ?></a></li>
                                        <?php endfor; ?>
                                        <li class="page-item<?php echo $next_disabled ? ' disabled' : ''; ?>" id="as-page-next">
                                            <a class="page-link" href="<?php echo esc_url( $next_url ); ?>" data-page="next" aria-label="Next">Next <i class="far fa-angle-right"></i></a>
                                        </li>
                                    </ul>
                                </nav>
                            </section>

                            <!-- recent activity card -->
                            <aside class="sd-card as-activity-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Recent Activity</h3>
                                        <p>Platform student activity overview.</p>
                                    </div>
                                </div>
                                <ul class="as-activity-list" id="as-activity-list">
                                    <?php if ( empty( $student_activity ) ) : ?>
                                    <li>
                                        <span class="as-activity-icon"><i class="far fa-bell"></i></span>
                                        <div>
                                            <strong><?php echo esc_html__( 'No recent activity', 'gospel-music-mastery' ); ?></strong>
                                            <small><?php echo esc_html__( 'Student activity will appear here.', 'gospel-music-mastery' ); ?></small>
                                        </div>
                                    </li>
                                    <?php else : ?>
                                        <?php foreach ( $student_activity as $act ) : ?>
                                    <li>
                                        <span class="as-activity-icon"><i class="<?php echo esc_attr( isset( $act['icon'] ) ? $act['icon'] : 'far fa-bell' ); ?>"></i></span>
                                        <div>
                                            <strong><?php echo esc_html( isset( $act['title'] ) ? $act['title'] : '' ); ?></strong>
                                            <small><?php echo esc_html( isset( $act['meta'] ) ? $act['meta'] : '' ); ?></small>
                                        </div>
                                    </li>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </ul>
                            </aside>

                        </div>

                        <!-- dashboard footer -->

                    </div>
                </div>
            </div>
        </div>
        <!-- admin students end -->

    

<!-- student detail modal -->
    <div class="modal fade gospel-demo-modal" id="as-student-modal" tabindex="-1" aria-labelledby="as-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="as-modal-title">Student Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body at-modal-body">
                    <div class="at-modal-hero">
                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="Student" id="as-modal-image">
                        <div>
                            <h4 id="as-modal-name">Sarah Johnson</h4>
                            <p id="as-modal-level">Intermediate</p>
                            <span class="sb-badge is-confirmed" id="as-modal-status">Active</span>
                        </div>
                    </div>
                    <ul class="booking-modal-list at-modal-list">
                        <li><span>Email</span><strong id="as-modal-email">sarah@email.com</strong></li>
                        <li><span>Phone</span><strong id="as-modal-phone">+1 555 123456</strong></li>
                        <li><span>Learning Level</span><strong id="as-modal-level-detail">Intermediate</strong></li>
                        <li><span>Preferred Instruments</span><strong id="as-modal-instruments">Gospel Piano</strong></li>
                        <li><span>Enrolled Classes</span><strong id="as-modal-classes">5</strong></li>
                        <li><span>Completed Lessons</span><strong id="as-modal-completed">24</strong></li>
                        <li><span>Registration Date</span><strong id="as-modal-joined">Jan 08, 2026</strong></li>
                    </ul>
                    <div class="at-modal-bio">
                        <h5>Bio</h5>
                        <p id="as-modal-bio">Student biography will appear here.</p>
                    </div>
                    <div class="as-modal-activity">
                        <h5>Recent Activity</h5>
                        <ul id="as-modal-activity-list">
                            <li>Booked new lesson</li>
                            <li>Completed class</li>
                            <li>Added favourite teacher</li>
                            <li>Payment completed</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" id="as-modal-edit">
                        <i class="far fa-pen"></i> Edit Student
                    </button>
                    <button type="button" class="theme-btn" id="as-modal-suspend">
                        <i class="far fa-ban"></i> Suspend Student
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- demo toast -->
    <div class="gospel-alert gospel-alert-success sl-toast" id="as-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="as-toast-text">Action completed.</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

