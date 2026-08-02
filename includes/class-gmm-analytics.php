<?php
/**
 * Reports and analytics for Gospel Music Mastery.
 *
 * Chart.js-ready data only — does not change dashboard/chart UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Analytics
 *
 * Admin, teacher, and student statistics with date filters and report prep.
 */
class GMM_Analytics {

	/**
	 * Register hooks (dashboard data filters prepared; no template changes).
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_dashboard_analytics', 20, 2 );
	}

	/**
	 * Attach analytics payloads to dashboard shortcodes (templates unchanged).
	 *
	 * @param array<string, mixed> $args Template args.
	 * @param string               $tag  Shortcode tag.
	 * @return array<string, mixed>
	 */
	public function inject_dashboard_analytics( $args, $tag ) {
		$args = is_array( $args ) ? $args : array();
		$tag  = is_string( $tag ) ? $tag : '';

		switch ( $tag ) {
			case 'gmm_admin_dashboard':
				if ( current_user_can( 'manage_options' ) ) {
					$args['gmm_analytics'] = self::get_admin_statistics();
					$args['gmm_charts']    = array(
						'revenue'  => self::get_revenue_chart_data( 'this_year' ),
						'bookings' => self::get_booking_chart_data( 'this_year' ),
					);
				}
				break;

			case 'gmm_teacher_dashboard':
				$user_id = get_current_user_id();
				if ( $user_id && ( gmm_is_teacher( $user_id ) || current_user_can( 'manage_options' ) ) ) {
					$args['gmm_analytics'] = self::get_teacher_statistics( $user_id );
				}
				break;

			case 'gmm_student_dashboard':
				$user_id = get_current_user_id();
				if ( $user_id && ( gmm_is_student( $user_id ) || current_user_can( 'manage_options' ) ) ) {
					$args['gmm_analytics'] = self::get_student_statistics( $user_id );
				}
				break;
		}

		return $args;
	}

