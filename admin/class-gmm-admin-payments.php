<?php
/**
 * Admin payment management controller.
 *
 * Supplies list/search/filter/pagination, revenue, commission, and status
 * actions for templates/admin/payments.php without changing the frozen UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Admin_Payments
 */
class GMM_Admin_Payments {

	const PER_PAGE     = 8;
	const CACHE_GROUP  = 'gmm_admin_payments';
	const CACHE_TTL    = 60;
	const SPLITS_OPTION = 'gmm_payment_splits';
	const REFUNDS_OPTION = 'gmm_refund_requests';

	/**
	 * @param GMM_Loader $loader Loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 20, 2 );
		$loader->add_filter( 'gmm_admin_page_args', $instance, 'inject_admin_page_args', 20, 2 );
		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 30 );
		$loader->add_action( 'admin_enqueue_scripts', $instance, 'maybe_enqueue_admin_assets', 30 );
		$loader->add_action( 'gmm_payment_completed', $instance, 'persist_split_on_completed', 20, 2 );
	}

	/**
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Tag.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_admin_payments' !== $tag ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * @param array<string, mixed> $args Args.
	 * @param string               $page Page.
	 * @return array<string, mixed>
	 */
	public function inject_admin_page_args( $args, $page ) {
		if ( 'payments' !== $page ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * @return bool
	 */
	public static function user_can_manage() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_template_vars() {
		if ( ! self::user_can_manage() ) {
			return array(
				'gmm_admin_denied'      => true,
				'payments'              => array(),
				'payment_stats'         => self::empty_revenue(),
				'filters'               => self::empty_filters(),
				'pagination'            => self::empty_pagination(),
				'refund_requests'       => array(),
				'teacher_earnings_list' => array(),
				'chart_data'            => array(),
			);
		}

		$filters = self::get_request_filters();
		$list    = self::list_payments( $filters );
		$revenue = self::get_revenue();

		return array(
			'gmm_admin_denied'      => false,
			'payments'              => isset( $list['items'] ) ? $list['items'] : array(),
			'payment_stats'         => $revenue,
			'filters'               => $filters,
			'pagination'            => array(
				'page'        => isset( $list['page'] ) ? absint( $list['page'] ) : 1,
				'per_page'    => isset( $list['per_page'] ) ? absint( $list['per_page'] ) : self::PER_PAGE,
				'total'       => isset( $list['total'] ) ? absint( $list['total'] ) : 0,
				'total_pages' => isset( $list['total_pages'] ) ? absint( $list['total_pages'] ) : 0,
				'has_prev'    => ! empty( $list['has_prev'] ),
				'has_next'    => ! empty( $list['has_next'] ),
				'prev_page'   => isset( $list['prev_page'] ) ? $list['prev_page'] : null,
				'next_page'   => isset( $list['next_page'] ) ? $list['next_page'] : null,
			),
			'refund_requests'       => self::get_refund_requests(),
			'teacher_earnings_list' => self::get_teacher_earnings_overview( 12 ),
			'chart_data'            => self::get_analytics_payload(),
			'logout_url'            => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
			'last_login_label'      => self::format_last_login(),
			'export_ready'          => true,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_request_filters() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search = isset( $_GET['ap_search'] ) ? sanitize_text_field( wp_unslash( $_GET['ap_search'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = isset( $_GET['ap_status'] ) ? sanitize_key( wp_unslash( $_GET['ap_status'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$type = isset( $_GET['ap_type'] ) ? sanitize_key( wp_unslash( $_GET['ap_type'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$method = isset( $_GET['ap_method'] ) ? sanitize_key( wp_unslash( $_GET['ap_method'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$period = isset( $_GET['ap_date'] ) ? sanitize_key( wp_unslash( $_GET['ap_date'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$date_from = isset( $_GET['ap_date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['ap_date_from'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$date_to = isset( $_GET['ap_date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['ap_date_to'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['ap_page'] ) ? absint( $_GET['ap_page'] ) : 1;

		if ( ! in_array( $status, array( 'all', 'pending', 'completed', 'failed', 'refunded' ), true ) ) {
			$status = 'all';
		}
		if ( ! in_array( $type, array( 'all', 'lesson', 'payout', 'refund' ), true ) ) {
			$type = 'all';
		}
		if ( ! in_array( $method, array( 'all', 'stripe', 'paypal', 'manual' ), true ) ) {
			$method = 'all';
		}
		if ( ! in_array( $period, array( 'all', 'today', 'week', 'month', 'custom' ), true ) ) {
			$period = 'all';
		}
		if ( $date_from || $date_to ) {
			$period = 'custom';
		}

		return array(
			'search'    => $search,
			'status'    => $status,
			'type'      => $type,
			'method'    => $method,
			'period'    => $period,
			'date_from' => $date_from,
			'date_to'   => $date_to,
			'page'      => max( 1, $page ),
			'per_page'  => self::PER_PAGE,
		);
	}

	/**
	 * @param array<string, mixed> $args Args.
	 * @return array<string, mixed>
	 */
	public static function list_payments( $args = array() ) {
		if ( ! self::user_can_manage() ) {
			return self::empty_list();
		}

		$args = wp_parse_args(
			$args,
			array(
				'search'    => '',
				'status'    => 'all',
				'type'      => 'all',
				'method'    => 'all',
				'period'    => 'all',
				'date_from' => '',
				'date_to'   => '',
				'page'      => 1,
				'per_page'  => self::PER_PAGE,
			)
		);

		$page     = max( 1, absint( $args['page'] ) );
		$per_page = max( 1, min( 50, absint( $args['per_page'] ) ) );
		$offset   = ( $page - 1 ) * $per_page;

		global $wpdb;
		$payments = GMM_Database::table( 'payments' );
		$students = GMM_Database::table( 'students' );
		$teachers = GMM_Database::table( 'teachers' );
		$bookings = GMM_Database::table( 'bookings' );

		$where  = array( '1=1' );
		$params = array();

		$search = trim( (string) $args['search'] );
		if ( '' !== $search ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$id_q = absint( preg_replace( '/\D+/', '', $search ) );
			$where[]  = '(p.transaction_id LIKE %s OR CONCAT(\'TXN-\', p.id) LIKE %s OR CAST(p.id AS CHAR) LIKE %s OR CAST(p.booking_id AS CHAR) LIKE %s OR CONCAT(\'BK-\', p.booking_id) LIKE %s OR s.first_name LIKE %s OR s.last_name LIKE %s OR CONCAT(s.first_name, \' \', s.last_name) LIKE %s OR t.first_name LIKE %s OR t.last_name LIKE %s OR CONCAT(t.first_name, \' \', t.last_name) LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			if ( $id_q ) {
				$where[ count( $where ) - 1 ] = '(' . $where[ count( $where ) - 1 ] . ' OR p.id = %d OR p.booking_id = %d)';
				$params[] = $id_q;
				$params[] = $id_q;
			}
		}

		$status = sanitize_key( (string) $args['status'] );
		if ( $status && 'all' !== $status ) {
			$where[]  = 'p.payment_status = %s';
			$params[] = $status;
		}

		$type = sanitize_key( (string) $args['type'] );
		if ( 'lesson' === $type ) {
			$where[]  = 'p.payment_method <> %s AND p.payment_status <> %s';
			$params[] = 'withdrawal';
			$params[] = 'refunded';
		} elseif ( 'payout' === $type ) {
			$where[]  = 'p.payment_method = %s';
			$params[] = 'withdrawal';
		} elseif ( 'refund' === $type ) {
			$where[]  = 'p.payment_status = %s';
			$params[] = 'refunded';
		}

		$method = sanitize_key( (string) $args['method'] );
		if ( $method && 'all' !== $method ) {
			$where[]  = 'LOWER(p.payment_method) = %s';
			$params[] = $method;
		}

		self::append_period_filter( $where, $params, $args );

		$where_sql = implode( ' AND ', $where );

		$count_sql = "SELECT COUNT(*) FROM {$payments} p
			LEFT JOIN {$students} s ON s.id = p.student_id
			LEFT JOIN {$teachers} t ON t.id = p.teacher_id
			WHERE {$where_sql}";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) );

		$sql = "SELECT p.*,
			s.first_name AS student_first_name, s.last_name AS student_last_name, s.email AS student_email, s.profile_image AS student_image,
			t.first_name AS teacher_first_name, t.last_name AS teacher_last_name, t.email AS teacher_email, t.profile_image AS teacher_image,
			b.booking_date, b.booking_time, b.booking_status
			FROM {$payments} p
			LEFT JOIN {$students} s ON s.id = p.student_id
			LEFT JOIN {$teachers} t ON t.id = p.teacher_id
			LEFT JOIN {$bookings} b ON b.id = p.booking_id
			WHERE {$where_sql}
			ORDER BY p.created_at DESC, p.id DESC
			LIMIT %d OFFSET %d";

		$qparams   = $params;
		$qparams[] = $per_page;
		$qparams[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $qparams ), ARRAY_A );
		$rows = is_array( $rows ) ? $rows : array();

		$items = array();
		foreach ( $rows as $row ) {
			$items[] = self::format_payment_row( $row );
		}

		$total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 0;

		return array(
			'items'       => $items,
			'total'       => $total,
			'page'        => $page,
			'per_page'    => $per_page,
			'total_pages' => $total_pages,
			'has_prev'    => $page > 1,
			'has_next'    => $page < $total_pages,
			'prev_page'   => $page > 1 ? $page - 1 : null,
			'next_page'   => $page < $total_pages ? $page + 1 : null,
		);
	}

	/**
	 * Revenue summary for admin (also used as gmm_get_admin_revenue).
	 *
	 * @return array<string, mixed>
	 */
	public static function get_revenue() {
		if ( ! self::user_can_manage() ) {
			return self::empty_revenue();
		}

		$cached = get_transient( self::CACHE_GROUP . '_revenue' );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;
		$table = GMM_Database::table( 'payments' );

		$total_revenue = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$table}
				WHERE payment_status = %s AND payment_method <> %s",
				'completed',
				'withdrawal'
			)
		);

		$pending_payments = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$table}
				WHERE payment_status = %s AND payment_method <> %s",
				'pending',
				'withdrawal'
			)
		);

		$refund_amount = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$table} WHERE payment_status = %s",
				'refunded'
			)
		);

		$completed_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table}
				WHERE payment_status = %s AND payment_method <> %s",
				'completed',
				'withdrawal'
			)
		);

		$refund_count = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE payment_status = %s", 'refunded' )
		);

		$split = class_exists( 'GMM_Payment' )
			? GMM_Payment::calculate_split( $total_revenue )
			: array(
				'commission'       => 0,
				'teacher_earnings' => $total_revenue,
				'commission_percent' => 10,
			);

		$pending_split = class_exists( 'GMM_Payment' )
			? GMM_Payment::calculate_split( $pending_payments )
			: array( 'teacher_earnings' => $pending_payments );

		$revenue = array(
			'total_revenue'       => round( $total_revenue, 2 ),
			'completed_payments'  => round( $total_revenue, 2 ),
			'pending_payments'    => round( $pending_payments, 2 ),
			'refund_amount'       => round( $refund_amount, 2 ),
			'platform_commission' => round( (float) $split['commission'], 2 ),
			'teacher_earnings'    => round( (float) $split['teacher_earnings'], 2 ),
			'pending_payouts'     => round( (float) $pending_split['teacher_earnings'], 2 ),
			'completed_count'     => $completed_count,
			'refund_count'        => $refund_count,
			'commission_percent'  => isset( $split['commission_percent'] ) ? (float) $split['commission_percent'] : self::get_commission_percent(),
		);

		/**
		 * Filter admin revenue payload (charts / dashboard).
		 *
		 * @param array<string, mixed> $revenue Revenue.
		 */
		$revenue = apply_filters( 'gmm_admin_revenue', $revenue );

		set_transient( self::CACHE_GROUP . '_revenue', $revenue, self::CACHE_TTL );
		return $revenue;
	}

	/**
	 * @return float
	 */
	public static function get_commission_percent() {
		if ( function_exists( 'gmm_get_commission_percent' ) ) {
			return (float) gmm_get_commission_percent();
		}
		if ( class_exists( 'GMM_Payment' ) ) {
			return (float) GMM_Payment::get_commission_percent();
		}
		$opts = get_option( 'gmm_commission_settings', array() );
		return isset( $opts['commission_percent'] ) ? (float) $opts['commission_percent'] : 10.0;
	}

	/**
	 * Persist commission split safely (option store; no schema change).
	 *
	 * @param float $amount Gross.
	 * @param int   $payment_id Optional payment ID to save against.
	 * @return array<string, mixed>
	 */
	public static function calculate_and_save_split( $amount, $payment_id = 0 ) {
		$split = class_exists( 'GMM_Payment' )
			? GMM_Payment::calculate_split( $amount )
			: array(
				'gross'              => round( (float) $amount, 2 ),
				'commission_percent' => self::get_commission_percent(),
				'commission'         => 0,
				'teacher_earnings'   => round( (float) $amount, 2 ),
			);

		$payment_id = absint( $payment_id );
		if ( $payment_id ) {
			$all = get_option( self::SPLITS_OPTION, array() );
			if ( ! is_array( $all ) ) {
				$all = array();
			}
			$all[ $payment_id ] = array(
				'gross'              => $split['gross'],
				'commission_percent' => $split['commission_percent'],
				'commission'         => $split['commission'],
				'teacher_earnings'   => $split['teacher_earnings'],
				'saved_at'           => current_time( 'mysql' ),
			);
			update_option( self::SPLITS_OPTION, $all, false );
		}

		return $split;
	}

	/**
	 * @param int                  $payment_id Payment ID.
	 * @param array<string, mixed> $row        Payment row.
	 * @return void
	 */
	public function persist_split_on_completed( $payment_id, $row ) {
		$amount = is_array( $row ) && isset( $row['amount'] ) ? (float) $row['amount'] : 0;
		self::calculate_and_save_split( $amount, absint( $payment_id ) );
		self::flush_cache();
	}

	/**
	 * Teacher earnings overview for admin (connects with teacher dashboard math).
	 *
	 * @param int $limit Limit.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_teacher_earnings_overview( $limit = 12 ) {
		if ( ! self::user_can_manage() ) {
			return array();
		}

		$limit = max( 1, min( 50, absint( $limit ) ) );

		global $wpdb;
		$payments = GMM_Database::table( 'payments' );
		$teachers = GMM_Database::table( 'teachers' );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT t.id, t.user_id, t.first_name, t.last_name, t.profile_image,
					COALESCE(SUM(CASE WHEN p.payment_status = 'completed' AND p.payment_method <> 'withdrawal' THEN p.amount ELSE 0 END),0) AS completed_gross,
					COALESCE(SUM(CASE WHEN p.payment_status = 'pending' AND p.payment_method <> 'withdrawal' THEN p.amount ELSE 0 END),0) AS pending_gross
				FROM {$teachers} t
				INNER JOIN {$payments} p ON p.teacher_id = t.id
				GROUP BY t.id
				ORDER BY completed_gross DESC
				LIMIT %d",
				$limit
			),
			ARRAY_A
		);

		$rows = is_array( $rows ) ? $rows : array();
		$out  = array();

		foreach ( $rows as $row ) {
			$completed_split = self::calculate_and_save_split( (float) $row['completed_gross'] );
			$pending_split   = self::calculate_and_save_split( (float) $row['pending_gross'] );
			$name            = trim( (string) $row['first_name'] . ' ' . (string) $row['last_name'] );
			$paid            = (float) $completed_split['teacher_earnings'];
			$pending         = (float) $pending_split['teacher_earnings'];

			$out[] = array(
				'teacher_id'      => absint( $row['id'] ),
				'user_id'         => absint( $row['user_id'] ),
				'name'            => $name ? $name : __( 'Teacher', 'gospel-music-mastery' ),
				'total_earnings'  => round( $paid + $pending, 2 ),
				'paid_earnings'   => round( $paid, 2 ),
				'pending_earnings'=> round( $pending, 2 ),
				'image'           => self::resolve_image( isset( $row['profile_image'] ) ? (string) $row['profile_image'] : '', 'assets/img/team/01.jpg' ),
			);
		}

		return $out;
	}

	/**
	 * @param int $payment_id Payment ID.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function get_profile( $payment_id ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$payment_id = absint( $payment_id );
		if ( ! $payment_id ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid payment.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$payments = GMM_Database::table( 'payments' );
		$students = GMM_Database::table( 'students' );
		$teachers = GMM_Database::table( 'teachers' );
		$bookings = GMM_Database::table( 'bookings' );
		$classes  = GMM_Database::table( 'classes' );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT p.*,
					s.first_name AS student_first_name, s.last_name AS student_last_name, s.email AS student_email, s.phone AS student_phone, s.profile_image AS student_image,
					t.first_name AS teacher_first_name, t.last_name AS teacher_last_name, t.email AS teacher_email, t.phone AS teacher_phone, t.profile_image AS teacher_image,
					b.booking_date, b.booking_time, b.booking_status, b.amount AS booking_amount, b.class_id,
					c.title AS class_title
				FROM {$payments} p
				LEFT JOIN {$students} s ON s.id = p.student_id
				LEFT JOIN {$teachers} t ON t.id = p.teacher_id
				LEFT JOIN {$bookings} b ON b.id = p.booking_id
				LEFT JOIN {$classes} c ON c.id = b.class_id
				WHERE p.id = %d LIMIT 1",
				$payment_id
			),
			ARRAY_A
		);

		if ( ! is_array( $row ) ) {
			return new WP_Error( 'gmm_missing', __( 'Payment not found.', 'gospel-music-mastery' ) );
		}

		$formatted = self::format_payment_row( $row );
		$split     = self::get_saved_split( $payment_id, (float) $row['amount'] );

		$timeline = array(
			array(
				'label' => __( 'Payment created', 'gospel-music-mastery' ),
				'time'  => isset( $row['created_at'] ) ? $row['created_at'] : '',
			),
			array(
				'label' => sprintf(
					/* translators: %s: status */
					__( 'Current status: %s', 'gospel-music-mastery' ),
					isset( $row['payment_status'] ) ? $row['payment_status'] : ''
				),
				'time'  => current_time( 'mysql' ),
			),
			array(
				'label' => sprintf(
					/* translators: 1: commission 2: teacher earnings */
					__( 'Commission $%1$s · Teacher $%2$s', 'gospel-music-mastery' ),
					number_format_i18n( (float) $split['commission'], 2 ),
					number_format_i18n( (float) $split['teacher_earnings'], 2 )
				),
				'time'  => '',
			),
		);

		return array(
			'payment'   => $formatted,
			'student'   => array(
				'id'    => absint( $row['student_id'] ),
				'name'  => $formatted['student'],
				'email' => isset( $row['student_email'] ) ? $row['student_email'] : '',
				'phone' => isset( $row['student_phone'] ) ? $row['student_phone'] : '',
				'image' => $formatted['student_image'],
			),
			'teacher'   => array(
				'id'    => absint( $row['teacher_id'] ),
				'name'  => $formatted['teacher'],
				'email' => isset( $row['teacher_email'] ) ? $row['teacher_email'] : '',
				'phone' => isset( $row['teacher_phone'] ) ? $row['teacher_phone'] : '',
				'image' => $formatted['teacher_image'],
			),
			'booking'   => array(
				'id'     => absint( $row['booking_id'] ),
				'code'   => $formatted['booking_code'],
				'date'   => isset( $row['booking_date'] ) ? $row['booking_date'] : '',
				'time'   => isset( $row['booking_time'] ) ? $row['booking_time'] : '',
				'status' => isset( $row['booking_status'] ) ? $row['booking_status'] : '',
				'class'  => isset( $row['class_title'] ) ? $row['class_title'] : '',
			),
			'split'     => $split,
			'timeline'  => $timeline,
			'formatted' => $formatted,
		);
	}

	/**
	 * @param int    $payment_id Payment ID.
	 * @param string $status     Status.
	 * @return true|WP_Error
	 */
	public static function set_status( $payment_id, $status ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$payment_id = absint( $payment_id );
		$status     = sanitize_key( $status );

		if ( 'paid' === $status ) {
			$status = 'completed';
		}

		if ( ! $payment_id || ! in_array( $status, array( 'pending', 'completed', 'failed', 'refunded' ), true ) ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid payment or status.', 'gospel-music-mastery' ) );
		}

		if ( ! class_exists( 'GMM_Payment' ) ) {
			return new WP_Error( 'gmm_missing', __( 'Payment system unavailable.', 'gospel-music-mastery' ) );
		}

		$result = GMM_Payment::update_status( $payment_id, $status, '' );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$row = GMM_Payment::admin_get_transaction( $payment_id );
		if ( is_array( $row ) ) {
			self::calculate_and_save_split( isset( $row['amount'] ) ? (float) $row['amount'] : 0, $payment_id );

			// Keep linked booking.payment_status in sync (direct update — avoid nested payment AJAX).
			$booking_id = isset( $row['booking_id'] ) ? absint( $row['booking_id'] ) : 0;
			if ( $booking_id ) {
				$map = array(
					'completed' => 'paid',
					'pending'   => 'pending',
					'failed'    => 'failed',
					'refunded'  => 'refunded',
				);
				if ( isset( $map[ $status ] ) ) {
					global $wpdb;
					$bt = GMM_Database::table( 'bookings' );
					$wpdb->update(
						$bt,
						array(
							'payment_status' => $map[ $status ],
							'updated_at'     => current_time( 'mysql' ),
						),
						array( 'id' => $booking_id ),
						array( '%s', '%s' ),
						array( '%d' )
					);
				}
			}
		}

		/**
		 * Fires after admin updates payment status.
		 *
		 * @param int    $payment_id Payment ID.
		 * @param string $status     Status.
		 */
		do_action( 'gmm_admin_payment_status_updated', $payment_id, $status );

		self::flush_cache();
		return true;
	}

	/**
	 * Mark completed.
	 *
	 * @param int $payment_id ID.
	 * @return true|WP_Error
	 */
	public static function mark_completed( $payment_id ) {
		return self::set_status( $payment_id, 'completed' );
	}

	/**
	 * Mark failed.
	 *
	 * @param int $payment_id ID.
	 * @return true|WP_Error
	 */
	public static function mark_failed( $payment_id ) {
		return self::set_status( $payment_id, 'failed' );
	}

	/**
	 * Mark refunded (status only — no gateway).
	 *
	 * @param int $payment_id ID.
	 * @return true|WP_Error
	 */
	public static function mark_refunded( $payment_id ) {
		return self::set_status( $payment_id, 'refunded' );
	}

	/**
	 * Approve refund request + mark payment refunded (no gateway).
	 *
	 * @param int $payment_id Payment ID.
	 * @param int $request_index Optional request index.
	 * @return true|WP_Error
	 */
	public static function approve_refund( $payment_id, $request_index = -1 ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$payment_id = absint( $payment_id );
		$result     = class_exists( 'GMM_Payment' )
			? GMM_Payment::process_refund( $payment_id, '' )
			: self::mark_refunded( $payment_id );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		self::update_refund_request_status( $payment_id, 'approved', $request_index );
		do_action( 'gmm_admin_refund_approved', $payment_id );
		self::flush_cache();
		return true;
	}

	/**
	 * Reject refund request (no gateway).
	 *
	 * @param int $payment_id Payment ID.
	 * @param int $request_index Index.
	 * @return true|WP_Error
	 */
	public static function reject_refund( $payment_id, $request_index = -1 ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$payment_id = absint( $payment_id );
		self::update_refund_request_status( $payment_id, 'rejected', $request_index );
		do_action( 'gmm_admin_refund_rejected', $payment_id );
		return true;
	}

	/**
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_refund_requests() {
		$raw = get_option( self::REFUNDS_OPTION, array() );
		if ( ! is_array( $raw ) ) {
			return array();
		}

		$out = array();
		foreach ( $raw as $index => $req ) {
			if ( ! is_array( $req ) ) {
				continue;
			}
			$status = isset( $req['status'] ) ? sanitize_key( (string) $req['status'] ) : 'requested';
			if ( ! in_array( $status, array( 'requested', 'pending' ), true ) ) {
				continue;
			}

			$payment_id = isset( $req['payment_id'] ) ? absint( $req['payment_id'] ) : 0;
			$user_name  = __( 'Student', 'gospel-music-mastery' );
			$user_img   = self::resolve_image( '', 'assets/img/team/02.jpg' );

			if ( $payment_id ) {
				$profile = self::get_profile( $payment_id );
				if ( ! is_wp_error( $profile ) && is_array( $profile ) ) {
					$user_name = isset( $profile['student']['name'] ) ? $profile['student']['name'] : $user_name;
					$user_img  = isset( $profile['student']['image'] ) ? $profile['student']['image'] : $user_img;
				}
			}

			$amount = isset( $req['amount'] ) ? (float) $req['amount'] : 0;
			$out[]  = array(
				'index'      => (int) $index,
				'id'         => 'RF-' . ( $payment_id ? $payment_id : ( $index + 1 ) ),
				'payment_id' => $payment_id,
				'user'       => $user_name,
				'user_image' => $user_img,
				'amount'     => $amount,
				'amount_label' => '$' . number_format_i18n( $amount, 0 ),
				'reason'     => isset( $req['reason'] ) ? (string) $req['reason'] : '',
				'status'     => 'pending',
				'status_label' => __( 'Pending', 'gospel-music-mastery' ),
			);
		}

		return array_slice( $out, 0, 20 );
	}

	/**
	 * Analytics payload for charts / dashboard.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_analytics_payload() {
		$revenue = self::get_revenue();
		$chart   = function_exists( 'gmm_get_admin_revenue_chart' ) ? gmm_get_admin_revenue_chart() : array();

		$payload = array(
			'revenue'       => $revenue,
			'revenue_chart' => $chart,
			'status_counts' => array(
				'completed' => isset( $revenue['completed_count'] ) ? absint( $revenue['completed_count'] ) : 0,
				'pending'   => 0,
				'failed'    => 0,
				'refunded'  => isset( $revenue['refund_count'] ) ? absint( $revenue['refund_count'] ) : 0,
			),
		);

		global $wpdb;
		$table = GMM_Database::table( 'payments' );
		$payload['status_counts']['pending'] = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE payment_status = %s", 'pending' )
		);
		$payload['status_counts']['failed'] = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE payment_status = %s", 'failed' )
		);

		/**
		 * Filter admin payments analytics for charts/reports.
		 *
		 * @param array<string, mixed> $payload Payload.
		 */
		return apply_filters( 'gmm_admin_payments_analytics', $payload );
	}

