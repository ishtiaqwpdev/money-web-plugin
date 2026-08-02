<?php
/**
 * Template: teacher-login
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
<div class="gmm-wrapper gmm-frontend">

<!-- teacher login area -->
        <div class="login-area py-120">
            <div class="container">
                <div class="col-md-5 mx-auto">
                    <div class="login-form">
                        <div class="login-header">
                            <span class="login-portal-badge">Teacher Portal</span>
                            <h2>Welcome Back</h2>
                            <p class="login-desc">Sign in to manage your profile, classes, availability, bookings, and earnings.</p>
                        </div>
                        <form action="#" method="post" id="teacher-login-form" novalidate>
                            <div class="form-group">
                                <label for="teacher-username">Username or Email</label>
                                <input type="text" class="form-control" id="teacher-username" name="username"
                                    placeholder="Enter username or email" autocomplete="username">
                            </div>
                            <div class="form-group">
                                <label for="teacher-password">Password</label>
                                <div class="password-field">
                                    <input type="password" class="form-control" id="teacher-password" name="password"
                                        placeholder="Enter your password" autocomplete="current-password">
                                    <button type="button" class="password-toggle" id="password-toggle"
                                        aria-label="Show password" title="Show password">
                                        <i class="far fa-eye" id="password-toggle-icon"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="" id="keep-signed-in">
                                    <label class="form-check-label" for="keep-signed-in">
                                        Keep me signed in
                                    </label>
                                </div>
                                <a href="forgot-password.html" class="forgot-pass">Forgot Password?</a>
                            </div>
                            <div class="d-flex align-items-center">
                                <button type="submit" class="theme-btn"><i class="far fa-sign-in"></i> Sign In</button>
                            </div>
                        </form>
                        <div class="login-footer">
                            <p>New to Gospel Music Mastery? <a href="<?php echo esc_url( gmm_get_page_link( 'teacher_register' ) ); ?>">Register as a Teacher</a></p>
                            <p>Student or admin? <a href="login.html">Choose another portal</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- teacher login area end -->

    
</div><!-- .gmm-wrapper -->

