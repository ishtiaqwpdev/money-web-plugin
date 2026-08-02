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
 * Returns student information, learning details, and booking summary.
 *
 * @param int $user_id Optional user ID.
 * @return array<string, mixed>|null
 */
function gmm_get_student_profile( $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Student' ) ) {
		return null;
	}

	$row = GMM_Student::get_profile( $user_id );
	if ( ! is_array( $row ) ) {
		return null;
	}

	$user_id    = isset( $row['user_id'] ) ? absint( $row['user_id'] ) : absint( $user_id );
	$student_id = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
	$dashboard  = function_exists( 'gmm_get_student_dashboard_data' )
		? gmm_get_student_dashboard_data( $user_id )
		: ( class_exists( 'GMM_Student' ) ? GMM_Student::get_dashboard_data( $user_id ) : array() );

	$bookings = array();
	if ( $user_id && class_exists( 'GMM_Student_Bookings' ) ) {
		$bookings = GMM_Student_Bookings::get_bookings( $user_id, array( 'limit' => 10 ) );
	}

	return array_merge(
		$row,
		array(
			'student_id'       => $student_id,
			'display_name'     => trim( ( isset( $row['first_name'] ) ? $row['first_name'] : '' ) . ' ' . ( isset( $row['last_name'] ) ? $row['last_name'] : '' ) ),
			'learning'         => array(
				'level'                 => isset( $row['learning_level'] ) ? (string) $row['learning_level'] : '',
				'goals'                 => isset( $row['learning_goals'] ) ? (string) $row['learning_goals'] : '',
				'preferred_instruments' => isset( $row['preferred_instruments'] ) ? (string) $row['preferred_instruments'] : '',
			),
			'bookings'         => is_array( $bookings ) ? $bookings : array(),
			'booking_summary'  => array(
				'total'     => isset( $dashboard['total_lessons'] ) ? (int) $dashboard['total_lessons'] : 0,
				'upcoming'  => isset( $dashboard['upcoming_lessons'] ) ? (int) $dashboard['upcoming_lessons'] : 0,
				'completed' => isset( $dashboard['completed_lessons'] ) ? (int) $dashboard['completed_lessons'] : 0,
			),
			'dashboard'        => $dashboard,
		)
	);
}

/**
 * Update student profile (owner only).
 *
 * @param int                  $user_id User ID.
 * @param array<string, mixed> $data    Fields.
 * @return true|WP_Error
 */
function gmm_update_student_profile( $user_id = 0, $data = array() ) {
	if ( class_exists( 'GMM_Student_Profile' ) ) {
		return GMM_Student_Profile::update_profile( $user_id, $data );
	}
	if ( ! class_exists( 'GMM_Student' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Student system unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Student::update_profile( $user_id, $data );
}

/**
 * Student profile completion percentage (image, phone, level, goals, instruments).
 *
 * @param int $user_id Optional user ID.
 * @return array{percent:int,items:array<int,array<string,mixed>>,done:int,total:int}|int
 */
function gmm_get_student_profile_completion( $user_id = 0 ) {
	if ( class_exists( 'GMM_Student_Profile' ) ) {
		return GMM_Student_Profile::get_profile_completion( $user_id );
	}
	return array(
		'percent' => 0,
		'items'   => array(),
		'done'    => 0,
		'total'   => 5,
	);
}

/**
 * Student dashboard aggregates.
 *
 * @param int $user_id Optional user ID.
 * @return array<string, mixed>
 */
function gmm_get_student_dashboard_data( $user_id = 0 ) {
	if ( class_exists( 'GMM_Student_Dashboard' ) ) {
		return GMM_Student_Dashboard::get_statistics( $user_id );
	}
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
 * Upcoming lessons for the current student.
 *
 * @param int $user_id Optional user ID.
 * @param int $limit   Max rows.
 * @return array<int, array<string, mixed>>
 */
function gmm_get_student_upcoming_lessons( $user_id = 0, $limit = 5 ) {
	if ( class_exists( 'GMM_Student_Dashboard' ) ) {
		return GMM_Student_Dashboard::get_upcoming_lessons( $user_id, $limit );
	}
	if ( class_exists( 'GMM_Student_Lessons' ) ) {
		$rows = GMM_Student_Lessons::get_upcoming_lessons( $user_id );
		return array_slice( is_array( $rows ) ? $rows : array(), 0, max( 1, absint( $limit ) ) );
	}
	return array();
}

/**
 * Recent completed lessons for the current student.
 *
 * @param int $user_id Optional user ID.
 * @param int $limit   Max rows.
 * @return array<int, array<string, mixed>>
 */
function gmm_get_student_recent_lessons( $user_id = 0, $limit = 5 ) {
	if ( class_exists( 'GMM_Student_Dashboard' ) ) {
		return GMM_Student_Dashboard::get_recent_lessons( $user_id, $limit );
	}
	if ( class_exists( 'GMM_Student_Lessons' ) ) {
		$rows = GMM_Student_Lessons::get_completed_lessons( $user_id );
		return array_slice( is_array( $rows ) ? $rows : array(), 0, max( 1, absint( $limit ) ) );
	}
	return array();
}

/**
 * Student payment summary (total paid, pending, recent).
 *
 * @param int $user_id Optional user ID.
 * @return array<string, mixed>
 */
function gmm_get_student_payment_summary( $user_id = 0 ) {
	if ( class_exists( 'GMM_Student_Dashboard' ) ) {
		return GMM_Student_Dashboard::get_payment_summary( $user_id );
	}
	return array(
		'total_paid'          => 0.0,
		'pending_payments'    => 0.0,
		'recent_transactions' => array(),
	);
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
