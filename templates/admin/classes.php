<?php
/**
 * Template: admin-classes
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $gmm_admin_denied ) ) {
	echo '<div class="gmm-wrapper"><p>' . esc_html__( 'You do not have permission to manage classes.', 'gospel-music-mastery' ) . '</p></div>';
	return;
}

if ( ! isset( $user_name ) ) {
	$user_name = 'Guest';
}
if ( ! isset( $user_first_name ) ) {
	$user_first_name = $user_name;
}
if ( ! isset( $classes ) || ! is_array( $classes ) ) {
	$classes = array();
}
if ( ! isset( $class_stats ) || ! is_array( $class_stats ) ) {
	$class_stats = array(
		'total'    => 0,
		'approved' => 0,
		'pending'  => 0,
		'rejected' => 0,
	);
}
if ( ! isset( $filters ) || ! is_array( $filters ) ) {
	$filters = array(
		'search'     => '',
		'status'     => 'all',
		'category'   => 'all',
		'difficulty' => 'all',
		'page'       => 1,
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
if ( ! isset( $featured_classes ) || ! is_array( $featured_classes ) ) {
	$featured_classes = array();
}
if ( ! isset( $logout_url ) ) {
	$logout_url = function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) );
}
if ( ! isset( $last_login_label ) ) {
	$last_login_label = __( 'Last login: Today', 'gospel-music-mastery' );
}

$filter_search     = isset( $filters['search'] ) ? (string) $filters['search'] : '';
$filter_status     = isset( $filters['status'] ) ? (string) $filters['status'] : 'all';
$filter_category   = isset( $filters['category'] ) ? (string) $filters['category'] : 'all';
$filter_difficulty = isset( $filters['difficulty'] ) ? (string) $filters['difficulty'] : 'all';
$result_total      = isset( $pagination['total'] ) ? absint( $pagination['total'] ) : count( $classes );
$total_pages       = isset( $pagination['total_pages'] ) ? absint( $pagination['total_pages'] ) : 1;
$current_page      = isset( $pagination['page'] ) ? absint( $pagination['page'] ) : 1;
?>
<div class="gmm-wrapper gmm-dashboard gmm-admin">

        <!-- admin classes -->
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
                                        <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_students' ) ); ?>" class="sd-nav-link ad-sub-link" data-nav="students"><i class="far fa-graduation-cap"></i> Students</a></li>
                                    </ul>
                                </li>

                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_classes' ) ); ?>" class="sd-nav-link active" data-nav="classes"><i class="far fa-chalkboard"></i> Classes</a></li>
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
                                    <span class="login-portal-badge">Class Management</span>
                                    <h3>Manage Classes</h3>
                                    <p>Review instructor classes, approve new lessons, and manage all music programs on the platform.</p>
                                </div>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'admin_dashboard' ) ); ?>" class="theme-btn theme-btn-outline"><i class="far fa-grid-2"></i> Dashboard</a>
                            </div>
                        </section>

                        <!-- summary cards -->
                        <section class="sd-stats-grid ad-stats-grid at-stats-grid">
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-chalkboard"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $class_stats['total'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Total Classes</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $class_stats['approved'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Published Classes</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card is-pending">
                                <div class="sd-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $class_stats['pending'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Pending Review</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-xmark"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="<?php echo esc_attr( (string) absint( $class_stats['rejected'] ) ); ?>" data-format="number">0</span>
                                    <span class="sd-stat-title">Rejected Classes</span>
                                </div>
                            </div>
                        </section>

                        <!-- analytics charts -->
                        <div class="gmm-chart-grid">
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Classes Created Overview</h3>
                                        <p>New classes submitted each month.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-ac-created" aria-label="Classes created line chart"></canvas>
                                </div>
                            </section>
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Class Category Distribution</h3>
                                        <p>Classes by music category.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap is-doughnut">
                                    <canvas id="gmm-ac-category" aria-label="Class category doughnut chart"></canvas>
                                </div>
                            </section>
                        </div>

                        <!-- classes table -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>All Classes</h3>
                                    <p>Search, filter, and review teacher-created music classes.</p>
                                </div>
                                <span class="sf-count-pill" id="ac-result-count"><i class="far fa-chalkboard"></i> <strong><?php echo esc_html( (string) $result_total ); ?></strong> Shown</span>
                            </div>

                            <form class="at-filter-bar" id="ac-filter-form" action="" method="get">
                                <?php if ( is_admin() ) : ?>
                                    <input type="hidden" name="page" value="gmm-classes">
                                <?php endif; ?>
                                <div class="at-search-field">
                                    <i class="far fa-search" aria-hidden="true"></i>
                                    <input type="search" class="form-control" id="ac-search" name="ac_search"
                                        value="<?php echo esc_attr( $filter_search ); ?>"
                                        placeholder="Search classes by title or teacher..." autocomplete="off">
                                </div>
                                <div class="at-filter-selects">
                                    <div class="form-group mb-0">
                                        <label for="ac-status" class="visually-hidden">Status</label>
                                        <select class="form-control form-select" id="ac-status" name="ac_status">
                                            <option value="all" <?php selected( $filter_status, 'all' ); ?>>All Status</option>
                                            <option value="pending" <?php selected( $filter_status, 'pending' ); ?>>Pending</option>
                                            <option value="approved" <?php selected( $filter_status, 'approved' ); ?>>Approved</option>
                                            <option value="rejected" <?php selected( $filter_status, 'rejected' ); ?>>Rejected</option>
                                            <option value="draft" <?php selected( $filter_status, 'draft' ); ?>>Draft</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="ac-category" class="visually-hidden">Category</label>
                                        <select class="form-control form-select" id="ac-category" name="ac_category">
                                            <option value="all" <?php selected( $filter_category, 'all' ); ?>>All Categories</option>
                                            <option value="piano" <?php selected( $filter_category, 'piano' ); ?>>Gospel Piano</option>
                                            <option value="vocals" <?php selected( $filter_category, 'vocals' ); ?>>Vocals</option>
                                            <option value="guitar" <?php selected( $filter_category, 'guitar' ); ?>>Guitar</option>
                                            <option value="drums" <?php selected( $filter_category, 'drums' ); ?>>Drums</option>
                                            <option value="theory" <?php selected( $filter_category, 'theory' ); ?>>Music Theory</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="ac-difficulty" class="visually-hidden">Difficulty</label>
                                        <select class="form-control form-select" id="ac-difficulty" name="ac_difficulty">
                                            <option value="all" <?php selected( $filter_difficulty, 'all' ); ?>>All Difficulty</option>
                                            <option value="beginner" <?php selected( $filter_difficulty, 'beginner' ); ?>>Beginner</option>
                                            <option value="intermediate" <?php selected( $filter_difficulty, 'intermediate' ); ?>>Intermediate</option>
                                            <option value="advanced" <?php selected( $filter_difficulty, 'advanced' ); ?>>Advanced</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="theme-btn" id="ac-filter-btn">
                                        <i class="far fa-filter"></i> Filter
                                    </button>
                                </div>
                            </form>

                            <div class="table-responsive td-table-wrap" id="ac-table-wrap" <?php echo empty( $classes ) ? 'hidden' : ''; ?>>
                                <table class="table td-table sb-table at-table">
                                    <thead>
                                        <tr>
                                            <th>Class Image</th>
                                            <th>Class Name</th>
                                            <th>Teacher</th>
                                            <th>Category</th>
                                            <th>Price</th>
                                            <th>Students</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ac-table-body">
                                        <?php if ( empty( $classes ) ) : ?>
                                        <?php else : ?>
                                            <?php foreach ( $classes as $class ) : ?>
                                                <?php
                                                $cid = isset( $class['id'] ) ? absint( $class['id'] ) : 0;
                                                $ctitle = isset( $class['title'] ) ? (string) $class['title'] : '';
                                                $cteacher = isset( $class['teacher'] ) ? (string) $class['teacher'] : '';
                                                $cstatus = isset( $class['status'] ) ? (string) $class['status'] : 'draft';
                                                $ccat = isset( $class['category'] ) ? (string) $class['category'] : 'all';
                                                $ccat_label = isset( $class['category_label'] ) ? (string) $class['category_label'] : '';
                                                $cdiff = isset( $class['difficulty'] ) ? (string) $class['difficulty'] : '';
                                                $cdiff_label = isset( $class['difficulty_label'] ) ? (string) $class['difficulty_label'] : '';
                                                $cprice = isset( $class['price_label'] ) ? (string) $class['price_label'] : '$0';
                                                $cprice_raw = isset( $class['price'] ) ? (string) $class['price'] : '0';
                                                $cstudents = isset( $class['students'] ) ? absint( $class['students'] ) : 0;
                                                $cduration = isset( $class['duration_label'] ) ? (string) $class['duration_label'] : '';
                                                $cduration_mins = isset( $class['duration'] ) ? absint( $class['duration'] ) : 0;
                                                $crating = isset( $class['rating'] ) ? (string) $class['rating'] : '0.0';
                                                $ccreated = isset( $class['created'] ) ? (string) $class['created'] : '';
                                                $cdesc = isset( $class['description'] ) ? (string) $class['description'] : '';
                                                $cimg = isset( $class['image'] ) ? (string) $class['image'] : '';
                                                $cfeatured = ! empty( $class['featured'] ) ? 'true' : 'false';
                                                $cstatus_label = isset( $class['status_label'] ) ? (string) $class['status_label'] : '';
                                                $cstatus_class = isset( $class['status_class'] ) ? (string) $class['status_class'] : 'is-pending';
                                                ?>
                                        <tr class="ac-row"
                                            data-class-id="<?php echo esc_attr( (string) $cid ); ?>"
                                            data-title="<?php echo esc_attr( $ctitle ); ?>"
                                            data-teacher="<?php echo esc_attr( $cteacher ); ?>"
                                            data-status="<?php echo esc_attr( $cstatus ); ?>"
                                            data-category="<?php echo esc_attr( $ccat ); ?>"
                                            data-category-label="<?php echo esc_attr( $ccat_label ); ?>"
                                            data-difficulty="<?php echo esc_attr( $cdiff ); ?>"
                                            data-difficulty-label="<?php echo esc_attr( $cdiff_label ); ?>"
                                            data-price="<?php echo esc_attr( $cprice ); ?>"
                                            data-price-raw="<?php echo esc_attr( $cprice_raw ); ?>"
                                            data-students="<?php echo esc_attr( (string) $cstudents ); ?>"
                                            data-duration="<?php echo esc_attr( $cduration ); ?>"
                                            data-duration-mins="<?php echo esc_attr( (string) $cduration_mins ); ?>"
                                            data-rating="<?php echo esc_attr( $crating ); ?>"
                                            data-created="<?php echo esc_attr( $ccreated ); ?>"
                                            data-description="<?php echo esc_attr( $cdesc ); ?>"
                                            data-image="<?php echo esc_url( $cimg ); ?>"
                                            data-featured="<?php echo esc_attr( $cfeatured ); ?>">
                                            <td data-label="Class Image"><img class="ac-thumb" src="<?php echo esc_url( $cimg ); ?>" alt="<?php echo esc_attr( $ctitle ); ?>"></td>
                                            <td data-label="Class Name"><strong><?php echo esc_html( $ctitle ); ?></strong></td>
                                            <td data-label="Teacher"><?php echo esc_html( $cteacher ); ?></td>
                                            <td data-label="Category"><?php echo esc_html( $ccat_label ); ?></td>
                                            <td data-label="Price"><?php echo esc_html( $cprice ); ?></td>
                                            <td data-label="Students"><?php echo esc_html( (string) $cstudents ); ?> Students</td>
                                            <td data-label="Status"><span class="sb-badge <?php echo esc_attr( $cstatus_class ); ?> ac-status"><?php echo esc_html( $cstatus_label ); ?></span></td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="<?php echo esc_attr( sprintf( 'Actions for %s', $ctitle ) ); ?>">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item ac-view-btn"><i class="far fa-eye"></i> <span>View Class</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item ac-approve-btn"><i class="far fa-circle-check"></i> <span>Approve</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item ac-reject-btn"><i class="far fa-circle-xmark"></i> <span>Reject</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item ac-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item ac-feature-btn"><i class="far fa-star"></i> <span>Feature Class</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item is-logout ac-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>

                                    </tbody>
                                </table>
                            </div>

                            <div class="sl-empty" id="ac-empty" <?php echo empty( $classes ) ? '' : 'hidden'; ?>>
                                <i class="far fa-chalkboard"></i>
                                <h3>No classes found.</h3>
                                <p>Try adjusting your search or filter options.</p>
                            </div>

                            <?php
                            $show_pagination = $total_pages > 1;
                            $prev_disabled   = empty( $pagination['has_prev'] );
                            $next_disabled   = empty( $pagination['has_next'] );
                            $prev_url = ( ! $prev_disabled && ! empty( $pagination['prev_page'] ) && function_exists( 'gmm_admin_classes_page_url' ) )
                                ? gmm_admin_classes_page_url( (int) $pagination['prev_page'], $filters )
                                : '#';
                            $next_url = ( ! $next_disabled && ! empty( $pagination['next_page'] ) && function_exists( 'gmm_admin_classes_page_url' ) )
                                ? gmm_admin_classes_page_url( (int) $pagination['next_page'], $filters )
                                : '#';
                            ?>
                            <nav class="at-pagination" id="ac-pagination" aria-label="Classes pagination" <?php echo $show_pagination ? '' : 'hidden'; ?>>
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item<?php echo $prev_disabled ? ' disabled' : ''; ?>" id="ac-page-prev">
                                        <a class="page-link" href="<?php echo esc_url( $prev_url ); ?>" data-page="prev" aria-label="Previous"><i class="far fa-angle-left"></i> Previous</a>
                                    </li>
                                    <?php
                                    $start_p = max( 1, $current_page - 2 );
                                    $end_p   = min( $total_pages, $start_p + 4 );
                                    $start_p = max( 1, $end_p - 4 );
                                    for ( $p = $start_p; $p <= $end_p; $p++ ) :
                                        $p_url = function_exists( 'gmm_admin_classes_page_url' ) ? gmm_admin_classes_page_url( $p, $filters ) : '#';
                                        ?>
                                    <li class="page-item<?php echo ( $p === $current_page ) ? ' active' : ''; ?>"><a class="page-link" href="<?php echo esc_url( $p_url ); ?>" data-page="<?php echo esc_attr( (string) $p ); ?>"><?php echo esc_html( (string) $p ); ?></a></li>
                                    <?php endfor; ?>
                                    <li class="page-item<?php echo $next_disabled ? ' disabled' : ''; ?>" id="ac-page-next">
                                        <a class="page-link" href="<?php echo esc_url( $next_url ); ?>" data-page="next" aria-label="Next">Next <i class="far fa-angle-right"></i></a>
                                    </li>
                                </ul>
                            </nav>
                        </section>

                        <!-- featured classes -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Featured Classes</h3>
                                    <p>Highlight top classes on the public marketplace.</p>
                                </div>
                            </div>
                            <div class="ac-featured-grid" id="ac-featured-grid">
                                <?php foreach ( $featured_classes as $fclass ) : ?>
                                    <?php
                                    $ftitle = isset( $fclass['title'] ) ? (string) $fclass['title'] : '';
                                    $fteacher = isset( $fclass['teacher'] ) ? (string) $fclass['teacher'] : '';
                                    $fimg = isset( $fclass['image'] ) ? (string) $fclass['image'] : '';
                                    ?>
                                <article class="ac-featured-card">
                                    <img src="<?php echo esc_url( $fimg ); ?>" alt="<?php echo esc_attr( $ftitle ); ?>">
                                    <div class="ac-featured-body">
                                        <h4><?php echo esc_html( $ftitle ); ?></h4>
                                        <p><?php echo esc_html( $fteacher ); ?></p>
                                    </div>
                                </article>
                                <?php endforeach; ?>
                            </div>
                            <div class="sl-empty ac-featured-empty" id="ac-featured-empty" <?php echo empty( $featured_classes ) ? '' : 'hidden'; ?>>
                                <i class="far fa-star"></i>
                                <h3>No featured classes.</h3>
                                <p>Use Feature Class from the action menu to highlight lessons here.</p>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- admin classes end -->

    

<!-- class detail modal -->
    <div class="modal fade gospel-demo-modal" id="ac-class-modal" tabindex="-1" aria-labelledby="ac-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ac-modal-title">Class Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body at-modal-body">
                    <div class="ac-modal-hero">
                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/01.jpg' ) ); ?>" alt="Class" id="ac-modal-image">
                        <div>
                            <h4 id="ac-modal-name">Class Title</h4>
                            <p id="ac-modal-teacher">Teacher Name</p>
                            <span class="td-rating" id="ac-modal-rating">★★★★★ 4.9</span>
                            <span class="sb-badge is-pending" id="ac-modal-status">Pending</span>
                        </div>
                    </div>
                    <ul class="booking-modal-list at-modal-list">
                        <li><span>Category</span><strong id="ac-modal-category">Gospel Piano</strong></li>
                        <li><span>Duration</span><strong id="ac-modal-duration">60 Minutes</strong></li>
                        <li><span>Difficulty</span><strong id="ac-modal-difficulty">Beginner</strong></li>
                        <li><span>Price</span><strong id="ac-modal-price">$40</strong></li>
                        <li><span>Students</span><strong id="ac-modal-students">25</strong></li>
                        <li><span>Created Date</span><strong id="ac-modal-created">Jan 15, 2026</strong></li>
                    </ul>
                    <div class="at-modal-bio">
                        <h5>Description</h5>
                        <p id="ac-modal-description">Class description will appear here.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" id="ac-modal-reject">
                        <i class="far fa-circle-xmark"></i> Reject Class
                    </button>
                    <button type="button" class="theme-btn" id="ac-modal-approve">
                        <i class="far fa-circle-check"></i> Approve Class
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="gospel-alert gospel-alert-success sl-toast" id="ac-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="ac-toast-text">Action completed.</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

