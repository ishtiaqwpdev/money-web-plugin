<?php
/**
 * Teacher dashboard data controller.
 *
 * Supplies real statistics, lessons, earnings, and Chart.js payloads
 * for templates/teacher/dashboard.php without changing the frozen UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Teacher_Dashboard
 */
class GMM_Teacher_Dashboard {

	const CACHE_GROUP = 'gmm_teacher_dash';
	const CACHE_TTL   = 90;

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 20, 2 );
		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 35 );
		$loader->add_action( 'wp_ajax_gmm_teacher_dashboard_stats', $instance, 'ajax_dashboard_stats' );
		$loader->add_action( 'wp_ajax_gmm_teacher_dashboard_bookings', $instance, 'ajax_dashboard_bookings' );
		$loader->add_action( 'wp_ajax_gmm_teacher_dashboard_earnings', $instance, 'ajax_dashboard_earnings' );

		$loader->add_action( 'gmm_teacher_approved', $instance, 'flush_on_teacher_change', 10, 2 );
		$loader->add_action( 'gmm_booking_confirmed', $instance, 'flush_on_booking_hook' );
		$loader->add_action( 'gmm_booking_completed', $instance, 'flush_on_booking_hook' );
		$loader->add_action( 'gmm_booking_cancelled', $instance, 'flush_on_booking_hook' );
	}

	/**
	 * Inject dashboard payload into [gmm_teacher_dashboard].
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Shortcode.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_teacher_dashboard' !== $tag ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * Whether current user may view the teacher dashboard.
	 *
	 * @param int $user_id Optional user ID.
	 * @return bool
	 */
	public static function user_can_view( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id || ! is_user_logged_in() ) {
			return false;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		if ( ! function_exists( 'gmm_is_teacher' ) || ! gmm_is_teacher( $user_id ) ) {
			return false;
		}
		if ( get_current_user_id() !== $user_id ) {
			return false;
		}
		return class_exists( 'GMM_Teacher_Auth' ) ? GMM_Teacher_Auth::is_approved( $user_id ) : true;
	}

	/**
	 * Full template variable set.
	 *
	 * @param int $user_id Optional WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_template_vars( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! self::user_can_view( $user_id ) ) {
			$pending = function_exists( 'gmm_is_teacher' ) && gmm_is_teacher( $user_id )
				&& class_exists( 'GMM_Teacher_Auth' )
				&& ! GMM_Teacher_Auth::is_approved( $user_id );

			return array(
				'gmm_teacher_denied'  => true,
				'gmm_teacher_pending' => $pending,
				'stats'               => self::empty_stats(),
				'profile_summary'     => array(),
				'upcoming_lessons'    => array(),
				'recent_bookings'     => array(),
				'recent_classes'      => array(),
				'earnings_summary'    => self::empty_earnings(),
				'charts'              => array(),
				'dashboard_data'      => self::empty_stats(),
				'logout_url'          => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
				'links'               => self::quick_links(),
			);
		}

		$stats    = self::get_statistics( $user_id );
		$profile  = self::get_profile_summary( $user_id );
		$earnings = self::get_earnings_summary( $user_id );
		$charts   = self::get_chart_data( $user_id );

		return array(
			'gmm_teacher_denied'  => false,
			'gmm_teacher_pending' => false,
			'stats'               => $stats,
			'profile_summary'     => $profile,
			'upcoming_lessons'    => self::get_upcoming_lessons( $user_id, 5 ),
			'recent_bookings'     => self::get_recent_bookings( $user_id, 8 ),
			'recent_classes'      => self::get_recent_classes( $user_id, 5 ),
			'earnings_summary'    => $earnings,
			'charts'              => $charts,
			'dashboard_data'      => $stats,
			'completion'          => self::get_profile_completion( $user_id, $profile, $stats ),
			'logout_url'          => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
			'links'               => self::quick_links(),
			'user_name'           => isset( $profile['name'] ) ? $profile['name'] : '',
			'user_first_name'     => isset( $profile['first_name'] ) ? $profile['first_name'] : '',
		);
	}

	/**
	 * Cached dashboard statistics.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, int|float>
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

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return self::empty_stats();
		}

		global $wpdb;
		$classes_t  = GMM_Database::table( 'classes' );
		$bookings_t = GMM_Database::table( 'bookings' );
		$payments_t = GMM_Database::table( 'payments' );
		$reviews_t  = GMM_Database::table( 'reviews' );
		$today      = current_time( 'Y-m-d' );

		$total_classes = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$classes_t} WHERE teacher_id = %d", $teacher_id )
		);

		$active_classes = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$classes_t}
				WHERE teacher_id = %d AND status IN ('published','approved','active')",
				$teacher_id
			)
		);

		$enrolled_classes = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT class_id) FROM {$bookings_t}
				WHERE teacher_id = %d AND class_id > 0
				AND booking_status NOT IN ('cancelled','rejected')",
				$teacher_id
			)
		);

		$total_students = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT student_id) FROM {$bookings_t}
				WHERE teacher_id = %d AND student_id > 0",
				$teacher_id
			)
		);

		$upcoming = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$bookings_t}
				WHERE teacher_id = %d
				AND booking_date >= %s
				AND booking_status IN ('pending','confirmed','upcoming','scheduled')",
				$teacher_id,
				$today
			)
		);

		$completed = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$bookings_t}
				WHERE teacher_id = %d AND booking_status = %s",
				$teacher_id,
				'completed'
			)
		);

		$gross = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$payments_t}
				WHERE teacher_id = %d
				AND payment_status IN ('completed','paid','success')
				AND payment_method <> %s",
				$teacher_id,
				'withdrawal'
			)
		);

		$total_earnings = $gross;
		if ( class_exists( 'GMM_Payment' ) ) {
			$split          = GMM_Payment::calculate_split( $gross );
			$total_earnings = isset( $split['teacher_earnings'] ) ? (float) $split['teacher_earnings'] : $gross;
		}

		$avg_rating = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT AVG(rating) FROM {$reviews_t}
				WHERE teacher_id = %d AND status IN ('approved','published','active')",
				$teacher_id
			)
		);
		if ( $avg_rating <= 0 ) {
			$teachers_t = GMM_Database::table( 'teachers' );
			$avg_rating = (float) $wpdb->get_var(
				$wpdb->prepare( "SELECT rating FROM {$teachers_t} WHERE id = %d LIMIT 1", $teacher_id )
			);
		}

		$stats = array(
			'total_classes'     => $total_classes,
			'active_classes'    => $active_classes,
			'enrolled_classes'  => $enrolled_classes ? $enrolled_classes : $total_classes,
			'total_students'    => $total_students,
			'upcoming_lessons'  => $upcoming,
			'completed_lessons' => $completed,
			'total_earnings'    => round( $total_earnings, 2 ),
			'average_rating'    => round( $avg_rating, 1 ),
		);

		self::cache_set( $cache_key, $stats );
		return $stats;
	}

	/**
	 * Upcoming lessons for the teacher (own bookings only).
	 *
	 * @param int $user_id WP user ID.
	 * @param int $limit   Max rows.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_upcoming_lessons( $user_id = 0, $limit = 5 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$limit   = max( 1, min( absint( $limit ), 50 ) );

		if ( ! self::user_can_view( $user_id ) ) {
			return array();
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$students = GMM_Database::table( 'students' );
		$classes  = GMM_Database::table( 'classes' );
		$today    = current_time( 'Y-m-d' );

		$sql = "SELECT b.id, b.booking_date, b.booking_time, b.booking_status, b.class_id, b.student_id,
				COALESCE(NULLIF(TRIM(CONCAT(s.first_name,' ',s.last_name)),''), s.email, 'Student') AS student_name,
				COALESCE(NULLIF(c.title,''), 'Class') AS class_name
			FROM {$bookings} b
			LEFT JOIN {$students} s ON s.id = b.student_id
			LEFT JOIN {$classes} c ON c.id = b.class_id
			WHERE b.teacher_id = %d
			AND b.booking_date >= %s
			AND b.booking_status IN ('pending','confirmed','upcoming','scheduled')
			ORDER BY b.booking_date ASC, b.booking_time ASC
			LIMIT %d";

		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $teacher_id, $today, $limit ), ARRAY_A );
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$out = array();
		foreach ( $rows as $row ) {
			$out[] = self::format_booking_row( $row );
		}
		return $out;
	}

	/**
	 * Recent bookings (pending / confirmed / completed).
	 *
	 * @param int $user_id WP user ID.
	 * @param int $limit   Max rows.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_recent_bookings( $user_id = 0, $limit = 8 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$limit   = max( 1, min( absint( $limit ), 50 ) );

		if ( ! self::user_can_view( $user_id ) ) {
			return array();
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$students = GMM_Database::table( 'students' );
		$classes  = GMM_Database::table( 'classes' );

		$sql = "SELECT b.id, b.booking_date, b.booking_time, b.booking_status, b.class_id, b.student_id,
				COALESCE(NULLIF(TRIM(CONCAT(s.first_name,' ',s.last_name)),''), s.email, 'Student') AS student_name,
				COALESCE(NULLIF(c.title,''), 'Class') AS class_name
			FROM {$bookings} b
			LEFT JOIN {$students} s ON s.id = b.student_id
			LEFT JOIN {$classes} c ON c.id = b.class_id
			WHERE b.teacher_id = %d
			AND b.booking_status IN ('pending','confirmed','completed','upcoming','scheduled')
			ORDER BY b.updated_at DESC, b.id DESC
			LIMIT %d";

		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $teacher_id, $limit ), ARRAY_A );
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$out = array();
		foreach ( $rows as $row ) {
			$out[] = self::format_booking_row( $row );
		}
		return $out;
	}

	/**
	 * Earnings summary from gmm_payments (teacher share).
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, float>
	 */
	public static function get_earnings_summary( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! self::user_can_view( $user_id ) ) {
			return self::empty_earnings();
		}

		if ( class_exists( 'GMM_Teacher_Earnings' ) ) {
			$raw = GMM_Teacher_Earnings::get_earnings( $user_id );
			return array(
				'total_earnings'    => isset( $raw['total_earnings'] ) ? (float) $raw['total_earnings'] : 0.0,
				'pending_earnings'  => isset( $raw['pending_earnings'] ) ? (float) $raw['pending_earnings'] : 0.0,
				'available_balance' => isset( $raw['available_balance'] ) ? (float) $raw['available_balance'] : 0.0,
				'paid_earnings'     => isset( $raw['completed_earnings'] ) ? (float) $raw['completed_earnings'] : 0.0,
				'withdrawn_amount'  => isset( $raw['withdrawn_amount'] ) ? (float) $raw['withdrawn_amount'] : 0.0,
			);
		}

		return self::empty_earnings();
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

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}

		$year   = (int) current_time( 'Y' );
		$labels = array( 'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec' );
		$earn   = array_fill( 0, 12, 0.0 );
		$lessons = array_fill( 0, 12, 0 );
		$students = array_fill( 0, 6, 0 );

		global $wpdb;
		$payments = GMM_Database::table( 'payments' );
		$bookings = GMM_Database::table( 'bookings' );

		$pay_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT MONTH(created_at) AS m, COALESCE(SUM(amount),0) AS total
				FROM {$payments}
				WHERE teacher_id = %d
				AND YEAR(created_at) = %d
				AND payment_status IN ('completed','paid','success')
				AND payment_method <> %s
				GROUP BY MONTH(created_at)",
				$teacher_id,
				$year,
				'withdrawal'
			),
			ARRAY_A
		);
		if ( is_array( $pay_rows ) ) {
			foreach ( $pay_rows as $row ) {
				$m = absint( $row['m'] );
				if ( $m >= 1 && $m <= 12 ) {
					$gross = (float) $row['total'];
					if ( class_exists( 'GMM_Payment' ) ) {
						$split = GMM_Payment::calculate_split( $gross );
						$gross = isset( $split['teacher_earnings'] ) ? (float) $split['teacher_earnings'] : $gross;
					}
					$earn[ $m - 1 ] = round( $gross, 2 );
				}
			}
		}

		$lesson_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT MONTH(booking_date) AS m, COUNT(*) AS total
				FROM {$bookings}
				WHERE teacher_id = %d
				AND YEAR(booking_date) = %d
				AND booking_status = %s
				GROUP BY MONTH(booking_date)",
				$teacher_id,
				$year,
				'completed'
			),
			ARRAY_A
		);
		if ( is_array( $lesson_rows ) ) {
			foreach ( $lesson_rows as $row ) {
				$m = absint( $row['m'] );
				if ( $m >= 1 && $m <= 12 ) {
					$lessons[ $m - 1 ] = (int) $row['total'];
				}
			}
		}

		$stats = self::get_statistics( $user_id );
		$completed = isset( $stats['completed_lessons'] ) ? (int) $stats['completed_lessons'] : 0;
		$upcoming  = isset( $stats['upcoming_lessons'] ) ? (int) $stats['upcoming_lessons'] : 0;
		$cancelled = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$bookings}
				WHERE teacher_id = %d AND booking_status IN ('cancelled','canceled')",
				$teacher_id
			)
		);

		// Last 6 months new students.
		for ( $i = 5; $i >= 0; $i-- ) {
			$ts    = strtotime( '-' . $i . ' months', current_time( 'timestamp' ) );
			$ym    = gmdate( 'Y-m', $ts );
			$idx   = 5 - $i;
			$count = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(DISTINCT student_id) FROM {$bookings}
					WHERE teacher_id = %d
					AND student_id > 0
					AND DATE_FORMAT(created_at, '%%Y-%%m') = %s",
					$teacher_id,
					$ym
				)
			);
			$students[ $idx ] = $count;
		}

		$month_labels_6 = array();
		for ( $i = 5; $i >= 0; $i-- ) {
			$month_labels_6[] = gmdate( 'M', strtotime( '-' . $i . ' months', current_time( 'timestamp' ) ) );
		}

		$charts = array(
			'earnings' => array(
				'labels'   => $labels,
				'datasets' => array(
					array(
						'label' => __( 'Monthly Earnings', 'gospel-music-mastery' ),
						'data'  => $earn,
					),
				),
			),
			'lessons'  => array(
				'labels'   => array(
					__( 'Completed Lessons', 'gospel-music-mastery' ),
					__( 'Upcoming Lessons', 'gospel-music-mastery' ),
					__( 'Cancelled Lessons', 'gospel-music-mastery' ),
				),
				'datasets' => array(
					array(
						'label' => __( 'Lessons', 'gospel-music-mastery' ),
						'data'  => array( $completed, $upcoming, $cancelled ),
					),
				),
			),
			'students' => array(
				'labels'   => $month_labels_6,
				'datasets' => array(
					array(
						'label' => __( 'New Students', 'gospel-music-mastery' ),
						'data'  => $students,
					),
				),
			),
			'monthly_lessons' => array(
				'labels'   => $labels,
				'datasets' => array(
					array(
						'label' => __( 'Monthly Lessons', 'gospel-music-mastery' ),
						'data'  => $lessons,
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

		$profile = GMM_Teacher::get_profile( $user_id );
		if ( ! is_array( $profile ) ) {
			return array();
		}

		$image = '';
		if ( ! empty( $profile['profile_image'] ) && function_exists( 'gmm_get_media_url' ) ) {
			$image = gmm_get_media_url( $profile['profile_image'], 'medium' );
		}
		if ( ! $image ) {
			$image = function_exists( 'gmm_design_asset_url' )
				? gmm_design_asset_url( 'assets/img/team/01.jpg' )
				: '';
		}

		$first = isset( $profile['first_name'] ) ? (string) $profile['first_name'] : '';
		$last  = isset( $profile['last_name'] ) ? (string) $profile['last_name'] : '';
		$name  = trim( $first . ' ' . $last );
		if ( '' === $name ) {
			$user = get_userdata( $user_id );
			$name = $user ? $user->display_name : __( 'Instructor', 'gospel-music-mastery' );
		}

		$stats = self::get_statistics( $user_id );

		return array(
			'id'             => isset( $profile['id'] ) ? absint( $profile['id'] ) : 0,
			'user_id'        => $user_id,
			'first_name'     => $first,
			'last_name'      => $last,
			'name'           => $name,
			'email'          => isset( $profile['email'] ) ? (string) $profile['email'] : '',
			'specialization' => isset( $profile['specialization'] ) && $profile['specialization']
				? (string) $profile['specialization']
				: __( 'Gospel Music Instructor', 'gospel-music-mastery' ),
			'experience'     => isset( $profile['experience'] ) ? (string) $profile['experience'] : '',
			'bio'            => isset( $profile['bio'] ) ? (string) $profile['bio'] : '',
			'image_url'      => $image,
			'rating'         => isset( $stats['average_rating'] ) ? (float) $stats['average_rating'] : 0.0,
			'total_students' => isset( $stats['total_students'] ) ? (int) $stats['total_students'] : 0,
			'total_classes'  => isset( $stats['total_classes'] ) ? (int) $stats['total_classes'] : 0,
			'status'         => isset( $profile['status'] ) ? sanitize_key( (string) $profile['status'] ) : '',
		);
	}

	/**
	 * Recent classes for the table.
	 *
	 * @param int $user_id WP user ID.
	 * @param int $limit   Max rows.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_recent_classes( $user_id = 0, $limit = 5 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$limit   = max( 1, min( absint( $limit ), 50 ) );

		if ( ! self::user_can_view( $user_id ) ) {
			return array();
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}

		global $wpdb;
		$classes  = GMM_Database::table( 'classes' );
		$bookings = GMM_Database::table( 'bookings' );

		$sql = "SELECT c.id, c.title, c.category, c.status, c.rating,
				(SELECT COUNT(DISTINCT b.student_id) FROM {$bookings} b
					WHERE b.class_id = c.id AND b.student_id > 0) AS student_count
			FROM {$classes} c
			WHERE c.teacher_id = %d
			ORDER BY c.updated_at DESC, c.id DESC
			LIMIT %d";

		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $teacher_id, $limit ), ARRAY_A );
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$out = array();
		foreach ( $rows as $row ) {
			$status = sanitize_key( isset( $row['status'] ) ? (string) $row['status'] : 'draft' );
			$badge  = 'is-draft';
			$label  = ucfirst( $status );
			if ( in_array( $status, array( 'published', 'approved', 'active' ), true ) ) {
				$badge = 'is-published';
				$label = 'Published';
			} elseif ( 'pending' === $status ) {
				$badge = 'is-pending';
				$label = 'Pending';
			} elseif ( in_array( $status, array( 'scheduled' ), true ) ) {
				$badge = 'is-scheduled';
			}

			$rating = isset( $row['rating'] ) ? (float) $row['rating'] : 0;
			$stars  = self::rating_stars( $rating );

			$out[] = array(
				'id'            => absint( $row['id'] ),
				'title'         => isset( $row['title'] ) ? (string) $row['title'] : '',
				'category'      => isset( $row['category'] ) && $row['category'] ? (string) $row['category'] : '—',
				'student_count' => isset( $row['student_count'] ) ? (int) $row['student_count'] : 0,
				'rating'        => $rating,
				'rating_stars'  => $stars,
				'status'        => $status,
				'status_badge'  => $badge,
				'status_label'  => $label,
			);
		}
		return $out;
	}

	/**
	 * Profile completion checklist data.
	 *
	 * @param int                  $user_id User ID.
	 * @param array<string, mixed> $profile Profile summary.
	 * @param array<string, mixed> $stats   Stats.
	 * @return array<string, mixed>
	 */
	public static function get_profile_completion( $user_id, $profile, $stats ) {
		$raw = GMM_Teacher::get_profile( $user_id );
		$raw = is_array( $raw ) ? $raw : array();

		$has_info  = ! empty( $profile['first_name'] ) && ! empty( $profile['last_name'] );
		$has_photo = ! empty( $raw['profile_image'] );
		$has_bio   = ! empty( $raw['bio'] );
		$has_class = ! empty( $stats['total_classes'] );
		$has_spec  = ! empty( $raw['specialization'] );

		$items = array(
			array(
				'label' => __( 'Profile Information', 'gospel-music-mastery' ),
				'done'  => $has_info,
			),
			array(
				'label' => __( 'Profile Photo', 'gospel-music-mastery' ),
				'done'  => $has_photo,
			),
			array(
				'label' => __( 'Specialization', 'gospel-music-mastery' ),
				'done'  => $has_spec || $has_bio,
			),
			array(
				'label' => __( 'First Class', 'gospel-music-mastery' ),
				'done'  => $has_class,
			),
			array(
				'label' => __( 'Availability', 'gospel-music-mastery' ),
				'done'  => class_exists( 'GMM_Availability' ) && ! empty( GMM_Availability::get_availability( $user_id ) ),
			),
		);

		$done  = 0;
		$total = count( $items );
		foreach ( $items as $item ) {
			if ( ! empty( $item['done'] ) ) {
				$done++;
			}
		}
		$percent = $total ? (int) round( ( $done / $total ) * 100 ) : 0;

		return array(
			'percent' => $percent,
			'items'   => $items,
			'done'    => $done,
			'total'   => $total,
		);
	}

	/**
	 * Quick action page URLs.
	 *
	 * @return array<string, string>
	 */
	public static function quick_links() {
		return array(
			'add_class'    => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teacher_classes' ) : '',
			'classes'      => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teacher_classes' ) : '',
			'bookings'     => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teacher_bookings' ) : '',
			'availability' => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teacher_availability' ) : '',
			'earnings'     => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teacher_withdrawals' ) : '',
			'profile'      => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teacher_profile' ) : '',
			'settings'     => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teacher_settings' ) : '',
			'dashboard'    => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teacher_dashboard' ) : '',
		);
	}

	/**
	 * Enqueue chart localize script on teacher dashboard page.
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

		$post = get_queried_object();
		$content = ( $post instanceof WP_Post ) ? (string) $post->post_content : '';
		if ( ! has_shortcode( $content, 'gmm_teacher_dashboard' ) && false === strpos( $content, 'gmm_teacher_dashboard' ) ) {
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
			'gmm-teacher-dashboard',
			$js . 'gmm-teacher-dashboard.js',
			array( 'gmm-core-script', 'gmm-dashboard-charts' ),
			$version,
			true
		);

		$vars = self::get_template_vars();
		wp_localize_script(
			'gmm-teacher-dashboard',
			'GMM_TEACHER_DASH',
			array(
				'nonce'   => wp_create_nonce( 'gmm_nonce' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'charts'  => isset( $vars['charts'] ) ? $vars['charts'] : array(),
				'stats'   => isset( $vars['stats'] ) ? $vars['stats'] : array(),
				'earnings'=> isset( $vars['earnings_summary'] ) ? $vars['earnings_summary'] : array(),
				'actions' => array(
					'stats'    => 'gmm_teacher_dashboard_stats',
					'bookings' => 'gmm_teacher_dashboard_bookings',
					'earnings' => 'gmm_teacher_dashboard_earnings',
				),
			)
		);
	}

	/**
	 * AJAX: refresh statistics.
	 *
	 * @return void
	 */
	public function ajax_dashboard_stats() {
		check_ajax_referer( 'gmm_nonce', 'nonce' );
		if ( ! self::user_can_view() ) {
			wp_send_json_error( array( 'message' => __( 'Your account is waiting for approval.', 'gospel-music-mastery' ) ), 403 );
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
		check_ajax_referer( 'gmm_nonce', 'nonce' );
		if ( ! self::user_can_view() ) {
			wp_send_json_error( array( 'message' => __( 'Your account is waiting for approval.', 'gospel-music-mastery' ) ), 403 );
		}
		$user_id = get_current_user_id();
		wp_send_json_success(
			array(
				'upcoming' => self::get_upcoming_lessons( $user_id, 5 ),
				'recent'   => self::get_recent_bookings( $user_id, 8 ),
			)
		);
	}

	/**
	 * AJAX: refresh earnings summary.
	 *
	 * @return void
	 */
	public function ajax_dashboard_earnings() {
		check_ajax_referer( 'gmm_nonce', 'nonce' );
		if ( ! self::user_can_view() ) {
			wp_send_json_error( array( 'message' => __( 'Your account is waiting for approval.', 'gospel-music-mastery' ) ), 403 );
		}
		$user_id = get_current_user_id();
		self::flush_cache( $user_id );
		wp_send_json_success(
			array(
				'earnings' => self::get_earnings_summary( $user_id ),
				'stats'    => self::get_statistics( $user_id ),
			)
		);
	}

	/**
	 * @param int                  $teacher_id Teacher ID.
	 * @param array<string, mixed> $row        Row.
	 * @return void
	 */
	public function flush_on_teacher_change( $teacher_id, $row = array() ) {
		unset( $teacher_id );
		$user_id = is_array( $row ) && ! empty( $row['user_id'] ) ? absint( $row['user_id'] ) : 0;
		self::flush_cache( $user_id );
	}

	/**
	 * @return void
	 */
	public function flush_on_booking_hook() {
		self::flush_cache( get_current_user_id() );
	}

	/**
	 * @param int $user_id User ID (0 = flush group pattern for current).
	 * @return void
	 */
	public static function flush_cache( $user_id = 0 ) {
		$user_id = absint( $user_id );
		if ( $user_id ) {
			delete_transient( self::CACHE_GROUP . '_stats_' . $user_id );
			delete_transient( self::CACHE_GROUP . '_charts_' . $user_id );
			return;
		}
		// Best-effort: current user.
		$uid = get_current_user_id();
		if ( $uid ) {
			delete_transient( self::CACHE_GROUP . '_stats_' . $uid );
			delete_transient( self::CACHE_GROUP . '_charts_' . $uid );
		}
	}

	/**
	 * @param array<string, mixed> $row Raw booking row.
	 * @return array<string, mixed>
	 */
	private static function format_booking_row( $row ) {
		$status = sanitize_key( isset( $row['booking_status'] ) ? (string) $row['booking_status'] : 'pending' );
		$badge  = 'is-pending';
		$label  = 'Pending';
		if ( in_array( $status, array( 'confirmed', 'upcoming' ), true ) ) {
			$badge = 'is-published';
			$label = 'Confirmed';
		} elseif ( 'scheduled' === $status ) {
			$badge = 'is-scheduled';
			$label = 'Scheduled';
		} elseif ( 'completed' === $status ) {
			$badge = 'is-published';
			$label = 'Completed';
		} elseif ( in_array( $status, array( 'cancelled', 'canceled' ), true ) ) {
			$badge = 'is-pending';
			$label = 'Cancelled';
		}

		$date = isset( $row['booking_date'] ) ? (string) $row['booking_date'] : '';
		$time = isset( $row['booking_time'] ) ? (string) $row['booking_time'] : '';
		$meta = '';
		if ( $date ) {
			$ts = strtotime( $date . ' ' . $time );
			if ( $ts ) {
				$meta = wp_date( 'l · g:i A', $ts );
			} else {
				$meta = $date . ( $time ? ' · ' . $time : '' );
			}
		}

		return array(
			'id'           => absint( isset( $row['id'] ) ? $row['id'] : 0 ),
			'student_name' => isset( $row['student_name'] ) ? (string) $row['student_name'] : __( 'Student', 'gospel-music-mastery' ),
			'class_name'   => isset( $row['class_name'] ) ? (string) $row['class_name'] : __( 'Class', 'gospel-music-mastery' ),
			'date'         => $date,
			'time'         => $time,
			'meta'         => $meta,
			'status'       => $status,
			'status_badge' => $badge,
			'status_label' => $label,
		);
	}

	/**
	 * @param float $rating Rating.
	 * @return string
	 */
	private static function rating_stars( $rating ) {
		$rating = (float) $rating;
		if ( $rating <= 0 ) {
			return '—';
		}
		$full  = (int) floor( $rating );
		$half  = ( $rating - $full ) >= 0.5 ? 1 : 0;
		$empty = 5 - $full - $half;
		return str_repeat( '★', max( 0, $full ) ) . ( $half ? '☆' : '' ) . str_repeat( '☆', max( 0, $empty ) );
	}

	/**
	 * @return array<string, int|float>
	 */
	private static function empty_stats() {
		return array(
			'total_classes'     => 0,
			'active_classes'    => 0,
			'enrolled_classes'  => 0,
			'total_students'    => 0,
			'upcoming_lessons'  => 0,
			'completed_lessons' => 0,
			'total_earnings'    => 0.0,
			'average_rating'    => 0.0,
		);
	}

	/**
	 * @return array<string, float>
	 */
	private static function empty_earnings() {
		return array(
			'total_earnings'    => 0.0,
			'pending_earnings'  => 0.0,
			'available_balance' => 0.0,
			'paid_earnings'     => 0.0,
			'withdrawn_amount'  => 0.0,
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
