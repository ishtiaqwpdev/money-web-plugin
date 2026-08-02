<?php
/**
 * Admin dashboard helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Monthly revenue chart (last 12 months) — Chart.js compatible.
 *
 * @return array<string, mixed>
 */
function gmm_get_admin_revenue_chart() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return array(
			'labels'   => array(),
			'datasets' => array(),
		);
	}

	$cached = get_transient( 'gmm_admin_dashboard_revenue_chart' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;
	$table = GMM_Database::table( 'payments' );

	$labels = array();
	$data   = array();

	for ( $i = 11; $i >= 0; $i-- ) {
		$ts       = strtotime( gmdate( 'Y-m-01' ) . " -{$i} months" );
		$month    = gmdate( 'Y-m', $ts );
		$labels[] = gmdate( 'M', $ts );
		$from     = $month . '-01';
		$to       = gmdate( 'Y-m-t', $ts );

		$sum = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$table}
				WHERE payment_status = %s
				AND payment_method <> %s
				AND DATE(created_at) >= %s
				AND DATE(created_at) <= %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				'completed',
				'withdrawal',
				$from,
				$to
			)
		);
		$data[] = round( $sum, 2 );
	}

	$payload = array(
		'labels'   => $labels,
		'datasets' => array(
			array(
				'label' => __( 'Monthly Revenue', 'gospel-music-mastery' ),
				'data'  => $data,
			),
		),
		'total'    => array_sum( $data ),
	);

	set_transient( 'gmm_admin_dashboard_revenue_chart', $payload, 120 );
	return $payload;
}

/**
 * Monthly new students + teachers (last 12 months).
 *
 * @return array<string, mixed>
 */
function gmm_get_user_growth_chart() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return array(
			'labels'   => array(),
			'datasets' => array(),
		);
	}

	$cached = get_transient( 'gmm_admin_dashboard_growth_chart' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;
	$students = GMM_Database::table( 'students' );
	$teachers = GMM_Database::table( 'teachers' );

	$labels   = array();
	$student_data = array();
	$teacher_data = array();

	for ( $i = 11; $i >= 0; $i-- ) {
		$ts       = strtotime( gmdate( 'Y-m-01' ) . " -{$i} months" );
		$month    = gmdate( 'Y-m', $ts );
		$labels[] = gmdate( 'M', $ts );
		$from     = $month . '-01';
		$to       = gmdate( 'Y-m-t', $ts );

		$student_data[] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$students} WHERE DATE(created_at) >= %s AND DATE(created_at) <= %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$from,
				$to
			)
		);
		$teacher_data[] = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$teachers} WHERE DATE(created_at) >= %s AND DATE(created_at) <= %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				$from,
				$to
			)
		);
	}

	$payload = array(
		'labels'   => $labels,
		'datasets' => array(
			array(
				'label' => __( 'Students Growth', 'gospel-music-mastery' ),
				'data'  => $student_data,
			),
			array(
				'label' => __( 'Teachers Growth', 'gospel-music-mastery' ),
				'data'  => $teacher_data,
			),
		),
	);

	set_transient( 'gmm_admin_dashboard_growth_chart', $payload, 120 );
	return $payload;
}

/**
 * Platform distribution: students / teachers / classes.
 *
 * @return array<string, mixed>
 */
function gmm_get_platform_distribution() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return array(
			'labels'   => array(),
			'datasets' => array(),
		);
	}

	$cached = get_transient( 'gmm_admin_dashboard_platform_chart' );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	$stats = class_exists( 'GMM_Admin_Dashboard' )
		? GMM_Admin_Dashboard::get_statistics()
		: array(
			'total_students' => 0,
			'total_teachers' => 0,
			'total_classes'  => 0,
		);

	$payload = array(
		'labels'   => array(
			__( 'Students', 'gospel-music-mastery' ),
			__( 'Teachers', 'gospel-music-mastery' ),
			__( 'Classes', 'gospel-music-mastery' ),
		),
		'datasets' => array(
			array(
				'label' => __( 'Platform', 'gospel-music-mastery' ),
				'data'  => array(
					absint( $stats['total_students'] ),
					absint( $stats['total_teachers'] ),
					absint( $stats['total_classes'] ),
				),
			),
		),
	);

	set_transient( 'gmm_admin_dashboard_platform_chart', $payload, 120 );
	return $payload;
}

/**
 * Recent platform activity feed.
 *
 * @param int $limit Max items.
 * @return array<int, array<string, mixed>>
 */
