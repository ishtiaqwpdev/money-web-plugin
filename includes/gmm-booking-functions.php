<?php
/**
 * Booking engine helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Create a lesson booking.
 *
 * @param array<string, mixed> $data  Booking fields.
 * @param string               $nonce Optional nonce (gmm_booking_action).
 * @return int|WP_Error
 */
function gmm_create_booking( $data, $nonce = '' ) {
	if ( ! class_exists( 'GMM_Booking' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Booking engine unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Booking::create( $data, $nonce );
}

/**
 * Create a student lesson booking (price from class; payment pending).
 *
 * @param array<string, mixed> $data  Booking fields.
 * @param string               $nonce Optional gmm_booking_flow nonce.
 * @return int|WP_Error
 */
function gmm_create_student_booking( $data, $nonce = '' ) {
	if ( class_exists( 'GMM_Booking_Flow' ) ) {
		return GMM_Booking_Flow::create_student_booking( $data, $nonce );
	}
	return gmm_create_booking( $data, $nonce );
}

/**
 * Check whether a teacher time slot is free.
 *
 * @param int    $teacher_id Teacher row ID.
 * @param string $date       Date.
 * @param string $time       Time.
 * @param int    $duration   Minutes.
 * @param int    $exclude_id Optional booking to ignore.
 * @return true|WP_Error
 */
function gmm_check_teacher_availability( $teacher_id, $date, $time, $duration = 0, $exclude_id = 0 ) {
	if ( ! class_exists( 'GMM_Booking' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Booking engine unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Booking::check_teacher_availability( $teacher_id, $date, $time, $duration, $exclude_id );
}

/**
 * Get rich booking details.
 *
 * @param int $booking_id Booking ID.
 * @param int $user_id    Optional requester.
 * @return array<string, mixed>|null
 */
function gmm_get_booking_details( $booking_id, $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Booking' ) ) {
		return null;
	}
	$details = GMM_Booking::get_details( $booking_id, $user_id );
	return ( is_array( $details ) ) ? $details : null;
}

/**
 * Student bookings list.
 *
 * @param int                  $user_id WP user ID.
 * @param array<string, mixed> $args    Filters.
 * @return array<int, array<string, mixed>>
 */
function gmm_student_get_bookings( $user_id = 0, $args = array() ) {
	if ( ! class_exists( 'GMM_Booking' ) ) {
		return array();
	}
	return GMM_Booking::student_get_bookings( $user_id, $args );
}

/**
 * Student cancel booking.
 *
 * @param int    $booking_id Booking ID.
 * @param int    $user_id    WP user ID.
 * @param string $nonce      Optional nonce.
 * @return true|WP_Error
 */
function gmm_student_cancel_booking( $booking_id, $user_id = 0, $nonce = '' ) {
	if ( ! class_exists( 'GMM_Booking' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Booking engine unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Booking::student_cancel_booking( $booking_id, $user_id, $nonce );
}

/**
 * Student view booking.
 *
 * @param int $booking_id Booking ID.
 * @param int $user_id    WP user ID.
 * @return array<string, mixed>|null
 */
function gmm_student_view_booking( $booking_id, $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Booking' ) ) {
		return null;
	}
	return GMM_Booking::student_view_booking( $booking_id, $user_id );
}

/**
 * Teacher bookings list.
 *
 * @param int                  $user_id WP user ID.
 * @param array<string, mixed> $args    Filters.
 * @return array<int, array<string, mixed>>
 */
function gmm_teacher_get_bookings( $user_id = 0, $args = array() ) {
	if ( ! class_exists( 'GMM_Booking' ) ) {
		return array();
	}
	return GMM_Booking::teacher_get_bookings( $user_id, $args );
}

/**
 * Teacher confirm booking.
 *
 * @param int    $booking_id Booking ID.
 * @param int    $user_id    WP user ID.
 * @param string $nonce      Optional nonce.
 * @return true|WP_Error
 */
function gmm_teacher_confirm_booking( $booking_id, $user_id = 0, $nonce = '' ) {
	if ( class_exists( 'GMM_Teacher_Bookings' ) ) {
		return GMM_Teacher_Bookings::confirm_booking( $booking_id, $user_id, $nonce );
	}
	if ( ! class_exists( 'GMM_Booking' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Booking engine unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Booking::teacher_confirm_booking( $booking_id, $user_id, $nonce );
}

/**
 * Teacher cancel booking.
 *
 * @param int    $booking_id Booking ID.
 * @param int    $user_id    WP user ID.
 * @param string $nonce      Optional nonce.
 * @return true|WP_Error
 */
function gmm_teacher_cancel_booking( $booking_id, $user_id = 0, $nonce = '', $reason = '' ) {
	if ( class_exists( 'GMM_Teacher_Bookings' ) ) {
		return GMM_Teacher_Bookings::cancel_booking( $booking_id, $user_id, $nonce, $reason );
	}
	if ( ! class_exists( 'GMM_Booking' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Booking engine unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Booking::teacher_cancel_booking( $booking_id, $user_id, $nonce, $reason );
}

/**
 * Teacher complete booking.
 *
 * @param int    $booking_id Booking ID.
 * @param int    $user_id    WP user ID.
 * @param string $nonce      Optional nonce.
 * @return true|WP_Error
 */
function gmm_teacher_complete_booking( $booking_id, $user_id = 0, $nonce = '' ) {
	if ( class_exists( 'GMM_Teacher_Bookings' ) ) {
		return GMM_Teacher_Bookings::complete_booking( $booking_id, $user_id, $nonce );
	}
	if ( ! class_exists( 'GMM_Booking' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Booking engine unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Booking::teacher_complete_booking( $booking_id, $user_id, $nonce );
}

/**
 * Booking nonce field HTML.
 *
 * @return string
 */
function gmm_booking_nonce_field() {
	return wp_nonce_field( 'gmm_booking_action', 'gmm_booking_nonce', true, false );
}

/**
 * Verify booking nonce.
 *
 * @param string $nonce Nonce value.
 * @return bool
 */
function gmm_verify_booking_nonce( $nonce ) {
	return class_exists( 'GMM_Booking' ) ? GMM_Booking::verify_nonce( $nonce ) : false;
}
