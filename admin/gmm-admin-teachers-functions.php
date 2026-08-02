<?php
/**
 * Admin teacher management helpers.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * List teachers for admin UI.
 *
 * @param array<string, mixed> $args Args.
 * @return array<string, mixed>
 */
function gmm_admin_list_teachers( $args = array() ) {
	if ( ! class_exists( 'GMM_Admin_Teachers' ) ) {
		return array( 'items' => array(), 'total' => 0 );
	}
	return GMM_Admin_Teachers::list_teachers( $args );
}

/**
 * Teacher management stats.
 *
 * @return array<string, int>
 */
function gmm_admin_teacher_stats() {
	if ( ! class_exists( 'GMM_Admin_Teachers' ) ) {
		return array(
			'total'     => 0,
			'pending'   => 0,
			'approved'  => 0,
			'suspended' => 0,
		);
	}
	return GMM_Admin_Teachers::get_stats();
}

/**
 * Approve teacher.
 *
 * @param int $teacher_id Teacher ID.
 * @return true|WP_Error
 */
function gmm_admin_approve_teacher( $teacher_id ) {
	return class_exists( 'GMM_Admin_Teachers' )
		? GMM_Admin_Teachers::approve( $teacher_id )
		: new WP_Error( 'gmm_missing', __( 'Teacher management unavailable.', 'gospel-music-mastery' ) );
}

/**
 * Reject teacher.
 *
 * @param int    $teacher_id Teacher ID.
 * @param string $reason     Reason.
 * @return true|WP_Error
 */
function gmm_admin_reject_teacher( $teacher_id, $reason = '' ) {
	return class_exists( 'GMM_Admin_Teachers' )
		? GMM_Admin_Teachers::reject( $teacher_id, $reason )
		: new WP_Error( 'gmm_missing', __( 'Teacher management unavailable.', 'gospel-music-mastery' ) );
}

/**
 * Suspend teacher.
 *
 * @param int $teacher_id Teacher ID.
 * @return true|WP_Error
 */
function gmm_admin_suspend_teacher( $teacher_id ) {
	return class_exists( 'GMM_Admin_Teachers' )
		? GMM_Admin_Teachers::suspend( $teacher_id )
		: new WP_Error( 'gmm_missing', __( 'Teacher management unavailable.', 'gospel-music-mastery' ) );
}

/**
 * Soft-delete teacher plugin data.
 *
 * @param int $teacher_id Teacher ID.
 * @return true|WP_Error
 */
function gmm_admin_delete_teacher( $teacher_id ) {
	return class_exists( 'GMM_Admin_Teachers' )
		? GMM_Admin_Teachers::delete_teacher( $teacher_id )
		: new WP_Error( 'gmm_missing', __( 'Teacher management unavailable.', 'gospel-music-mastery' ) );
}

/**
 * Build pagination query URL preserving filters.
 *
 * @param int                  $page    Page number.
 * @param array<string, mixed> $filters Current filters.
 * @return string
 */
function gmm_admin_teachers_page_url( $page, $filters = array() ) {
	$args = array(
		'at_page' => max( 1, absint( $page ) ),
	);
	if ( ! empty( $filters['search'] ) ) {
		$args['at_search'] = (string) $filters['search'];
	}
	if ( ! empty( $filters['status'] ) && 'all' !== $filters['status'] ) {
		$args['at_status'] = (string) $filters['status'];
	}
	if ( ! empty( $filters['specialty'] ) && 'all' !== $filters['specialty'] ) {
		$args['at_specialty'] = (string) $filters['specialty'];
	}

	$base = '';
	if ( is_admin() && function_exists( 'menu_page_url' ) ) {
		$base = menu_page_url( 'gmm-teachers', false );
	}
	if ( ! $base ) {
		$base = function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'admin_teachers' ) : '';
	}
	if ( ! $base ) {
		$base = remove_query_arg( array( 'at_page', 'at_search', 'at_status', 'at_specialty' ) );
	}

	return add_query_arg( $args, $base );
}
