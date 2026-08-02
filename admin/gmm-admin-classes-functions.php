<?php
/**
 * Admin class management helpers.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param array<string, mixed> $args Args.
 * @return array<string, mixed>
 */
function gmm_admin_list_classes( $args = array() ) {
	if ( ! class_exists( 'GMM_Admin_Classes' ) ) {
		return array( 'items' => array(), 'total' => 0 );
	}
	return GMM_Admin_Classes::list_classes( $args );
}

/**
 * @return array<string, int>
 */
function gmm_admin_class_stats() {
	return class_exists( 'GMM_Admin_Classes' )
		? GMM_Admin_Classes::get_stats()
		: array(
			'total'    => 0,
			'approved' => 0,
			'pending'  => 0,
			'rejected' => 0,
		);
}

/**
 * @param int $class_id Class ID.
 * @return true|WP_Error
 */
function gmm_admin_approve_class( $class_id ) {
	return class_exists( 'GMM_Admin_Classes' )
		? GMM_Admin_Classes::approve( $class_id )
		: new WP_Error( 'gmm_missing', __( 'Class management unavailable.', 'gospel-music-mastery' ) );
}

/**
 * @param int    $class_id Class ID.
 * @param string $reason   Reason.
 * @return true|WP_Error
 */
function gmm_admin_reject_class( $class_id, $reason = '' ) {
	return class_exists( 'GMM_Admin_Classes' )
		? GMM_Admin_Classes::reject( $class_id, $reason )
		: new WP_Error( 'gmm_missing', __( 'Class management unavailable.', 'gospel-music-mastery' ) );
}

/**
 * Public-facing approved class statuses.
 *
 * @return array<int, string>
 */
function gmm_public_class_statuses() {
	return class_exists( 'GMM_Admin_Classes' )
		? GMM_Admin_Classes::public_statuses()
		: array( 'approved', 'published' );
}

/**
 * @param int                  $page    Page.
 * @param array<string, mixed> $filters Filters.
 * @return string
 */
function gmm_admin_classes_page_url( $page, $filters = array() ) {
	$args = array( 'ac_page' => max( 1, absint( $page ) ) );
	if ( ! empty( $filters['search'] ) ) {
		$args['ac_search'] = (string) $filters['search'];
	}
	if ( ! empty( $filters['status'] ) && 'all' !== $filters['status'] ) {
		$args['ac_status'] = (string) $filters['status'];
	}
	if ( ! empty( $filters['category'] ) && 'all' !== $filters['category'] ) {
		$args['ac_category'] = (string) $filters['category'];
	}
	if ( ! empty( $filters['difficulty'] ) && 'all' !== $filters['difficulty'] ) {
		$args['ac_difficulty'] = (string) $filters['difficulty'];
	}

	$base = '';
	if ( is_admin() && function_exists( 'menu_page_url' ) ) {
		$base = menu_page_url( 'gmm-classes', false );
	}
	if ( ! $base ) {
		$base = function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'admin_classes' ) : '';
	}
	if ( ! $base ) {
		$base = remove_query_arg( array( 'ac_page', 'ac_search', 'ac_status', 'ac_category', 'ac_difficulty' ) );
	}

	return add_query_arg( $args, $base );
}
