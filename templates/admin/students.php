<?php
/**
 * Template: admin-students
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

        <!-- admin students -->
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
                                        <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_students' ) ); ?>" class="sd-nav-link ad-sub-link active" data-nav="students"><i class="far fa-graduation-cap"></i> Students</a></li>
                                    </ul>
                                </li>

                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_classes' ) ); ?>" class="sd-nav-link" data-nav="classes"><i class="far fa-chalkboard"></i> Classes</a></li>
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
                                    <span class="login-portal-badge">User Management</span>
                                    <h3>Manage Students</h3>
                                    <p>View student accounts, monitor learning activity, and manage platform users.</p>
                                </div>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'admin_dashboard' ) ); ?>" class="theme-btn theme-btn-outline"><i class="far fa-grid-2"></i> Dashboard</a>
                            </div>
                        </section>

                        <!-- summary cards -->
                        <section class="sd-stats-grid ad-stats-grid at-stats-grid">
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-graduation-cap"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="1250" data-format="number">0</span>
                                    <span class="sd-stat-title">Total Students</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="1100" data-format="number">0</span>
                                    <span class="sd-stat-title">Active Students</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-user-plus"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="85" data-format="number">0</span>
                                    <span class="sd-stat-title">New Registrations</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card is-pending">
                                <div class="sd-stat-icon"><i class="far fa-ban"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="15" data-format="number">0</span>
                                    <span class="sd-stat-title">Suspended Accounts</span>
                                </div>
                            </div>
                        </section>

                        <!-- analytics charts -->
                        <div class="gmm-chart-grid">
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Student Registration Growth</h3>
                                        <p>New student registrations each month.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-as-registration" aria-label="Student registration line chart"></canvas>
                                </div>
                            </section>
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Student Level Distribution</h3>
                                        <p>Students by learning level.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap is-doughnut">
                                    <canvas id="gmm-as-level" aria-label="Student level doughnut chart"></canvas>
                                </div>
                            </section>
                        </div>

                        <div class="as-content-grid">

                            <!-- students table -->
                            <section class="sd-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>All Students</h3>
                                        <p>Search, filter, and manage student accounts.</p>
                                    </div>
                                    <span class="sf-count-pill" id="as-result-count"><i class="far fa-graduation-cap"></i> <strong>8</strong> Shown</span>
                                </div>

                                <!-- search & filters -->
                                <form class="at-filter-bar" id="as-filter-form" action="#" method="get">
                                    <div class="at-search-field">
                                        <i class="far fa-search" aria-hidden="true"></i>
                                        <input type="search" class="form-control" id="as-search"
                                            placeholder="Search students by name or email..." autocomplete="off">
                                    </div>
                                    <div class="at-filter-selects">
                                        <div class="form-group mb-0">
                                            <label for="as-status" class="visually-hidden">Status</label>
                                            <select class="form-control form-select" id="as-status">
                                                <option value="all">All Status</option>
                                                <option value="active">Active</option>
                                                <option value="inactive">Inactive</option>
                                                <option value="suspended">Suspended</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="as-level" class="visually-hidden">Learning Level</label>
                                            <select class="form-control form-select" id="as-level">
                                                <option value="all">All Levels</option>
                                                <option value="beginner">Beginner</option>
                                                <option value="intermediate">Intermediate</option>
                                                <option value="advanced">Advanced</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="as-period" class="visually-hidden">Registration Date</label>
                                            <select class="form-control form-select" id="as-period">
                                                <option value="all">All Time</option>
                                                <option value="month">This Month</option>
                                                <option value="year">This Year</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="theme-btn" id="as-filter-btn">
                                            <i class="far fa-filter"></i> Filter
                                        </button>
                                    </div>
                                </form>

                                <div class="table-responsive td-table-wrap" id="as-table-wrap">
                                    <table class="table td-table sb-table at-table">
                                        <thead>
                                            <tr>
                                                <th>Profile</th>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Phone</th>
                                                <th>Learning Level</th>
                                                <th>Enrolled Classes</th>
                                                <th>Status</th>
                                                <th>Joined Date</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="as-table-body">

                                            <tr class="as-row"
                                                data-name="Sarah Johnson"
                                                data-email="sarah@email.com"
                                                data-phone="+1 555 123456"
                                                data-status="active"
                                                data-level="intermediate"
                                                data-period="year"
                                                data-instruments="Gospel Piano, Worship Vocals"
                                                data-classes="5"
                                                data-completed="24"
                                                data-joined="Jan 08, 2026"
                                                data-bio="Intermediate student focused on worship piano skills and Sunday service confidence."
                                                data-image="assets/img/team/02.jpg"
                                                data-activity="Booked new lesson|Completed class|Added favourite teacher|Payment completed">
                                                <td data-label="Profile"><img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="Sarah Johnson"></td>
                                                <td data-label="Name"><strong>Sarah Johnson</strong></td>
                                                <td data-label="Email">sarah@email.com</td>
                                                <td data-label="Phone">+1 555 123456</td>
                                                <td data-label="Learning Level">Intermediate</td>
                                                <td data-label="Enrolled Classes">5 Classes</td>
                                                <td data-label="Status"><span class="sb-badge is-confirmed as-status">Active</span></td>
                                                <td data-label="Joined Date">Jan 08, 2026</td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for Sarah Johnson">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-edit-btn"><i class="far fa-pen"></i> <span>Edit Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-lessons-btn"><i class="far fa-book-open"></i> <span>View Lessons</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-payments-btn"><i class="far fa-credit-card"></i> <span>View Payments</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-suspend-btn"><i class="far fa-ban"></i> <span>Suspend Account</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item is-logout as-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="as-row"
                                                data-name="David Wilson"
                                                data-email="david@email.com"
                                                data-phone="+1 555 456789"
                                                data-status="active"
                                                data-level="beginner"
                                                data-period="month"
                                                data-instruments="Piano"
                                                data-classes="2"
                                                data-completed="4"
                                                data-joined="Mar 12, 2026"
                                                data-bio="Beginner learner starting gospel piano fundamentals and chord progressions."
                                                data-image="assets/img/team/05.jpg"
                                                data-activity="Booked new lesson|Payment completed|Completed class|Added favourite teacher">
                                                <td data-label="Profile"><img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/05.jpg' ) ); ?>" alt="David Wilson"></td>
                                                <td data-label="Name"><strong>David Wilson</strong></td>
                                                <td data-label="Email">david@email.com</td>
                                                <td data-label="Phone">+1 555 456789</td>
                                                <td data-label="Learning Level">Beginner</td>
                                                <td data-label="Enrolled Classes">2 Classes</td>
                                                <td data-label="Status"><span class="sb-badge is-confirmed as-status">Active</span></td>
                                                <td data-label="Joined Date">Mar 12, 2026</td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for David Wilson">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-edit-btn"><i class="far fa-pen"></i> <span>Edit Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-lessons-btn"><i class="far fa-book-open"></i> <span>View Lessons</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-payments-btn"><i class="far fa-credit-card"></i> <span>View Payments</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-suspend-btn"><i class="far fa-ban"></i> <span>Suspend Account</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item is-logout as-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="as-row"
                                                data-name="Michael Brown"
                                                data-email="michael@email.com"
                                                data-phone="+1 555 987654"
                                                data-status="suspended"
                                                data-level="advanced"
                                                data-period="year"
                                                data-instruments="Guitar, Music Theory"
                                                data-classes="8"
                                                data-completed="42"
                                                data-joined="Sep 03, 2025"
                                                data-bio="Advanced guitar student focusing on worship band arranging and improvisation."
                                                data-image="assets/img/team/04.jpg"
                                                data-activity="Payment completed|Completed class|Booked new lesson|Added favourite teacher">
                                                <td data-label="Profile"><img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/04.jpg' ) ); ?>" alt="Michael Brown"></td>
                                                <td data-label="Name"><strong>Michael Brown</strong></td>
                                                <td data-label="Email">michael@email.com</td>
                                                <td data-label="Phone">+1 555 987654</td>
                                                <td data-label="Learning Level">Advanced</td>
                                                <td data-label="Enrolled Classes">8 Classes</td>
                                                <td data-label="Status"><span class="sb-badge is-suspended as-status">Suspended</span></td>
                                                <td data-label="Joined Date">Sep 03, 2025</td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for Michael Brown">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-edit-btn"><i class="far fa-pen"></i> <span>Edit Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-lessons-btn"><i class="far fa-book-open"></i> <span>View Lessons</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-payments-btn"><i class="far fa-credit-card"></i> <span>View Payments</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-suspend-btn"><i class="far fa-ban"></i> <span>Suspend Account</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item is-logout as-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="as-row"
                                                data-name="Emily Carter"
                                                data-email="emily.c@email.com"
                                                data-phone="+1 555 222333"
                                                data-status="inactive"
                                                data-level="intermediate"
                                                data-period="year"
                                                data-instruments="Vocals"
                                                data-classes="3"
                                                data-completed="11"
                                                data-joined="Nov 19, 2025"
                                                data-bio="Inactive vocal student who previously trained for church choir leadership."
                                                data-image="assets/img/team/03.jpg"
                                                data-activity="Completed class|Added favourite teacher|Payment completed|Booked new lesson">
                                                <td data-label="Profile"><img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/03.jpg' ) ); ?>" alt="Emily Carter"></td>
                                                <td data-label="Name"><strong>Emily Carter</strong></td>
                                                <td data-label="Email">emily.c@email.com</td>
                                                <td data-label="Phone">+1 555 222333</td>
                                                <td data-label="Learning Level">Intermediate</td>
                                                <td data-label="Enrolled Classes">3 Classes</td>
                                                <td data-label="Status"><span class="sb-badge is-inactive as-status">Inactive</span></td>
                                                <td data-label="Joined Date">Nov 19, 2025</td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for Emily Carter">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-edit-btn"><i class="far fa-pen"></i> <span>Edit Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-lessons-btn"><i class="far fa-book-open"></i> <span>View Lessons</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-payments-btn"><i class="far fa-credit-card"></i> <span>View Payments</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-suspend-btn"><i class="far fa-ban"></i> <span>Suspend Account</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item is-logout as-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="as-row"
                                                data-name="Olivia Martin"
                                                data-email="olivia@email.com"
                                                data-phone="+1 555 444555"
                                                data-status="active"
                                                data-level="advanced"
                                                data-period="month"
                                                data-instruments="Vocals, Piano"
                                                data-classes="6"
                                                data-completed="31"
                                                data-joined="Mar 01, 2026"
                                                data-bio="Advanced worship vocalist refining range, harmony, and stage leadership."
                                                data-image="assets/img/team/07.jpg"
                                                data-activity="Added favourite teacher|Booked new lesson|Payment completed|Completed class">
                                                <td data-label="Profile"><img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/07.jpg' ) ); ?>" alt="Olivia Martin"></td>
                                                <td data-label="Name"><strong>Olivia Martin</strong></td>
                                                <td data-label="Email">olivia@email.com</td>
                                                <td data-label="Phone">+1 555 444555</td>
                                                <td data-label="Learning Level">Advanced</td>
                                                <td data-label="Enrolled Classes">6 Classes</td>
                                                <td data-label="Status"><span class="sb-badge is-confirmed as-status">Active</span></td>
                                                <td data-label="Joined Date">Mar 01, 2026</td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for Olivia Martin">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-edit-btn"><i class="far fa-pen"></i> <span>Edit Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-lessons-btn"><i class="far fa-book-open"></i> <span>View Lessons</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-payments-btn"><i class="far fa-credit-card"></i> <span>View Payments</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-suspend-btn"><i class="far fa-ban"></i> <span>Suspend Account</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item is-logout as-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="as-row"
                                                data-name="James Carter"
                                                data-email="james.c@email.com"
                                                data-phone="+1 555 666777"
                                                data-status="active"
                                                data-level="beginner"
                                                data-period="month"
                                                data-instruments="Drums"
                                                data-classes="1"
                                                data-completed="2"
                                                data-joined="Mar 18, 2026"
                                                data-bio="New drum student learning worship grooves and live tempo control."
                                                data-image="assets/img/team/06.jpg"
                                                data-activity="Booked new lesson|Payment completed|Added favourite teacher|Completed class">
                                                <td data-label="Profile"><img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/06.jpg' ) ); ?>" alt="James Carter"></td>
                                                <td data-label="Name"><strong>James Carter</strong></td>
                                                <td data-label="Email">james.c@email.com</td>
                                                <td data-label="Phone">+1 555 666777</td>
                                                <td data-label="Learning Level">Beginner</td>
                                                <td data-label="Enrolled Classes">1 Class</td>
                                                <td data-label="Status"><span class="sb-badge is-confirmed as-status">Active</span></td>
                                                <td data-label="Joined Date">Mar 18, 2026</td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for James Carter">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-edit-btn"><i class="far fa-pen"></i> <span>Edit Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-lessons-btn"><i class="far fa-book-open"></i> <span>View Lessons</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-payments-btn"><i class="far fa-credit-card"></i> <span>View Payments</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-suspend-btn"><i class="far fa-ban"></i> <span>Suspend Account</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item is-logout as-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="as-row"
                                                data-name="Daniel Brooks"
                                                data-email="daniel.b@email.com"
                                                data-phone="+1 555 888999"
                                                data-status="active"
                                                data-level="intermediate"
                                                data-period="year"
                                                data-instruments="Music Theory, Piano"
                                                data-classes="4"
                                                data-completed="16"
                                                data-joined="Jul 22, 2025"
                                                data-bio="Theory-focused student building harmony skills for songwriting and team rehearsals."
                                                data-image="assets/img/team/08.jpg"
                                                data-activity="Completed class|Booked new lesson|Payment completed|Added favourite teacher">
                                                <td data-label="Profile"><img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/08.jpg' ) ); ?>" alt="Daniel Brooks"></td>
                                                <td data-label="Name"><strong>Daniel Brooks</strong></td>
                                                <td data-label="Email">daniel.b@email.com</td>
                                                <td data-label="Phone">+1 555 888999</td>
                                                <td data-label="Learning Level">Intermediate</td>
                                                <td data-label="Enrolled Classes">4 Classes</td>
                                                <td data-label="Status"><span class="sb-badge is-confirmed as-status">Active</span></td>
                                                <td data-label="Joined Date">Jul 22, 2025</td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for Daniel Brooks">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-edit-btn"><i class="far fa-pen"></i> <span>Edit Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-lessons-btn"><i class="far fa-book-open"></i> <span>View Lessons</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-payments-btn"><i class="far fa-credit-card"></i> <span>View Payments</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-suspend-btn"><i class="far fa-ban"></i> <span>Suspend Account</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item is-logout as-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="as-row"
                                                data-name="Hannah Lee"
                                                data-email="hannah@email.com"
                                                data-phone="+1 555 101112"
                                                data-status="suspended"
                                                data-level="beginner"
                                                data-period="year"
                                                data-instruments="Piano"
                                                data-classes="2"
                                                data-completed="3"
                                                data-joined="Dec 04, 2025"
                                                data-bio="Beginner piano student currently suspended pending account review."
                                                data-image="assets/img/team/02.jpg"
                                                data-activity="Payment completed|Booked new lesson|Completed class|Added favourite teacher">
                                                <td data-label="Profile"><img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="Hannah Lee"></td>
                                                <td data-label="Name"><strong>Hannah Lee</strong></td>
                                                <td data-label="Email">hannah@email.com</td>
                                                <td data-label="Phone">+1 555 101112</td>
                                                <td data-label="Learning Level">Beginner</td>
                                                <td data-label="Enrolled Classes">2 Classes</td>
                                                <td data-label="Status"><span class="sb-badge is-suspended as-status">Suspended</span></td>
                                                <td data-label="Joined Date">Dec 04, 2025</td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for Hannah Lee">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-edit-btn"><i class="far fa-pen"></i> <span>Edit Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-lessons-btn"><i class="far fa-book-open"></i> <span>View Lessons</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-payments-btn"><i class="far fa-credit-card"></i> <span>View Payments</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item as-suspend-btn"><i class="far fa-ban"></i> <span>Suspend Account</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item is-logout as-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>

                                <div class="sl-empty" id="as-empty" hidden>
                                    <i class="far fa-graduation-cap"></i>
                                    <h3>No students found.</h3>
                                    <p>Try adjusting your search or filter options.</p>
                                </div>

                                <nav class="at-pagination" id="as-pagination" aria-label="Students pagination">
                                    <ul class="pagination justify-content-center mb-0">
                                        <li class="page-item disabled" id="as-page-prev">
                                            <a class="page-link" href="#" data-page="prev" aria-label="Previous"><i class="far fa-angle-left"></i> Previous</a>
                                        </li>
                                        <li class="page-item active"><a class="page-link" href="#" data-page="1">1</a></li>
                                        <li class="page-item"><a class="page-link" href="#" data-page="2">2</a></li>
                                        <li class="page-item"><a class="page-link" href="#" data-page="3">3</a></li>
                                        <li class="page-item" id="as-page-next">
                                            <a class="page-link" href="#" data-page="next" aria-label="Next">Next <i class="far fa-angle-right"></i></a>
                                        </li>
                                    </ul>
                                </nav>
                            </section>

                            <!-- recent activity card -->
                            <aside class="sd-card as-activity-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Recent Activity</h3>
                                        <p>Platform student activity overview.</p>
                                    </div>
                                </div>
                                <ul class="as-activity-list" id="as-activity-list">
                                    <li>
                                        <span class="as-activity-icon"><i class="far fa-calendar-plus"></i></span>
                                        <div>
                                            <strong>Booked new lesson</strong>
                                            <small>Sarah Johnson · 12 min ago</small>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="as-activity-icon"><i class="far fa-circle-check"></i></span>
                                        <div>
                                            <strong>Completed class</strong>
                                            <small>Daniel Brooks · 1 hour ago</small>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="as-activity-icon"><i class="far fa-heart"></i></span>
                                        <div>
                                            <strong>Added favourite teacher</strong>
                                            <small>Olivia Martin · 3 hours ago</small>
                                        </div>
                                    </li>
                                    <li>
                                        <span class="as-activity-icon"><i class="far fa-credit-card"></i></span>
                                        <div>
                                            <strong>Payment completed</strong>
                                            <small>David Wilson · Today, 09:40 AM</small>
                                        </div>
                                    </li>
                                </ul>
                            </aside>

                        </div>

                        <!-- dashboard footer -->

                    </div>
                </div>
            </div>
        </div>
        <!-- admin students end -->

    

<!-- student detail modal -->
    <div class="modal fade gospel-demo-modal" id="as-student-modal" tabindex="-1" aria-labelledby="as-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="as-modal-title">Student Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body at-modal-body">
                    <div class="at-modal-hero">
                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="Student" id="as-modal-image">
                        <div>
                            <h4 id="as-modal-name">Sarah Johnson</h4>
                            <p id="as-modal-level">Intermediate</p>
                            <span class="sb-badge is-confirmed" id="as-modal-status">Active</span>
                        </div>
                    </div>
                    <ul class="booking-modal-list at-modal-list">
                        <li><span>Email</span><strong id="as-modal-email">sarah@email.com</strong></li>
                        <li><span>Phone</span><strong id="as-modal-phone">+1 555 123456</strong></li>
                        <li><span>Learning Level</span><strong id="as-modal-level-detail">Intermediate</strong></li>
                        <li><span>Preferred Instruments</span><strong id="as-modal-instruments">Gospel Piano</strong></li>
                        <li><span>Enrolled Classes</span><strong id="as-modal-classes">5</strong></li>
                        <li><span>Completed Lessons</span><strong id="as-modal-completed">24</strong></li>
                        <li><span>Registration Date</span><strong id="as-modal-joined">Jan 08, 2026</strong></li>
                    </ul>
                    <div class="at-modal-bio">
                        <h5>Bio</h5>
                        <p id="as-modal-bio">Student biography will appear here.</p>
                    </div>
                    <div class="as-modal-activity">
                        <h5>Recent Activity</h5>
                        <ul id="as-modal-activity-list">
                            <li>Booked new lesson</li>
                            <li>Completed class</li>
                            <li>Added favourite teacher</li>
                            <li>Payment completed</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" id="as-modal-edit">
                        <i class="far fa-pen"></i> Edit Student
                    </button>
                    <button type="button" class="theme-btn" id="as-modal-suspend">
                        <i class="far fa-ban"></i> Suspend Student
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- demo toast -->
    <div class="gospel-alert gospel-alert-success sl-toast" id="as-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="as-toast-text">Action completed (demo).</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

