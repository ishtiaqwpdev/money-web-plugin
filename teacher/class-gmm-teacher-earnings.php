<?php
/**
 * Teacher earnings and transaction views.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Teacher_Earnings
 *
 * Teachers may only view their own payment-derived earnings.
 */
class GMM_Teacher_Earnings {

	/**
	 * Earnings summary with commission split.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_earnings( $user_id = 0 ) {
		$percent = class_exists( 'GMM_Payment' ) ? GMM_Payment::get_commission_percent() : 10.0;
		$empty   = array(
			'total_earnings'      => 0.0,
			'pending_earnings'    => 0.0,
			'completed_earnings'  => 0.0,
			'platform_commission' => 0.0,
			'commission_percent'  => $percent,
		);

		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id || ! is_user_logged_in() ) {
			return $empty;
		}

		if ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== $user_id ) {
			return $empty;
		}

		if ( ! class_exists( 'GMM_Teacher' ) || ! GMM_Teacher::can_view_profile( $user_id ) ) {
			return $empty;
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return $empty;
		}

		global $wpdb;
		$table = GMM_Database::table( 'payments' );

		$completed_gross = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$table}
				WHERE teacher_id = %d
				AND payment_status = %s
				AND payment_method <> %s",
				$teacher_id,
				'completed',
				'withdrawal'
			)
		);

		$pending_gross = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$table}
				WHERE teacher_id = %d
				AND payment_status = %s
				AND payment_method <> %s",
				$teacher_id,
				'pending',
				'withdrawal'
			)
		);

		$completed_split = class_exists( 'GMM_Payment' )
			? GMM_Payment::calculate_split( $completed_gross )
			: array(
				'teacher_earnings' => $completed_gross,
				'commission'       => 0,
			);

		$pending_split = class_exists( 'GMM_Payment' )
			? GMM_Payment::calculate_split( $pending_gross )
			: array(
				'teacher_earnings' => $pending_gross,
				'commission'       => 0,
			);

		$teacher_completed = (float) $completed_split['teacher_earnings'];
		$teacher_pending   = (float) $pending_split['teacher_earnings'];

		return array(
			'total_earnings'      => round( $teacher_completed + $teacher_pending, 2 ),
			'pending_earnings'    => round( $teacher_pending, 2 ),
			'completed_earnings'  => round( $teacher_completed, 2 ),
			'platform_commission' => round( (float) $completed_split['commission'], 2 ),
			'commission_percent'  => $percent,
		);
	}

	/**
	 * Payment transactions for teacher (own only).
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_transactions( $user_id = 0, $args = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id || ! is_user_logged_in() ) {
			return array();
		}

		if ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== $user_id ) {
			return array();
		}

		if ( ! class_exists( 'GMM_Teacher' ) || ! GMM_Teacher::can_view_profile( $user_id ) ) {
			return array();
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}

		global $wpdb;
		$table = GMM_Database::table( 'payments' );

		$sql    = "SELECT * FROM {$table} WHERE teacher_id = %d AND payment_method <> %s";
		$params = array( $teacher_id, 'withdrawal' );

		if ( ! empty( $args['status'] ) ) {
			$sql     .= ' AND payment_status = %s';
			$params[] = sanitize_key( $args['status'] );
		}

		$sql     .= ' ORDER BY created_at DESC LIMIT %d';
		$params[] = isset( $args['limit'] ) ? min( absint( $args['limit'] ), 200 ) : 100;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
		if ( ! is_array( $rows ) ) {
			return array();
		}

		foreach ( $rows as &$row ) {
			$split = class_exists( 'GMM_Payment' )
				? GMM_Payment::calculate_split( isset( $row['amount'] ) ? (float) $row['amount'] : 0 )
				: array();
			$row['split'] = $split;
		}
		unset( $row );

		return $rows;
	}
}