	/**
	 * Export rows (CSV / reports).
	 *
	 * @param array<string, mixed> $filters Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function prepare_export_rows( $filters = array() ) {
		if ( ! self::user_can_manage() ) {
			return array();
		}

		$filters = wp_parse_args( $filters, self::get_request_filters() );
		$filters['page']     = 1;
		$filters['per_page'] = 500;
		$list = self::list_payments( $filters );
		$rows = isset( $list['items'] ) ? $list['items'] : array();

		$export = array();
		foreach ( $rows as $item ) {
			$export[] = array(
				'transaction_id'   => isset( $item['txn_code'] ) ? $item['txn_code'] : '',
				'booking_id'       => isset( $item['booking_code'] ) ? $item['booking_code'] : '',
				'student'          => isset( $item['student'] ) ? $item['student'] : '',
				'teacher'          => isset( $item['teacher'] ) ? $item['teacher'] : '',
				'amount'           => isset( $item['amount'] ) ? $item['amount'] : 0,
				'commission'       => isset( $item['commission'] ) ? $item['commission'] : 0,
				'teacher_earnings' => isset( $item['teacher_earnings'] ) ? $item['teacher_earnings'] : 0,
				'method'           => isset( $item['method'] ) ? $item['method'] : '',
				'status'           => isset( $item['status'] ) ? $item['status'] : '',
				'date'             => isset( $item['date_raw'] ) ? $item['date_raw'] : '',
			);
		}

		$export = apply_filters( 'gmm_admin_payments_export_rows', $export, $filters );
		do_action( 'gmm_admin_payments_export_prepared', $export, $filters );
		return $export;
	}

	/**
	 * @return void
	 */
	public static function flush_cache() {
		delete_transient( self::CACHE_GROUP . '_revenue' );
		delete_transient( 'gmm_admin_dashboard_revenue_chart' );
		if ( class_exists( 'GMM_Admin_Dashboard' ) ) {
			GMM_Admin_Dashboard::flush_cache();
		}
	}

