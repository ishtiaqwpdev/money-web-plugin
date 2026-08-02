<?php
/**
 * Template: student-dashboard
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
?>
<div class="gmm-wrapper gmm-dashboard">

        <!-- student dashboard -->
        <div class="student-dashboard-area py-120">
            <div class="container">

                <!-- dashboard profile header -->
                <div class="sd-profile-header">
                    <div class="sd-profile-main">
                        <div class="sd-profile-avatar">
                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="<?php echo esc_attr( $user_name ); ?>">
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
                        <a href="teachers.html" class="theme-btn"><i class="far fa-search"></i> Find New Teachers</a>
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_login' ) ); ?>" class="sd-nav-link sd-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
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
                                    <p>Continue your gospel music journey and manage your upcoming lessons.</p>
                                </div>
                                <a href="teachers.html" class="theme-btn"><i class="far fa-users"></i> Browse Teachers</a>
                            </div>
                        </section>

                        <!-- profile completion -->
                        <section class="sd-card sd-completion-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Complete Your Profile</h3>
                                    <p>Finish a few details to get better teacher recommendations.</p>
                                </div>
                                <span class="sd-progress-label">75% Complete</span>
                            </div>
                            <div class="progress-box sd-progress-box">
                                <div class="progress">
                                    <div class="progress-bar sd-progress-bar-75" role="progressbar" aria-valuenow="75"
                                        aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <ul class="sd-checklist">
                                <li class="is-done"><i class="far fa-circle-check"></i> Personal Information</li>
                                <li class="is-done"><i class="far fa-circle-check"></i> Profile Photo</li>
                                <li class="is-done"><i class="far fa-circle-check"></i> Learning Goals</li>
                                <li class="is-done"><i class="far fa-circle-check"></i> Preferred Instruments</li>
                            </ul>
                            <div class="sd-card-actions">
                                <a href="<?php echo esc_url( gmm_get_page_link( 'student_profile' ) ); ?>" class="theme-btn"><i class="far fa-user-pen"></i> Update Profile</a>
                            </div>
                        </section>

                        <!-- stats -->
                        <section class="sd-stats-grid">
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-chalkboard"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="5">0</span>
                                    <span class="sd-stat-title">Enrolled Classes</span>
                                </div>
                            </div>
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-calendar-days"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="3">0</span>
                                    <span class="sd-stat-title">Upcoming Lessons</span>
                                </div>
                            </div>
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="24">0</span>
                                    <span class="sd-stat-title">Completed Lessons</span>
                                </div>
                            </div>
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-heart"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="8">0</span>
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
                                <a href="<?php echo esc_url( gmm_get_page_link( 'student_lessons' ) ); ?>" class="sd-link">View All</a>
                            </div>
                            <div class="sd-lesson-list">
                                <article class="sd-lesson-card">
                                    <div class="sd-lesson-teacher">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="John Smith">
                                        <div>
                                            <h4>John Smith</h4>
                                            <p>Beginner Gospel Piano</p>
                                        </div>
                                    </div>
                                    <div class="sd-lesson-meta">
                                        <span><i class="far fa-calendar"></i> Monday</span>
                                        <span><i class="far fa-clock"></i> 10:00 AM</span>
                                        <span class="sd-badge is-confirmed">Confirmed</span>
                                    </div>
                                    <div class="sd-lesson-actions">
                                        <div class="dropdown">
                                            <button class="theme-btn theme-btn-outline sd-action-btn dropdown-toggle"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false">View Lesson</button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><a class="dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'student_lessons' ) ); ?>">Lesson Details</a></li>
                                                <li><a class="dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'student_bookings' ) ); ?>">Reschedule</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </article>
                                <article class="sd-lesson-card">
                                    <div class="sd-lesson-teacher">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/03.jpg' ) ); ?>" alt="Emily Carter">
                                        <div>
                                            <h4>Emily Carter</h4>
                                            <p>Worship Vocal Training</p>
                                        </div>
                                    </div>
                                    <div class="sd-lesson-meta">
                                        <span><i class="far fa-calendar"></i> Wednesday</span>
                                        <span><i class="far fa-clock"></i> 02:00 PM</span>
                                        <span class="sd-badge is-scheduled">Scheduled</span>
                                    </div>
                                    <div class="sd-lesson-actions">
                                        <a href="<?php echo esc_url( gmm_get_page_link( 'student_lessons' ) ); ?>" class="theme-btn theme-btn-outline sd-action-btn">View Lesson</a>
                                    </div>
                                </article>
                                <article class="sd-lesson-card">
                                    <div class="sd-lesson-teacher">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/04.jpg' ) ); ?>" alt="Michael Brown">
                                        <div>
                                            <h4>Michael Brown</h4>
                                            <p>Hammond Organ Essentials</p>
                                        </div>
                                    </div>
                                    <div class="sd-lesson-meta">
                                        <span><i class="far fa-calendar"></i> Friday</span>
                                        <span><i class="far fa-clock"></i> 11:00 AM</span>
                                        <span class="sd-badge is-pending">Pending</span>
                                    </div>
                                    <div class="sd-lesson-actions">
                                        <a href="<?php echo esc_url( gmm_get_page_link( 'student_lessons' ) ); ?>" class="theme-btn theme-btn-outline sd-action-btn">View Lesson</a>
                                    </div>
                                </article>
                            </div>
                        </section>

                        <div class="row g-4">
                            <div class="col-lg-7">
                                <!-- recommended teachers -->
                                <section class="sd-card">
                                    <div class="sd-card-head">
                                        <h3>Recommended Teachers</h3>
                                        <a href="teachers.html" class="sd-link">Browse All</a>
                                    </div>
                                    <div class="sd-teacher-grid">
                                        <article class="sd-teacher-card">
                                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="John Smith">
                                            <div class="sd-teacher-body">
                                                <h4>John Smith</h4>
                                                <p>Gospel Piano Instructor</p>
                                                <span class="sd-rating">★★★★★</span>
                                                <strong class="sd-price">$40/Lesson</strong>
                                                <a href="student-teacher-profile.html" class="theme-btn theme-btn-outline sd-action-btn">View Profile</a>
                                            </div>
                                        </article>
                                        <article class="sd-teacher-card">
                                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/03.jpg' ) ); ?>" alt="Emily Carter">
                                            <div class="sd-teacher-body">
                                                <h4>Emily Carter</h4>
                                                <p>Vocal Worship Coach</p>
                                                <span class="sd-rating">★★★★★</span>
                                                <strong class="sd-price">$45/Lesson</strong>
                                                <a href="student-teacher-profile.html" class="theme-btn theme-btn-outline sd-action-btn">View Profile</a>
                                            </div>
                                        </article>
                                        <article class="sd-teacher-card">
                                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/05.jpg' ) ); ?>" alt="David Lee">
                                            <div class="sd-teacher-body">
                                                <h4>David Lee</h4>
                                                <p>Hammond Organ Teacher</p>
                                                <span class="sd-rating">★★★★☆</span>
                                                <strong class="sd-price">$50/Lesson</strong>
                                                <a href="student-teacher-profile.html" class="theme-btn theme-btn-outline sd-action-btn">View Profile</a>
                                            </div>
                                        </article>
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
                                        <li>
                                            <span class="sd-activity-icon"><i class="far fa-calendar-plus"></i></span>
                                            <div>
                                                <strong>Booked Gospel Piano lesson</strong>
                                                <span>Today · 9:15 AM</span>
                                            </div>
                                        </li>
                                        <li>
                                            <span class="sd-activity-icon"><i class="far fa-credit-card"></i></span>
                                            <div>
                                                <strong>Payment completed</strong>
                                                <span>Yesterday · $40</span>
                                            </div>
                                        </li>
                                        <li>
                                            <span class="sd-activity-icon"><i class="far fa-heart"></i></span>
                                            <div>
                                                <strong>New teacher added to favourites</strong>
                                                <span>2 days ago</span>
                                            </div>
                                        </li>
                                        <li>
                                            <span class="sd-activity-icon"><i class="far fa-circle-check"></i></span>
                                            <div>
                                                <strong>Completed Worship Vocal lesson</strong>
                                                <span>3 days ago</span>
                                            </div>
                                        </li>
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
                                <a href="teachers.html" class="sd-quick-card">
                                    <i class="far fa-users"></i>
                                    <span>Browse Teachers</span>
                                </a>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'student_lessons' ) ); ?>" class="sd-quick-card">
                                    <i class="far fa-book-open"></i>
                                    <span>My Lessons</span>
                                </a>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'student_bookings' ) ); ?>" class="sd-quick-card">
                                    <i class="far fa-calendar-check"></i>
                                    <span>My Bookings</span>
                                </a>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'student_profile' ) ); ?>" class="sd-quick-card">
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

