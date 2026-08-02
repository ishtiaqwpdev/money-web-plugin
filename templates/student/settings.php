<?php
/**
 * Template: student-settings
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $gmm_student_denied ) ) {
	echo '<div class="gmm-wrapper gmm-dashboard"><div class="student-dashboard-area py-120"><div class="container"><div class="sd-card"><div class="sd-card-head"><h3>' . esc_html__( 'You do not have permission to view these settings.', 'gospel-music-mastery' ) . '</h3></div></div></div></div></div>';
	return;
}

$profile = ( isset( $profile ) && is_array( $profile ) ) ? $profile : array();
$prefs   = ( isset( $preferences ) && is_array( $preferences ) ) ? $preferences : array();
$logout_url = isset( $logout_url ) ? $logout_url : ( function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ) );

$p = wp_parse_args(
	$profile,
	array(
		'first_name'     => '',
		'last_name'      => '',
		'display_name'   => '',
		'username'       => '',
		'email'          => '',
		'phone'          => '',
		'learning_level' => '',
		'learning_goals' => '',
		'image_url'      => gmm_design_asset_url( 'assets/img/team/02.jpg' ),
		'country'        => '',
		'timezone'       => 'Eastern Time',
		'status_label'   => 'Active',
		'instruments'    => array(),
		'preferred_instruments' => '',
	)
);
$prefs = wp_parse_args(
	$prefs,
	array(
		'email_notifications' => true,
		'lesson_reminders'    => true,
		'booking_updates'     => true,
		'teacher_messages'    => true,
		'payment_alerts'      => false,
	)
);

if ( ! isset( $user_name ) || '' === $user_name ) {
	$user_name = $p['display_name'] ? $p['display_name'] : trim( $p['first_name'] . ' ' . $p['last_name'] );
}
if ( ! $user_name ) {
	$user_name = 'Guest';
}
if ( ! isset( $user_first_name ) || '' === $user_first_name ) {
	$user_first_name = $p['first_name'] ? $p['first_name'] : $user_name;
}

$level_label = $p['learning_level'] ? $p['learning_level'] : __( 'Not set', 'gospel-music-mastery' );
$inst_label  = ! empty( $p['instruments'][0] ) ? $p['instruments'][0] : ( $p['preferred_instruments'] ? $p['preferred_instruments'] : __( 'Instruments', 'gospel-music-mastery' ) );
$countries   = array( 'United States', 'Canada', 'United Kingdom', 'Australia', 'Other' );
$levels      = array( 'Beginner', 'Intermediate', 'Advanced' );
$tz_opts     = array( 'Eastern Time', 'Central Time', 'Mountain Time', 'Pacific Time', 'UTC' );
if ( $p['timezone'] && ! in_array( $p['timezone'], $tz_opts, true ) ) {
	array_unshift( $tz_opts, $p['timezone'] );
}
?>
<div class="gmm-wrapper gmm-dashboard">

        <!-- student settings -->
        <div class="student-dashboard-area py-120">
            <div class="container">

                <!-- dashboard profile header -->
                <div class="sd-profile-header">
                    <div class="sd-profile-main">
                        <div class="sd-profile-avatar">
                            <img src="<?php echo esc_url( $p['image_url'] ); ?>" alt="<?php echo esc_attr( $user_name ); ?>" id="ss-header-avatar">
                        </div>
                        <div class="sd-profile-meta">
                            <h2><?php echo esc_html( $user_name ); ?></h2>
                            <span class="sd-role">Music Student</span>
                            <div class="sd-profile-stats">
                                <span class="sd-stat-item"><i class="far fa-signal"></i> Learning Level: <?php echo esc_html( $level_label ); ?></span>
                                <span class="sd-stat-item"><i class="far fa-music"></i> <?php echo esc_html( $inst_label ); ?></span>
                                <span class="sd-stat-item"><i class="far fa-shield-check"></i> Account Status: <?php echo esc_html( $p['status_label'] ); ?></span>
                            </div>
                        </div>
                    </div>
                    <div class="sd-profile-actions">
                        <a href="<?php echo esc_url( gmm_get_page_link( 'student_dashboard' ) ); ?>" class="theme-btn theme-btn-outline"><i class="far fa-grid-2"></i> Dashboard</a>
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_payments' ) ); ?>" class="sd-nav-link" data-nav="payments"><i class="far fa-credit-card"></i> Payments</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_settings' ) ); ?>" class="sd-nav-link active" data-nav="settings"><i class="far fa-gear"></i> Settings</a></li>
                                <li><a href="<?php echo esc_url( $logout_url ); ?>" class="sd-nav-link sd-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
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
                                    <span class="login-portal-badge">Account Settings</span>
                                    <h3>Manage Your Account</h3>
                                    <p>Update your personal information, security settings, billing details, and notification preferences.</p>
                                </div>
                            </div>
                        </section>

                        <div class="gospel-alert gospel-alert-error" id="ss-error" hidden>
                            <i class="far fa-circle-exclamation"></i>
                            <span id="ss-error-text">Please check the highlighted fields.</span>
                        </div>
                        <div class="gospel-alert gospel-alert-success" id="ss-success" hidden>
                            <i class="far fa-circle-check"></i>
                            <span id="ss-success-text">Settings saved successfully (demo).</span>
                        </div>

                        <div class="sl-tabs ss-tabs settings-tabs" role="tablist" aria-label="Account settings sections">
                            <button type="button" class="sl-tab is-active" data-tab="profile" role="tab" aria-selected="true">Profile</button>
                            <button type="button" class="sl-tab" data-tab="password" role="tab" aria-selected="false">Password</button>
                            <button type="button" class="sl-tab" data-tab="billing" role="tab" aria-selected="false">Billing</button>
                            <button type="button" class="sl-tab" data-tab="notifications" role="tab" aria-selected="false">Notifications</button>
                        </div>

                        <!-- TAB 1: PROFILE -->
                        <section class="sd-card settings-panel active" id="ss-panel-profile" data-panel="profile">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Profile Settings</h3>
                                    <p>Update your personal details and learning preferences.</p>
                                </div>
                            </div>

                            <form action="#" method="post" id="ss-profile-form" class="student-profile-form" novalidate>
                                <div class="sd-photo-row">
                                    <div class="sd-photo-preview">
                                        <img src="<?php echo esc_url( $p['image_url'] ); ?>" alt="<?php echo esc_attr( $user_name ); ?>" id="ss-profile-photo-img">
                                    </div>
                                    <div class="sd-photo-meta">
                                        <h4>Profile Photo</h4>
                                        <p class="sd-photo-hint">Accepted formats: JPG, PNG. Maximum size: 5MB.</p>
                                        <div class="sd-photo-buttons">
                                            <label class="theme-btn theme-btn-outline photo-upload-label" for="ss-profile-photo">
                                                <i class="far fa-camera"></i> Upload Photo
                                            </label>
                                            <input type="file" id="ss-profile-photo" name="profile_photo" accept=".jpg,.jpeg,.png,image/jpeg,image/png" hidden>
                                            <button type="button" class="theme-btn theme-btn-outline" id="ss-remove-photo">
                                                <i class="far fa-trash-alt"></i> Remove Photo
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ss-first-name">First Name</label>
                                            <input type="text" class="form-control" id="ss-first-name" name="first_name" value="<?php echo esc_attr( $p['first_name'] ); ?>">
                                            <span class="field-feedback" data-for="ss-first-name"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ss-last-name">Last Name</label>
                                            <input type="text" class="form-control" id="ss-last-name" name="last_name" value="<?php echo esc_attr( $p['last_name'] ); ?>">
                                            <span class="field-feedback" data-for="ss-last-name"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ss-username">Username</label>
                                            <input type="text" class="form-control" id="ss-username" name="username" value="<?php echo esc_attr( $p['username'] ); ?>">
                                            <span class="field-feedback" data-for="ss-username"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ss-email">Email Address</label>
                                            <input type="email" class="form-control" id="ss-email" name="email" value="<?php echo esc_attr( $p['email'] ); ?>">
                                            <span class="field-feedback" data-for="ss-email"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ss-phone">Phone Number</label>
                                            <input type="tel" class="form-control" id="ss-phone" name="phone" value="<?php echo esc_attr( $p['phone'] ); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ss-country">Country</label>
                                            <select class="form-control form-select" id="ss-country" name="country">
                                                <option value="">Select country</option>
												<?php foreach ( $countries as $country ) : ?>
                                                <option value="<?php echo esc_attr( $country ); ?>" <?php selected( $p['country'], $country ); ?>><?php echo esc_html( $country ); ?></option>
												<?php endforeach; ?>
                                            </select>
                                            <span class="field-feedback" data-for="ss-country"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ss-timezone">Timezone</label>
                                            <select class="form-control form-select" id="ss-timezone" name="timezone" data-selected="<?php echo esc_attr( $p['timezone'] ); ?>">
												<?php foreach ( $tz_opts as $tz ) : ?>
                                                <option value="<?php echo esc_attr( $tz ); ?>" <?php selected( $p['timezone'], $tz ); ?>><?php echo esc_html( $tz ); ?></option>
												<?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ss-level">Learning Level</label>
                                            <select class="form-control form-select" id="ss-level" name="learning_level">
												<?php foreach ( $levels as $level ) : ?>
                                                <option value="<?php echo esc_attr( $level ); ?>" <?php selected( strtolower( $p['learning_level'] ), strtolower( $level ) ); ?>><?php echo esc_html( $level ); ?></option>
												<?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="ss-goals">Learning Goals</label>
                                    <textarea class="form-control" id="ss-goals" name="learning_goals" rows="4"><?php echo esc_textarea( $p['learning_goals'] ); ?></textarea>
                                    <span class="field-feedback" data-for="ss-goals"></span>
                                </div>

                                <button type="submit" class="theme-btn"><i class="far fa-check"></i> Save Profile</button>
                            </form>
                        </section>

                        <!-- TAB 2: PASSWORD -->
                        <section class="sd-card settings-panel" id="ss-panel-password" data-panel="password" hidden>
                            <div class="sd-card-head">
                                <div>
                                    <h3>Password Security</h3>
                                    <p>Choose a strong password to keep your student account secure.</p>
                                </div>
                            </div>

                            <form action="#" method="post" id="ss-password-form" class="settings-password-form login-form student-profile-form" novalidate>
                                <div class="form-group">
                                    <label for="ss-current-password">Current Password</label>
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="ss-current-password" name="current_password" autocomplete="current-password">
                                        <button type="button" class="password-toggle" data-target="ss-current-password" aria-label="Show password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                    <span class="field-feedback" data-for="ss-current-password"></span>
                                </div>
                                <div class="form-group">
                                    <label for="ss-new-password">New Password</label>
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="ss-new-password" name="new_password" autocomplete="new-password">
                                        <button type="button" class="password-toggle" data-target="ss-new-password" aria-label="Show password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                    <span class="field-feedback" data-for="ss-new-password"></span>
                                </div>
                                <div class="form-group">
                                    <label for="ss-confirm-password">Confirm Password</label>
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="ss-confirm-password" name="confirm_password" autocomplete="new-password">
                                        <button type="button" class="password-toggle" data-target="ss-confirm-password" aria-label="Show password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                    <span class="field-feedback" data-for="ss-confirm-password"></span>
                                </div>
                                <button type="submit" class="theme-btn"><i class="far fa-key"></i> Update Password</button>
                            </form>
                        </section>

                        <!-- TAB 3: BILLING -->
                        <section class="sd-card settings-panel" id="ss-panel-billing" data-panel="billing" hidden>
                            <div class="sd-card-head">
                                <div>
                                    <h3>Billing Information</h3>
                                    <p>Keep your billing details up to date for lesson invoices.</p>
                                </div>
                            </div>

                            <form action="#" method="post" id="ss-billing-form" class="student-profile-form" novalidate>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ss-bill-name">Full Name</label>
                                            <input type="text" class="form-control" id="ss-bill-name" name="full_name" value="<?php echo esc_attr( $user_name ); ?>">
                                            <span class="field-feedback" data-for="ss-bill-name"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ss-bill-email">Email Address</label>
                                            <input type="email" class="form-control" id="ss-bill-email" name="email" value="<?php echo esc_attr( $p['email'] ); ?>">
                                            <span class="field-feedback" data-for="ss-bill-email"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ss-bill-country">Country</label>
                                            <select class="form-control form-select" id="ss-bill-country" name="country">
                                                <option value="">Select country</option>
                                                <option value="United States" selected>United States</option>
                                                <option value="Canada">Canada</option>
                                                <option value="United Kingdom">United Kingdom</option>
                                                <option value="Australia">Australia</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            <span class="field-feedback" data-for="ss-bill-country"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="ss-bill-address">Address</label>
                                            <input type="text" class="form-control" id="ss-bill-address" name="address" value="25 Milford Road">
                                            <span class="field-feedback" data-for="ss-bill-address"></span>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ss-bill-city">City</label>
                                            <input type="text" class="form-control" id="ss-bill-city" name="city" value="Nashville">
                                            <span class="field-feedback" data-for="ss-bill-city"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ss-bill-state">State</label>
                                            <input type="text" class="form-control" id="ss-bill-state" name="state" value="Tennessee">
                                            <span class="field-feedback" data-for="ss-bill-state"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="ss-bill-zip">ZIP Code</label>
                                            <input type="text" class="form-control" id="ss-bill-zip" name="zip" value="37201">
                                            <span class="field-feedback" data-for="ss-bill-zip"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="sp-method-row mb-4">
                                    <div class="sp-method-icon"><i class="fab fa-stripe-s"></i></div>
                                    <div class="sp-method-info">
                                        <strong>Stripe Connected</strong>
                                        <span>Payment method linked for lesson checkout</span>
                                        <span class="sp-card-mask">**** **** **** 4242</span>
                                    </div>
                                    <button type="button" class="theme-btn theme-btn-outline" id="ss-manage-payment">
                                        <i class="far fa-credit-card"></i> Manage Payment Method
                                    </button>
                                </div>

                                <button type="submit" class="theme-btn"><i class="far fa-check"></i> Save Billing Information</button>
                            </form>
                        </section>

                        <!-- TAB 4: NOTIFICATIONS -->
                        <section class="sd-card settings-panel" id="ss-panel-notifications" data-panel="notifications" hidden>
                            <div class="sd-card-head">
                                <div>
                                    <h3>Notification Preferences</h3>
                                    <p>Choose which updates you want to receive by email.</p>
                                </div>
                            </div>

                            <form action="#" method="post" id="ss-notifications-form" novalidate>
                                <div class="ss-toggle-list">
                                    <label class="ss-toggle-row">
                                        <span>
                                            <strong>Email Notifications</strong>
                                            <small>General account and platform updates</small>
                                        </span>
                                        <input type="checkbox" class="ss-toggle-input" id="ss-toggle-email" name="email_notifications" value="1" <?php checked( ! empty( $prefs['email_notifications'] ) ); ?>>
                                        <span class="ss-toggle-ui" aria-hidden="true"></span>
                                    </label>
                                    <label class="ss-toggle-row">
                                        <span>
                                            <strong>Lesson Reminders</strong>
                                            <small>Reminders before scheduled lessons</small>
                                        </span>
                                        <input type="checkbox" class="ss-toggle-input" id="ss-toggle-lessons" name="lesson_reminders" value="1" <?php checked( ! empty( $prefs['lesson_reminders'] ) ); ?>>
                                        <span class="ss-toggle-ui" aria-hidden="true"></span>
                                    </label>
                                    <label class="ss-toggle-row">
                                        <span>
                                            <strong>Booking Updates</strong>
                                            <small>Confirmations, changes, and cancellations</small>
                                        </span>
                                        <input type="checkbox" class="ss-toggle-input" id="ss-toggle-bookings" name="booking_updates" value="1" <?php checked( ! empty( $prefs['booking_updates'] ) ); ?>>
                                        <span class="ss-toggle-ui" aria-hidden="true"></span>
                                    </label>
                                    <label class="ss-toggle-row">
                                        <span>
                                            <strong>Teacher Messages</strong>
                                            <small>Messages from your instructors</small>
                                        </span>
                                        <input type="checkbox" class="ss-toggle-input" id="ss-toggle-messages" name="teacher_messages" value="1" <?php checked( ! empty( $prefs['teacher_messages'] ) ); ?>>
                                        <span class="ss-toggle-ui" aria-hidden="true"></span>
                                    </label>
                                    <label class="ss-toggle-row">
                                        <span>
                                            <strong>Payment Alerts</strong>
                                            <small>Receipts, failed charges, and refunds</small>
                                        </span>
                                        <input type="checkbox" class="ss-toggle-input" id="ss-toggle-payments" name="payment_alerts" value="1" <?php checked( ! empty( $prefs['payment_alerts'] ) ); ?>>
                                        <span class="ss-toggle-ui" aria-hidden="true"></span>
                                    </label>
                                </div>
                                <button type="submit" class="theme-btn"><i class="far fa-bell"></i> Save Preferences</button>
                            </form>
                        </section>

                        <!-- Danger zone -->
                        <section class="sd-card ss-danger-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Delete Account</h3>
                                    <p>Deleting your account will permanently remove your profile and learning data.</p>
                                </div>
                            </div>
                            <button type="button" class="theme-btn theme-btn-outline ss-danger-btn" id="ss-delete-open"
                                data-bs-toggle="modal" data-bs-target="#ss-delete-modal">
                                <i class="far fa-trash-alt"></i> Delete Account
                            </button>
                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- student settings end -->

    

<!-- delete account modal -->
    <div class="modal fade gospel-demo-modal" id="ss-delete-modal" tabindex="-1" aria-labelledby="ss-delete-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="ss-delete-title">Delete Account</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Are you sure you want to delete your student account? This action cannot be undone and will permanently remove your profile and learning data.</p>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="ss-delete-confirm">
                        <label class="form-check-label" for="ss-delete-confirm">
                            I understand this will permanently delete my account.
                        </label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="theme-btn ss-danger-btn" id="ss-delete-confirm-btn" disabled>
                        <i class="far fa-trash-alt"></i> Confirm Delete
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

