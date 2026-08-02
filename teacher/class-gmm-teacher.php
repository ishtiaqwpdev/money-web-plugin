<?php
/**
 * Teacher profile, dashboard, bookings, and earnings.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Teacher
 *
 * Teacher-owned data access for gmm_teachers, bookings, and payments.
 */
class GMM_Teacher {

	/**
	 * Register hooks (shortcode data injection).
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 10, 2 );
	}

	/**
	 * Inject teacher template variables for teacher shortcodes. UI unchanged.
	 *
	 * @param array<string, mixed> $args Template args.
	 * @param string               $tag  Shortcode tag.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		$teacher_tags = array(
			'gmm_teacher_dashboard',
			'gmm_teacher_profile',
			'gmm_teacher_classes',
			'gmm_teacher_bookings',
			'gmm_teacher_availability',
			'gmm_teacher_withdrawals',
			'gmm_teacher_settings',
		);

		if ( ! in_array( $tag, $teacher_tags, true ) ) {
			return $args;
		}

		$user_id = get_current_user_id();
		$profile = gmm_get_teacher_profile( $user_id );

		$args['teacher_profile'] = is_array( $profile ) ? $profile : array();
		$args['user_name']       = self::format_display_name( $profile );
		$args['user_first_name'] = isset( $profile['first_name'] ) && $profile['first_name']
			? $profile['first_name']
			: ( isset( $args['user_first_name'] ) ? $args['user_first_name'] : '' );

		switch ( $tag ) {
			case 'gmm_teacher_dashboard':
				$args['dashboard_data'] = gmm_get_teacher_dashboard_data( $user_id );
				break;
			case 'gmm_teacher_classes':
				$args['classes'] = class_exists( 'GMM_Teacher_Classes' )
					? GMM_Teacher_Classes::get_teacher_classes( $user_id )
					: array();
				break;
			case 'gmm_teacher_bookings':
				$args['bookings'] = self::get_teacher_bookings( $user_id );
				break;
			case 'gmm_teacher_availability':
				$args['availability'] = class_exists( 'GMM_Availability' )
					? GMM_Availability::get_availability( $user_id )
					: array();
				break;
			case 'gmm_teacher_withdrawals':
				$args['earnings']           = function_exists( 'gmm_get_teacher_earnings' )
					? gmm_get_teacher_earnings( $user_id )
					: self::get_earnings( $user_id );
				$args['transactions']       = function_exists( 'gmm_get_teacher_transactions' )
					? gmm_get_teacher_transactions( $user_id )
					: array();
				$args['withdrawal_history'] = self::get_withdrawal_history( $user_id );
				break;
		}

		return $args;
	}

	/**
	 * Get teacher profile row by WP user ID.
	 *
	 * @param int $user_id WP user ID (0 = current).
	 * @return array<string, mixed>|null
	 */
	public static function get_profile( $user_id = 0 ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( ! $user_id ) {
			return null;
		}

		if ( ! self::can_view_profile( $user_id ) ) {
			return null;
		}

		global $wpdb;
		$table = GMM_Database::table( 'teachers' );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE user_id = %d LIMIT 1",
				$user_id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Get teacher row ID (gmm_teachers.id) for a WP user.
	 *
	 * @param int $user_id WP user ID.
	 * @return int
	 */
	public static function get_teacher_id( $user_id = 0 ) {
		$profile = self::get_profile( $user_id );
		return ( $profile && isset( $profile['id'] ) ) ? absint( $profile['id'] ) : 0;
	}

	/**
	 * Update teacher profile (owner or admin only).
	 *
	 * @param int                  $user_id WP user ID (0 = current).
	 * @param array<string, mixed> $data    Profile fields.
	 * @return true|WP_Error
	 */
	public static function update_profile( $user_id = 0, $data = array() ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id || ! is_user_logged_in() ) {
			return new WP_Error( 'gmm_not_logged_in', __( 'You must be logged in.', 'gospel-music-mastery' ) );
		}

		if ( ! self::can_edit_profile( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot edit this teacher profile.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_gmm_profile' ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_cap', __( 'Missing profile capability.', 'gospel-music-mastery' ) );
		}

		$data = is_array( $data ) ? $data : array();
		$clean = self::sanitize_profile_fields( $data, false );

		if ( empty( $clean ) ) {
			return new WP_Error( 'gmm_no_fields', __( 'No valid profile fields to update.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'teachers' );
		$existing = $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$table} WHERE user_id = %d LIMIT 1", $user_id )
		);

		$now = current_time( 'mysql' );

		if ( $existing ) {
			$clean['updated_at'] = $now;
			$updated = $wpdb->update(
				$table,
				$clean,
				array( 'user_id' => $user_id ),
				self::profile_format_map( $clean ),
				array( '%d' )
			);

			if ( false === $updated ) {
				return new WP_Error( 'gmm_db_error', __( 'Could not update teacher profile.', 'gospel-music-mastery' ) );
			}

			return true;
		}

		// Create profile row if missing (no registration flow yet).
		$insert = array_merge(
			array(
				'user_id'       => $user_id,
				'first_name'    => '',
				'last_name'     => '',
				'email'         => '',
				'phone'         => '',
				'profile_image' => '',
				'bio'           => '',
				'specialization'=> '',
				'experience'    => '',
				'rating'        => 0,
				'status'        => 'pending',
				'created_at'    => $now,
				'updated_at'    => $now,
			),
			$clean
		);

		// Teachers cannot self-approve.
		if ( ! current_user_can( 'manage_options' ) ) {
			$insert['status'] = 'pending';
			unset( $insert['rating'] );
		}

		$inserted = $wpdb->insert( $table, $insert );
		if ( ! $inserted ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not create teacher profile.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * Dashboard aggregate data (UI not changed — data prepared for templates).
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_dashboard_data( $user_id = 0 ) {
		$empty = array(
			'total_classes'        => 0,
			'total_students'       => 0,
			'upcoming_lessons'     => 0,
			'completed_lessons'    => 0,
			'total_earnings'       => 0.0,
			'pending_withdrawals'  => 0.0,
		);

		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id || ! self::can_view_profile( $user_id ) ) {
			return $empty;
		}

		$teacher_id = self::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return $empty;
		}

		global $wpdb;
		$classes_t = GMM_Database::table( 'classes' );
		$bookings_t = GMM_Database::table( 'bookings' );
		$payments_t = GMM_Database::table( 'payments' );

		$total_classes = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$classes_t} WHERE teacher_id = %d", $teacher_id )
		);

