<?php
/**
 * Template: teacher-dashboard
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

<!-- teacher dashboard -->
        <div class="teacher-dashboard-area py-120">
            <div class="container">

                <!-- dashboard profile header -->
                <div class="td-profile-header">
                    <div class="td-profile-main">
                        <div class="td-profile-avatar">
                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="<?php echo esc_attr( $user_name ); ?>">
                        </div>
                        <div class="td-profile-meta">
                            <h2><?php echo esc_html( $user_name ); ?></h2>
                            <span class="td-role">Gospel Music Instructor</span>
                            <div class="td-profile-stats">
                                <span class="td-stat-item"><i class="fas fa-star"></i> 4.9</span>
                                <span class="td-stat-item"><i class="far fa-users"></i> 25 Students</span>
                                <span class="td-stat-item"><i class="far fa-books"></i> 12 Classes</span>
                            </div>
                        </div>
                    </div>
                    <div class="td-profile-actions">
                        <a href="teacher-onboarding-class.html" class="theme-btn"><i class="far fa-plus"></i> Create New Class</a>
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_login' ) ); ?>" class="td-nav-link td-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
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
                                <span class="td-progress-label">80% Complete</span>
                            </div>
                            <div class="progress-box td-progress-box">
                                <div class="progress">
                                    <div class="progress-bar td-progress-bar-80" role="progressbar" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            <ul class="td-checklist">
                                <li class="is-done"><i class="far fa-circle-check"></i> Profile Information</li>
                                <li class="is-done"><i class="far fa-circle-check"></i> Profile Photo</li>
                                <li class="is-done"><i class="far fa-circle-check"></i> Intro Video</li>
                                <li class="is-done"><i class="far fa-circle-check"></i> Payment Account</li>
                                <li class="is-done"><i class="far fa-circle-check"></i> First Class</li>
                            </ul>
                            <div class="td-card-actions">
                                <a href="<?php echo esc_url( gmm_get_page_link( 'teacher_profile' ) ); ?>" class="theme-btn theme-btn-outline"><i class="far fa-user-pen"></i> Edit Profile</a>
                                <a href="teacher-onboarding-profile.html" class="theme-btn"><i class="far fa-arrow-right"></i> Complete Setup</a>
                            </div>
                        </section>

                        <!-- stats -->
                        <section class="td-stats-grid">
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-book-open-reader"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value counter" data-count="12">0</span>
                                    <span class="td-stat-title">Enrolled Classes</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-bolt"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value counter" data-count="8">0</span>
                                    <span class="td-stat-title">Active Classes</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value counter" data-count="45">0</span>
                                    <span class="td-stat-title">Completed Lessons</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-users"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value counter" data-count="25">0</span>
                                    <span class="td-stat-title">Total Students</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-chalkboard-user"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value counter" data-count="10">0</span>
                                    <span class="td-stat-title">Total Classes</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-dollar-sign"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value">$<span class="counter" data-count="1250">0</span></span>
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
                                <a href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>" class="td-link">View All</a>
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
                                        <tr>
                                            <td data-label="Class Name">Beginner Gospel Piano</td>
                                            <td data-label="Category">Gospel Piano</td>
                                            <td data-label="Students">12 Students</td>
                                            <td data-label="Rating" class="td-rating">★★★★★</td>
                                            <td data-label="Status"><span class="td-badge is-published">Published</span></td>
                                            <td data-label="Action">
                                                <div class="dropdown td-action-dropdown">
                                                    <button class="theme-btn theme-btn-outline td-action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>">View</a></li>
                                                        <li><a class="dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>">Edit</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td data-label="Class Name">Worship Vocal Training</td>
                                            <td data-label="Category">Vocal Training</td>
                                            <td data-label="Students">8 Students</td>
                                            <td data-label="Rating" class="td-rating">★★★★★</td>
                                            <td data-label="Status"><span class="td-badge is-active">Active</span></td>
                                            <td data-label="Action">
                                                <div class="dropdown td-action-dropdown">
                                                    <button class="theme-btn theme-btn-outline td-action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>">View</a></li>
                                                        <li><a class="dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>">Edit</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td data-label="Class Name">Hammond Organ Essentials</td>
                                            <td data-label="Category">Hammond Organ</td>
                                            <td data-label="Students">5 Students</td>
                                            <td data-label="Rating" class="td-rating">★★★★☆</td>
                                            <td data-label="Status"><span class="td-badge is-pending">Pending</span></td>
                                            <td data-label="Action">
                                                <div class="dropdown td-action-dropdown">
                                                    <button class="theme-btn theme-btn-outline td-action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>">View</a></li>
                                                        <li><a class="dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>">Edit</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td data-label="Class Name">Choir Direction Basics</td>
                                            <td data-label="Category">Choir Direction</td>
                                            <td data-label="Students">0 Students</td>
                                            <td data-label="Rating" class="td-rating">—</td>
                                            <td data-label="Status"><span class="td-badge is-draft">Draft</span></td>
                                            <td data-label="Action">
                                                <div class="dropdown td-action-dropdown">
                                                    <button class="theme-btn theme-btn-outline td-action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>">View</a></li>
                                                        <li><a class="dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>">Edit</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td data-label="Class Name">Weekend Worship Intensive</td>
                                            <td data-label="Category">Worship Leadership</td>
                                            <td data-label="Students">15 Students</td>
                                            <td data-label="Rating" class="td-rating">★★★★★</td>
                                            <td data-label="Status"><span class="td-badge is-scheduled">Scheduled</span></td>
                                            <td data-label="Action">
                                                <div class="dropdown td-action-dropdown">
                                                    <button class="theme-btn theme-btn-outline td-action-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Actions</button>
                                                    <ul class="dropdown-menu dropdown-menu-end">
                                                        <li><a class="dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>">View</a></li>
                                                        <li><a class="dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>">Edit</a></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
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
                                        <a href="<?php echo esc_url( gmm_get_page_link( 'teacher_bookings' ) ); ?>" class="td-link">View All</a>
                                    </div>
                                    <div class="td-booking-list" id="td-booking-list">
                                        <article class="td-booking-card">
                                            <div class="td-booking-info">
                                                <h4>Sarah Johnson</h4>
                                                <p>Gospel Piano Basics</p>
                                                <span class="td-booking-meta"><i class="far fa-calendar"></i> Monday · 10:00 AM</span>
                                            </div>
                                            <span class="td-badge is-published">Confirmed</span>
                                        </article>
                                        <article class="td-booking-card">
                                            <div class="td-booking-info">
                                                <h4>Michael Brown</h4>
                                                <p>Worship Vocal Training</p>
                                                <span class="td-booking-meta"><i class="far fa-calendar"></i> Wednesday · 02:00 PM</span>
                                            </div>
                                            <span class="td-badge is-scheduled">Scheduled</span>
                                        </article>
                                        <article class="td-booking-card">
                                            <div class="td-booking-info">
                                                <h4>Emily Davis</h4>
                                                <p>Beginner Gospel Piano</p>
                                                <span class="td-booking-meta"><i class="far fa-calendar"></i> Friday · 11:00 AM</span>
                                            </div>
                                            <span class="td-badge is-pending">Pending</span>
                                        </article>
                                    </div>
                                    <p class="td-empty-state" id="td-bookings-empty" hidden>No upcoming lessons scheduled yet.</p>
                                </section>
                            </div>
                            <div class="col-lg-5">
                                <!-- quick actions -->
                                <section class="td-card">
                                    <div class="td-card-head">
                                        <h3>Quick Actions</h3>
                                    </div>
                                    <div class="td-quick-grid">
                                        <a href="<?php echo esc_url( gmm_get_page_link( 'teacher_profile' ) ); ?>" class="td-quick-card">
                                            <i class="far fa-user-pen"></i>
                                            <span>Edit Profile</span>
                                        </a>
                                        <a href="teacher-onboarding-class.html" class="td-quick-card">
                                            <i class="far fa-plus"></i>
                                            <span>Add New Class</span>
                                        </a>
                                        <a href="<?php echo esc_url( gmm_get_page_link( 'teacher_availability' ) ); ?>" class="td-quick-card">
                                            <i class="far fa-calendar-days"></i>
                                            <span>Update Availability</span>
                                        </a>
                                        <a href="<?php echo esc_url( gmm_get_page_link( 'teacher_withdrawals' ) ); ?>" class="td-quick-card">
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

