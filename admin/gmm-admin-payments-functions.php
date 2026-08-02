<?php
/**
 * Admin payment management helpers.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin revenue summary.
 *
 * @return array<string, mixed>
 */
function gmm_get_admin_revenue() {
	if ( class_exists( 'GMM_Admin_Payments' ) ) {
		return GMM_Admin_Payments::get_revenue();
	}
	return array(
		'total_revenue'       => 0,
		'completed_payments'  => 0,
		'pending_payments'    => 0,
		'refund_amount'       => 0,
		'platform_commission' => 0,
		'teacher_earnings'    => 0,
		'pending_payouts'     => 0,
		'completed_count'     => 0,
		'refund_count'        => 0,
		'commission_percent'  => function_exists( 'gmm_get_commission_percent' ) ? gmm_get_commission_percent() : 10,
	);
}

/**
 * @param array<string, mixed> $args Args.
 * @return array<string, mixed>
 */
function gmm_admin_list_payments( $args = array() ) {
	if ( ! class_exists( 'GMM_Admin_Payments' ) ) {
		return array( 'items' => array(), 'total' => 0 );
	}
	return GMM_Admin_Payments::list_payments( $args );
}

/**
 * @param int    $payment_id Payment ID.
 * @param string $status     Status.
 * @return true|WP_Error
 */
function gmm_admin_set_payment_status( $payment_id, $status ) {
	return class_exists( 'GMM_Admin_Payments' )
		? GMM_Admin_Payments::set_status( $payment_id, $status )
		: new WP_Error( 'gmm_missing', __( 'Payment management unavailable.', 'gospel-music-mastery' ) );
}

/**
 * @param int $limit Limit.
 * @return array<int, array<string, mixed>>
 */
function gmm_admin_teacher_earnings_overview( $limit = 12 ) {
	return class_exists( 'GMM_Admin_Payments' )
		? GMM_Admin_Payments::get_teacher_earnings_overview( $limit )
		: array();
}

/**
 * @param array<string, mixed> $filters Filters.
 * @return array<int, array<string, mixed>>
 */
function gmm_admin_prepare_payment_export( $filters = array() ) {
	return class_exists( 'GMM_Admin_Payments' )
		? GMM_Admin_Payments::prepare_export_rows( $filters )
		: array();
}

/**
 * @param int                  $page    Page.
 * @param array<string, mixed> $filters Filters.
 * @return string
 */
function gmm_admin_payments_page_url( $page, $filters = array() ) {
	$args = array( 'ap_page' => max( 1, absint( $page ) ) );
	if ( ! empty( $filters['search'] ) ) {
		$args['ap_search'] = (string) $filters['search'];
	}
	if ( ! empty( $filters['status'] ) && 'all' !== $filters['status'] ) {
		$args['ap_status'] = (string) $filters['status'];
	}
	if ( ! empty( $filters['type'] ) && 'all' !== $filters['type'] ) {
		$args['ap_type'] = (string) $filters['type'];
	}
	if ( ! empty( $filters['method'] ) && 'all' !== $filters['method'] ) {
		$args['ap_method'] = (string) $filters['method'];
	}
	if ( ! empty( $filters['period'] ) && 'all' !== $filters['period'] ) {
		$args['ap_date'] = (string) $filters['period'];
	}
	if ( ! empty( $filters['date_from'] ) ) {
		$args['ap_date_from'] = (string) $filters['date_from'];
	}
	if ( ! empty( $filters['date_to'] ) ) {
		$args['ap_date_to'] = (string) $filters['date_to'];
	}

	$base = '';
	if ( is_admin() && function_exists( 'menu_page_url' ) ) {
		$base = menu_page_url( 'gmm-payments', false );
	}
	if ( ! $base ) {
		$base = function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'admin_payments' ) : '';
	}
	if ( ! $base ) {
		$base = remove_query_arg( array( 'ap_page', 'ap_search', 'ap_status', 'ap_type', 'ap_method', 'ap_date', 'ap_date_from', 'ap_date_to' ) );
	}

	return add_query_arg( $args, $base );
}
