<?php
/**
 * Template: admin-teachers
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

        <!-- admin teachers -->
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
                                        <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_teachers' ) ); ?>" class="sd-nav-link ad-sub-link active" data-nav="teachers"><i class="far fa-chalkboard-user"></i> Teachers</a></li>
                                        <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_students' ) ); ?>" class="sd-nav-link ad-sub-link" data-nav="students"><i class="far fa-graduation-cap"></i> Students</a></li>
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
                                    <h3>Manage Teachers</h3>
                                    <p>Review teacher applications, manage instructor accounts, and monitor teaching activity.</p>
                                </div>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'admin_dashboard' ) ); ?>" class="theme-btn theme-btn-outline"><i class="far fa-grid-2"></i> Dashboard</a>
                            </div>
                        </section>

                        <!-- summary cards -->
                        <section class="sd-stats-grid ad-stats-grid at-stats-grid">
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-chalkboard-user"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="85" data-format="number">0</span>
                                    <span class="sd-stat-title">Total Teachers</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card is-pending">
                                <div class="sd-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="15" data-format="number">0</span>
                                    <span class="sd-stat-title">Pending Approval</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="65" data-format="number">0</span>
                                    <span class="sd-stat-title">Approved Teachers</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-ban"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="5" data-format="number">0</span>
                                    <span class="sd-stat-title">Suspended</span>
                                </div>
                            </div>
                        </section>

                        <!-- analytics charts -->
                        <div class="gmm-chart-grid">
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Teacher Registration Analytics</h3>
                                        <p>New teacher registrations each month.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-at-registration" aria-label="Teacher registration line chart"></canvas>
                                </div>
                            </section>
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Teacher Status Distribution</h3>
                                        <p>Breakdown of teacher account statuses.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap is-doughnut">
                                    <canvas id="gmm-at-status" aria-label="Teacher status doughnut chart"></canvas>
                                </div>
                            </section>
                        </div>

                        <!-- teachers table card -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>All Teachers</h3>
                                    <p>Search, filter, and manage instructor accounts.</p>
                                </div>
                                <span class="sf-count-pill" id="at-result-count"><i class="far fa-chalkboard-user"></i> <strong>8</strong> Shown</span>
                            </div>

                            <!-- search & filters -->
                            <form class="at-filter-bar" id="at-filter-form" action="#" method="get">
                                <div class="at-search-field">
                                    <i class="far fa-search" aria-hidden="true"></i>
                                    <input type="search" class="form-control" id="at-search"
                                        placeholder="Search teachers by name or email..." autocomplete="off">
                                </div>
                                <div class="at-filter-selects">
                                    <div class="form-group mb-0">
                                        <label for="at-status" class="visually-hidden">Status</label>
                                        <select class="form-control form-select" id="at-status">
                                            <option value="all">All Status</option>
                                            <option value="pending">Pending</option>
                                            <option value="approved">Approved</option>
                                            <option value="rejected">Rejected</option>
                                            <option value="suspended">Suspended</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="at-specialty" class="visually-hidden">Specialization</label>
                                        <select class="form-control form-select" id="at-specialty">
                                            <option value="all">All Specializations</option>
                                            <option value="piano">Piano</option>
                                            <option value="vocals">Vocals</option>
                                            <option value="guitar">Guitar</option>
                                            <option value="drums">Drums</option>
                                            <option value="theory">Music Theory</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="theme-btn" id="at-filter-btn">
                                        <i class="far fa-filter"></i> Filter
                                    </button>
                                </div>
                            </form>

                            <div class="table-responsive td-table-wrap" id="at-table-wrap">
                                <table class="table td-table sb-table at-table">
                                    <thead>
                                        <tr>
                                            <th>Profile</th>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Specialization</th>
                                            <th>Students</th>
                                            <th>Rating</th>
                                            <th>Status</th>
                                            <th>Joined Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="at-table-body">

                                        <tr class="at-row"
                                            data-name="John Smith"
                                            data-email="john@email.com"
                                            data-status="approved"
                                            data-specialty="piano"
                                            data-phone="+1 615 555 0101"
                                            data-experience="10+ Years"
                                            data-rating="4.9"
                                            data-classes="12"
                                            data-students="25"
                                            data-bio="Experienced gospel piano instructor helping students grow in worship accompaniment and musical confidence."
                                            data-image="assets/img/team/01.jpg"
                                            data-joined="Jan 12, 2025">
                                            <td data-label="Profile">
                                                <img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="John Smith">
                                            </td>
                                            <td data-label="Name"><strong>John Smith</strong></td>
                                            <td data-label="Email">john@email.com</td>
                                            <td data-label="Specialization">Gospel Piano</td>
                                            <td data-label="Students">25 Students</td>
                                            <td data-label="Rating"><span class="td-rating">★★★★★</span> 4.9</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed at-status">Approved</span></td>
                                            <td data-label="Joined Date">Jan 12, 2025</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for John Smith">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-approve-btn"><i class="far fa-circle-check"></i> <span>Approve</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-reject-btn"><i class="far fa-circle-xmark"></i> <span>Reject</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-suspend-btn"><i class="far fa-ban"></i> <span>Suspend</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item is-logout at-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="at-row"
                                            data-name="Emily Davis"
                                            data-email="emily@email.com"
                                            data-status="pending"
                                            data-specialty="vocals"
                                            data-phone="+1 615 555 0102"
                                            data-experience="8 Years"
                                            data-rating="4.8"
                                            data-classes="6"
                                            data-students="18"
                                            data-bio="Vocal coach specializing in worship leadership and contemporary gospel singing techniques."
                                            data-image="assets/img/team/03.jpg"
                                            data-joined="Mar 02, 2026">
                                            <td data-label="Profile">
                                                <img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/03.jpg' ) ); ?>" alt="Emily Davis">
                                            </td>
                                            <td data-label="Name"><strong>Emily Davis</strong></td>
                                            <td data-label="Email">emily@email.com</td>
                                            <td data-label="Specialization">Vocal Training</td>
                                            <td data-label="Students">18 Students</td>
                                            <td data-label="Rating"><span class="td-rating">★★★★★</span> 4.8</td>
                                            <td data-label="Status"><span class="sb-badge is-pending at-status">Pending</span></td>
                                            <td data-label="Joined Date">Mar 02, 2026</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for Emily Davis">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-approve-btn"><i class="far fa-circle-check"></i> <span>Approve</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-reject-btn"><i class="far fa-circle-xmark"></i> <span>Reject</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-suspend-btn"><i class="far fa-ban"></i> <span>Suspend</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item is-logout at-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="at-row"
                                            data-name="Michael Brown"
                                            data-email="michael@email.com"
                                            data-status="suspended"
                                            data-specialty="guitar"
                                            data-phone="+1 615 555 0103"
                                            data-experience="6 Years"
                                            data-rating="4.7"
                                            data-classes="4"
                                            data-students="10"
                                            data-bio="Acoustic and electric guitar instructor focused on gospel grooves and worship band skills."
                                            data-image="assets/img/team/04.jpg"
                                            data-joined="Nov 18, 2024">
                                            <td data-label="Profile">
                                                <img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/04.jpg' ) ); ?>" alt="Michael Brown">
                                            </td>
                                            <td data-label="Name"><strong>Michael Brown</strong></td>
                                            <td data-label="Email">michael@email.com</td>
                                            <td data-label="Specialization">Guitar</td>
                                            <td data-label="Students">10 Students</td>
                                            <td data-label="Rating"><span class="td-rating">★★★★★</span> 4.7</td>
                                            <td data-label="Status"><span class="sb-badge is-suspended at-status">Suspended</span></td>
                                            <td data-label="Joined Date">Nov 18, 2024</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for Michael Brown">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-approve-btn"><i class="far fa-circle-check"></i> <span>Approve</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-reject-btn"><i class="far fa-circle-xmark"></i> <span>Reject</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-suspend-btn"><i class="far fa-ban"></i> <span>Suspend</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item is-logout at-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="at-row"
                                            data-name="David Wilson"
                                            data-email="david@email.com"
                                            data-status="approved"
                                            data-specialty="drums"
                                            data-phone="+1 615 555 0104"
                                            data-experience="12 Years"
                                            data-rating="4.9"
                                            data-classes="8"
                                            data-students="22"
                                            data-bio="Worship drummer teaching groove, tempo control, and live church set dynamics."
                                            data-image="assets/img/team/05.jpg"
                                            data-joined="Aug 05, 2024">
                                            <td data-label="Profile">
                                                <img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/05.jpg' ) ); ?>" alt="David Wilson">
                                            </td>
                                            <td data-label="Name"><strong>David Wilson</strong></td>
                                            <td data-label="Email">david@email.com</td>
                                            <td data-label="Specialization">Drums</td>
                                            <td data-label="Students">22 Students</td>
                                            <td data-label="Rating"><span class="td-rating">★★★★★</span> 4.9</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed at-status">Approved</span></td>
                                            <td data-label="Joined Date">Aug 05, 2024</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for David Wilson">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-approve-btn"><i class="far fa-circle-check"></i> <span>Approve</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-reject-btn"><i class="far fa-circle-xmark"></i> <span>Reject</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-suspend-btn"><i class="far fa-ban"></i> <span>Suspend</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item is-logout at-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="at-row"
                                            data-name="Sarah Lee"
                                            data-email="sarah.lee@email.com"
                                            data-status="rejected"
                                            data-specialty="theory"
                                            data-phone="+1 615 555 0105"
                                            data-experience="4 Years"
                                            data-rating="4.5"
                                            data-classes="2"
                                            data-students="5"
                                            data-bio="Music theory educator focused on chord progressions and gospel harmony fundamentals."
                                            data-image="assets/img/team/02.jpg"
                                            data-joined="Feb 20, 2026">
                                            <td data-label="Profile">
                                                <img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="Sarah Lee">
                                            </td>
                                            <td data-label="Name"><strong>Sarah Lee</strong></td>
                                            <td data-label="Email">sarah.lee@email.com</td>
                                            <td data-label="Specialization">Music Theory</td>
                                            <td data-label="Students">5 Students</td>
                                            <td data-label="Rating"><span class="td-rating">★★★★☆</span> 4.5</td>
                                            <td data-label="Status"><span class="sb-badge is-cancelled at-status">Rejected</span></td>
                                            <td data-label="Joined Date">Feb 20, 2026</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for Sarah Lee">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-approve-btn"><i class="far fa-circle-check"></i> <span>Approve</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-reject-btn"><i class="far fa-circle-xmark"></i> <span>Reject</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-suspend-btn"><i class="far fa-ban"></i> <span>Suspend</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item is-logout at-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="at-row"
                                            data-name="James Carter"
                                            data-email="james@email.com"
                                            data-status="pending"
                                            data-specialty="piano"
                                            data-phone="+1 615 555 0106"
                                            data-experience="5 Years"
                                            data-rating="4.6"
                                            data-classes="3"
                                            data-students="9"
                                            data-bio="Beginner-friendly gospel piano teacher specializing in chords and hymn arrangements."
                                            data-image="assets/img/team/06.jpg"
                                            data-joined="Mar 10, 2026">
                                            <td data-label="Profile">
                                                <img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/06.jpg' ) ); ?>" alt="James Carter">
                                            </td>
                                            <td data-label="Name"><strong>James Carter</strong></td>
                                            <td data-label="Email">james@email.com</td>
                                            <td data-label="Specialization">Gospel Piano</td>
                                            <td data-label="Students">9 Students</td>
                                            <td data-label="Rating"><span class="td-rating">★★★★★</span> 4.6</td>
                                            <td data-label="Status"><span class="sb-badge is-pending at-status">Pending</span></td>
                                            <td data-label="Joined Date">Mar 10, 2026</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for James Carter">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-approve-btn"><i class="far fa-circle-check"></i> <span>Approve</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-reject-btn"><i class="far fa-circle-xmark"></i> <span>Reject</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-suspend-btn"><i class="far fa-ban"></i> <span>Suspend</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item is-logout at-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="at-row"
                                            data-name="Olivia Martin"
                                            data-email="olivia@email.com"
                                            data-status="approved"
                                            data-specialty="vocals"
                                            data-phone="+1 615 555 0107"
                                            data-experience="9 Years"
                                            data-rating="5.0"
                                            data-classes="10"
                                            data-students="30"
                                            data-bio="Award-winning worship vocalist coaching range, breath control, and stage presence."
                                            data-image="assets/img/team/07.jpg"
                                            data-joined="Jun 01, 2024">
                                            <td data-label="Profile">
                                                <img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/07.jpg' ) ); ?>" alt="Olivia Martin">
                                            </td>
                                            <td data-label="Name"><strong>Olivia Martin</strong></td>
                                            <td data-label="Email">olivia@email.com</td>
                                            <td data-label="Specialization">Vocal Training</td>
                                            <td data-label="Students">30 Students</td>
                                            <td data-label="Rating"><span class="td-rating">★★★★★</span> 5.0</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed at-status">Approved</span></td>
                                            <td data-label="Joined Date">Jun 01, 2024</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for Olivia Martin">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-approve-btn"><i class="far fa-circle-check"></i> <span>Approve</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-reject-btn"><i class="far fa-circle-xmark"></i> <span>Reject</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-suspend-btn"><i class="far fa-ban"></i> <span>Suspend</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item is-logout at-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="at-row"
                                            data-name="Daniel Brooks"
                                            data-email="daniel@email.com"
                                            data-status="approved"
                                            data-specialty="theory"
                                            data-phone="+1 615 555 0108"
                                            data-experience="7 Years"
                                            data-rating="4.8"
                                            data-classes="7"
                                            data-students="14"
                                            data-bio="Teaches music theory for worship teams with practical songwriting applications."
                                            data-image="assets/img/team/08.jpg"
                                            data-joined="Sep 22, 2025">
                                            <td data-label="Profile">
                                                <img class="at-avatar" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/08.jpg' ) ); ?>" alt="Daniel Brooks">
                                            </td>
                                            <td data-label="Name"><strong>Daniel Brooks</strong></td>
                                            <td data-label="Email">daniel@email.com</td>
                                            <td data-label="Specialization">Music Theory</td>
                                            <td data-label="Students">14 Students</td>
                                            <td data-label="Rating"><span class="td-rating">★★★★★</span> 4.8</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed at-status">Approved</span></td>
                                            <td data-label="Joined Date">Sep 22, 2025</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions for Daniel Brooks">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-view-btn"><i class="far fa-eye"></i> <span>View Profile</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-approve-btn"><i class="far fa-circle-check"></i> <span>Approve</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-reject-btn"><i class="far fa-circle-xmark"></i> <span>Reject</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item at-suspend-btn"><i class="far fa-ban"></i> <span>Suspend</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item is-logout at-delete-btn"><i class="far fa-trash-alt"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>

                            <div class="sl-empty" id="at-empty" hidden>
                                <i class="far fa-chalkboard-user"></i>
                                <h3>No teachers found.</h3>
                                <p>Try adjusting your search or filter options.</p>
                            </div>

                            <!-- pagination -->
                            <nav class="at-pagination" id="at-pagination" aria-label="Teachers pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item disabled" id="at-page-prev">
                                        <a class="page-link" href="#" data-page="prev" aria-label="Previous"><i class="far fa-angle-left"></i> Previous</a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#" data-page="1">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#" data-page="2">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#" data-page="3">3</a></li>
                                    <li class="page-item" id="at-page-next">
                                        <a class="page-link" href="#" data-page="next" aria-label="Next">Next <i class="far fa-angle-right"></i></a>
                                    </li>
                                </ul>
                            </nav>
                        </section>

                        <!-- dashboard footer -->

                    </div>
                </div>
            </div>
        </div>
        <!-- admin teachers end -->

    

<!-- teacher detail modal -->
    <div class="modal fade gospel-demo-modal" id="at-teacher-modal" tabindex="-1" aria-labelledby="at-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="at-modal-title">Teacher Profile</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body at-modal-body">
                    <div class="at-modal-hero">
                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="Teacher" id="at-modal-image">
                        <div>
                            <h4 id="at-modal-name">John Smith</h4>
                            <p id="at-modal-specialty">Gospel Piano</p>
                            <span class="td-rating" id="at-modal-rating">★★★★★ 4.9</span>
                            <span class="sb-badge is-pending" id="at-modal-status">Pending</span>
                        </div>
                    </div>
                    <ul class="booking-modal-list at-modal-list">
                        <li><span>Email</span><strong id="at-modal-email">john@email.com</strong></li>
                        <li><span>Phone</span><strong id="at-modal-phone">+1 615 555 0101</strong></li>
                        <li><span>Experience</span><strong id="at-modal-experience">10+ Years</strong></li>
                        <li><span>Classes</span><strong id="at-modal-classes">12</strong></li>
                        <li><span>Students</span><strong id="at-modal-students">25</strong></li>
                        <li><span>Joined</span><strong id="at-modal-joined">Jan 12, 2025</strong></li>
                    </ul>
                    <div class="at-modal-bio">
                        <h5>Bio</h5>
                        <p id="at-modal-bio">Teacher biography will appear here.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" id="at-modal-reject">
                        <i class="far fa-circle-xmark"></i> Reject Teacher
                    </button>
                    <button type="button" class="theme-btn" id="at-modal-approve">
                        <i class="far fa-circle-check"></i> Approve Teacher
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- demo toast -->
    <div class="gospel-alert gospel-alert-success sl-toast" id="at-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="at-toast-text">Action completed (demo).</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

