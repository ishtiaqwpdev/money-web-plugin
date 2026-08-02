<?php
/**
 * Template: student-lessons
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

        <!-- student lessons -->
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_dashboard' ) ); ?>" class="sd-nav-link" data-nav="dashboard"><i class="far fa-grid-2"></i> Dashboard</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_profile' ) ); ?>" class="sd-nav-link" data-nav="profile"><i class="far fa-user"></i> My Profile</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_lessons' ) ); ?>" class="sd-nav-link active" data-nav="lessons"><i class="far fa-book-open"></i> My Lessons</a></li>
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

                        <!-- page header -->
                        <section class="sd-card sd-welcome-card">
                            <div class="sd-card-head">
                                <div>
                                    <span class="login-portal-badge">My Learning</span>
                                    <h3>My Lessons</h3>
                                    <p>View your upcoming lessons, active courses, and completed learning sessions.</p>
                                </div>
                                <a href="teachers.html" class="theme-btn"><i class="far fa-plus"></i> Book New Lesson</a>
                            </div>
                        </section>

                        <!-- next lesson highlight -->
                        <section class="sd-card sl-next-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Next Lesson</h3>
                                    <p>Your soonest scheduled session.</p>
                                </div>
                                <span class="sd-badge is-scheduled">Upcoming</span>
                            </div>
                            <div class="sl-next-body">
                                <div class="sl-next-teacher">
                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="John Smith">
                                    <div>
                                        <h4>John Smith</h4>
                                        <p>Gospel Piano Basics</p>
                                    </div>
                                </div>
                                <div class="sl-next-meta">
                                    <span><i class="far fa-calendar"></i> Monday, March 20</span>
                                    <span><i class="far fa-clock"></i> 10:00 AM</span>
                                    <span><i class="far fa-hourglass"></i> 60 Minutes</span>
                                </div>
                                <div class="sl-next-actions">
                                    <button type="button" class="theme-btn" id="sl-join-next">
                                        <i class="far fa-video"></i> Join Lesson
                                    </button>
                                    <button type="button" class="theme-btn theme-btn-outline sl-open-details"
                                        data-teacher="John Smith"
                                        data-class="Gospel Piano Basics"
                                        data-category="Gospel Piano"
                                        data-date="Monday, March 20, 2026"
                                        data-time="10:00 AM"
                                        data-duration="60 Minutes"
                                        data-status="Upcoming"
                                        data-notes="Bring your practice hymn sheet and warm up for 5 minutes before class."
                                        data-link="https://meet.example.com/gospel-piano-demo">
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </section>

                        <!-- tabs + lessons -->
                        <section class="sd-card">
                            <div class="sl-tabs" role="tablist" aria-label="Lesson status filters">
                                <button type="button" class="sl-tab is-active" data-filter="all" role="tab" aria-selected="true">All Lessons</button>
                                <button type="button" class="sl-tab" data-filter="upcoming" role="tab" aria-selected="false">Upcoming</button>
                                <button type="button" class="sl-tab" data-filter="active" role="tab" aria-selected="false">Active</button>
                                <button type="button" class="sl-tab" data-filter="completed" role="tab" aria-selected="false">Completed</button>
                            </div>

                            <div class="sl-empty" id="sl-empty" hidden>
                                <i class="far fa-book-open"></i>
                                <h3>No lessons found yet.</h3>
                                <p>Start learning with one of our expert gospel music teachers.</p>
                                <a href="teachers.html" class="theme-btn"><i class="far fa-users"></i> Browse Teachers</a>
                            </div>

                            <div class="sl-grid" id="sl-grid">

                                <article class="sl-lesson-card" data-status="upcoming"
                                    data-teacher="John Smith"
                                    data-class="Beginner Gospel Piano Worship Lessons"
                                    data-category="Gospel Piano"
                                    data-date="March 20, 2026"
                                    data-time="10:00 AM"
                                    data-duration="60 Minutes"
                                    data-notes="Focus on worship chord progressions and left-hand patterns."
                                    data-link="https://meet.example.com/gospel-piano-demo">
                                    <div class="sl-lesson-top">
                                        <div class="sl-lesson-teacher">
                                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="John Smith">
                                            <div>
                                                <h4>John Smith</h4>
                                                <span class="sl-category">Gospel Piano</span>
                                            </div>
                                        </div>
                                        <span class="sd-badge is-scheduled">Upcoming</span>
                                    </div>
                                    <h5>Beginner Gospel Piano Worship Lessons</h5>
                                    <ul class="sl-meta">
                                        <li><i class="far fa-calendar"></i> March 20, 2026</li>
                                        <li><i class="far fa-clock"></i> 10:00 AM</li>
                                        <li><i class="far fa-hourglass"></i> 60 Minutes</li>
                                    </ul>
                                    <div class="sl-actions">
                                        <button type="button" class="theme-btn theme-btn-outline sd-action-btn sl-open-details">View Details</button>
                                        <button type="button" class="theme-btn theme-btn-outline sd-action-btn sl-cancel-btn">Cancel Lesson</button>
                                    </div>
                                </article>

                                <article class="sl-lesson-card" data-status="upcoming"
                                    data-teacher="Emily Carter"
                                    data-class="Worship Vocal Training"
                                    data-category="Vocal Training"
                                    data-date="March 22, 2026"
                                    data-time="02:00 PM"
                                    data-duration="45 Minutes"
                                    data-notes="Prepare one Sunday song for run-through."
                                    data-link="https://meet.example.com/vocal-demo">
                                    <div class="sl-lesson-top">
                                        <div class="sl-lesson-teacher">
                                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/03.jpg' ) ); ?>" alt="Emily Carter">
                                            <div>
                                                <h4>Emily Carter</h4>
                                                <span class="sl-category">Vocal Training</span>
                                            </div>
                                        </div>
                                        <span class="sd-badge is-scheduled">Upcoming</span>
                                    </div>
                                    <h5>Worship Vocal Training</h5>
                                    <ul class="sl-meta">
                                        <li><i class="far fa-calendar"></i> March 22, 2026</li>
                                        <li><i class="far fa-clock"></i> 02:00 PM</li>
                                        <li><i class="far fa-hourglass"></i> 45 Minutes</li>
                                    </ul>
                                    <div class="sl-actions">
                                        <button type="button" class="theme-btn theme-btn-outline sd-action-btn sl-open-details">View Details</button>
                                        <button type="button" class="theme-btn theme-btn-outline sd-action-btn sl-cancel-btn">Cancel Lesson</button>
                                    </div>
                                </article>

                                <article class="sl-lesson-card" data-status="active"
                                    data-teacher="Michael Brown"
                                    data-class="Hammond Organ Essentials"
                                    data-category="Hammond Organ"
                                    data-date="March 18, 2026"
                                    data-time="11:00 AM"
                                    data-duration="60 Minutes"
                                    data-notes="Live session in progress — join when ready."
                                    data-link="https://meet.example.com/organ-live">
                                    <div class="sl-lesson-top">
                                        <div class="sl-lesson-teacher">
                                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/04.jpg' ) ); ?>" alt="Michael Brown">
                                            <div>
                                                <h4>Michael Brown</h4>
                                                <span class="sl-category">Hammond Organ</span>
                                            </div>
                                        </div>
                                        <span class="sd-badge is-confirmed">Active</span>
                                    </div>
                                    <h5>Hammond Organ Essentials</h5>
                                    <ul class="sl-meta">
                                        <li><i class="far fa-calendar"></i> March 18, 2026</li>
                                        <li><i class="far fa-clock"></i> 11:00 AM</li>
                                        <li><i class="far fa-hourglass"></i> 60 Minutes</li>
                                    </ul>
                                    <div class="sl-actions">
                                        <button type="button" class="theme-btn sd-action-btn sl-join-btn">Join Lesson</button>
                                        <a href="<?php echo esc_url( gmm_get_page_link( 'student_dashboard' ) ); ?>" class="theme-btn theme-btn-outline sd-action-btn" data-gmm-message="student" data-teacher-name="John Smith" data-teacher-id="john-smith" data-student-name="Sarah Johnson" data-student-id="sarah-johnson">Message Teacher</a>
                                    </div>
                                </article>

                                <article class="sl-lesson-card" data-status="active"
                                    data-teacher="David Lee"
                                    data-class="Music Theory Fundamentals"
                                    data-category="Music Theory"
                                    data-date="March 19, 2026"
                                    data-time="04:00 PM"
                                    data-duration="90 Minutes"
                                    data-notes="Continue harmonic analysis worksheet from last week."
                                    data-link="https://meet.example.com/theory-live">
                                    <div class="sl-lesson-top">
                                        <div class="sl-lesson-teacher">
                                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/05.jpg' ) ); ?>" alt="David Lee">
                                            <div>
                                                <h4>David Lee</h4>
                                                <span class="sl-category">Music Theory</span>
                                            </div>
                                        </div>
                                        <span class="sd-badge is-confirmed">Active</span>
                                    </div>
                                    <h5>Music Theory Fundamentals</h5>
                                    <ul class="sl-meta">
                                        <li><i class="far fa-calendar"></i> March 19, 2026</li>
                                        <li><i class="far fa-clock"></i> 04:00 PM</li>
                                        <li><i class="far fa-hourglass"></i> 90 Minutes</li>
                                    </ul>
                                    <div class="sl-actions">
                                        <button type="button" class="theme-btn sd-action-btn sl-join-btn">Join Lesson</button>
                                        <a href="<?php echo esc_url( gmm_get_page_link( 'student_dashboard' ) ); ?>" class="theme-btn theme-btn-outline sd-action-btn" data-gmm-message="student" data-teacher-name="John Smith" data-teacher-id="john-smith" data-student-name="Sarah Johnson" data-student-id="sarah-johnson">Message Teacher</a>
                                    </div>
                                </article>

                                <article class="sl-lesson-card" data-status="completed"
                                    data-teacher="John Smith"
                                    data-class="Beginner Gospel Piano Worship Lessons"
                                    data-category="Gospel Piano"
                                    data-date="March 10, 2026"
                                    data-time="10:00 AM"
                                    data-duration="60 Minutes"
                                    data-notes="Completed — review recording for chord transitions."
                                    data-link="#">
                                    <div class="sl-lesson-top">
                                        <div class="sl-lesson-teacher">
                                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="John Smith">
                                            <div>
                                                <h4>John Smith</h4>
                                                <span class="sl-category">Gospel Piano</span>
                                            </div>
                                        </div>
                                        <span class="sd-badge is-pending">Completed</span>
                                    </div>
                                    <h5>Beginner Gospel Piano Worship Lessons</h5>
                                    <ul class="sl-meta">
                                        <li><i class="far fa-calendar"></i> March 10, 2026</li>
                                        <li><i class="far fa-clock"></i> 10:00 AM</li>
                                        <li><i class="far fa-hourglass"></i> 60 Minutes</li>
                                    </ul>
                                    <div class="sl-actions">
                                        <button type="button" class="theme-btn theme-btn-outline sd-action-btn sl-review-btn">Leave Review</button>
                                        <button type="button" class="theme-btn theme-btn-outline sd-action-btn sl-certificate-btn">View Certificate</button>
                                    </div>
                                </article>

                                <article class="sl-lesson-card" data-status="completed"
                                    data-teacher="Emily Carter"
                                    data-class="Worship Vocal Training"
                                    data-category="Vocal Training"
                                    data-date="March 5, 2026"
                                    data-time="02:00 PM"
                                    data-duration="45 Minutes"
                                    data-notes="Completed — practice breath support exercises daily."
                                    data-link="#">
                                    <div class="sl-lesson-top">
                                        <div class="sl-lesson-teacher">
                                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/03.jpg' ) ); ?>" alt="Emily Carter">
                                            <div>
                                                <h4>Emily Carter</h4>
                                                <span class="sl-category">Vocal Training</span>
                                            </div>
                                        </div>
                                        <span class="sd-badge is-pending">Completed</span>
                                    </div>
                                    <h5>Worship Vocal Training</h5>
                                    <ul class="sl-meta">
                                        <li><i class="far fa-calendar"></i> March 5, 2026</li>
                                        <li><i class="far fa-clock"></i> 02:00 PM</li>
                                        <li><i class="far fa-hourglass"></i> 45 Minutes</li>
                                    </ul>
                                    <div class="sl-actions">
                                        <button type="button" class="theme-btn theme-btn-outline sd-action-btn sl-review-btn">Leave Review</button>
                                        <button type="button" class="theme-btn theme-btn-outline sd-action-btn sl-certificate-btn">View Certificate</button>
                                    </div>
                                </article>

                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- student lessons end -->

    

<!-- lesson details modal -->
    <div class="modal fade gospel-demo-modal" id="sl-details-modal" tabindex="-1" aria-labelledby="sl-details-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="sl-details-title">Lesson Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="booking-modal-list">
                        <li><span>Teacher</span><strong id="sl-modal-teacher">—</strong></li>
                        <li><span>Class</span><strong id="sl-modal-class">—</strong></li>
                        <li><span>Category</span><strong id="sl-modal-category">—</strong></li>
                        <li><span>Date</span><strong id="sl-modal-date">—</strong></li>
                        <li><span>Time</span><strong id="sl-modal-time">—</strong></li>
                        <li><span>Duration</span><strong id="sl-modal-duration">—</strong></li>
                        <li><span>Status</span><strong id="sl-modal-status">—</strong></li>
                    </ul>
                    <div class="sl-modal-notes">
                        <h6>Notes</h6>
                        <p id="sl-modal-notes">—</p>
                    </div>
                    <div class="sl-modal-link">
                        <h6>Meeting Link</h6>
                        <a href="#" id="sl-modal-link" target="_blank" rel="noopener">Meeting link placeholder</a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Close</button>
                    <a href="#" class="theme-btn" id="sl-modal-join" target="_blank" rel="noopener">
                        <i class="far fa-video"></i> Join Lesson
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- demo alert toast area -->
    <div class="gospel-alert gospel-alert-success sl-toast" id="sl-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="sl-toast-text">Action completed (demo).</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