	/**
	 * Admin platform statistics.
	 *
	 * @param array<string, mixed> $args Optional date filter.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function get_admin_statistics( $args = array() ) {
		if ( ! self::can_view_admin() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Admin access required.', 'gospel-music-mastery' ) );
		}

		$args  = is_array( $args ) ? $args : array();
		$range = self::resolve_date_range( $args );
		$key   = self::cache_key( 'admin_stats', $range );
		$cached = self::get_cache( $key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;
		$students = GMM_Database::table( 'students' );
		$teachers = GMM_Database::table( 'teachers' );
		$classes  = GMM_Database::table( 'classes' );
		$bookings = GMM_Database::table( 'bookings' );
		$payments = GMM_Database::table( 'payments' );

		$total_students = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$students}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$total_teachers = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$teachers}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$total_classes  = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$classes}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$booking_sql = "SELECT COUNT(*) FROM {$bookings} WHERE 1=1";
		$booking_params = array();
		if ( $range['from'] && $range['to'] ) {
			$booking_sql     .= ' AND booking_date >= %s AND booking_date <= %s';
			$booking_params[] = $range['from'];
			$booking_params[] = $range['to'];
		}
		$total_bookings = empty( $booking_params )
			? (int) $wpdb->get_var( $booking_sql ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			: (int) $wpdb->get_var( $wpdb->prepare( $booking_sql, $booking_params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$revenue_sql = "SELECT COALESCE(SUM(amount),0) FROM {$payments}
			WHERE payment_status = %s AND payment_method <> %s";
		$rev_params  = array( 'completed', 'withdrawal' );
		if ( $range['from'] && $range['to'] ) {
			$revenue_sql .= ' AND DATE(created_at) >= %s AND DATE(created_at) <= %s';
			$rev_params[] = $range['from'];
			$rev_params[] = $range['to'];
		}
		$total_revenue = (float) $wpdb->get_var( $wpdb->prepare( $revenue_sql, $rev_params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$pending_teachers = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$teachers} WHERE status = %s", 'pending' )
		);
		$pending_classes = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$classes} WHERE status IN (%s,%s)",
				'pending',
				'draft'
			)
		);

		$data = array(
			'total_students'    => $total_students,
			'total_teachers'    => $total_teachers,
			'total_classes'     => $total_classes,
			'total_bookings'    => $total_bookings,
			'total_revenue'     => round( $total_revenue, 2 ),
			'pending_approvals' => $pending_teachers + $pending_classes,
			'pending_teachers'  => $pending_teachers,
			'pending_classes'   => $pending_classes,
			'range'             => $range,
		);

		self::set_cache( $key, $data );
		return apply_filters( 'gmm_admin_statistics', $data, $args );
	}

	/**
	 * Revenue report with daily/weekly/monthly/yearly totals + chart data.
	 *
	 * @param array<string, mixed> $args Date filter args.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function get_revenue_report( $args = array() ) {
		if ( ! self::can_view_admin() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Admin access required.', 'gospel-music-mastery' ) );
		}

		$args  = is_array( $args ) ? $args : array();
		$range = self::resolve_date_range( $args );
		$key   = self::cache_key( 'revenue', $range );
		$cached = self::get_cache( $key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		$today = current_time( 'Y-m-d' );
		$week  = self::range_for_period( 'this_week' );
		$month = self::range_for_period( 'this_month' );
		$year  = self::range_for_period( 'this_year' );

		$data = array(
			'daily_revenue'   => self::sum_revenue( $today, $today ),
			'weekly_revenue'  => self::sum_revenue( $week['from'], $week['to'] ),
			'monthly_revenue' => self::sum_revenue( $month['from'], $month['to'] ),
			'yearly_revenue'  => self::sum_revenue( $year['from'], $year['to'] ),
			'range_revenue'   => self::sum_revenue( $range['from'], $range['to'] ),
			'chart'           => self::get_revenue_chart_data( isset( $args['period'] ) ? $args['period'] : 'this_year', $args ),
			'range'           => $range,
		);

		self::set_cache( $key, $data );
		return apply_filters( 'gmm_revenue_report', $data, $args );
	}

	/**
	 * Booking status report.
	 *
	 * @param array<string, mixed> $args Date filter args.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function get_booking_report( $args = array() ) {
		if ( ! self::can_view_admin() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Admin access required.', 'gospel-music-mastery' ) );
		}

		$args  = is_array( $args ) ? $args : array();
		$range = self::resolve_date_range( $args );

		global $wpdb;
		$table = GMM_Database::table( 'bookings' );

		$where  = array( '1=1' );
		$params = array();
		if ( $range['from'] && $range['to'] ) {
			$where[]  = 'booking_date >= %s';
			$where[]  = 'booking_date <= %s';
			$params[] = $range['from'];
			$params[] = $range['to'];
		}
		$where_sql = implode( ' AND ', $where );

		$count_status = function ( $status ) use ( $wpdb, $table, $where_sql, $params ) {
			$sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql} AND booking_status = %s";
			$p   = $params;
			$p[] = $status;
			return (int) $wpdb->get_var( $wpdb->prepare( $sql, $p ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		};

		$total_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total     = empty( $params )
			? (int) $wpdb->get_var( $total_sql ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			: (int) $wpdb->get_var( $wpdb->prepare( $total_sql, $params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$data = array(
			'total_bookings'     => $total,
			'completed_bookings' => $count_status( 'completed' ),
			'pending_bookings'   => $count_status( 'pending' ),
			'confirmed_bookings' => $count_status( 'confirmed' ),
			'cancelled_bookings' => $count_status( 'cancelled' ),
			'chart'              => self::get_booking_chart_data( isset( $args['period'] ) ? $args['period'] : 'this_year', $args ),
			'range'              => $range,
		);

		return apply_filters( 'gmm_booking_report', $data, $args );
	}

	/**
	 * Teacher statistics (own data only unless admin).
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Optional filters.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function get_teacher_statistics( $user_id = 0, $args = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$args    = is_array( $args ) ? $args : array();

		if ( ! self::can_view_teacher( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot view these statistics.', 'gospel-music-mastery' ) );
		}

		$teacher_id = class_exists( 'GMM_Teacher' ) ? GMM_Teacher::get_teacher_id( $user_id ) : 0;
		if ( ! $teacher_id ) {
			return new WP_Error( 'gmm_no_profile', __( 'Teacher profile not found.', 'gospel-music-mastery' ) );
		}

		$range = self::resolve_date_range( $args );

		global $wpdb;
		$classes  = GMM_Database::table( 'classes' );
		$bookings = GMM_Database::table( 'bookings' );
		$payments = GMM_Database::table( 'payments' );
		$teachers = GMM_Database::table( 'teachers' );

		$total_classes = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$classes} WHERE teacher_id = %d", $teacher_id )
		);

		$total_students = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT student_id) FROM {$bookings}
				WHERE teacher_id = %d AND student_id > 0",
				$teacher_id
			)
		);

		$completed_sql = "SELECT COUNT(*) FROM {$bookings} WHERE teacher_id = %d AND booking_status = %s";
		$completed_params = array( $teacher_id, 'completed' );
		if ( $range['from'] && $range['to'] ) {
			$completed_sql     .= ' AND booking_date >= %s AND booking_date <= %s';
			$completed_params[] = $range['from'];
			$completed_params[] = $range['to'];
		}
		$completed_lessons = (int) $wpdb->get_var( $wpdb->prepare( $completed_sql, $completed_params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$earn_sql = "SELECT COALESCE(SUM(amount),0) FROM {$payments}
			WHERE teacher_id = %d AND payment_status = %s AND payment_method <> %s";
		$earn_params = array( $teacher_id, 'completed', 'withdrawal' );
		if ( $range['from'] && $range['to'] ) {
			$earn_sql     .= ' AND DATE(created_at) >= %s AND DATE(created_at) <= %s';
			$earn_params[] = $range['from'];
			$earn_params[] = $range['to'];
		}
		$gross = (float) $wpdb->get_var( $wpdb->prepare( $earn_sql, $earn_params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$total_earnings = $gross;
		if ( class_exists( 'GMM_Payment' ) ) {
			$split          = GMM_Payment::calculate_split( $gross );
			$total_earnings = isset( $split['teacher_earnings'] ) ? (float) $split['teacher_earnings'] : $gross;
		}

		$rating = (float) $wpdb->get_var(
			$wpdb->prepare( "SELECT rating FROM {$teachers} WHERE id = %d LIMIT 1", $teacher_id )
		);

		$data = array(
			'total_classes'     => $total_classes,
			'total_students'    => $total_students,
			'completed_lessons' => $completed_lessons,
			'total_earnings'    => round( $total_earnings, 2 ),
			'average_rating'    => round( $rating, 2 ),
			'teacher_id'        => $teacher_id,
			'range'             => $range,
		);

		return apply_filters( 'gmm_teacher_statistics', $data, $user_id, $args );
	}

	/**
	 * Student statistics (own data only unless admin).
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Optional filters.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function get_student_statistics( $user_id = 0, $args = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$args    = is_array( $args ) ? $args : array();

		if ( ! self::can_view_student( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot view these statistics.', 'gospel-music-mastery' ) );
		}

		$student_id = class_exists( 'GMM_Student' ) ? GMM_Student::get_student_id( $user_id ) : 0;
		if ( ! $student_id ) {
			return new WP_Error( 'gmm_no_profile', __( 'Student profile not found.', 'gospel-music-mastery' ) );
		}

		$range = self::resolve_date_range( $args );
		$today = current_time( 'Y-m-d' );

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$payments = GMM_Database::table( 'payments' );

		$total_lessons = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$bookings} WHERE student_id = %d", $student_id )
		);

		$completed = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$bookings} WHERE student_id = %d AND booking_status = %s",
				$student_id,
				'completed'
			)
		);

		$upcoming = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$bookings}
				WHERE student_id = %d
				AND booking_date >= %s
				AND booking_status IN ('pending','confirmed')",
				$student_id,
				$today
			)
		);

		$pay_sql = "SELECT COALESCE(SUM(amount),0) FROM {$payments}
			WHERE student_id = %d AND payment_status = %s";
		$pay_params = array( $student_id, 'completed' );
		if ( $range['from'] && $range['to'] ) {
			$pay_sql     .= ' AND DATE(created_at) >= %s AND DATE(created_at) <= %s';
			$pay_params[] = $range['from'];
			$pay_params[] = $range['to'];
		}
		$total_payments = (float) $wpdb->get_var( $wpdb->prepare( $pay_sql, $pay_params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$practice_progress = 0;
		if ( $total_lessons > 0 ) {
			$practice_progress = (int) round( ( $completed / $total_lessons ) * 100 );
		}

		$data = array(
			'total_lessons'     => $total_lessons,
			'completed_lessons' => $completed,
			'upcoming_lessons'  => $upcoming,
			'practice_progress' => min( 100, max( 0, $practice_progress ) ),
			'total_payments'    => round( $total_payments, 2 ),
			'student_id'        => $student_id,
			'range'             => $range,
		);

		return apply_filters( 'gmm_student_statistics', $data, $user_id, $args );
	}

	/**
	 * Program analytics.
	 *
	 * @param array<string, mixed> $args Optional args.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function get_program_statistics( $args = array() ) {
		if ( ! self::can_view_admin() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Admin access required.', 'gospel-music-mastery' ) );
		}

		$args = is_array( $args ) ? $args : array();

		global $wpdb;
		$table = GMM_Database::table( 'programs' );

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$published = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status = %s", 'published' )
		);
		$featured = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE featured = %d", 1 )
		);

		$categories = $wpdb->get_results(
			"SELECT category, COUNT(*) AS total FROM {$table}
			WHERE category <> ''
			GROUP BY category
			ORDER BY total DESC, category ASC
			LIMIT 20", // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			ARRAY_A
		);
		$categories = is_array( $categories ) ? $categories : array();

		$popular = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, title, category, difficulty, status, featured, image, created_at
				FROM {$table}
				WHERE status = %s
				ORDER BY featured DESC, created_at DESC
				LIMIT %d",
				'published',
				10
			),
			ARRAY_A
		);
		$popular = is_array( $popular ) ? $popular : array();

		// Enrollment system not built yet — prepared field for future.
		$enrollments = (int) apply_filters( 'gmm_program_enrollments_count', 0, $args );

		$data = array(
			'total_programs'     => $total,
			'published_programs' => $published,
			'featured_programs'  => $featured,
			'program_enrollments'=> $enrollments,
			'program_categories' => $categories,
			'popular_programs'   => $popular,
			'chart'              => self::chart_from_categories( $categories ),
		);

		return apply_filters( 'gmm_program_statistics', $data, $args );
	}

	/**
	 * Admin report bundles (CSV-ready headers/rows later).
	 *
	 * @param string               $type teacher|student|booking|payment|class.
	 * @param array<string, mixed> $args Filters.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function get_admin_report( $type, $args = array() ) {
		if ( ! self::can_view_admin() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Admin access required.', 'gospel-music-mastery' ) );
		}

		$type  = sanitize_key( (string) $type );
		$args  = is_array( $args ) ? $args : array();
		$range = self::resolve_date_range( $args );
		$limit = isset( $args['limit'] ) ? min( absint( $args['limit'] ), 500 ) : 100;

		switch ( $type ) {
			case 'teacher':
			case 'teachers':
				return self::build_table_report(
					'teachers',
					array( 'id', 'first_name', 'last_name', 'email', 'specialization', 'rating', 'status', 'created_at' ),
					$range,
					'created_at',
					$limit
				);

			case 'student':
			case 'students':
				return self::build_table_report(
					'students',
					array( 'id', 'first_name', 'last_name', 'email', 'learning_level', 'status', 'created_at' ),
					$range,
					'created_at',
					$limit
				);

			case 'booking':
			case 'bookings':
				return self::build_table_report(
					'bookings',
					array( 'id', 'student_id', 'teacher_id', 'class_id', 'booking_date', 'booking_time', 'amount', 'booking_status', 'payment_status', 'created_at' ),
					$range,
					'booking_date',
					$limit
				);

			case 'payment':
			case 'payments':
				return self::build_table_report(
					'payments',
					array( 'id', 'booking_id', 'student_id', 'teacher_id', 'amount', 'payment_method', 'payment_status', 'transaction_id', 'created_at' ),
					$range,
					'created_at',
					$limit
				);

			case 'class':
			case 'classes':
				return self::build_table_report(
					'classes',
					array( 'id', 'teacher_id', 'title', 'category', 'difficulty', 'price', 'rating', 'status', 'created_at' ),
					$range,
					'created_at',
					$limit
				);

			default:
				return new WP_Error( 'gmm_invalid', __( 'Unknown report type.', 'gospel-music-mastery' ) );
		}
	}

	/**
	 * Chart.js revenue dataset.
	 *
	 * @param string               $period Period key.
	 * @param array<string, mixed> $args   Extra args.
	 * @return array<string, mixed>
	 */
	public static function get_revenue_chart_data( $period = 'this_year', $args = array() ) {
		$buckets = self::build_time_buckets( $period, $args );
		$values  = array();

		foreach ( $buckets as $bucket ) {
			$values[] = self::sum_revenue( $bucket['from'], $bucket['to'] );
		}

		return self::chart_payload(
			wp_list_pluck( $buckets, 'label' ),
			$values,
			__( 'Revenue', 'gospel-music-mastery' )
		);
	}

