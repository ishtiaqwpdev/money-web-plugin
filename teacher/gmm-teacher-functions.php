<?php
/**
 * Global teacher helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Whether a teacher is approved for dashboard / teaching features.
 *
 * @param int $user_id Optional WP user ID.
 * @return bool
 */
function gmm_teacher_is_approved( $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Teacher_Auth' ) ) {
		return false;
	}
	return GMM_Teacher_Auth::is_approved( $user_id );
}

/**
 * Teacher dashboard statistics (cached).
 *
 * @param int $user_id Optional WP user ID.
 * @return array<string, mixed>
 */
function gmm_get_teacher_dashboard_stats( $user_id = 0 ) {
	if ( class_exists( 'GMM_Teacher_Dashboard' ) ) {
		return GMM_Teacher_Dashboard::get_statistics( $user_id );
	}
	return function_exists( 'gmm_get_teacher_dashboard_data' )
		? gmm_get_teacher_dashboard_data( $user_id )
		: array();
}

/**
 * Upcoming lessons for the current teacher.
 *
 * @param int $user_id Optional WP user ID.
 * @param int $limit   Max rows.
 * @return array<int, array<string, mixed>>
 */
function gmm_get_teacher_upcoming_lessons( $user_id = 0, $limit = 5 ) {
	if ( ! class_exists( 'GMM_Teacher_Dashboard' ) ) {
		return array();
	}
	return GMM_Teacher_Dashboard::get_upcoming_lessons( $user_id, $limit );
}

/**
 * Recent bookings for the current teacher.
 *
 * @param int $user_id Optional WP user ID.
 * @param int $limit   Max rows.
 * @return array<int, array<string, mixed>>
 */
function gmm_get_teacher_recent_bookings( $user_id = 0, $limit = 8 ) {
	if ( ! class_exists( 'GMM_Teacher_Dashboard' ) ) {
		return array();
	}
	return GMM_Teacher_Dashboard::get_recent_bookings( $user_id, $limit );
}

/**
 * Earnings summary (total / pending / paid).
 *
 * @param int $user_id Optional WP user ID.
 * @return array<string, float>
 */
