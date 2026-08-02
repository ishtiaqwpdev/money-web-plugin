<?php
/**
 * Student dashboard data controller.
 *
 * Supplies real statistics, lessons, favourites, payments, and Chart.js
 * payloads for templates/student/dashboard.php without changing the frozen UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Student_Dashboard
 */
class GMM_Student_Dashboard {

	const CACHE_GROUP = 'gmm_student_dash';
	const CACHE_TTL   = 90;
	const NONCE_ACTION = 'gmm_nonce';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 25, 2 );
		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 35 );

		$loader->add_action( 'wp_ajax_gmm_student_dashboard_stats', $instance, 'ajax_dashboard_stats' );
		$loader->add_action( 'wp_ajax_gmm_student_dashboard_bookings', $instance, 'ajax_dashboard_bookings' );
		$loader->add_action( 'wp_ajax_gmm_student_dashboard_favourites', $instance, 'ajax_dashboard_favourites' );

		$loader->add_action( 'gmm_booking_confirmed', $instance, 'flush_on_booking_hook' );
		$loader->add_action( 'gmm_booking_completed', $instance, 'flush_on_booking_hook' );
		$loader->add_action( 'gmm_booking_cancelled', $instance, 'flush_on_booking_hook' );
		$loader->add_action( 'gmm_payment_completed', $instance, 'flush_on_payment_hook', 20, 2 );
		$loader->add_action( 'gmm_student_registered', $instance, 'flush_on_user', 10, 1 );
	}

	/**
	 * Inject dashboard payload into [gmm_student_dashboard].
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Shortcode.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_student_dashboard' !== $tag ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * Whether current user may view the student dashboard.
	 *
	 * @param int $user_id Optional user ID.
	 * @return bool
	 */
	public static function user_can_view( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id || ! is_user_logged_in() ) {
			return false;
		}
		if ( function_exists( 'gmm_student_can_access_dashboard' ) ) {
			return gmm_student_can_access_dashboard( $user_id );
		}
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		return function_exists( 'gmm_is_student' ) && gmm_is_student( $user_id )
			&& get_current_user_id() === $user_id;
	}

	/**
	 * Full template variable set.
	 *
	 * @param int $user_id Optional WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_template_vars( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		$logout = function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) );

		if ( ! self::user_can_view( $user_id ) ) {
			return array(
				'gmm_student_denied'  => true,
				'stats'               => self::empty_stats(),
				'profile_summary'     => array(),
				'upcoming_lessons'    => array(),
				'recent_lessons'      => array(),
				'favourite_teachers'  => array(),
				'payment_summary'     => self::empty_payment_summary(),
				'activity'            => array(),
				'completion'          => array( 'percent' => 0, 'items' => array() ),
				'charts'              => array(),
				'dashboard_data'      => self::empty_stats(),
				'logout_url'          => $logout,
				'links'               => self::quick_links(),
			);
		}

		$stats    = self::get_statistics( $user_id );
		$profile  = self::get_profile_summary( $user_id );
		$payments = self::get_payment_summary( $user_id );
		$charts   = self::get_chart_data( $user_id );

		return array(
			'gmm_student_denied' => false,
			'stats'              => $stats,
			'profile_summary'    => $profile,
			'upcoming_lessons'   => self::get_upcoming_lessons( $user_id, 5 ),
			'recent_lessons'     => self::get_recent_lessons( $user_id, 5 ),
			'favourite_teachers' => self::get_favourite_teachers( $user_id, 6 ),
			'payment_summary'    => $payments,
			'activity'           => self::get_recent_activity( $user_id, 6 ),
			'completion'         => self::get_profile_completion( $user_id ),
			'charts'             => $charts,
			'dashboard_data'     => $stats,
			'logout_url'         => $logout,
			'links'              => self::quick_links(),
			'user_name'          => isset( $profile['name'] ) ? $profile['name'] : '',
			'user_first_name'    => isset( $profile['first_name'] ) && $profile['first_name']
				? $profile['first_name']
				: ( isset( $profile['name'] ) ? $profile['name'] : '' ),
		);
	}

	/**
	 * Dashboard statistics (own student only).
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_statistics( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! self::user_can_view( $user_id ) ) {
			return self::empty_stats();
		}

		$cache_key = 'stats_' . $user_id;
		$cached    = self::cache_get( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$student_id = class_exists( 'GMM_Student' ) ? GMM_Student::get_student_id( $user_id ) : 0;
		if ( ! $student_id ) {
			return self::empty_stats();
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$payments = GMM_Database::table( 'payments' );
		$fav      = GMM_Database::table( 'favourites' );
		$today    = current_time( 'Y-m-d' );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					COUNT(*) AS total_lessons,
					SUM(CASE WHEN booking_date >= %s AND booking_status IN ('pending','confirmed','upcoming') THEN 1 ELSE 0 END) AS upcoming_lessons,
					SUM(CASE WHEN booking_status = 'completed' THEN 1 ELSE 0 END) AS completed_lessons,
					COUNT(DISTINCT CASE WHEN class_id > 0 THEN class_id ELSE NULL END) AS enrolled_classes
				FROM {$bookings}
				WHERE student_id = %d",
				$today,
				$student_id
			),
			ARRAY_A
		);

		$favourites = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$fav} WHERE student_id = %d", $student_id )
		);

		$total_payments = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$payments}
				WHERE student_id = %d
				AND payment_status IN ('completed','paid','success')
				AND payment_method <> %s",
				$student_id,
				'withdrawal'
			)
		);

		$pending_payments = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$payments}
				WHERE student_id = %d
				AND payment_status = %s
				AND payment_method <> %s",
				$student_id,
				'pending',
				'withdrawal'
			)
		);

		$stats = array(
			'total_lessons'      => isset( $row['total_lessons'] ) ? (int) $row['total_lessons'] : 0,
			'enrolled_classes'   => isset( $row['enrolled_classes'] ) ? (int) $row['enrolled_classes'] : 0,
			'upcoming_lessons'   => isset( $row['upcoming_lessons'] ) ? (int) $row['upcoming_lessons'] : 0,
			'completed_lessons'  => isset( $row['completed_lessons'] ) ? (int) $row['completed_lessons'] : 0,
			'favourite_teachers' => $favourites,
			'total_payments'     => round( $total_payments, 2 ),
			'pending_payments'   => round( $pending_payments, 2 ),
		);

		// Prefer total lessons for the first card when enrolled_classes is zero.
		if ( $stats['enrolled_classes'] < 1 && $stats['total_lessons'] > 0 ) {
			$stats['enrolled_classes'] = $stats['total_lessons'];
		}

		self::cache_set( $cache_key, $stats );
		return $stats;
	}

	/**
	 * Upcoming lessons for current student.
	 *
	 * @param int $user_id WP user ID.
	 * @param int $limit   Max rows.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_upcoming_lessons( $user_id = 0, $limit = 5 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$limit   = min( max( 1, absint( $limit ) ), 50 );

		if ( ! self::user_can_view( $user_id ) ) {
			return array();
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return array();
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$teachers = GMM_Database::table( 'teachers' );
		$classes  = GMM_Database::table( 'classes' );
		$today    = current_time( 'Y-m-d' );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT b.*,
					t.first_name AS teacher_first_name,
					t.last_name AS teacher_last_name,
					t.profile_image AS teacher_image,
					t.specialization AS teacher_specialization,
					c.title AS class_title,
					c.price AS class_price
				FROM {$bookings} b
				LEFT JOIN {$teachers} t ON t.id = b.teacher_id
				LEFT JOIN {$classes} c ON c.id = b.class_id
				WHERE b.student_id = %d
				AND b.booking_date >= %s
				AND b.booking_status IN ('pending','confirmed','upcoming')
				ORDER BY b.booking_date ASC, b.booking_time ASC
				LIMIT %d",
				$student_id,
				$today,
				$limit
			),
			ARRAY_A
		);

		if ( ! is_array( $rows ) ) {
			return array();
		}

		$out = array();
		foreach ( $rows as $row ) {
			$out[] = self::format_lesson_row( $row );
		}
		return $out;
	}

	/**
	 * Recent completed lessons.
	 *
	 * @param int $user_id WP user ID.
	 * @param int $limit   Max rows.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_recent_lessons( $user_id = 0, $limit = 5 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$limit   = min( max( 1, absint( $limit ) ), 50 );

		if ( ! self::user_can_view( $user_id ) ) {
			return array();
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return array();
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$teachers = GMM_Database::table( 'teachers' );
		$classes  = GMM_Database::table( 'classes' );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT b.*,
					t.first_name AS teacher_first_name,
					t.last_name AS teacher_last_name,
					t.profile_image AS teacher_image,
					t.specialization AS teacher_specialization,
					c.title AS class_title
				FROM {$bookings} b
				LEFT JOIN {$teachers} t ON t.id = b.teacher_id
				LEFT JOIN {$classes} c ON c.id = b.class_id
				WHERE b.student_id = %d
				AND b.booking_status = %s
				ORDER BY b.booking_date DESC, b.booking_time DESC
				LIMIT %d",
				$student_id,
				'completed',
				$limit
			),
			ARRAY_A
		);

		if ( ! is_array( $rows ) ) {
			return array();
		}

		$out = array();
		foreach ( $rows as $row ) {
			$out[] = self::format_lesson_row( $row );
		}
		return $out;
	}

	/**
	 * Favourite teachers for dashboard cards.
	 *
	 * @param int $user_id WP user ID.
	 * @param int $limit   Max rows.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_favourite_teachers( $user_id = 0, $limit = 6 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$limit   = min( max( 1, absint( $limit ) ), 50 );

		if ( ! self::user_can_view( $user_id ) ) {
			return array();
		}

		$rows = class_exists( 'GMM_Favourites' )
			? GMM_Favourites::get_favourite_teachers( $user_id )
			: array();

		if ( ! is_array( $rows ) || empty( $rows ) ) {
			return array();
		}

		$out   = array();
		$count = 0;
		foreach ( $rows as $row ) {
			if ( $count >= $limit ) {
				break;
			}
			$out[] = self::format_teacher_card( $row );
			$count++;
		}
		return $out;
	}

	/**
	 * Payment summary for student.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_payment_summary( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! self::user_can_view( $user_id ) ) {
			return self::empty_payment_summary();
		}

		$stats = self::get_statistics( $user_id );
		$recent = array();

		if ( class_exists( 'GMM_Student_Payments' ) ) {
			$history = GMM_Student_Payments::get_payment_rows( $user_id, array( 'limit' => 5 ) );
			foreach ( (array) $history as $pay ) {
				$recent[] = array(
					'id'           => isset( $pay['id'] ) ? absint( $pay['id'] ) : 0,
					'amount'       => isset( $pay['amount'] ) ? round( (float) $pay['amount'], 2 ) : 0.0,
					'amount_label' => isset( $pay['amount_label'] ) ? (string) $pay['amount_label'] : '',
					'status'       => isset( $pay['status'] ) ? (string) $pay['status'] : '',
					'status_label' => isset( $pay['status_label'] ) ? (string) $pay['status_label'] : '',
					'method'       => isset( $pay['payment_method'] ) ? (string) $pay['payment_method'] : '',
					'teacher_name' => isset( $pay['teacher_name'] ) ? (string) $pay['teacher_name'] : '',
					'class_name'   => isset( $pay['class_name'] ) ? (string) $pay['class_name'] : '',
					'created_at'   => isset( $pay['created_at'] ) ? (string) $pay['created_at'] : '',
					'date_label'   => isset( $pay['date_label'] ) ? (string) $pay['date_label'] : '',
				);
			}

			$pstats = GMM_Student_Payments::get_payment_stats( $user_id );
			return array(
				'total_paid'          => isset( $pstats['total_spent'] ) ? (float) $pstats['total_spent'] : ( isset( $stats['total_payments'] ) ? (float) $stats['total_payments'] : 0.0 ),
				'pending_payments'    => isset( $pstats['pending_amount'] ) ? (float) $pstats['pending_amount'] : ( isset( $stats['pending_payments'] ) ? (float) $stats['pending_payments'] : 0.0 ),
				'completed_count'     => isset( $pstats['completed_count'] ) ? (int) $pstats['completed_count'] : 0,
				'pending_count'       => isset( $pstats['pending_count'] ) ? (int) $pstats['pending_count'] : 0,
				'recent_transactions' => $recent,
			);
		}

		return array(
			'total_paid'          => isset( $stats['total_payments'] ) ? (float) $stats['total_payments'] : 0.0,
			'pending_payments'    => isset( $stats['pending_payments'] ) ? (float) $stats['pending_payments'] : 0.0,
			'recent_transactions' => $recent,
		);
	}

	/**
	 * Chart.js compatible payloads.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_chart_data( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! self::user_can_view( $user_id ) ) {
			return array();
		}

		$cache_key = 'charts_' . $user_id;
		$cached    = self::cache_get( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return array();
		}

		$year   = (int) current_time( 'Y' );
		$labels = array( 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );
		$monthly = array_fill( 0, 12, 0 );
		$weekdays = array( 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun' );
		$practice = array_fill( 0, 7, 0.0 );

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );

		$month_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT MONTH(booking_date) AS m, COUNT(*) AS total
				FROM {$bookings}
				WHERE student_id = %d
				AND YEAR(booking_date) = %d
				AND booking_status IN ('completed','confirmed','upcoming','pending')
				GROUP BY MONTH(booking_date)",
				$student_id,
				$year
			),
			ARRAY_A
		);
		if ( is_array( $month_rows ) ) {
			foreach ( $month_rows as $row ) {
				$m = absint( $row['m'] );
				if ( $m >= 1 && $m <= 12 ) {
					$monthly[ $m - 1 ] = (int) $row['total'];
				}
			}
		}

		// Practice hours approx: duration minutes this week / 60 by weekday.
		$week_start = gmdate( 'Y-m-d', strtotime( 'monday this week', current_time( 'timestamp' ) ) );
		$week_end   = gmdate( 'Y-m-d', strtotime( 'sunday this week', current_time( 'timestamp' ) ) );
		$week_rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DAYOFWEEK(booking_date) AS dow, COALESCE(SUM(duration),0) AS mins
				FROM {$bookings}
				WHERE student_id = %d
				AND booking_date >= %s AND booking_date <= %s
				AND booking_status IN ('completed','confirmed','upcoming')
				GROUP BY DAYOFWEEK(booking_date)",
				$student_id,
				$week_start,
				$week_end
			),
			ARRAY_A
		);
		// MySQL DAYOFWEEK: 1=Sunday … 7=Saturday. Map to Mon–Sun index.
		if ( is_array( $week_rows ) ) {
			foreach ( $week_rows as $row ) {
				$dow = absint( $row['dow'] ); // 1 Sun … 7 Sat
				$idx = ( 1 === $dow ) ? 6 : ( $dow - 2 ); // Mon=0 … Sun=6
				if ( $idx >= 0 && $idx <= 6 ) {
					$practice[ $idx ] = round( ( (float) $row['mins'] ) / 60, 1 );
				}
			}
		}

		$stats     = self::get_statistics( $user_id );
		$completed = isset( $stats['completed_lessons'] ) ? (int) $stats['completed_lessons'] : 0;
		$upcoming  = isset( $stats['upcoming_lessons'] ) ? (int) $stats['upcoming_lessons'] : 0;
		$remaining = max( 0, ( isset( $stats['total_lessons'] ) ? (int) $stats['total_lessons'] : 0 ) - $completed - $upcoming );

		$charts = array(
			'learning' => array(
				'labels'   => $labels,
				'datasets' => array(
					array(
						'label' => __( 'Learning Activity', 'gospel-music-mastery' ),
						'data'  => $monthly,
					),
				),
			),
			'lessons'  => array(
				'labels'   => array(
					__( 'Completed', 'gospel-music-mastery' ),
					__( 'Upcoming', 'gospel-music-mastery' ),
					__( 'Remaining', 'gospel-music-mastery' ),
				),
				'datasets' => array(
					array(
						'label' => __( 'Lessons', 'gospel-music-mastery' ),
						'data'  => array( $completed, $upcoming, $remaining ),
					),
				),
			),
			'practice' => array(
				'labels'   => $weekdays,
				'datasets' => array(
					array(
						'label' => __( 'Practice Hours', 'gospel-music-mastery' ),
						'data'  => $practice,
					),
				),
			),
			'monthly'  => array(
				'labels'   => $labels,
				'datasets' => array(
					array(
						'label' => __( 'Monthly Lessons', 'gospel-music-mastery' ),
						'data'  => $monthly,
					),
				),
			),
		);

		self::cache_set( $cache_key, $charts );
		return $charts;
	}

	/**
	 * Profile summary for header.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_profile_summary( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! self::user_can_view( $user_id ) ) {
			return array();
		}

		$profile = class_exists( 'GMM_Student' ) ? GMM_Student::get_profile( $user_id ) : null;
		if ( ! is_array( $profile ) ) {
			$user = get_userdata( $user_id );
			return array(
				'name'           => $user ? $user->display_name : __( 'Student', 'gospel-music-mastery' ),
				'first_name'     => $user ? $user->first_name : '',
				'learning_level' => '',
				'learning_goals' => '',
				'instruments'    => '',
				'image_url'      => function_exists( 'gmm_design_asset_url' )
					? gmm_design_asset_url( 'assets/img/team/02.jpg' )
					: '',
			);
		}

		$image = '';
		if ( ! empty( $profile['profile_image'] ) && function_exists( 'gmm_get_media_url' ) ) {
			$image = gmm_get_media_url( $profile['profile_image'], 'medium' );
		}
		if ( ! $image ) {
			$image = function_exists( 'gmm_design_asset_url' )
				? gmm_design_asset_url( 'assets/img/team/02.jpg' )
				: '';
		}

		$first = isset( $profile['first_name'] ) ? (string) $profile['first_name'] : '';
		$last  = isset( $profile['last_name'] ) ? (string) $profile['last_name'] : '';
		$name  = trim( $first . ' ' . $last );
		if ( '' === $name ) {
			$user = get_userdata( $user_id );
			$name = $user ? $user->display_name : __( 'Student', 'gospel-music-mastery' );
		}

		$instruments = isset( $profile['preferred_instruments'] ) ? (string) $profile['preferred_instruments'] : '';
		$level       = isset( $profile['learning_level'] ) ? (string) $profile['learning_level'] : '';
		$goals       = isset( $profile['learning_goals'] ) ? (string) $profile['learning_goals'] : '';

		return array(
			'id'             => isset( $profile['id'] ) ? absint( $profile['id'] ) : 0,
			'user_id'        => $user_id,
			'first_name'     => $first,
			'last_name'      => $last,
			'name'           => $name,
			'email'          => isset( $profile['email'] ) ? (string) $profile['email'] : '',
			'learning_level' => $level,
			'learning_goals' => $goals,
			'instruments'    => $instruments,
			'image_url'      => $image,
			'status'         => isset( $profile['status'] ) ? (string) $profile['status'] : '',
		);
	}

	/**
	 * Profile completion checklist.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_profile_completion( $user_id = 0 ) {
		if ( class_exists( 'GMM_Student_Profile' ) ) {
			return GMM_Student_Profile::get_profile_completion( $user_id );
		}

		$profile = self::get_profile_summary( $user_id );
		$items   = array(
			array(
				'label' => __( 'Personal Information', 'gospel-music-mastery' ),
				'done'  => ! empty( $profile['first_name'] ) && ! empty( $profile['last_name'] ) && ! empty( $profile['email'] ),
			),
			array(
				'label' => __( 'Profile Photo', 'gospel-music-mastery' ),
				'done'  => ! empty( $profile['image_url'] ) && false === strpos( (string) $profile['image_url'], 'team/02.jpg' ),
			),
			array(
				'label' => __( 'Learning Goals', 'gospel-music-mastery' ),
				'done'  => ! empty( $profile['learning_goals'] ),
			),
			array(
				'label' => __( 'Preferred Instruments', 'gospel-music-mastery' ),
				'done'  => ! empty( $profile['instruments'] ),
			),
		);

		$done  = 0;
		$total = count( $items );
		foreach ( $items as $item ) {
			if ( ! empty( $item['done'] ) ) {
				$done++;
			}
		}

		return array(
			'percent' => $total ? (int) round( ( $done / $total ) * 100 ) : 0,
			'items'   => $items,
			'done'    => $done,
			'total'   => $total,
		);
	}

	/**
	 * Recent activity feed (lessons + payments + favourites).
	 *
	 * @param int $user_id WP user ID.
	 * @param int $limit   Max items.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_recent_activity( $user_id = 0, $limit = 6 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$limit   = min( max( 1, absint( $limit ) ), 20 );
		if ( ! self::user_can_view( $user_id ) ) {
			return array();
		}

		$items = array();

		foreach ( self::get_upcoming_lessons( $user_id, 3 ) as $lesson ) {
			$items[] = array(
				'icon'    => 'far fa-calendar-plus',
				'title'   => sprintf(
					/* translators: %s: class name */
					__( 'Upcoming: %s', 'gospel-music-mastery' ),
					isset( $lesson['class_name'] ) ? $lesson['class_name'] : __( 'Lesson', 'gospel-music-mastery' )
				),
				'subtitle'=> isset( $lesson['date_label'] ) ? $lesson['date_label'] : '',
				'sort'    => isset( $lesson['booking_date'] ) ? (string) $lesson['booking_date'] : '',
			);
		}

		foreach ( self::get_recent_lessons( $user_id, 3 ) as $lesson ) {
			$items[] = array(
				'icon'    => 'far fa-circle-check',
				'title'   => sprintf(
					/* translators: %s: class name */
					__( 'Completed %s', 'gospel-music-mastery' ),
					isset( $lesson['class_name'] ) ? $lesson['class_name'] : __( 'lesson', 'gospel-music-mastery' )
				),
				'subtitle'=> isset( $lesson['date_label'] ) ? $lesson['date_label'] : '',
				'sort'    => isset( $lesson['booking_date'] ) ? (string) $lesson['booking_date'] : '',
			);
		}

		$payments = self::get_payment_summary( $user_id );
		foreach ( (array) $payments['recent_transactions'] as $pay ) {
			$subtitle_parts = array_filter(
				array(
					isset( $pay['amount_label'] ) ? (string) $pay['amount_label'] : '',
					isset( $pay['teacher_name'] ) ? (string) $pay['teacher_name'] : '',
					isset( $pay['status_label'] ) ? (string) $pay['status_label'] : '',
				)
			);
			$items[] = array(
				'icon'     => 'far fa-credit-card',
				'title'    => ! empty( $pay['class_name'] )
					? sprintf(
						/* translators: %s: class name */
						__( 'Payment · %s', 'gospel-music-mastery' ),
						(string) $pay['class_name']
					)
					: __( 'Payment', 'gospel-music-mastery' ),
				'subtitle' => implode( ' · ', $subtitle_parts ),
				'sort'     => isset( $pay['created_at'] ) ? (string) $pay['created_at'] : '',
			);
		}

		usort(
			$items,
			static function ( $a, $b ) {
				return strcmp( (string) $b['sort'], (string) $a['sort'] );
			}
		);

		return array_slice( $items, 0, $limit );
	}

	/**
	 * Quick action URLs.
	 *
	 * @return array<string, string>
	 */
	public static function quick_links() {
		$link = static function ( $key ) {
			return function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( $key ) : '';
		};

		$teachers = $link( 'student_favourites' );
		$book     = $link( 'student_bookings' );
		if ( ! $book ) {
			$book = $link( 'student_lessons' );
		}

		return array(
			'teachers'   => $teachers ? $teachers : home_url( '/' ),
			'book'       => $book ? $book : home_url( '/' ),
			'lessons'    => $link( 'student_lessons' ),
			'bookings'   => $link( 'student_bookings' ),
			'payments'   => $link( 'student_payments' ),
			'profile'    => $link( 'student_profile' ),
			'favourites' => $link( 'student_favourites' ),
			'settings'   => $link( 'student_settings' ),
			'dashboard'  => $link( 'student_dashboard' ),
		);
	}

	/**
	 * Format booking row for UI.
	 *
	 * @param array<string, mixed> $row DB row.
	 * @return array<string, mixed>
	 */
	private static function format_lesson_row( $row ) {
		$first = isset( $row['teacher_first_name'] ) ? (string) $row['teacher_first_name'] : '';
		$last  = isset( $row['teacher_last_name'] ) ? (string) $row['teacher_last_name'] : '';
		$name  = trim( $first . ' ' . $last );
		if ( '' === $name ) {
			$name = __( 'Teacher', 'gospel-music-mastery' );
		}

		$image = '';
		if ( ! empty( $row['teacher_image'] ) && function_exists( 'gmm_get_media_url' ) ) {
			$image = gmm_get_media_url( $row['teacher_image'], 'thumbnail' );
		}
		if ( ! $image ) {
			$image = function_exists( 'gmm_design_asset_url' )
				? gmm_design_asset_url( 'assets/img/team/01.jpg' )
				: '';
		}

		$status = isset( $row['booking_status'] ) ? sanitize_key( $row['booking_status'] ) : '';
		$date   = isset( $row['booking_date'] ) ? (string) $row['booking_date'] : '';
		$time   = isset( $row['booking_time'] ) ? (string) $row['booking_time'] : '';

		$badge = 'is-pending';
		$label = ucfirst( $status ? $status : 'pending' );
		if ( 'confirmed' === $status || 'upcoming' === $status ) {
			$badge = 'is-confirmed';
			$label = 'confirmed' === $status ? __( 'Confirmed', 'gospel-music-mastery' ) : __( 'Scheduled', 'gospel-music-mastery' );
			if ( 'upcoming' === $status ) {
				$badge = 'is-scheduled';
			}
		} elseif ( 'completed' === $status ) {
			$badge = 'is-confirmed';
			$label = __( 'Completed', 'gospel-music-mastery' );
		} elseif ( 'pending' === $status ) {
			$badge = 'is-pending';
			$label = __( 'Pending', 'gospel-music-mastery' );
		}

		$date_label = $date ? date_i18n( 'l', strtotime( $date ) ) : '';
		$time_label = $time ? date_i18n( get_option( 'time_format' ), strtotime( $time ) ) : '';

		return array(
			'id'           => isset( $row['id'] ) ? absint( $row['id'] ) : 0,
			'teacher_name' => $name,
			'class_name'   => ! empty( $row['class_title'] ) ? (string) $row['class_title'] : __( 'Lesson', 'gospel-music-mastery' ),
			'booking_date' => $date,
			'booking_time' => $time,
			'date_label'   => $date_label,
			'time_label'   => $time_label,
			'status'       => $status,
			'status_label' => $label,
			'badge_class'  => $badge,
			'teacher_image'=> $image,
			'specialization'=> isset( $row['teacher_specialization'] ) ? (string) $row['teacher_specialization'] : '',
		);
	}

	/**
	 * Format favourite teacher card.
	 *
	 * @param array<string, mixed> $row Teacher + favourite row.
	 * @return array<string, mixed>
	 */
	private static function format_teacher_card( $row ) {
		$first = isset( $row['first_name'] ) ? (string) $row['first_name'] : '';
		$last  = isset( $row['last_name'] ) ? (string) $row['last_name'] : '';
		$name  = trim( $first . ' ' . $last );
		if ( '' === $name ) {
			$name = __( 'Teacher', 'gospel-music-mastery' );
		}

		$image = '';
		if ( ! empty( $row['profile_image'] ) && function_exists( 'gmm_get_media_url' ) ) {
			$image = gmm_get_media_url( $row['profile_image'], 'medium' );
		}
		if ( ! $image ) {
			$image = function_exists( 'gmm_design_asset_url' )
				? gmm_design_asset_url( 'assets/img/team/01.jpg' )
				: '';
		}

		$rating = isset( $row['rating'] ) ? (float) $row['rating'] : 0.0;
		$stars  = '';
		$full   = (int) round( $rating );
		for ( $i = 1; $i <= 5; $i++ ) {
			$stars .= ( $i <= $full ) ? '★' : '☆';
		}

		$profile_url = function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'student_favourites' ) : '';

		return array(
			'id'             => isset( $row['id'] ) ? absint( $row['id'] ) : 0,
			'name'           => $name,
			'specialization' => ! empty( $row['specialization'] )
				? (string) $row['specialization']
				: __( 'Gospel Music Instructor', 'gospel-music-mastery' ),
			'rating'         => $rating,
			'rating_stars'   => $stars,
			'image_url'      => $image,
			'price_label'    => '',
			'profile_url'    => $profile_url,
		);
	}

	/**
	 * Enqueue assets on student dashboard.
	 *
	 * @return void
	 */
	public function maybe_enqueue_assets() {
		if ( ! self::user_can_view() ) {
			return;
		}
		if ( ! class_exists( 'GMM_Assets' ) || ! GMM_Assets::is_gmm_page() ) {
			return;
		}

		$post    = get_queried_object();
		$content = ( $post instanceof WP_Post ) ? (string) $post->post_content : '';
		if ( ! has_shortcode( $content, 'gmm_student_dashboard' ) && false === strpos( $content, 'gmm_student_dashboard' ) ) {
			return;
		}

		self::enqueue_dashboard_assets();
	}

	/**
	 * Enqueue Chart.js override script with localized data.
	 *
	 * @return void
	 */
	public static function enqueue_dashboard_assets() {
		$version = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';
		$js      = GMM_URL . 'assets/js/';

		if ( ! wp_script_is( 'gmm-chartjs', 'registered' ) ) {
			wp_register_script(
				'gmm-chartjs',
				'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
				array(),
				'4.4.1',
				true
			);
		}
		wp_enqueue_script( 'gmm-chartjs' );

		if ( ! wp_script_is( 'gmm-dashboard-charts', 'registered' ) ) {
			wp_register_script(
				'gmm-dashboard-charts',
				$js . 'dashboard-charts.js',
				array( 'gmm-chartjs' ),
				$version,
				true
			);
		}
		wp_enqueue_script( 'gmm-dashboard-charts' );

		wp_enqueue_script(
			'gmm-student-dashboard',
			$js . 'gmm-student-dashboard.js',
			array( 'gmm-core-script', 'gmm-dashboard-charts' ),
			$version,
			true
		);

		$vars = self::get_template_vars();
		wp_localize_script(
			'gmm-student-dashboard',
			'GMM_STUDENT_DASH',
			array(
				'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'charts'  => isset( $vars['charts'] ) ? $vars['charts'] : array(),
				'stats'   => isset( $vars['stats'] ) ? $vars['stats'] : array(),
				'actions' => array(
					'stats'      => 'gmm_student_dashboard_stats',
					'bookings'   => 'gmm_student_dashboard_bookings',
					'favourites' => 'gmm_student_dashboard_favourites',
				),
			)
		);
	}

	/**
	 * AJAX: refresh statistics + charts.
	 *
	 * @return void
	 */
	public function ajax_dashboard_stats() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );
		if ( ! self::user_can_view() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'gospel-music-mastery' ) ), 403 );
		}
		$user_id = get_current_user_id();
		self::flush_cache( $user_id );
		wp_send_json_success(
			array(
				'stats'   => self::get_statistics( $user_id ),
				'charts'  => self::get_chart_data( $user_id ),
				'message' => __( 'Dashboard statistics updated.', 'gospel-music-mastery' ),
			)
		);
	}

	/**
	 * AJAX: refresh bookings lists.
	 *
	 * @return void
	 */
	public function ajax_dashboard_bookings() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );
		if ( ! self::user_can_view() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'gospel-music-mastery' ) ), 403 );
		}
		$user_id = get_current_user_id();
		self::flush_cache( $user_id );
		wp_send_json_success(
			array(
				'upcoming_lessons' => self::get_upcoming_lessons( $user_id, 5 ),
				'recent_lessons'   => self::get_recent_lessons( $user_id, 5 ),
				'stats'            => self::get_statistics( $user_id ),
			)
		);
	}

	/**
	 * AJAX: refresh favourites.
	 *
	 * @return void
	 */
	public function ajax_dashboard_favourites() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );
		if ( ! self::user_can_view() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'gospel-music-mastery' ) ), 403 );
		}
		$user_id = get_current_user_id();
		self::flush_cache( $user_id );
		wp_send_json_success(
			array(
				'favourite_teachers' => self::get_favourite_teachers( $user_id, 6 ),
				'stats'              => self::get_statistics( $user_id ),
			)
		);
	}

	/**
	 * Flush cache on booking hooks.
	 *
	 * @return void
	 */
	public function flush_on_booking_hook() {
		self::flush_cache( get_current_user_id() );
	}

	/**
	 * Flush cache on payment.
	 *
	 * @param int                  $payment_id Payment ID.
	 * @param array<string, mixed> $row        Payment row.
	 * @return void
	 */
	public function flush_on_payment_hook( $payment_id, $row = array() ) {
		unset( $payment_id );
		$user_id = 0;
		if ( is_array( $row ) && ! empty( $row['student_id'] ) && class_exists( 'GMM_Student' ) ) {
			global $wpdb;
			$table   = GMM_Database::table( 'students' );
			$user_id = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT user_id FROM {$table} WHERE id = %d", absint( $row['student_id'] ) )
			);
		}
		self::flush_cache( $user_id );
	}

	/**
	 * Flush on student user event.
	 *
	 * @param int $user_id User ID.
	 * @return void
	 */
	public function flush_on_user( $user_id ) {
		self::flush_cache( absint( $user_id ) );
	}

	/**
	 * Flush dashboard cache.
	 *
	 * @param int $user_id WP user ID.
	 * @return void
	 */
	public static function flush_cache( $user_id = 0 ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( ! $user_id ) {
			return;
		}
		delete_transient( self::CACHE_GROUP . '_stats_' . $user_id );
		delete_transient( self::CACHE_GROUP . '_charts_' . $user_id );
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function empty_stats() {
		return array(
			'total_lessons'      => 0,
			'enrolled_classes'   => 0,
			'upcoming_lessons'   => 0,
			'completed_lessons'  => 0,
			'favourite_teachers' => 0,
			'total_payments'     => 0.0,
			'pending_payments'   => 0.0,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function empty_payment_summary() {
		return array(
			'total_paid'          => 0.0,
			'pending_payments'    => 0.0,
			'completed_count'     => 0,
			'pending_count'       => 0,
			'recent_transactions' => array(),
		);
	}

	/**
	 * @param string $key Cache key suffix.
	 * @return mixed
	 */
	private static function cache_get( $key ) {
		return get_transient( self::CACHE_GROUP . '_' . sanitize_key( $key ) );
	}

	/**
	 * @param string $key  Cache key suffix.
	 * @param mixed  $data Data.
	 * @return void
	 */
	private static function cache_set( $key, $data ) {
		set_transient( self::CACHE_GROUP . '_' . sanitize_key( $key ), $data, self::CACHE_TTL );
	}
}
