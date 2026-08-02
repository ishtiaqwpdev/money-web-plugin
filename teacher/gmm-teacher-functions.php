<?php
/**
 * Global teacher helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get teacher profile by WP user ID.
 *
 * @param int $user_id Optional. Defaults to current user.
 * @return array<string, mixed>|null
 */
function gmm_get_teacher_profile( $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Teacher' ) ) {
		return null;
	}
	return GMM_Teacher::get_profile( $user_id );
}

/**
 * Update teacher profile (owner only).
 *
 * @param int                  $user_id User ID.
 * @param array<string, mixed> $data    Fields.
 * @return true|WP_Error
 */
function gmm_update_teacher_profile( $user_id = 0, $data = array() ) {
	if ( ! class_exists( 'GMM_Teacher' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Teacher system unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Teacher::update_profile( $user_id, $data );
}

/**
 * Teacher dashboard aggregates.
 *
 * @param int $user_id Optional user ID.
 * @return array<string, mixed>
 */
function gmm_get_teacher_dashboard_data( $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Teacher' ) ) {
		return array(
			'total_classes'       => 0,
			'total_students'      => 0,
			'upcoming_lessons'    => 0,
			'completed_lessons'   => 0,
			'total_earnings'      => 0.0,
			'pending_withdrawals' => 0.0,
		);
	}
	return GMM_Teacher::get_dashboard_data( $user_id );
}

/**
 * Verify a teacher action nonce.
 *
 * @param string $nonce  Nonce value.
 * @param string $action Action name (default gmm_teacher_action).
 * @return bool
 */
function gmm_verify_teacher_nonce( $nonce, $action = 'gmm_teacher_action' ) {
	return (bool) wp_verify_nonce( (string) $nonce, $action );
}

/**
 * Create a teacher action nonce field HTML.
 *
 * @param string $action Action name.
 * @return string
 */
function gmm_teacher_nonce_field( $action = 'gmm_teacher_action' ) {
	return wp_nonce_field( $action, 'gmm_teacher_nonce', true, false );
}
