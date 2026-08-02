<?php
/**
 * AJAX helper query functions.
 *
 * Delegates to GMM_Search. Returns item arrays for backward compatibility,
 * or full paginated payloads when $args['paginate'] is true.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Search teachers (AJAX-compatible).
 *
 * @param array<string, mixed> $args Args.
 * @return array<int, array<string, mixed>>|array<string, mixed>
 */
function gmm_ajax_search_teachers( $args = array() ) {
	$result = gmm_search_teachers( $args );
	if ( ! empty( $args['paginate'] ) ) {
		return $result;
	}
	return isset( $result['items'] ) && is_array( $result['items'] ) ? $result['items'] : array();
}

/**
 * Search students (admin AJAX).
 *
 * @param array<string, mixed> $args Args.
 * @return array<int, array<string, mixed>>|array<string, mixed>
 */
function gmm_ajax_search_students( $args = array() ) {
	$result = gmm_admin_filter_students( $args );
	if ( is_wp_error( $result ) ) {
		return array();
	}
	if ( ! empty( $args['paginate'] ) ) {
		return $result;
	}
	return isset( $result['items'] ) && is_array( $result['items'] ) ? $result['items'] : array();
}

/**
 * Search classes (AJAX-compatible).
 *
 * @param array<string, mixed> $args Args.
 * @return array<int, array<string, mixed>>|array<string, mixed>
 */
function gmm_ajax_search_classes( $args = array() ) {
	$result = gmm_search_classes( $args );
	if ( ! empty( $args['paginate'] ) ) {
		return $result;
	}
	return isset( $result['items'] ) && is_array( $result['items'] ) ? $result['items'] : array();
}

/**
 * Search programs (AJAX-compatible).
 *
 * @param array<string, mixed> $args Args.
 * @return array<int, array<string, mixed>>|array<string, mixed>
 */
function gmm_ajax_search_programs( $args = array() ) {
	$result = gmm_search_programs( $args );
	if ( ! empty( $args['paginate'] ) ) {
		return $result;
	}
	return isset( $result['items'] ) && is_array( $result['items'] ) ? $result['items'] : array();
}
