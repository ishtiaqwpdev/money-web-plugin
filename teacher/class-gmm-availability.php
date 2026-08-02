<?php
/**
 * Teacher availability CRUD.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Availability
 *
 * Manages gmm_availability rows for the owning teacher only.
 */
class GMM_Availability {

	/**
	 * Add availability slot.
	 *
	 * @param array<string, mixed> $data    Fields (date/available_date, start_time, end_time, status).
	 * @param int                  $user_id WP user ID.
	 * @return int|WP_Error
	 */
	public static function add_availability( $data, $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		$auth = self::authorize( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return new WP_Error( 'gmm_no_profile', __( 'Teacher profile not found.', 'gospel-music-mastery' ) );
		}

		$clean = self::sanitize_fields( $data, true );
		if ( empty( $clean['available_date'] ) || empty( $clean['start_time'] ) || empty( $clean['end_time'] ) ) {
			return new WP_Error( 'gmm_invalid', __( 'Date, start time, and end time are required.', 'gospel-music-mastery' ) );
		}

		if ( $clean['end_time'] <= $clean['start_time'] ) {
			return new WP_Error( 'gmm_time_range', __( 'End time must be after start time.', 'gospel-music-mastery' ) );
		}

		$row = array(
			'teacher_id'     => $teacher_id,
			'available_date' => $clean['available_date'],
			'start_time'     => $clean['start_time'],
			'end_time'       => $clean['end_time'],
			'status'         => isset( $clean['status'] ) ? $clean['status'] : 'open',
			'created_at'     => current_time( 'mysql' ),
		);

		global $wpdb;
		$table    = GMM_Database::table( 'availability' );
		$inserted = $wpdb->insert( $table, $row );

		if ( ! $inserted ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not add availability.', 'gospel-music-mastery' ) );
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Update own availability slot.
	 *
	 * @param int                  $availability_id Row ID.
	 * @param array<string, mixed> $data            Fields.
	 * @param int                  $user_id         WP user ID.
	 * @return true|WP_Error
	 */
	public static function update_availability( $availability_id, $data, $user_id = 0 ) {
		$user_id         = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$availability_id = absint( $availability_id );

		$auth = self::authorize( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( ! self::owns_slot( $availability_id, $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only update your own availability.', 'gospel-music-mastery' ) );
		}

		$clean = self::sanitize_fields( $data, false );
		if ( empty( $clean ) ) {
			return new WP_Error( 'gmm_no_fields', __( 'No valid availability fields to update.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'availability' );
		$updated = $wpdb->update(
			$table,
			$clean,
			array( 'id' => $availability_id ),
			null,
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not update availability.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * Delete own availability slot.
	 *
	 * @param int $availability_id Row ID.
	 * @param int $user_id         WP user ID.
	 * @return true|WP_Error
	 */
	public static function delete_availability( $availability_id, $user_id = 0 ) {
		$user_id         = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$availability_id = absint( $availability_id );

		$auth = self::authorize( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( ! self::owns_slot( $availability_id, $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only delete your own availability.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'availability' );
		$deleted = $wpdb->delete( $table, array( 'id' => $availability_id ), array( '%d' ) );

		if ( false === $deleted ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not delete availability.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * Get availability for a teacher.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Optional filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_availability( $user_id = 0, $args = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id || ! GMM_Teacher::can_view_profile( $user_id ) ) {
			return array();
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}

		global $wpdb;
		$table = GMM_Database::table( 'availability' );

		$sql    = "SELECT * FROM {$table} WHERE teacher_id = %d";
		$params = array( $teacher_id );

		if ( ! empty( $args['from'] ) ) {
			$sql     .= ' AND available_date >= %s';
			$params[] = sanitize_text_field( $args['from'] );
		}
		if ( ! empty( $args['to'] ) ) {
			$sql     .= ' AND available_date <= %s';
			$params[] = sanitize_text_field( $args['to'] );
		}

		$sql .= ' ORDER BY available_date ASC, start_time ASC LIMIT %d';
		$params[] = isset( $args['limit'] ) ? min( absint( $args['limit'] ), 500 ) : 200;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @param int $user_id WP user ID.
	 * @return true|WP_Error
	 */
	private static function authorize( $user_id ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'gmm_not_logged_in', __( 'You must be logged in.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_gmm_availability' ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_cap', __( 'Missing availability capability.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== absint( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only manage your own availability.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * @param int $availability_id Row ID.
	 * @param int $user_id         WP user ID.
	 * @return bool
	 */
	private static function owns_slot( $availability_id, $user_id ) {
		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id || ! $availability_id ) {
			return false;
		}

		global $wpdb;
		$table = GMM_Database::table( 'availability' );
		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE id = %d AND teacher_id = %d LIMIT 1",
				$availability_id,
				$teacher_id
			)
		);

		return ! empty( $found );
	}

	/**
	 * @param array<string, mixed> $data   Raw.
	 * @param bool                 $create Creating.
	 * @return array<string, mixed>
	 */
	private static function sanitize_fields( $data, $create = false ) {
		$data  = is_array( $data ) ? $data : array();
		$clean = array();

		$date_raw = '';
		if ( isset( $data['available_date'] ) ) {
			$date_raw = (string) $data['available_date'];
		} elseif ( isset( $data['date'] ) ) {
			$date_raw = (string) $data['date'];
		}

		if ( $date_raw || $create ) {
			$date = self::sanitize_date( $date_raw );
			if ( $date ) {
				$clean['available_date'] = $date;
			} elseif ( $create ) {
				$clean['available_date'] = '';
			}
		}

		if ( array_key_exists( 'start_time', $data ) || $create ) {
			$clean['start_time'] = self::sanitize_time( isset( $data['start_time'] ) ? (string) $data['start_time'] : '' );
		}
		if ( array_key_exists( 'end_time', $data ) || $create ) {
			$clean['end_time'] = self::sanitize_time( isset( $data['end_time'] ) ? (string) $data['end_time'] : '' );
		}
		if ( array_key_exists( 'status', $data ) ) {
			$status = sanitize_key( (string) $data['status'] );
			if ( in_array( $status, array( 'open', 'booked', 'blocked', 'closed' ), true ) ) {
				$clean['status'] = $status;
			}
		}

		return $clean;
	}

	/**
	 * @param string $date Date string.
	 * @return string Y-m-d or empty.
	 */
	private static function sanitize_date( $date ) {
		$date = sanitize_text_field( $date );
		$ts   = strtotime( $date );
		if ( ! $ts ) {
			return '';
		}
		return gmdate( 'Y-m-d', $ts );
	}

	/**
	 * @param string $time Time string.
	 * @return string H:i:s or empty.
	 */
	private static function sanitize_time( $time ) {
		$time = sanitize_text_field( $time );
		if ( preg_match( '/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $time, $m ) ) {
			$h = min( 23, absint( $m[1] ) );
			$i = min( 59, absint( $m[2] ) );
			$s = isset( $m[3] ) ? min( 59, absint( $m[3] ) ) : 0;
			return sprintf( '%02d:%02d:%02d', $h, $i, $s );
		}
		$ts = strtotime( $time );
		return $ts ? gmdate( 'H:i:s', $ts ) : '';
	}
}
