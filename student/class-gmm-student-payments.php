<?php
/**
 * Student payment history (view only).
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Student_Payments
 *
 * Students may only view their own payment rows. No gateway.
 */
class GMM_Student_Payments {

	/**
	 * Payment history for student.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_payment_history( $user_id = 0, $args = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! self::authorize_view( $user_id ) ) {
			return array();
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return array();
		}

		global $wpdb;
		$table = GMM_Database::table( 'payments' );

		$sql    = "SELECT * FROM {$table} WHERE student_id = %d";
		$params = array( $student_id );

		if ( ! empty( $args['status'] ) ) {
			$sql     .= ' AND payment_status = %s';
			$params[] = sanitize_key( $args['status'] );
		}

		$sql     .= ' ORDER BY created_at DESC LIMIT %d';
		$params[] = isset( $args['limit'] ) ? min( absint( $args['limit'] ), 200 ) : 100;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Single transaction (own only).
	 *
	 * @param int $payment_id Payment ID.
	 * @param int $user_id    WP user ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_transaction_details( $payment_id, $user_id = 0 ) {
		$user_id    = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$payment_id = absint( $payment_id );

		if ( ! $payment_id || ! self::authorize_view( $user_id ) ) {
			return null;
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return null;
		}

		global $wpdb;
		$table = GMM_Database::table( 'payments' );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE id = %d AND student_id = %d LIMIT 1",
				$payment_id,
				$student_id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Receipt-oriented payload for a payment (view data only).
	 *
	 * @param int $payment_id Payment ID.
	 * @param int $user_id    WP user ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_receipt( $payment_id, $user_id = 0 ) {
		$payment = self::get_transaction_details( $payment_id, $user_id );
		if ( ! $payment ) {
			return null;
		}

		$profile = GMM_Student::get_profile( $user_id ? $user_id : get_current_user_id() );

		return array(
			'receipt_id'     => 'GMM-' . absint( $payment['id'] ),
			'transaction_id' => isset( $payment['transaction_id'] ) ? $payment['transaction_id'] : '',
			'amount'         => isset( $payment['amount'] ) ? (float) $payment['amount'] : 0,
			'payment_method' => isset( $payment['payment_method'] ) ? $payment['payment_method'] : '',
			'payment_status' => isset( $payment['payment_status'] ) ? $payment['payment_status'] : '',
			'paid_at'        => isset( $payment['created_at'] ) ? $payment['created_at'] : '',
			'booking_id'     => isset( $payment['booking_id'] ) ? absint( $payment['booking_id'] ) : 0,
			'student_name'   => $profile
				? trim( ( isset( $profile['first_name'] ) ? $profile['first_name'] : '' ) . ' ' . ( isset( $profile['last_name'] ) ? $profile['last_name'] : '' ) )
				: '',
			'student_email'  => $profile && isset( $profile['email'] ) ? $profile['email'] : '',
		);
	}

	/**
	 * Alias used by payment helpers.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_student_payments( $user_id = 0, $args = array() ) {
		return self::get_payment_history( $user_id, $args );
	}

	/**
	 * Alias for single transaction.
	 *
	 * @param int $payment_id Payment ID.
	 * @param int $user_id    WP user ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_student_transaction( $payment_id, $user_id = 0 ) {
		return self::get_transaction_details( $payment_id, $user_id );
	}

	/**
	 * @param int $user_id WP user ID.
	 * @return bool
	 */
	private static function authorize_view( $user_id ) {
		if ( ! is_user_logged_in() || ! GMM_Student::can_view_profile( $user_id ) ) {
			return false;
		}
		return current_user_can( 'manage_gmm_bookings' )
			|| current_user_can( 'manage_gmm_profile' )
			|| current_user_can( 'manage_options' );
	}
}