function gmm_get_recent_activity( $limit = 8 ) {
	$limit = max( 1, min( 30, absint( $limit ) ) );

	if ( ! current_user_can( 'manage_options' ) ) {
		return array();
	}

	$cache_key = 'gmm_admin_dashboard_activity_' . $limit;
	$cached    = get_transient( $cache_key );
	if ( is_array( $cached ) ) {
		return $cached;
	}

	global $wpdb;
	$teachers = GMM_Database::table( 'teachers' );
	$students = GMM_Database::table( 'students' );
	$classes  = GMM_Database::table( 'classes' );
	$payments = GMM_Database::table( 'payments' );
	$bookings = GMM_Database::table( 'bookings' );

	$events = array();

	$t_rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, first_name, last_name, created_at FROM {$teachers} ORDER BY created_at DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$limit
		),
		ARRAY_A
	);
	if ( is_array( $t_rows ) ) {
		foreach ( $t_rows as $row ) {
			$name = trim( $row['first_name'] . ' ' . $row['last_name'] );
			$events[] = array(
				'type'        => 'teacher',
				'css'         => 'is-teacher',
				'icon'        => 'far fa-chalkboard-user',
				'title'       => sprintf(
					/* translators: %s: teacher name */
					__( 'New teacher registered: %s', 'gospel-music-mastery' ),
					$name ? $name : __( 'Teacher', 'gospel-music-mastery' )
				),
				'description' => __( 'Instructor submitted an application for review.', 'gospel-music-mastery' ),
				'time'        => human_time_diff( strtotime( $row['created_at'] ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'gospel-music-mastery' ),
				'url'         => gmm_get_page_link( 'admin_teachers' ),
				'sort'        => strtotime( $row['created_at'] ),
			);
		}
	}

	$s_rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, first_name, last_name, created_at FROM {$students} ORDER BY created_at DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$limit
		),
		ARRAY_A
	);
	if ( is_array( $s_rows ) ) {
		foreach ( $s_rows as $row ) {
			$name = trim( $row['first_name'] . ' ' . $row['last_name'] );
			$events[] = array(
				'type'        => 'student',
				'css'         => 'is-student',
				'icon'        => 'far fa-graduation-cap',
				'title'       => sprintf(
					/* translators: %s: student name */
					__( 'New student joined: %s', 'gospel-music-mastery' ),
					$name ? $name : __( 'Student', 'gospel-music-mastery' )
				),
				'description' => __( 'New student registration on the platform.', 'gospel-music-mastery' ),
				'time'        => human_time_diff( strtotime( $row['created_at'] ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'gospel-music-mastery' ),
				'url'         => gmm_get_page_link( 'admin_students' ),
				'sort'        => strtotime( $row['created_at'] ),
			);
		}
	}

	$c_rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, title, created_at FROM {$classes} ORDER BY created_at DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$limit
		),
		ARRAY_A
	);
	if ( is_array( $c_rows ) ) {
		foreach ( $c_rows as $row ) {
			$events[] = array(
				'type'        => 'class',
				'css'         => 'is-class',
				'icon'        => 'far fa-chalkboard',
				'title'       => sprintf(
					/* translators: %s: class title */
					__( 'New class submitted: %s', 'gospel-music-mastery' ),
					$row['title'] ? $row['title'] : __( 'Class', 'gospel-music-mastery' )
				),
				'description' => __( 'Awaiting admin review before it appears publicly.', 'gospel-music-mastery' ),
				'time'        => human_time_diff( strtotime( $row['created_at'] ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'gospel-music-mastery' ),
				'url'         => gmm_get_page_link( 'admin_classes' ),
				'sort'        => strtotime( $row['created_at'] ),
			);
		}
	}

	$p_rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, amount, created_at FROM {$payments}
			WHERE payment_status = %s AND payment_method <> %s
			ORDER BY created_at DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			'completed',
			'withdrawal',
			$limit
		),
		ARRAY_A
	);
	if ( is_array( $p_rows ) ) {
		foreach ( $p_rows as $row ) {
			$events[] = array(
				'type'        => 'payment',
				'css'         => 'is-payment',
				'icon'        => 'far fa-credit-card',
				'title'       => sprintf(
					/* translators: %s: money amount */
					__( 'Payment received: $%s', 'gospel-music-mastery' ),
					number_format_i18n( (float) $row['amount'], 2 )
				),
				'description' => __( 'Lesson booking payment recorded.', 'gospel-music-mastery' ),
				'time'        => human_time_diff( strtotime( $row['created_at'] ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'gospel-music-mastery' ),
				'url'         => gmm_get_page_link( 'admin_payments' ),
				'sort'        => strtotime( $row['created_at'] ),
			);
		}
	}

	$b_rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT id, booking_date, created_at FROM {$bookings} ORDER BY created_at DESC LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$limit
		),
		ARRAY_A
	);
	if ( is_array( $b_rows ) ) {
		foreach ( $b_rows as $row ) {
			$events[] = array(
				'type'        => 'booking',
				'css'         => 'is-booking',
				'icon'        => 'far fa-calendar-check',
				'title'       => sprintf(
					/* translators: %s: booking date */
					__( 'Booking created for %s', 'gospel-music-mastery' ),
					$row['booking_date'] ? $row['booking_date'] : __( 'a lesson', 'gospel-music-mastery' )
				),
				'description' => __( 'A new lesson booking was created.', 'gospel-music-mastery' ),
				'time'        => human_time_diff( strtotime( $row['created_at'] ), current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'gospel-music-mastery' ),
				'url'         => gmm_get_page_link( 'admin_bookings' ),
				'sort'        => strtotime( $row['created_at'] ),
			);
		}
	}

	usort(
		$events,
		static function ( $a, $b ) {
			return (int) $b['sort'] - (int) $a['sort'];
		}
	);

	$events = array_slice( $events, 0, $limit );
	set_transient( $cache_key, $events, 120 );
	return $events;
}
