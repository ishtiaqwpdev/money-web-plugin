<?php
/**
 * Template: teacher-withdrawals
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>" class="td-nav-link" data-nav="classes"><i class="far fa-chalkboard"></i> My Classes</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_dashboard' ) ); ?>" class="td-nav-link" data-nav="messages"><i class="far fa-comments"></i> Messages</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_withdrawals' ) ); ?>" class="td-nav-link active" data-nav="withdrawals"><i class="far fa-wallet"></i> Withdrawals</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_settings' ) ); ?>" class="td-nav-link" data-nav="settings"><i class="far fa-gear"></i> Settings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_login' ) ); ?>" class="td-nav-link td-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
                            </ul>
                        </nav>
                    </aside>
                    <div class="td-sidebar-backdrop" id="td-sidebar-backdrop" hidden></div>

                    <!-- main content -->
                    <div class="td-main">

                        <div class="gospel-alert gospel-alert-error" id="withdrawal-error" hidden>
                            <i class="far fa-circle-exclamation"></i>
                            <span id="withdrawal-error-text">Please enter a valid withdrawal amount.</span>
                        </div>
                        <div class="gospel-alert gospel-alert-success" id="withdrawal-success" hidden>
                            <i class="far fa-circle-check"></i>
                            <span>Withdrawal request submitted successfully (demo). No real payment was processed.</span>
                        </div>

                        <!-- page header -->
                        <section class="td-card">
                            <div class="td-card-head">
                                <div>
                                    <span class="login-portal-badge">Earnings &amp; Payouts</span>
                                    <h3>Manage Your Withdrawals</h3>
                                    <p>Track your lesson earnings and request payments securely from your instructor account.</p>
                                </div>
                            </div>
                        </section>

                        <!-- summary cards -->
                        <section class="td-stats-grid bookings-summary-grid">
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-chart-line"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value">$5,250</span>
                                    <span class="td-stat-title">Total Earnings</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-wallet"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value" id="available-balance-display">$1,250</span>
                                    <span class="td-stat-title">Available Balance</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-money-bill-transfer"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value">$4,000</span>
                                    <span class="td-stat-title">Total Withdrawals</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value">$500</span>
                                    <span class="td-stat-title">Pending Requests</span>
                                </div>
                            </div>
                        </section>

                        <div class="withdrawal-layout">
                            <!-- payment account -->
                            <section class="td-card withdrawal-equal-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Payment Method</h3>
                                        <p>Connect and manage where your lesson payouts are sent.</p>
                                    </div>
                                </div>
                                <div class="payment-method-box">
                                    <div class="payment-method-row">
                                        <div class="payment-method-icon"><i class="fab fa-stripe"></i></div>
                                        <div class="payment-method-info">
                                            <strong>Stripe Account</strong>
                                            <span>Connected</span>
                                        </div>
                                        <span class="td-badge is-confirmed">Active</span>
                                    </div>
                                    <a href="teacher-onboarding-payment.html" class="theme-btn theme-btn-outline">
                                        <i class="far fa-gear"></i> Manage Payment Account
                                    </a>
                                    <p class="field-note payment-secure-note">
                                        Your payment information is securely managed through Stripe.
                                    </p>
                                </div>
                            </section>

                            <!-- request withdrawal -->
                            <section class="td-card withdrawal-equal-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Request Withdrawal</h3>
                                        <p>Transfer available earnings to your connected Stripe account.</p>
                                    </div>
                                </div>
                                <form action="#" method="post" id="withdrawal-request-form" novalidate>
                                    <div class="form-group">
                                        <label for="withdrawal-amount">Amount</label>
                                        <div class="price-input-wrap">
                                            <span class="price-prefix">$</span>
                                            <input type="number" class="form-control" id="withdrawal-amount" name="amount"
                                                min="50" step="0.01" placeholder="0.00" inputmode="decimal">
                                        </div>
                                        <p class="field-note">Minimum withdrawal amount: $50</p>
                                    </div>
                                    <div class="form-group">
                                        <label for="withdrawal-method">Payment Method</label>
                                        <select class="form-control form-select" id="withdrawal-method" name="payment_method">
                                            <option value="Stripe" selected>Stripe</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="theme-btn" id="request-withdrawal-btn">
                                        <i class="far fa-paper-plane"></i> Request Withdrawal
                                    </button>
                                </form>
                            </section>
                        </div>

                        <!-- transaction history -->
                        <section class="td-card">
                            <div class="td-card-head">
                                <div>
                                    <h3>Withdrawal History</h3>
                                    <p>Review past and pending payout requests.</p>
                                </div>
                            </div>

                            <div class="booking-tabs" role="tablist" aria-label="Withdrawal status filters">
                                <button type="button" class="booking-tab active" data-filter="all" role="tab" aria-selected="true">All</button>
                                <button type="button" class="booking-tab" data-filter="completed" role="tab" aria-selected="false">Completed</button>
                                <button type="button" class="booking-tab" data-filter="pending" role="tab" aria-selected="false">Pending</button>
                                <button type="button" class="booking-tab" data-filter="failed" role="tab" aria-selected="false">Failed</button>
                            </div>

                            <div class="table-responsive" id="withdrawal-table-wrap">
                                <table class="table td-table" id="withdrawal-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Payment Method</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="withdrawal-tbody">
                                        <tr data-status="completed">
                                            <td data-label="Date">March 10, 2026</td>
                                            <td data-label="Amount"><strong>$250</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="td-badge is-confirmed">Completed</span></td>
                                            <td data-label="Action">
                                                <button type="button" class="theme-btn theme-btn-outline td-action-btn withdrawal-view-btn"
                                                    data-date="March 10, 2026"
                                                    data-amount="$250"
                                                    data-method="Stripe"
                                                    data-status="Completed">View</button>
                                            </td>
                                        </tr>
                                        <tr data-status="pending">
                                            <td data-label="Date">March 20, 2026</td>
                                            <td data-label="Amount"><strong>$500</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="td-badge is-pending">Pending</span></td>
                                            <td data-label="Action">
                                                <button type="button" class="theme-btn theme-btn-outline td-action-btn withdrawal-view-btn"
                                                    data-date="March 20, 2026"
                                                    data-amount="$500"
                                                    data-method="Stripe"
                                                    data-status="Pending">View</button>
                                            </td>
                                        </tr>
                                        <tr data-status="completed">
                                            <td data-label="Date">February 28, 2026</td>
                                            <td data-label="Amount"><strong>$750</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="td-badge is-confirmed">Completed</span></td>
                                            <td data-label="Action">
                                                <button type="button" class="theme-btn theme-btn-outline td-action-btn withdrawal-view-btn"
                                                    data-date="February 28, 2026"
                                                    data-amount="$750"
                                                    data-method="Stripe"
                                                    data-status="Completed">View</button>
                                            </td>
                                        </tr>
                                        <tr data-status="failed">
                                            <td data-label="Date">February 12, 2026</td>
                                            <td data-label="Amount"><strong>$100</strong></td>
                                            <td data-label="Payment Method">Stripe</td>
                                            <td data-label="Status"><span class="td-badge is-cancelled">Failed</span></td>
                                            <td data-label="Action">
                                                <button type="button" class="theme-btn theme-btn-outline td-action-btn withdrawal-view-btn"
                                                    data-date="February 12, 2026"
                                                    data-amount="$100"
                                                    data-method="Stripe"
                                                    data-status="Failed">View</button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <p class="td-empty-state" id="withdrawal-empty" hidden>
                                No withdrawal history available yet.
                            </p>
                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- teacher dashboard end -->

    

<!-- withdrawal details modal -->
    <div class="modal fade gospel-demo-modal" id="withdrawal-details-modal" tabindex="-1" aria-labelledby="withdrawal-details-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="withdrawal-details-title">Withdrawal Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body booking-modal-body">
                    <ul class="booking-modal-list">
                        <li><span>Date</span><strong id="modal-wd-date">—</strong></li>
                        <li><span>Amount</span><strong id="modal-wd-amount">—</strong></li>
                        <li><span>Payment Method</span><strong id="modal-wd-method">—</strong></li>
                        <li><span>Status</span><strong id="modal-wd-status">—</strong></li>
                    </ul>
                    <p class="field-note mt-3 mb-0">Frontend demo only. Stripe payout details will appear here after backend integration.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<!-- js -->
</div><!-- .gmm-wrapper -->

