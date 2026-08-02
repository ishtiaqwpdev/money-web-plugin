<?php
/**
 * Template: student-dashboard
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $gmm_student_denied ) ) {
	$msg = __( 'Please sign in with a student account to view this dashboard.', 'gospel-music-mastery' );
	echo '<div class="gmm-wrapper gmm-dashboard"><div class="student-dashboard-area py-120"><div class="container"><div class="sd-card"><div class="sd-card-head"><h3>' . esc_html( $msg ) . '</h3></div></div></div></div></div>';
	return;
}

$stats = ( isset( $stats ) && is_array( $stats ) ) ? $stats : array();
$stats = wp_parse_args(
	$stats,
	array(
		'total_lessons'      => 0,
		'enrolled_classes'   => 0,
		'upcoming_lessons'   => 0,
		'completed_lessons'  => 0,
		'favourite_teachers' => 0,
		'total_payments'     => 0,
		'pending_payments'   => 0,
	)
);

$profile_summary     = ( isset( $profile_summary ) && is_array( $profile_summary ) ) ? $profile_summary : array();
$upcoming_lessons    = ( isset( $upcoming_lessons ) && is_array( $upcoming_lessons ) ) ? $upcoming_lessons : array();
$recent_lessons      = ( isset( $recent_lessons ) && is_array( $recent_lessons ) ) ? $recent_lessons : array();
$favourite_teachers  = ( isset( $favourite_teachers ) && is_array( $favourite_teachers ) ) ? $favourite_teachers : array();
$payment_summary     = ( isset( $payment_summary ) && is_array( $payment_summary ) ) ? $payment_summary : array();
$activity            = ( isset( $activity ) && is_array( $activity ) ) ? $activity : array();
$completion          = ( isset( $completion ) && is_array( $completion ) ) ? $completion : array( 'percent' => 0, 'items' => array() );
$links               = ( isset( $links ) && is_array( $links ) ) ? $links : array();
$logout_url          = isset( $logout_url ) ? $logout_url : ( function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ) );

if ( ! isset( $user_name ) || '' === $user_name ) {
	$user_name = isset( $profile_summary['name'] ) ? $profile_summary['name'] : 'Guest';
}
if ( ! isset( $user_first_name ) || '' === $user_first_name ) {
	$user_first_name = isset( $profile_summary['first_name'] ) && $profile_summary['first_name']
		? $profile_summary['first_name']
		: $user_name;
}

$avatar_url = ! empty( $profile_summary['image_url'] )
	? $profile_summary['image_url']
	: gmm_design_asset_url( 'assets/img/team/02.jpg' );
$level_label = ! empty( $profile_summary['learning_level'] )
	? (string) $profile_summary['learning_level']
	: __( 'Not set', 'gospel-music-mastery' );
$instrument_label = ! empty( $profile_summary['instruments'] )
	? (string) $profile_summary['instruments']
	: __( 'Gospel Music', 'gospel-music-mastery' );
$pct = isset( $completion['percent'] ) ? absint( $completion['percent'] ) : 0;

$link_teachers  = ! empty( $links['teachers'] ) ? $links['teachers'] : gmm_get_page_link( 'student_favourites' );
$link_lessons   = ! empty( $links['lessons'] ) ? $links['lessons'] : gmm_get_page_link( 'student_lessons' );
$link_bookings  = ! empty( $links['bookings'] ) ? $links['bookings'] : gmm_get_page_link( 'student_bookings' );
$link_profile   = ! empty( $links['profile'] ) ? $links['profile'] : gmm_get_page_link( 'student_profile' );
$link_payments  = ! empty( $links['payments'] ) ? $links['payments'] : gmm_get_page_link( 'student_payments' );
$link_favourites = ! empty( $links['favourites'] ) ? $links['favourites'] : gmm_get_page_link( 'student_favourites' );
$link_book      = ! empty( $links['book'] ) ? $links['book'] : $link_bookings;

$total_paid_label = isset( $payment_summary['total_paid'] )
	? '$' . number_format_i18n( (float) $payment_summary['total_paid'], 2 )
	: '$0.00';
?>
<div class="gmm-wrapper gmm-dashboard">

        <!-- student dashboard -->
        <div class="student-dashboard-area py-120">
            <div class="container">

                <!-- dashboard profile header -->
                <div class="sd-profile-header">
                    <div class="sd-profile-main">
                        <div class="sd-profile-avatar">
                            <img src="<?php echo esc_url( $avatar_url ); ?>" alt="<?php echo esc_attr( $user_name ); ?>">
                        </div>
                        <div class="sd-profile-meta">
                            <h2><?php echo esc_html( $user_name ); ?></h2>
                            <span class="sd-role">Music Student</span>
                            <div class="sd-profile-stats">
                                <span class="sd-stat-item"><i class="far fa-signal"></i> Learning Level: <?php echo esc_html( $level_label ); ?></span>
                                <span class="sd-stat-item"><i class="far fa-music"></i> <?php echo esc_html( $instrument_label ); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="sd-profile-actions">
                        <a href="<?php echo esc_url( $link_teachers ); ?>" class="theme-btn"><i class="far fa-search"></i> Find New Teachers</a>
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_dashboard' ) ); ?>" class="sd-nav-link active" data-nav="dashboard"><i class="far fa-grid-2"></i> Dashboard</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_profile' ) ); ?>" class="sd-nav-link" data-nav="profile"><i class="far fa-user"></i> My Profile</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_lessons' ) ); ?>" class="sd-nav-link" data-nav="lessons"><i class="far fa-book-open"></i> My Lessons</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_bookings' ) ); ?>" class="sd-nav-link" data-nav="bookings"><i class="far fa-calendar-check"></i> My Bookings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_dashboard' ) ); ?>" class="sd-nav-link" data-nav="messages"><i class="far fa-comments"></i> Messages</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_favourites' ) ); ?>" class="sd-nav-link" data-nav="favourites"><i class="far fa-heart"></i> Favourite Teachers</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_payments' ) ); ?>" class="sd-nav-link" data-nav="payments"><i class="far fa-credit-card"></i> Payments</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_settings' ) ); ?>" class="sd-nav-link" data-nav="settings"><i class="far fa-gear"></i> Settings</a></li>
                                <li><a href="<?php echo esc_url( $logout_url ); ?>" class="sd-nav-link sd-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
                            </ul>
                        </nav>
                    </aside>
                    <div class="sd-sidebar-backdrop" id="sd-sidebar-backdrop" hidden></div>

                    <!-- main content -->
                    <div class="sd-main">

                        <!-- welcome card -->
                        <section class="sd-card sd-welcome-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Welcome Back, <?php echo esc_html( $user_first_name ); ?>!</h3>
                                    <p>Continue your gospel music journey and manage your upcoming lessons.<?php
									if ( ! empty( $profile_summary['learning_goals'] ) ) {
										echo ' ' . esc_html( wp_trim_words( (string) $profile_summary['learning_goals'], 18 ) );
									}
									?></p>
                                </div>
                                <a href="<?php echo esc_url( $link_teachers ); ?>" class="theme-btn"><i class="far fa-users"></i> Browse Teachers</a>
                            </div>
                        </section>

                        <!-- profile completion -->
                        <section class="sd-card sd-completion-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Complete Your Profile</h3>
                                    <p>Finish a few details to get better teacher recommendations.</p>
                                </div>
                                <span class="sd-progress-label"><?php echo esc_html( (string) $pct ); ?>% Complete</span>
                            </div>
                            <div class="progress-box sd-progress-box">
                                <div class="progress">
                                    <div class="progress-bar sd-progress-bar-75" role="progressbar" aria-valuenow="<?php echo esc_attr( (string) $pct ); ?>"
                                        aria-valuemin="0" aria-valuemax="100" style="width:<?php echo esc_attr( (string) $pct ); ?>%"></div>
                                </div>
                            </div>
                            <ul class="sd-checklist">
								<?php if ( ! empty( $completion['items'] ) ) : ?>
									<?php foreach ( $completion['items'] as $item ) : ?>
                                <li class="<?php echo ! empty( $item['done'] ) ? 'is-done' : ''; ?>"><i class="far fa-circle-check"></i> <?php echo esc_html( isset( $item['label'] ) ? $item['label'] : '' ); ?></li>
									<?php endforeach; ?>
								<?php else : ?>
                                <li class="is-done"><i class="far fa-circle-check"></i> Personal Information</li>
                                <li class="is-done"><i class="far fa-circle-check"></i> Profile Photo</li>
                                <li class="is-done"><i class="far fa-circle-check"></i> Learning Goals</li>
                                <li class="is-done"><i class="far fa-circle-check"></i> Preferred Instruments</li>
								<?php endif; ?>
                            </ul>
                            <div class="sd-card-actions">
                                <a href="<?php echo esc_url( $link_profile ); ?>" class="theme-btn"><i class="far fa-user-pen"></i> Update Profile</a>
                            </div>
                        </section>

                        <!-- stats -->
                        <section class="sd-stats-grid">
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-chalkboard"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="<?php echo esc_attr( (string) (int) $stats['enrolled_classes'] ); ?>">0</span>
                                    <span class="sd-stat-title">Enrolled Classes</span>
                                </div>
                            </div>
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-calendar-days"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="<?php echo esc_attr( (string) (int) $stats['upcoming_lessons'] ); ?>">0</span>
                                    <span class="sd-stat-title">Upcoming Lessons</span>
                                </div>
                            </div>
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="<?php echo esc_attr( (string) (int) $stats['completed_lessons'] ); ?>">0</span>
                                    <span class="sd-stat-title">Completed Lessons</span>
                                </div>
                            </div>
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-heart"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="<?php echo esc_attr( (string) (int) $stats['favourite_teachers'] ); ?>">0</span>
                                    <span class="sd-stat-title">Favourite Teachers</span>
                                </div>
                            </div>
                        </section>

                        <!-- analytics charts -->
                        <div class="gmm-chart-grid is-triple">
                            <section class="sd-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Learning Progress</h3>
                                        <p>Your monthly learning activity.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-student-learning" aria-label="Learning progress line chart"></canvas>
                                </div>
                            </section>
                            <section class="sd-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Lesson Status</h3>
                                        <p>Completed, upcoming, and remaining lessons.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap is-doughnut">
                                    <canvas id="gmm-student-lesson-status" aria-label="Lesson status doughnut chart"></canvas>
                                </div>
                            </section>
                            <section class="sd-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Practice Hours</h3>
                                        <p>Weekly practice hours this week.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-student-practice" aria-label="Practice hours bar chart"></canvas>
                                </div>
                            </section>
                        </div>

                        <!-- upcoming lessons -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <h3>Upcoming Lessons</h3>
                                <a href="<?php echo esc_url( $link_lessons ); ?>" class="sd-link">View All</a>
                            </div>
                            <div class="sd-lesson-list">
<?php if ( ! empty( $upcoming_lessons ) ) : ?>
<?php foreach ( $upcoming_lessons as $lesson ) : ?>
                                <article class="sd-lesson-card">
                                    <div class="sd-lesson-teacher">
                                        <img src="<?php echo esc_url( isset( $lesson['teacher_image'] ) ? $lesson['teacher_image'] : gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="<?php echo esc_attr( isset( $lesson['teacher_name'] ) ? $lesson['teacher_name'] : '' ); ?>">
                                        <div>
                                            <h4><?php echo esc_html( isset( $lesson['teacher_name'] ) ? $lesson['teacher_name'] : '' ); ?></h4>
                                            <p><?php echo esc_html( isset( $lesson['class_name'] ) ? $lesson['class_name'] : '' ); ?></p>
                                        </div>
                                    </div>
                                    <div class="sd-lesson-meta">
                                        <span><i class="far fa-calendar"></i> <?php echo esc_html( isset( $lesson['date_label'] ) ? $lesson['date_label'] : '' ); ?></span>
                                        <span><i class="far fa-clock"></i> <?php echo esc_html( isset( $lesson['time_label'] ) ? $lesson['time_label'] : '' ); ?></span>
                                        <span class="sd-badge <?php echo esc_attr( isset( $lesson['badge_class'] ) ? $lesson['badge_class'] : 'is-pending' ); ?>"><?php echo esc_html( isset( $lesson['status_label'] ) ? $lesson['status_label'] : '' ); ?></span>
                                    </div>
                                    <div class="sd-lesson-actions">
                                        <div class="dropdown">
                                            <button class="theme-btn theme-btn-outline sd-action-btn dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">View Lesson</button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="<?php echo esc_url( $link_lessons ); ?>">Lesson Details</a></li>
                                                <li><a class="dropdown-item" href="<?php echo esc_url( $link_bookings ); ?>">Reschedule</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </article>
<?php endforeach; ?>
<?php else : ?>
                                <p class="td-empty-state mb-0"><?php esc_html_e( 'No upcoming lessons yet.', 'gospel-music-mastery' ); ?></p>
<?php endif; ?>
                            </div>
                        </section>

                        <div class="row g-4">
                            <div class="col-lg-7">
                                <!-- recommended teachers / favourites -->
                                <section class="sd-card">
                                    <div class="sd-card-head">
                                        <h3>Recommended Teachers</h3>
                                        <a href="<?php echo esc_url( $link_favourites ); ?>" class="sd-link">Browse All</a>
                                    </div>
                                    <div class="sd-teacher-grid">
<?php if ( ! empty( $favourite_teachers ) ) : ?>
<?php foreach ( $favourite_teachers as $teacher ) : ?>
                                        <article class="sd-teacher-card">
                                            <img src="<?php echo esc_url( isset( $teacher['image_url'] ) ? $teacher['image_url'] : gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="<?php echo esc_attr( isset( $teacher['name'] ) ? $teacher['name'] : '' ); ?>">
                                            <div class="sd-teacher-body">
                                                <h4><?php echo esc_html( isset( $teacher['name'] ) ? $teacher['name'] : '' ); ?></h4>
                                                <p><?php echo esc_html( isset( $teacher['specialization'] ) ? $teacher['specialization'] : '' ); ?></p>
                                                <span class="sd-rating"><?php echo esc_html( isset( $teacher['rating_stars'] ) ? $teacher['rating_stars'] : '★★★★☆' ); ?></span>
												<?php if ( ! empty( $teacher['price_label'] ) ) : ?>
                                                <strong class="sd-price"><?php echo esc_html( $teacher['price_label'] ); ?></strong>
												<?php else : ?>
                                                <strong class="sd-price"><?php esc_html_e( 'Favourite', 'gospel-music-mastery' ); ?></strong>
												<?php endif; ?>
                                                <a href="<?php echo esc_url( ! empty( $teacher['profile_url'] ) ? $teacher['profile_url'] : $link_favourites ); ?>" class="theme-btn theme-btn-outline sd-action-btn">View Profile</a>
                                            </div>
                                        </article>
<?php endforeach; ?>
<?php else : ?>
                                        <p class="td-empty-state mb-0"><?php esc_html_e( 'No favourite teachers yet. Browse and save instructors you like.', 'gospel-music-mastery' ); ?></p>
<?php endif; ?>
                                    </div>
                                </section>
                            </div>
                            <div class="col-lg-5">
                                <!-- recent activity -->
                                <section class="sd-card">
                                    <div class="sd-card-head">
                                        <h3>Recent Activity</h3>
                                    </div>
                                    <ul class="sd-activity-list">
<?php if ( ! empty( $activity ) ) : ?>
<?php foreach ( $activity as $item ) : ?>
                                        <li>
                                            <span class="sd-activity-icon"><i class="<?php echo esc_attr( isset( $item['icon'] ) ? $item['icon'] : 'far fa-circle' ); ?>"></i></span>
                                            <div>
                                                <strong><?php echo esc_html( isset( $item['title'] ) ? $item['title'] : '' ); ?></strong>
                                                <span><?php echo esc_html( isset( $item['subtitle'] ) ? $item['subtitle'] : '' ); ?></span>
                                            </div>
                                        </li>
<?php endforeach; ?>
<?php elseif ( ! empty( $recent_lessons ) ) : ?>
<?php foreach ( $recent_lessons as $lesson ) : ?>
                                        <li>
                                            <span class="sd-activity-icon"><i class="far fa-circle-check"></i></span>
                                            <div>
                                                <strong><?php echo esc_html( sprintf( __( 'Completed %s', 'gospel-music-mastery' ), isset( $lesson['class_name'] ) ? $lesson['class_name'] : __( 'lesson', 'gospel-music-mastery' ) ) ); ?></strong>
                                                <span><?php echo esc_html( ( isset( $lesson['teacher_name'] ) ? $lesson['teacher_name'] : '' ) . ( ! empty( $lesson['date_label'] ) ? ' · ' . $lesson['date_label'] : '' ) ); ?></span>
                                            </div>
                                        </li>
<?php endforeach; ?>
<?php else : ?>
                                        <li>
                                            <span class="sd-activity-icon"><i class="far fa-info-circle"></i></span>
                                            <div>
                                                <strong><?php esc_html_e( 'No recent activity yet', 'gospel-music-mastery' ); ?></strong>
                                                <span><?php echo esc_html( sprintf( __( 'Total paid: %s', 'gospel-music-mastery' ), $total_paid_label ) ); ?></span>
                                            </div>
                                        </li>
<?php endif; ?>
                                    </ul>
                                </section>
                            </div>
                        </div>

                        <!-- quick actions -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <h3>Quick Actions</h3>
                            </div>
                            <div class="sd-quick-grid">
                                <a href="<?php echo esc_url( $link_teachers ); ?>" class="sd-quick-card">
                                    <i class="far fa-users"></i>
                                    <span>Browse Teachers</span>
                                </a>
                                <a href="<?php echo esc_url( $link_lessons ); ?>" class="sd-quick-card">
                                    <i class="far fa-book-open"></i>
                                    <span>My Lessons</span>
                                </a>
                                <a href="<?php echo esc_url( $link_book ); ?>" class="sd-quick-card">
                                    <i class="far fa-calendar-check"></i>
                                    <span>My Bookings</span>
                                </a>
                                <a href="<?php echo esc_url( $link_profile ); ?>" class="sd-quick-card">
                                    <i class="far fa-user-pen"></i>
                                    <span>Update Profile</span>
                                </a>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- student dashboard end -->

    
</div><!-- .gmm-wrapper -->
