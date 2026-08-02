<?php
/**
 * Payment helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Create a payment record.
 *
 * @param array<string, mixed> $data  Fields.
 * @param string               $nonce Optional nonce.
 * @return int|WP_Error
 */
function gmm_create_payment( $data, $nonce = '' ) {
	if ( ! class_exists( 'GMM_Payment' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Payment system unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Payment::create( $data, $nonce );
}

/**
 * Student payment history.
 *
 * @param int                  $user_id WP user ID.
 * @param array<string, mixed> $args    Filters.
 * @return array<int, array<string, mixed>>
 */
function gmm_get_student_payments( $user_id = 0, $args = array() ) {
	if ( ! class_exists( 'GMM_Student_Payments' ) ) {
		return array();
	}
	return GMM_Student_Payments::get_payment_history( $user_id, $args );
}

/**
 * Student single transaction.
 *
 * @param int $payment_id Payment ID.
 * @param int $user_id    WP user ID.
 * @return array<string, mixed>|null
 */
function gmm_get_student_transaction( $payment_id, $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Student_Payments' ) ) {
		return null;
	}
	return GMM_Student_Payments::get_transaction_details( $payment_id, $user_id );
}

/**
 * Rich student transaction details (payment, booking, teacher, class, timeline).
 *
 * @param int $payment_id Payment ID.
 * @param int $user_id    WP user ID.
 * @return array<string, mixed>|null
 */
function gmm_get_student_transaction_details( $payment_id, $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Student_Payments' ) ) {
		return null;
	}
	return GMM_Student_Payments::get_student_transaction_details( $payment_id, $user_id );
}

/**
 * Student payment receipt structure (no PDF).
 *
 * @param int $payment_id Payment ID.
 * @param int $user_id    WP user ID.
 * @return array<string, mixed>|null
 */
function gmm_get_student_payment_receipt( $payment_id, $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Student_Payments' ) ) {
		return null;
	}
	return GMM_Student_Payments::get_receipt( $payment_id, $user_id );
}

/**
 * Teacher earnings summary.
 *
 * @param int $user_id WP user ID.
 * @return array<string, mixed>
 */
function gmm_get_teacher_earnings( $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Teacher_Earnings' ) ) {
		return array(
			'total_earnings'      => 0.0,
			'pending_earnings'    => 0.0,
			'completed_earnings'  => 0.0,
			'available_balance'   => 0.0,
			'withdrawn_amount'    => 0.0,
			'pending_withdrawals' => 0.0,
			'platform_commission' => 0.0,
			'commission_percent'  => 0.0,
			'min_withdrawal'      => 50.0,
		);
	}
	return GMM_Teacher_Earnings::get_earnings( $user_id );
}

/**
 * Teacher transactions list.
 *
 * @param int                  $user_id WP user ID.
 * @param array<string, mixed> $args    Filters.
 * @return array<int, array<string, mixed>>
 */
function gmm_get_teacher_transactions( $user_id = 0, $args = array() ) {
	if ( ! class_exists( 'GMM_Teacher_Earnings' ) ) {
		return array();
	}
	return GMM_Teacher_Earnings::get_transactions( $user_id, $args );
}

/**
 * Create teacher withdrawal request.
 *
 * @param array<string, mixed> $data  Fields (amount, payment_method, account_details).
 * @param string               $nonce Optional nonce.
 * @return int|WP_Error
 */
function gmm_create_withdrawal_request( $data, $nonce = '' ) {
	if ( ! class_exists( 'GMM_Teacher_Earnings' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Earnings system unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Teacher_Earnings::create_withdrawal_request( $data, $nonce );
}

/**
 * Teacher withdrawal history.
 *
 * @param int                  $user_id WP user ID.
 * @param array<string, mixed> $args    Filters.
 * @return array<int, array<string, mixed>>
 */
function gmm_get_withdrawal_history( $user_id = 0, $args = array() ) {
	if ( ! class_exists( 'GMM_Teacher_Earnings' ) ) {
		return array();
	}
	return GMM_Teacher_Earnings::get_withdrawal_history( $user_id, $args );
}

/**
 * Prepare refund request (no gateway).
 *
 * @param int    $payment_id Payment ID.
 * @param string $reason     Reason.
 * @param string $nonce      Optional nonce.
 * @return array<string, mixed>|WP_Error
 */
function gmm_create_refund_request( $payment_id, $reason = '', $nonce = '' ) {
	if ( ! class_exists( 'GMM_Payment' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Payment system unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Payment::create_refund_request( $payment_id, $reason, $nonce );
}

/**
 * Process refund locally (admin; no gateway).
 *
 * @param int    $payment_id Payment ID.
 * @param string $nonce      Optional nonce.
 * @return true|WP_Error
 */
function gmm_process_refund( $payment_id, $nonce = '' ) {
	if ( ! class_exists( 'GMM_Payment' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Payment system unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Payment::process_refund( $payment_id, $nonce );
}

/**
 * Platform commission percent from settings.
 *
 * @return float
 */
function gmm_get_commission_percent() {
	return class_exists( 'GMM_Payment' ) ? GMM_Payment::get_commission_percent() : 10.0;
}

/**
 * Payment nonce field.
 *
 * @return string
 */
function gmm_payment_nonce_field() {
	return wp_nonce_field( 'gmm_payment_action', 'gmm_payment_nonce', true, false );
}

/**
 * Verify payment nonce.
 *
 * @param string $nonce Nonce.
 * @return bool
 */
function gmm_verify_payment_nonce( $nonce ) {
	return class_exists( 'GMM_Payment' ) ? GMM_Payment::verify_nonce( $nonce ) : false;
}