	/**
	 * @return void
	 */
	public function maybe_enqueue_assets() {
		if ( ! self::user_can_manage() ) {
			return;
		}
		if ( ! class_exists( 'GMM_Assets' ) || ! GMM_Assets::is_gmm_page() ) {
			return;
		}
		$post    = get_queried_object();
		$content = ( $post instanceof WP_Post ) ? (string) $post->post_content : '';
		if ( ! has_shortcode( $content, 'gmm_admin_payments' ) && false === strpos( $content, 'gmm_admin_payments' ) ) {
			return;
		}
		self::enqueue_assets();
	}

	/**
	 * @param string $hook Hook.
	 * @return void
	 */
	public function maybe_enqueue_admin_assets( $hook = '' ) {
		if ( ! self::user_can_manage() ) {
			return;
		}
		if ( ! function_exists( 'gmm_is_plugin_admin_page' ) || ! gmm_is_plugin_admin_page( $hook ) ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';
		if ( 'gmm-payments' !== $page && false === strpos( (string) $hook, 'gmm-payments' ) ) {
			return;
		}
		self::enqueue_assets();
	}

	/**
	 * @return void
	 */
	public static function enqueue_assets() {
		$version = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';

		wp_enqueue_script(
			'gmm-admin-payments',
			GMM_URL . 'assets/js/gmm-admin-payments.js',
			array( 'gmm-core-script', 'gmm-ajax-script' ),
			$version,
			true
		);

		wp_localize_script(
			'gmm-admin-payments',
			'GMM_ADMIN_PAYMENTS',
			array(
				'nonce'   => wp_create_nonce( 'gmm_nonce' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'export'  => self::prepare_export_rows( self::get_request_filters() ),
				'i18n'    => array(
					'completed'      => __( 'Payment marked completed.', 'gospel-music-mastery' ),
					'failed'         => __( 'Payment marked failed.', 'gospel-music-mastery' ),
					'refunded'       => __( 'Payment marked refunded.', 'gospel-music-mastery' ),
					'refundApproved' => __( 'Refund approved.', 'gospel-music-mastery' ),
					'refundRejected' => __( 'Refund rejected.', 'gospel-music-mastery' ),
					'confirmRefund'  => __( 'Process refund for this payment? (No gateway chargeback — status only.)', 'gospel-music-mastery' ),
					'confirmApprove' => __( 'Approve this refund request?', 'gospel-music-mastery' ),
					'confirmReject'  => __( 'Reject this refund request?', 'gospel-music-mastery' ),
					'exportReady'    => __( 'Export prepared.', 'gospel-music-mastery' ),
					'error'          => __( 'Action failed. Please try again.', 'gospel-music-mastery' ),
				),
			)
		);
	}

	/**
	 * @param array<string, mixed> $row Row.
	 * @return array<string, mixed>
	 */
	private static function format_payment_row( $row ) {
		$id      = absint( $row['id'] );
		$txn_raw = isset( $row['transaction_id'] ) ? trim( (string) $row['transaction_id'] ) : '';
		$txn     = $txn_raw ? $txn_raw : ( 'TXN-' . $id );

		$sfirst  = isset( $row['student_first_name'] ) ? (string) $row['student_first_name'] : '';
		$slast   = isset( $row['student_last_name'] ) ? (string) $row['student_last_name'] : '';
		$student = trim( $sfirst . ' ' . $slast );

		$tfirst  = isset( $row['teacher_first_name'] ) ? (string) $row['teacher_first_name'] : '';
		$tlast   = isset( $row['teacher_last_name'] ) ? (string) $row['teacher_last_name'] : '';
		$teacher = trim( $tfirst . ' ' . $tlast );

		$method_raw = isset( $row['payment_method'] ) ? sanitize_key( (string) $row['payment_method'] ) : '';
		$status     = isset( $row['payment_status'] ) ? sanitize_key( (string) $row['payment_status'] ) : 'pending';
		$amount     = isset( $row['amount'] ) ? (float) $row['amount'] : 0.0;
		$split      = self::get_saved_split( $id, $amount );

		$type = 'lesson';
		$type_label = __( 'Lesson Payment', 'gospel-music-mastery' );
		if ( 'withdrawal' === $method_raw ) {
			$type       = 'payout';
			$type_label = __( 'Teacher Payout', 'gospel-music-mastery' );
			$user       = $teacher ? $teacher : __( 'Teacher', 'gospel-music-mastery' );
			$user_img   = self::resolve_image( isset( $row['teacher_image'] ) ? (string) $row['teacher_image'] : '', 'assets/img/team/01.jpg' );
			$user_email = isset( $row['teacher_email'] ) ? (string) $row['teacher_email'] : '';
		} elseif ( 'refunded' === $status ) {
			$type       = 'refund';
			$type_label = __( 'Refund', 'gospel-music-mastery' );
			$user       = $student ? $student : __( 'Student', 'gospel-music-mastery' );
			$user_img   = self::resolve_image( isset( $row['student_image'] ) ? (string) $row['student_image'] : '', 'assets/img/team/02.jpg' );
			$user_email = isset( $row['student_email'] ) ? (string) $row['student_email'] : '';
		} else {
			$user       = $student ? $student : __( 'Student', 'gospel-music-mastery' );
			$user_img   = self::resolve_image( isset( $row['student_image'] ) ? (string) $row['student_image'] : '', 'assets/img/team/02.jpg' );
			$user_email = isset( $row['student_email'] ) ? (string) $row['student_email'] : '';
		}

		$created_raw = isset( $row['created_at'] ) ? (string) $row['created_at'] : '';
		$date_label  = '—';
		$ts          = $created_raw ? strtotime( $created_raw ) : false;
		if ( $ts ) {
			$date_label = wp_date( 'F j, Y', $ts );
		}

		$booking_id   = absint( isset( $row['booking_id'] ) ? $row['booking_id'] : 0 );
		$booking_code = $booking_id ? ( 'BK-' . $booking_id ) : '—';

		$method_label = self::method_label( $method_raw );

		return array(
			'id'                 => $id,
			'txn_code'           => $txn,
			'booking_id'         => $booking_id,
			'booking_code'       => $booking_code,
			'student_id'         => absint( isset( $row['student_id'] ) ? $row['student_id'] : 0 ),
			'teacher_id'         => absint( isset( $row['teacher_id'] ) ? $row['teacher_id'] : 0 ),
			'student'            => $student ? $student : __( 'Student', 'gospel-music-mastery' ),
			'teacher'            => $teacher ? $teacher : __( 'Teacher', 'gospel-music-mastery' ),
			'user'               => $user,
			'user_email'         => $user_email,
			'user_image'         => $user_img,
			'student_image'      => self::resolve_image( isset( $row['student_image'] ) ? (string) $row['student_image'] : '', 'assets/img/team/02.jpg' ),
			'teacher_image'      => self::resolve_image( isset( $row['teacher_image'] ) ? (string) $row['teacher_image'] : '', 'assets/img/team/01.jpg' ),
			'type'               => $type,
			'type_label'         => $type_label,
			'amount'             => $amount,
			'amount_label'       => '$' . number_format_i18n( $amount, 0 ),
			'commission'         => (float) $split['commission'],
			'commission_label'   => '$' . number_format_i18n( (float) $split['commission'], 2 ),
			'teacher_earnings'   => (float) $split['teacher_earnings'],
			'teacher_earnings_label' => '$' . number_format_i18n( (float) $split['teacher_earnings'], 2 ),
			'method'             => $method_raw,
			'method_label'       => $method_label,
			'status'             => $status,
			'status_label'       => self::status_label( $status ),
			'status_class'       => self::status_badge_class( $status ),
			'date'               => $date_label,
			'date_raw'           => $created_raw ? substr( $created_raw, 0, 10 ) : '',
			'period'             => self::period_bucket( $ts ? $ts : 0 ),
		);
	}

	/**
	 * @param int   $payment_id ID.
	 * @param float $amount     Amount.
	 * @return array<string, mixed>
	 */
	private static function get_saved_split( $payment_id, $amount ) {
		$all = get_option( self::SPLITS_OPTION, array() );
		if ( is_array( $all ) && isset( $all[ $payment_id ] ) && is_array( $all[ $payment_id ] ) ) {
			return wp_parse_args(
				$all[ $payment_id ],
				array(
					'commission'         => 0,
					'teacher_earnings'   => 0,
					'commission_percent' => self::get_commission_percent(),
					'gross'              => $amount,
				)
			);
		}
		return self::calculate_and_save_split( $amount, $payment_id );
	}

	/**
	 * @param int    $payment_id Payment ID.
	 * @param string $status     Status.
	 * @param int    $index      Index.
	 * @return void
	 */
	private static function update_refund_request_status( $payment_id, $status, $index = -1 ) {
		$all = get_option( self::REFUNDS_OPTION, array() );
		if ( ! is_array( $all ) ) {
			return;
		}
		$index = (int) $index;
		if ( $index >= 0 && isset( $all[ $index ] ) && is_array( $all[ $index ] ) ) {
			$all[ $index ]['status']       = sanitize_key( $status );
			$all[ $index ]['resolved_at']  = current_time( 'mysql' );
			$all[ $index ]['resolved_by']  = get_current_user_id();
		} else {
			foreach ( $all as $i => $req ) {
				if ( ! is_array( $req ) ) {
					continue;
				}
				if ( absint( isset( $req['payment_id'] ) ? $req['payment_id'] : 0 ) === absint( $payment_id ) ) {
					$all[ $i ]['status']      = sanitize_key( $status );
					$all[ $i ]['resolved_at'] = current_time( 'mysql' );
					$all[ $i ]['resolved_by'] = get_current_user_id();
				}
			}
		}
		update_option( self::REFUNDS_OPTION, $all, false );
	}

	/**
	 * @param array<int, string>   $where  Where.
	 * @param array<int, mixed>    $params Params.
	 * @param array<string, mixed> $args   Args.
	 * @return void
	 */
	private static function append_period_filter( &$where, &$params, $args ) {
		$period = sanitize_key( isset( $args['period'] ) ? (string) $args['period'] : 'all' );
		$today  = wp_date( 'Y-m-d' );

		switch ( $period ) {
			case 'today':
				$where[]  = 'DATE(p.created_at) = %s';
				$params[] = $today;
				break;
			case 'week':
				$where[]  = 'DATE(p.created_at) >= %s';
				$params[] = wp_date( 'Y-m-d', strtotime( '-6 days', strtotime( $today . ' 00:00:00' ) ) );
				break;
			case 'month':
				$where[]  = 'DATE(p.created_at) >= %s';
				$params[] = wp_date( 'Y-m-01' );
				break;
			case 'custom':
				if ( ! empty( $args['date_from'] ) ) {
					$where[]  = 'DATE(p.created_at) >= %s';
					$params[] = self::sanitize_date( $args['date_from'] );
				}
				if ( ! empty( $args['date_to'] ) ) {
					$where[]  = 'DATE(p.created_at) <= %s';
					$params[] = self::sanitize_date( $args['date_to'] );
				}
				break;
			default:
				break;
		}
	}

	/**
	 * @param string $method Method.
	 * @return string
	 */
	private static function method_label( $method ) {
		$map = array(
			'stripe'     => 'Stripe',
			'paypal'     => 'PayPal',
			'manual'     => 'Manual',
			'withdrawal' => 'Payout',
			'pending'    => 'Pending',
		);
		$method = sanitize_key( $method );
		if ( isset( $map[ $method ] ) ) {
			return $map[ $method ];
		}
		return $method ? ucfirst( $method ) : '—';
	}

	/**
	 * @param string $status Status.
	 * @return string
	 */
	private static function status_label( $status ) {
		$labels = array(
			'pending'   => __( 'Pending', 'gospel-music-mastery' ),
			'completed' => __( 'Completed', 'gospel-music-mastery' ),
			'failed'    => __( 'Failed', 'gospel-music-mastery' ),
			'refunded'  => __( 'Refunded', 'gospel-music-mastery' ),
		);
		return isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
	}

	/**
	 * @param string $status Status.
	 * @return string
	 */
	private static function status_badge_class( $status ) {
		$map = array(
			'pending'   => 'is-pending',
			'completed' => 'is-confirmed',
			'failed'    => 'is-failed',
			'refunded'  => 'is-inactive',
		);
		return isset( $map[ $status ] ) ? $map[ $status ] : 'is-pending';
	}

	/**
	 * @param int $ts Timestamp.
	 * @return string
	 */
	private static function period_bucket( $ts ) {
		if ( ! $ts ) {
			return 'all';
		}
		$today = strtotime( wp_date( 'Y-m-d' ) . ' 00:00:00' );
		if ( $ts >= $today ) {
			return 'today';
		}
		if ( $ts >= strtotime( '-6 days', $today ) ) {
			return 'week';
		}
		if ( $ts >= strtotime( wp_date( 'Y-m-01' ) . ' 00:00:00' ) ) {
			return 'month';
		}
		return 'all';
	}

	/**
	 * @param string $raw Path.
	 * @param string $fallback Fallback.
	 * @return string
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
		if ( $raw && false === strpos( $raw, '://' ) && function_exists( 'gmm_design_asset_url' ) ) {
			return gmm_design_asset_url( ltrim( $raw, '/' ) );
		}
		return function_exists( 'gmm_design_asset_url' ) ? gmm_design_asset_url( $fallback ) : '';
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
	 * @return array<string, mixed>
	 */
	private static function empty_revenue() {
		return array(
			'total_revenue'       => 0,
			'completed_payments'  => 0,
			'pending_payments'    => 0,
			'refund_amount'       => 0,
			'platform_commission' => 0,
			'teacher_earnings'    => 0,
			'pending_payouts'     => 0,
			'completed_count'     => 0,
			'refund_count'        => 0,
			'commission_percent'  => 10,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function empty_filters() {
		return array(
			'search'    => '',
			'status'    => 'all',
			'type'      => 'all',
			'method'    => 'all',
			'period'    => 'all',
			'date_from' => '',
			'date_to'   => '',
			'page'      => 1,
			'per_page'  => self::PER_PAGE,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function empty_pagination() {
		return array(
			'page'        => 1,
			'per_page'    => self::PER_PAGE,
			'total'       => 0,
			'total_pages' => 0,
			'has_prev'    => false,
			'has_next'    => false,
			'prev_page'   => null,
			'next_page'   => null,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function empty_list() {
		return array_merge( array( 'items' => array() ), self::empty_pagination() );
	}
}
