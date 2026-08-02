<?php
/**
 * Template: admin-classes
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
                                <span class="sd-stat-item"><i class="far fa-clock"></i> Last login: Today, 09:12 AM</span>
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
                                <a class="dropdown-item ad-dropdown-item is-logout" href="admin-login.html"><i class="far fa-right-from-bracket"></i> <span>Logout</span></a>
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
                                <li><a href="admin-login.html" class="sd-nav-link sd-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
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
                                    <span class="sd-stat-value ad-counter" data-count="320" data-format="number">0</span>
                                    <span class="sd-stat-title">Total Classes</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="250" data-format="number">0</span>
                                    <span class="sd-stat-title">Published Classes</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card is-pending">
                                <div class="sd-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="45" data-format="number">0</span>
                                    <span class="sd-stat-title">Pending Review</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-xmark"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="25" data-format="number">0</span>
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
                                <span class="sf-count-pill" id="ac-result-count"><i class="far fa-chalkboard"></i> <strong>8</strong> Shown</span>
                            </div>

                            <form class="at-filter-bar" id="ac-filter-form" action="#" method="get">
                                <div class="at-search-field">
                                    <i class="far fa-search" aria-hidden="true"></i>
                                    <input type="search" class="form-control" id="ac-search"
                                        placeholder="Search classes by title or teacher..." autocomplete="off">
                                </div>
                                <div class="at-filter-selects">
                                    <div class="form-group mb-0">
                                        <label for="ac-status" class="visually-hidden">Status</label>
                                        <select class="form-control form-select" id="ac-status">
                                            <option value="all">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                            <option value="draft">Draft</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="ac-category" class="visually-hidden">Category</label>
                                        <select class="form-control form-select" id="ac-category">
                                            <option value="all">All Categories</option>
                                            <option value="piano">Gospel Piano</option>
                                            <option value="vocals">Vocals</option>
                                            <option value="guitar">Guitar</option>
                                            <option value="drums">Drums</option>
                                            <option value="theory">Music Theory</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="ac-difficulty" class="visually-hidden">Difficulty</label>
                                        <select class="form-control form-select" id="ac-difficulty">
                                            <option value="all">All Difficulty</option>
                                            <option value="beginner">Beginner</option>
                                            <option value="intermediate">Intermediate</option>
                                            <option value="advanced">Advanced</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="theme-btn" id="ac-filter-btn">
                                        <i class="far fa-filter"></i> Filter
                                    </button>
                                </div>
                            </form>

                            <div class="table-responsive td-table-wrap" id="ac-table-wrap">
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

                                        <tr class="ac-row"
                                            data-title="Beginner Gospel Piano Worship Lessons"
                                            data-teacher="John Smith"
                                            data-status="approved"
                                            data-category="piano"
                                            data-difficulty="beginner"
                                            data-price="$40"
                                            data-students="25"
                                            data-duration="60 Minutes"
                                            data-rating="4.9"
                                            data-created="Jan 15, 2026"
                                            data-description="Learn gospel piano fundamentals, worship chords, and practical techniques for Sunday service."
                                            data-image="assets/img/course/01.jpg"
                                            data-featured="true">
                                            <td data-label="Class Image"><img class="ac-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/01.jpg' ) ); ?>" alt="Beginner Gospel Piano Worship Lessons"></td>
                                            <td data-label="Class Name"><strong>Beginner Gospel Piano Worship Lessons</strong></td>
                                            <td data-label="Teacher">John Smith</td>
                                            <td data-label="Category">Gospel Piano</td>
                                            <td data-label="Price">$40</td>
                                            <td data-label="Students">25 Students</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed ac-status">Approved</span></td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
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

                                        <tr class="ac-row"
                                            data-title="Worship Vocal Training"
                                            data-teacher="Emily Davis"
                                            data-status="pending"
                                            data-category="vocals"
                                            data-difficulty="intermediate"
                                            data-price="$50"
                                            data-students="18"
                                            data-duration="45 Minutes"
                                            data-rating="4.8"
                                            data-created="Mar 02, 2026"
                                            data-description="Build vocal range, breath control, and worship leadership confidence for live ministry."
                                            data-image="assets/img/course/02.jpg"
                                            data-featured="false">
                                            <td data-label="Class Image"><img class="ac-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/02.jpg' ) ); ?>" alt="Worship Vocal Training"></td>
                                            <td data-label="Class Name"><strong>Worship Vocal Training</strong></td>
                                            <td data-label="Teacher">Emily Davis</td>
                                            <td data-label="Category">Vocals</td>
                                            <td data-label="Price">$50</td>
                                            <td data-label="Students">18 Students</td>
                                            <td data-label="Status"><span class="sb-badge is-pending ac-status">Pending</span></td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
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

                                        <tr class="ac-row"
                                            data-title="Church Guitar Basics"
                                            data-teacher="Michael Brown"
                                            data-status="draft"
                                            data-category="guitar"
                                            data-difficulty="beginner"
                                            data-price="$35"
                                            data-students="10"
                                            data-duration="50 Minutes"
                                            data-rating="4.7"
                                            data-created="Feb 20, 2026"
                                            data-description="Acoustic and electric guitar fundamentals for church bands and beginner worship players."
                                            data-image="assets/img/course/03.jpg"
                                            data-featured="false">
                                            <td data-label="Class Image"><img class="ac-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/03.jpg' ) ); ?>" alt="Church Guitar Basics"></td>
                                            <td data-label="Class Name"><strong>Church Guitar Basics</strong></td>
                                            <td data-label="Teacher">Michael Brown</td>
                                            <td data-label="Category">Guitar</td>
                                            <td data-label="Price">$35</td>
                                            <td data-label="Students">10 Students</td>
                                            <td data-label="Status"><span class="sb-badge is-inactive ac-status">Draft</span></td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
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

                                        <tr class="ac-row"
                                            data-title="Gospel Drum Grooves"
                                            data-teacher="David Wilson"
                                            data-status="approved"
                                            data-category="drums"
                                            data-difficulty="intermediate"
                                            data-price="$45"
                                            data-students="22"
                                            data-duration="55 Minutes"
                                            data-rating="4.9"
                                            data-created="Dec 10, 2025"
                                            data-description="Master gospel drum patterns, fills, and live worship tempo control."
                                            data-image="assets/img/course/04.jpg"
                                            data-featured="true">
                                            <td data-label="Class Image"><img class="ac-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/04.jpg' ) ); ?>" alt="Gospel Drum Grooves"></td>
                                            <td data-label="Class Name"><strong>Gospel Drum Grooves</strong></td>
                                            <td data-label="Teacher">David Wilson</td>
                                            <td data-label="Category">Drums</td>
                                            <td data-label="Price">$45</td>
                                            <td data-label="Students">22 Students</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed ac-status">Approved</span></td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
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

                                        <tr class="ac-row"
                                            data-title="Music Theory Fundamentals"
                                            data-teacher="Daniel Brooks"
                                            data-status="rejected"
                                            data-category="theory"
                                            data-difficulty="beginner"
                                            data-price="$30"
                                            data-students="8"
                                            data-duration="40 Minutes"
                                            data-rating="4.5"
                                            data-created="Feb 28, 2026"
                                            data-description="Chord progressions, scales, and harmony basics tailored for gospel musicians."
                                            data-image="assets/img/course/05.jpg"
                                            data-featured="false">
                                            <td data-label="Class Image"><img class="ac-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/05.jpg' ) ); ?>" alt="Music Theory Fundamentals"></td>
                                            <td data-label="Class Name"><strong>Music Theory Fundamentals</strong></td>
                                            <td data-label="Teacher">Daniel Brooks</td>
                                            <td data-label="Category">Music Theory</td>
                                            <td data-label="Price">$30</td>
                                            <td data-label="Students">8 Students</td>
                                            <td data-label="Status"><span class="sb-badge is-cancelled ac-status">Rejected</span></td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
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

                                        <tr class="ac-row"
                                            data-title="Advanced Worship Piano"
                                            data-teacher="John Smith"
                                            data-status="pending"
                                            data-category="piano"
                                            data-difficulty="advanced"
                                            data-price="$55"
                                            data-students="14"
                                            data-duration="60 Minutes"
                                            data-rating="5.0"
                                            data-created="Mar 08, 2026"
                                            data-description="Advanced runs, fills, and reharmonization techniques for worship piano leaders."
                                            data-image="assets/img/course/06.jpg"
                                            data-featured="false">
                                            <td data-label="Class Image"><img class="ac-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/06.jpg' ) ); ?>" alt="Advanced Worship Piano"></td>
                                            <td data-label="Class Name"><strong>Advanced Worship Piano</strong></td>
                                            <td data-label="Teacher">John Smith</td>
                                            <td data-label="Category">Gospel Piano</td>
                                            <td data-label="Price">$55</td>
                                            <td data-label="Students">14 Students</td>
                                            <td data-label="Status"><span class="sb-badge is-pending ac-status">Pending</span></td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
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

                                        <tr class="ac-row"
                                            data-title="Choir Harmony Coaching"
                                            data-teacher="Olivia Martin"
                                            data-status="approved"
                                            data-category="vocals"
                                            data-difficulty="advanced"
                                            data-price="$48"
                                            data-students="30"
                                            data-duration="50 Minutes"
                                            data-rating="4.9"
                                            data-created="Oct 05, 2025"
                                            data-description="Blend, harmony stacking, and choir section leadership for worship teams."
                                            data-image="assets/img/course/01.jpg"
                                            data-featured="true">
                                            <td data-label="Class Image"><img class="ac-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/01.jpg' ) ); ?>" alt="Choir Harmony Coaching"></td>
                                            <td data-label="Class Name"><strong>Choir Harmony Coaching</strong></td>
                                            <td data-label="Teacher">Olivia Martin</td>
                                            <td data-label="Category">Vocals</td>
                                            <td data-label="Price">$48</td>
                                            <td data-label="Students">30 Students</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed ac-status">Approved</span></td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
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

                                        <tr class="ac-row"
                                            data-title="Acoustic Worship Guitar"
                                            data-teacher="James Carter"
                                            data-status="draft"
                                            data-category="guitar"
                                            data-difficulty="intermediate"
                                            data-price="$38"
                                            data-students="6"
                                            data-duration="45 Minutes"
                                            data-rating="4.6"
                                            data-created="Mar 14, 2026"
                                            data-description="Fingerpicking, strum patterns, and capo techniques for acoustic worship sets."
                                            data-image="assets/img/course/03.jpg"
                                            data-featured="false">
                                            <td data-label="Class Image"><img class="ac-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/03.jpg' ) ); ?>" alt="Acoustic Worship Guitar"></td>
                                            <td data-label="Class Name"><strong>Acoustic Worship Guitar</strong></td>
                                            <td data-label="Teacher">James Carter</td>
                                            <td data-label="Category">Guitar</td>
                                            <td data-label="Price">$38</td>
                                            <td data-label="Students">6 Students</td>
                                            <td data-label="Status"><span class="sb-badge is-inactive ac-status">Draft</span></td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
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

                                    </tbody>
                                </table>
                            </div>

                            <div class="sl-empty" id="ac-empty" hidden>
                                <i class="far fa-chalkboard"></i>
                                <h3>No classes found.</h3>
                                <p>Try adjusting your search or filter options.</p>
                            </div>

                            <nav class="at-pagination" id="ac-pagination" aria-label="Classes pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item disabled" id="ac-page-prev">
                                        <a class="page-link" href="#" data-page="prev" aria-label="Previous"><i class="far fa-angle-left"></i> Previous</a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#" data-page="1">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#" data-page="2">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#" data-page="3">3</a></li>
                                    <li class="page-item" id="ac-page-next">
                                        <a class="page-link" href="#" data-page="next" aria-label="Next">Next <i class="far fa-angle-right"></i></a>
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
                                <!-- filled by JS from featured rows -->
                            </div>
                            <div class="sl-empty ac-featured-empty" id="ac-featured-empty" hidden>
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
        <span id="ac-toast-text">Action completed (demo).</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

