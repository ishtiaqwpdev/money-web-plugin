<?php
/**
 * Admin booking management controller.
 *
 * Supplies list/search/filter/pagination and status/payment actions for
 * templates/admin/bookings.php without changing the frozen UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Admin_Bookings
 */
class GMM_Admin_Bookings {

	const PER_PAGE    = 8;
	const CACHE_GROUP = 'gmm_admin_bookings';
	const CACHE_TTL   = 60;

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
	}

	/**
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Tag.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_admin_bookings' !== $tag ) {
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
		if ( 'bookings' !== $page ) {
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
				'gmm_admin_denied'  => true,
				'bookings'          => array(),
				'booking_stats'     => self::empty_stats(),
				'filters'           => self::empty_filters(),
				'pagination'        => self::empty_pagination(),
				'booking_activity'  => array(),
			);
		}

		$filters = self::get_request_filters();
		$list    = self::list_bookings( $filters );

		return array(
			'gmm_admin_denied' => false,
			'bookings'         => isset( $list['items'] ) ? $list['items'] : array(),
			'booking_stats'    => self::get_stats(),
			'filters'          => $filters,
			'pagination'       => array(
				'page'        => isset( $list['page'] ) ? absint( $list['page'] ) : 1,
				'per_page'    => isset( $list['per_page'] ) ? absint( $list['per_page'] ) : self::PER_PAGE,
				'total'       => isset( $list['total'] ) ? absint( $list['total'] ) : 0,
				'total_pages' => isset( $list['total_pages'] ) ? absint( $list['total_pages'] ) : 0,
				'has_prev'    => ! empty( $list['has_prev'] ),
				'has_next'    => ! empty( $list['has_next'] ),
				'prev_page'   => isset( $list['prev_page'] ) ? $list['prev_page'] : null,
				'next_page'   => isset( $list['next_page'] ) ? $list['next_page'] : null,
			),
			'booking_activity' => self::get_recent_activity( 6 ),
			'logout_url'       => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
			'last_login_label' => self::format_last_login(),
			'export_ready'     => true,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_request_filters() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search = isset( $_GET['ab_search'] ) ? sanitize_text_field( wp_unslash( $_GET['ab_search'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = isset( $_GET['ab_status'] ) ? sanitize_key( wp_unslash( $_GET['ab_status'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$payment = isset( $_GET['ab_payment'] ) ? sanitize_key( wp_unslash( $_GET['ab_payment'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$period = isset( $_GET['ab_date'] ) ? sanitize_key( wp_unslash( $_GET['ab_date'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$date_from = isset( $_GET['ab_date_from'] ) ? sanitize_text_field( wp_unslash( $_GET['ab_date_from'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$date_to = isset( $_GET['ab_date_to'] ) ? sanitize_text_field( wp_unslash( $_GET['ab_date_to'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['ab_page'] ) ? absint( $_GET['ab_page'] ) : 1;

		if ( ! in_array( $status, array( 'all', 'pending', 'confirmed', 'completed', 'cancelled' ), true ) ) {
			$status = 'all';
		}
		if ( ! in_array( $payment, array( 'all', 'pending', 'paid', 'failed', 'refunded' ), true ) ) {
			$payment = 'all';
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
			'payment'   => $payment,
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
	public static function list_bookings( $args = array() ) {
		if ( ! self::user_can_manage() ) {
			return self::empty_list();
		}

		$args = wp_parse_args(
			$args,
			array(
				'search'    => '',
				'status'    => 'all',
				'payment'   => 'all',
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
		$bookings = GMM_Database::table( 'bookings' );
		$students = GMM_Database::table( 'students' );
		$teachers = GMM_Database::table( 'teachers' );
		$classes  = GMM_Database::table( 'classes' );
		$payments = GMM_Database::table( 'payments' );

		$where  = array( '1=1' );
		$params = array();

		$search = trim( (string) $args['search'] );
		if ( '' !== $search ) {
			$like = '%' . $wpdb->esc_like( $search ) . '%';
			$id_q = absint( preg_replace( '/\D+/', '', $search ) );
			$where[]  = '(CAST(b.id AS CHAR) LIKE %s OR CONCAT(\'BK-\', b.id) LIKE %s OR s.first_name LIKE %s OR s.last_name LIKE %s OR CONCAT(s.first_name, \' \', s.last_name) LIKE %s OR t.first_name LIKE %s OR t.last_name LIKE %s OR CONCAT(t.first_name, \' \', t.last_name) LIKE %s OR c.title LIKE %s)';
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
				$where[ count( $where ) - 1 ] = '(' . $where[ count( $where ) - 1 ] . ' OR b.id = %d)';
				$params[] = $id_q;
			}
		}

		$status = sanitize_key( (string) $args['status'] );
		if ( $status && 'all' !== $status ) {
			$where[]  = 'b.booking_status = %s';
			$params[] = $status;
		}

		$payment = sanitize_key( (string) $args['payment'] );
		if ( $payment && 'all' !== $payment ) {
			if ( 'paid' === $payment ) {
				$where[]  = 'b.payment_status IN (%s, %s)';
				$params[] = 'paid';
				$params[] = 'completed';
			} else {
				$where[]  = 'b.payment_status = %s';
				$params[] = $payment;
			}
		}

		self::append_period_filter( $where, $params, $args );

		$where_sql = implode( ' AND ', $where );

		$count_sql = "SELECT COUNT(*) FROM {$bookings} b
			LEFT JOIN {$students} s ON s.id = b.student_id
			LEFT JOIN {$teachers} t ON t.id = b.teacher_id
			LEFT JOIN {$classes} c ON c.id = b.class_id
			WHERE {$where_sql}";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) );

		$sql = "SELECT b.*,
			s.first_name AS student_first_name, s.last_name AS student_last_name, s.email AS student_email, s.phone AS student_phone, s.profile_image AS student_image,
			t.first_name AS teacher_first_name, t.last_name AS teacher_last_name, t.email AS teacher_email, t.phone AS teacher_phone, t.profile_image AS teacher_image,
			c.title AS class_title, c.duration AS class_duration, c.image AS class_image,
			(SELECT p.id FROM {$payments} p WHERE p.booking_id = b.id ORDER BY p.id DESC LIMIT 1) AS payment_id
			FROM {$bookings} b
			LEFT JOIN {$students} s ON s.id = b.student_id
			LEFT JOIN {$teachers} t ON t.id = b.teacher_id
			LEFT JOIN {$classes} c ON c.id = b.class_id
			WHERE {$where_sql}
			ORDER BY b.booking_date DESC, b.id DESC
			LIMIT %d OFFSET %d";

		$qparams   = $params;
		$qparams[] = $per_page;
		$qparams[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $qparams ), ARRAY_A );
		$rows = is_array( $rows ) ? $rows : array();

		$items = array();
		foreach ( $rows as $row ) {
			$items[] = self::format_booking_row( $row );
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
	 * @return array<string, int>
	 */
	public static function get_stats() {
		$cached = get_transient( self::CACHE_GROUP . '_stats' );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;
		$table = GMM_Database::table( 'bookings' );
		$today = wp_date( 'Y-m-d' );

		$total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table}" ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$upcoming = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table}
				WHERE booking_date >= %s AND booking_status IN ('pending','confirmed','upcoming')",
				$today
			)
		);
		$completed = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE booking_status = %s", 'completed' )
		);
		$cancelled = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE booking_status = %s", 'cancelled' )
		);
		$pending = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE booking_status = %s", 'pending' )
		);

		$stats = array(
			'total'     => $total,
			'upcoming'  => $upcoming,
			'completed' => $completed,
			'cancelled' => $cancelled,
			'pending'   => $pending,
		);

		set_transient( self::CACHE_GROUP . '_stats', $stats, self::CACHE_TTL );
		return $stats;
	}

	/**
	 * @param int $limit Limit.
	 * @return array<int, array<string, string>>
	 */
	public static function get_recent_activity( $limit = 6 ) {
		$limit = max( 1, min( 20, absint( $limit ) ) );
		if ( ! function_exists( 'gmm_get_recent_activity' ) ) {
			return array();
		}
		$raw   = gmm_get_recent_activity( max( 12, $limit * 2 ) );
		$items = array();
		if ( ! is_array( $raw ) ) {
			return $items;
		}
		foreach ( $raw as $row ) {
			$type = isset( $row['type'] ) ? (string) $row['type'] : '';
			if ( $type && ! in_array( $type, array( 'booking', 'payment', 'student' ), true ) ) {
				continue;
			}
			$items[] = array(
				'title' => isset( $row['title'] ) ? wp_strip_all_tags( (string) $row['title'] ) : '',
				'meta'  => isset( $row['description'] ) ? wp_strip_all_tags( (string) $row['description'] ) : '',
				'time'  => isset( $row['time'] ) ? (string) $row['time'] : '',
				'icon'  => isset( $row['icon'] ) ? (string) $row['icon'] : 'far fa-calendar-check',
				'css'   => isset( $row['css'] ) ? (string) $row['css'] : 'is-class',
			);
			if ( count( $items ) >= $limit ) {
				break;
			}
		}
		return $items;
	}

	/**
	 * @param int $booking_id Booking ID.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function get_profile( $booking_id ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$booking_id = absint( $booking_id );
		if ( ! $booking_id ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid booking.', 'gospel-music-mastery' ) );
		}

		$details = class_exists( 'GMM_Booking' ) ? GMM_Booking::get_details( $booking_id, get_current_user_id() ) : null;
		if ( is_wp_error( $details ) ) {
			return $details;
		}
		if ( ! is_array( $details ) ) {
			return new WP_Error( 'gmm_missing', __( 'Booking not found.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$payments_t = GMM_Database::table( 'payments' );
		$payment    = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$payments_t} WHERE booking_id = %d ORDER BY id DESC LIMIT 1",
				$booking_id
			),
			ARRAY_A
		);

		$row = self::get_raw_row( $booking_id );
		$timeline = array();
		if ( $row ) {
			$timeline[] = array(
				'label' => __( 'Booking created', 'gospel-music-mastery' ),
				'time'  => ! empty( $row['created_at'] ) ? $row['created_at'] : '',
			);
			$timeline[] = array(
				'label' => sprintf(
					/* translators: %s: status */
					__( 'Current status: %s', 'gospel-music-mastery' ),
					isset( $row['booking_status'] ) ? $row['booking_status'] : ''
				),
				'time'  => ! empty( $row['updated_at'] ) ? $row['updated_at'] : '',
			);
			if ( is_array( $payment ) ) {
				$timeline[] = array(
					'label' => sprintf(
						/* translators: %s: payment status */
						__( 'Payment: %s', 'gospel-music-mastery' ),
						isset( $payment['payment_status'] ) ? $payment['payment_status'] : ''
					),
					'time'  => isset( $payment['created_at'] ) ? $payment['created_at'] : '',
				);
			}
		}

		$details['payment']  = is_array( $payment ) ? $payment : array();
		$details['timeline'] = $timeline;
		$details['formatted'] = $row ? self::format_booking_row( array_merge( $row, self::enrich_names_from_details( $details ) ) ) : array();

		return $details;
	}

	/**
	 * Confirm booking (admin).
	 *
	 * @param int $booking_id Booking ID.
	 * @return true|WP_Error
	 */
	public static function confirm( $booking_id ) {
		return self::set_booking_status( $booking_id, 'confirmed', 'gmm_booking_confirmed' );
	}

	/**
	 * Complete booking (admin).
	 *
	 * @param int $booking_id Booking ID.
	 * @return true|WP_Error
	 */
	public static function complete( $booking_id ) {
		return self::set_booking_status( $booking_id, 'completed', 'gmm_booking_completed' );
	}

	/**
	 * Cancel booking (admin). Keeps history; prepares refund hook.
	 *
	 * @param int  $booking_id   Booking ID.
	 * @param bool $prep_refund  Whether to prepare refund support.
	 * @return true|WP_Error
	 */
	public static function cancel( $booking_id, $prep_refund = true ) {
		$result = self::set_booking_status( $booking_id, 'cancelled', 'gmm_booking_cancelled' );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( $prep_refund ) {
			/**
			 * Prepare refund support after admin cancellation (no automatic refund).
			 *
			 * @param int $booking_id Booking ID.
			 */
			do_action( 'gmm_admin_booking_refund_prepared', absint( $booking_id ) );
		}

		return true;
	}

	/**
	 * @param int    $booking_id Booking ID.
	 * @param string $status     Status.
	 * @param string $hook       Hook.
	 * @return true|WP_Error
	 */
	public static function set_booking_status( $booking_id, $status, $hook = '' ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$booking_id = absint( $booking_id );
		$status     = sanitize_key( $status );
		$allowed    = array( 'pending', 'confirmed', 'upcoming', 'completed', 'cancelled', 'no_show', 'refunded' );

		if ( ! $booking_id || ! in_array( $status, $allowed, true ) ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid booking or status.', 'gospel-music-mastery' ) );
		}

		$row = self::get_raw_row( $booking_id );
		if ( ! $row ) {
			return new WP_Error( 'gmm_missing', __( 'Booking not found.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'bookings' );
		$updated = $wpdb->update(
			$table,
			array(
				'booking_status' => $status,
				'updated_at'     => current_time( 'mysql' ),
			),
			array( 'id' => $booking_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db', __( 'Could not update booking.', 'gospel-music-mastery' ) );
		}

		$row['booking_status'] = $status;

		if ( ! $hook ) {
			$map = array(
				'confirmed' => 'gmm_booking_confirmed',
				'completed' => 'gmm_booking_completed',
				'cancelled' => 'gmm_booking_cancelled',
			);
			$hook = isset( $map[ $status ] ) ? $map[ $status ] : '';
		}

		if ( $hook ) {
			do_action( $hook, $booking_id, $status );
		}

		/**
		 * Fires after admin updates any booking status.
		 *
		 * @param int                  $booking_id Booking ID.
		 * @param string               $status     New status.
		 * @param array<string, mixed> $row        Row.
		 */
		do_action( 'gmm_admin_booking_status_updated', $booking_id, $status, $row );

		self::flush_cache();
		return true;
	}

	/**
	 * Update booking payment status + linked payment row.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $status     UI/payment status.
	 * @return true|WP_Error
	 */
	public static function set_payment_status( $booking_id, $status ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$booking_id = absint( $booking_id );
		$status     = sanitize_key( $status );

		// Normalize UI "paid" to storage values.
		$booking_payment = $status;
		if ( 'paid' === $status ) {
			$booking_payment = 'paid';
		}
		if ( 'completed' === $status ) {
			$booking_payment = 'paid';
			$status          = 'paid';
		}

		$allowed = array( 'pending', 'paid', 'failed', 'refunded', 'completed' );
		if ( ! $booking_id || ! in_array( $status, $allowed, true ) ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid payment status.', 'gospel-music-mastery' ) );
		}

		$row = self::get_raw_row( $booking_id );
		if ( ! $row ) {
			return new WP_Error( 'gmm_missing', __( 'Booking not found.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$payments = GMM_Database::table( 'payments' );

		$updated = $wpdb->update(
			$bookings,
			array(
				'payment_status' => $booking_payment,
				'updated_at'     => current_time( 'mysql' ),
			),
			array( 'id' => $booking_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db', __( 'Could not update payment status.', 'gospel-music-mastery' ) );
		}

		$payment_id = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$payments} WHERE booking_id = %d ORDER BY id DESC LIMIT 1",
				$booking_id
			)
		);

		$payment_db_status = $status;
		if ( 'paid' === $status ) {
			$payment_db_status = class_exists( 'GMM_Payment' ) ? GMM_Payment::STATUS_COMPLETED : 'completed';
		}

		if ( $payment_id && class_exists( 'GMM_Payment' ) ) {
			GMM_Payment::update_status( $payment_id, $payment_db_status, '' );
		} elseif ( $payment_id ) {
			$wpdb->update(
				$payments,
				array( 'payment_status' => $payment_db_status ),
				array( 'id' => $payment_id ),
				array( '%s' ),
				array( '%d' )
			);
		}

		/**
		 * Fires after admin updates booking payment status.
		 *
		 * @param int    $booking_id Booking ID.
		 * @param string $status     Payment status.
		 */
		do_action( 'gmm_admin_booking_payment_updated', $booking_id, $booking_payment );

		self::flush_cache();
		return true;
	}

	/**
	 * Prepare exportable booking rows (no UI export yet).
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

		$list = self::list_bookings( $filters );
		$rows = isset( $list['items'] ) && is_array( $list['items'] ) ? $list['items'] : array();

		$export = array();
		foreach ( $rows as $item ) {
			$export[] = array(
				'booking_id'     => isset( $item['id'] ) ? $item['id'] : 0,
				'booking_code'   => isset( $item['code'] ) ? $item['code'] : '',
				'student'        => isset( $item['student'] ) ? $item['student'] : '',
				'teacher'        => isset( $item['teacher'] ) ? $item['teacher'] : '',
				'class'          => isset( $item['class'] ) ? $item['class'] : '',
				'date'           => isset( $item['date_raw'] ) ? $item['date_raw'] : '',
				'time'           => isset( $item['time_raw'] ) ? $item['time_raw'] : '',
				'amount'         => isset( $item['amount'] ) ? $item['amount'] : 0,
				'payment_status' => isset( $item['payment'] ) ? $item['payment'] : '',
				'booking_status' => isset( $item['status'] ) ? $item['status'] : '',
				'created_at'     => isset( $item['created_raw'] ) ? $item['created_raw'] : '',
			);
		}

		/**
		 * Filter prepared booking export rows (CSV / reports later).
		 *
		 * @param array<int, array<string, mixed>> $export  Rows.
		 * @param array<string, mixed>             $filters Filters.
		 */
		$export = apply_filters( 'gmm_admin_bookings_export_rows', $export, $filters );

		do_action( 'gmm_admin_bookings_export_prepared', $export, $filters );

		return $export;
	}

	/**
	 * @return void
	 */
	public static function flush_cache() {
		delete_transient( self::CACHE_GROUP . '_stats' );
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
		if ( ! has_shortcode( $content, 'gmm_admin_bookings' ) && false === strpos( $content, 'gmm_admin_bookings' ) ) {
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
		if ( 'gmm-bookings' !== $page && false === strpos( (string) $hook, 'gmm-bookings' ) ) {
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
			'gmm-admin-bookings',
			GMM_URL . 'assets/js/gmm-admin-bookings.js',
			array( 'gmm-core-script', 'gmm-ajax-script' ),
			$version,
			true
		);

		wp_localize_script(
			'gmm-admin-bookings',
			'GMM_ADMIN_BOOKINGS',
			array(
				'nonce'   => wp_create_nonce( 'gmm_nonce' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'i18n'    => array(
					'confirmed'       => __( 'Booking confirmed.', 'gospel-music-mastery' ),
					'completed'       => __( 'Booking completed.', 'gospel-music-mastery' ),
					'cancelled'       => __( 'Booking cancelled.', 'gospel-music-mastery' ),
					'paymentUpdated'  => __( 'Payment status updated.', 'gospel-music-mastery' ),
					'confirmCancel'   => __( 'Cancel this booking? History will be kept.', 'gospel-music-mastery' ),
					'confirmRefund'   => __( 'Mark payment as refunded?', 'gospel-music-mastery' ),
					'error'           => __( 'Action failed. Please try again.', 'gospel-music-mastery' ),
				),
			)
		);
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
				$where[]  = 'b.booking_date = %s';
				$params[] = $today;
				break;
			case 'week':
				$where[]  = 'b.booking_date >= %s';
				$params[] = wp_date( 'Y-m-d', strtotime( '-6 days', strtotime( $today . ' 00:00:00' ) ) );
				break;
			case 'month':
				$where[]  = 'b.booking_date >= %s';
				$params[] = wp_date( 'Y-m-01' );
				break;
			case 'custom':
				if ( ! empty( $args['date_from'] ) ) {
					$where[]  = 'b.booking_date >= %s';
					$params[] = self::sanitize_date( $args['date_from'] );
				}
				if ( ! empty( $args['date_to'] ) ) {
					$where[]  = 'b.booking_date <= %s';
					$params[] = self::sanitize_date( $args['date_to'] );
				}
				break;
			default:
				break;
		}
	}

	/**
	 * @param array<string, mixed> $row Row.
	 * @return array<string, mixed>
	 */
	private static function format_booking_row( $row ) {
		$id = absint( $row['id'] );
		$code = 'BK-' . $id;

		$sfirst = isset( $row['student_first_name'] ) ? (string) $row['student_first_name'] : '';
		$slast  = isset( $row['student_last_name'] ) ? (string) $row['student_last_name'] : '';
		$student = trim( $sfirst . ' ' . $slast );
		if ( '' === $student ) {
			$student = __( 'Student', 'gospel-music-mastery' );
		}

		$tfirst = isset( $row['teacher_first_name'] ) ? (string) $row['teacher_first_name'] : '';
		$tlast  = isset( $row['teacher_last_name'] ) ? (string) $row['teacher_last_name'] : '';
		$teacher = trim( $tfirst . ' ' . $tlast );
		if ( '' === $teacher ) {
			$teacher = __( 'Teacher', 'gospel-music-mastery' );
		}

		$class = isset( $row['class_title'] ) && $row['class_title'] ? (string) $row['class_title'] : __( 'Class', 'gospel-music-mastery' );

		$date_raw = isset( $row['booking_date'] ) ? (string) $row['booking_date'] : '';
		$time_raw = isset( $row['booking_time'] ) ? (string) $row['booking_time'] : '';
		$date_label = $date_raw;
		$time_label = $time_raw;
		$ts_date = $date_raw ? strtotime( $date_raw . ' 00:00:00' ) : false;
		if ( $ts_date ) {
			$date_label = wp_date( 'F j, Y', $ts_date );
		}
		$ts_time = $time_raw ? strtotime( '1970-01-01 ' . $time_raw ) : false;
		if ( $ts_time ) {
			$time_label = wp_date( 'g:i A', $ts_time );
		}

		$duration = isset( $row['duration'] ) ? absint( $row['duration'] ) : 0;
		if ( ! $duration && ! empty( $row['class_duration'] ) ) {
			$duration = absint( $row['class_duration'] );
		}

		$amount = isset( $row['amount'] ) ? (float) $row['amount'] : 0.0;
		$pay_db = isset( $row['payment_status'] ) ? sanitize_key( (string) $row['payment_status'] ) : 'pending';
		$pay_ui = self::payment_to_ui( $pay_db );
		$status = isset( $row['booking_status'] ) ? sanitize_key( (string) $row['booking_status'] ) : 'pending';
		if ( 'upcoming' === $status ) {
			$status = 'confirmed';
		}

		$created_raw = isset( $row['created_at'] ) ? (string) $row['created_at'] : '';
		$created     = '—';
		if ( $created_raw && '0000-00-00 00:00:00' !== $created_raw ) {
			$cts = strtotime( $created_raw );
			if ( $cts ) {
				$created = wp_date( 'M j, Y', $cts );
			}
		}

		$period = self::period_bucket( $ts_date ? $ts_date : 0 );

		return array(
			'id'                 => $id,
			'code'               => $code,
			'student_id'         => absint( isset( $row['student_id'] ) ? $row['student_id'] : 0 ),
			'teacher_id'         => absint( isset( $row['teacher_id'] ) ? $row['teacher_id'] : 0 ),
			'class_id'           => absint( isset( $row['class_id'] ) ? $row['class_id'] : 0 ),
			'payment_id'         => absint( isset( $row['payment_id'] ) ? $row['payment_id'] : 0 ),
			'student'            => $student,
			'teacher'            => $teacher,
			'class'              => $class,
			'date'               => $date_label,
			'date_raw'           => $date_raw,
			'time'               => $time_label,
			'time_raw'           => $time_raw,
			'duration'           => $duration,
			'duration_label'     => $duration ? sprintf( '%d Minutes', $duration ) : '—',
			'amount'             => $amount,
			'amount_label'       => '$' . number_format( $amount, 0 ),
			'payment'            => $pay_ui,
			'payment_label'      => self::payment_label( $pay_ui ),
			'payment_class'      => self::payment_badge_class( $pay_ui ),
			'status'             => $status,
			'status_label'       => self::status_label( $status ),
			'status_class'       => self::status_badge_class( $status ),
			'period'             => $period,
			'notes'              => isset( $row['notes'] ) ? (string) $row['notes'] : '',
			'student_email'      => isset( $row['student_email'] ) ? (string) $row['student_email'] : '',
			'student_phone'      => isset( $row['student_phone'] ) ? (string) $row['student_phone'] : '',
			'teacher_phone'      => isset( $row['teacher_phone'] ) ? (string) $row['teacher_phone'] : '',
			'student_image'      => self::resolve_image( isset( $row['student_image'] ) ? (string) $row['student_image'] : '', 'assets/img/team/02.jpg' ),
			'teacher_image'      => self::resolve_image( isset( $row['teacher_image'] ) ? (string) $row['teacher_image'] : '', 'assets/img/team/01.jpg' ),
			'created'            => $created,
			'created_raw'        => $created_raw,
		);
	}

	/**
	 * @param array<string, mixed> $details Details.
	 * @return array<string, mixed>
	 */
	private static function enrich_names_from_details( $details ) {
		$out = array();
		if ( ! empty( $details['student'] ) && is_array( $details['student'] ) ) {
			$out['student_first_name'] = isset( $details['student']['first_name'] ) ? $details['student']['first_name'] : '';
			$out['student_last_name']  = isset( $details['student']['last_name'] ) ? $details['student']['last_name'] : '';
			$out['student_email']      = isset( $details['student']['email'] ) ? $details['student']['email'] : '';
			$out['student_phone']      = isset( $details['student']['phone'] ) ? $details['student']['phone'] : '';
			$out['student_image']      = isset( $details['student']['image'] ) ? $details['student']['image'] : '';
		}
		if ( ! empty( $details['teacher'] ) && is_array( $details['teacher'] ) ) {
			$out['teacher_first_name'] = isset( $details['teacher']['first_name'] ) ? $details['teacher']['first_name'] : '';
			$out['teacher_last_name']  = isset( $details['teacher']['last_name'] ) ? $details['teacher']['last_name'] : '';
			$out['teacher_phone']      = isset( $details['teacher']['phone'] ) ? $details['teacher']['phone'] : '';
			$out['teacher_image']      = isset( $details['teacher']['image'] ) ? $details['teacher']['image'] : '';
		}
		if ( ! empty( $details['class'] ) && is_array( $details['class'] ) ) {
			$out['class_title']    = isset( $details['class']['title'] ) ? $details['class']['title'] : '';
			$out['class_duration'] = isset( $details['class']['duration'] ) ? $details['class']['duration'] : 0;
		}
		return $out;
	}

	/**
	 * @param int $booking_id ID.
	 * @return array<string, mixed>|null
	 */
	private static function get_raw_row( $booking_id ) {
		global $wpdb;
		$table = GMM_Database::table( 'bookings' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", absint( $booking_id ) ),
			ARRAY_A
		);
		return is_array( $row ) ? $row : null;
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
	 * @param string $status DB status.
	 * @return string
	 */
	private static function payment_to_ui( $status ) {
		$status = sanitize_key( $status );
		if ( in_array( $status, array( 'paid', 'completed', 'success' ), true ) ) {
			return 'paid';
		}
		return $status ? $status : 'pending';
	}

	/**
	 * @param string $status UI status.
	 * @return string
	 */
	private static function payment_label( $status ) {
		$labels = array(
			'paid'     => __( 'Paid', 'gospel-music-mastery' ),
			'pending'  => __( 'Pending', 'gospel-music-mastery' ),
			'failed'   => __( 'Failed', 'gospel-music-mastery' ),
			'refunded' => __( 'Refunded', 'gospel-music-mastery' ),
		);
		return isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
	}

	/**
	 * @param string $status UI status.
	 * @return string
	 */
	private static function payment_badge_class( $status ) {
		$map = array(
			'paid'     => 'is-confirmed',
			'pending'  => 'is-pending',
			'failed'   => 'is-cancelled',
			'refunded' => 'is-inactive',
		);
		return isset( $map[ $status ] ) ? $map[ $status ] : 'is-pending';
	}

	/**
	 * @param string $status Status.
	 * @return string
	 */
	private static function status_label( $status ) {
		$labels = array(
			'pending'   => __( 'Pending', 'gospel-music-mastery' ),
			'confirmed' => __( 'Confirmed', 'gospel-music-mastery' ),
			'completed' => __( 'Completed', 'gospel-music-mastery' ),
			'cancelled' => __( 'Cancelled', 'gospel-music-mastery' ),
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
			'confirmed' => 'is-confirmed',
			'completed' => 'is-completed',
			'cancelled' => 'is-cancelled',
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
		$week = strtotime( '-6 days', $today );
		if ( $ts >= $week ) {
			return 'week';
		}
		$month = strtotime( wp_date( 'Y-m-01' ) . ' 00:00:00' );
		if ( $ts >= $month ) {
			return 'month';
		}
		return 'all';
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
	 * @return array<string, int>
	 */
	private static function empty_stats() {
		return array(
			'total'     => 0,
			'upcoming'  => 0,
			'completed' => 0,
			'cancelled' => 0,
			'pending'   => 0,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function empty_filters() {
		return array(
			'search'    => '',
			'status'    => 'all',
			'payment'   => 'all',
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
