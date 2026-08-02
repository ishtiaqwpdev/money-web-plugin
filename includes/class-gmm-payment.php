<?php
/**
 * Central payment records and status management.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Payment
 *
 * Manages gmm_payments rows, commission math, and refund preparation.
 * Does not process card data or call gateways.
 */
class GMM_Payment {

	const STATUS_PENDING   = 'pending';
	const STATUS_COMPLETED = 'completed';
	const STATUS_FAILED    = 'failed';
	const STATUS_REFUNDED  = 'refunded';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		// When a booking is created, prepare a pending payment record (architecture only).
		$loader->add_action( 'gmm_booking_created', $instance, 'maybe_create_pending_for_booking', 10, 2 );
		$loader->add_action( 'gmm_payment_completed', $instance, 'maybe_confirm_booking_after_payment', 10, 2 );
	}

	/**
	 * Create a payment record.
	 *
	 * @param array<string, mixed> $data  Payment fields.
	 * @param string               $nonce Optional nonce.
	 * @return int|WP_Error Payment ID.
	 */
	public static function create( $data, $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'gmm_not_logged_in', __( 'You must be logged in.', 'gospel-music-mastery' ) );
		}

		$validated = self::validate_create_data( $data );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		// Only student owner, teacher of booking, or admin may create.
		if ( ! self::user_can_create_for( $validated ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot create this payment.', 'gospel-music-mastery' ) );
		}

		$row = array(
			'booking_id'     => $validated['booking_id'],
			'student_id'     => $validated['student_id'],
			'teacher_id'     => $validated['teacher_id'],
			'transaction_id' => $validated['transaction_id'],
			'amount'         => $validated['amount'],
			'payment_method' => $validated['payment_method'],
			'payment_status' => $validated['payment_status'],
			'created_at'     => current_time( 'mysql' ),
		);

		global $wpdb;
		$table = GMM_Database::table( 'payments' );

		if ( ! $wpdb->insert( $table, $row ) ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not create payment record.', 'gospel-music-mastery' ) );
		}

		$payment_id = (int) $wpdb->insert_id;

		/**
		 * Fires after a payment row is created.
		 *
		 * @since 1.0.0
		 * @param int                  $payment_id Payment ID.
		 * @param array<string, mixed> $row        Row data.
		 */
		do_action( 'gmm_payment_created', $payment_id, $row );

		if ( self::STATUS_COMPLETED === $row['payment_status'] ) {
			do_action( 'gmm_payment_completed', $payment_id, $row );
		} elseif ( self::STATUS_FAILED === $row['payment_status'] ) {
			do_action( 'gmm_payment_failed', $payment_id, $row );
		} elseif ( self::STATUS_REFUNDED === $row['payment_status'] ) {
			do_action( 'gmm_payment_refunded', $payment_id, $row );
		}

		return $payment_id;
	}

	/**
	 * Update payment status (admin or authorized flows).
	 *
	 * @param int    $payment_id Payment ID.
	 * @param string $status     New status.
	 * @param string $nonce      Optional nonce.
	 * @return true|WP_Error
	 */
	public static function update_status( $payment_id, $status, $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'Only administrators can update payment status.', 'gospel-music-mastery' ) );
		}

		$payment_id = absint( $payment_id );
		$status     = sanitize_key( $status );

		if ( ! $payment_id || ! in_array( $status, self::get_statuses(), true ) ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid payment or status.', 'gospel-music-mastery' ) );
		}

		$existing = self::get_raw( $payment_id );
		if ( ! $existing ) {
			return new WP_Error( 'gmm_not_found', __( 'Payment not found.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'payments' );
		$updated = $wpdb->update(
			$table,
			array( 'payment_status' => $status ),
			array( 'id' => $payment_id ),
			array( '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not update payment status.', 'gospel-music-mastery' ) );
		}

		$row = self::get_raw( $payment_id );

		if ( self::STATUS_COMPLETED === $status ) {
			do_action( 'gmm_payment_completed', $payment_id, $row );
		} elseif ( self::STATUS_FAILED === $status ) {
			do_action( 'gmm_payment_failed', $payment_id, $row );
		} elseif ( self::STATUS_REFUNDED === $status ) {
			do_action( 'gmm_payment_refunded', $payment_id, $row );
		}

		return true;
	}

	/**
	 * Platform commission percentage from settings (not hardcoded).
	 *
	 * @return float
	 */
	public static function get_commission_percent() {
		$percent = 10.0;

		if ( function_exists( 'gmm_get_setting' ) ) {
			$percent = (float) gmm_get_setting( 'commission_rate', 10 );
		} else {
			$opts = wp_parse_args(
				get_option( 'gmm_payment_settings', array() ),
				array(
					'currency'           => 'USD',
					'commission_percent' => 10,
				)
			);
			$percent = isset( $opts['commission_percent'] ) ? (float) $opts['commission_percent'] : 10.0;
		}

		$percent = max( 0, min( 100, $percent ) );

		/**
		 * Filter platform commission percentage.
		 *
		 * @since 1.0.0
		 * @param float $percent Commission %.
		 */
		return (float) apply_filters( 'gmm_platform_commission_percent', $percent );
	}

	/**
	 * Split booking amount into teacher earnings + platform commission.
	 *
	 * @param float $amount Gross amount.
	 * @return array{gross:float,commission_percent:float,commission:float,teacher_earnings:float}
	 */
	public static function calculate_split( $amount ) {
		$amount  = max( 0, (float) $amount );
		$percent = self::get_commission_percent();
		$commission = round( $amount * ( $percent / 100 ), 2 );
		$teacher    = round( $amount - $commission, 2 );

		return array(
			'gross'              => round( $amount, 2 ),
			'commission_percent' => $percent,
			'commission'         => $commission,
			'teacher_earnings'   => $teacher,
		);
	}

	/**
	 * Admin: list payments with filters.
	 *
	 * @param array<string, mixed> $args Filters.
	 * @return array<int, array<string, mixed>>|WP_Error
	 */
	public static function admin_get_payments( $args = array() ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'Administrator access required.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table = GMM_Database::table( 'payments' );

		$sql    = "SELECT * FROM {$table} WHERE 1=1";
		$params = array();

		if ( ! empty( $args['status'] ) ) {
			$sql     .= ' AND payment_status = %s';
			$params[] = sanitize_key( $args['status'] );
		}
		if ( ! empty( $args['student_id'] ) ) {
			$sql     .= ' AND student_id = %d';
			$params[] = absint( $args['student_id'] );
		}
		if ( ! empty( $args['teacher_id'] ) ) {
			$sql     .= ' AND teacher_id = %d';
			$params[] = absint( $args['teacher_id'] );
		}
		if ( ! empty( $args['booking_id'] ) ) {
			$sql     .= ' AND booking_id = %d';
			$params[] = absint( $args['booking_id'] );
		}
		if ( ! empty( $args['payment_method'] ) ) {
			$sql     .= ' AND payment_method = %s';
			$params[] = sanitize_key( $args['payment_method'] );
		}

		$sql     .= ' ORDER BY created_at DESC LIMIT %d';
		$params[] = isset( $args['limit'] ) ? min( absint( $args['limit'] ), 500 ) : 100;

		if ( empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$rows = $wpdb->get_results( $sql, ARRAY_A );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
		}

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Admin: single transaction.
	 *
	 * @param int $payment_id Payment ID.
	 * @return array<string, mixed>|WP_Error|null
	 */
	public static function admin_get_transaction( $payment_id ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'Administrator access required.', 'gospel-music-mastery' ) );
		}

		$row = self::get_raw( $payment_id );
		if ( ! $row ) {
			return null;
		}

		$split = self::calculate_split( isset( $row['amount'] ) ? (float) $row['amount'] : 0 );

		return array_merge( $row, array( 'split' => $split ) );
	}

	/**
	 * Prepare refund request (no gateway).
	 *
	 * @param int    $payment_id Payment ID.
	 * @param string $reason     Reason text.
	 * @param string $nonce      Optional nonce.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function create_refund_request( $payment_id, $reason = '', $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		$payment_id = absint( $payment_id );
		$payment    = self::get_raw( $payment_id );

		if ( ! $payment ) {
			return new WP_Error( 'gmm_not_found', __( 'Payment not found.', 'gospel-music-mastery' ) );
		}

		if ( ! self::user_can_request_refund( $payment ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot request a refund for this payment.', 'gospel-music-mastery' ) );
		}

		if ( self::STATUS_COMPLETED !== $payment['payment_status'] ) {
			return new WP_Error( 'gmm_invalid', __( 'Only completed payments can be refunded.', 'gospel-music-mastery' ) );
		}

		$request = array(
			'payment_id'     => $payment_id,
			'booking_id'     => absint( $payment['booking_id'] ),
			'amount'         => (float) $payment['amount'],
			'reason'         => sanitize_textarea_field( $reason ),
			'requested_by'   => get_current_user_id(),
			'requested_at'   => current_time( 'mysql' ),
			'status'         => 'requested',
		);

		/**
		 * Filter / store refund request payload (gateway later).
		 *
		 * @since 1.0.0
		 * @param array<string, mixed> $request Request.
		 * @param array<string, mixed> $payment Payment row.
		 */
		$request = apply_filters( 'gmm_refund_request', $request, $payment );

		$existing = get_option( 'gmm_refund_requests', array() );
		if ( ! is_array( $existing ) ) {
			$existing = array();
		}
		$existing[] = $request;
		update_option( 'gmm_refund_requests', $existing, false );

		return $request;
	}

	/**
	 * Process refund locally (status flip only — no gateway).
	 *
	 * @param int    $payment_id Payment ID.
	 * @param string $nonce      Optional nonce.
	 * @return true|WP_Error
	 */
	public static function process_refund( $payment_id, $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'Only administrators can process refunds.', 'gospel-music-mastery' ) );
		}

		return self::update_status( $payment_id, self::STATUS_REFUNDED, '' );
	}

	/**
	 * Auto-create pending payment when booking is created.
	 *
	 * @param int                  $booking_id Booking ID.
	 * @param array<string, mixed> $booking    Booking row.
	 * @return void
	 */
	public function maybe_create_pending_for_booking( $booking_id, $booking ) {
		$booking_id = absint( $booking_id );
		if ( ! $booking_id || ! is_array( $booking ) ) {
			return;
		}

		// Avoid duplicate pending rows for same booking.
		global $wpdb;
		$table  = GMM_Database::table( 'payments' );
		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE booking_id = %d AND payment_status = %s LIMIT 1",
				$booking_id,
				self::STATUS_PENDING
			)
		);

		if ( $exists ) {
			return;
		}

		$row = array(
			'booking_id'     => $booking_id,
			'student_id'     => isset( $booking['student_id'] ) ? absint( $booking['student_id'] ) : 0,
			'teacher_id'     => isset( $booking['teacher_id'] ) ? absint( $booking['teacher_id'] ) : 0,
			'transaction_id' => '',
			'amount'         => isset( $booking['amount'] ) ? round( (float) $booking['amount'], 2 ) : 0,
			'payment_method' => 'pending',
			'payment_status' => self::STATUS_PENDING,
			'created_at'     => current_time( 'mysql' ),
		);

		if ( $wpdb->insert( $table, $row ) ) {
			do_action( 'gmm_payment_created', (int) $wpdb->insert_id, $row );
		}
	}

	/**
	 * After payment completed → confirm related booking (workflow prep).
	 *
	 * @param int                  $payment_id Payment ID.
	 * @param array<string, mixed> $row        Payment row.
	 * @return void
	 */
	public function maybe_confirm_booking_after_payment( $payment_id, $row ) {
		if ( ! is_array( $row ) || empty( $row['booking_id'] ) ) {
			return;
		}

		if ( ! function_exists( 'gmm_teacher_confirm_booking' ) && ! class_exists( 'GMM_Booking' ) ) {
			return;
		}

		$booking_id = absint( $row['booking_id'] );
		$booking    = null;

		global $wpdb;
		$bt = GMM_Database::table( 'bookings' );
		$booking = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$bt} WHERE id = %d LIMIT 1", $booking_id ),
			ARRAY_A
		);

		if ( ! is_array( $booking ) || GMM_Booking::STATUS_PENDING !== $booking['booking_status'] ) {
			return;
		}

		// Direct status update to avoid teacher-only capability during system hook.
		$wpdb->update(
			$bt,
			array(
				'booking_status' => GMM_Booking::STATUS_CONFIRMED,
				'payment_status' => 'paid',
				'updated_at'     => current_time( 'mysql' ),
			),
			array( 'id' => $booking_id ),
			array( '%s', '%s', '%s' ),
			array( '%d' )
		);

		do_action( 'gmm_booking_confirmed', $booking_id, GMM_Booking::STATUS_CONFIRMED );
	}

	/**
	 * @return string[]
	 */
	public static function get_statuses() {
		return array(
			self::STATUS_PENDING,
			self::STATUS_COMPLETED,
			self::STATUS_FAILED,
			self::STATUS_REFUNDED,
		);
	}

	/**
	 * @param string $nonce Nonce.
	 * @return bool
	 */
	public static function verify_nonce( $nonce ) {
		return (bool) wp_verify_nonce( (string) $nonce, 'gmm_payment_action' );
	}

	/**
	 * @param int $payment_id Payment ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_raw( $payment_id ) {
		global $wpdb;
		$table = GMM_Database::table( 'payments' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", absint( $payment_id ) ),
			ARRAY_A
		);
		return is_array( $row ) ? $row : null;
	}

	/**
	 * @param array<string, mixed> $data Raw.
	 * @return array<string, mixed>|WP_Error
	 */
	private static function validate_create_data( $data ) {
		$data = is_array( $data ) ? $data : array();

		$booking_id = isset( $data['booking_id'] ) ? absint( $data['booking_id'] ) : 0;
		$student_id = isset( $data['student_id'] ) ? absint( $data['student_id'] ) : 0;
		$teacher_id = isset( $data['teacher_id'] ) ? absint( $data['teacher_id'] ) : 0;
		$amount     = isset( $data['amount'] ) ? round( (float) $data['amount'], 2 ) : 0.0;

		if ( $amount <= 0 ) {
			return new WP_Error( 'gmm_invalid_amount', __( 'A valid payment amount is required.', 'gospel-music-mastery' ) );
		}

		$booking = null;
		if ( $booking_id ) {
			global $wpdb;
			$bt      = GMM_Database::table( 'bookings' );
			$booking = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$bt} WHERE id = %d LIMIT 1", $booking_id ),
				ARRAY_A
			);
			if ( ! $booking ) {
				return new WP_Error( 'gmm_invalid_booking', __( 'Booking not found.', 'gospel-music-mastery' ) );
			}
			if ( ! $student_id ) {
				$student_id = absint( $booking['student_id'] );
			}
			if ( ! $teacher_id ) {
				$teacher_id = absint( $booking['teacher_id'] );
			}
			if ( absint( $booking['student_id'] ) !== $student_id || absint( $booking['teacher_id'] ) !== $teacher_id ) {
				return new WP_Error( 'gmm_booking_mismatch', __( 'Payment parties do not match the booking.', 'gospel-music-mastery' ) );
			}
		}

		if ( ! $student_id || ! self::student_exists( $student_id ) ) {
			return new WP_Error( 'gmm_invalid_student', __( 'Student not found.', 'gospel-music-mastery' ) );
		}
		if ( ! $teacher_id || ! self::teacher_exists( $teacher_id ) ) {
			return new WP_Error( 'gmm_invalid_teacher', __( 'Teacher not found.', 'gospel-music-mastery' ) );
		}

		$method = isset( $data['payment_method'] ) ? sanitize_key( (string) $data['payment_method'] ) : 'manual';
		if ( '' === $method ) {
			$method = 'manual';
		}

		// Never accept card numbers or sensitive fields.
		unset( $data['card_number'], $data['cvv'], $data['cvc'], $data['expiry'] );

		$status = isset( $data['status'] ) ? sanitize_key( (string) $data['status'] ) : ( isset( $data['payment_status'] ) ? sanitize_key( (string) $data['payment_status'] ) : self::STATUS_PENDING );
		if ( ! in_array( $status, self::get_statuses(), true ) ) {
			$status = self::STATUS_PENDING;
		}

		$txn = isset( $data['transaction_id'] ) ? sanitize_text_field( (string) $data['transaction_id'] ) : '';

		return array(
			'booking_id'     => $booking_id,
			'student_id'     => $student_id,
			'teacher_id'     => $teacher_id,
			'amount'         => $amount,
			'payment_method' => $method,
			'payment_status' => $status,
			'transaction_id' => $txn,
		);
	}

	/**
	 * @param array<string, mixed> $validated Validated create data.
	 * @return bool
	 */
	private static function user_can_create_for( $validated ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$user_id = get_current_user_id();

		if ( class_exists( 'GMM_Student' ) ) {
			$sid = GMM_Student::get_student_id( $user_id );
			if ( $sid && $sid === absint( $validated['student_id'] ) && current_user_can( 'manage_gmm_bookings' ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param array<string, mixed> $payment Payment row.
	 * @return bool
	 */
	private static function user_can_request_refund( $payment ) {
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		if ( ! class_exists( 'GMM_Student' ) ) {
			return false;
		}
		$sid = GMM_Student::get_student_id( get_current_user_id() );
		return $sid && $sid === absint( $payment['student_id'] );
	}

	/**
	 * @param int $student_id Student row ID.
	 * @return bool
	 */
	private static function student_exists( $student_id ) {
		global $wpdb;
		$table = GMM_Database::table( 'students' );
		$id    = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE id = %d LIMIT 1", $student_id ) );
		return ! empty( $id );
	}

	/**
	 * @param int $teacher_id Teacher row ID.
	 * @return bool
	 */
	private static function teacher_exists( $teacher_id ) {
		global $wpdb;
		$table = GMM_Database::table( 'teachers' );
		$id    = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE id = %d LIMIT 1", $teacher_id ) );
		return ! empty( $id );
	}
}