function gmm_get_teacher_earnings_summary( $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Teacher_Dashboard' ) ) {
		return array(
			'total_earnings'   => 0.0,
			'pending_earnings' => 0.0,
			'paid_earnings'    => 0.0,
		);
	}
	return GMM_Teacher_Dashboard::get_earnings_summary( $user_id );
}

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
	if ( class_exists( 'GMM_Teacher_Profile' ) ) {
		return GMM_Teacher_Profile::update_profile( $user_id, $data );
	}
	if ( ! class_exists( 'GMM_Teacher' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Teacher system unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Teacher::update_profile( $user_id, $data );
}

/**
 * Profile completion percentage for a teacher.
 *
 * @param int $user_id Optional WP user ID.
 * @return array<string, mixed>
 */
function gmm_get_profile_completion( $user_id = 0 ) {
	if ( class_exists( 'GMM_Teacher_Profile' ) ) {
		return GMM_Teacher_Profile::get_profile_completion( $user_id );
	}
	return array(
		'percent' => 0,
		'items'   => array(),
		'done'    => 0,
		'total'   => 0,
	);
}

/**
 * Public teacher profile for listings / detail pages.
 *
 * @param int  $teacher_id Teacher row ID (or user ID when $by_user).
 * @param bool $by_user    Treat ID as WP user ID.
 * @return array<string, mixed>|null
 */
function gmm_get_teacher_public_profile( $teacher_id, $by_user = false ) {
	$teacher_id = absint( $teacher_id );
	if ( $by_user && class_exists( 'GMM_Teacher' ) ) {
		$teacher_id = absint( GMM_Teacher::get_teacher_id( $teacher_id ) );
	}
	if ( class_exists( 'GMM_Teacher_Profile_Public' ) && $teacher_id ) {
		return GMM_Teacher_Profile_Public::get_profile( $teacher_id );
	}
	if ( ! class_exists( 'GMM_Teacher_Profile' ) ) {
		return null;
	}
	return GMM_Teacher_Profile::get_public_profile( $teacher_id, false );
}

/**
 * Teacher dashboard aggregates.
 *
 * @param int $user_id Optional user ID.
 * @return array<string, mixed>
 */
function gmm_get_teacher_dashboard_data( $user_id = 0 ) {
	if ( class_exists( 'GMM_Teacher_Dashboard' ) ) {
		return GMM_Teacher_Dashboard::get_statistics( $user_id );
	}
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
 * Add a teacher availability slot.
 *
 * @param array<string, mixed> $data    Fields (date, start_time, end_time).
 * @param int                  $user_id Optional WP user ID.
 * @return int|WP_Error
 */
function gmm_add_teacher_availability( $data, $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Teacher_Availability' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Availability management is unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Teacher_Availability::add_availability( $data, $user_id );
}

/**
 * Update an owned availability slot.
 *
 * @param int                  $availability_id Slot ID.
 * @param array<string, mixed> $data            Fields.
 * @param int                  $user_id         Optional WP user ID.
 * @return true|WP_Error
 */
function gmm_update_teacher_availability( $availability_id, $data, $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Teacher_Availability' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Availability management is unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Teacher_Availability::update_availability( $availability_id, $data, $user_id );
}

/**
 * Delete an owned availability slot.
 *
 * @param int $availability_id Slot ID.
 * @param int $user_id         Optional WP user ID.
 * @return true|WP_Error
 */
function gmm_delete_teacher_availability( $availability_id, $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Teacher_Availability' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Availability management is unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Teacher_Availability::delete_availability( $availability_id, $user_id );
}

/**
 * List teacher availability slots.
 *
 * @param int                  $user_id Optional WP user ID.
 * @param array<string, mixed> $args    Optional filters.
 * @return array<int, array<string, mixed>>
 */
function gmm_get_teacher_availability( $user_id = 0, $args = array() ) {
	if ( ! class_exists( 'GMM_Teacher_Availability' ) ) {
		return array();
	}
	return GMM_Teacher_Availability::get_availability( $user_id, $args );
}

/**
 * Create a class for the logged-in teacher (status pending).
 *
 * @param array<string, mixed> $data    Class fields.
 * @param int                  $user_id Optional WP user ID.
 * @return int|WP_Error
 */
function gmm_create_teacher_class( $data, $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Teacher_Classes' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Class management is unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Teacher_Classes::create_class( $data, $user_id );
}

/**
 * Update an owned teacher class.
 *
 * @param int                  $class_id Class ID.
 * @param array<string, mixed> $data     Fields.
 * @param int                  $user_id  Optional WP user ID.
 * @return true|WP_Error
 */
function gmm_update_teacher_class( $class_id, $data, $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Teacher_Classes' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Class management is unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Teacher_Classes::update_class( $class_id, $data, $user_id );
}

/**
 * Soft-delete an owned teacher class.
 *
 * @param int $class_id Class ID.
 * @param int $user_id  Optional WP user ID.
 * @return true|WP_Error
 */
function gmm_delete_teacher_class( $class_id, $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Teacher_Classes' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Class management is unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Teacher_Classes::delete_class( $class_id, $user_id );
}

/**
 * List classes for a teacher (optional search/status/category filters).
 *
 * @param int                  $user_id Optional WP user ID.
 * @param array<string, mixed> $filters Optional filters.
 * @return array<int, array<string, mixed>>
 */
function gmm_get_teacher_classes( $user_id = 0, $filters = array() ) {
	if ( ! class_exists( 'GMM_Teacher_Classes' ) ) {
		return array();
	}
	return GMM_Teacher_Classes::get_teacher_classes( $user_id, $filters );
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
