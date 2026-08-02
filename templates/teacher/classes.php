<?php
/**
 * Template: teacher-classes
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $gmm_teacher_pending ) || ! empty( $gmm_teacher_denied ) ) {
	$msg = ! empty( $gmm_teacher_pending )
		? __( 'Your account is waiting for approval.', 'gospel-music-mastery' )
		: __( 'You do not have permission to manage classes.', 'gospel-music-mastery' );
	echo '<div class="gmm-wrapper gmm-dashboard"><div class="teacher-dashboard-area py-120"><div class="container"><div class="td-card"><div class="td-card-head"><h3>' . esc_html( $msg ) . '</h3></div></div></div></div></div>';
	return;
}

$class_cards = ( isset( $class_cards ) && is_array( $class_cards ) ) ? $class_cards : array();
$class_stats = ( isset( $class_stats ) && is_array( $class_stats ) ) ? $class_stats : array(
	'total'     => 0,
	'published' => 0,
	'pending'   => 0,
	'drafts'    => 0,
);
$class_filters = ( isset( $class_filters ) && is_array( $class_filters ) ) ? $class_filters : array(
	'search'   => '',
	'status'   => 'all',
	'category' => '',
);
$profile_summary = ( isset( $profile_summary ) && is_array( $profile_summary ) ) ? $profile_summary : array();
$profile_stats   = ( isset( $profile_stats ) && is_array( $profile_stats ) ) ? $profile_stats : array(
	'rating'   => 0,
	'students' => 0,
	'classes'  => 0,
);
$logout_url = isset( $logout_url ) ? $logout_url : ( function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ) );

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
$has_cards   = ! empty( $class_cards );
?>
<div class="gmm-wrapper gmm-dashboard" data-gmm-class-search="<?php echo esc_attr( isset( $class_filters['search'] ) ? $class_filters['search'] : '' ); ?>" data-gmm-class-category="<?php echo esc_attr( isset( $class_filters['category'] ) ? $class_filters['category'] : '' ); ?>">

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
                                <span class="td-stat-item"><i class="far fa-users"></i> <?php echo esc_html( (int) $profile_stats['students'] ); ?> Students</span>
                                <span class="td-stat-item"><i class="far fa-books"></i> <?php echo esc_html( (int) $class_stats['total'] ); ?> Classes</span>
                            </div>
                        </div>
                    </div>
                    <div class="td-profile-actions">
                        <a href="#class-form-modal" class="theme-btn" data-bs-toggle="modal" data-gmm-class-action="create"><i class="far fa-plus"></i> Create New Class</a>
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_bookings' ) ); ?>" class="td-nav-link" data-nav="bookings"><i class="far fa-calendar-check"></i> My Bookings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>" class="td-nav-link active" data-nav="classes"><i class="far fa-chalkboard"></i> My Classes</a></li>
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
                                    <span class="login-portal-badge">Class Management</span>
                                    <h3>My Music Classes</h3>
                                    <p>Create, manage, and update your gospel music lessons from one place.</p>
                                </div>
                                <a href="#class-form-modal" class="theme-btn" data-bs-toggle="modal" data-gmm-class-action="create"><i class="far fa-plus"></i> Create New Class</a>
                            </div>
                        </section>

                        <!-- class statistics -->
                        <section class="td-stats-grid bookings-summary-grid">
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-chalkboard"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value" data-gmm-stat="total"><?php echo esc_html( (int) $class_stats['total'] ); ?></span>
                                    <span class="td-stat-title">Total Classes</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value" data-gmm-stat="published"><?php echo esc_html( (int) $class_stats['published'] ); ?></span>
                                    <span class="td-stat-title">Published</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value" data-gmm-stat="pending"><?php echo esc_html( (int) $class_stats['pending'] ); ?></span>
                                    <span class="td-stat-title">Pending Review</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-file-lines"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value" data-gmm-stat="drafts"><?php echo esc_html( (int) $class_stats['drafts'] ); ?></span>
                                    <span class="td-stat-title">Drafts</span>
                                </div>
                            </div>
                        </section>

                        <!-- classes grid -->
                        <section class="td-card">
                            <div class="td-card-head">
                                <div>
                                    <h3>Classes</h3>
                                    <p>Filter by status and manage each lesson card.</p>
                                </div>
                            </div>

                            <div class="booking-tabs class-tabs" role="tablist" aria-label="Class status filters">
                                <button type="button" class="booking-tab class-tab<?php echo ( empty( $class_filters['status'] ) || 'all' === $class_filters['status'] ) ? ' active' : ''; ?>" data-filter="all" role="tab" aria-selected="<?php echo ( empty( $class_filters['status'] ) || 'all' === $class_filters['status'] ) ? 'true' : 'false'; ?>">All Classes</button>
                                <button type="button" class="booking-tab class-tab<?php echo ( isset( $class_filters['status'] ) && 'published' === $class_filters['status'] ) ? ' active' : ''; ?>" data-filter="published" role="tab" aria-selected="<?php echo ( isset( $class_filters['status'] ) && 'published' === $class_filters['status'] ) ? 'true' : 'false'; ?>">Published</button>
                                <button type="button" class="booking-tab class-tab<?php echo ( isset( $class_filters['status'] ) && 'pending' === $class_filters['status'] ) ? ' active' : ''; ?>" data-filter="pending" role="tab" aria-selected="<?php echo ( isset( $class_filters['status'] ) && 'pending' === $class_filters['status'] ) ? 'true' : 'false'; ?>">Pending</button>
                                <button type="button" class="booking-tab class-tab<?php echo ( isset( $class_filters['status'] ) && 'draft' === $class_filters['status'] ) ? ' active' : ''; ?>" data-filter="draft" role="tab" aria-selected="<?php echo ( isset( $class_filters['status'] ) && 'draft' === $class_filters['status'] ) ? 'true' : 'false'; ?>">Draft</button>
                                <button type="button" class="booking-tab class-tab<?php echo ( isset( $class_filters['status'] ) && 'scheduled' === $class_filters['status'] ) ? ' active' : ''; ?>" data-filter="scheduled" role="tab" aria-selected="<?php echo ( isset( $class_filters['status'] ) && 'scheduled' === $class_filters['status'] ) ? 'true' : 'false'; ?>">Scheduled</button>
                            </div>

                            <div class="class-cards-grid" id="class-cards-grid">

<?php if ( $has_cards ) : ?>
	<?php foreach ( $class_cards as $card ) : ?>
                                <article class="class-manage-card" data-class-id="<?php echo esc_attr( (string) $card['id'] ); ?>" data-status="<?php echo esc_attr( $card['ui_status'] ); ?>" data-category="<?php echo esc_attr( $card['category'] ); ?>" data-title="<?php echo esc_attr( $card['title'] ); ?>" data-view-text="<?php echo esc_attr( $card['view_text'] ); ?>">
                                    <div class="class-manage-media">
                                        <img src="<?php echo esc_url( $card['image_url'] ); ?>" alt="<?php echo esc_attr( $card['title'] ); ?>">
                                        <span class="td-badge <?php echo esc_attr( $card['badge_class'] ); ?> class-manage-badge"><?php echo esc_html( $card['status_label'] ); ?></span>
                                    </div>
                                    <div class="class-manage-body">
                                        <span class="class-manage-category"><?php echo esc_html( $card['category'] ); ?></span>
                                        <h4><?php echo esc_html( $card['title'] ); ?></h4>
                                        <ul class="class-manage-meta">
                                            <li><i class="far fa-clock"></i> <?php echo esc_html( $card['duration_label'] ); ?></li>
                                            <li><i class="far fa-dollar-sign"></i> <?php echo esc_html( $card['price_label'] ); ?></li>
                                            <li><i class="far fa-users"></i> <?php echo esc_html( $card['students_label'] ); ?></li>
                                        </ul>
                                        <div class="class-manage-footer">
                                            <span class="td-rating"><?php echo esc_html( $card['rating_display'] ); ?></span>
                                            <div class="dropdown">
                                                <button class="class-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Class actions">
                                                    <i class="far fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><button type="button" class="dropdown-item class-view-btn">View</button></li>
                                                    <li><button type="button" class="dropdown-item class-edit-btn" data-gmm-class-action="edit">Edit</button></li>
                                                    <li><button type="button" class="dropdown-item class-duplicate-btn">Duplicate</button></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><button type="button" class="dropdown-item text-danger class-delete-btn">Delete</button></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </article>
	<?php endforeach; ?>
<?php endif; ?>

                            </div>

                            <p class="td-empty-state" id="classes-empty"<?php echo $has_cards ? ' hidden' : ''; ?>>
                                No classes created yet.<br>
                                Create your first gospel music lesson and start teaching students.
                            </p>
                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- teacher dashboard end -->

    

<!-- delete confirmation modal -->
    <div class="modal fade gospel-demo-modal" id="class-delete-modal" tabindex="-1" aria-labelledby="class-delete-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="class-delete-title">Delete Class</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to delete <strong id="class-delete-name">this class</strong>? This will remove it from your list and hide it from public listings.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="theme-btn" id="class-delete-confirm">Delete Class</button>
                </div>
            </div>
        </div>
    </div>

    <!-- view class modal -->
    <div class="modal fade gospel-demo-modal" id="class-view-modal" tabindex="-1" aria-labelledby="class-view-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="class-view-title">Class Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p id="class-view-text">Class details preview.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="theme-btn" id="class-view-edit" data-gmm-class-action="edit">Edit Class</button>
                </div>
            </div>
        </div>
    </div>

<!-- js -->
</div><!-- .gmm-wrapper -->
