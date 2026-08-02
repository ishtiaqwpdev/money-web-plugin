<?php
/**
 * Template: student-profile
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $gmm_student_denied ) ) {
	echo '<div class="gmm-wrapper gmm-dashboard"><div class="student-dashboard-area py-120"><div class="container"><div class="sd-card"><div class="sd-card-head"><h3>' . esc_html__( 'You do not have permission to view this profile.', 'gospel-music-mastery' ) . '</h3></div></div></div></div></div>';
	return;
}

$profile = ( isset( $profile ) && is_array( $profile ) ) ? $profile : array();
$logout_url    = isset( $logout_url ) ? $logout_url : ( function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ) );
$flash_success = isset( $flash_success ) ? (string) $flash_success : '';
$flash_error   = isset( $flash_error ) ? (string) $flash_error : '';
$completion    = ( isset( $profile_completion ) && is_array( $profile_completion ) ) ? $profile_completion : array( 'percent' => 0 );

$p = wp_parse_args(
	$profile,
	array(
		'first_name'            => '',
		'last_name'             => '',
		'display_name'          => '',
		'username'              => '',
		'email'                 => '',
		'phone'                 => '',
		'learning_level'        => '',
		'learning_goals'        => '',
		'preferred_instruments' => '',
		'instruments'           => array(),
		'bio'                   => '',
		'status'                => 'active',
		'status_label'          => 'Active',
		'image_url'             => gmm_design_asset_url( 'assets/img/team/02.jpg' ),
		'country'               => '',
		'timezone'              => '',
		'music_style'           => '',
		'facebook'              => '',
		'instagram'             => '',
		'youtube'               => '',
	)
);

if ( ! is_array( $p['instruments'] ) ) {
	$p['instruments'] = array();
}

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
$inst_label  = ! empty( $p['instruments'] ) ? $p['instruments'][0] : ( $p['preferred_instruments'] ? $p['preferred_instruments'] : __( 'Instruments', 'gospel-music-mastery' ) );

$countries = array( 'United States', 'Canada', 'United Kingdom', 'Australia', 'Other' );
$levels    = array( 'Beginner', 'Intermediate', 'Advanced' );
$styles    = array( 'Gospel', 'Worship', 'Contemporary Christian', 'Choir' );
$tz_opts   = array( 'Eastern Time', 'Central Time', 'Mountain Time', 'Pacific Time', 'UTC' );
if ( $p['timezone'] && ! in_array( $p['timezone'], $tz_opts, true ) ) {
	array_unshift( $tz_opts, $p['timezone'] );
}

$inst_ids = array(
	'Piano'              => 'inst-piano',
	'Guitar'             => 'inst-guitar',
	'Vocals'             => 'inst-vocals',
	'Drums'              => 'inst-drums',
	'Music Theory'       => 'inst-theory',
	'Worship Leadership' => 'inst-worship',
);
$inst_lower = array_map( 'strtolower', $p['instruments'] );

$bio_len = strlen( (string) $p['bio'] );
?>
<div class="gmm-wrapper gmm-dashboard">

        <!-- student profile -->
        <div class="student-dashboard-area py-120">
            <div class="container">

                <!-- dashboard profile header -->
                <div class="sd-profile-header">
                    <div class="sd-profile-main">
                        <div class="sd-profile-avatar">
                            <img src="<?php echo esc_url( $p['image_url'] ); ?>" alt="<?php echo esc_attr( $user_name ); ?>" id="sd-header-avatar">
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
                        <a href="#student-profile-form" class="theme-btn"><i class="far fa-user-pen"></i> Edit Profile</a>
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_profile' ) ); ?>" class="sd-nav-link active" data-nav="profile"><i class="far fa-user"></i> My Profile</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_lessons' ) ); ?>" class="sd-nav-link" data-nav="lessons"><i class="far fa-book-open"></i> My Lessons</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_bookings' ) ); ?>" class="sd-nav-link" data-nav="bookings"><i class="far fa-calendar-check"></i> My Bookings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_dashboard' ) ); ?>" class="sd-nav-link" data-nav="messages"><i class="far fa-comments"></i> Messages</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_favourites' ) ); ?>" class="sd-nav-link" data-nav="favourites"><i class="far fa-heart"></i> Favourite Teachers</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_payments' ) ); ?>" class="sd-nav-link" data-nav="payments"><i class="far fa-credit-card"></i> Payments</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_settings' ) ); ?>" class="sd-nav-link" data-nav="settings"><i class="far fa-gear"></i> Settings</a></li>
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
                                    <span class="login-portal-badge">My Profile</span>
                                    <h3>Student Profile</h3>
                                    <p>Manage your personal information and learning preferences.<?php echo ! empty( $completion['percent'] ) ? ' ' . esc_html( sprintf( /* translators: %d: percent */ __( 'Profile %d%% complete.', 'gospel-music-mastery' ), (int) $completion['percent'] ) ) : ''; ?></p>
                                </div>
                            </div>
                        </section>

                        <form action="#" method="post" id="student-profile-form" class="student-profile-form" novalidate>

                            <div class="gospel-alert gospel-alert-error" id="profile-error" <?php echo $flash_error ? '' : 'hidden'; ?>>
                                <i class="far fa-circle-exclamation"></i>
                                <span id="profile-error-text"><?php echo $flash_error ? esc_html( $flash_error ) : esc_html__( 'Please fix the highlighted fields.', 'gospel-music-mastery' ); ?></span>
                            </div>
                            <div class="gospel-alert gospel-alert-success" id="profile-success" <?php echo $flash_success ? '' : 'hidden'; ?>>
                                <i class="far fa-circle-check"></i>
                                <span><?php echo $flash_success ? esc_html( $flash_success ) : esc_html__( 'Profile changes saved successfully.', 'gospel-music-mastery' ); ?></span>
                            </div>

                            <!-- profile photo + summary -->
                            <section class="sd-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Profile Photo</h3>
                                        <p>Upload a clear photo so teachers can recognize you.</p>
                                    </div>
                                </div>

                                <div class="sd-photo-row">
                                    <div class="sd-photo-preview" id="sd-photo-preview">
                                        <img src="<?php echo esc_url( $p['image_url'] ); ?>" alt="<?php echo esc_attr( $user_name ); ?>" id="sd-profile-photo-img">
                                    </div>
                                    <div class="sd-photo-meta">
                                        <h4><?php echo esc_html( $user_name ); ?></h4>
                                        <span class="sd-role">Music Student</span>
                                        <p class="sd-photo-hint">Learning Level: <?php echo esc_html( $level_label ); ?></p>
                                        <p class="sd-photo-hint">Accepted formats: JPG, PNG. Maximum size: 5MB.</p>
                                        <div class="sd-photo-buttons">
                                            <label class="theme-btn theme-btn-outline photo-upload-label" for="sd-profile-photo">
                                                <i class="far fa-camera"></i> Upload Photo
                                            </label>
                                            <input type="file" id="sd-profile-photo" name="profile_photo"
                                                accept=".jpg,.jpeg,.png,image/jpeg,image/png" hidden>
                                            <button type="button" class="theme-btn theme-btn-outline" id="sd-remove-photo">
                                                <i class="far fa-trash-alt"></i> Remove Photo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- personal details -->
                            <section class="sd-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Personal Details</h3>
                                        <p>Keep your contact information up to date.</p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="first-name">First Name</label>
                                            <input type="text" class="form-control" id="first-name" name="first_name"
                                                value="<?php echo esc_attr( $p['first_name'] ); ?>" autocomplete="given-name">
                                            <span class="field-feedback" data-for="first-name"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="last-name">Last Name</label>
                                            <input type="text" class="form-control" id="last-name" name="last_name"
                                                value="<?php echo esc_attr( $p['last_name'] ); ?>" autocomplete="family-name">
                                            <span class="field-feedback" data-for="last-name"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="username">Username</label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                value="<?php echo esc_attr( $p['username'] ); ?>" autocomplete="username">
                                            <span class="field-feedback" data-for="username"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="<?php echo esc_attr( $p['email'] ); ?>" autocomplete="email">
                                            <span class="field-feedback" data-for="email"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone">Phone Number</label>
                                            <input type="tel" class="form-control" id="phone" name="phone"
                                                value="<?php echo esc_attr( $p['phone'] ); ?>" autocomplete="tel">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="country">Country</label>
                                            <select class="form-control form-select" id="country" name="country">
                                                <option value="">Select country</option>
												<?php foreach ( $countries as $country ) : ?>
                                                <option value="<?php echo esc_attr( $country ); ?>" <?php selected( $p['country'], $country ); ?>><?php echo esc_html( $country ); ?></option>
												<?php endforeach; ?>
                                            </select>
                                            <span class="field-feedback" data-for="country"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="timezone">Timezone</label>
                                            <select class="form-control form-select" id="timezone" name="timezone" data-selected="<?php echo esc_attr( $p['timezone'] ); ?>">
												<?php foreach ( $tz_opts as $tz ) : ?>
                                                <option value="<?php echo esc_attr( $tz ); ?>" <?php selected( $p['timezone'], $tz ); ?>><?php echo esc_html( $tz ); ?></option>
												<?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- learning preferences -->
                            <section class="sd-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Music Learning Preferences</h3>
                                        <p>Help us recommend the right teachers and lessons.</p>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="learning-goals">Learning Goals</label>
                                    <textarea class="form-control" id="learning-goals" name="learning_goals" rows="4"
                                        placeholder="Share what you want to improve..."><?php echo esc_textarea( $p['learning_goals'] ); ?></textarea>
                                    <span class="field-feedback" data-for="learning-goals"></span>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="experience-level">Experience Level</label>
                                            <select class="form-control form-select" id="experience-level" name="experience_level">
												<?php foreach ( $levels as $level ) : ?>
                                                <option value="<?php echo esc_attr( $level ); ?>" <?php selected( strtolower( $p['learning_level'] ), strtolower( $level ) ); ?>><?php echo esc_html( $level ); ?></option>
												<?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="music-style">Favourite Music Style</label>
                                            <select class="form-control form-select" id="music-style" name="music_style">
												<?php foreach ( $styles as $style ) : ?>
                                                <option value="<?php echo esc_attr( $style ); ?>" <?php selected( $p['music_style'], $style ); ?>><?php echo esc_html( $style ); ?></option>
												<?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="d-block mb-2">Preferred Instruments</label>
                                    <div class="sd-instrument-grid" role="group" aria-label="Preferred instruments">
										<?php
										foreach ( $inst_ids as $label => $iid ) :
											$checked = in_array( strtolower( $label ), $inst_lower, true );
											?>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="<?php echo esc_attr( $label ); ?>" id="<?php echo esc_attr( $iid ); ?>" name="instruments[]" <?php checked( $checked ); ?>>
                                            <label class="form-check-label" for="<?php echo esc_attr( $iid ); ?>"><?php echo esc_html( $label ); ?></label>
                                        </div>
										<?php endforeach; ?>
                                    </div>
                                    <span class="field-feedback" data-for="instruments"></span>
                                </div>
                            </section>

                            <!-- about me -->
                            <section class="sd-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>About Me</h3>
                                        <p>Write a short student biography for your learning profile.</p>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="about-me">Short Biography</label>
                                    <textarea class="form-control" id="about-me" name="about_me" rows="5" maxlength="500"
                                        placeholder="Tell teachers a little about your musical journey..."><?php echo esc_textarea( $p['bio'] ); ?></textarea>
                                    <div class="sd-bio-counter-row">
                                        <span class="field-note">Optional — visible on your student profile.</span>
                                        <span class="sd-bio-counter" id="about-counter"><?php echo esc_html( (string) $bio_len ); ?> / 500</span>
                                    </div>
                                </div>
                            </section>

                            <!-- social links -->
                            <section class="sd-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Social Links</h3>
                                        <p>Add optional social profiles.</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-md-0">
                                            <label for="facebook"><i class="fab fa-facebook-f"></i> Facebook</label>
                                            <input type="url" class="form-control" id="facebook" name="facebook"
                                                value="<?php echo esc_attr( $p['facebook'] ); ?>" placeholder="https://facebook.com/username">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-md-0">
                                            <label for="instagram"><i class="fab fa-instagram"></i> Instagram</label>
                                            <input type="url" class="form-control" id="instagram" name="instagram"
                                                value="<?php echo esc_attr( $p['instagram'] ); ?>" placeholder="https://instagram.com/username">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label for="youtube"><i class="fab fa-youtube"></i> YouTube</label>
                                            <input type="url" class="form-control" id="youtube" name="youtube"
                                                value="<?php echo esc_attr( $p['youtube'] ); ?>" placeholder="https://youtube.com/@channel">
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <div class="sd-card-actions sd-profile-form-actions">
                                <button type="submit" class="theme-btn"><i class="far fa-check"></i> Save Changes</button>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'student_dashboard' ) ); ?>" class="theme-btn theme-btn-outline"><i class="far fa-times"></i> Cancel</a>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
        <!-- student profile end -->

    
</div><!-- .gmm-wrapper -->
