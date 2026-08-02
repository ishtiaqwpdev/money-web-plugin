<?php
/**
 * Template: admin-teachers
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $gmm_admin_denied ) ) {
	echo '<div class="gmm-wrapper"><p>' . esc_html__( 'You do not have permission to manage teachers.', 'gospel-music-mastery' ) . '</p></div>';
	return;
}

if ( ! isset( $user_name ) ) {
	$user_name = 'Guest';
}
if ( ! isset( $user_first_name ) ) {
	$user_first_name = $user_name;
}
if ( ! isset( $teachers ) || ! is_array( $teachers ) ) {
	$teachers = array();
}
if ( ! isset( $teacher_stats ) || ! is_array( $teacher_stats ) ) {
	$teacher_stats = array(
		'total'     => 0,
		'pending'   => 0,
		'approved'  => 0,
		'suspended' => 0,
	);
}
if ( ! isset( $filters ) || ! is_array( $filters ) ) {
	$filters = array(
		'search'    => '',
		'status'    => 'all',
		'specialty' => 'all',
		'page'      => 1,
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
if ( ! isset( $logout_url ) ) {
	$logout_url = function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) );
}
if ( ! isset( $last_login_label ) ) {
	$last_login_label = __( 'Last login: Today', 'gospel-music-mastery' );
}

$filter_search    = isset( $filters['search'] ) ? (string) $filters['search'] : '';
$filter_status    = isset( $filters['status'] ) ? (string) $filters['status'] : 'all';
$filter_specialty = isset( $filters['specialty'] ) ? (string) $filters['specialty'] : 'all';
$result_total     = isset( $pagination['total'] ) ? absint( $pagination['total'] ) : count( $teachers );
$total_pages      = isset( $pagination['total_pages'] ) ? absint( $pagination['total_pages'] ) : 1;
$current_page     = isset( $pagination['page'] ) ? absint( $pagination['page'] ) : 1;
?>
<div class="gmm-wrapper gmm-dashboard gmm-admin">

        <!-- admin teachers -->
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
                                        <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_teachers' ) ); ?>" class="sd-nav-link ad-sub-link active" data-nav="teachers"><i class="far fa-chalkboard-user"></i> Teachers</a></li>
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

                        <!-- page header -->
                        <section class="sd-card sd-welcome-card">
                            <div class="sd-card-head">
                                <div>
                                    <span class="login-portal-badge">User Management</span>
                                    <h3>Manage Teachers</h3>
                                    <p>Review teacher applications, manage instructor accounts, and monitor teaching activity.</p>
                                </div>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'admin_dashboard' ) ); ?>" class="theme-btn theme-btn-outline"><i class="far fa-grid-2"></i> Dashboard</a>
                            </div>
                        </section>

                        <!-- summary cards -->
                        <section class="sd-stats-grid ad-stats-grid at-stats-grid">
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-chalkboard-user"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $teacher_stats['total'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Total Teachers</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card is-pending">
                                <div class="sd-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $teacher_stats['pending'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Pending Approval</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $teacher_stats['approved'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Approved Teachers</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-ban"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $teacher_stats['suspended'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Suspended</span>
                                </div>
                            </div>
                        </section>

                        <!-- analytics charts -->
                        <div class="gmm-chart-grid">
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Teacher Registration Analytics</h3>
                                        <p>New teacher registrations each month.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-at-registration" aria-label="Teacher registration line chart"></canvas>
                                </div>
                            </section>
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Teacher Status Distribution</h3>
                                        <p>Breakdown of teacher account statuses.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap is-doughnut">
                                    <canvas id="gmm-at-status" aria-label="Teacher status doughnut chart"></canvas>
                                </div>
                            </section>
                        </div>

                        <!-- teachers table card -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>All Teachers</h3>
                                    <p>Search, filter, and manage instructor accounts.</p>
                                </div>
                                <span class="sf-count-pill" id="at-result-count"><i class="far fa-chalkboard-user"></i> <strong><?php echo esc_html( (string) $result_total ); ?></strong> Shown</span>
                            </div>

                            <!-- search & filters -->
                            <form class="at-filter-bar" id="at-filter-form" action="" method="get">
                                <?php if ( is_admin() ) : ?>
                                    <input type="hidden" name="page" value="gmm-teachers">
                                <?php endif; ?>
                                <div class="at-search-field">
                                    <i class="far fa-search" aria-hidden="true"></i>
                                    <input type="search" class="form-control" id="at-search" name="at_search"
                                        value="<?php echo esc_attr( $filter_search ); ?>"
                                        placeholder="Search teachers by name or email..." autocomplete="off">
                                </div>
                                <div class="at-filter-selects">
                                    <div class="form-group mb-0">
                                        <label for="at-status" class="visually-hidden">Status</label>
                                        <select class="form-control form-select" id="at-status" name="at_status">
                                            <option value="all" <?php selected( $filter_status, 'all' ); ?>>All Status</option>
                                            <option value="pending" <?php selected( $filter_status, 'pending' ); ?>>Pending</option>
                                            <option value="approved" <?php selected( $filter_status, 'approved' ); ?>>Approved</option>
                                            <option value="rejected" <?php selected( $filter_status, 'rejected' ); ?>>Rejected</option>
                                            <option value="suspended" <?php selected( $filter_status, 'suspended' ); ?>>Suspended</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="at-specialty" class="visually-hidden">Specialization</label>
                                        <select class="form-control form-select" id="at-specialty" name="at_specialty">
                                            <option value="all" <?php selected( $filter_specialty, 'all' ); ?>>All Specializations</option>
                                            <option value="piano" <?php selected( $filter_specialty, 'piano' ); ?>>Piano</option>
                                            <option value="vocals" <?php selected( $filter_specialty, 'vocals' ); ?>>Vocals</option>
                                            <option value="guitar" <?php selected( $filter_specialty, 'guitar' ); ?>>Guitar</option>
                                            <option value="drums" <?php selected( $filter_specialty, 'drums' ); ?>>Drums</option>
                                            <option value="theory" <?php selected( $filter_specialty, 'theory' ); ?>>Music Theory</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="theme-btn" id="at-filter-btn">
                                        <i class="far fa-filter"></i> Filter
                                    </button>
                                </div>
                            </form>

                            <div class="table-responsive td-table-wrap" id="at-table-wrap" <?php echo empty( $teachers ) ? 'hidden' : ''; ?>>
                                <table class="table td-table sb-table at-table">
                                    <thead>
                                        <tr>
                                            <th>Profile</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Specialization</th>
                                            <th>Students</th>
                                            <th>Rating</th>
                                            <th>Status</th>
                                            <th>Joined Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="at-table-body">
                                        <?php if ( empty( $teachers ) ) : ?>
                                        <?php else : ?>
                                            <?php foreach ( $teachers as $teacher ) : ?>
                                                <?php
                                                $tid   = isset( $teacher['id'] ) ? absint( $teacher['id'] ) : 0;
                                                $tname = isset( $teacher['name'] ) ? (string) $teacher['name'] : '';
                                                $temail = isset( $teacher['email'] ) ? (string) $teacher['email'] : '';
                                                $tstatus = isset( $teacher['status'] ) ? (string) $teacher['status'] : 'pending';
                                                $tspec  = isset( $teacher['specialty'] ) ? (string) $teacher['specialty'] : 'all';
                                                $tphone = isset( $teacher['phone'] ) ? (string) $teacher['phone'] : '';
                                                $texp   = isset( $teacher['experience'] ) ? (string) $teacher['experience'] : '';
                                                $trating = isset( $teacher['rating'] ) ? (string) $teacher['rating'] : '0.0';
                                                $tclasses = isset( $teacher['classes'] ) ? absint( $teacher['classes'] ) : 0;
                                                $tstudents = isset( $teacher['students'] ) ? absint( $teacher['students'] ) : 0;
                                                $tbio = isset( $teacher['bio'] ) ? (string) $teacher['bio'] : '';
                                                $timg = isset( $teacher['image'] ) ? (string) $teacher['image'] : '';
                                                $tjoined = isset( $teacher['joined'] ) ? (string) $teacher['joined'] : '';
                                                $tspec_label = isset( $teacher['specialization'] ) ? (string) $teacher['specialization'] : '';
                                                $tstatus_label = isset( $teacher['status_label'] ) ? (string) $teacher['status_label'] : '';
                                                $tstatus_class = isset( $teacher['status_class'] ) ? (string) $teacher['status_class'] : 'is-pending';
                                                ?>
                                        <tr class="at-row"
                                            data-teacher-id="<?php echo esc_attr( (string) $tid ); ?>"
                                            data-name="<?php echo esc_attr( $tname ); ?>"
                                            data-email="<?php echo esc_attr( $temail ); ?>"
                                            data-status="<?php echo esc_attr( $tstatus ); ?>"
                                            data-specialty="<?php echo esc_attr( $tspec ); ?>"
                                            data-phone="<?php echo esc_attr( $tphone ); ?>"
                                            data-experience="<?php echo esc_attr( $texp ); ?>"
                                            data-rating="<?php echo esc_attr( $trating ); ?>"
                                            data-classes="<?php echo esc_attr( (string) $tclasses ); ?>"
                                            data-students="<?php echo esc_attr( (string) $tstudents ); ?>"
                                            data-bio="<?php echo esc_attr( $tbio ); ?>"
                                            data-image="<?php echo esc_url( $timg ); ?>"
                                            data-joined="<?php echo esc_attr( $tjoined ); ?>">
                                            <td data-label="Profile">
                                                <img class="at-avatar" src="<?php echo esc_url( $timg ); ?>" alt="<?php echo esc_attr( $tname ); ?>">
                                            </td>
                                            <td data-label="Name"><strong><?php echo esc_html( $tname ); ?></strong></td>
                                            <td data-label="Email"><?php echo esc_html( $temail ); ?></td>
                                            <td data-label="Specialization"><?php echo esc_html( $tspec_label ); ?></td>
                                            <td data-label="Students"><?php echo esc_html( (string) $tstudents ); ?> Students</td>
                                            <td data-label="Rating"><span class="td-rating">★★★★★</span> <?php echo esc_html( $trating ); ?></td>
                                            <td data-label="Status"><span class="sb-badge <?php echo esc_attr( $tstatus_class ); ?> at-status"><?php echo esc_html( $tstatus_label ); ?></span></td>
                                            <td data-label="Joined Date"><?php echo esc_html( $tjoined ); ?></td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="<?php echo esc_attr( sprintf( 'Actions for %s', $tname ) ); ?>">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-approve-btn"><i class="far fa-circle-check"></i> <span>Approve</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-reject-btn"><i class="far fa-circle-xmark"></i> <span>Reject</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-suspend-btn"><i class="far fa-ban"></i> <span>Suspend</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item is-logout at-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                    </tbody>
                                </table>
                            </div>

                            <div class="sl-empty" id="at-empty" <?php echo empty( $teachers ) ? '' : 'hidden'; ?>>
                                <i class="far fa-chalkboard-user"></i>
                                <h3>No teachers found.</h3>
                                <p>Try adjusting your search or filter options.</p>
                            </div>

                            <!-- pagination -->
                            <?php
                            $show_pagination = $total_pages > 1;
                            $prev_disabled   = empty( $pagination['has_prev'] );
                            $next_disabled   = empty( $pagination['has_next'] );
                            $prev_url = ( ! $prev_disabled && ! empty( $pagination['prev_page'] ) && function_exists( 'gmm_admin_teachers_page_url' ) )
                                ? gmm_admin_teachers_page_url( (int) $pagination['prev_page'], $filters )
                                : '#';
                            $next_url = ( ! $next_disabled && ! empty( $pagination['next_page'] ) && function_exists( 'gmm_admin_teachers_page_url' ) )
                                ? gmm_admin_teachers_page_url( (int) $pagination['next_page'], $filters )
                                : '#';
                            ?>
                            <nav class="at-pagination" id="at-pagination" aria-label="Teachers pagination" <?php echo $show_pagination ? '' : 'hidden'; ?>>
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item<?php echo $prev_disabled ? ' disabled' : ''; ?>" id="at-page-prev">
                                        <a class="page-link" href="<?php echo esc_url( $prev_url ); ?>" data-page="prev" aria-label="Previous"><i class="far fa-angle-left"></i> Previous</a>
                                    </li>
                                    <?php
                                    $start_p = max( 1, $current_page - 2 );
                                    $end_p   = min( $total_pages, $start_p + 4 );
                                    $start_p = max( 1, $end_p - 4 );
                                    for ( $p = $start_p; $p <= $end_p; $p++ ) :
                                        $p_url = function_exists( 'gmm_admin_teachers_page_url' ) ? gmm_admin_teachers_page_url( $p, $filters ) : '#';
                                        ?>
                                    <li class="page-item<?php echo ( $p === $current_page ) ? ' active' : ''; ?>"><a class="page-link" href="<?php echo esc_url( $p_url ); ?>" data-page="<?php echo esc_attr( (string) $p ); ?>"><?php echo esc_html( (string) $p ); ?></a></li>
                                    <?php endfor; ?>
                                    <li class="page-item<?php echo $next_disabled ? ' disabled' : ''; ?>" id="at-page-next">
                                        <a class="page-link" href="<?php echo esc_url( $next_url ); ?>" data-page="next" aria-label="Next">Next <i class="far fa-angle-right"></i></a>
                                    </li>
                                </ul>
                            </nav>
                        </section>

                        <!-- dashboard footer -->

                    </div>
                </div>
            </div>
        </div>
        <!-- admin teachers end -->

    

<!-- teacher detail modal -->
    <div class="modal fade gospel-demo-modal" id="at-teacher-modal" tabindex="-1" aria-labelledby="at-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="at-modal-title">Teacher Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body at-modal-body">
                    <div class="at-modal-hero">
                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="Teacher" id="at-modal-image">
                        <div>
                            <h4 id="at-modal-name">John Smith</h4>
                            <p id="at-modal-specialty">Gospel Piano</p>
                            <span class="td-rating" id="at-modal-rating">★★★★★ 4.9</span>
                            <span class="sb-badge is-pending" id="at-modal-status">Pending</span>
                        </div>
                    </div>
                    <ul class="booking-modal-list at-modal-list">
                        <li><span>Email</span><strong id="at-modal-email">john@email.com</strong></li>
                        <li><span>Phone</span><strong id="at-modal-phone">+1 615 555 0101</strong></li>
                        <li><span>Experience</span><strong id="at-modal-experience">10+ Years</strong></li>
                        <li><span>Classes</span><strong id="at-modal-classes">12</strong></li>
                        <li><span>Students</span><strong id="at-modal-students">25</strong></li>
                        <li><span>Joined</span><strong id="at-modal-joined">Jan 12, 2025</strong></li>
                    </ul>
                    <div class="at-modal-bio">
                        <h5>Bio</h5>
                        <p id="at-modal-bio">Teacher biography will appear here.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" id="at-modal-reject">
                        <i class="far fa-circle-xmark"></i> Reject Teacher
                    </button>
                    <button type="button" class="theme-btn" id="at-modal-approve">
                        <i class="far fa-circle-check"></i> Approve Teacher
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- demo toast -->
    <div class="gospel-alert gospel-alert-success sl-toast" id="at-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="at-toast-text">Action completed.</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

