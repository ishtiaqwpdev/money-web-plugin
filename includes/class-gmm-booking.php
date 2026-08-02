<?php
/**
 * Central booking engine for Gospel Music Mastery.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Booking
 *
 * Creates and manages lesson bookings with availability and ownership checks.
 * No payment gateway or email sending.
 */
class GMM_Booking {

	const STATUS_PENDING   = 'pending';
	const STATUS_CONFIRMED = 'confirmed';
	const STATUS_COMPLETED = 'completed';
	const STATUS_CANCELLED = 'cancelled';
	const STATUS_REFUNDED  = 'refunded';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 20, 2 );
	}

	/**
	 * Prepare booking-related template args (templates unchanged).
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Shortcode.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_student_bookings' === $tag ) {
			$args['bookings'] = self::student_get_bookings( get_current_user_id() );
		}

		if ( 'gmm_teacher_bookings' === $tag ) {
			$args['bookings'] = self::teacher_get_bookings( get_current_user_id() );
		}

		if ( 'gmm_booking_form' === $tag ) {
			$args['booking_engine'] = true;
			$args['booking_nonce']  = wp_create_nonce( 'gmm_booking_action' );
		}

		return $args;
	}

	/**
	 * Create a booking after full validation + availability check.
	 *
	 * @param array<string, mixed> $data  Booking data.
	 * @param string               $nonce Optional nonce.
	 * @return int|WP_Error Booking ID.
	 */
	public static function create( $data, $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'gmm_not_logged_in', __( 'You must be logged in to book a lesson.', 'gospel-music-mastery' ) );
		}

		$user_id = get_current_user_id();

		if ( ! current_user_can( 'manage_options' ) ) {
			if ( ! gmm_is_student( $user_id ) ) {
				return new WP_Error( 'gmm_not_student', __( 'Only students can create bookings.', 'gospel-music-mastery' ) );
			}
			if ( ! current_user_can( 'manage_gmm_bookings' ) ) {
				return new WP_Error( 'gmm_cap', __( 'Missing booking capability.', 'gospel-music-mastery' ) );
			}
		}

		$validated = self::validate_create_data( $data, $user_id );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$available = self::check_teacher_availability(
			$validated['teacher_id'],
			$validated['booking_date'],
			$validated['booking_time'],
			$validated['duration']
		);

		if ( is_wp_error( $available ) ) {
			return $available;
		}
		if ( true !== $available ) {
			return new WP_Error( 'gmm_unavailable', __( 'Selected time slot is not available.', 'gospel-music-mastery' ) );
		}

		$now = current_time( 'mysql' );
		$row = array(
			'student_id'     => $validated['student_id'],
			'teacher_id'     => $validated['teacher_id'],
			'class_id'       => $validated['class_id'],
			'booking_date'   => $validated['booking_date'],
			'booking_time'   => $validated['booking_time'],
			'duration'       => $validated['duration'],
			'amount'         => $validated['amount'],
			'notes'          => $validated['notes'],
			'payment_status' => $validated['payment_status'],
			'booking_status' => self::STATUS_PENDING,
			'created_at'     => $now,
			'updated_at'     => $now,
		);

		global $wpdb;
		$table = GMM_Database::table( 'bookings' );

		if ( ! $wpdb->insert( $table, $row ) ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not create booking.', 'gospel-music-mastery' ) );
		}

		$booking_id = (int) $wpdb->insert_id;

		/**
		 * Fires after a booking is created (email hooks later).
		 *
		 * @since 1.0.0
		 * @param int                  $booking_id Booking ID.
		 * @param array<string, mixed> $row        Inserted row data.
		 */
		do_action( 'gmm_booking_created', $booking_id, $row );

		return $booking_id;
	}

	/**
	 * Check teacher availability for a proposed slot.
	 *
	 * @param int    $teacher_id   gmm_teachers.id.
	 * @param string $date         Y-m-d.
	 * @param string $time         H:i:s.
	 * @param int    $duration     Minutes.
	 * @param int    $exclude_id   Optional booking ID to ignore (reschedule).
	 * @return true|WP_Error
	 */
	public static function check_teacher_availability( $teacher_id, $date, $time, $duration = 0, $exclude_id = 0 ) {
		$teacher_id = absint( $teacher_id );
		$date       = self::sanitize_date( $date );
		$time       = self::sanitize_time( $time );
		$duration   = absint( $duration );
		$exclude_id = absint( $exclude_id );

		if ( ! $teacher_id || ! $date || ! $time ) {
			return new WP_Error( 'gmm_invalid_slot', __( 'Invalid date or time.', 'gospel-music-mastery' ) );
		}

		// Past dates not bookable (compare in site timezone via current_time).
		$today = current_time( 'Y-m-d' );
		if ( $date < $today ) {
			return new WP_Error( 'gmm_past_date', __( 'Cannot book a past date.', 'gospel-music-mastery' ) );
		}

		if ( ! self::teacher_exists( $teacher_id ) ) {
			return new WP_Error( 'gmm_teacher_missing', __( 'Teacher not found.', 'gospel-music-mastery' ) );
		}

		// Suspended / non-approved teachers cannot receive new bookings.
		if ( class_exists( 'GMM_Admin_Teachers' ) && ! GMM_Admin_Teachers::can_receive_bookings( $teacher_id ) ) {
			return new WP_Error( 'gmm_teacher_unavailable', __( 'This teacher is not available for new bookings.', 'gospel-music-mastery' ) );
		}

		$start_ts = strtotime( $date . ' ' . $time );
		if ( ! $start_ts ) {
			return new WP_Error( 'gmm_invalid_slot', __( 'Invalid date or time.', 'gospel-music-mastery' ) );
		}

		if ( $duration < 1 ) {
			$duration = 60;
		}
		$end_ts   = $start_ts + ( $duration * MINUTE_IN_SECONDS );
		$end_time = wp_date( 'H:i:s', $end_ts );

		global $wpdb;
		$avail_t = GMM_Database::table( 'availability' );

		// Must fall inside an open availability window.
		$slot = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$avail_t}
				WHERE teacher_id = %d
				AND available_date = %s
				AND status = %s
				AND start_time <= %s
				AND end_time >= %s
				LIMIT 1",
				$teacher_id,
				$date,
				'open',
				$time,
				$end_time
			)
		);

		if ( ! $slot ) {
			return new WP_Error( 'gmm_no_slot', __( 'Teacher has no open availability for this time.', 'gospel-music-mastery' ) );
		}

		// Prevent double-booking / overlapping active bookings.
		$bookings_t = GMM_Database::table( 'bookings' );
		$sql        = "SELECT id, booking_time, duration FROM {$bookings_t}
			WHERE teacher_id = %d
			AND booking_date = %s
			AND booking_status NOT IN (%s, %s)";
		$params     = array(
			$teacher_id,
			$date,
			self::STATUS_CANCELLED,
			self::STATUS_REFUNDED,
		);

		if ( $exclude_id ) {
			$sql     .= ' AND id <> %d';
			$params[] = $exclude_id;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$existing = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		if ( is_array( $existing ) ) {
			foreach ( $existing as $row ) {
				$other_start = strtotime( $date . ' ' . $row['booking_time'] );
				$other_dur   = absint( $row['duration'] ) > 0 ? absint( $row['duration'] ) : 60;
				$other_end   = $other_start + ( $other_dur * MINUTE_IN_SECONDS );

				// Overlap if start < other_end AND end > other_start.
				if ( $start_ts < $other_end && $end_ts > $other_start ) {
					return new WP_Error( 'gmm_conflict', __( 'This time conflicts with an existing booking.', 'gospel-music-mastery' ) );
				}
			}
		}

		return true;
	}

	/**
	 * Full booking details with related student/teacher/class.
	 *
	 * @param int $booking_id Booking ID.
	 * @param int $user_id    Requester WP user ID (0 = current).
	 * @return array<string, mixed>|WP_Error|null
	 */
	public static function get_details( $booking_id, $user_id = 0 ) {
		$booking_id = absint( $booking_id );
		$user_id    = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $booking_id || ! $user_id || ! is_user_logged_in() ) {
			return null;
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$students = GMM_Database::table( 'students' );
		$teachers = GMM_Database::table( 'teachers' );
		$classes  = GMM_Database::table( 'classes' );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT b.*,
					s.first_name AS student_first_name,
					s.last_name AS student_last_name,
					s.email AS student_email,
					s.phone AS student_phone,
					s.profile_image AS student_image,
					t.first_name AS teacher_first_name,
					t.last_name AS teacher_last_name,
					t.email AS teacher_email,
					t.phone AS teacher_phone,
					t.profile_image AS teacher_image,
					c.title AS class_title,
					c.description AS class_description,
					c.category AS class_category,
					c.difficulty AS class_difficulty,
					c.duration AS class_duration,
					c.price AS class_price,
					c.image AS class_image
				FROM {$bookings} b
				LEFT JOIN {$students} s ON s.id = b.student_id
				LEFT JOIN {$teachers} t ON t.id = b.teacher_id
				LEFT JOIN {$classes} c ON c.id = b.class_id
				WHERE b.id = %d
				LIMIT 1",
				$booking_id
			),
			ARRAY_A
		);

		if ( ! is_array( $row ) ) {
			return null;
		}

		if ( ! self::user_can_access_booking( $row, $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot view this booking.', 'gospel-music-mastery' ) );
		}

		return array(
			'booking'  => array(
				'id'             => absint( $row['id'] ),
				'booking_date'   => $row['booking_date'],
				'booking_time'   => $row['booking_time'],
				'duration'       => absint( $row['duration'] ),
				'amount'         => (float) $row['amount'],
				'notes'          => $row['notes'],
				'payment_status' => $row['payment_status'],
				'booking_status' => $row['booking_status'],
				'created_at'     => $row['created_at'],
				'updated_at'     => $row['updated_at'],
			),
			'student'  => array(
				'id'         => absint( $row['student_id'] ),
				'first_name' => $row['student_first_name'],
				'last_name'  => $row['student_last_name'],
				'email'      => $row['student_email'],
				'phone'      => $row['student_phone'],
				'image'      => $row['student_image'],
			),
			'teacher'  => array(
				'id'         => absint( $row['teacher_id'] ),
				'first_name' => $row['teacher_first_name'],
				'last_name'  => $row['teacher_last_name'],
				'email'      => $row['teacher_email'],
				'phone'      => $row['teacher_phone'],
				'image'      => $row['teacher_image'],
			),
			'class'    => array(
				'id'          => absint( $row['class_id'] ),
				'title'       => $row['class_title'],
				'description' => $row['class_description'],
				'category'    => $row['class_category'],
				'difficulty'  => $row['class_difficulty'],
				'duration'    => absint( $row['class_duration'] ),
				'price'       => (float) $row['class_price'],
				'image'       => $row['class_image'],
			),
			'date'     => $row['booking_date'],
			'time'     => $row['booking_time'],
			'amount'   => (float) $row['amount'],
			'status'   => $row['booking_status'],
		);
	}

	/**
	 * Student: list own bookings.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function student_get_bookings( $user_id = 0, $args = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id || ! is_user_logged_in() ) {
			return array();
		}

		if ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== $user_id ) {
			return array();
		}

		if ( ! class_exists( 'GMM_Student' ) ) {
			return array();
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return array();
		}

		return self::query_by_party( 'student_id', $student_id, $args );
	}

	/**
	 * Student: view own booking.
	 *
	 * @param int $booking_id Booking ID.
	 * @param int $user_id    WP user ID.
	 * @return array<string, mixed>|null
	 */
	public static function student_view_booking( $booking_id, $user_id = 0 ) {
		$details = self::get_details( $booking_id, $user_id );
		if ( is_wp_error( $details ) || ! is_array( $details ) ) {
			return null;
		}

		$user_id    = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$student_id = class_exists( 'GMM_Student' ) ? GMM_Student::get_student_id( $user_id ) : 0;

		if ( ! current_user_can( 'manage_options' ) && absint( $details['student']['id'] ) !== $student_id ) {
			return null;
		}

		return $details;
	}

	/**
	 * Student: cancel own booking.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param int    $user_id    WP user ID.
	 * @param string $nonce      Optional nonce.
	 * @return true|WP_Error
	 */
	public static function student_cancel_booking( $booking_id, $user_id = 0, $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$row     = self::get_raw_booking( $booking_id );

		if ( ! $row ) {
			return new WP_Error( 'gmm_not_found', __( 'Booking not found.', 'gospel-music-mastery' ) );
		}

		if ( ! self::user_is_booking_student( $row, $user_id ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only cancel your own bookings.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_gmm_bookings' ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_cap', __( 'Missing booking capability.', 'gospel-music-mastery' ) );
		}

		$allowed_from = array( self::STATUS_PENDING, self::STATUS_CONFIRMED );
		if ( ! in_array( $row['booking_status'], $allowed_from, true ) ) {
			return new WP_Error( 'gmm_invalid_transition', __( 'This booking cannot be cancelled.', 'gospel-music-mastery' ) );
		}

		return self::transition_status( $booking_id, self::STATUS_CANCELLED, 'gmm_booking_cancelled' );
	}

	/**
	 * Teacher: list own bookings.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function teacher_get_bookings( $user_id = 0, $args = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id || ! is_user_logged_in() ) {
			return array();
		}

		if ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== $user_id ) {
			return array();
		}

		if ( ! class_exists( 'GMM_Teacher' ) ) {
			return array();
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}

		return self::query_by_party( 'teacher_id', $teacher_id, $args );
	}

	/**
	 * Teacher: confirm pending booking.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param int    $user_id    WP user ID.
	 * @param string $nonce      Optional nonce.
	 * @return true|WP_Error
	 */
	public static function teacher_confirm_booking( $booking_id, $user_id = 0, $nonce = '' ) {
		return self::teacher_set_status( $booking_id, self::STATUS_CONFIRMED, $user_id, $nonce, array( self::STATUS_PENDING ), 'gmm_booking_confirmed' );
	}

	/**
	 * Teacher: cancel own booking.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param int    $user_id    WP user ID.
	 * @param string $nonce      Optional nonce.
	 * @return true|WP_Error
	 */
	public static function teacher_cancel_booking( $booking_id, $user_id = 0, $nonce = '' ) {
		return self::teacher_set_status(
			$booking_id,
			self::STATUS_CANCELLED,
			$user_id,
			$nonce,
			array( self::STATUS_PENDING, self::STATUS_CONFIRMED ),
			'gmm_booking_cancelled'
		);
	}

	/**
	 * Teacher: mark booking completed.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param int    $user_id    WP user ID.
	 * @param string $nonce      Optional nonce.
	 * @return true|WP_Error
	 */
	public static function teacher_complete_booking( $booking_id, $user_id = 0, $nonce = '' ) {
		return self::teacher_set_status( $booking_id, self::STATUS_COMPLETED, $user_id, $nonce, array( self::STATUS_CONFIRMED ), 'gmm_booking_completed' );
	}

	/**
	 * Allowed booking statuses.
	 *
	 * @return string[]
	 */
	public static function get_statuses() {
		return array(
			self::STATUS_PENDING,
			self::STATUS_CONFIRMED,
			self::STATUS_COMPLETED,
			self::STATUS_CANCELLED,
			self::STATUS_REFUNDED,
		);
	}

	/**
	 * Verify booking action nonce.
	 *
	 * @param string $nonce Nonce.
	 * @return bool
	 */
	public static function verify_nonce( $nonce ) {
		return (bool) wp_verify_nonce( (string) $nonce, 'gmm_booking_action' );
	}

	/**
	 * Validate + sanitize create payload.
	 *
	 * @param array<string, mixed> $data    Raw.
	 * @param int                  $user_id WP user.
	 * @return array<string, mixed>|WP_Error
	 */
	private static function validate_create_data( $data, $user_id ) {
		$data = is_array( $data ) ? $data : array();

		$student_id = 0;
		if ( ! empty( $data['student_id'] ) && current_user_can( 'manage_options' ) ) {
			$student_id = absint( $data['student_id'] );
		} elseif ( class_exists( 'GMM_Student' ) ) {
			$student_id = GMM_Student::get_student_id( $user_id );
		}

		if ( ! $student_id ) {
			return new WP_Error( 'gmm_no_student', __( 'Student profile is required.', 'gospel-music-mastery' ) );
		}

		$teacher_id = isset( $data['teacher_id'] ) ? absint( $data['teacher_id'] ) : 0;
		if ( ! $teacher_id || ! self::teacher_exists( $teacher_id ) ) {
			return new WP_Error( 'gmm_teacher_missing', __( 'Teacher not found.', 'gospel-music-mastery' ) );
		}

		$class_id = isset( $data['class_id'] ) ? absint( $data['class_id'] ) : 0;
		if ( $class_id ) {
			$class = self::get_class_row( $class_id );
			if ( ! $class ) {
				return new WP_Error( 'gmm_class_missing', __( 'Class not found.', 'gospel-music-mastery' ) );
			}
			if ( absint( $class['teacher_id'] ) !== $teacher_id ) {
				return new WP_Error( 'gmm_class_mismatch', __( 'Class does not belong to this teacher.', 'gospel-music-mastery' ) );
			}
		} else {
			return new WP_Error( 'gmm_class_required', __( 'Class is required.', 'gospel-music-mastery' ) );
		}

		$date_raw = isset( $data['booking_date'] ) ? $data['booking_date'] : ( isset( $data['date'] ) ? $data['date'] : '' );
		$time_raw = isset( $data['booking_time'] ) ? $data['booking_time'] : ( isset( $data['time'] ) ? $data['time'] : '' );
		$date     = self::sanitize_date( $date_raw );
		$time     = self::sanitize_time( $time_raw );

		if ( ! $date || ! $time ) {
			return new WP_Error( 'gmm_datetime', __( 'Valid date and time are required.', 'gospel-music-mastery' ) );
		}

		$duration = isset( $data['duration'] ) ? absint( $data['duration'] ) : 0;
		if ( $duration < 1 ) {
			$duration = isset( $class['duration'] ) ? absint( $class['duration'] ) : 60;
		}
		if ( $duration < 1 ) {
			$duration = 60;
		}

		$amount = isset( $data['amount'] ) ? round( max( 0, (float) $data['amount'] ), 2 ) : 0.0;
		if ( $amount <= 0 && isset( $class['price'] ) ) {
			$amount = round( max( 0, (float) $class['price'] ), 2 );
		}

		$payment_status = isset( $data['payment_status'] ) ? sanitize_key( (string) $data['payment_status'] ) : 'pending';
		if ( ! in_array( $payment_status, array( 'pending', 'paid', 'completed', 'failed', 'refunded' ), true ) ) {
			$payment_status = 'pending';
		}

		$notes = isset( $data['notes'] ) ? sanitize_textarea_field( (string) $data['notes'] ) : '';

		return array(
			'student_id'     => $student_id,
			'teacher_id'     => $teacher_id,
			'class_id'       => $class_id,
			'booking_date'   => $date,
			'booking_time'   => $time,
			'duration'       => $duration,
			'amount'         => $amount,
			'notes'          => $notes,
			'payment_status' => $payment_status,
		);
	}

	/**
	 * Teacher status transition helper.
	 *
	 * @param int      $booking_id   Booking ID.
	 * @param string   $new_status   Target status.
	 * @param int      $user_id      WP user.
	 * @param string   $nonce        Nonce.
	 * @param string[] $from_allowed Allowed current statuses.
	 * @param string   $hook         Action hook name.
	 * @return true|WP_Error
	 */
	private static function teacher_set_status( $booking_id, $new_status, $user_id, $nonce, $from_allowed, $hook ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$row     = self::get_raw_booking( $booking_id );

		if ( ! $row ) {
			return new WP_Error( 'gmm_not_found', __( 'Booking not found.', 'gospel-music-mastery' ) );
		}

		if ( ! self::user_is_booking_teacher( $row, $user_id ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only update your own bookings.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_gmm_bookings' ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_cap', __( 'Missing booking capability.', 'gospel-music-mastery' ) );
		}

		if ( ! in_array( $row['booking_status'], $from_allowed, true ) ) {
			return new WP_Error( 'gmm_invalid_transition', __( 'Invalid booking status transition.', 'gospel-music-mastery' ) );
		}

		return self::transition_status( $booking_id, $new_status, $hook );
	}

	/**
	 * Persist status + fire hook.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $status     New status.
	 * @param string $hook       Action name.
	 * @return true|WP_Error
	 */
	private static function transition_status( $booking_id, $status, $hook ) {
		$status = sanitize_key( $status );
		if ( ! in_array( $status, self::get_statuses(), true ) ) {
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
			array( 'id' => absint( $booking_id ) ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not update booking status.', 'gospel-music-mastery' ) );
		}

		/**
		 * Dynamic booking status hook (confirmed / completed / cancelled).
		 *
		 * @since 1.0.0
		 * @param int    $booking_id Booking ID.
		 * @param string $status     New status.
		 */
		do_action( $hook, absint( $booking_id ), $status );

		return true;
	}

	/**
	 * @param string               $column student_id|teacher_id.
	 * @param int                  $id     Party ID.
	 * @param array<string, mixed> $args   Filters.
	 * @return array<int, array<string, mixed>>
	 */
	private static function query_by_party( $column, $id, $args ) {
		$column = in_array( $column, array( 'student_id', 'teacher_id' ), true ) ? $column : '';
		$id     = absint( $id );
		if ( ! $column || ! $id ) {
			return array();
		}

		global $wpdb;
		$table = GMM_Database::table( 'bookings' );

		$sql    = "SELECT * FROM {$table} WHERE {$column} = %d";
		$params = array( $id );

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
	 * @param int $booking_id Booking ID.
	 * @return array<string, mixed>|null
	 */
	private static function get_raw_booking( $booking_id ) {
		global $wpdb;
		$table = GMM_Database::table( 'bookings' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", absint( $booking_id ) ),
			ARRAY_A
		);
		return is_array( $row ) ? $row : null;
	}

	/**
	 * @param array<string, mixed> $row     Booking row.
	 * @param int                  $user_id WP user.
	 * @return bool
	 */
	private static function user_can_access_booking( $row, $user_id ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		return self::user_is_booking_student( $row, $user_id ) || self::user_is_booking_teacher( $row, $user_id );
	}

	/**
	 * @param array<string, mixed> $row     Booking.
	 * @param int                  $user_id WP user.
	 * @return bool
	 */
	private static function user_is_booking_student( $row, $user_id ) {
		if ( ! class_exists( 'GMM_Student' ) ) {
			return false;
		}
		$student_id = GMM_Student::get_student_id( $user_id );
		return $student_id && absint( $row['student_id'] ) === $student_id;
	}

	/**
	 * @param array<string, mixed> $row     Booking.
	 * @param int                  $user_id WP user.
	 * @return bool
	 */
	private static function user_is_booking_teacher( $row, $user_id ) {
		if ( ! class_exists( 'GMM_Teacher' ) ) {
			return false;
		}
		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		return $teacher_id && absint( $row['teacher_id'] ) === $teacher_id;
	}

	/**
	 * @param int $teacher_id Teacher row ID.
	 * @return bool
	 */
	private static function teacher_exists( $teacher_id ) {
		global $wpdb;
		$table = GMM_Database::table( 'teachers' );
		$id    = $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$table} WHERE id = %d LIMIT 1", absint( $teacher_id ) )
		);
		return ! empty( $id );
	}

	/**
	 * @param int $class_id Class ID.
	 * @return array<string, mixed>|null
	 */
	private static function get_class_row( $class_id ) {
		global $wpdb;
		$table = GMM_Database::table( 'classes' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", absint( $class_id ) ),
			ARRAY_A
		);
		return is_array( $row ) ? $row : null;
	}

	/**
	 * @param mixed $date Date.
	 * @return string
	 */
	private static function sanitize_date( $date ) {
		$date = sanitize_text_field( (string) $date );
		$ts   = strtotime( $date );
		return $ts ? gmdate( 'Y-m-d', $ts ) : '';
	}

	/**
	 * @param mixed $time Time.
	 * @return string
	 */
	private static function sanitize_time( $time ) {
		$time = sanitize_text_field( (string) $time );
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
