<?php
/**
 * Analytics helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin platform statistics.
 *
 * @param array<string, mixed> $args Optional date filters.
 * @return array<string, mixed>|WP_Error
 */
function gmm_get_admin_statistics( $args = array() ) {
	if ( ! class_exists( 'GMM_Analytics' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Analytics unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Analytics::get_admin_statistics( $args );
}

/**
 * Revenue report (daily/weekly/monthly/yearly + chart).
 *
 * @param array<string, mixed> $args Optional filters.
 * @return array<string, mixed>|WP_Error
 */
function gmm_get_revenue_report( $args = array() ) {
	if ( ! class_exists( 'GMM_Analytics' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Analytics unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Analytics::get_revenue_report( $args );
}

/**
 * Booking status report.
 *
 * @param array<string, mixed> $args Optional filters.
 * @return array<string, mixed>|WP_Error
 */
function gmm_get_booking_report( $args = array() ) {
	if ( ! class_exists( 'GMM_Analytics' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Analytics unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Analytics::get_booking_report( $args );
}

/**
 * Teacher statistics (own data only).
 *
 * @param int                  $user_id WP user ID.
 * @param array<string, mixed> $args    Optional filters.
 * @return array<string, mixed>|WP_Error
 */
function gmm_get_teacher_statistics( $user_id = 0, $args = array() ) {
	if ( ! class_exists( 'GMM_Analytics' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Analytics unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Analytics::get_teacher_statistics( $user_id, $args );
}

/**
 * Student statistics (own data only).
 *
 * @param int                  $user_id WP user ID.
 * @param array<string, mixed> $args    Optional filters.
 * @return array<string, mixed>|WP_Error
 */
function gmm_get_student_statistics( $user_id = 0, $args = array() ) {
	if ( ! class_exists( 'GMM_Analytics' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Analytics unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Analytics::get_student_statistics( $user_id, $args );
}

/**
 * Program statistics.
 *
 * @param array<string, mixed> $args Optional args.
 * @return array<string, mixed>|WP_Error
 */
function gmm_get_program_statistics( $args = array() ) {
	if ( ! class_exists( 'GMM_Analytics' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Analytics unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Analytics::get_program_statistics( $args );
}

/**
 * Admin report dataset (CSV-ready structure).
 *
 * @param string               $type teacher|student|booking|payment|class.
 * @param array<string, mixed> $args Filters.
 * @return array<string, mixed>|WP_Error
 */
function gmm_get_admin_report( $type, $args = array() ) {
	if ( ! class_exists( 'GMM_Analytics' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Analytics unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Analytics::get_admin_report( $type, $args );
}

/**
 * Chart.js revenue data helper.
 *
 * @param string               $period Period key.
 * @param array<string, mixed> $args   Extra args.
 * @return array<string, mixed>
 */
function gmm_get_revenue_chart_data( $period = 'this_year', $args = array() ) {
	if ( ! class_exists( 'GMM_Analytics' ) ) {
		return array(
			'labels'   => array(),
			'datasets' => array(),
		);
	}
	return GMM_Analytics::get_revenue_chart_data( $period, $args );
}

/**
 * Chart.js booking data helper.
 *
 * @param string               $period Period key.
 * @param array<string, mixed> $args   Extra args.
 * @return array<string, mixed>
 */
function gmm_get_booking_chart_data( $period = 'this_year', $args = array() ) {
	if ( ! class_exists( 'GMM_Analytics' ) ) {
		return array(
			'labels'   => array(),
			'datasets' => array(),
		);
	}
	return GMM_Analytics::get_booking_chart_data( $period, $args );
}
