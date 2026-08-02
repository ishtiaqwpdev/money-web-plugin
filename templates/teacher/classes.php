<?php
/**
 * Template: teacher-classes
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_dashboard' ) ); ?>" class="td-nav-link" data-nav="dashboard"><i class="far fa-grid-2"></i> Dashboard</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_profile' ) ); ?>" class="td-nav-link" data-nav="profile"><i class="far fa-user"></i> My Profile</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_availability' ) ); ?>" class="td-nav-link" data-nav="availability"><i class="far fa-calendar-days"></i> Availability</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_bookings' ) ); ?>" class="td-nav-link" data-nav="bookings"><i class="far fa-calendar-check"></i> My Bookings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>" class="td-nav-link active" data-nav="classes"><i class="far fa-chalkboard"></i> My Classes</a></li>
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

                        <!-- page header -->
                        <section class="td-card">
                            <div class="td-card-head">
                                <div>
                                    <span class="login-portal-badge">Class Management</span>
                                    <h3>My Music Classes</h3>
                                    <p>Create, manage, and update your gospel music lessons from one place.</p>
                                </div>
                                <a href="teacher-onboarding-class.html" class="theme-btn"><i class="far fa-plus"></i> Create New Class</a>
                            </div>
                        </section>

                        <!-- class statistics -->
                        <section class="td-stats-grid bookings-summary-grid">
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-chalkboard"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value">12</span>
                                    <span class="td-stat-title">Total Classes</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value">8</span>
                                    <span class="td-stat-title">Published</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value">2</span>
                                    <span class="td-stat-title">Pending Review</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-file-lines"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value">2</span>
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
                                <button type="button" class="booking-tab class-tab active" data-filter="all" role="tab" aria-selected="true">All Classes</button>
                                <button type="button" class="booking-tab class-tab" data-filter="published" role="tab" aria-selected="false">Published</button>
                                <button type="button" class="booking-tab class-tab" data-filter="pending" role="tab" aria-selected="false">Pending</button>
                                <button type="button" class="booking-tab class-tab" data-filter="draft" role="tab" aria-selected="false">Draft</button>
                                <button type="button" class="booking-tab class-tab" data-filter="scheduled" role="tab" aria-selected="false">Scheduled</button>
                            </div>

                            <div class="class-cards-grid" id="class-cards-grid">

                                <article class="class-manage-card" data-status="published" data-title="Beginner Gospel Piano Worship Lessons">
                                    <div class="class-manage-media">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/01.jpg' ) ); ?>" alt="Beginner Gospel Piano Worship Lessons">
                                        <span class="td-badge is-published class-manage-badge">Published</span>
                                    </div>
                                    <div class="class-manage-body">
                                        <span class="class-manage-category">Gospel Piano</span>
                                        <h4>Beginner Gospel Piano Worship Lessons</h4>
                                        <ul class="class-manage-meta">
                                            <li><i class="far fa-clock"></i> 60 Minutes</li>
                                            <li><i class="far fa-dollar-sign"></i> $40</li>
                                            <li><i class="far fa-users"></i> 12 Students</li>
                                        </ul>
                                        <div class="class-manage-footer">
                                            <span class="td-rating">★★★★★</span>
                                            <div class="dropdown">
                                                <button class="class-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Class actions">
                                                    <i class="far fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><button type="button" class="dropdown-item class-view-btn">View</button></li>
                                                    <li><a class="dropdown-item" href="teacher-onboarding-class.html">Edit</a></li>
                                                    <li><button type="button" class="dropdown-item class-duplicate-btn">Duplicate</button></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><button type="button" class="dropdown-item text-danger class-delete-btn">Delete</button></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </article>

                                <article class="class-manage-card" data-status="scheduled" data-title="Vocal Training For Worship Leaders">
                                    <div class="class-manage-media">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/02.jpg' ) ); ?>" alt="Vocal Training For Worship Leaders">
                                        <span class="td-badge is-scheduled class-manage-badge">Scheduled</span>
                                    </div>
                                    <div class="class-manage-body">
                                        <span class="class-manage-category">Vocal Training</span>
                                        <h4>Vocal Training For Worship Leaders</h4>
                                        <ul class="class-manage-meta">
                                            <li><i class="far fa-clock"></i> 45 Minutes</li>
                                            <li><i class="far fa-dollar-sign"></i> $35</li>
                                            <li><i class="far fa-users"></i> 8 Students</li>
                                        </ul>
                                        <div class="class-manage-footer">
                                            <span class="td-rating">★★★★★</span>
                                            <div class="dropdown">
                                                <button class="class-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Class actions">
                                                    <i class="far fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><button type="button" class="dropdown-item class-view-btn">View</button></li>
                                                    <li><a class="dropdown-item" href="teacher-onboarding-class.html">Edit</a></li>
                                                    <li><button type="button" class="dropdown-item class-duplicate-btn">Duplicate</button></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><button type="button" class="dropdown-item text-danger class-delete-btn">Delete</button></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </article>

                                <article class="class-manage-card" data-status="draft" data-title="Church Guitar Basics">
                                    <div class="class-manage-media">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/03.jpg' ) ); ?>" alt="Church Guitar Basics">
                                        <span class="td-badge is-draft class-manage-badge">Draft</span>
                                    </div>
                                    <div class="class-manage-body">
                                        <span class="class-manage-category">Guitar</span>
                                        <h4>Church Guitar Basics</h4>
                                        <ul class="class-manage-meta">
                                            <li><i class="far fa-clock"></i> 60 Minutes</li>
                                            <li><i class="far fa-dollar-sign"></i> $30</li>
                                            <li><i class="far fa-users"></i> 0 Students</li>
                                        </ul>
                                        <div class="class-manage-footer">
                                            <span class="td-rating">—</span>
                                            <div class="dropdown">
                                                <button class="class-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Class actions">
                                                    <i class="far fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><button type="button" class="dropdown-item class-view-btn">View</button></li>
                                                    <li><a class="dropdown-item" href="teacher-onboarding-class.html">Edit</a></li>
                                                    <li><button type="button" class="dropdown-item class-duplicate-btn">Duplicate</button></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><button type="button" class="dropdown-item text-danger class-delete-btn">Delete</button></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </article>

                                <article class="class-manage-card" data-status="pending" data-title="Music Theory Fundamentals">
                                    <div class="class-manage-media">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/04.jpg' ) ); ?>" alt="Music Theory Fundamentals">
                                        <span class="td-badge is-pending class-manage-badge">Pending</span>
                                    </div>
                                    <div class="class-manage-body">
                                        <span class="class-manage-category">Music Theory</span>
                                        <h4>Music Theory Fundamentals</h4>
                                        <ul class="class-manage-meta">
                                            <li><i class="far fa-clock"></i> 90 Minutes</li>
                                            <li><i class="far fa-dollar-sign"></i> $45</li>
                                            <li><i class="far fa-users"></i> 0 Students</li>
                                        </ul>
                                        <div class="class-manage-footer">
                                            <span class="td-rating">—</span>
                                            <div class="dropdown">
                                                <button class="class-menu-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Class actions">
                                                    <i class="far fa-ellipsis-vertical"></i>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end">
                                                    <li><button type="button" class="dropdown-item class-view-btn">View</button></li>
                                                    <li><a class="dropdown-item" href="teacher-onboarding-class.html">Edit</a></li>
                                                    <li><button type="button" class="dropdown-item class-duplicate-btn">Duplicate</button></li>
                                                    <li><hr class="dropdown-divider"></li>
                                                    <li><button type="button" class="dropdown-item text-danger class-delete-btn">Delete</button></li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </article>

                            </div>

                            <p class="td-empty-state" id="classes-empty" hidden>
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
                    <p>Are you sure you want to delete <strong id="class-delete-name">this class</strong>? This is a frontend demo and will only remove it from the list.</p>
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
                    <p id="class-view-text">Class details preview (demo).</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Close</button>
                    <a href="teacher-onboarding-class.html" class="theme-btn">Edit Class</a>
                </div>
            </div>
        </div>
    </div>

<!-- js -->
</div><!-- .gmm-wrapper -->

