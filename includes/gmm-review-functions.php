<?php
/**
 * Review and rating helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Create a student review.
 *
 * @param array<string, mixed> $data  Review fields (teacher_id, class_id, rating, comment).
 * @param string               $nonce Optional nonce (gmm_review_action).
 * @param int                  $user_id Optional WP user ID.
 * @return int|WP_Error
 */
function gmm_create_review( $data, $nonce = '', $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Reviews' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Reviews unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Reviews::create( $data, $nonce, $user_id );
}

/**
 * Update own review.
 *
 * @param int                  $review_id Review ID.
 * @param array<string, mixed> $data      Fields.
 * @param string               $nonce     Optional nonce.
 * @param int                  $user_id   Optional user.
 * @return true|WP_Error
 */
function gmm_update_review( $review_id, $data, $nonce = '', $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Reviews' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Reviews unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Reviews::update( $review_id, $data, $nonce, $user_id );
}

/**
 * Delete a review (own as student, or admin).
 *
 * @param int    $review_id Review ID.
 * @param string $nonce     Optional nonce.
 * @param int    $user_id   Optional user.
 * @return true|WP_Error
 */
function gmm_delete_review( $review_id, $nonce = '', $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Reviews' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Reviews unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Reviews::delete( $review_id, $nonce, $user_id );
}

/**
 * Get reviews written by a student.
 *
 * @param int                  $user_id WP user ID.
 * @param array<string, mixed> $args    Filters.
 * @return array<int, array<string, mixed>>
 */
function gmm_get_student_reviews( $user_id = 0, $args = array() ) {
	if ( ! class_exists( 'GMM_Reviews' ) ) {
		return array();
	}
	return GMM_Reviews::get_student_reviews( $user_id, $args );
}

/**
 * Get reviews for a teacher.
 *
 * @param int                  $teacher_id Teacher row ID.
 * @param array<string, mixed> $args       Filters.
 * @return array<int, array<string, mixed>>
 */
function gmm_get_teacher_reviews( $teacher_id, $args = array() ) {
	if ( ! class_exists( 'GMM_Reviews' ) ) {
		return array();
	}
	return GMM_Reviews::get_teacher_reviews( $teacher_id, $args );
}

/**
 * Get reviews for a class.
 *
 * @param int                  $class_id Class ID.
 * @param array<string, mixed> $args     Filters.
 * @return array<int, array<string, mixed>>
 */
function gmm_get_class_reviews( $class_id, $args = array() ) {
	if ( ! class_exists( 'GMM_Reviews' ) ) {
		return array();
	}
	return GMM_Reviews::get_class_reviews( $class_id, $args );
}

/**
 * Calculate and update average teacher rating.
 *
 * Returns average + total approved reviews.
 *
 * @param int $teacher_id Teacher row ID.
 * @return array{average:float,total:int}|float
 */
function gmm_calculate_teacher_rating( $teacher_id ) {
	if ( ! class_exists( 'GMM_Reviews' ) ) {
		return array(
			'average' => 0.0,
			'total'   => 0,
		);
	}

	$average = GMM_Reviews::calculate_teacher_rating( $teacher_id );
	$total   = method_exists( 'GMM_Reviews', 'count_teacher_reviews' )
		? GMM_Reviews::count_teacher_reviews( $teacher_id )
		: 0;

	return array(
		'average' => (float) $average,
		'total'   => (int) $total,
	);
}

/**
 * Calculate and update average class rating.
 *
 * @param int $class_id Class ID.
 * @return float
 */
function gmm_calculate_class_rating( $class_id ) {
	if ( ! class_exists( 'GMM_Reviews' ) ) {
		return 0.0;
	}
	return GMM_Reviews::calculate_class_rating( $class_id );
}

/**
 * Admin: list reviews.
 *
 * @param array<string, mixed> $args Filters.
 * @return array<int, array<string, mixed>>|WP_Error
 */
function gmm_admin_get_reviews( $args = array() ) {
	if ( ! class_exists( 'GMM_Reviews' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Reviews unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Reviews::admin_get_reviews( $args );
}

/**
 * Admin: approve review.
 *
 * @param int    $review_id Review ID.
 * @param string $nonce     Optional nonce.
 * @return true|WP_Error
 */
function gmm_admin_approve_review( $review_id, $nonce = '' ) {
	if ( ! class_exists( 'GMM_Reviews' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Reviews unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Reviews::admin_approve( $review_id, $nonce );
}

/**
 * Admin: reject review.
 *
 * @param int    $review_id Review ID.
 * @param string $nonce     Optional nonce.
 * @return true|WP_Error
 */
function gmm_admin_reject_review( $review_id, $nonce = '' ) {
	if ( ! class_exists( 'GMM_Reviews' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Reviews unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Reviews::admin_reject( $review_id, $nonce );
}

/**
 * Admin: delete review.
 *
 * @param int    $review_id Review ID.
 * @param string $nonce     Optional nonce.
 * @return true|WP_Error
 */
function gmm_admin_delete_review( $review_id, $nonce = '' ) {
	if ( ! class_exists( 'GMM_Reviews' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Reviews unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Reviews::admin_delete( $review_id, $nonce );
}
