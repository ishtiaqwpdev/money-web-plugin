<?php
/**
 * Admin student management helpers.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * @param array<string, mixed> $args Args.
 * @return array<string, mixed>
 */
function gmm_admin_list_students( $args = array() ) {
	if ( ! class_exists( 'GMM_Admin_Students' ) ) {
		return array( 'items' => array(), 'total' => 0 );
	}
	return GMM_Admin_Students::list_students( $args );
}

/**
 * @return array<string, int>
 */
function gmm_admin_student_stats() {
	if ( ! class_exists( 'GMM_Admin_Students' ) ) {
		return array(
			'total'     => 0,
			'active'    => 0,
			'new'       => 0,
			'suspended' => 0,
		);
	}
	return GMM_Admin_Students::get_stats();
}

/**
 * @param int $student_id Student ID.
 * @return true|WP_Error
 */
function gmm_admin_activate_student( $student_id ) {
	return class_exists( 'GMM_Admin_Students' )
		? GMM_Admin_Students::set_status( $student_id, 'active' )
		: new WP_Error( 'gmm_missing', __( 'Student management unavailable.', 'gospel-music-mastery' ) );
}

/**
 * @param int $student_id Student ID.
 * @return true|WP_Error
 */
function gmm_admin_deactivate_student( $student_id ) {
	return class_exists( 'GMM_Admin_Students' )
		? GMM_Admin_Students::set_status( $student_id, 'inactive' )
		: new WP_Error( 'gmm_missing', __( 'Student management unavailable.', 'gospel-music-mastery' ) );
}

/**
 * @param int $student_id Student ID.
 * @return true|WP_Error
 */
function gmm_admin_suspend_student( $student_id ) {
	return class_exists( 'GMM_Admin_Students' )
		? GMM_Admin_Students::set_status( $student_id, 'suspended' )
		: new WP_Error( 'gmm_missing', __( 'Student management unavailable.', 'gospel-music-mastery' ) );
}

/**
 * @param int $student_id Student ID.
 * @return true|WP_Error
 */
function gmm_admin_delete_student( $student_id ) {
	return class_exists( 'GMM_Admin_Students' )
		? GMM_Admin_Students::delete_student( $student_id )
		: new WP_Error( 'gmm_missing', __( 'Student management unavailable.', 'gospel-music-mastery' ) );
}

/**
 * Build pagination URL preserving filters.
 *
 * @param int                  $page    Page.
 * @param array<string, mixed> $filters Filters.
 * @return string
 */
function gmm_admin_students_page_url( $page, $filters = array() ) {
	$args = array(
		'as_page' => max( 1, absint( $page ) ),
	);
	if ( ! empty( $filters['search'] ) ) {
		$args['as_search'] = (string) $filters['search'];
	}
	if ( ! empty( $filters['status'] ) && 'all' !== $filters['status'] ) {
		$args['as_status'] = (string) $filters['status'];
	}
	if ( ! empty( $filters['level'] ) && 'all' !== $filters['level'] ) {
		$args['as_level'] = (string) $filters['level'];
	}
	if ( ! empty( $filters['period'] ) && 'all' !== $filters['period'] ) {
		$args['as_period'] = (string) $filters['period'];
	}

	$base = '';
	if ( is_admin() && function_exists( 'menu_page_url' ) ) {
		$base = menu_page_url( 'gmm-students', false );
	}
	if ( ! $base ) {
		$base = function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'admin_students' ) : '';
	}
	if ( ! $base ) {
		$base = remove_query_arg( array( 'as_page', 'as_search', 'as_status', 'as_level', 'as_period' ) );
	}

	return add_query_arg( $args, $base );
}
