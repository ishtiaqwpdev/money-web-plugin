<?php
/**
 * Template: admin-bookings
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

        <!-- admin bookings -->
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
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_teachers' ) ); ?>"><i class="far fa-user-plus"></i> <span><strong>John Smith</strong> applied as a teacher</span></a>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_classes' ) ); ?>"><i class="far fa-chalkboard"></i> <span>New class submitted for review</span></a>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_payments' ) ); ?>"><i class="far fa-credit-card"></i> <span>Payment of <strong>$40</strong> received</span></a>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_teachers' ) ); ?>"><i class="far fa-triangle-exclamation"></i> <span>15 approvals are pending</span></a>
                            </div>
                        </div>

                        <div class="dropdown ad-profile-dropdown">
                            <button class="ad-profile-btn" type="button" id="ad-profile-menu" data-bs-toggle="dropdown" aria-expanded="false">
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_classes' ) ); ?>" class="sd-nav-link" data-nav="classes"><i class="far fa-chalkboard"></i> Classes</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_bookings' ) ); ?>" class="sd-nav-link active" data-nav="bookings"><i class="far fa-calendar-check"></i> Bookings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_payments' ) ); ?>" class="sd-nav-link" data-nav="payments"><i class="far fa-credit-card"></i> Payments</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_programs' ) ); ?>" class="sd-nav-link" data-nav="programs"><i class="far fa-music"></i> Music Programs</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_settings' ) ); ?>" class="sd-nav-link" data-nav="settings"><i class="far fa-gear"></i> Settings</a></li>
                                <li><a href="admin-login.html" class="sd-nav-link sd-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
                            </ul>
                        </nav>
                    </aside>
                    <div class="sd-sidebar-backdrop" id="sd-sidebar-backdrop" hidden></div>

                    <div class="sd-main ad-main">

                        <section class="sd-card sd-welcome-card">
                            <div class="sd-card-head">
                                <div>
                                    <span class="login-portal-badge">Booking Management</span>
                                    <h3>Manage Bookings</h3>
                                    <p>Monitor student lesson bookings, teacher schedules, payment status, and lesson progress.</p>
                                </div>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'admin_dashboard' ) ); ?>" class="theme-btn theme-btn-outline"><i class="far fa-grid-2"></i> Dashboard</a>
                            </div>
                        </section>

                        <!-- stats -->
                        <section class="sd-stats-grid ab-stats-grid">
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-calendar-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="540">0</span>
                                    <span class="sd-stat-title">Total Bookings</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-calendar-days"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="120">0</span>
                                    <span class="sd-stat-title">Upcoming Lessons</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="350">0</span>
                                    <span class="sd-stat-title">Completed Lessons</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-xmark"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="40">0</span>
                                    <span class="sd-stat-title">Cancelled</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card is-pending">
                                <div class="sd-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="30">0</span>
                                    <span class="sd-stat-title">Pending</span>
                                </div>
                            </div>
                        </section>

                        <!-- analytics charts -->
                        <div class="gmm-chart-grid">
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Booking Analytics</h3>
                                        <p>Lesson bookings created each month.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-ab-analytics" aria-label="Booking analytics line chart"></canvas>
                                </div>
                            </section>
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Booking Status</h3>
                                        <p>Distribution of booking statuses.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap is-doughnut">
                                    <canvas id="gmm-ab-status" aria-label="Booking status doughnut chart"></canvas>
                                </div>
                            </section>
                        </div>

                        <div class="ab-content-grid">

                            <!-- bookings table -->
                            <section class="sd-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>All Bookings</h3>
                                        <p>Search and manage student lesson bookings.</p>
                                    </div>
                                    <span class="sf-count-pill" id="ab-result-count"><i class="far fa-calendar-check"></i> <strong>8</strong> Shown</span>
                                </div>

                                <form class="at-filter-bar" id="ab-filter-form" action="#" method="get">
                                    <div class="at-search-field">
                                        <i class="far fa-search" aria-hidden="true"></i>
                                        <input type="search" class="form-control" id="ab-search"
                                            placeholder="Search by student, teacher, or class..." autocomplete="off">
                                    </div>
                                    <div class="at-filter-selects">
                                        <div class="form-group mb-0">
                                            <label for="ab-status" class="visually-hidden">Booking Status</label>
                                            <select class="form-control form-select" id="ab-status">
                                                <option value="all">All Booking Status</option>
                                                <option value="pending">Pending</option>
                                                <option value="confirmed">Confirmed</option>
                                                <option value="completed">Completed</option>
                                                <option value="cancelled">Cancelled</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="ab-payment" class="visually-hidden">Payment Status</label>
                                            <select class="form-control form-select" id="ab-payment">
                                                <option value="all">All Payment Status</option>
                                                <option value="paid">Paid</option>
                                                <option value="pending">Pending</option>
                                                <option value="refunded">Refunded</option>
                                            </select>
                                        </div>
                                        <div class="form-group mb-0">
                                            <label for="ab-date" class="visually-hidden">Date</label>
                                            <select class="form-control form-select" id="ab-date">
                                                <option value="all">All Dates</option>
                                                <option value="today">Today</option>
                                                <option value="week">This Week</option>
                                                <option value="month">This Month</option>
                                            </select>
                                        </div>
                                        <button type="submit" class="theme-btn"><i class="far fa-filter"></i> Filter</button>
                                    </div>
                                </form>

                                <div class="table-responsive td-table-wrap" id="ab-table-wrap">
                                    <table class="table td-table sb-table at-table">
                                        <thead>
                                            <tr>
                                                <th>Student</th>
                                                <th>Teacher</th>
                                                <th>Class</th>
                                                <th>Date</th>
                                                <th>Time</th>
                                                <th>Payment</th>
                                                <th>Booking Status</th>
                                                <th>Action</th>
                                            </tr>
                                        </thead>
                                        <tbody id="ab-table-body">

                                            <tr class="ab-row"
                                                data-id="BK-1042"
                                                data-student="Sarah Johnson"
                                                data-teacher="John Smith"
                                                data-class="Beginner Gospel Piano"
                                                data-date="March 20, 2026"
                                                data-time="10:00 AM"
                                                data-duration="60 Minutes"
                                                data-amount="$40"
                                                data-payment="paid"
                                                data-status="confirmed"
                                                data-period="month"
                                                data-notes="Confirmed booking for worship piano fundamentals."
                                                data-student-img="assets/img/team/02.jpg"
                                                data-teacher-img="assets/img/team/01.jpg"
                                                data-phone="+1 555 123456"
                                                data-email="sarah@email.com">
                                                <td data-label="Student">
                                                    <div class="sb-teacher-cell">
                                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="Sarah Johnson">
                                                        <strong>Sarah Johnson</strong>
                                                    </div>
                                                </td>
                                                <td data-label="Teacher">John Smith</td>
                                                <td data-label="Class">Beginner Gospel Piano</td>
                                                <td data-label="Date">March 20, 2026</td>
                                                <td data-label="Time">10:00 AM</td>
                                                <td data-label="Payment"><span class="sb-badge is-confirmed ab-payment">Paid</span></td>
                                                <td data-label="Booking Status"><span class="sb-badge is-confirmed ab-status">Confirmed</span></td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-view-btn"><i class="far fa-eye"></i> <span>View Booking</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-student-btn"><i class="far fa-graduation-cap"></i> <span>View Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-teacher-btn"><i class="far fa-chalkboard-user"></i> <span>View Teacher</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-complete-btn"><i class="far fa-circle-check"></i> <span>Mark Completed</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-cancel-btn"><i class="far fa-circle-xmark"></i> <span>Cancel Booking</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-refund-btn"><i class="far fa-rotate-left"></i> <span>Refund Payment</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="ab-row"
                                                data-id="BK-1048"
                                                data-student="David Wilson"
                                                data-teacher="Emily Davis"
                                                data-class="Vocal Training"
                                                data-date="March 25, 2026"
                                                data-time="02:00 PM"
                                                data-duration="45 Minutes"
                                                data-amount="$50"
                                                data-payment="pending"
                                                data-status="pending"
                                                data-period="week"
                                                data-notes="Awaiting payment confirmation before lesson start."
                                                data-student-img="assets/img/team/05.jpg"
                                                data-teacher-img="assets/img/team/03.jpg"
                                                data-phone="+1 555 456789"
                                                data-email="david@email.com">
                                                <td data-label="Student">
                                                    <div class="sb-teacher-cell">
                                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/05.jpg' ) ); ?>" alt="David Wilson">
                                                        <strong>David Wilson</strong>
                                                    </div>
                                                </td>
                                                <td data-label="Teacher">Emily Davis</td>
                                                <td data-label="Class">Vocal Training</td>
                                                <td data-label="Date">March 25, 2026</td>
                                                <td data-label="Time">02:00 PM</td>
                                                <td data-label="Payment"><span class="sb-badge is-pending ab-payment">Pending</span></td>
                                                <td data-label="Booking Status"><span class="sb-badge is-pending ab-status">Pending</span></td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-view-btn"><i class="far fa-eye"></i> <span>View Booking</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-student-btn"><i class="far fa-graduation-cap"></i> <span>View Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-teacher-btn"><i class="far fa-chalkboard-user"></i> <span>View Teacher</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-complete-btn"><i class="far fa-circle-check"></i> <span>Mark Completed</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-cancel-btn"><i class="far fa-circle-xmark"></i> <span>Cancel Booking</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-refund-btn"><i class="far fa-rotate-left"></i> <span>Refund Payment</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="ab-row"
                                                data-id="BK-1031"
                                                data-student="Olivia Martin"
                                                data-teacher="John Smith"
                                                data-class="Advanced Worship Piano"
                                                data-date="March 18, 2026"
                                                data-time="11:00 AM"
                                                data-duration="60 Minutes"
                                                data-amount="$55"
                                                data-payment="paid"
                                                data-status="completed"
                                                data-period="week"
                                                data-notes="Lesson completed successfully. Student requested follow-up."
                                                data-student-img="assets/img/team/07.jpg"
                                                data-teacher-img="assets/img/team/01.jpg"
                                                data-phone="+1 555 444555"
                                                data-email="olivia@email.com">
                                                <td data-label="Student">
                                                    <div class="sb-teacher-cell">
                                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/07.jpg' ) ); ?>" alt="Olivia Martin">
                                                        <strong>Olivia Martin</strong>
                                                    </div>
                                                </td>
                                                <td data-label="Teacher">John Smith</td>
                                                <td data-label="Class">Advanced Worship Piano</td>
                                                <td data-label="Date">March 18, 2026</td>
                                                <td data-label="Time">11:00 AM</td>
                                                <td data-label="Payment"><span class="sb-badge is-confirmed ab-payment">Paid</span></td>
                                                <td data-label="Booking Status"><span class="sb-badge is-completed ab-status">Completed</span></td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-view-btn"><i class="far fa-eye"></i> <span>View Booking</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-student-btn"><i class="far fa-graduation-cap"></i> <span>View Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-teacher-btn"><i class="far fa-chalkboard-user"></i> <span>View Teacher</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-complete-btn"><i class="far fa-circle-check"></i> <span>Mark Completed</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-cancel-btn"><i class="far fa-circle-xmark"></i> <span>Cancel Booking</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-refund-btn"><i class="far fa-rotate-left"></i> <span>Refund Payment</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="ab-row"
                                                data-id="BK-1020"
                                                data-student="Michael Brown"
                                                data-teacher="David Wilson"
                                                data-class="Gospel Drum Grooves"
                                                data-date="March 10, 2026"
                                                data-time="04:00 PM"
                                                data-duration="55 Minutes"
                                                data-amount="$45"
                                                data-payment="refunded"
                                                data-status="cancelled"
                                                data-period="month"
                                                data-notes="Cancelled by student. Refund processed."
                                                data-student-img="assets/img/team/04.jpg"
                                                data-teacher-img="assets/img/team/05.jpg"
                                                data-phone="+1 555 987654"
                                                data-email="michael@email.com">
                                                <td data-label="Student">
                                                    <div class="sb-teacher-cell">
                                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/04.jpg' ) ); ?>" alt="Michael Brown">
                                                        <strong>Michael Brown</strong>
                                                    </div>
                                                </td>
                                                <td data-label="Teacher">David Wilson</td>
                                                <td data-label="Class">Gospel Drum Grooves</td>
                                                <td data-label="Date">March 10, 2026</td>
                                                <td data-label="Time">04:00 PM</td>
                                                <td data-label="Payment"><span class="sb-badge is-inactive ab-payment">Refunded</span></td>
                                                <td data-label="Booking Status"><span class="sb-badge is-cancelled ab-status">Cancelled</span></td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-view-btn"><i class="far fa-eye"></i> <span>View Booking</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-student-btn"><i class="far fa-graduation-cap"></i> <span>View Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-teacher-btn"><i class="far fa-chalkboard-user"></i> <span>View Teacher</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-complete-btn"><i class="far fa-circle-check"></i> <span>Mark Completed</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-cancel-btn"><i class="far fa-circle-xmark"></i> <span>Cancel Booking</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-refund-btn"><i class="far fa-rotate-left"></i> <span>Refund Payment</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="ab-row"
                                                data-id="BK-1055"
                                                data-student="James Carter"
                                                data-teacher="Michael Brown"
                                                data-class="Church Guitar Basics"
                                                data-date="March 22, 2026"
                                                data-time="09:00 AM"
                                                data-duration="50 Minutes"
                                                data-amount="$35"
                                                data-payment="paid"
                                                data-status="confirmed"
                                                data-period="today"
                                                data-notes="First guitar lesson — beginner level."
                                                data-student-img="assets/img/team/06.jpg"
                                                data-teacher-img="assets/img/team/04.jpg"
                                                data-phone="+1 555 666777"
                                                data-email="james.c@email.com">
                                                <td data-label="Student">
                                                    <div class="sb-teacher-cell">
                                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/06.jpg' ) ); ?>" alt="James Carter">
                                                        <strong>James Carter</strong>
                                                    </div>
                                                </td>
                                                <td data-label="Teacher">Michael Brown</td>
                                                <td data-label="Class">Church Guitar Basics</td>
                                                <td data-label="Date">March 22, 2026</td>
                                                <td data-label="Time">09:00 AM</td>
                                                <td data-label="Payment"><span class="sb-badge is-confirmed ab-payment">Paid</span></td>
                                                <td data-label="Booking Status"><span class="sb-badge is-confirmed ab-status">Confirmed</span></td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-view-btn"><i class="far fa-eye"></i> <span>View Booking</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-student-btn"><i class="far fa-graduation-cap"></i> <span>View Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-teacher-btn"><i class="far fa-chalkboard-user"></i> <span>View Teacher</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-complete-btn"><i class="far fa-circle-check"></i> <span>Mark Completed</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-cancel-btn"><i class="far fa-circle-xmark"></i> <span>Cancel Booking</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-refund-btn"><i class="far fa-rotate-left"></i> <span>Refund Payment</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="ab-row"
                                                data-id="BK-1050"
                                                data-student="Daniel Brooks"
                                                data-teacher="Daniel Brooks"
                                                data-class="Music Theory Fundamentals"
                                                data-date="March 21, 2026"
                                                data-time="03:30 PM"
                                                data-duration="40 Minutes"
                                                data-amount="$30"
                                                data-payment="paid"
                                                data-status="confirmed"
                                                data-period="week"
                                                data-notes="Theory focus on chord progressions."
                                                data-student-img="assets/img/team/08.jpg"
                                                data-teacher-img="assets/img/team/08.jpg"
                                                data-phone="+1 555 888999"
                                                data-email="daniel.b@email.com">
                                                <td data-label="Student">
                                                    <div class="sb-teacher-cell">
                                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/08.jpg' ) ); ?>" alt="Daniel Brooks">
                                                        <strong>Daniel Brooks</strong>
                                                    </div>
                                                </td>
                                                <td data-label="Teacher">Daniel Brooks</td>
                                                <td data-label="Class">Music Theory Fundamentals</td>
                                                <td data-label="Date">March 21, 2026</td>
                                                <td data-label="Time">03:30 PM</td>
                                                <td data-label="Payment"><span class="sb-badge is-confirmed ab-payment">Paid</span></td>
                                                <td data-label="Booking Status"><span class="sb-badge is-confirmed ab-status">Confirmed</span></td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-view-btn"><i class="far fa-eye"></i> <span>View Booking</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-student-btn"><i class="far fa-graduation-cap"></i> <span>View Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-teacher-btn"><i class="far fa-chalkboard-user"></i> <span>View Teacher</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-complete-btn"><i class="far fa-circle-check"></i> <span>Mark Completed</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-cancel-btn"><i class="far fa-circle-xmark"></i> <span>Cancel Booking</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-refund-btn"><i class="far fa-rotate-left"></i> <span>Refund Payment</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="ab-row"
                                                data-id="BK-1040"
                                                data-student="Emily Carter"
                                                data-teacher="Olivia Martin"
                                                data-class="Choir Harmony Coaching"
                                                data-date="March 15, 2026"
                                                data-time="01:00 PM"
                                                data-duration="50 Minutes"
                                                data-amount="$48"
                                                data-payment="paid"
                                                data-status="completed"
                                                data-period="month"
                                                data-notes="Harmony stacking session completed."
                                                data-student-img="assets/img/team/03.jpg"
                                                data-teacher-img="assets/img/team/07.jpg"
                                                data-phone="+1 555 222333"
                                                data-email="emily.c@email.com">
                                                <td data-label="Student">
                                                    <div class="sb-teacher-cell">
                                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/03.jpg' ) ); ?>" alt="Emily Carter">
                                                        <strong>Emily Carter</strong>
                                                    </div>
                                                </td>
                                                <td data-label="Teacher">Olivia Martin</td>
                                                <td data-label="Class">Choir Harmony Coaching</td>
                                                <td data-label="Date">March 15, 2026</td>
                                                <td data-label="Time">01:00 PM</td>
                                                <td data-label="Payment"><span class="sb-badge is-confirmed ab-payment">Paid</span></td>
                                                <td data-label="Booking Status"><span class="sb-badge is-completed ab-status">Completed</span></td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-view-btn"><i class="far fa-eye"></i> <span>View Booking</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-student-btn"><i class="far fa-graduation-cap"></i> <span>View Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-teacher-btn"><i class="far fa-chalkboard-user"></i> <span>View Teacher</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-complete-btn"><i class="far fa-circle-check"></i> <span>Mark Completed</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-cancel-btn"><i class="far fa-circle-xmark"></i> <span>Cancel Booking</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-refund-btn"><i class="far fa-rotate-left"></i> <span>Refund Payment</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                            <tr class="ab-row"
                                                data-id="BK-1058"
                                                data-student="Hannah Lee"
                                                data-teacher="John Smith"
                                                data-class="Beginner Gospel Piano"
                                                data-date="March 28, 2026"
                                                data-time="05:00 PM"
                                                data-duration="60 Minutes"
                                                data-amount="$40"
                                                data-payment="pending"
                                                data-status="pending"
                                                data-period="month"
                                                data-notes="New student booking — payment not yet received."
                                                data-student-img="assets/img/team/02.jpg"
                                                data-teacher-img="assets/img/team/01.jpg"
                                                data-phone="+1 555 101112"
                                                data-email="hannah@email.com">
                                                <td data-label="Student">
                                                    <div class="sb-teacher-cell">
                                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="Hannah Lee">
                                                        <strong>Hannah Lee</strong>
                                                    </div>
                                                </td>
                                                <td data-label="Teacher">John Smith</td>
                                                <td data-label="Class">Beginner Gospel Piano</td>
                                                <td data-label="Date">March 28, 2026</td>
                                                <td data-label="Time">05:00 PM</td>
                                                <td data-label="Payment"><span class="sb-badge is-pending ab-payment">Pending</span></td>
                                                <td data-label="Booking Status"><span class="sb-badge is-pending ab-status">Pending</span></td>
                                                <td data-label="Action">
                                                    <div class="dropdown at-action-dropdown">
                                                        <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                            <i class="far fa-ellipsis-vertical"></i>
                                                        </button>
                                                        <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-view-btn"><i class="far fa-eye"></i> <span>View Booking</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-student-btn"><i class="far fa-graduation-cap"></i> <span>View Student</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-teacher-btn"><i class="far fa-chalkboard-user"></i> <span>View Teacher</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-complete-btn"><i class="far fa-circle-check"></i> <span>Mark Completed</span></button></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-cancel-btn"><i class="far fa-circle-xmark"></i> <span>Cancel Booking</span></button></li>
                                                            <li><hr class="dropdown-divider"></li>
                                                            <li><button type="button" class="dropdown-item ad-dropdown-item ab-refund-btn"><i class="far fa-rotate-left"></i> <span>Refund Payment</span></button></li>
                                                        </ul>
                                                    </div>
                                                </td>
                                            </tr>

                                        </tbody>
                                    </table>
                                </div>

                                <div class="sl-empty" id="ab-empty" hidden>
                                    <i class="far fa-calendar-xmark"></i>
                                    <h3>No bookings available.</h3>
                                    <p>Try adjusting your search or filter options.</p>
                                </div>

                                <nav class="at-pagination" id="ab-pagination" aria-label="Bookings pagination">
                                    <ul class="pagination justify-content-center mb-0">
                                        <li class="page-item disabled" id="ab-page-prev">
                                            <a class="page-link" href="#" data-page="prev" aria-label="Previous"><i class="far fa-angle-left"></i> Previous</a>
                                        </li>
                                        <li class="page-item active"><a class="page-link" href="#" data-page="1">1</a></li>
                                        <li class="page-item"><a class="page-link" href="#" data-page="2">2</a></li>
                                        <li class="page-item"><a class="page-link" href="#" data-page="3">3</a></li>
                                        <li class="page-item" id="ab-page-next">
                                            <a class="page-link" href="#" data-page="next" aria-label="Next">Next <i class="far fa-angle-right"></i></a>
                                        </li>
                                    </ul>
                                </nav>
                            </section>

                            <!-- side column -->
                            <aside class="ab-side-col">
                                <section class="sd-card ab-calendar-card">
                                    <div class="sd-card-head">
                                        <div>
                                            <h3>Booking Calendar</h3>
                                            <p>March 2026 scheduled lessons.</p>
                                        </div>
                                    </div>
                                    <div class="ab-calendar" id="ab-calendar" role="group" aria-label="Booking calendar demo">
                                        <div class="ab-cal-weekdays">
                                            <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                        </div>
                                        <div class="ab-cal-days" id="ab-cal-days">
                                            <!-- filled by JS -->
                                        </div>
                                    </div>
                                    <ul class="ab-cal-legend">
                                        <li><span class="ab-dot is-confirmed"></span> Confirmed</li>
                                        <li><span class="ab-dot is-pending"></span> Pending</li>
                                        <li><span class="ab-dot is-selected"></span> Selected</li>
                                    </ul>
                                    <div class="ab-cal-selected" id="ab-cal-selected">
                                        <strong>Select a date</strong>
                                        <p>Click a highlighted day to preview scheduled lessons.</p>
                                    </div>
                                </section>

                                <section class="sd-card">
                                    <div class="sd-card-head">
                                        <div>
                                            <h3>Recent Booking Activity</h3>
                                            <p>Latest booking events.</p>
                                        </div>
                                    </div>
                                    <ul class="ad-timeline ab-activity-timeline">
                                        <li class="ad-timeline-item is-class">
                                            <span class="ad-timeline-icon"><i class="far fa-calendar-plus"></i></span>
                                            <div class="ad-timeline-body">
                                                <h4>New booking created</h4>
                                                <p>Sarah Johnson booked Beginner Gospel Piano.</p>
                                            </div>
                                            <span class="ad-timeline-time">12 min ago</span>
                                        </li>
                                        <li class="ad-timeline-item is-payment">
                                            <span class="ad-timeline-icon"><i class="far fa-credit-card"></i></span>
                                            <div class="ad-timeline-body">
                                                <h4>Payment received</h4>
                                                <p>$40 paid for booking BK-1042.</p>
                                            </div>
                                            <span class="ad-timeline-time">45 min ago</span>
                                        </li>
                                        <li class="ad-timeline-item is-teacher">
                                            <span class="ad-timeline-icon"><i class="far fa-circle-check"></i></span>
                                            <div class="ad-timeline-body">
                                                <h4>Lesson completed</h4>
                                                <p>Olivia Martin finished Advanced Worship Piano.</p>
                                            </div>
                                            <span class="ad-timeline-time">2 hours ago</span>
                                        </li>
                                        <li class="ad-timeline-item is-student">
                                            <span class="ad-timeline-icon"><i class="far fa-circle-xmark"></i></span>
                                            <div class="ad-timeline-body">
                                                <h4>Booking cancelled</h4>
                                                <p>Michael Brown cancelled Gospel Drum Grooves.</p>
                                            </div>
                                            <span class="ad-timeline-time">Yesterday</span>
                                        </li>
                                    </ul>
                                </section>
                            </aside>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <!-- admin bookings end -->

    

<!-- booking detail modal -->
    <div class="modal fade gospel-demo-modal" id="ab-booking-modal" tabindex="-1" aria-labelledby="ab-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ab-modal-title">Booking Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body at-modal-body">
                    <div class="ab-modal-parties">
                        <div class="ab-modal-party">
                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="Student" id="ab-modal-student-img">
                            <div>
                                <small>Student</small>
                                <strong id="ab-modal-student">Sarah Johnson</strong>
                                <span id="ab-modal-email">sarah@email.com</span>
                            </div>
                        </div>
                        <div class="ab-modal-party">
                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="Teacher" id="ab-modal-teacher-img">
                            <div>
                                <small>Teacher</small>
                                <strong id="ab-modal-teacher">John Smith</strong>
                                <span id="ab-modal-phone">+1 555 123456</span>
                            </div>
                        </div>
                    </div>
                    <ul class="booking-modal-list at-modal-list">
                        <li><span>Booking ID</span><strong id="ab-modal-id">BK-1042</strong></li>
                        <li><span>Class Details</span><strong id="ab-modal-class">Beginner Gospel Piano</strong></li>
                        <li><span>Date</span><strong id="ab-modal-date">March 20, 2026</strong></li>
                        <li><span>Time</span><strong id="ab-modal-time">10:00 AM</strong></li>
                        <li><span>Duration</span><strong id="ab-modal-duration">60 Minutes</strong></li>
                        <li><span>Payment Amount</span><strong id="ab-modal-amount">$40</strong></li>
                        <li><span>Payment Status</span><strong id="ab-modal-payment-wrap"><span class="sb-badge is-confirmed" id="ab-modal-payment">Paid</span></strong></li>
                        <li><span>Booking Status</span><strong id="ab-modal-status-wrap"><span class="sb-badge is-confirmed" id="ab-modal-status">Confirmed</span></strong></li>
                    </ul>
                    <div class="at-modal-bio">
                        <h5>Notes</h5>
                        <p id="ab-modal-notes">Booking notes will appear here.</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" id="ab-modal-cancel">
                        <i class="far fa-circle-xmark"></i> Cancel Booking
                    </button>
                    <button type="button" class="theme-btn theme-btn-outline" id="ab-modal-confirm">
                        <i class="far fa-check"></i> Confirm Booking
                    </button>
                    <button type="button" class="theme-btn" id="ab-modal-complete">
                        <i class="far fa-circle-check"></i> Mark Complete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="gospel-alert gospel-alert-success sl-toast" id="ab-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="ab-toast-text">Action completed (demo).</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

