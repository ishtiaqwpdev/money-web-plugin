<?php
/**
 * Template: teacher-settings
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_withdrawals' ) ); ?>" class="td-nav-link" data-nav="withdrawals"><i class="far fa-wallet"></i> Withdrawals</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_settings' ) ); ?>" class="td-nav-link active" data-nav="settings"><i class="far fa-gear"></i> Settings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_login' ) ); ?>" class="td-nav-link td-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
                            </ul>
                        </nav>
                    </aside>
                    <div class="td-sidebar-backdrop" id="td-sidebar-backdrop" hidden></div>

                    <!-- main content -->
                    <div class="td-main teacher-settings-main">

                        <!-- page header -->
                        <section class="td-card">
                            <div class="td-card-head">
                                <div>
                                    <span class="login-portal-badge">Account Settings</span>
                                    <h3>Manage Your Account</h3>
                                    <p>Update your profile information, security settings, payment details, and social profiles.</p>
                                </div>
                            </div>
                        </section>

                        <div class="gospel-alert gospel-alert-error" id="settings-error" hidden>
                            <i class="far fa-circle-exclamation"></i>
                            <span id="settings-error-text">Please check the highlighted fields.</span>
                        </div>
                        <div class="gospel-alert gospel-alert-success" id="settings-success" hidden>
                            <i class="far fa-circle-check"></i>
                            <span id="settings-success-text">Settings saved successfully (demo).</span>
                        </div>

                        <div class="booking-tabs settings-tabs" role="tablist" aria-label="Account settings sections">
                            <button type="button" class="booking-tab settings-tab active" data-tab="profile" role="tab" aria-selected="true">Profile</button>
                            <button type="button" class="booking-tab settings-tab" data-tab="password" role="tab" aria-selected="false">Password</button>
                            <button type="button" class="booking-tab settings-tab" data-tab="withdraw" role="tab" aria-selected="false">Withdraw</button>
                            <button type="button" class="booking-tab settings-tab" data-tab="social" role="tab" aria-selected="false">Social Profile</button>
                            <button type="button" class="booking-tab settings-tab" data-tab="billing" role="tab" aria-selected="false">Billing</button>
                        </div>

                        <!-- TAB 1: PROFILE -->
                        <section class="td-card settings-panel active" id="settings-panel-profile" data-panel="profile">
                            <div class="td-card-head">
                                <div>
                                    <h3>Profile</h3>
                                    <p>Update the details students see on your instructor profile.</p>
                                </div>
                            </div>

                            <form action="#" method="post" id="settings-profile-form" class="teacher-profile-form" novalidate>
                                <div class="settings-cover-block">
                                    <label class="settings-upload-label">Cover Photo</label>
                                    <div class="settings-cover-preview" id="cover-preview">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/breadcrumb/01.jpg' ) ); ?>" alt="Cover photo" id="cover-preview-img">
                                        <div class="settings-cover-actions">
                                            <label class="theme-btn theme-btn-outline" for="cover-photo-input">
                                                <i class="far fa-image"></i> Upload Cover
                                            </label>
                                            <input type="file" id="cover-photo-input" accept="image/*" hidden>
                                        </div>
                                    </div>
                                </div>

                                <div class="profile-photo-block settings-avatar-block">
                                    <div class="profile-photo-preview">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="Profile photo" id="avatar-preview-img">
                                    </div>
                                    <div class="profile-photo-actions">
                                        <h5>Profile Photo</h5>
                                        <div class="photo-buttons">
                                            <label class="theme-btn theme-btn-outline" for="avatar-photo-input">
                                                <i class="far fa-camera"></i> Upload Photo
                                            </label>
                                            <input type="file" id="avatar-photo-input" accept="image/*" hidden>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="settings-first-name">First Name</label>
                                            <input type="text" class="form-control" id="settings-first-name" name="first_name" value="John" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="settings-last-name">Last Name</label>
                                            <input type="text" class="form-control" id="settings-last-name" name="last_name" value="Smith" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="settings-username">Username</label>
                                            <input type="text" class="form-control" id="settings-username" name="username" value="johnsmith" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="settings-phone">Phone Number</label>
                                            <input type="tel" class="form-control" id="settings-phone" name="phone" value="+1 615 555 4821">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="settings-skill">Skill / Occupation</label>
                                            <select class="form-control form-select" id="settings-skill" name="skill">
                                                <option value="">Select skill / occupation</option>
                                                <optgroup label="Piano &amp; Keys">
                                                    <option value="Gospel Piano Instructor" selected>Gospel Piano Instructor</option>
                                                    <option value="Worship Piano Instructor">Worship Piano Instructor</option>
                                                    <option value="Keyboard Instructor">Keyboard Instructor</option>
                                                    <option value="Organ Instructor">Organ Instructor</option>
                                                </optgroup>
                                                <optgroup label="Vocals">
                                                    <option value="Vocal Coach">Vocal Coach</option>
                                                    <option value="Gospel Vocal Instructor">Gospel Vocal Instructor</option>
                                                    <option value="Worship Vocal Coach">Worship Vocal Coach</option>
                                                    <option value="Choir Director">Choir Director</option>
                                                    <option value="Backing Vocals Coach">Backing Vocals Coach</option>
                                                </optgroup>
                                                <optgroup label="Guitar &amp; Bass">
                                                    <option value="Guitar Instructor">Guitar Instructor</option>
                                                    <option value="Acoustic Guitar Instructor">Acoustic Guitar Instructor</option>
                                                    <option value="Electric Guitar Instructor">Electric Guitar Instructor</option>
                                                    <option value="Bass Guitar Instructor">Bass Guitar Instructor</option>
                                                </optgroup>
                                                <optgroup label="Drums &amp; Percussion">
                                                    <option value="Drums Instructor">Drums Instructor</option>
                                                    <option value="Percussion Instructor">Percussion Instructor</option>
                                                </optgroup>
                                                <optgroup label="Worship &amp; Leadership">
                                                    <option value="Worship Leader">Worship Leader</option>
                                                    <option value="Worship Leadership Coach">Worship Leadership Coach</option>
                                                    <option value="Music Director">Music Director</option>
                                                    <option value="Band Director">Band Director</option>
                                                </optgroup>
                                                <optgroup label="Theory &amp; Production">
                                                    <option value="Music Theory Instructor">Music Theory Instructor</option>
                                                    <option value="Songwriting Coach">Songwriting Coach</option>
                                                    <option value="Music Production Instructor">Music Production Instructor</option>
                                                    <option value="Audio Engineering Instructor">Audio Engineering Instructor</option>
                                                </optgroup>
                                                <optgroup label="Other">
                                                    <option value="Violin Instructor">Violin Instructor</option>
                                                    <option value="Saxophone Instructor">Saxophone Instructor</option>
                                                    <option value="Trumpet Instructor">Trumpet Instructor</option>
                                                    <option value="Flute Instructor">Flute Instructor</option>
                                                    <option value="General Music Teacher">General Music Teacher</option>
                                                </optgroup>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="settings-timezone">Timezone</label>
                                            <select class="form-control form-select" id="settings-timezone" name="timezone" data-selected="America/New_York">
                                                <option value="Eastern Time" selected>Eastern Time</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="settings-display-name">Display Name</label>
                                    <input type="text" class="form-control" id="settings-display-name" name="display_name" value="John Smith">
                                </div>

                                <div class="form-group">
                                    <label for="settings-bio">Biography</label>
                                    <textarea class="form-control" id="settings-bio" name="biography" rows="6" maxlength="500">Experienced gospel music instructor helping students grow in piano, worship leadership, and musical confidence.</textarea>
                                    <div class="bio-counter-row">
                                        <span class="field-note">Visible on your public teacher profile.</span>
                                        <span class="bio-counter" id="settings-bio-counter">0 / 500</span>
                                    </div>
                                </div>

                                <button type="submit" class="theme-btn"><i class="far fa-check"></i> Update Profile</button>
                            </form>
                        </section>

                        <!-- TAB 2: PASSWORD -->
                        <section class="td-card settings-panel" id="settings-panel-password" data-panel="password" hidden>
                            <div class="td-card-head">
                                <div>
                                    <h3>Password</h3>
                                    <p>Change your account password to keep your instructor login secure.</p>
                                </div>
                            </div>

                            <form action="#" method="post" id="settings-password-form" class="settings-password-form" novalidate>
                                <div class="form-group">
                                    <label for="current-password">Current Password</label>
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="current-password" name="current_password" autocomplete="current-password" required>
                                        <button type="button" class="password-toggle" data-target="current-password" aria-label="Show password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="new-password">New Password</label>
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="new-password" name="new_password" autocomplete="new-password" required>
                                        <button type="button" class="password-toggle" data-target="new-password" aria-label="Show password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="confirm-password">Confirm New Password</label>
                                    <div class="password-field">
                                        <input type="password" class="form-control" id="confirm-password" name="confirm_password" autocomplete="new-password" required>
                                        <button type="button" class="password-toggle" data-target="confirm-password" aria-label="Show password">
                                            <i class="far fa-eye"></i>
                                        </button>
                                    </div>
                                </div>
                                <button type="submit" class="theme-btn"><i class="far fa-key"></i> Change Password</button>
                            </form>
                        </section>

                        <!-- TAB 3: WITHDRAW -->
                        <section class="td-card settings-panel" id="settings-panel-withdraw" data-panel="withdraw" hidden>
                            <div class="td-card-head">
                                <div>
                                    <h3>Withdrawal Settings</h3>
                                    <p>Review your connected payout account and minimum payout rules.</p>
                                </div>
                            </div>

                            <div class="payment-method-box">
                                <div class="payment-method-row">
                                    <div class="payment-method-icon"><i class="fab fa-stripe"></i></div>
                                    <div class="payment-method-info">
                                        <strong>Payment Status</strong>
                                        <span>Stripe Connected</span>
                                    </div>
                                    <span class="td-badge is-confirmed">Active</span>
                                </div>

                                <ul class="booking-modal-list settings-withdraw-list">
                                    <li><span>Payment Email</span><strong>example@email.com</strong></li>
                                    <li><span>Minimum Payout</span><strong>$50</strong></li>
                                </ul>

                                <a href="<?php echo esc_url( gmm_get_page_link( 'teacher_withdrawals' ) ); ?>" class="theme-btn">
                                    <i class="far fa-wallet"></i> Manage Payment Account
                                </a>

                                <div class="gospel-alert gospel-alert-warning mb-0">
                                    <i class="far fa-circle-info"></i>
                                    <span>Your payout details are securely handled through your connected payment provider.</span>
                                </div>
                            </div>
                        </section>

                        <!-- TAB 4: SOCIAL PROFILE -->
                        <section class="td-card settings-panel" id="settings-panel-social" data-panel="social" hidden>
                            <div class="td-card-head">
                                <div>
                                    <h3>Social Profile</h3>
                                    <p>Add links so students can follow your ministry and music.</p>
                                </div>
                            </div>

                            <form action="#" method="post" id="settings-social-form" class="teacher-profile-form" novalidate>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="settings-facebook"><i class="fab fa-facebook-f"></i> Facebook URL</label>
                                            <input type="url" class="form-control" id="settings-facebook" name="facebook" value="https://facebook.com/johnsmith" placeholder="https://facebook.com/username">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="settings-instagram"><i class="fab fa-instagram"></i> Instagram URL</label>
                                            <input type="url" class="form-control" id="settings-instagram" name="instagram" value="https://instagram.com/johnsmith" placeholder="https://instagram.com/username">
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="settings-youtube"><i class="fab fa-youtube"></i> YouTube URL</label>
                                            <input type="url" class="form-control" id="settings-youtube" name="youtube" value="https://youtube.com/@johnsmith" placeholder="https://youtube.com/@channel">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="settings-linkedin"><i class="fab fa-linkedin-in"></i> LinkedIn URL</label>
                                            <input type="url" class="form-control" id="settings-linkedin" name="linkedin" value="" placeholder="https://linkedin.com/in/username">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="settings-website"><i class="far fa-globe"></i> Website URL</label>
                                    <input type="url" class="form-control" id="settings-website" name="website" value="https://johnsmithmusic.com" placeholder="https://yourwebsite.com">
                                </div>
                                <button type="submit" class="theme-btn"><i class="far fa-check"></i> Save Social Profiles</button>
                            </form>
                        </section>

                        <!-- TAB 5: BILLING -->
                        <section class="td-card settings-panel" id="settings-panel-billing" data-panel="billing" hidden>
                            <div class="td-card-head">
                                <div>
                                    <h3>Billing Information</h3>
                                    <p>Keep your billing address up to date for invoices and account records.</p>
                                </div>
                            </div>

                            <form action="#" method="post" id="settings-billing-form" class="teacher-profile-form" novalidate>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="billing-name">Billing Name</label>
                                            <input type="text" class="form-control" id="billing-name" name="billing_name" value="John Smith" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="billing-country">Country</label>
                                            <select class="form-control form-select" id="billing-country" name="country" required>
                                                <option value="">Select country</option>
                                                <option value="United States" selected>United States</option>
                                                <option value="Canada">Canada</option>
                                                <option value="United Kingdom">United Kingdom</option>
                                                <option value="Australia">Australia</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="billing-address">Address</label>
                                    <input type="text" class="form-control" id="billing-address" name="address" value="123 Music Row" required>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="billing-city">City</label>
                                            <input type="text" class="form-control" id="billing-city" name="city" value="Nashville" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="billing-state">State</label>
                                            <input type="text" class="form-control" id="billing-state" name="state" value="TN" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="billing-zip">ZIP Code</label>
                                            <input type="text" class="form-control" id="billing-zip" name="zip" value="37203" required>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="theme-btn"><i class="far fa-check"></i> Save Billing Information</button>
                            </form>
                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- teacher dashboard end -->

    
</div><!-- .gmm-wrapper -->

