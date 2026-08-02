<?php
/**
 * Template: student-payments
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

        <!-- student payments -->
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
                        <a href="teachers.html" class="theme-btn"><i class="far fa-calendar-plus"></i> Book a Lesson</a>
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_lessons' ) ); ?>" class="sd-nav-link" data-nav="lessons"><i class="far fa-book-open"></i> My Lessons</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_bookings' ) ); ?>" class="sd-nav-link" data-nav="bookings"><i class="far fa-calendar-check"></i> My Bookings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_dashboard' ) ); ?>" class="sd-nav-link" data-nav="messages"><i class="far fa-comments"></i> Messages</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_favourites' ) ); ?>" class="sd-nav-link" data-nav="favourites"><i class="far fa-heart"></i> Favourite Teachers</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_payments' ) ); ?>" class="sd-nav-link active" data-nav="payments"><i class="far fa-credit-card"></i> Payments</a></li>
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
                                    <span class="login-portal-badge">Payment History</span>
                                    <h3>My Payments</h3>
                                    <p>View your lesson payments, transaction history, and billing information.</p>
                                </div>
                            </div>
                        </section>

                        <!-- summary stats -->
                        <section class="sd-stats-grid">
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-dollar-sign"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value">$<span class="counter" data-count="850">0</span></span>
                                    <span class="sd-stat-title">Total Spent</span>
                                </div>
                            </div>
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="12">0</span>
                                    <span class="sd-stat-title">Completed Payments</span>
                                </div>
                            </div>
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="2">0</span>
                                    <span class="sd-stat-title">Pending Payments</span>
                                </div>
                            </div>
                            <div class="sd-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-rotate-left"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value counter" data-count="1">0</span>
                                    <span class="sd-stat-title">Refunds</span>
                                </div>
                            </div>
                        </section>

                        <!-- payment method -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Saved Payment Method</h3>
                                    <p>Your payment information is securely processed through our payment provider.</p>
                                </div>
                                <span class="sb-badge is-confirmed">Connected</span>
                            </div>
                            <div class="sp-method-row">
                                <div class="sp-method-icon"><i class="fab fa-stripe-s"></i></div>
                                <div class="sp-method-info">
                                    <strong>Stripe</strong>
                                    <span>Card ending in 4242</span>
                                    <span class="sp-card-mask">**** **** **** 4242</span>
                                </div>
                                <button type="button" class="theme-btn theme-btn-outline" id="sp-manage-method">
                                    <i class="far fa-credit-card"></i> Manage Payment Method
                                </button>
                            </div>
                            <p class="sp-secure-note">
                                <i class="far fa-shield-check"></i>
                                Frontend demo only — no real payment gateway or Stripe connection.
                            </p>
                        </section>

                        <!-- transaction history -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Transaction History</h3>
                                    <p>Review recent lesson payments and invoices.</p>
                                </div>
                            </div>

                            <div class="sl-tabs sp-tabs" role="tablist" aria-label="Payment status filters">
                                <button type="button" class="sl-tab is-active" data-filter="all" role="tab" aria-selected="true">All</button>
                                <button type="button" class="sl-tab" data-filter="completed" role="tab" aria-selected="false">Completed</button>
                                <button type="button" class="sl-tab" data-filter="pending" role="tab" aria-selected="false">Pending</button>
                                <button type="button" class="sl-tab" data-filter="failed" role="tab" aria-selected="false">Failed</button>
                            </div>

                            <div class="sl-empty" id="sp-empty" hidden>
                                <i class="far fa-receipt"></i>
                                <h3>No payment history available yet.</h3>
                                <p>Your lesson payments and invoices will appear here.</p>
                                <a href="teachers.html" class="theme-btn"><i class="far fa-calendar-plus"></i> Book a Lesson</a>
                            </div>

                            <div class="table-responsive td-table-wrap" id="sp-table-wrap">
                                <table class="table td-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Teacher</th>
                                            <th>Lesson</th>
                                            <th>Amount</th>
                                            <th>Payment Method</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>

                                        <tr class="sp-row" data-status="completed"
                                            data-id="TXN-20260320-001"
                                            data-date="March 20, 2026"
                                            data-student="Sarah Johnson"
                                            data-teacher="John Smith"
                                            data-lesson="Gospel Piano Lesson"
                                            data-amount="$40"
                                            data-method="Stripe">
                                            <td data-label="Date">March 20, 2026</td>
                                            <td data-label="Teacher">John Smith</td>
                                            <td data-label="Lesson">Gospel Piano Lesson</td>
                                            <td data-label="Amount"><strong>$40</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed">Completed</span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sp-view-receipt">View Receipt</button>
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sp-download-invoice">Download Invoice</button>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="sp-row" data-status="pending"
                                            data-id="TXN-20260325-002"
                                            data-date="March 25, 2026"
                                            data-student="Sarah Johnson"
                                            data-teacher="Emily Davis"
                                            data-lesson="Vocal Training"
                                            data-amount="$50"
                                            data-method="Stripe">
                                            <td data-label="Date">March 25, 2026</td>
                                            <td data-label="Teacher">Emily Davis</td>
                                            <td data-label="Lesson">Vocal Training</td>
                                            <td data-label="Amount"><strong>$50</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="sb-badge is-pending">Pending</span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sp-view-receipt">View Receipt</button>
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sp-download-invoice">Download Invoice</button>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="sp-row" data-status="completed"
                                            data-id="TXN-20260310-003"
                                            data-date="March 10, 2026"
                                            data-student="Sarah Johnson"
                                            data-teacher="Michael Brown"
                                            data-lesson="Guitar Basics"
                                            data-amount="$40"
                                            data-method="Stripe">
                                            <td data-label="Date">March 10, 2026</td>
                                            <td data-label="Teacher">Michael Brown</td>
                                            <td data-label="Lesson">Guitar Basics</td>
                                            <td data-label="Amount"><strong>$40</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed">Completed</span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sp-view-receipt">View Receipt</button>
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sp-download-invoice">Download Invoice</button>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="sp-row" data-status="completed"
                                            data-id="TXN-20260305-004"
                                            data-date="March 5, 2026"
                                            data-student="Sarah Johnson"
                                            data-teacher="John Smith"
                                            data-lesson="Beginner Gospel Piano"
                                            data-amount="$40"
                                            data-method="Stripe">
                                            <td data-label="Date">March 5, 2026</td>
                                            <td data-label="Teacher">John Smith</td>
                                            <td data-label="Lesson">Beginner Gospel Piano</td>
                                            <td data-label="Amount"><strong>$40</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed">Completed</span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sp-view-receipt">View Receipt</button>
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sp-download-invoice">Download Invoice</button>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="sp-row" data-status="failed"
                                            data-id="TXN-20260228-005"
                                            data-date="February 28, 2026"
                                            data-student="Sarah Johnson"
                                            data-teacher="James Lee"
                                            data-lesson="Hammond Organ Intro"
                                            data-amount="$50"
                                            data-method="Stripe">
                                            <td data-label="Date">February 28, 2026</td>
                                            <td data-label="Teacher">James Lee</td>
                                            <td data-label="Lesson">Hammond Organ Intro</td>
                                            <td data-label="Amount"><strong>$50</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="sb-badge is-cancelled">Failed</span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sp-view-receipt">View Receipt</button>
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sp-download-invoice">Download Invoice</button>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="sp-row" data-status="pending"
                                            data-id="TXN-20260402-006"
                                            data-date="April 2, 2026"
                                            data-student="Sarah Johnson"
                                            data-teacher="Olivia Harris"
                                            data-lesson="Music Theory"
                                            data-amount="$38"
                                            data-method="Stripe">
                                            <td data-label="Date">April 2, 2026</td>
                                            <td data-label="Teacher">Olivia Harris</td>
                                            <td data-label="Lesson">Music Theory</td>
                                            <td data-label="Amount"><strong>$38</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="sb-badge is-pending">Pending</span></td>
                                            <td data-label="Action">
                                                <div class="sb-actions">
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sp-view-receipt">View Receipt</button>
                                                    <button type="button" class="theme-btn theme-btn-outline sd-action-btn sp-download-invoice">Download Invoice</button>
                                                </div>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>
                        </section>

                        <!-- billing information -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Billing Information</h3>
                                    <p>Used for invoices and payment receipts (frontend demo).</p>
                                </div>
                            </div>

                            <div class="gospel-alert gospel-alert-success" id="sp-billing-success" hidden>
                                <i class="far fa-circle-check"></i>
                                <span>Billing information updated successfully (demo).</span>
                            </div>
                            <div class="gospel-alert gospel-alert-error" id="sp-billing-error" hidden>
                                <i class="far fa-circle-exclamation"></i>
                                <span id="sp-billing-error-text">Please complete all required fields.</span>
                            </div>

                            <form action="#" method="post" id="sp-billing-form" novalidate>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sp-full-name">Full Name</label>
                                            <input type="text" class="form-control" id="sp-full-name" name="full_name" value="Sarah Johnson">
                                            <span class="field-feedback" data-for="sp-full-name"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sp-email">Email</label>
                                            <input type="email" class="form-control" id="sp-email" name="email" value="sarah@example.com">
                                            <span class="field-feedback" data-for="sp-email"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sp-country">Country</label>
                                            <select class="form-control form-select" id="sp-country" name="country">
                                                <option value="">Select country</option>
                                                <option value="United States" selected>United States</option>
                                                <option value="Canada">Canada</option>
                                                <option value="United Kingdom">United Kingdom</option>
                                                <option value="Australia">Australia</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            <span class="field-feedback" data-for="sp-country"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="sp-address">Address</label>
                                            <input type="text" class="form-control" id="sp-address" name="address" value="25 Milford Road">
                                            <span class="field-feedback" data-for="sp-address"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-md-0">
                                            <label for="sp-city">City</label>
                                            <input type="text" class="form-control" id="sp-city" name="city" value="Nashville">
                                            <span class="field-feedback" data-for="sp-city"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="sp-zip">ZIP Code</label>
                                            <input type="text" class="form-control" id="sp-zip" name="zip" value="37201">
                                            <span class="field-feedback" data-for="sp-zip"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="sd-card-actions sd-profile-form-actions mt-3">
                                    <button type="submit" class="theme-btn"><i class="far fa-check"></i> Update Billing</button>
                                </div>
                            </form>
                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- student payments end -->

    

<!-- receipt modal -->
    <div class="modal fade gospel-demo-modal" id="sp-receipt-modal" tabindex="-1" aria-labelledby="sp-receipt-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" id="sp-receipt-print">
                <div class="modal-header">
                    <h5 class="modal-title" id="sp-receipt-title">Payment Receipt</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <ul class="booking-modal-list">
                        <li><span>Transaction ID</span><strong id="sp-modal-id">—</strong></li>
                        <li><span>Date</span><strong id="sp-modal-date">—</strong></li>
                        <li><span>Student Name</span><strong id="sp-modal-student">—</strong></li>
                        <li><span>Teacher Name</span><strong id="sp-modal-teacher">—</strong></li>
                        <li><span>Lesson Name</span><strong id="sp-modal-lesson">—</strong></li>
                        <li><span>Amount</span><strong id="sp-modal-amount">—</strong></li>
                        <li><span>Payment Method</span><strong id="sp-modal-method">—</strong></li>
                        <li><span>Payment Status</span><strong id="sp-modal-status">—</strong></li>
                    </ul>
                    <p class="sp-secure-note mb-0">
                        <i class="far fa-info-circle"></i>
                        Demo receipt only. No real payment was processed.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="theme-btn" id="sp-print-receipt">
                        <i class="far fa-print"></i> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="gospel-alert gospel-alert-success sl-toast" id="sp-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="sp-toast-text">Action completed (demo).</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

