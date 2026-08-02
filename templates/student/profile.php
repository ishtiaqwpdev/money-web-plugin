<?php
/**
 * Template: student-profile
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

        <!-- student profile -->
        <div class="student-dashboard-area py-120">
            <div class="container">

                <!-- dashboard profile header -->
                <div class="sd-profile-header">
                    <div class="sd-profile-main">
                        <div class="sd-profile-avatar">
                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="<?php echo esc_attr( $user_name ); ?>" id="sd-header-avatar">
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
                                    <span class="login-portal-badge">My Profile</span>
                                    <h3>Student Profile</h3>
                                    <p>Manage your personal information and learning preferences.</p>
                                </div>
                            </div>
                        </section>

                        <form action="#" method="post" id="student-profile-form" class="student-profile-form" novalidate>

                            <div class="gospel-alert gospel-alert-error" id="profile-error" hidden>
                                <i class="far fa-circle-exclamation"></i>
                                <span id="profile-error-text">Please fix the highlighted fields.</span>
                            </div>
                            <div class="gospel-alert gospel-alert-success" id="profile-success" hidden>
                                <i class="far fa-circle-check"></i>
                                <span>Profile changes saved successfully (demo).</span>
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
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="<?php echo esc_attr( $user_name ); ?>" id="sd-profile-photo-img">
                                    </div>
                                    <div class="sd-photo-meta">
                                        <h4>Sarah Johnson</h4>
                                        <span class="sd-role">Music Student</span>
                                        <p class="sd-photo-hint">Learning Level: Intermediate</p>
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
                                                value="Sarah" autocomplete="given-name">
                                            <span class="field-feedback" data-for="first-name"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="last-name">Last Name</label>
                                            <input type="text" class="form-control" id="last-name" name="last_name"
                                                value="Johnson" autocomplete="family-name">
                                            <span class="field-feedback" data-for="last-name"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="username">Username</label>
                                            <input type="text" class="form-control" id="username" name="username"
                                                value="sarahjohnson" autocomplete="username">
                                            <span class="field-feedback" data-for="username"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="email">Email Address</label>
                                            <input type="email" class="form-control" id="email" name="email"
                                                value="sarah@example.com" autocomplete="email">
                                            <span class="field-feedback" data-for="email"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="phone">Phone Number</label>
                                            <input type="tel" class="form-control" id="phone" name="phone"
                                                value="+1 615 555 0198" autocomplete="tel">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="country">Country</label>
                                            <select class="form-control form-select" id="country" name="country">
                                                <option value="">Select country</option>
                                                <option value="United States" selected>United States</option>
                                                <option value="Canada">Canada</option>
                                                <option value="United Kingdom">United Kingdom</option>
                                                <option value="Australia">Australia</option>
                                                <option value="Other">Other</option>
                                            </select>
                                            <span class="field-feedback" data-for="country"></span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group mb-0">
                                            <label for="timezone">Timezone</label>
                                            <select class="form-control form-select" id="timezone" name="timezone" data-selected="America/New_York">
                                                <option value="Eastern Time" selected>Eastern Time</option>
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
                                        placeholder="Share what you want to improve...">Improve worship piano skills and learn to play confidently in church settings.</textarea>
                                    <span class="field-feedback" data-for="learning-goals"></span>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="experience-level">Experience Level</label>
                                            <select class="form-control form-select" id="experience-level" name="experience_level">
                                                <option value="Beginner">Beginner</option>
                                                <option value="Intermediate" selected>Intermediate</option>
                                                <option value="Advanced">Advanced</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="music-style">Favourite Music Style</label>
                                            <select class="form-control form-select" id="music-style" name="music_style">
                                                <option value="Gospel" selected>Gospel</option>
                                                <option value="Worship">Worship</option>
                                                <option value="Contemporary Christian">Contemporary Christian</option>
                                                <option value="Choir">Choir</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group mb-0">
                                    <label class="d-block mb-2">Preferred Instruments</label>
                                    <div class="sd-instrument-grid" role="group" aria-label="Preferred instruments">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="Piano" id="inst-piano" name="instruments[]" checked>
                                            <label class="form-check-label" for="inst-piano">Piano</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="Guitar" id="inst-guitar" name="instruments[]" checked>
                                            <label class="form-check-label" for="inst-guitar">Guitar</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="Vocals" id="inst-vocals" name="instruments[]" checked>
                                            <label class="form-check-label" for="inst-vocals">Vocals</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="Drums" id="inst-drums" name="instruments[]" checked>
                                            <label class="form-check-label" for="inst-drums">Drums</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="Music Theory" id="inst-theory" name="instruments[]" checked>
                                            <label class="form-check-label" for="inst-theory">Music Theory</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="Worship Leadership" id="inst-worship" name="instruments[]" checked>
                                            <label class="form-check-label" for="inst-worship">Worship Leadership</label>
                                        </div>
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
                                        placeholder="Tell teachers a little about your musical journey...">I am passionate about gospel worship music and currently growing my piano skills for church ministry.</textarea>
                                    <div class="sd-bio-counter-row">
                                        <span class="field-note">Optional — visible on your student profile.</span>
                                        <span class="sd-bio-counter" id="about-counter">0 / 500</span>
                                    </div>
                                </div>
                            </section>

                            <!-- social links -->
                            <section class="sd-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Social Links</h3>
                                        <p>Add optional social profiles (frontend demo only).</p>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group mb-md-0">
                                            <label for="facebook"><i class="fab fa-facebook-f"></i> Facebook</label>
                                            <input type="url" class="form-control" id="facebook" name="facebook"
                                                value="https://facebook.com/sarahjohnson" placeholder="https://facebook.com/username">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-md-0">
                                            <label for="instagram"><i class="fab fa-instagram"></i> Instagram</label>
                                            <input type="url" class="form-control" id="instagram" name="instagram"
                                                value="https://instagram.com/sarahjohnson" placeholder="https://instagram.com/username">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group mb-0">
                                            <label for="youtube"><i class="fab fa-youtube"></i> YouTube</label>
                                            <input type="url" class="form-control" id="youtube" name="youtube"
                                                value="https://youtube.com/@sarahjohnson" placeholder="https://youtube.com/@channel">
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

