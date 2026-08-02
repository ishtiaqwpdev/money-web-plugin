<?php
/**
 * Global role helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Check if the user is a GMM teacher.
 *
 * Prefer current_user_can( 'manage_gmm_*' ) for action-level security.
 *
 * @param int $user_id Optional user ID. Defaults to current user.
 * @return bool
 */
function gmm_is_teacher( $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Roles' ) ) {
		return false;
	}
	return GMM_Roles::is_teacher( $user_id );
}

/**
 * Check if the user is a GMM student.
 *
 * @param int $user_id Optional user ID. Defaults to current user.
 * @return bool
 */
function gmm_is_student( $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Roles' ) ) {
		return false;
	}
	return GMM_Roles::is_student( $user_id );
}

/**
 * Check if the user is a WordPress administrator (manage_options).
 *
 * @param int $user_id Optional user ID. Defaults to current user.
 * @return bool
 */
function gmm_is_admin( $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Roles' ) ) {
		return false;
	}
	return GMM_Roles::is_admin( $user_id );
}
