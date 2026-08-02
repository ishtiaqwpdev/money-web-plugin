<?php
/**
 * Global student helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get student profile by WP user ID.
 *
 * @param int $user_id Optional user ID.
 * @return array<string, mixed>|null
 */
function gmm_get_student_profile( $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Student' ) ) {
		return null;
	}
	return GMM_Student::get_profile( $user_id );
}

/**
 * Update student profile (owner only).
 *
 * @param int                  $user_id User ID.
 * @param array<string, mixed> $data    Fields.
 * @return true|WP_Error
 */
function gmm_update_student_profile( $user_id = 0, $data = array() ) {
	if ( ! class_exists( 'GMM_Student' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Student system unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Student::update_profile( $user_id, $data );
}

/**
 * Student dashboard aggregates.
 *
 * @param int $user_id Optional user ID.
 * @return array<string, mixed>
 */
function gmm_get_student_dashboard_data( $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Student' ) ) {
		return array(
			'total_lessons'      => 0,
			'upcoming_lessons'   => 0,
			'completed_lessons'  => 0,
			'favourite_teachers' => 0,
			'total_payments'     => 0.0,
		);
	}
	return GMM_Student::get_dashboard_data( $user_id );
}

/**
 * Verify a student action nonce.
 *
 * @param string $nonce  Nonce value.
 * @param string $action Action name.
 * @return bool
 */
function gmm_verify_student_nonce( $nonce, $action = 'gmm_student_action' ) {
	return (bool) wp_verify_nonce( (string) $nonce, $action );
}

/**
 * Student nonce field HTML.
 *
 * @param string $action Action name.
 * @return string
 */
function gmm_student_nonce_field( $action = 'gmm_student_action' ) {
	return wp_nonce_field( $action, 'gmm_student_nonce', true, false );
}
