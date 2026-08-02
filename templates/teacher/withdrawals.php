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

if ( ! isset( $earnings ) || ! is_array( $earnings ) ) {
	$earnings = array();
}
if ( ! isset( $withdrawal_history ) || ! is_array( $withdrawal_history ) ) {
	$withdrawal_history = array();
}
if ( ! isset( $transactions ) || ! is_array( $transactions ) ) {
	$transactions = array();
}
if ( ! isset( $min_withdrawal ) ) {
	$min_withdrawal = isset( $earnings['min_withdrawal'] ) ? (float) $earnings['min_withdrawal'] : 50.0;
}

$total_earnings      = isset( $earnings['total_earnings'] ) ? (float) $earnings['total_earnings'] : 0.0;
$available_balance   = isset( $earnings['available_balance'] ) ? (float) $earnings['available_balance'] : 0.0;
$withdrawn_amount    = isset( $earnings['withdrawn_amount'] ) ? (float) $earnings['withdrawn_amount'] : 0.0;
$pending_withdrawals = isset( $earnings['pending_withdrawals'] ) ? (float) $earnings['pending_withdrawals'] : 0.0;
$has_withdrawals     = ! empty( $withdrawal_history );
$link_create_class   = function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teacher_classes' ) : '#';
$link_payment        = function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teacher_settings' ) : '#';
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
                        <a href="<?php echo esc_url( $link_create_class ); ?>" class="theme-btn"><i class="far fa-plus"></i> Create New Class</a>
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
                            <span id="withdrawal-success-text">Withdrawal request submitted successfully.</span>
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
                                    <span class="td-stat-value" id="total-earnings-display">$<?php echo esc_html( number_format_i18n( $total_earnings, 2 ) ); ?></span>
                                    <span class="td-stat-title">Total Earnings</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-wallet"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value" id="available-balance-display">$<?php echo esc_html( number_format_i18n( $available_balance, 2 ) ); ?></span>
                                    <span class="td-stat-title">Available Balance</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-money-bill-transfer"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value" id="withdrawn-amount-display">$<?php echo esc_html( number_format_i18n( $withdrawn_amount, 2 ) ); ?></span>
                                    <span class="td-stat-title">Total Withdrawals</span>
                                </div>
                            </div>
                            <div class="td-stat-card">
                                <div class="td-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="td-stat-body">
                                    <span class="td-stat-value" id="pending-withdrawals-display">$<?php echo esc_html( number_format_i18n( $pending_withdrawals, 2 ) ); ?></span>
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
                                    <a href="<?php echo esc_url( $link_payment ); ?>" class="theme-btn theme-btn-outline">
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
                                                min="<?php echo esc_attr( (string) $min_withdrawal ); ?>" step="0.01" placeholder="0.00" inputmode="decimal"
                                                max="<?php echo esc_attr( (string) $available_balance ); ?>">
                                        </div>
                                        <p class="field-note">Minimum withdrawal amount: $<?php echo esc_html( number_format_i18n( (float) $min_withdrawal, 2 ) ); ?></p>
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
<?php if ( $has_withdrawals ) : ?>
<?php foreach ( $withdrawal_history as $wd ) : ?>
                                        <tr data-status="<?php echo esc_attr( isset( $wd['ui_filter'] ) ? $wd['ui_filter'] : 'pending' ); ?>" data-id="<?php echo esc_attr( (string) ( isset( $wd['id'] ) ? $wd['id'] : 0 ) ); ?>">
                                            <td data-label="Date"><?php echo esc_html( isset( $wd['date_label'] ) ? $wd['date_label'] : '' ); ?></td>
                                            <td data-label="Amount"><strong><?php echo esc_html( isset( $wd['amount_label'] ) ? $wd['amount_label'] : '' ); ?></strong></td>
                                            <td data-label="Payment Method"><?php echo esc_html( isset( $wd['payment_method'] ) ? $wd['payment_method'] : '' ); ?></td>
                                            <td data-label="Status"><span class="td-badge <?php echo esc_attr( isset( $wd['badge_class'] ) ? $wd['badge_class'] : 'is-pending' ); ?>"><?php echo esc_html( isset( $wd['status_label'] ) ? $wd['status_label'] : '' ); ?></span></td>
                                            <td data-label="Action">
                                                <button type="button" class="theme-btn theme-btn-outline td-action-btn withdrawal-view-btn"
                                                    data-date="<?php echo esc_attr( isset( $wd['date_label'] ) ? $wd['date_label'] : '' ); ?>"
                                                    data-amount="<?php echo esc_attr( isset( $wd['amount_label'] ) ? $wd['amount_label'] : '' ); ?>"
                                                    data-method="<?php echo esc_attr( isset( $wd['payment_method'] ) ? $wd['payment_method'] : '' ); ?>"
                                                    data-status="<?php echo esc_attr( isset( $wd['status_label'] ) ? $wd['status_label'] : '' ); ?>"
                                                    data-note="<?php echo esc_attr( isset( $wd['admin_response'] ) ? $wd['admin_response'] : '' ); ?>">View</button>
                                            </td>
                                        </tr>
<?php endforeach; ?>
<?php endif; ?>
                                    </tbody>
                                </table>
                            </div>

                            <p class="td-empty-state" id="withdrawal-empty"<?php echo $has_withdrawals ? ' hidden' : ''; ?>>
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
                        <li><span>Admin Response</span><strong id="modal-wd-note">—</strong></li>
                    </ul>
                    <p class="field-note mt-3 mb-0">Admin response and payout notes appear here when available.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

<!-- js -->
</div><!-- .gmm-wrapper -->

