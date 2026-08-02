<?php
/**
 * Template: teacher-profile
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $gmm_teacher_pending ) || ! empty( $gmm_teacher_denied ) ) {
	$msg = ! empty( $gmm_teacher_pending )
		? __( 'Your account is waiting for approval.', 'gospel-music-mastery' )
		: __( 'You do not have permission to view this profile.', 'gospel-music-mastery' );
	echo '<div class="gmm-wrapper gmm-dashboard"><div class="teacher-dashboard-area py-120"><div class="container"><div class="td-card"><div class="td-card-head"><h3>' . esc_html( $msg ) . '</h3></div></div></div></div></div>';
	return;
}

$profile = ( isset( $profile ) && is_array( $profile ) ) ? $profile : array();
$profile_stats = ( isset( $profile_stats ) && is_array( $profile_stats ) ) ? $profile_stats : array(
	'rating'   => 0,
	'students' => 0,
	'classes'  => 0,
);
$logout_url    = isset( $logout_url ) ? $logout_url : ( function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ) );
$flash_success = isset( $flash_success ) ? (string) $flash_success : '';
$flash_error   = isset( $flash_error ) ? (string) $flash_error : '';

$p = wp_parse_args(
	$profile,
	array(
		'first_name'     => '',
		'last_name'      => '',
		'display_name'   => '',
		'username'       => '',
		'email'          => '',
		'phone'          => '',
		'specialization' => '',
		'skill'          => '',
		'experience'     => '',
		'bio'            => '',
		'timezone'       => 'Eastern Time',
		'facebook'       => '',
		'instagram'      => '',
		'youtube'        => '',
		'website'        => '',
		'image_url'      => gmm_design_asset_url( 'assets/img/team/01.jpg' ),
		'rating'         => 0,
		'status'         => '',
	)
);

$skill = $p['skill'] ? $p['skill'] : $p['specialization'];
$role  = $skill ? $skill : 'Gospel Music Instructor';

if ( ! isset( $user_name ) || '' === $user_name ) {
	$user_name = $p['display_name'] ? $p['display_name'] : trim( $p['first_name'] . ' ' . $p['last_name'] );
}
if ( ! $user_name ) {
	$user_name = 'Guest';
}
if ( ! isset( $user_first_name ) ) {
	$user_first_name = $p['first_name'] ? $p['first_name'] : $user_name;
}

$rating_disp = (float) $profile_stats['rating'] > 0
	? number_format_i18n( (float) $profile_stats['rating'], 1 )
	: ( (float) $p['rating'] > 0 ? number_format_i18n( (float) $p['rating'], 1 ) : '—' );

$skill_options = array(
	'Piano & Keys'         => array( 'Gospel Piano Instructor', 'Worship Piano Instructor', 'Keyboard Instructor', 'Organ Instructor' ),
	'Vocals'               => array( 'Vocal Coach', 'Gospel Vocal Instructor', 'Worship Vocal Coach', 'Choir Director', 'Backing Vocals Coach' ),
	'Guitar & Bass'        => array( 'Guitar Instructor', 'Acoustic Guitar Instructor', 'Electric Guitar Instructor', 'Bass Guitar Instructor' ),
	'Drums & Percussion'   => array( 'Drums Instructor', 'Percussion Instructor' ),
	'Worship & Leadership' => array( 'Worship Leader', 'Worship Leadership Coach', 'Music Director', 'Band Director' ),
	'Theory & Production'  => array( 'Music Theory Instructor', 'Songwriting Coach', 'Music Production Instructor', 'Audio Engineering Instructor' ),
	'Other'                => array( 'Violin Instructor', 'Saxophone Instructor', 'Trumpet Instructor', 'Flute Instructor', 'General Music Teacher' ),
);

$tz_options = array( 'Eastern Time', 'Central Time', 'Mountain Time', 'Pacific Time', 'UTC' );
if ( $p['timezone'] && ! in_array( $p['timezone'], $tz_options, true ) ) {
	array_unshift( $tz_options, $p['timezone'] );
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
                            <img src="<?php echo esc_url( $p['image_url'] ); ?>" alt="<?php echo esc_attr( $user_name ); ?>">
                        </div>
                        <div class="td-profile-meta">
                            <h2><?php echo esc_html( $user_name ); ?></h2>
                            <span class="td-role"><?php echo esc_html( $role ); ?></span>
                            <div class="td-profile-stats">
                                <span class="td-stat-item"><i class="fas fa-star"></i> <?php echo esc_html( $rating_disp ); ?></span>
                                <span class="td-stat-item"><i class="far fa-users"></i> <?php echo esc_html( (int) $profile_stats['students'] ); ?> Students</span>
                                <span class="td-stat-item"><i class="far fa-books"></i> <?php echo esc_html( (int) $profile_stats['classes'] ); ?> Classes</span>
                            </div>
                        </div>
                    </div>
                    <div class="td-profile-actions">
                        <a href="#teacher-profile-form" class="theme-btn"><i class="far fa-user-pen"></i> Edit Profile</a>
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_profile' ) ); ?>" class="td-nav-link active" data-nav="profile"><i class="far fa-user"></i> My Profile</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_availability' ) ); ?>" class="td-nav-link" data-nav="availability"><i class="far fa-calendar-days"></i> Availability</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_bookings' ) ); ?>" class="td-nav-link" data-nav="bookings"><i class="far fa-calendar-check"></i> My Bookings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_classes' ) ); ?>" class="td-nav-link" data-nav="classes"><i class="far fa-chalkboard"></i> My Classes</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_dashboard' ) ); ?>" class="td-nav-link" data-nav="messages"><i class="far fa-comments"></i> Messages</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_withdrawals' ) ); ?>" class="td-nav-link" data-nav="withdrawals"><i class="far fa-wallet"></i> Withdrawals</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_settings' ) ); ?>" class="td-nav-link" data-nav="settings"><i class="far fa-gear"></i> Settings</a></li>
                                <li><a href="<?php echo esc_url( $logout_url ); ?>" class="td-nav-link td-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
                            </ul>
                        </nav>
                    </aside>
                    <div class="td-sidebar-backdrop" id="td-sidebar-backdrop" hidden></div>

                    <!-- main content -->
                    <div class="td-main">

                        <form action="#" method="post" id="teacher-profile-form" class="teacher-profile-form" novalidate>

                            <div class="gospel-alert gospel-alert-success" id="profile-success" <?php echo $flash_success ? '' : 'hidden'; ?>>
                                <i class="far fa-circle-check"></i>
                                <span><?php echo $flash_success ? esc_html( $flash_success ) : esc_html__( 'Profile changes saved successfully.', 'gospel-music-mastery' ); ?></span>
                            </div>
							<?php if ( $flash_error ) : ?>
                            <div class="gospel-alert gospel-alert-error" role="alert">
                                <i class="far fa-circle-exclamation"></i>
                                <span><?php echo esc_html( $flash_error ); ?></span>
                            </div>
							<?php endif; ?>

                            <!-- profile information -->
                            <section class="td-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Profile Information</h3>
                                        <p>Update the details students see on your public instructor profile.</p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-first-name">First Name</label>
                                            <input type="text" class="form-control" id="profile-first-name" name="first_name" value="<?php echo esc_attr( $p['first_name'] ); ?>" autocomplete="given-name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-last-name">Last Name</label>
                                            <input type="text" class="form-control" id="profile-last-name" name="last_name" value="<?php echo esc_attr( $p['last_name'] ); ?>" autocomplete="family-name">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-username">Username</label>
                                            <input type="text" class="form-control" id="profile-username" name="username" value="<?php echo esc_attr( $p['username'] ); ?>" autocomplete="username" readonly>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-email">Email</label>
                                            <input type="email" class="form-control" id="profile-email" name="email" value="<?php echo esc_attr( $p['email'] ); ?>" autocomplete="email">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-phone">Phone</label>
                                            <input type="tel" class="form-control" id="profile-phone" name="phone" value="<?php echo esc_attr( $p['phone'] ); ?>" autocomplete="tel">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-skill">Skill / Occupation</label>
                                            <select class="form-control form-select" id="profile-skill" name="skill">
                                                <option value="">Select skill / occupation</option>
												<?php foreach ( $skill_options as $group => $options ) : ?>
                                                <optgroup label="<?php echo esc_attr( $group ); ?>">
													<?php foreach ( $options as $opt ) : ?>
                                                    <option value="<?php echo esc_attr( $opt ); ?>" <?php selected( $skill, $opt ); ?>><?php echo esc_html( $opt ); ?></option>
													<?php endforeach; ?>
                                                </optgroup>
												<?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-timezone">Timezone</label>
                                            <select class="form-control form-select" id="profile-timezone" name="timezone" data-selected="<?php echo esc_attr( $p['timezone'] ); ?>">
												<?php foreach ( $tz_options as $tz ) : ?>
                                                <option value="<?php echo esc_attr( $tz ); ?>" <?php selected( $p['timezone'], $tz ); ?>><?php echo esc_html( $tz ); ?></option>
												<?php endforeach; ?>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-display-name">Display Name</label>
                                            <input type="text" class="form-control" id="profile-display-name" name="display_name" value="<?php echo esc_attr( $p['display_name'] ); ?>">
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <!-- biography -->
                            <section class="td-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Biography</h3>
                                        <p>Share your teaching experience and musical background.</p>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="profile-bio">About You</label>
                                    <textarea class="form-control" id="profile-bio" name="biography" rows="6" maxlength="500"><?php echo esc_textarea( $p['bio'] ); ?></textarea>
                                    <div class="bio-counter-row">
                                        <span class="field-note">Visible on your public teacher profile.</span>
                                        <span class="bio-counter" id="bio-counter">0 / 500</span>
                                    </div>
                                </div>
                            </section>

                            <!-- social profile -->
                            <section class="td-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Social Profile</h3>
                                        <p>Add links so students can follow your ministry and music.</p>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-facebook"><i class="fab fa-facebook-f"></i> Facebook</label>
                                            <input type="url" class="form-control" id="profile-facebook" name="facebook" value="<?php echo esc_attr( $p['facebook'] ); ?>" placeholder="https://facebook.com/username">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-instagram"><i class="fab fa-instagram"></i> Instagram</label>
                                            <input type="url" class="form-control" id="profile-instagram" name="instagram" value="<?php echo esc_attr( $p['instagram'] ); ?>" placeholder="https://instagram.com/username">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-youtube"><i class="fab fa-youtube"></i> YouTube</label>
                                            <input type="url" class="form-control" id="profile-youtube" name="youtube" value="<?php echo esc_attr( $p['youtube'] ); ?>" placeholder="https://youtube.com/@channel">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="profile-website"><i class="far fa-globe"></i> Website</label>
                                            <input type="url" class="form-control" id="profile-website" name="website" value="<?php echo esc_attr( $p['website'] ); ?>" placeholder="https://yourwebsite.com">
                                        </div>
                                    </div>
                                </div>
                            </section>

                            <div class="td-card-actions td-profile-form-actions">
                                <button type="submit" class="theme-btn"><i class="far fa-check"></i> Save Changes</button>
                                <a href="<?php echo esc_url( gmm_get_page_link( 'teacher_dashboard' ) ); ?>" class="theme-btn theme-btn-outline"><i class="far fa-times"></i> Cancel</a>
                            </div>

                        </form>

                    </div>
                </div>
            </div>
        </div>
        <!-- teacher dashboard end -->

    
</div><!-- .gmm-wrapper -->
