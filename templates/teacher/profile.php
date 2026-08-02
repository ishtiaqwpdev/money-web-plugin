<?php
/**
 * Template: teacher-profile
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'teacher_login' ) ); ?>" class="td-nav-link td-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
                            </ul>
                        </nav>
                    </aside>
                    <div class="td-sidebar-backdrop" id="td-sidebar-backdrop" hidden></div>

                    <!-- main content -->
                    <div class="td-main">

                        <form action="#" method="post" id="teacher-profile-form" class="teacher-profile-form" novalidate>

                            <div class="gospel-alert gospel-alert-success" id="profile-success" hidden>
                                <i class="far fa-circle-check"></i>
                                <span>Profile changes saved successfully (demo).</span>
                            </div>

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
                                            <input type="text" class="form-control" id="profile-first-name" name="first_name" value="John" autocomplete="given-name">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-last-name">Last Name</label>
                                            <input type="text" class="form-control" id="profile-last-name" name="last_name" value="Smith" autocomplete="family-name">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-username">Username</label>
                                            <input type="text" class="form-control" id="profile-username" name="username" value="johnsmith" autocomplete="username">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-email">Email</label>
                                            <input type="email" class="form-control" id="profile-email" name="email" value="john@example.com" autocomplete="email">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-phone">Phone</label>
                                            <input type="tel" class="form-control" id="profile-phone" name="phone" value="+1 615 555 4821" autocomplete="tel">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-skill">Skill / Occupation</label>
                                            <select class="form-control form-select" id="profile-skill" name="skill">
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
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-timezone">Timezone</label>
                                            <select class="form-control form-select" id="profile-timezone" name="timezone" data-selected="America/New_York">
                                                <option value="Eastern Time" selected>Eastern Time</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-display-name">Display Name</label>
                                            <input type="text" class="form-control" id="profile-display-name" name="display_name" value="John Smith">
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
                                    <textarea class="form-control" id="profile-bio" name="biography" rows="6" maxlength="500">Experienced gospel music instructor helping students grow in piano, worship leadership, and musical confidence.</textarea>
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
                                            <input type="url" class="form-control" id="profile-facebook" name="facebook" value="https://facebook.com/johnsmith" placeholder="https://facebook.com/username">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-instagram"><i class="fab fa-instagram"></i> Instagram</label>
                                            <input type="url" class="form-control" id="profile-instagram" name="instagram" value="https://instagram.com/johnsmith" placeholder="https://instagram.com/username">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="profile-youtube"><i class="fab fa-youtube"></i> YouTube</label>
                                            <input type="url" class="form-control" id="profile-youtube" name="youtube" value="https://youtube.com/@johnsmith" placeholder="https://youtube.com/@channel">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="profile-website"><i class="far fa-globe"></i> Website</label>
                                            <input type="url" class="form-control" id="profile-website" name="website" value="https://johnsmithmusic.com" placeholder="https://yourwebsite.com">
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