		$total_students = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT student_id) FROM {$bookings_t} WHERE teacher_id = %d AND student_id > 0",
				$teacher_id
			)
		);

		$today = current_time( 'Y-m-d' );

		$upcoming = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$bookings_t}
				WHERE teacher_id = %d
				AND booking_date >= %s
				AND booking_status IN ('pending','confirmed','upcoming')",
				$teacher_id,
				$today
			)
		);

		$completed = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$bookings_t}
				WHERE teacher_id = %d AND booking_status = %s",
				$teacher_id,
				'completed'
			)
		);

		$total_earnings = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$payments_t}
				WHERE teacher_id = %d AND payment_status IN ('completed','paid','success')",
				$teacher_id
			)
		);

		// Pending withdrawals prepared as pending payment rows marked withdrawal (no gateway yet).
		$pending_withdrawals = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$payments_t}
				WHERE teacher_id = %d
				AND payment_method = %s
				AND payment_status = %s",
				$teacher_id,
				'withdrawal',
				'pending'
			)
		);

		return array(
			'total_classes'       => $total_classes,
			'total_students'      => $total_students,
			'upcoming_lessons'    => $upcoming,
			'completed_lessons'   => $completed,
			'total_earnings'      => round( $total_earnings, 2 ),
			'pending_withdrawals' => round( $pending_withdrawals, 2 ),
		);
	}

	/**
	 * Bookings for this teacher only.
	 *
	 * @param int $user_id WP user ID.
	 * @param array<string, mixed> $args Optional filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_teacher_bookings( $user_id = 0, $args = array() ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id || ! self::can_view_profile( $user_id ) ) {
			return array();
		}

		if ( ! current_user_can( 'manage_gmm_bookings' ) && ! current_user_can( 'manage_options' ) ) {
			return array();
		}

		$teacher_id = self::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}

		global $wpdb;
		$table = GMM_Database::table( 'bookings' );

		$sql    = "SELECT * FROM {$table} WHERE teacher_id = %d";
		$params = array( $teacher_id );

		if ( ! empty( $args['status'] ) ) {
			$sql     .= ' AND booking_status = %s';
			$params[] = sanitize_key( $args['status'] );
		}

		$sql .= ' ORDER BY booking_date DESC, booking_time DESC LIMIT %d';
		$params[] = isset( $args['limit'] ) ? min( absint( $args['limit'] ), 200 ) : 100;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared -- Placeholders built above.
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Single booking detail — ownership enforced.
	 *
	 * @param int $booking_id Booking ID.
	 * @param int $user_id    WP user ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_booking_details( $booking_id, $user_id = 0 ) {
		$booking_id = absint( $booking_id );
		$user_id    = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $booking_id || ! $user_id || ! self::can_view_profile( $user_id ) ) {
			return null;
		}

		$teacher_id = self::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return null;
		}

		global $wpdb;
		$table = GMM_Database::table( 'bookings' );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d AND teacher_id = %d LIMIT 1",
				$booking_id,
				$teacher_id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Update booking status for own booking only.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $status     New status.
	 * @param int    $user_id    WP user ID.
	 * @return true|WP_Error
	 */
	public static function update_booking_status( $booking_id, $status, $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'gmm_not_logged_in', __( 'You must be logged in.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_gmm_bookings' ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_cap', __( 'Missing booking capability.', 'gospel-music-mastery' ) );
		}

		$booking = self::get_booking_details( $booking_id, $user_id );
		if ( ! $booking ) {
			return new WP_Error( 'gmm_not_found', __( 'Booking not found.', 'gospel-music-mastery' ) );
		}

		$allowed = array( 'pending', 'confirmed', 'upcoming', 'completed', 'cancelled', 'no_show' );
		$status  = sanitize_key( $status );
		if ( ! in_array( $status, $allowed, true ) ) {
			return new WP_Error( 'gmm_invalid_status', __( 'Invalid booking status.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'bookings' );
		$updated = $wpdb->update(
			$table,
			array(
				'booking_status' => $status,
				'updated_at'     => current_time( 'mysql' ),
			),
			array(
				'id'         => absint( $booking_id ),
				'teacher_id' => absint( $booking['teacher_id'] ),
			),
			array( '%s', '%s' ),
			array( '%d', '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not update booking.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * Earnings summary from payments (no gateway).
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, float|int>
	 */
	public static function get_earnings( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$empty   = array(
			'total'     => 0.0,
			'pending'   => 0.0,
			'completed' => 0.0,
			'count'     => 0,
		);

		if ( ! $user_id || ! self::can_view_profile( $user_id ) ) {
			return $empty;
		}

		$teacher_id = self::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return $empty;
		}

		global $wpdb;
		$table = GMM_Database::table( 'payments' );

		$total = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$table}
				WHERE teacher_id = %d AND payment_status IN ('completed','paid','success')
				AND payment_method <> %s",
				$teacher_id,
				'withdrawal'
			)
		);

		$pending = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$table}
				WHERE teacher_id = %d AND payment_status = %s AND payment_method <> %s",
				$teacher_id,
				'pending',
				'withdrawal'
			)
		);

		$count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE teacher_id = %d AND payment_method <> %s",
				$teacher_id,
				'withdrawal'
			)
		);

		return array(
			'total'     => round( $total, 2 ),
			'pending'   => round( $pending, 2 ),
			'completed' => round( $total, 2 ),
			'count'     => $count,
		);
	}

	/**
	 * Withdrawal history rows (payment_method = withdrawal). No processing.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_withdrawal_history( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id || ! self::can_view_profile( $user_id ) ) {
			return array();
		}

		$teacher_id = self::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}

		global $wpdb;
		$table = GMM_Database::table( 'payments' );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE teacher_id = %d AND payment_method = %s
				ORDER BY created_at DESC LIMIT 100",
				$teacher_id,
				'withdrawal'
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * View own profile or admin.
	 *
	 * @param int $user_id Target WP user ID.
	 * @return bool
	 */
	public static function can_view_profile( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id || ! is_user_logged_in() ) {
			return false;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		return ( get_current_user_id() === $user_id ) && ( gmm_is_teacher( $user_id ) || current_user_can( 'manage_gmm_profile' ) );
	}

	/**
	 * Edit own profile only (or admin).
	 *
	 * @param int $user_id Target WP user ID.
	 * @return bool
	 */
	public static function can_edit_profile( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id || ! is_user_logged_in() ) {
			return false;
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		return ( get_current_user_id() === $user_id )
			&& gmm_is_teacher( $user_id )
			&& current_user_can( 'manage_gmm_profile' );
	}

	/**
	 * Sanitize editable profile fields.
	 *
	 * @param array<string, mixed> $data   Raw data.
	 * @param bool                 $is_admin Include status/rating.
	 * @return array<string, mixed>
	 */
	private static function sanitize_profile_fields( $data, $is_admin = false ) {
		$clean = array();

		$map = array(
			'first_name'     => 'sanitize_text_field',
			'last_name'      => 'sanitize_text_field',
			'phone'          => 'sanitize_text_field',
			'profile_image'  => 'esc_url_raw',
			'specialization' => 'sanitize_text_field',
			'experience'     => 'sanitize_text_field',
		);

		foreach ( $map as $key => $cb ) {
			if ( array_key_exists( $key, $data ) ) {
				$clean[ $key ] = call_user_func( $cb, (string) $data[ $key ] );
			}
		}

		if ( array_key_exists( 'email', $data ) ) {
			$email = sanitize_email( (string) $data['email'] );
			if ( is_email( $email ) ) {
				$clean['email'] = $email;
			}
		}

		if ( array_key_exists( 'bio', $data ) ) {
			$clean['bio'] = wp_kses_post( (string) $data['bio'] );
		}

		if ( $is_admin || current_user_can( 'manage_options' ) ) {
			if ( array_key_exists( 'status', $data ) ) {
				$status = sanitize_key( (string) $data['status'] );
				if ( in_array( $status, array( 'pending', 'active', 'inactive', 'suspended' ), true ) ) {
					$clean['status'] = $status;
				}
			}
			if ( array_key_exists( 'rating', $data ) ) {
				$clean['rating'] = min( 5, max( 0, (float) $data['rating'] ) );
			}
		}

		return $clean;
	}

	/**
	 * wpdb format map for profile update.
	 *
	 * @param array<string, mixed> $clean Clean fields.
	 * @return array<int, string>
	 */
	private static function profile_format_map( $clean ) {
		$formats = array();
		foreach ( array_keys( $clean ) as $key ) {
			if ( 'rating' === $key ) {
				$formats[] = '%f';
			} else {
				$formats[] = '%s';
			}
		}
		return $formats;
	}

	/**
	 * @param array<string, mixed>|null $profile Profile row.
	 * @return string
	 */
	private static function format_display_name( $profile ) {
		if ( ! is_array( $profile ) ) {
			$user = wp_get_current_user();
			return $user->exists() ? $user->display_name : 'Teacher';
		}
		$name = trim( ( isset( $profile['first_name'] ) ? $profile['first_name'] : '' ) . ' ' . ( isset( $profile['last_name'] ) ? $profile['last_name'] : '' ) );
		if ( $name ) {
			return $name;
		}
		return isset( $profile['email'] ) && $profile['email'] ? $profile['email'] : 'Teacher';
	}
}
