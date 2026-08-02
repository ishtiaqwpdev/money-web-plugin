<?php
/**
 * Template: admin-settings
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

        <!-- admin settings -->
        <div class="admin-dashboard-area py-120">
            <div class="container">

                <!-- admin top header -->
                <header class="sd-profile-header ad-topbar">
                    <div class="sd-profile-main ad-topbar-main">
                        <div class="sd-profile-avatar ad-topbar-avatar">
                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="<?php echo esc_attr( $user_name ); ?>" id="aset-topbar-avatar">
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
                                <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="<?php echo esc_attr( $user_name ); ?>" id="aset-menu-avatar">
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_payments' ) ); ?>" class="sd-nav-link" data-nav="payments"><i class="far fa-credit-card"></i> Payments</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_programs' ) ); ?>" class="sd-nav-link" data-nav="programs"><i class="far fa-music"></i> Music Programs</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_settings' ) ); ?>" class="sd-nav-link active" data-nav="settings"><i class="far fa-gear"></i> Settings</a></li>
                                <li><a href="admin-login.html" class="sd-nav-link sd-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
                            </ul>
                        </nav>
                    </aside>
                    <div class="sd-sidebar-backdrop" id="sd-sidebar-backdrop" hidden></div>

                    <div class="sd-main ad-main">

                        <section class="sd-card sd-welcome-card">
                            <div class="sd-card-head">
                                <div>
                                    <span class="login-portal-badge">System Settings</span>
                                    <h3>Admin Settings</h3>
                                    <p>Manage your administrator account, website preferences, payment settings, and platform configuration.</p>
                                </div>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'admin_dashboard' ) ); ?>" class="theme-btn theme-btn-outline"><i class="far fa-grid-2"></i> Dashboard</a>
                            </div>
                        </section>

                        <div class="gospel-alert gospel-alert-success" id="aset-success" hidden>
                            <i class="far fa-circle-check"></i>
                            <span id="aset-success-text">Settings saved successfully (demo).</span>
                        </div>

                        <!-- settings tabs -->
                        <div class="sl-tabs ss-tabs settings-tabs aset-tabs" role="tablist" aria-label="Admin settings sections">
                            <button type="button" class="sl-tab is-active" data-tab="profile" role="tab" aria-selected="true">Admin Profile</button>
                            <button type="button" class="sl-tab" data-tab="password" role="tab" aria-selected="false">Password</button>
                            <button type="button" class="sl-tab" data-tab="website" role="tab" aria-selected="false">Website Settings</button>
                            <button type="button" class="sl-tab" data-tab="payment" role="tab" aria-selected="false">Payment Settings</button>
                            <button type="button" class="sl-tab" data-tab="email" role="tab" aria-selected="false">Email Settings</button>
                        </div>

                        <!-- TAB 1: ADMIN PROFILE -->
                        <section class="sd-card settings-panel active" id="aset-panel-profile" data-panel="profile">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Admin Profile</h3>
                                    <p>Update your administrator account details.</p>
                                </div>
                            </div>

                            <form action="#" method="post" id="aset-profile-form" class="student-profile-form" novalidate>
                                <div class="sd-photo-row">
                                    <div class="sd-photo-preview">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="<?php echo esc_attr( $user_name ); ?>" id="aset-profile-photo-img">
                                    </div>
                                    <div class="sd-photo-meta">
                                        <h4>Profile Image</h4>
                                        <p class="sd-photo-hint">Accepted formats: JPG, PNG. Maximum size: 5MB.</p>
                                        <div class="sd-photo-buttons">
                                            <label class="theme-btn theme-btn-outline photo-upload-label" for="aset-profile-photo">
                                                <i class="far fa-camera"></i> Upload Image
                                            </label>
                                            <input type="file" id="aset-profile-photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png" hidden>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aset-full-name">Full Name</label>
                                            <input type="text" class="form-control" id="aset-full-name" name="full_name" value="Platform Administrator">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aset-username">Username</label>
                                            <input type="text" class="form-control" id="aset-username" name="username" value="admin">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aset-email">Email Address</label>
                                            <input type="email" class="form-control" id="aset-email" name="email" value="admin@gospelmusicmastery.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aset-phone">Phone Number</label>
                                            <input type="tel" class="form-control" id="aset-phone" name="phone" value="+1 615 555 0100">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aset-role">Role</label>
                                            <input type="text" class="form-control" id="aset-role" name="role" value="Administrator" readonly>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="theme-btn"><i class="far fa-check"></i> Save Profile</button>
                            </form>
                        </section>

                        <!-- TAB 2: PASSWORD -->
                        <section class="sd-card settings-panel" id="aset-panel-password" data-panel="password" hidden>
                            <div class="sd-card-head">
                                <div>
                                    <h3>Password Security</h3>
                                    <p>Choose a strong password to protect the admin account.</p>
                                </div>
                            </div>

                            <form action="#" method="post" id="aset-password-form" class="settings-password-form login-form student-profile-form" novalidate>
                                <div class="form-group">
                                    <label for="aset-current-password">Current Password</label>
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="aset-current-password" name="current_password" autocomplete="current-password">
                                        <button type="button" class="password-toggle" data-target="aset-current-password" aria-label="Show password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="aset-new-password">New Password</label>
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="aset-new-password" name="new_password" autocomplete="new-password">
                                        <button type="button" class="password-toggle" data-target="aset-new-password" aria-label="Show password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="aset-confirm-password">Confirm Password</label>
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="aset-confirm-password" name="confirm_password" autocomplete="new-password">
                                        <button type="button" class="password-toggle" data-target="aset-confirm-password" aria-label="Show password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" class="theme-btn"><i class="far fa-key"></i> Update Password</button>
                            </form>
                        </section>

                        <!-- TAB 3: WEBSITE SETTINGS -->
                        <section class="sd-card settings-panel" id="aset-panel-website" data-panel="website" hidden>
                            <div class="sd-card-head">
                                <div>
                                    <h3>Website Configuration</h3>
                                    <p>Manage branding, contact details, and social links.</p>
                                </div>
                            </div>

                            <form action="#" method="post" id="aset-website-form" class="student-profile-form" novalidate>
                                <div class="form-group">
                                    <label for="aset-site-name">Website Name</label>
                                    <input type="text" class="form-control" id="aset-site-name" name="website_name" value="Gospel Music Mastery">
                                </div>

                                <div class="row aset-upload-row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Website Logo Upload</label>
                                            <div class="aset-file-preview">
                                                <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/logo/logo.png' ) ); ?>" alt="Website logo" id="aset-logo-preview">
                                            </div>
                                            <label class="theme-btn theme-btn-outline photo-upload-label" for="aset-logo-file">
                                                <i class="far fa-image"></i> Upload Logo
                                            </label>
                                            <input type="file" id="aset-logo-file" accept="image/*" hidden>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Favicon Upload</label>
                                            <div class="aset-file-preview is-favicon">
                                                <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/logo/favicon.png' ) ); ?>" alt="Favicon" id="aset-favicon-preview">
                                            </div>
                                            <label class="theme-btn theme-btn-outline photo-upload-label" for="aset-favicon-file">
                                                <i class="far fa-icons"></i> Upload Favicon
                                            </label>
                                            <input type="file" id="aset-favicon-file" accept="image/*" hidden>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aset-contact-email">Contact Email</label>
                                            <input type="email" class="form-control" id="aset-contact-email" value="hello@gospelmusicmastery.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aset-contact-phone">Phone Number</label>
                                            <input type="tel" class="form-control" id="aset-contact-phone" value="+1 615 555 0199">
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="aset-address">Address</label>
                                    <textarea class="form-control" id="aset-address" rows="2">123 Worship Way, Nashville, TN 37201</textarea>
                                </div>

                                <h4 class="aset-section-title">Social Links</h4>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aset-facebook">Facebook</label>
                                            <input type="url" class="form-control" id="aset-facebook" value="https://facebook.com/gospelmusicmastery">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aset-instagram">Instagram</label>
                                            <input type="url" class="form-control" id="aset-instagram" value="https://instagram.com/gospelmusicmastery">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aset-youtube">YouTube</label>
                                            <input type="url" class="form-control" id="aset-youtube" value="https://youtube.com/@gospelmusicmastery">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aset-linkedin">LinkedIn</label>
                                            <input type="url" class="form-control" id="aset-linkedin" value="https://linkedin.com/company/gospelmusicmastery">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="theme-btn"><i class="far fa-check"></i> Save Website Settings</button>
                            </form>
                        </section>

                        <!-- TAB 4: PAYMENT SETTINGS -->
                        <section class="sd-card settings-panel" id="aset-panel-payment" data-panel="payment" hidden>
                            <div class="sd-card-head">
                                <div>
                                    <h3>Payment Configuration</h3>
                                    <p>Manage platform commission and payout preferences.</p>
                                </div>
                            </div>

                            <div class="sp-method-row aset-provider-row mb-4">
                                <div class="sp-method-icon"><i class="fab fa-stripe-s"></i></div>
                                <div class="sp-method-info">
                                    <strong>Payment Provider: Stripe</strong>
                                    <span>Status: <span class="sb-badge is-confirmed">Connected</span></span>
                                </div>
                            </div>

                            <div class="gospel-alert gospel-alert-info aset-info-box mb-4">
                                <i class="far fa-circle-info"></i>
                                <span>Payment integration settings are managed securely through the payment provider.</span>
                            </div>

                            <form action="#" method="post" id="aset-payment-form" class="student-profile-form" novalidate>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="aset-commission">Platform Commission</label>
                                            <div class="input-group">
                                                <input type="number" class="form-control" id="aset-commission" value="10" min="0" max="100">
                                                <span class="input-group-text">%</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="aset-currency">Currency</label>
                                            <select class="form-control form-select" id="aset-currency">
                                                <option value="USD" selected>USD</option>
                                                <option value="EUR">EUR</option>
                                                <option value="GBP">GBP</option>
                                                <option value="CAD">CAD</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="aset-min-withdrawal">Minimum Withdrawal</label>
                                            <div class="input-group">
                                                <span class="input-group-text">$</span>
                                                <input type="number" class="form-control" id="aset-min-withdrawal" value="50" min="0">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="aset-btn-row">
                                    <button type="submit" class="theme-btn"><i class="far fa-check"></i> Save Payment Settings</button>
                                    <button type="button" class="theme-btn theme-btn-outline" id="aset-test-connection">
                                        <i class="far fa-plug"></i> Test Connection
                                    </button>
                                </div>
                            </form>
                        </section>

                        <!-- TAB 5: EMAIL SETTINGS -->
                        <section class="sd-card settings-panel" id="aset-panel-email" data-panel="email" hidden>
                            <div class="sd-card-head">
                                <div>
                                    <h3>Email Configuration</h3>
                                    <p>Configure outbound email and notification preferences.</p>
                                </div>
                            </div>

                            <form action="#" method="post" id="aset-email-form" class="student-profile-form" novalidate>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aset-sender-name">Sender Name</label>
                                            <input type="text" class="form-control" id="aset-sender-name" value="Gospel Music Mastery">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aset-sender-email">Sender Email</label>
                                            <input type="email" class="form-control" id="aset-sender-email" value="noreply@gospelmusicmastery.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aset-smtp-host">SMTP Host</label>
                                            <input type="text" class="form-control" id="aset-smtp-host" value="smtp.gospelmusicmastery.com">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="aset-smtp-port">SMTP Port</label>
                                            <input type="number" class="form-control" id="aset-smtp-port" value="587">
                                        </div>
                                    </div>
                                </div>

                                <div class="ss-toggle-list mb-3">
                                    <label class="ss-toggle-row">
                                        <span>
                                            <strong>Email Notifications</strong>
                                            <small>Enable platform email notifications</small>
                                        </span>
                                        <input type="checkbox" class="ss-toggle-input" id="aset-email-notifications" checked>
                                        <span class="ss-toggle-ui" aria-hidden="true"></span>
                                    </label>
                                </div>

                                <h4 class="aset-section-title">Notification Options</h4>
                                <div class="ss-toggle-list">
                                    <label class="ss-toggle-row">
                                        <span>
                                            <strong>New Student Registration</strong>
                                            <small>Notify when a student creates an account</small>
                                        </span>
                                        <input type="checkbox" class="ss-toggle-input" id="aset-notify-student" checked>
                                        <span class="ss-toggle-ui" aria-hidden="true"></span>
                                    </label>
                                    <label class="ss-toggle-row">
                                        <span>
                                            <strong>New Teacher Application</strong>
                                            <small>Notify when a teacher applies to join</small>
                                        </span>
                                        <input type="checkbox" class="ss-toggle-input" id="aset-notify-teacher" checked>
                                        <span class="ss-toggle-ui" aria-hidden="true"></span>
                                    </label>
                                    <label class="ss-toggle-row">
                                        <span>
                                            <strong>New Booking</strong>
                                            <small>Notify when a lesson booking is created</small>
                                        </span>
                                        <input type="checkbox" class="ss-toggle-input" id="aset-notify-booking" checked>
                                        <span class="ss-toggle-ui" aria-hidden="true"></span>
                                    </label>
                                    <label class="ss-toggle-row">
                                        <span>
                                            <strong>Payment Received</strong>
                                            <small>Notify when a payment is successfully processed</small>
                                        </span>
                                        <input type="checkbox" class="ss-toggle-input" id="aset-notify-payment" checked>
                                        <span class="ss-toggle-ui" aria-hidden="true"></span>
                                    </label>
                                </div>

                                <button type="submit" class="theme-btn"><i class="far fa-envelope"></i> Save Email Settings</button>
                            </form>
                        </section>

                        <!-- Danger Zone / System Actions -->
                        <section class="sd-card aset-danger-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>System Actions</h3>
                                    <p>Platform maintenance tools. These actions are frontend demos only.</p>
                                </div>
                            </div>

                            <div class="aset-system-actions">
                                <div class="aset-action-row">
                                    <div>
                                        <strong>Clear Cache</strong>
                                        <small>Remove temporary cached files from the platform demo.</small>
                                    </div>
                                    <button type="button" class="theme-btn theme-btn-outline" id="aset-clear-cache">
                                        <i class="far fa-broom"></i> Clear Cache
                                    </button>
                                </div>
                                <div class="aset-action-row">
                                    <div>
                                        <strong>Backup Data</strong>
                                        <small>Generate a demo backup archive of platform data.</small>
                                    </div>
                                    <button type="button" class="theme-btn theme-btn-outline" id="aset-backup-data">
                                        <i class="far fa-database"></i> Backup Data
                                    </button>
                                </div>
                                <div class="aset-action-row">
                                    <div>
                                        <strong>Maintenance Mode</strong>
                                        <small>Temporarily show a maintenance notice to visitors.</small>
                                    </div>
                                    <label class="ss-toggle-row aset-inline-toggle">
                                        <input type="checkbox" class="ss-toggle-input" id="aset-maintenance-mode">
                                        <span class="ss-toggle-ui" aria-hidden="true"></span>
                                    </label>
                                </div>
                                <div class="aset-action-row is-danger">
                                    <div>
                                        <strong>Delete Platform Data</strong>
                                        <small>Permanently remove demo platform data. Requires confirmation.</small>
                                    </div>
                                    <button type="button" class="theme-btn aset-danger-btn" id="aset-delete-data">
                                        <i class="far fa-triangle-exclamation"></i> Delete Platform Data
                                    </button>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- admin settings end -->

    

<!-- delete confirmation modal -->
    <div class="modal fade gospel-demo-modal" id="aset-delete-modal" tabindex="-1" aria-labelledby="aset-delete-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="aset-delete-title">Delete Platform Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete platform data? This frontend demo will not permanently remove anything.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="theme-btn aset-danger-btn" id="aset-delete-confirm">
                        <i class="far fa-trash"></i> Confirm Delete
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="gospel-alert gospel-alert-success sl-toast" id="aset-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="aset-toast-text">Action completed (demo).</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

