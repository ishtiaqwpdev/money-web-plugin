<?php
/**
 * Template: admin-payments
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

        <!-- admin payments -->
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_bookings' ) ); ?>" class="sd-nav-link" data-nav="bookings"><i class="far fa-calendar-check"></i> Bookings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_payments' ) ); ?>" class="sd-nav-link active" data-nav="payments"><i class="far fa-credit-card"></i> Payments</a></li>
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
                                    <span class="login-portal-badge">Financial Management</span>
                                    <h3>Payments &amp; Earnings</h3>
                                    <p>Monitor platform revenue, teacher payouts, student payments and financial transactions.</p>
                                </div>
                                <div class="ap-export-actions">
                                    <button type="button" class="theme-btn theme-btn-outline" id="ap-export-csv">
                                        <i class="far fa-file-csv"></i> Export CSV
                                    </button>
                                    <button type="button" class="theme-btn" id="ap-generate-report">
                                        <i class="far fa-file-chart-column"></i> Generate Report
                                    </button>
                                </div>
                            </div>
                        </section>

                        <!-- revenue stats -->
                        <section class="sd-stats-grid ad-stats-grid">
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-dollar-sign"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="50000" data-format="currency">$0</span>
                                    <span class="sd-stat-title">Total Revenue</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-percent"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="10000" data-format="currency">$0</span>
                                    <span class="sd-stat-title">Platform Commission</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-chalkboard-user"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="40000" data-format="currency">$0</span>
                                    <span class="sd-stat-title">Teacher Earnings</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card is-pending">
                                <div class="sd-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="5000" data-format="currency">$0</span>
                                    <span class="sd-stat-title">Pending Payouts</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-receipt"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="850" data-format="number">0</span>
                                    <span class="sd-stat-title">Completed Transactions</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-rotate-left"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="25" data-format="number">0</span>
                                    <span class="sd-stat-title">Refunds</span>
                                </div>
                            </div>
                        </section>

                        <!-- earnings summary -->
                        <div class="ap-earn-grid">
                            <section class="sd-card ap-earn-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Teacher Earnings</h3>
                                        <p>Paid and pending teacher payouts.</p>
                                    </div>
                                    <span class="sd-stat-icon ap-earn-icon"><i class="far fa-chalkboard-user"></i></span>
                                </div>
                                <ul class="ap-earn-list">
                                    <li>
                                        <span>Total Paid</span>
                                        <strong>$40,000</strong>
                                    </li>
                                    <li>
                                        <span>Pending</span>
                                        <strong class="is-pending">$5,000</strong>
                                    </li>
                                </ul>
                            </section>

                            <section class="sd-card ap-earn-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Platform Earnings</h3>
                                        <p>Commission retained by the platform.</p>
                                    </div>
                                    <span class="sd-stat-icon ap-earn-icon"><i class="far fa-building-columns"></i></span>
                                </div>
                                <ul class="ap-earn-list">
                                    <li>
                                        <span>Commission</span>
                                        <strong>$10,000</strong>
                                    </li>
                                    <li>
                                        <span>Share of Revenue</span>
                                        <strong>20%</strong>
                                    </li>
                                </ul>
                            </section>
                        </div>

                        <!-- charts -->
                        <div class="ap-top-grid gmm-chart-grid">
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Revenue Chart</h3>
                                        <p>Monthly platform revenue for the year.</p>
                                    </div>
                                    <span class="ad-chart-total">$50,000</span>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-ap-revenue" aria-label="Revenue area chart"></canvas>
                                </div>
                            </section>

                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Payment Status</h3>
                                        <p>Completed, pending, failed, and refunded.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap is-doughnut">
                                    <canvas id="gmm-ap-status" aria-label="Payment status doughnut chart"></canvas>
                                </div>
                            </section>
                        </div>

                        <!-- transactions -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Transactions</h3>
                                    <p>Search and filter all platform payment activity.</p>
                                </div>
                                <span class="sf-count-pill" id="ap-result-count"><i class="far fa-receipt"></i> <strong>8</strong> Shown</span>
                            </div>

                            <form class="at-filter-bar" id="ap-filter-form" action="#" method="get">
                                <div class="at-search-field">
                                    <i class="far fa-search" aria-hidden="true"></i>
                                    <input type="search" class="form-control" id="ap-search"
                                        placeholder="Search transaction ID or user..." autocomplete="off">
                                </div>
                                <div class="at-filter-selects">
                                    <div class="form-group mb-0">
                                        <label for="ap-status" class="visually-hidden">Payment Status</label>
                                        <select class="form-control form-select" id="ap-status">
                                            <option value="all">All Payment Status</option>
                                            <option value="completed">Completed</option>
                                            <option value="pending">Pending</option>
                                            <option value="failed">Failed</option>
                                            <option value="refunded">Refunded</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="ap-type" class="visually-hidden">Payment Type</label>
                                        <select class="form-control form-select" id="ap-type">
                                            <option value="all">All Payment Types</option>
                                            <option value="lesson">Lesson Payment</option>
                                            <option value="payout">Teacher Payout</option>
                                            <option value="refund">Refund</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="ap-date" class="visually-hidden">Date</label>
                                        <select class="form-control form-select" id="ap-date">
                                            <option value="all">All Dates</option>
                                            <option value="today">Today</option>
                                            <option value="week">This Week</option>
                                            <option value="month">This Month</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="theme-btn"><i class="far fa-filter"></i> Filter</button>
                                </div>
                            </form>

                            <div class="table-responsive td-table-wrap" id="ap-table-wrap">
                                <table class="table td-table sb-table at-table">
                                    <thead>
                                        <tr>
                                            <th>Transaction ID</th>
                                            <th>User</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Payment Method</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ap-table-body">

                                        <tr class="ap-row"
                                            data-id="TXN-1001"
                                            data-user="Sarah Johnson"
                                            data-type="lesson"
                                            data-type-label="Lesson Payment"
                                            data-amount="$40"
                                            data-method="Stripe"
                                            data-status="completed"
                                            data-date="March 20, 2026"
                                            data-period="month"
                                            data-email="sarah@email.com"
                                            data-user-img="assets/img/team/02.jpg"
                                            data-booking="BK-1042">
                                            <td data-label="Transaction ID"><strong>TXN-1001</strong></td>
                                            <td data-label="User">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="Sarah Johnson">
                                                    <strong>Sarah Johnson</strong>
                                                </div>
                                            </td>
                                            <td data-label="Type">Lesson Payment</td>
                                            <td data-label="Amount"><strong>$40</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed ap-status">Completed</span></td>
                                            <td data-label="Date">March 20, 2026</td>
                                            <td data-label="Action">
                                                <button type="button" class="at-action-btn ap-view-btn" aria-label="View transaction">
                                                    <i class="far fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <tr class="ap-row"
                                            data-id="TXN-1002"
                                            data-user="John Smith"
                                            data-type="payout"
                                            data-type-label="Teacher Payout"
                                            data-amount="$250"
                                            data-method="Stripe"
                                            data-status="pending"
                                            data-date="March 25, 2026"
                                            data-period="week"
                                            data-email="john@email.com"
                                            data-user-img="assets/img/team/01.jpg"
                                            data-booking="—">
                                            <td data-label="Transaction ID"><strong>TXN-1002</strong></td>
                                            <td data-label="User">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="John Smith">
                                                    <strong>John Smith</strong>
                                                </div>
                                            </td>
                                            <td data-label="Type">Teacher Payout</td>
                                            <td data-label="Amount"><strong>$250</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="sb-badge is-pending ap-status">Pending</span></td>
                                            <td data-label="Date">March 25, 2026</td>
                                            <td data-label="Action">
                                                <button type="button" class="at-action-btn ap-view-btn" aria-label="View transaction">
                                                    <i class="far fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <tr class="ap-row"
                                            data-id="TXN-1003"
                                            data-user="David Wilson"
                                            data-type="lesson"
                                            data-type-label="Lesson Payment"
                                            data-amount="$50"
                                            data-method="Stripe"
                                            data-status="pending"
                                            data-date="March 25, 2026"
                                            data-period="week"
                                            data-email="david@email.com"
                                            data-user-img="assets/img/team/05.jpg"
                                            data-booking="BK-1048">
                                            <td data-label="Transaction ID"><strong>TXN-1003</strong></td>
                                            <td data-label="User">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/05.jpg' ) ); ?>" alt="David Wilson">
                                                    <strong>David Wilson</strong>
                                                </div>
                                            </td>
                                            <td data-label="Type">Lesson Payment</td>
                                            <td data-label="Amount"><strong>$50</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="sb-badge is-pending ap-status">Pending</span></td>
                                            <td data-label="Date">March 25, 2026</td>
                                            <td data-label="Action">
                                                <button type="button" class="at-action-btn ap-view-btn" aria-label="View transaction">
                                                    <i class="far fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <tr class="ap-row"
                                            data-id="TXN-1004"
                                            data-user="Olivia Martin"
                                            data-type="lesson"
                                            data-type-label="Lesson Payment"
                                            data-amount="$55"
                                            data-method="Stripe"
                                            data-status="completed"
                                            data-date="March 18, 2026"
                                            data-period="week"
                                            data-email="olivia@email.com"
                                            data-user-img="assets/img/team/07.jpg"
                                            data-booking="BK-1031">
                                            <td data-label="Transaction ID"><strong>TXN-1004</strong></td>
                                            <td data-label="User">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/07.jpg' ) ); ?>" alt="Olivia Martin">
                                                    <strong>Olivia Martin</strong>
                                                </div>
                                            </td>
                                            <td data-label="Type">Lesson Payment</td>
                                            <td data-label="Amount"><strong>$55</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed ap-status">Completed</span></td>
                                            <td data-label="Date">March 18, 2026</td>
                                            <td data-label="Action">
                                                <button type="button" class="at-action-btn ap-view-btn" aria-label="View transaction">
                                                    <i class="far fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <tr class="ap-row"
                                            data-id="TXN-1005"
                                            data-user="Michael Brown"
                                            data-type="refund"
                                            data-type-label="Refund"
                                            data-amount="$45"
                                            data-method="Stripe"
                                            data-status="refunded"
                                            data-date="March 12, 2026"
                                            data-period="month"
                                            data-email="michael@email.com"
                                            data-user-img="assets/img/team/04.jpg"
                                            data-booking="BK-1020">
                                            <td data-label="Transaction ID"><strong>TXN-1005</strong></td>
                                            <td data-label="User">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/04.jpg' ) ); ?>" alt="Michael Brown">
                                                    <strong>Michael Brown</strong>
                                                </div>
                                            </td>
                                            <td data-label="Type">Refund</td>
                                            <td data-label="Amount"><strong>$45</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="sb-badge is-inactive ap-status">Refunded</span></td>
                                            <td data-label="Date">March 12, 2026</td>
                                            <td data-label="Action">
                                                <button type="button" class="at-action-btn ap-view-btn" aria-label="View transaction">
                                                    <i class="far fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <tr class="ap-row"
                                            data-id="TXN-1006"
                                            data-user="Emily Davis"
                                            data-type="payout"
                                            data-type-label="Teacher Payout"
                                            data-amount="$320"
                                            data-method="Stripe"
                                            data-status="completed"
                                            data-date="March 22, 2026"
                                            data-period="today"
                                            data-email="emily@email.com"
                                            data-user-img="assets/img/team/03.jpg"
                                            data-booking="—">
                                            <td data-label="Transaction ID"><strong>TXN-1006</strong></td>
                                            <td data-label="User">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/03.jpg' ) ); ?>" alt="Emily Davis">
                                                    <strong>Emily Davis</strong>
                                                </div>
                                            </td>
                                            <td data-label="Type">Teacher Payout</td>
                                            <td data-label="Amount"><strong>$320</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed ap-status">Completed</span></td>
                                            <td data-label="Date">March 22, 2026</td>
                                            <td data-label="Action">
                                                <button type="button" class="at-action-btn ap-view-btn" aria-label="View transaction">
                                                    <i class="far fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <tr class="ap-row"
                                            data-id="TXN-1007"
                                            data-user="James Carter"
                                            data-type="lesson"
                                            data-type-label="Lesson Payment"
                                            data-amount="$35"
                                            data-method="Stripe"
                                            data-status="failed"
                                            data-date="March 21, 2026"
                                            data-period="week"
                                            data-email="james.c@email.com"
                                            data-user-img="assets/img/team/06.jpg"
                                            data-booking="BK-1055">
                                            <td data-label="Transaction ID"><strong>TXN-1007</strong></td>
                                            <td data-label="User">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/06.jpg' ) ); ?>" alt="James Carter">
                                                    <strong>James Carter</strong>
                                                </div>
                                            </td>
                                            <td data-label="Type">Lesson Payment</td>
                                            <td data-label="Amount"><strong>$35</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="sb-badge is-failed ap-status">Failed</span></td>
                                            <td data-label="Date">March 21, 2026</td>
                                            <td data-label="Action">
                                                <button type="button" class="at-action-btn ap-view-btn" aria-label="View transaction">
                                                    <i class="far fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>

                                        <tr class="ap-row"
                                            data-id="TXN-1008"
                                            data-user="Hannah Lee"
                                            data-type="lesson"
                                            data-type-label="Lesson Payment"
                                            data-amount="$40"
                                            data-method="Stripe"
                                            data-status="completed"
                                            data-date="March 19, 2026"
                                            data-period="week"
                                            data-email="hannah@email.com"
                                            data-user-img="assets/img/team/02.jpg"
                                            data-booking="BK-1058">
                                            <td data-label="Transaction ID"><strong>TXN-1008</strong></td>
                                            <td data-label="User">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="Hannah Lee">
                                                    <strong>Hannah Lee</strong>
                                                </div>
                                            </td>
                                            <td data-label="Type">Lesson Payment</td>
                                            <td data-label="Amount"><strong>$40</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed ap-status">Completed</span></td>
                                            <td data-label="Date">March 19, 2026</td>
                                            <td data-label="Action">
                                                <button type="button" class="at-action-btn ap-view-btn" aria-label="View transaction">
                                                    <i class="far fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>

                            <div class="sl-empty" id="ap-empty" hidden>
                                <i class="far fa-receipt"></i>
                                <h3>No transactions found.</h3>
                                <p>Try adjusting your search or filter options.</p>
                            </div>

                            <nav class="at-pagination" id="ap-pagination" aria-label="Transactions pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item disabled" id="ap-page-prev">
                                        <a class="page-link" href="#" data-page="prev" aria-label="Previous"><i class="far fa-angle-left"></i> Previous</a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#" data-page="1">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#" data-page="2">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#" data-page="3">3</a></li>
                                    <li class="page-item" id="ap-page-next">
                                        <a class="page-link" href="#" data-page="next" aria-label="Next">Next <i class="far fa-angle-right"></i></a>
                                    </li>
                                </ul>
                            </nav>
                        </section>

                        <!-- refund management -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Recent Refund Requests</h3>
                                    <p>Review and process student refund requests.</p>
                                </div>
                            </div>

                            <div class="table-responsive td-table-wrap">
                                <table class="table td-table sb-table at-table">
                                    <thead>
                                        <tr>
                                            <th>User</th>
                                            <th>Amount</th>
                                            <th>Reason</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ap-refund-body">
                                        <tr class="ap-refund-row" data-id="RF-201" data-user="Michael Brown" data-amount="$45">
                                            <td data-label="User">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/04.jpg' ) ); ?>" alt="Michael Brown">
                                                    <strong>Michael Brown</strong>
                                                </div>
                                            </td>
                                            <td data-label="Amount"><strong>$45</strong></td>
                                            <td data-label="Reason">Lesson cancelled by student</td>
                                            <td data-label="Status"><span class="sb-badge is-pending ap-refund-status">Pending</span></td>
                                            <td data-label="Action">
                                                <div class="ap-refund-actions">
                                                    <button type="button" class="theme-btn ap-approve-refund"><i class="far fa-check"></i> Approve Refund</button>
                                                    <button type="button" class="theme-btn theme-btn-outline ap-reject-refund"><i class="far fa-xmark"></i> Reject Refund</button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="ap-refund-row" data-id="RF-202" data-user="David Wilson" data-amount="$50">
                                            <td data-label="User">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/05.jpg' ) ); ?>" alt="David Wilson">
                                                    <strong>David Wilson</strong>
                                                </div>
                                            </td>
                                            <td data-label="Amount"><strong>$50</strong></td>
                                            <td data-label="Reason">Teacher unavailable — reschedule declined</td>
                                            <td data-label="Status"><span class="sb-badge is-pending ap-refund-status">Pending</span></td>
                                            <td data-label="Action">
                                                <div class="ap-refund-actions">
                                                    <button type="button" class="theme-btn ap-approve-refund"><i class="far fa-check"></i> Approve Refund</button>
                                                    <button type="button" class="theme-btn theme-btn-outline ap-reject-refund"><i class="far fa-xmark"></i> Reject Refund</button>
                                                </div>
                                            </td>
                                        </tr>
                                        <tr class="ap-refund-row" data-id="RF-203" data-user="Hannah Lee" data-amount="$40">
                                            <td data-label="User">
                                                <div class="sb-teacher-cell">
                                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="Hannah Lee">
                                                    <strong>Hannah Lee</strong>
                                                </div>
                                            </td>
                                            <td data-label="Amount"><strong>$40</strong></td>
                                            <td data-label="Reason">Duplicate payment charged</td>
                                            <td data-label="Status"><span class="sb-badge is-pending ap-refund-status">Pending</span></td>
                                            <td data-label="Action">
                                                <div class="ap-refund-actions">
                                                    <button type="button" class="theme-btn ap-approve-refund"><i class="far fa-check"></i> Approve Refund</button>
                                                    <button type="button" class="theme-btn theme-btn-outline ap-reject-refund"><i class="far fa-xmark"></i> Reject Refund</button>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- admin payments end -->

    

<!-- transaction detail modal -->
    <div class="modal fade gospel-demo-modal" id="ap-txn-modal" tabindex="-1" aria-labelledby="ap-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ap-modal-title">Transaction Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body at-modal-body">
                    <div class="ab-modal-parties ap-modal-user">
                        <div class="ab-modal-party">
                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="User" id="ap-modal-user-img">
                            <div>
                                <small>User Details</small>
                                <strong id="ap-modal-user">Sarah Johnson</strong>
                                <span id="ap-modal-email">sarah@email.com</span>
                            </div>
                        </div>
                    </div>
                    <ul class="booking-modal-list at-modal-list">
                        <li><span>Transaction ID</span><strong id="ap-modal-id">TXN-1001</strong></li>
                        <li><span>Payment Type</span><strong id="ap-modal-type">Lesson Payment</strong></li>
                        <li><span>Amount</span><strong id="ap-modal-amount">$40</strong></li>
                        <li><span>Payment Method</span><strong id="ap-modal-method">Stripe</strong></li>
                        <li><span>Date</span><strong id="ap-modal-date">March 20, 2026</strong></li>
                        <li><span>Related Booking</span><strong id="ap-modal-booking">BK-1042</strong></li>
                        <li><span>Status</span><strong><span class="sb-badge is-confirmed" id="ap-modal-status">Completed</span></strong></li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" id="ap-modal-user-btn">
                        <i class="far fa-user"></i> View User
                    </button>
                    <button type="button" class="theme-btn theme-btn-outline" id="ap-modal-booking-btn">
                        <i class="far fa-calendar-check"></i> View Booking
                    </button>
                    <button type="button" class="theme-btn" id="ap-modal-refund-btn">
                        <i class="far fa-rotate-left"></i> Process Refund
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="gospel-alert gospel-alert-success sl-toast" id="ap-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="ap-toast-text">Action completed (demo).</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

