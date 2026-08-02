<?php
/**
 * Template: student-login
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
<div class="gmm-wrapper gmm-frontend"><!-- student login area -->
        <div class="login-area py-120">
            <div class="container">
                <div class="col-md-5 mx-auto">
                    <div class="login-form student-login-form">
                        <div class="login-header">
                            <span class="login-portal-badge">Student Portal</span>
                            <h2>Welcome Back Student</h2>
                            <p class="login-desc">Sign in to access your lessons, bookings, teachers, and learning dashboard.</p>
                        </div>

                        <div class="gospel-alert gospel-alert-error" id="student-login-error" hidden>
                            <i class="far fa-circle-exclamation"></i>
                            <span id="student-login-error-text">Please enter your email and password.</span>
                        </div>

                        <form action="#" method="post" id="student-login-form" novalidate>
                            <div class="form-group">
                                <label for="student-username">Email Address or Username</label>
                                <input type="text" class="form-control" id="student-username" name="username"
                                    placeholder="Enter email or username" autocomplete="username">
                                <span class="field-feedback" id="student-username-feedback"></span>
                            </div>
                            <div class="form-group">
                                <label for="student-password">Password</label>
                                <div class="password-field">
                                    <input type="password" class="form-control" id="student-password" name="password"
                                        placeholder="Enter your password" autocomplete="current-password">
                                    <button type="button" class="password-toggle" id="password-toggle"
                                        aria-label="Show password" title="Show password">
                                        <i class="far fa-eye" id="password-toggle-icon"></i>
                                    </button>
                                </div>
                                <span class="field-feedback" id="student-password-feedback"></span>
                            </div>
                            <div class="d-flex justify-content-between mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" value="1" id="remember-me">
                                    <label class="form-check-label" for="remember-me">
                                        Remember Me
                                    </label>
                                </div>
                                <a href="forgot-password.html?from=student" class="forgot-pass">Forgot Password?</a>
                            </div>
                            <div class="d-flex align-items-center">
                                <button type="submit" class="theme-btn w-100"><i class="far fa-sign-in"></i> Sign In</button>
                            </div>
                        </form>

                        <div class="login-footer">
                            <p>Don't have a student account? <a href="<?php echo esc_url( gmm_get_page_link( 'student_register' ) ); ?>">Register as Student</a></p>
                            <p>Teacher or admin? <a href="login.html">Choose another portal</a></p>
                        </div>

                        <div class="login-or-divider" aria-hidden="true">
                            <span>OR</span>
                        </div>

                        <div class="student-social-login">
                            <button type="button" class="theme-btn-outline student-social-btn" id="student-google-btn">
                                <i class="fab fa-google"></i> Continue with Google
                            </button>
                            <button type="button" class="theme-btn-outline student-social-btn" id="student-facebook-btn">
                                <i class="fab fa-facebook-f"></i> Continue with Facebook
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- student login area end -->

    
</div><!-- .gmm-wrapper -->

