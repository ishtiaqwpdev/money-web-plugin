<?php
/**
 * Student booking requests.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Student_Bookings
 *
 * Students may only create/view/cancel their own bookings.
 */
class GMM_Student_Bookings {

	/**
	 * Create a booking request (no payment processing).
	 *
	 * @param array<string, mixed> $data    Booking fields.
	 * @param int                  $user_id WP user ID.
	 * @return int|WP_Error
	 */
	public static function create_booking( $data, $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		$auth = self::authorize_manage( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		// Prefer central booking engine (availability + conflict checks).
		if ( function_exists( 'gmm_create_booking' ) ) {
			$data = is_array( $data ) ? $data : array();
			return gmm_create_booking( $data );
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return new WP_Error( 'gmm_no_profile', __( 'Student profile not found.', 'gospel-music-mastery' ) );
		}

		$data  = is_array( $data ) ? $data : array();
		$clean = self::sanitize_booking_fields( $data, true );

		if ( empty( $clean['teacher_id'] ) || empty( $clean['booking_date'] ) || empty( $clean['booking_time'] ) ) {
			return new WP_Error( 'gmm_invalid', __( 'Teacher, date, and time are required.', 'gospel-music-mastery' ) );
		}

		$now = current_time( 'mysql' );
		$row = array(
			'student_id'     => $student_id,
			'teacher_id'     => $clean['teacher_id'],
			'class_id'       => isset( $clean['class_id'] ) ? $clean['class_id'] : 0,
			'booking_date'   => $clean['booking_date'],
			'booking_time'   => $clean['booking_time'],
			'duration'       => isset( $clean['duration'] ) ? $clean['duration'] : 0,
			'amount'         => isset( $clean['amount'] ) ? $clean['amount'] : 0,
			'payment_status' => 'pending',
			'booking_status' => 'pending',
			'notes'          => isset( $clean['notes'] ) ? $clean['notes'] : '',
			'created_at'     => $now,
			'updated_at'     => $now,
		);

		global $wpdb;
		$table = GMM_Database::table( 'bookings' );

		if ( ! $wpdb->insert( $table, $row ) ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not create booking.', 'gospel-music-mastery' ) );
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * List own bookings.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_bookings( $user_id = 0, $args = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! GMM_Student::can_view_profile( $user_id ) ) {
			return array();
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return array();
		}

		global $wpdb;
		$table = GMM_Database::table( 'bookings' );

		$sql    = "SELECT * FROM {$table} WHERE student_id = %d";
		$params = array( $student_id );

		if ( ! empty( $args['status'] ) ) {
			$sql     .= ' AND booking_status = %s';
			$params[] = sanitize_key( $args['status'] );
		}

		$sql     .= ' ORDER BY booking_date DESC, booking_time DESC LIMIT %d';
		$params[] = isset( $args['limit'] ) ? min( absint( $args['limit'] ), 200 ) : 100;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Cancel own pending/confirmed booking request.
	 *
	 * @param int $booking_id Booking ID.
	 * @param int $user_id    WP user ID.
	 * @return true|WP_Error
	 */
	public static function cancel_booking( $booking_id, $user_id = 0 ) {
		$user_id    = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$booking_id = absint( $booking_id );

		$auth = self::authorize_manage( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( function_exists( 'gmm_student_cancel_booking' ) ) {
			return gmm_student_cancel_booking( $booking_id, $user_id );
		}

		$booking = self::get_booking_details( $booking_id, $user_id );
		if ( ! $booking ) {
			return new WP_Error( 'gmm_not_found', __( 'Booking not found.', 'gospel-music-mastery' ) );
		}

		$cancellable = array( 'pending', 'confirmed', 'upcoming' );
		if ( ! in_array( $booking['booking_status'], $cancellable, true ) ) {
			return new WP_Error( 'gmm_not_cancellable', __( 'This booking cannot be cancelled.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'bookings' );
		$updated = $wpdb->update(
			$table,
			array(
				'booking_status' => 'cancelled',
				'updated_at'     => current_time( 'mysql' ),
			),
			array(
				'id'         => $booking_id,
				'student_id' => absint( $booking['student_id'] ),
			),
			array( '%s', '%s' ),
			array( '%d', '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not cancel booking.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * Own booking details only.
	 *
	 * @param int $booking_id Booking ID.
	 * @param int $user_id    WP user ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_booking_details( $booking_id, $user_id = 0 ) {
		$user_id    = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$booking_id = absint( $booking_id );

		if ( ! $booking_id || ! GMM_Student::can_view_profile( $user_id ) ) {
			return null;
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return null;
		}

		global $wpdb;
		$table = GMM_Database::table( 'bookings' );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d AND student_id = %d LIMIT 1",
				$booking_id,
				$student_id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * @param int $user_id WP user ID.
	 * @return true|WP_Error
	 */
	private static function authorize_manage( $user_id ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'gmm_not_logged_in', __( 'You must be logged in.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_gmm_bookings' ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_cap', __( 'Missing booking capability.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== absint( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only manage your own bookings.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_options' ) && ! gmm_is_student( $user_id ) ) {
			return new WP_Error( 'gmm_not_student', __( 'Student role required.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * @param array<string, mixed> $data   Raw.
	 * @param bool                 $create Creating.
	 * @return array<string, mixed>
	 */
	private static function sanitize_booking_fields( $data, $create = false ) {
		$clean = array();

		if ( array_key_exists( 'teacher_id', $data ) || $create ) {
			$clean['teacher_id'] = absint( isset( $data['teacher_id'] ) ? $data['teacher_id'] : 0 );
		}
		if ( array_key_exists( 'class_id', $data ) || $create ) {
			$clean['class_id'] = absint( isset( $data['class_id'] ) ? $data['class_id'] : 0 );
		}
		if ( array_key_exists( 'booking_date', $data ) || array_key_exists( 'date', $data ) || $create ) {
			$raw = isset( $data['booking_date'] ) ? $data['booking_date'] : ( isset( $data['date'] ) ? $data['date'] : '' );
			$ts  = strtotime( sanitize_text_field( (string) $raw ) );
			$clean['booking_date'] = $ts ? gmdate( 'Y-m-d', $ts ) : '';
		}
		if ( array_key_exists( 'booking_time', $data ) || array_key_exists( 'time', $data ) || $create ) {
			$raw = isset( $data['booking_time'] ) ? $data['booking_time'] : ( isset( $data['time'] ) ? $data['time'] : '' );
			$clean['booking_time'] = self::sanitize_time( (string) $raw );
		}
		if ( array_key_exists( 'duration', $data ) ) {
			$clean['duration'] = absint( $data['duration'] );
		}
		if ( array_key_exists( 'amount', $data ) ) {
			$clean['amount'] = round( max( 0, (float) $data['amount'] ), 2 );
		}
		if ( array_key_exists( 'notes', $data ) ) {
			$clean['notes'] = sanitize_textarea_field( (string) $data['notes'] );
		}

		return $clean;
	}

	/**
	 * @param string $time Time string.
	 * @return string
	 */
	private static function sanitize_time( $time ) {
		$time = sanitize_text_field( $time );
		if ( preg_match( '/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $time, $m ) ) {
			return sprintf(
				'%02d:%02d:%02d',
				min( 23, absint( $m[1] ) ),
				min( 59, absint( $m[2] ) ),
				isset( $m[3] ) ? min( 59, absint( $m[3] ) ) : 0
			);
		}
		$ts = strtotime( $time );
		return $ts ? gmdate( 'H:i:s', $ts ) : '';
	}
}
