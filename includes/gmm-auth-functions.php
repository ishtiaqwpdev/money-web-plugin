<?php
/**
 * Authentication helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register a student account.
 *
 * @param array<string, mixed> $data  Fields.
 * @param string               $nonce Optional nonce.
 * @return int|WP_Error User ID.
 */
function gmm_student_register( $data, $nonce = '' ) {
	if ( class_exists( 'GMM_Student_Auth' ) ) {
		return GMM_Student_Auth::register( $data, $nonce );
	}
	if ( ! class_exists( 'GMM_Auth' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Authentication system unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Auth::student_register( $data, $nonce );
}

/**
 * Log in a student via the student portal.
 *
 * @param string $login    Username or email.
 * @param string $password Password.
 * @param bool   $remember Remember me.
 * @param string $nonce    Optional nonce.
 * @return WP_User|WP_Error
 */
function gmm_student_login( $login, $password, $remember = false, $nonce = '' ) {
	if ( class_exists( 'GMM_Student_Auth' ) ) {
		return GMM_Student_Auth::login( $login, $password, $remember, $nonce );
	}
	return gmm_login_user( $login, $password, $remember, class_exists( 'GMM_Roles' ) ? GMM_Roles::ROLE_STUDENT : 'gmm_student', $nonce );
}

/**
 * Whether the user can access the student dashboard.
 *
 * @param int $user_id Optional WP user ID.
 * @return bool
 */
function gmm_student_can_access_dashboard( $user_id = 0 ) {
	if ( class_exists( 'GMM_Student_Auth' ) ) {
		return GMM_Student_Auth::can_access_dashboard( $user_id );
	}
	$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
	if ( ! $user_id || ! is_user_logged_in() ) {
		return false;
	}
	if ( function_exists( 'gmm_is_admin' ) && gmm_is_admin( $user_id ) ) {
		return true;
	}
	return function_exists( 'gmm_is_student' ) && gmm_is_student( $user_id );
}

/**
 * Require student dashboard access or redirect to login.
 *
 * @return void
 */
function gmm_require_student_dashboard() {
	if ( class_exists( 'GMM_Student_Auth' ) ) {
		GMM_Student_Auth::require_dashboard_access();
		return;
	}
	gmm_require_student();
}

/**
 * Register a teacher account (pending approval).
 *
 * @param array<string, mixed> $data  Fields.
 * @param string               $nonce Optional nonce.
 * @return int|WP_Error User ID.
 */
function gmm_teacher_register( $data, $nonce = '' ) {
	if ( class_exists( 'GMM_Teacher_Auth' ) ) {
		return GMM_Teacher_Auth::register( $data, $nonce );
	}
	if ( class_exists( 'GMM_Auth' ) ) {
		return GMM_Auth::teacher_register( $data, $nonce );
	}
	return new WP_Error( 'gmm_missing', __( 'Authentication system unavailable.', 'gospel-music-mastery' ) );
}

/**
 * Log in a user.
 *
 * @param string $login       Username or email.
 * @param string $password    Password.
 * @param bool   $remember    Remember me.
 * @param string $expect_role Optional role.
 * @param string $nonce       Optional nonce.
 * @return WP_User|WP_Error
 */
function gmm_login_user( $login, $password, $remember = false, $expect_role = '', $nonce = '' ) {
	if ( ! class_exists( 'GMM_Auth' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Authentication system unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Auth::login_user( $login, $password, $remember, $expect_role, $nonce );
}

/**
 * Log out current user.
 *
 * @param string $nonce Optional nonce.
 * @return void
 */
function gmm_logout_user( $nonce = '' ) {
	if ( class_exists( 'GMM_Auth' ) ) {
		GMM_Auth::logout_user( $nonce );
	}
}

/**
 * Require login or redirect.
 *
 * @param string $redirect_to Login URL.
 * @return void
 */
function gmm_require_login( $redirect_to = '' ) {
	if ( class_exists( 'GMM_Auth' ) ) {
		GMM_Auth::require_login( $redirect_to );
	}
}

/**
 * Require student role or redirect.
 *
 * @return void
 */
function gmm_require_student() {
	if ( class_exists( 'GMM_Auth' ) ) {
		GMM_Auth::require_student();
	}
}

/**
 * Require teacher role or redirect.
 *
 * @return void
 */
function gmm_require_teacher() {
	if ( class_exists( 'GMM_Auth' ) ) {
		GMM_Auth::require_teacher();
	}
}

/**
 * Auth nonce field HTML.
 *
 * @return string
 */
function gmm_auth_nonce_field() {
	return class_exists( 'GMM_Auth' ) ? GMM_Auth::nonce_field() : '';
}

/**
 * Verify auth nonce.
 *
 * @param string $nonce Nonce.
 * @return bool
 */
function gmm_verify_auth_nonce( $nonce ) {
	return class_exists( 'GMM_Auth' ) ? GMM_Auth::verify_nonce( $nonce ) : false;
}

/**
 * Logout URL with nonce.
 *
 * @param string $redirect Redirect after logout.
 * @return string
 */
function gmm_logout_url( $redirect = '' ) {
	$url = wp_nonce_url(
		add_query_arg(
			array(
				'gmm_logout' => '1',
			),
			$redirect ? $redirect : home_url( '/' )
		),
		GMM_Auth::NONCE_ACTION
	);
	return esc_url( $url );
}