	/**
	 * Chart.js booking counts over time.
	 *
	 * @param string               $period Period key.
	 * @param array<string, mixed> $args   Extra args.
	 * @return array<string, mixed>
	 */
	public static function get_booking_chart_data( $period = 'this_year', $args = array() ) {
		$buckets = self::build_time_buckets( $period, $args );
		$values  = array();

		foreach ( $buckets as $bucket ) {
			$values[] = self::count_bookings( $bucket['from'], $bucket['to'] );
		}

		return self::chart_payload(
			wp_list_pluck( $buckets, 'label' ),
			$values,
			__( 'Bookings', 'gospel-music-mastery' )
		);
	}

	/**
	 * Resolve date filter into from/to (Y-m-d).
	 *
	 * Supports: today, this_week, this_month, this_year, custom (date_from/date_to).
	 *
	 * @param array<string, mixed> $args Args.
	 * @return array<string, string>
	 */
	public static function resolve_date_range( $args ) {
		$args   = is_array( $args ) ? $args : array();
		$period = isset( $args['period'] ) ? sanitize_key( (string) $args['period'] ) : '';

		if ( ! $period && ! empty( $args['date_from'] ) && ! empty( $args['date_to'] ) ) {
			$period = 'custom';
		}
		if ( ! $period ) {
			return array(
				'period' => 'all',
				'from'   => '',
				'to'     => '',
			);
		}

		if ( 'custom' === $period ) {
			$from = self::sanitize_date( isset( $args['date_from'] ) ? $args['date_from'] : '' );
			$to   = self::sanitize_date( isset( $args['date_to'] ) ? $args['date_to'] : '' );
			if ( $from && $to && $from > $to ) {
				$tmp  = $from;
				$from = $to;
				$to   = $tmp;
			}
			return array(
				'period' => 'custom',
				'from'   => $from,
				'to'     => $to,
			);
		}

		$resolved = self::range_for_period( $period );
		$resolved['period'] = $period;
		return $resolved;
	}

