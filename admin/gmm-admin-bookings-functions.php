<?php
/**
 * Admin booking management helpers.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param array<string, mixed> $args Args.
 * @return array<string, mixed>
 */
function gmm_admin_list_bookings( $args = array() ) {
	if ( ! class_exists( 'GMM_Admin_Bookings' ) ) {
		return array( 'items' => array(), 'total' => 0 );
	}
	return GMM_Admin_Bookings::list_bookings( $args );
}

/**
 * @return array<string, int>
 */
function gmm_admin_booking_stats() {
	return class_exists( 'GMM_Admin_Bookings' )
		? GMM_Admin_Bookings::get_stats()
		: array(
			'total'     => 0,
			'upcoming'  => 0,
			'completed' => 0,
			'cancelled' => 0,
			'pending'   => 0,
		);
}

/**
 * @param int $booking_id Booking ID.
 * @return true|WP_Error
 */
function gmm_admin_confirm_booking( $booking_id ) {
	return class_exists( 'GMM_Admin_Bookings' )
		? GMM_Admin_Bookings::confirm( $booking_id )
		: new WP_Error( 'gmm_missing', __( 'Booking management unavailable.', 'gospel-music-mastery' ) );
}

/**
 * @param int $booking_id Booking ID.
 * @return true|WP_Error
 */
function gmm_admin_complete_booking( $booking_id ) {
	return class_exists( 'GMM_Admin_Bookings' )
		? GMM_Admin_Bookings::complete( $booking_id )
		: new WP_Error( 'gmm_missing', __( 'Booking management unavailable.', 'gospel-music-mastery' ) );
}

/**
 * @param int $booking_id Booking ID.
 * @return true|WP_Error
 */
function gmm_admin_cancel_booking( $booking_id ) {
	return class_exists( 'GMM_Admin_Bookings' )
		? GMM_Admin_Bookings::cancel( $booking_id )
		: new WP_Error( 'gmm_missing', __( 'Booking management unavailable.', 'gospel-music-mastery' ) );
}

/**
 * @param int    $booking_id Booking ID.
 * @param string $status     Payment status.
 * @return true|WP_Error
 */
function gmm_admin_set_booking_payment( $booking_id, $status ) {
	return class_exists( 'GMM_Admin_Bookings' )
		? GMM_Admin_Bookings::set_payment_status( $booking_id, $status )
		: new WP_Error( 'gmm_missing', __( 'Booking management unavailable.', 'gospel-music-mastery' ) );
}

/**
 * Prepare export rows (CSV / reports later — no UI yet).
 *
 * @param array<string, mixed> $filters Filters.
 * @return array<int, array<string, mixed>>
 */
function gmm_admin_prepare_booking_export( $filters = array() ) {
	return class_exists( 'GMM_Admin_Bookings' )
		? GMM_Admin_Bookings::prepare_export_rows( $filters )
		: array();
}

/**
 * @param int                  $page    Page.
 * @param array<string, mixed> $filters Filters.
 * @return string
 */
function gmm_admin_bookings_page_url( $page, $filters = array() ) {
	$args = array( 'ab_page' => max( 1, absint( $page ) ) );
	if ( ! empty( $filters['search'] ) ) {
		$args['ab_search'] = (string) $filters['search'];
	}
	if ( ! empty( $filters['status'] ) && 'all' !== $filters['status'] ) {
		$args['ab_status'] = (string) $filters['status'];
	}
	if ( ! empty( $filters['payment'] ) && 'all' !== $filters['payment'] ) {
		$args['ab_payment'] = (string) $filters['payment'];
	}
	if ( ! empty( $filters['period'] ) && 'all' !== $filters['period'] ) {
		$args['ab_date'] = (string) $filters['period'];
	}
	if ( ! empty( $filters['date_from'] ) ) {
		$args['ab_date_from'] = (string) $filters['date_from'];
	}
	if ( ! empty( $filters['date_to'] ) ) {
		$args['ab_date_to'] = (string) $filters['date_to'];
	}

	$base = '';
	if ( is_admin() && function_exists( 'menu_page_url' ) ) {
		$base = menu_page_url( 'gmm-bookings', false );
	}
	if ( ! $base ) {
		$base = function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'admin_bookings' ) : '';
	}
	if ( ! $base ) {
		$base = remove_query_arg( array( 'ab_page', 'ab_search', 'ab_status', 'ab_payment', 'ab_date', 'ab_date_from', 'ab_date_to' ) );
	}

	return add_query_arg( $args, $base );
}
