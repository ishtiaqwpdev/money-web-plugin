<?php
/**
 * Search helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Search teachers with filters and pagination.
 *
 * @param array<string, mixed> $args Search args.
 * @return array<string, mixed>
 */
function gmm_search_teachers( $args = array() ) {
	if ( class_exists( 'GMM_Teacher_Search' ) && ! empty( $args['public'] ) ) {
		return GMM_Teacher_Search::search( $args );
	}
	if ( ! class_exists( 'GMM_Search' ) ) {
		return array(
			'items'       => array(),
			'total'       => 0,
			'page'        => 1,
			'per_page'    => 12,
			'total_pages' => 0,
			'has_prev'    => false,
			'has_next'    => false,
			'prev_page'   => null,
			'next_page'   => null,
		);
	}
	return GMM_Search::search_teachers( $args );
}

/**
 * Search classes with filters and pagination.
 *
 * @param array<string, mixed> $args Search args.
 * @return array<string, mixed>
 */
function gmm_search_classes( $args = array() ) {
	if ( ! class_exists( 'GMM_Search' ) ) {
		return array(
			'items'       => array(),
			'total'       => 0,
			'page'        => 1,
			'per_page'    => 12,
			'total_pages' => 0,
			'has_prev'    => false,
			'has_next'    => false,
			'prev_page'   => null,
			'next_page'   => null,
		);
	}
	return GMM_Search::search_classes( $args );
}

/**
 * Search programs with filters and pagination.
 *
 * @param array<string, mixed> $args Search args.
 * @return array<string, mixed>
 */
function gmm_search_programs( $args = array() ) {
	if ( ! class_exists( 'GMM_Search' ) ) {
		return array(
			'items'       => array(),
			'total'       => 0,
			'page'        => 1,
			'per_page'    => 12,
			'total_pages' => 0,
			'has_prev'    => false,
			'has_next'    => false,
			'prev_page'   => null,
			'next_page'   => null,
		);
	}
	return GMM_Search::search_programs( $args );
}

/**
 * Admin filter teachers.
 *
 * @param array<string, mixed> $args Args.
 * @return array<string, mixed>|WP_Error
 */
function gmm_admin_filter_teachers( $args = array() ) {
	if ( ! class_exists( 'GMM_Search' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Search unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Search::admin_filter_teachers( $args );
}

/**
 * Admin filter students.
 *
 * @param array<string, mixed> $args Args.
 * @return array<string, mixed>|WP_Error
 */
function gmm_admin_filter_students( $args = array() ) {
	if ( ! class_exists( 'GMM_Search' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Search unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Search::admin_filter_students( $args );
}

/**
 * Admin filter classes.
 *
 * @param array<string, mixed> $args Args.
 * @return array<string, mixed>|WP_Error
 */
function gmm_admin_filter_classes( $args = array() ) {
	if ( ! class_exists( 'GMM_Search' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Search unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Search::admin_filter_classes( $args );
}

/**
 * Admin filter bookings.
 *
 * @param array<string, mixed> $args Args.
 * @return array<string, mixed>|WP_Error
 */
function gmm_admin_filter_bookings( $args = array() ) {
	if ( class_exists( 'GMM_Admin_Bookings' ) ) {
		$list_args = array(
			'search'    => isset( $args['search'] ) ? $args['search'] : '',
			'status'    => ! empty( $args['status'] ) ? $args['status'] : 'all',
			'payment'   => ! empty( $args['payment'] ) ? $args['payment'] : 'all',
			'period'    => ! empty( $args['period'] ) ? $args['period'] : ( ! empty( $args['date'] ) ? 'custom' : 'all' ),
			'date_from' => isset( $args['date_from'] ) ? $args['date_from'] : ( ! empty( $args['date'] ) ? $args['date'] : '' ),
			'date_to'   => isset( $args['date_to'] ) ? $args['date_to'] : ( ! empty( $args['date'] ) ? $args['date'] : '' ),
			'page'      => isset( $args['page'] ) ? absint( $args['page'] ) : 1,
			'per_page'  => isset( $args['per_page'] ) ? absint( $args['per_page'] ) : GMM_Admin_Bookings::PER_PAGE,
		);
		return GMM_Admin_Bookings::list_bookings( $list_args );
	}
	if ( ! class_exists( 'GMM_Search' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Search unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Search::admin_filter_bookings( $args );
}

/**
 * Admin filter payments.
 *
 * @param array<string, mixed> $args Args.
 * @return array<string, mixed>|WP_Error
 */
function gmm_admin_filter_payments( $args = array() ) {
	if ( class_exists( 'GMM_Admin_Payments' ) ) {
		$list_args = array(
			'search'    => isset( $args['search'] ) ? $args['search'] : '',
			'status'    => ! empty( $args['status'] ) ? $args['status'] : 'all',
			'type'      => ! empty( $args['type'] ) ? $args['type'] : 'all',
			'method'    => ! empty( $args['method'] ) ? $args['method'] : ( ! empty( $args['payment_method'] ) ? $args['payment_method'] : 'all' ),
			'period'    => ! empty( $args['period'] ) ? $args['period'] : 'all',
			'date_from' => isset( $args['date_from'] ) ? $args['date_from'] : '',
			'date_to'   => isset( $args['date_to'] ) ? $args['date_to'] : '',
			'page'      => isset( $args['page'] ) ? absint( $args['page'] ) : 1,
			'per_page'  => isset( $args['per_page'] ) ? absint( $args['per_page'] ) : GMM_Admin_Payments::PER_PAGE,
		);
		return GMM_Admin_Payments::list_payments( $list_args );
	}
	if ( ! class_exists( 'GMM_Search' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Search unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Search::admin_filter_payments( $args );
}

/**
 * Admin filter blogs.
 *
 * @param array<string, mixed> $args Args.
 * @return array<string, mixed>|WP_Error
 */
function gmm_admin_filter_blogs( $args = array() ) {
	if ( ! class_exists( 'GMM_Search' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Search unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Search::admin_filter_blogs( $args );
}