	/**
	 * Built-in period ranges.
	 *
	 * @param string $period Period key.
	 * @return array<string, string>
	 */
	public static function range_for_period( $period ) {
		$today = current_time( 'Y-m-d' );
		$ts    = current_time( 'timestamp' );

		switch ( sanitize_key( $period ) ) {
			case 'today':
				return array( 'from' => $today, 'to' => $today );

			case 'this_week':
				$start = date( 'Y-m-d', strtotime( 'monday this week', $ts ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				$end   = date( 'Y-m-d', strtotime( 'sunday this week', $ts ) ); // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				return array( 'from' => $start, 'to' => $end );

			case 'this_month':
				return array(
					'from' => date( 'Y-m-01', $ts ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					'to'   => date( 'Y-m-t', $ts ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				);

			case 'this_year':
				return array(
					'from' => date( 'Y-01-01', $ts ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
					'to'   => date( 'Y-12-31', $ts ), // phpcs:ignore WordPress.DateTime.RestrictedFunctions.date_date
				);

			default:
				return array( 'from' => '', 'to' => '' );
		}
	}

	/**
	 * Chart.js structure.
	 *
	 * @param array<int, string> $labels  Labels.
	 * @param array<int, float|int> $data Data points.
	 * @param string             $label   Dataset label.
	 * @return array<string, mixed>
	 */
	public static function chart_payload( $labels, $data, $label ) {
		return array(
			'labels'   => array_values( $labels ),
			'datasets' => array(
				array(
					'label' => sanitize_text_field( $label ),
					'data'  => array_map( 'floatval', array_values( $data ) ),
				),
			),
		);
	}

	/**
	 * Sum completed revenue between dates.
	 *
	 * @param string $from Y-m-d.
	 * @param string $to   Y-m-d.
	 * @return float
	 */
	private static function sum_revenue( $from, $to ) {
		global $wpdb;
		$table = GMM_Database::table( 'payments' );

		$sql    = "SELECT COALESCE(SUM(amount),0) FROM {$table}
			WHERE payment_status = %s AND payment_method <> %s";
		$params = array( 'completed', 'withdrawal' );

		if ( $from && $to ) {
			$sql     .= ' AND DATE(created_at) >= %s AND DATE(created_at) <= %s';
			$params[] = $from;
			$params[] = $to;
		}

		return round( (float) $wpdb->get_var( $wpdb->prepare( $sql, $params ) ), 2 ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	}

	/**
	 * Count bookings between dates.
	 *
	 * @param string $from From.
	 * @param string $to   To.
	 * @return int
	 */
	private static function count_bookings( $from, $to ) {
		global $wpdb;
		$table = GMM_Database::table( 'bookings' );

		if ( ! $from || ! $to ) {
			return (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		}

		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE booking_date >= %s AND booking_date <= %s",
				$from,
				$to
			)
		);
	}

	/**
	 * Time buckets for charts.
	 *
	 * @param string               $period Period.
	 * @param array<string, mixed> $args   Args.
	 * @return array<int, array<string, string>>
	 */
	private static function build_time_buckets( $period, $args = array() ) {
		$period = sanitize_key( (string) $period );
		$ts     = current_time( 'timestamp' );
		$buckets = array();

		if ( 'custom' === $period || ( ! empty( $args['date_from'] ) && ! empty( $args['date_to'] ) ) ) {
			$range = self::resolve_date_range( array_merge( $args, array( 'period' => 'custom' ) ) );
			if ( $range['from'] && $range['to'] ) {
				$start = strtotime( $range['from'] );
				$end   = strtotime( $range['to'] );
				if ( $start && $end ) {
					// Daily buckets capped at 62 days.
					$days = (int) floor( ( $end - $start ) / DAY_IN_SECONDS ) + 1;
					if ( $days > 62 ) {
						// Monthly style within custom range.
						$cursor = strtotime( gmdate( 'Y-m-01', $start ) );
						while ( $cursor && $cursor <= $end ) {
							$from = gmdate( 'Y-m-01', $cursor );
							$to   = gmdate( 'Y-m-t', $cursor );
							if ( $from < $range['from'] ) {
								$from = $range['from'];
							}
							if ( $to > $range['to'] ) {
								$to = $range['to'];
							}
							$buckets[] = array(
								'label' => gmdate( 'M Y', $cursor ),
								'from'  => $from,
								'to'    => $to,
							);
							$cursor = strtotime( '+1 month', $cursor );
						}
						return $buckets;
					}
					for ( $i = 0; $i < $days; $i++ ) {
						$day = gmdate( 'Y-m-d', $start + ( $i * DAY_IN_SECONDS ) );
						$buckets[] = array(
							'label' => $day,
							'from'  => $day,
							'to'    => $day,
						);
					}
					return $buckets;
				}
			}
		}

		switch ( $period ) {
			case 'today':
			case 'this_week':
				$range = self::range_for_period( 'this_week' === $period ? 'this_week' : 'today' );
				if ( 'today' === $period ) {
					return array(
						array(
							'label' => $range['from'],
							'from'  => $range['from'],
							'to'    => $range['to'],
						),
					);
				}
				$start = strtotime( $range['from'] );
				for ( $i = 0; $i < 7; $i++ ) {
					$day = gmdate( 'Y-m-d', $start + ( $i * DAY_IN_SECONDS ) );
					$buckets[] = array(
						'label' => gmdate( 'D', $start + ( $i * DAY_IN_SECONDS ) ),
						'from'  => $day,
						'to'    => $day,
					);
				}
				return $buckets;

			case 'this_month':
				$year  = (int) gmdate( 'Y', $ts );
				$month = (int) gmdate( 'n', $ts );
				$days  = (int) gmdate( 't', $ts );
				for ( $d = 1; $d <= $days; $d++ ) {
					$day = sprintf( '%04d-%02d-%02d', $year, $month, $d );
					$buckets[] = array(
						'label' => (string) $d,
						'from'  => $day,
						'to'    => $day,
					);
				}
				return $buckets;

			case 'this_year':
			default:
				$year = (int) gmdate( 'Y', $ts );
				for ( $m = 1; $m <= 12; $m++ ) {
					$from = sprintf( '%04d-%02d-01', $year, $m );
					$to   = gmdate( 'Y-m-t', strtotime( $from ) );
					$buckets[] = array(
						'label' => gmdate( 'F', strtotime( $from ) ),
						'from'  => $from,
						'to'    => $to,
					);
				}
				return $buckets;
		}
	}

	/**
	 * Category chart helper.
	 *
	 * @param array<int, array<string, mixed>> $categories Rows.
	 * @return array<string, mixed>
	 */
	private static function chart_from_categories( $categories ) {
		$labels = array();
		$data   = array();
		foreach ( $categories as $row ) {
			$labels[] = isset( $row['category'] ) ? (string) $row['category'] : '';
			$data[]   = isset( $row['total'] ) ? (int) $row['total'] : 0;
		}
		return self::chart_payload( $labels, $data, __( 'Programs', 'gospel-music-mastery' ) );
	}

	/**
	 * Generic admin table report (CSV-ready).
	 *
	 * @param string               $table_key Table key.
	 * @param array<int, string>   $columns   Columns.
	 * @param array<string, string> $range    Date range.
	 * @param string               $date_col  Date column.
	 * @param int                  $limit     Limit.
	 * @return array<string, mixed>
	 */
	private static function build_table_report( $table_key, $columns, $range, $date_col, $limit ) {
		global $wpdb;
		$table   = GMM_Database::table( $table_key );
		$safe    = array();
		foreach ( $columns as $col ) {
			$col = preg_replace( '/[^a-z0-9_]/i', '', $col );
			if ( $col ) {
				$safe[] = $col;
			}
		}
		if ( ! $safe ) {
			return array(
				'type'    => $table_key,
				'headers' => array(),
				'rows'    => array(),
				'export'  => array( 'format' => 'csv', 'ready' => false ),
			);
		}

		$date_col = preg_replace( '/[^a-z0-9_]/i', '', $date_col );
		$select   = implode( ', ', $safe );
		$where    = array( '1=1' );
		$params   = array();

		if ( $date_col && ! empty( $range['from'] ) && ! empty( $range['to'] ) ) {
			if ( 'booking_date' === $date_col ) {
				$where[]  = "{$date_col} >= %s";
				$where[]  = "{$date_col} <= %s";
			} else {
				$where[]  = "DATE({$date_col}) >= %s";
				$where[]  = "DATE({$date_col}) <= %s";
			}
			$params[] = $range['from'];
			$params[] = $range['to'];
		}

		$sql       = "SELECT {$select} FROM {$table} WHERE " . implode( ' AND ', $where ) . " ORDER BY id DESC LIMIT %d";
		$params[]  = $limit;
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
		$rows = is_array( $rows ) ? $rows : array();

		return array(
			'type'    => sanitize_key( $table_key ),
			'headers' => $safe,
			'rows'    => $rows,
			'range'   => $range,
			'export'  => array(
				'format' => 'csv',
				'ready'  => true, // Structure ready; UI/export not implemented yet.
			),
		);
	}

	/**
	 * @return bool
	 */
	private static function can_view_admin() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @param int $user_id User ID.
	 * @return bool
	 */
	private static function can_view_teacher( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id || ! is_user_logged_in() ) {
			return false;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		return ( get_current_user_id() === $user_id ) && gmm_is_teacher( $user_id );
	}

	/**
	 * @param int $user_id User ID.
	 * @return bool
	 */
	private static function can_view_student( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id || ! is_user_logged_in() ) {
			return false;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		return ( get_current_user_id() === $user_id ) && gmm_is_student( $user_id );
	}

	/**
	 * @param string $date Date.
	 * @return string
	 */
	private static function sanitize_date( $date ) {
		$date = sanitize_text_field( (string) $date );
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return $date;
		}
		$ts = strtotime( $date );
		return $ts ? gmdate( 'Y-m-d', $ts ) : '';
	}

	/**
	 * @param string               $type Type.
	 * @param array<string, mixed> $range Range.
	 * @return string
	 */
	private static function cache_key( $type, $range ) {
		return 'gmm_analytics_' . sanitize_key( $type ) . '_' . md5( wp_json_encode( $range ) );
	}

	/**
	 * @param string $key Key.
	 * @return array<string, mixed>|null
	 */
	private static function get_cache( $key ) {
		if ( ! apply_filters( 'gmm_analytics_cache_enabled', false, $key ) ) {
			return null;
		}
		$cached = get_transient( $key );
		return is_array( $cached ) ? $cached : null;
	}

	/**
	 * @param string               $key  Key.
	 * @param array<string, mixed> $data Data.
	 * @return void
	 */
	private static function set_cache( $key, $data ) {
		if ( ! apply_filters( 'gmm_analytics_cache_enabled', false, $key ) ) {
			return;
		}
		$ttl = (int) apply_filters( 'gmm_analytics_cache_ttl', 5 * MINUTE_IN_SECONDS, $key );
		set_transient( $key, $data, max( 30, $ttl ) );
	}
}
