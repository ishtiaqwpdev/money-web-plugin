<?php
/**
 * Template: teacher-dashboard
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $gmm_teacher_pending ) || ! empty( $gmm_teacher_denied ) ) {
	$msg = ! empty( $gmm_teacher_pending )
		? __( 'Your account is waiting for approval.', 'gospel-music-mastery' )
		: __( 'You do not have permission to view this dashboard.', 'gospel-music-mastery' );
	echo '<div class="gmm-wrapper gmm-dashboard"><div class="teacher-dashboard-area py-120"><div class="container"><div class="td-card"><div class="td-card-head"><h3>' . esc_html( $msg ) . '</h3></div></div></div></div></div>';
	return;
}

$stats = ( isset( $stats ) && is_array( $stats ) ) ? $stats : array();
$stats = wp_parse_args(
	$stats,
	array(
		'total_classes'     => 0,
		'active_classes'    => 0,
		'enrolled_classes'  => 0,
		'total_students'    => 0,
		'upcoming_lessons'  => 0,
		'completed_lessons' => 0,
		'total_earnings'    => 0,
		'average_rating'    => 0,
	)
);

$profile_summary  = ( isset( $profile_summary ) && is_array( $profile_summary ) ) ? $profile_summary : array();
$upcoming_lessons = ( isset( $upcoming_lessons ) && is_array( $upcoming_lessons ) ) ? $upcoming_lessons : array();
$recent_classes   = ( isset( $recent_classes ) && is_array( $recent_classes ) ) ? $recent_classes : array();
$earnings_summary = ( isset( $earnings_summary ) && is_array( $earnings_summary ) ) ? $earnings_summary : array();
$completion       = ( isset( $completion ) && is_array( $completion ) ) ? $completion : array( 'percent' => 0, 'items' => array() );
$links            = ( isset( $links ) && is_array( $links ) ) ? $links : array();
$logout_url       = isset( $logout_url ) ? $logout_url : ( function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ) );

if ( ! isset( $user_name ) || '' === $user_name ) {
	$user_name = isset( $profile_summary['name'] ) ? $profile_summary['name'] : 'Guest';
}
if ( ! isset( $user_first_name ) ) {
	$user_first_name = isset( $profile_summary['first_name'] ) && $profile_summary['first_name']
		? $profile_summary['first_name']
		: $user_name;
}

$avatar_url = ! empty( $profile_summary['image_url'] )
	? $profile_summary['image_url']
	: gmm_design_asset_url( 'assets/img/team/01.jpg' );
$role_label = ! empty( $profile_summary['specialization'] )
	? $profile_summary['specialization']
	: 'Gospel Music Instructor';
$rating_val  = isset( $profile_summary['rating'] ) ? (float) $profile_summary['rating'] : (float) $stats['average_rating'];
$rating_disp = $rating_val > 0 ? number_format_i18n( $rating_val, 1 ) : '—';
$exp_label   = ! empty( $profile_summary['experience'] ) ? (string) $profile_summary['experience'] : '';
$pct         = isset( $completion['percent'] ) ? absint( $completion['percent'] ) : 0;
$link_classes  = ! empty( $links['classes'] ) ? $links['classes'] : gmm_get_page_link( 'teacher_classes' );
$link_bookings = ! empty( $links['bookings'] ) ? $links['bookings'] : gmm_get_page_link( 'teacher_bookings' );
$link_avail    = ! empty( $links['availability'] ) ? $links['availability'] : gmm_get_page_link( 'teacher_availability' );
$link_earn     = ! empty( $links['earnings'] ) ? $links['earnings'] : gmm_get_page_link( 'teacher_withdrawals' );
$link_profile  = ! empty( $links['profile'] ) ? $links['profile'] : gmm_get_page_link( 'teacher_profile' );
$link_add      = ! empty( $links['add_class'] ) ? $links['add_class'] : $link_classes;
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
                                <span class="td-stat-item"><i class="far fa-users"></i> <?php echo esc_html( (int) $stats['total_students'] ); ?> Students</span>
                                <span class="td-stat-item"><i class="far fa-books"></i> <?php echo esc_html( (int) $stats['total_classes'] ); ?> Classes</span>
								<?php if ( $exp_label ) : ?>
                                <span class="td-stat-item"><i class="far fa-briefcase"></i> <?php echo esc_html( $exp_label ); ?></span>
								<?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="td-profile-actions">
                        <a href="<?php echo esc_url( $link_add ); ?>" class="theme-btn"><i class="far fa-plus"></i> Create New Class</a>
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_dashboard' ) ); ?>" class="td-nav-link active" data-nav="dashboard"><i class="far fa-grid-2"></i> Dashboard</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_profile' ) ); ?>" class="td-nav-link" data-nav="profile"><i class="far fa-user"></i> My Profile</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_availability' ) ); ?>" class="td-nav-link" data-nav="availability"><i class="far fa-calendar-days"></i> Availability</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_bookings' ) ); ?>" class="td-nav-link" data-nav="bookings"><i class="far fa-calendar-check"></i> My Bookings</a></li>
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

                        <!-- profile completion -->
                        <section class="td-card td-completion-card">
                            <div class="td-card-head">
                                <div>
                                    <h3>Complete Your Profile</h3>
                                    <p>Finish onboarding items to unlock the full instructor experience.</p>
                                </div>
                                <span class="td-progress-label"><?php echo esc_html( (string) $pct ); ?>% Complete</span>
                            </div>
                            <div class="progress-box td-progress-box">
                                <div class="progress">
                                    <div class="progress-bar td-progress-bar-80" role="progressbar" aria-valuenow="<?php echo esc_attr( (string) $pct ); ?>" aria-valuemin="0" aria-valuemax="100" style="width:<?php echo esc_attr( (string) $pct ); ?>%"></div>
                                </div>
                            </div>
                            <ul class="td-checklist">
								<?php if ( ! empty( $completion['items'] ) ) : ?>
									<?php foreach ( $completion['items'] as $item ) : ?>
                                <li class="<?php echo ! empty( $item['done'] ) ? 'is-done' : ''; ?>"><i class="far fa-circle-check"></i> <?php echo esc_html( isset( $item['label'] ) ? $item['label'] : '' ); ?></li>
									<?php endforeach; ?>
								<?php else : ?>
                                <li class="is-done"><i class="far fa-circle-check"></i> Profile Information</li>
                                <li class="is-done"><i class="far fa-circle-check"></i> Profile Photo</li>
                                <li class="is-done"><i class="far fa-circle-check"></i> Intro Video</li>
                                <li class="is-done"><i class="far fa-circle-check"></i> Payment Account</li>
                                <li class="is-done"><i class="far fa-circle-check"></i> First Class</li>
								<?php endif; ?>
                            </ul>
                            <div class="td-card-actions">
                                <a href="<?php echo esc_url( $link_profile ); ?>" class="theme-btn theme-btn-outline"><i class="far fa-user-pen"></i> Edit Profile</a>
                                <a href="<?php echo esc_url( $link_profile ); ?>" class="theme-btn"><i class="far fa-arrow-right"></i> Complete Setup</a>
                            </div>
							<?php if ( ! empty( $earnings_summary ) ) : ?>
                            <p class="td-booking-meta">
                                <?php
								echo esc_html(
									sprintf(
										__( 'Earnings — Total: $%1$s · Pending: $%2$s · Available: $%3$s', 'gospel-music-mastery' ),
										number_format_i18n( (float) ( isset( $earnings_summary['total_earnings'] ) ? $earnings_summary['total_earnings'] : 0 ), 2 ),
										number_format_i18n( (float) ( isset( $earnings_summary['pending_earnings'] ) ? $earnings_summary['pending_earnings'] : 0 ), 2 ),
										number_format_i18n( (float) ( isset( $earnings_summary['available_balance'] ) ? $earnings_summary['available_balance'] : ( isset( $earnings_summary['paid_earnings'] ) ? $earnings_summary['paid_earnings'] : 0 ) ), 2 )
									)
								);
								?>
                            </p>
							<?php endif; ?>
                        </section>

                        <!-- stats -->
                        <section class="td-stats-grid">
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-book-open-reader"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value counter" data-count="<?php echo esc_attr( (string) (int) $stats['enrolled_classes'] ); ?>">0</span>
                                    <span class="td-stat-title">Enrolled Classes</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-bolt"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value counter" data-count="<?php echo esc_attr( (string) (int) $stats['active_classes'] ); ?>">0</span>
                                    <span class="td-stat-title">Active Classes</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value counter" data-count="<?php echo esc_attr( (string) (int) $stats['completed_lessons'] ); ?>">0</span>
                                    <span class="td-stat-title">Completed Lessons</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-users"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value counter" data-count="<?php echo esc_attr( (string) (int) $stats['total_students'] ); ?>">0</span>
                                    <span class="td-stat-title">Total Students</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-chalkboard-user"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value counter" data-count="<?php echo esc_attr( (string) (int) $stats['total_classes'] ); ?>">0</span>
                                    <span class="td-stat-title">Total Classes</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-dollar-sign"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value">$<span class="counter" data-count="<?php echo esc_attr( (string) (int) round( (float) $stats['total_earnings'] ) ); ?>">0</span></span>
                                    <span class="td-stat-title">Total Earnings</span>
                                </div>
                            </div>
                        </section>

                        <!-- analytics charts -->
                        <div class="gmm-chart-grid is-triple">
                            <section class="td-card gmm-chart-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Earnings Overview</h3>
                                        <p>Your monthly earnings for the year.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-teacher-earnings" aria-label="Teacher earnings line chart"></canvas>
                                </div>
                            </section>
                            <section class="td-card gmm-chart-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Lesson Statistics</h3>
                                        <p>Completed, upcoming, and cancelled lessons.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap is-doughnut">
                                    <canvas id="gmm-teacher-lessons" aria-label="Lesson statistics doughnut chart"></canvas>
                                </div>
                            </section>
                            <section class="td-card gmm-chart-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Student Growth</h3>
                                        <p>New students joining your classes each month.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-teacher-students" aria-label="Student growth bar chart"></canvas>
                                </div>
                            </section>
                        </div>

                        <!-- recent classes -->
                        <section class="td-card">
                            <div class="td-card-head">
                                <h3>My Recent Classes</h3>
                                <a href="<?php echo esc_url( $link_classes ); ?>" class="td-link">View All</a>
                            </div>
                            <div class="table-responsive td-table-wrap">
                                <table class="table td-table">
                                    <thead>
                                        <tr>
                                            <th>Class Name</th>
                                            <th>Category</th>
                                            <th>Students</th>
                                            <th>Rating</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
										<?php if ( empty( $recent_classes ) ) : ?>
                                        <tr>
                                            <td colspan="6" data-label="Class Name"><?php esc_html_e( 'No classes yet. Create your first class to get started.', 'gospel-music-mastery' ); ?></td>
                                        </tr>
										<?php else : ?>
											<?php foreach ( $recent_classes as $class_row ) : ?>
                                        <tr>
                                            <td data-label="Class Name"><?php echo esc_html( isset( $class_row['title'] ) ? $class_row['title'] : '' ); ?></td>
                                            <td data-label="Category"><?php echo esc_html( isset( $class_row['category'] ) ? $class_row['category'] : '—' ); ?></td>
                                            <td data-label="Students"><?php echo esc_html( (int) ( isset( $class_row['student_count'] ) ? $class_row['student_count'] : 0 ) ); ?> Students</td>
                                            <td data-label="Rating" class="td-rating"><?php echo esc_html( isset( $class_row['rating_stars'] ) ? $class_row['rating_stars'] : '—' ); ?></td>
                                            <td data-label="Status"><span class="td-badge <?php echo esc_attr( isset( $class_row['status_badge'] ) ? $class_row['status_badge'] : 'is-draft' ); ?>"><?php echo esc_html( isset( $class_row['status_label'] ) ? $class_row['status_label'] : '' ); ?></span></td>
                                            <td data-label="Action">
                                                <div class="dropdown td-action-dropdown">
                                                    <button class="theme-btn theme-btn-outline td-action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="<?php echo esc_url( $link_classes ); ?>">View</a></li>
                                                        <li><a class="dropdown-item" href="<?php echo esc_url( $link_classes ); ?>">Edit</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
											<?php endforeach; ?>
										<?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <div class="row">
                            <div class="col-lg-7">
                                <!-- upcoming bookings -->
                                <section class="td-card">
                                    <div class="td-card-head">
                                        <h3>Upcoming Bookings</h3>
                                        <a href="<?php echo esc_url( $link_bookings ); ?>" class="td-link">View All</a>
                                    </div>
                                    <div class="td-booking-list" id="td-booking-list" <?php echo empty( $upcoming_lessons ) ? 'hidden' : ''; ?>>
										<?php foreach ( $upcoming_lessons as $lesson ) : ?>
                                        <article class="td-booking-card">
                                            <div class="td-booking-info">
                                                <h4><?php echo esc_html( isset( $lesson['student_name'] ) ? $lesson['student_name'] : '' ); ?></h4>
                                                <p><?php echo esc_html( isset( $lesson['class_name'] ) ? $lesson['class_name'] : '' ); ?></p>
                                                <span class="td-booking-meta"><i class="far fa-calendar"></i> <?php echo esc_html( isset( $lesson['meta'] ) ? $lesson['meta'] : '' ); ?></span>
                                            </div>
                                            <span class="td-badge <?php echo esc_attr( isset( $lesson['status_badge'] ) ? $lesson['status_badge'] : 'is-pending' ); ?>"><?php echo esc_html( isset( $lesson['status_label'] ) ? $lesson['status_label'] : '' ); ?></span>
                                        </article>
										<?php endforeach; ?>
                                    </div>
                                    <p class="td-empty-state" id="td-bookings-empty" <?php echo empty( $upcoming_lessons ) ? '' : 'hidden'; ?>>No upcoming lessons scheduled yet.</p>
                                </section>
                            </div>
                            <div class="col-lg-5">
                                <!-- quick actions -->
                                <section class="td-card">
                                    <div class="td-card-head">
                                        <h3>Quick Actions</h3>
                                    </div>
                                    <div class="td-quick-grid">
                                        <a href="<?php echo esc_url( $link_profile ); ?>" class="td-quick-card">
                                            <i class="far fa-user-pen"></i>
                                            <span>Edit Profile</span>
                                        </a>
                                        <a href="<?php echo esc_url( $link_add ); ?>" class="td-quick-card">
                                            <i class="far fa-plus"></i>
                                            <span>Add New Class</span>
                                        </a>
                                        <a href="<?php echo esc_url( $link_avail ); ?>" class="td-quick-card">
                                            <i class="far fa-calendar-days"></i>
                                            <span>Update Availability</span>
                                        </a>
                                        <a href="<?php echo esc_url( $link_earn ); ?>" class="td-quick-card">
                                            <i class="far fa-chart-line"></i>
                                            <span>View Earnings</span>
                                        </a>
                                    </div>
                                </section>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- teacher dashboard end -->

    
</div><!-- .gmm-wrapper -->
