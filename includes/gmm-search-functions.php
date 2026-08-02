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
