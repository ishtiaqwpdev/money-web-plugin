<?php
/**
 * Template: student-register
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
<div class="gmm-wrapper gmm-frontend"><!-- student registration area -->
        <div class="login-area py-120">
            <div class="container">
                <div class="col-lg-7 col-md-9 mx-auto">
                    <div class="login-form student-login-form teacher-register-form">
                        <div class="login-header">
                            <span class="login-portal-badge">Student Portal</span>
                            <h2>Create Your Student Account</h2>
                            <p class="login-desc">Register to book gospel music lessons, connect with teachers, and track your learning progress.</p>
                        </div>

                        <div class="gospel-alert gospel-alert-error" id="register-error" hidden>
                            <i class="far fa-circle-exclamation"></i>
                            <span id="register-error-text">Please fill in all required fields correctly.</span>
                        </div>
                        <div class="gospel-alert gospel-alert-success" id="register-success" hidden>
                            <i class="far fa-circle-check"></i>
                            <span>Registration successful (demo). Redirecting to the enrollment agreement…</span>
                        </div>

                        <form action="#" method="post" id="student-register-form" novalidate>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="first-name">First Name</label>
                                        <input type="text" class="form-control" id="first-name" name="first_name"
                                            placeholder="Enter first name" autocomplete="given-name">
                                        <span class="field-feedback" data-for="first-name"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="last-name">Last Name</label>
                                        <input type="text" class="form-control" id="last-name" name="last_name"
                                            placeholder="Enter last name" autocomplete="family-name">
                                        <span class="field-feedback" data-for="last-name"></span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="reg-username">Username</label>
                                <input type="text" class="form-control" id="reg-username" name="username"
                                    placeholder="Choose a username" autocomplete="username">
                                <span class="field-feedback" data-for="reg-username"></span>
                            </div>
                            <div class="form-group">
                                <label for="reg-email">Email Address</label>
                                <input type="email" class="form-control" id="reg-email" name="email"
                                    placeholder="Enter email address" autocomplete="email">
                                <span class="field-feedback" data-for="reg-email"></span>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="reg-password">Password</label>
                                        <div class="password-field">
                                            <input type="password" class="form-control" id="reg-password" name="password"
                                                placeholder="Create password" autocomplete="new-password">
                                            <button type="button" class="password-toggle" data-target="reg-password"
                                                aria-label="Show password" title="Show password">
                                                <i class="far fa-eye"></i>
                                            </button>
                                        </div>
                                        <span class="field-feedback" data-for="reg-password"></span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="reg-confirm-password">Confirm Password</label>
                                        <div class="password-field">
                                            <input type="password" class="form-control" id="reg-confirm-password"
                                                name="confirm_password" placeholder="Confirm password"
                                                autocomplete="new-password">
                                            <button type="button" class="password-toggle"
                                                data-target="reg-confirm-password" aria-label="Show password"
                                                title="Show password">
                                                <i class="far fa-eye"></i>
                                            </button>
                                        </div>
                                        <span class="field-feedback" data-for="reg-confirm-password"></span>
                                    </div>
                                </div>
                            </div>

                            <div class="agreement-area">
                                <div class="form-check form-group">
                                    <input class="form-check-input" type="checkbox" value="1" id="agree-agreement"
                                        name="agree_agreement">
                                    <label class="form-check-label" for="agree-agreement">
                                        I have read and agree to the Student Enrollment Agreement.
                                    </label>
                                    <span class="field-feedback" data-for="agree-agreement"></span>
                                </div>
                                <a href="student-agreement.html" class="theme-btn theme-btn-outline">
                                    <i class="far fa-file-signature"></i> Read Enrollment Agreement
                                </a>
                            </div>

                            <div class="d-flex align-items-center">
                                <button type="submit" class="theme-btn">
                                    <i class="far fa-user-plus"></i> Register as Student
                                </button>
                            </div>
                        </form>
                        <div class="login-footer">
                            <p>Already have an account? <a href="<?php echo esc_url( gmm_get_page_link( 'student_login' ) ); ?>">Sign In</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- student registration area end -->

    
</div><!-- .gmm-wrapper -->

