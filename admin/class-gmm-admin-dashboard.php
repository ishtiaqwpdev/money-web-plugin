<?php
/**
 * Admin dashboard data controller.
 *
 * Supplies real statistics, charts, activity, and pending approvals
 * for templates/admin/dashboard.php without changing the frozen UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Admin_Dashboard
 */
class GMM_Admin_Dashboard {

	const CACHE_GROUP = 'gmm_admin_dashboard';
	const CACHE_TTL   = 120;

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 20, 2 );
		$loader->add_filter( 'gmm_admin_page_args', $instance, 'inject_admin_page_args', 20, 2 );
		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 30 );
		$loader->add_action( 'admin_enqueue_scripts', $instance, 'maybe_enqueue_admin_assets', 30 );
	}

	/**
	 * Inject dashboard payload into [gmm_admin_dashboard].
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Shortcode.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_admin_dashboard' !== $tag ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * Inject dashboard payload into wp-admin GMM dashboard screen.
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $page Page key.
	 * @return array<string, mixed>
	 */
	public function inject_admin_page_args( $args, $page ) {
		if ( 'dashboard' !== $page ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * Whether current user may view the admin dashboard.
	 *
	 * @return bool
	 */
	public static function user_can_view() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Full template variable set (or access-denied flag).
	 *
	 * @return array<string, mixed>
	 */
	public static function get_template_vars() {
		if ( ! self::user_can_view() ) {
			return array(
				'gmm_admin_denied' => true,
				'stats'            => self::empty_stats(),
				'charts'           => array(),
				'activity'         => array(),
				'approvals'        => array(),
				'booking_analytics'=> self::empty_booking_analytics(),
			);
		}

		$stats    = self::get_statistics();
		$bookings = self::get_booking_analytics();

		return array(
			'gmm_admin_denied'  => false,
			'stats'             => $stats,
			'charts'            => array(
				'revenue'    => gmm_get_admin_revenue_chart(),
				'growth'     => gmm_get_user_growth_chart(),
				'platform'   => gmm_get_platform_distribution(),
				'bookings'   => isset( $bookings['chart'] ) ? $bookings['chart'] : array(),
			),
			'activity'          => gmm_get_recent_activity( 8 ),
			'approvals'         => self::get_pending_approvals( 20 ),
			'booking_analytics' => $bookings,
			'last_login_label'  => self::format_last_login(),
			'logout_url'        => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
			'notifications'     => self::build_notification_items( $stats, gmm_get_recent_activity( 4 ) ),
		);
	}

	/**
	 * Cached admin statistics.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_statistics() {
		$cached = self::cache_get( 'stats' );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		if ( function_exists( 'gmm_get_admin_statistics' ) ) {
			$result = gmm_get_admin_statistics();
			if ( ! is_wp_error( $result ) && is_array( $result ) ) {
				self::cache_set( 'stats', $result );
				return $result;
			}
		}

		$data = self::empty_stats();
		self::cache_set( 'stats', $data );
		return $data;
	}

	/**
	 * Booking status breakdown for the donut + list.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_booking_analytics() {
		$cached = self::cache_get( 'bookings' );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		if ( ! self::user_can_view() ) {
			return self::empty_booking_analytics();
		}

		global $wpdb;
		$table = GMM_Database::table( 'bookings' );

		$completed = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE booking_status IN (%s,%s)", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				'completed',
				'confirmed'
			)
		);
		$pending = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE booking_status = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				'pending'
			)
		);
		$cancelled = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE booking_status = %s", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				'cancelled'
			)
		);

		$total = $completed + $pending + $cancelled;
		$pct   = function ( $n ) use ( $total ) {
			return $total > 0 ? (int) round( ( $n / $total ) * 100 ) : 0;
		};

		$p_completed = $pct( $completed );
		$p_pending   = $pct( $pending );
		$p_cancelled = max( 0, 100 - $p_completed - $p_pending );

		$data = array(
			'total'             => $total,
			'completed'         => $completed,
			'pending'           => $pending,
			'cancelled'         => $cancelled,
			'pct_completed'     => $p_completed,
			'pct_pending'       => $p_pending,
			'pct_cancelled'     => $p_cancelled,
			'gradient_stops'    => array(
				'completed_end' => $p_completed,
				'pending_end'   => $p_completed + $p_pending,
			),
			'chart'             => array(
				'labels'   => array(
					__( 'Completed', 'gospel-music-mastery' ),
					__( 'Pending', 'gospel-music-mastery' ),
					__( 'Cancelled', 'gospel-music-mastery' ),
				),
				'datasets' => array(
					array(
						'label' => __( 'Bookings', 'gospel-music-mastery' ),
						'data'  => array( $completed, $pending, $cancelled ),
					),
				),
			),
		);

		self::cache_set( 'bookings', $data );
		return $data;
	}

	/**
	 * Pending teacher applications + class submissions.
	 *
	 * @param int $limit Max rows.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_pending_approvals( $limit = 20 ) {
		$limit = max( 1, min( 50, absint( $limit ) ) );
		$key   = 'approvals_' . $limit;
		$cached = self::cache_get( $key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		if ( ! self::user_can_view() ) {
			return array();
		}

		global $wpdb;
		$teachers = GMM_Database::table( 'teachers' );
		$classes  = GMM_Database::table( 'classes' );

		$teacher_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, first_name, last_name, profile_image, created_at, status
				FROM {$teachers}
				WHERE status = %s
				ORDER BY created_at DESC
				LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				'pending',
				$limit
			),
			ARRAY_A
		);

		$class_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, title, image, created_at, status
				FROM {$classes}
				WHERE status IN (%s,%s)
				ORDER BY created_at DESC
				LIMIT %d", // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
				'pending',
				'draft',
				$limit
			),
			ARRAY_A
		);

		$items = array();

		if ( is_array( $teacher_rows ) ) {
			foreach ( $teacher_rows as $row ) {
				$name = trim( (string) $row['first_name'] . ' ' . (string) $row['last_name'] );
				$items[] = array(
					'type'       => 'teacher',
					'id'         => absint( $row['id'] ),
					'name'       => $name ? $name : __( 'Teacher', 'gospel-music-mastery' ),
					'image'      => self::resolve_image( isset( $row['profile_image'] ) ? $row['profile_image'] : '', 'assets/images/team/01.jpg' ),
					'date_label' => self::relative_date( $row['created_at'] ),
					'type_label' => __( 'Teacher Application', 'gospel-music-mastery' ),
					'view_url'   => gmm_get_page_link( 'admin_teachers' ),
					'sort'       => strtotime( (string) $row['created_at'] ),
				);
			}
		}

		if ( is_array( $class_rows ) ) {
			foreach ( $class_rows as $row ) {
				$items[] = array(
					'type'       => 'class',
					'id'         => absint( $row['id'] ),
					'name'       => $row['title'] ? (string) $row['title'] : __( 'Class', 'gospel-music-mastery' ),
					'image'      => self::resolve_image( isset( $row['image'] ) ? $row['image'] : '', 'assets/images/course/01.jpg' ),
					'date_label' => self::relative_date( $row['created_at'] ),
					'type_label' => __( 'New Class', 'gospel-music-mastery' ),
					'view_url'   => gmm_get_page_link( 'admin_classes' ),
					'sort'       => strtotime( (string) $row['created_at'] ),
				);
			}
		}

		usort(
			$items,
			static function ( $a, $b ) {
				return (int) $b['sort'] - (int) $a['sort'];
			}
		);

		$items = array_slice( $items, 0, $limit );
		self::cache_set( $key, $items );
		return $items;
	}

	/**
	 * Clear dashboard caches after approval actions.
	 *
	 * @return void
	 */
	public static function flush_cache() {
		$keys = array( 'stats', 'bookings', 'activity', 'approvals_20', 'revenue_chart', 'growth_chart', 'platform_chart' );
		foreach ( $keys as $key ) {
			delete_transient( self::CACHE_GROUP . '_' . $key );
		}
	}

	/**
	 * Enqueue chart + dashboard interaction scripts on front GMM admin dashboard pages.
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
		// Only when admin dashboard shortcode is present.
		$post = get_queried_object();
		if ( ! $post instanceof WP_Post || ! has_shortcode( (string) $post->post_content, 'gmm_admin_dashboard' ) ) {
			if ( false === strpos( (string) ( $post instanceof WP_Post ? $post->post_content : '' ), 'gmm_admin_dashboard' ) ) {
				return;
			}
		}
		self::enqueue_dashboard_assets();
	}

	/**
	 * Enqueue on wp-admin GMM dashboard screen.
	 *
	 * @param string $hook Hook.
	 * @return void
	 */
	public function maybe_enqueue_admin_assets( $hook = '' ) {
		if ( ! self::user_can_view() ) {
			return;
		}
		if ( ! function_exists( 'gmm_is_plugin_admin_page' ) || ! gmm_is_plugin_admin_page( $hook ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( 'gmm-dashboard' !== $page && false === strpos( (string) $hook, 'gmm-dashboard' ) ) {
			return;
		}
		self::enqueue_dashboard_assets();
	}

	/**
	 * Register/enqueue Chart.js + admin dashboard scripts with localized data.
	 *
	 * @return void
	 */
	public static function enqueue_dashboard_assets() {
		$version = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';
		$js      = GMM_URL . 'assets/js/';

		wp_enqueue_script(
			'gmm-chartjs',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
			array(),
			'4.4.1',
			true
		);

		wp_enqueue_script(
			'gmm-dashboard-charts',
			$js . 'dashboard-charts.js',
			array( 'gmm-chartjs' ),
			$version,
			true
		);

		wp_enqueue_script(
			'gmm-admin-dashboard',
			$js . 'gmm-admin-dashboard.js',
			array( 'gmm-core-script', 'gmm-ajax-script', 'gmm-dashboard-charts' ),
			$version,
			true
		);

		$vars = self::get_template_vars();
		wp_localize_script(
			'gmm-admin-dashboard',
			'GMM_ADMIN_DASH',
			array(
				'nonce'   => wp_create_nonce( 'gmm_nonce' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'charts'  => isset( $vars['charts'] ) ? $vars['charts'] : array(),
				'i18n'    => array(
					'approved' => __( 'approved.', 'gospel-music-mastery' ),
					'rejected' => __( 'rejected.', 'gospel-music-mastery' ),
					'error'    => __( 'Action failed. Please try again.', 'gospel-music-mastery' ),
				),
			)
		);
	}

	/**
	 * @return array<string, int|float>
	 */
	private static function empty_stats() {
		return array(
			'total_students'    => 0,
			'total_teachers'    => 0,
			'total_classes'     => 0,
			'total_bookings'    => 0,
			'total_revenue'     => 0,
			'pending_approvals' => 0,
			'pending_teachers'  => 0,
			'pending_classes'   => 0,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function empty_booking_analytics() {
		return array(
			'total'          => 0,
			'completed'      => 0,
			'pending'        => 0,
			'cancelled'      => 0,
			'pct_completed'  => 0,
			'pct_pending'    => 0,
			'pct_cancelled'  => 0,
			'gradient_stops' => array(
				'completed_end' => 0,
				'pending_end'   => 0,
			),
			'chart'          => array(
				'labels'   => array(),
				'datasets' => array(),
			),
		);
	}

	/**
	 * @param string $raw Attachment ID or path.
	 * @param string $fallback Relative design path.
	 * @return string URL.
	 */
	private static function resolve_image( $raw, $fallback ) {
		$raw = is_string( $raw ) ? trim( $raw ) : '';
		if ( $raw && ctype_digit( $raw ) ) {
			$url = wp_get_attachment_image_url( absint( $raw ), 'thumbnail' );
			if ( $url ) {
				return $url;
			}
		}
		if ( $raw && filter_var( $raw, FILTER_VALIDATE_URL ) ) {
			return esc_url_raw( $raw );
		}
		return function_exists( 'gmm_design_asset_url' ) ? gmm_design_asset_url( $fallback ) : '';
	}

	/**
	 * @param string $datetime MySQL datetime.
	 * @return string
	 */
	private static function relative_date( $datetime ) {
		$ts = strtotime( (string) $datetime );
		if ( ! $ts ) {
			return __( 'Recently', 'gospel-music-mastery' );
		}
		return human_time_diff( $ts, current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'gospel-music-mastery' );
	}

	/**
	 * @return string
	 */
	private static function format_last_login() {
		$user_id = get_current_user_id();
		$meta    = $user_id ? get_user_meta( $user_id, 'gmm_last_login', true ) : '';
		if ( $meta ) {
			$ts = strtotime( (string) $meta );
			if ( $ts ) {
				return sprintf(
					/* translators: %s: relative time */
					__( 'Last login: %s', 'gospel-music-mastery' ),
					human_time_diff( $ts, current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'gospel-music-mastery' )
				);
			}
		}
		return __( 'Last login: Today', 'gospel-music-mastery' );
	}

	/**
	 * @param array<string, mixed>         $stats    Stats.
	 * @param array<int, array<string, mixed>> $activity Activity.
	 * @return array<int, array<string, string>>
	 */
	private static function build_notification_items( $stats, $activity ) {
		$items = array();
		$pending = isset( $stats['pending_approvals'] ) ? absint( $stats['pending_approvals'] ) : 0;
		if ( $pending > 0 ) {
			$items[] = array(
				'icon' => 'far fa-triangle-exclamation',
				'html' => sprintf(
					/* translators: %d: pending count */
					__( '%d approvals are pending', 'gospel-music-mastery' ),
					$pending
				),
				'url'  => gmm_get_page_link( 'admin_teachers' ),
			);
		}
		if ( is_array( $activity ) ) {
			foreach ( array_slice( $activity, 0, 3 ) as $row ) {
				$items[] = array(
					'icon' => isset( $row['icon'] ) ? $row['icon'] : 'far fa-bell',
					'html' => isset( $row['title'] ) ? wp_strip_all_tags( (string) $row['title'] ) : '',
					'url'  => isset( $row['url'] ) ? $row['url'] : gmm_get_page_link( 'admin_dashboard' ),
				);
			}
		}
		return $items;
	}

	/**
	 * @param string $key Cache key.
	 * @return mixed
	 */
	private static function cache_get( $key ) {
		return get_transient( self::CACHE_GROUP . '_' . sanitize_key( $key ) );
	}

	/**
	 * @param string $key  Cache key.
	 * @param mixed  $data Data.
	 * @return void
	 */
	private static function cache_set( $key, $data ) {
		set_transient( self::CACHE_GROUP . '_' . sanitize_key( $key ), $data, self::CACHE_TTL );
	}
}
