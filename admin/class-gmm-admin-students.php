<?php
/**
 * Admin student management controller.
 *
 * Supplies list/search/filter/pagination and status/edit actions for
 * templates/admin/students.php without changing the frozen UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Admin_Students
 */
class GMM_Admin_Students {

	const PER_PAGE    = 8;
	const CACHE_GROUP = 'gmm_admin_students';
	const CACHE_TTL   = 60;

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
		$loader->add_filter( 'gmm_shortcode_user_can_access', $instance, 'block_suspended_student', 20, 2 );
		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 30 );
		$loader->add_action( 'admin_enqueue_scripts', $instance, 'maybe_enqueue_admin_assets', 30 );
	}

	/**
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Shortcode.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_admin_students' !== $tag ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * @param array<string, mixed> $args Args.
	 * @param string               $page Page key.
	 * @return array<string, mixed>
	 */
	public function inject_admin_page_args( $args, $page ) {
		if ( 'students' !== $page ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * Suspended students cannot access student portal shortcodes.
	 *
	 * @param bool   $allowed Allowed.
	 * @param string $access  Access key.
	 * @return bool
	 */
	public function block_suspended_student( $allowed, $access ) {
		if ( ! $allowed || 'student' !== $access ) {
			return $allowed;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return $allowed;
		}
		if ( self::is_user_suspended( get_current_user_id() ) ) {
			return false;
		}
		return $allowed;
	}

	/**
	 * @return bool
	 */
	public static function user_can_manage() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * @param int $user_id WP user ID.
	 * @return bool
	 */
	public static function is_user_suspended( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			return false;
		}
		$meta = get_user_meta( $user_id, 'gmm_student_status', true );
		if ( 'suspended' === $meta ) {
			return true;
		}
		global $wpdb;
		$table  = GMM_Database::table( 'students' );
		$status = $wpdb->get_var(
			$wpdb->prepare( "SELECT status FROM {$table} WHERE user_id = %d LIMIT 1", $user_id )
		);
		return 'suspended' === sanitize_key( (string) $status );
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_template_vars() {
		if ( ! self::user_can_manage() ) {
			return array(
				'gmm_admin_denied' => true,
				'students'         => array(),
				'student_stats'    => self::empty_stats(),
				'filters'          => self::empty_filters(),
				'pagination'       => self::empty_pagination(),
				'student_activity' => array(),
			);
		}

		$filters = self::get_request_filters();
		$list    = self::list_students( $filters );

		return array(
			'gmm_admin_denied' => false,
			'students'         => isset( $list['items'] ) ? $list['items'] : array(),
			'student_stats'    => self::get_stats(),
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
			'student_activity' => self::get_recent_activity( 6 ),
			'logout_url'       => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
			'last_login_label' => self::format_last_login(),
			'analytics'        => self::get_analytics_payload(),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_request_filters() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only list filters.
		$search = isset( $_GET['as_search'] ) ? sanitize_text_field( wp_unslash( $_GET['as_search'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = isset( $_GET['as_status'] ) ? sanitize_key( wp_unslash( $_GET['as_status'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$level = isset( $_GET['as_level'] ) ? sanitize_key( wp_unslash( $_GET['as_level'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$period = isset( $_GET['as_period'] ) ? sanitize_key( wp_unslash( $_GET['as_period'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['as_page'] ) ? absint( $_GET['as_page'] ) : 1;

		if ( ! in_array( $status, array( 'all', 'active', 'inactive', 'suspended' ), true ) ) {
			$status = 'all';
		}
		if ( ! in_array( $level, array( 'all', 'beginner', 'intermediate', 'advanced' ), true ) ) {
			$level = 'all';
		}
		if ( ! in_array( $period, array( 'all', 'today', 'month', 'year' ), true ) ) {
			$period = 'all';
		}

		return array(
			'search'   => $search,
			'status'   => $status,
			'level'    => $level,
			'period'   => $period,
			'page'     => max( 1, $page ),
			'per_page' => self::PER_PAGE,
		);
	}

	/**
	 * @param array<string, mixed> $args Filters.
	 * @return array<string, mixed>
	 */
	public static function list_students( $args = array() ) {
		if ( ! self::user_can_manage() ) {
			return self::empty_list();
		}

		$args = wp_parse_args(
			$args,
			array(
				'search'   => '',
				'status'   => 'all',
				'level'    => 'all',
				'period'   => 'all',
				'page'     => 1,
				'per_page' => self::PER_PAGE,
			)
		);

		$page     = max( 1, absint( $args['page'] ) );
		$per_page = max( 1, min( 50, absint( $args['per_page'] ) ) );
		$offset   = ( $page - 1 ) * $per_page;

		global $wpdb;
		$students = GMM_Database::table( 'students' );
		$bookings = GMM_Database::table( 'bookings' );

		$where  = array( 's.status <> %s' );
		$params = array( self::trash_status() );

		$search = trim( (string) $args['search'] );
		if ( '' !== $search ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '(s.first_name LIKE %s OR s.last_name LIKE %s OR CONCAT(s.first_name, \' \', s.last_name) LIKE %s OR s.email LIKE %s OR s.phone LIKE %s OR s.learning_level LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$status = sanitize_key( (string) $args['status'] );
		if ( $status && 'all' !== $status ) {
			$where[]  = 's.status = %s';
			$params[] = $status;
		}

		$level = sanitize_key( (string) $args['level'] );
		if ( $level && 'all' !== $level ) {
			$like     = '%' . $wpdb->esc_like( $level ) . '%';
			$where[]  = 'LOWER(s.learning_level) LIKE %s';
			$params[] = $like;
		}

		self::append_period_filter( $where, $params, (string) $args['period'] );

		$where_sql = implode( ' AND ', $where );

		$count_sql = "SELECT COUNT(*) FROM {$students} s WHERE {$where_sql}";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) );

		$sql = "SELECT s.*,
			(SELECT COUNT(*) FROM {$bookings} b WHERE b.student_id = s.id) AS booking_count,
			(SELECT COUNT(*) FROM {$bookings} b WHERE b.student_id = s.id AND b.booking_status = 'completed') AS lesson_count,
			(SELECT COUNT(DISTINCT b.class_id) FROM {$bookings} b WHERE b.student_id = s.id AND b.class_id > 0) AS class_count
			FROM {$students} s
			WHERE {$where_sql}
			ORDER BY s.created_at DESC, s.id DESC
			LIMIT %d OFFSET %d";

		$qparams   = $params;
		$qparams[] = $per_page;
		$qparams[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $qparams ), ARRAY_A );
		$rows = is_array( $rows ) ? $rows : array();

		$items = array();
		foreach ( $rows as $row ) {
			$items[] = self::format_student_row( $row );
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
		$table = GMM_Database::table( 'students' );
		$trash = self::trash_status();

		$total = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status <> %s", $trash )
		);
		$active = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status = %s", 'active' )
		);
		$suspended = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status = %s", 'suspended' )
		);

		$month_start = wp_date( 'Y-m-01' );
		$new_regs    = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE status <> %s AND DATE(created_at) >= %s",
				$trash,
				$month_start
			)
		);

		$stats = array(
			'total'     => $total,
			'active'    => $active,
			'new'       => $new_regs,
			'suspended' => $suspended,
		);

		set_transient( self::CACHE_GROUP . '_stats', $stats, self::CACHE_TTL );
		return $stats;
	}

	/**
	 * Analytics payload for growth / activity charts (prepared).
	 *
	 * @return array<string, mixed>
	 */
	public static function get_analytics_payload() {
		$stats = self::get_stats();
		$growth = function_exists( 'gmm_get_user_growth_chart' ) ? gmm_get_user_growth_chart() : array();

		return array(
			'student_count' => isset( $stats['total'] ) ? absint( $stats['total'] ) : 0,
			'active_count'  => isset( $stats['active'] ) ? absint( $stats['active'] ) : 0,
			'growth'        => $growth,
			'activity'      => self::get_recent_activity( 8 ),
		);
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
		$raw   = gmm_get_recent_activity( max( $limit * 2, 12 ) );
		$items = array();
		if ( ! is_array( $raw ) ) {
			return $items;
		}
		foreach ( $raw as $row ) {
			$type = isset( $row['type'] ) ? (string) $row['type'] : '';
			// Prefer student-related activity for this sidebar.
			if ( $type && ! in_array( $type, array( 'student', 'booking', 'payment' ), true ) ) {
				continue;
			}
			$items[] = array(
				'title' => isset( $row['title'] ) ? wp_strip_all_tags( (string) $row['title'] ) : '',
				'meta'  => isset( $row['time'] ) ? (string) $row['time'] : ( isset( $row['meta'] ) ? (string) $row['meta'] : '' ),
				'icon'  => isset( $row['icon'] ) ? (string) $row['icon'] : 'far fa-bell',
			);
			if ( count( $items ) >= $limit ) {
				break;
			}
		}
		if ( empty( $items ) ) {
			foreach ( array_slice( $raw, 0, $limit ) as $row ) {
				$items[] = array(
					'title' => isset( $row['title'] ) ? wp_strip_all_tags( (string) $row['title'] ) : '',
					'meta'  => isset( $row['time'] ) ? (string) $row['time'] : '',
					'icon'  => isset( $row['icon'] ) ? (string) $row['icon'] : 'far fa-bell',
				);
			}
		}
		return $items;
	}

	/**
	 * Admin-only student profile.
	 *
	 * @param int $student_id Student row ID.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function get_profile( $student_id ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$student_id = absint( $student_id );
		$row        = self::get_raw_row( $student_id );
		if ( ! $row ) {
			return new WP_Error( 'gmm_missing', __( 'Student not found.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$bookings_t = GMM_Database::table( 'bookings' );
		$payments_t = GMM_Database::table( 'payments' );
		$fav_t      = GMM_Database::table( 'favourites' );
		$reviews_t  = GMM_Database::table( 'reviews' );
		$teachers_t = GMM_Database::table( 'teachers' );

		$bookings = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, teacher_id, class_id, booking_date, booking_time, amount, booking_status, payment_status
				FROM {$bookings_t} WHERE student_id = %d
				ORDER BY booking_date DESC, id DESC LIMIT 20",
				$student_id
			),
			ARRAY_A
		);

		$payments = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, amount, payment_method, payment_status, transaction_id, created_at
				FROM {$payments_t} WHERE student_id = %d
				ORDER BY created_at DESC LIMIT 20",
				$student_id
			),
			ARRAY_A
		);

		$favourites = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT f.teacher_id, t.first_name, t.last_name, t.specialization
				FROM {$fav_t} f
				LEFT JOIN {$teachers_t} t ON t.id = f.teacher_id
				WHERE f.student_id = %d
				ORDER BY f.id DESC LIMIT 20",
				$student_id
			),
			ARRAY_A
		);

		$reviews = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, teacher_id, rating, comment, status, created_at
				FROM {$reviews_t} WHERE student_id = %d
				ORDER BY created_at DESC LIMIT 20",
				$student_id
			),
			ARRAY_A
		);

		$formatted = self::format_student_row( $row );

		return array(
			'student'    => $formatted,
			'bookings'   => is_array( $bookings ) ? $bookings : array(),
			'payments'   => is_array( $payments ) ? $payments : array(),
			'favourites' => is_array( $favourites ) ? $favourites : array(),
			'reviews'    => is_array( $reviews ) ? $reviews : array(),
		);
	}

	/**
	 * Edit student (WP user + gmm_students).
	 *
	 * @param int                  $student_id Student ID.
	 * @param array<string, mixed> $data       Fields.
	 * @return true|WP_Error
	 */
	public static function edit_student( $student_id, $data ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$student_id = absint( $student_id );
		$row        = self::get_raw_row( $student_id );
		if ( ! $row ) {
			return new WP_Error( 'gmm_missing', __( 'Student not found.', 'gospel-music-mastery' ) );
		}

		$user_id = absint( $row['user_id'] );
		$data    = is_array( $data ) ? $data : array();

		$first = array_key_exists( 'first_name', $data ) ? sanitize_text_field( (string) $data['first_name'] ) : null;
		$last  = array_key_exists( 'last_name', $data ) ? sanitize_text_field( (string) $data['last_name'] ) : null;
		$email = array_key_exists( 'email', $data ) ? sanitize_email( (string) $data['email'] ) : null;
		$phone = array_key_exists( 'phone', $data ) ? sanitize_text_field( (string) $data['phone'] ) : null;
		$level = array_key_exists( 'learning_level', $data ) ? sanitize_text_field( (string) $data['learning_level'] ) : null;
		$goals = array_key_exists( 'learning_goals', $data ) ? sanitize_textarea_field( (string) $data['learning_goals'] ) : null;
		$status = array_key_exists( 'status', $data ) ? sanitize_key( (string) $data['status'] ) : null;

		if ( null !== $email && ! is_email( $email ) ) {
			return new WP_Error( 'gmm_email', __( 'Invalid email address.', 'gospel-music-mastery' ) );
		}

		if ( $user_id ) {
			$wp_update = array( 'ID' => $user_id );
			if ( null !== $first ) {
				$wp_update['first_name'] = $first;
			}
			if ( null !== $last ) {
				$wp_update['last_name'] = $last;
			}
			if ( null !== $first || null !== $last ) {
				$display = trim( ( null !== $first ? $first : (string) $row['first_name'] ) . ' ' . ( null !== $last ? $last : (string) $row['last_name'] ) );
				if ( $display ) {
					$wp_update['display_name'] = $display;
				}
			}
			if ( null !== $email ) {
				$wp_update['user_email'] = $email;
			}
			if ( count( $wp_update ) > 1 ) {
				$result = wp_update_user( $wp_update );
				if ( is_wp_error( $result ) ) {
					return $result;
				}
			}
		}

		$update = array( 'updated_at' => current_time( 'mysql' ) );
		if ( null !== $first ) {
			$update['first_name'] = $first;
		}
		if ( null !== $last ) {
			$update['last_name'] = $last;
		}
		if ( null !== $email ) {
			$update['email'] = $email;
		}
		if ( null !== $phone ) {
			$update['phone'] = $phone;
		}
		if ( null !== $level ) {
			$update['learning_level'] = $level;
		}
		if ( null !== $goals ) {
			$update['learning_goals'] = $goals;
		}
		if ( null !== $status && in_array( $status, array( 'active', 'inactive', 'suspended', 'pending' ), true ) ) {
			$update['status'] = $status;
		}

		global $wpdb;
		$table   = GMM_Database::table( 'students' );
		$updated = $wpdb->update( $table, $update, array( 'id' => $student_id ) );
		if ( false === $updated ) {
			return new WP_Error( 'gmm_db', __( 'Could not update student.', 'gospel-music-mastery' ) );
		}

		if ( $user_id && isset( $update['status'] ) ) {
			update_user_meta( $user_id, 'gmm_student_status', $update['status'] );
		}

		/**
		 * Fires after admin edits a student.
		 *
		 * @param int                  $student_id Student ID.
		 * @param array<string, mixed> $update     Updated fields.
		 */
		do_action( 'gmm_admin_student_updated', $student_id, $update );

		self::flush_cache();
		return true;
	}

	/**
	 * @param int    $student_id Student ID.
	 * @param string $status     Status.
	 * @return true|WP_Error
	 */
	public static function set_status( $student_id, $status ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$student_id = absint( $student_id );
		$status     = sanitize_key( $status );
		if ( ! in_array( $status, array( 'active', 'inactive', 'suspended', 'pending' ), true ) ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid status.', 'gospel-music-mastery' ) );
		}

		$row = self::get_raw_row( $student_id );
		if ( ! $row ) {
			return new WP_Error( 'gmm_missing', __( 'Student not found.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'students' );
		$updated = $wpdb->update(
			$table,
			array(
				'status'     => $status,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $student_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db', __( 'Could not update student status.', 'gospel-music-mastery' ) );
		}

		$user_id = absint( $row['user_id'] );
		if ( $user_id ) {
			update_user_meta( $user_id, 'gmm_student_status', $status );
		}

		$row['status'] = $status;

		if ( 'active' === $status ) {
			do_action( 'gmm_student_activated', $student_id, $row );
		} elseif ( 'inactive' === $status ) {
			do_action( 'gmm_student_deactivated', $student_id, $row );
		} elseif ( 'suspended' === $status ) {
			do_action( 'gmm_student_suspended', $student_id, $row );
		}

		self::flush_cache();
		return true;
	}

	/**
	 * Soft-delete student plugin data.
	 *
	 * @param int $student_id Student ID.
	 * @return true|WP_Error
	 */
	public static function delete_student( $student_id ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$student_id = absint( $student_id );
		$row        = self::get_raw_row( $student_id );
		if ( ! $row ) {
			return new WP_Error( 'gmm_missing', __( 'Student not found.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$students = GMM_Database::table( 'students' );
		$fav      = GMM_Database::table( 'favourites' );
		$trash    = self::trash_status();

		$updated = $wpdb->update(
			$students,
			array(
				'status'     => $trash,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $student_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db', __( 'Could not delete student.', 'gospel-music-mastery' ) );
		}

		// Remove favourites (plugin relation only).
		$wpdb->delete( $fav, array( 'student_id' => $student_id ), array( '%d' ) );

		$user_id = absint( $row['user_id'] );
		if ( $user_id ) {
			update_user_meta( $user_id, 'gmm_student_status', $trash );

			/**
			 * Whether to permanently delete the linked WordPress user.
			 *
			 * @param bool $delete     Default false.
			 * @param int  $student_id Student ID.
			 * @param int  $user_id    WP user ID.
			 */
			$delete_user = (bool) apply_filters( 'gmm_admin_delete_student_wp_user', false, $student_id, $user_id );
			if ( $delete_user && current_user_can( 'delete_users' ) && get_current_user_id() !== $user_id ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
				wp_delete_user( $user_id );
			}
		}

		do_action( 'gmm_student_deleted', $student_id, $row );
		self::flush_cache();
		return true;
	}

	/**
	 * @param array<int, int> $ids    IDs.
	 * @param string          $action activate|suspend|delete.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function bulk_action( $ids, $action ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$action = sanitize_key( $action );
		$ids    = array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );
		if ( empty( $ids ) ) {
			return new WP_Error( 'gmm_invalid', __( 'No students selected.', 'gospel-music-mastery' ) );
		}

		$ok     = 0;
		$fail   = 0;
		$errors = array();

		foreach ( $ids as $id ) {
			switch ( $action ) {
				case 'activate':
					$result = self::set_status( $id, 'active' );
					break;
				case 'deactivate':
					$result = self::set_status( $id, 'inactive' );
					break;
				case 'suspend':
					$result = self::set_status( $id, 'suspended' );
					break;
				case 'delete':
					$result = self::delete_student( $id );
					break;
				default:
					return new WP_Error( 'gmm_invalid', __( 'Invalid bulk action.', 'gospel-music-mastery' ) );
			}

			if ( is_wp_error( $result ) ) {
				++$fail;
				$errors[ $id ] = $result->get_error_message();
			} else {
				++$ok;
			}
		}

		return array(
			'updated' => $ok,
			'failed'  => $fail,
			'errors'  => $errors,
		);
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
		if ( ! has_shortcode( $content, 'gmm_admin_students' ) && false === strpos( $content, 'gmm_admin_students' ) ) {
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
		if ( 'gmm-students' !== $page && false === strpos( (string) $hook, 'gmm-students' ) ) {
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
			'gmm-admin-students',
			GMM_URL . 'assets/js/gmm-admin-students.js',
			array( 'gmm-core-script', 'gmm-ajax-script' ),
			$version,
			true
		);

		wp_localize_script(
			'gmm-admin-students',
			'GMM_ADMIN_STUDENTS',
			array(
				'nonce'   => wp_create_nonce( 'gmm_nonce' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'i18n'    => array(
					'activated'     => __( 'Student activated.', 'gospel-music-mastery' ),
					'deactivated'   => __( 'Student deactivated.', 'gospel-music-mastery' ),
					'suspended'     => __( 'Student suspended.', 'gospel-music-mastery' ),
					'deleted'       => __( 'Student deleted.', 'gospel-music-mastery' ),
					'updated'       => __( 'Student updated.', 'gospel-music-mastery' ),
					'confirmDelete' => __( 'Delete this student and related plugin data? The WordPress user account will not be removed.', 'gospel-music-mastery' ),
					'editPrompt'    => __( 'Update student (JSON or leave blank to cancel). Prefer using profile fields.', 'gospel-music-mastery' ),
					'error'         => __( 'Action failed. Please try again.', 'gospel-music-mastery' ),
					'bulkDone'      => __( 'Bulk action completed.', 'gospel-music-mastery' ),
				),
			)
		);
	}

	/**
	 * @param array<string, mixed> $row DB row.
	 * @return array<string, mixed>
	 */
	private static function format_student_row( $row ) {
		$first = isset( $row['first_name'] ) ? (string) $row['first_name'] : '';
		$last  = isset( $row['last_name'] ) ? (string) $row['last_name'] : '';
		$name  = trim( $first . ' ' . $last );
		if ( '' === $name ) {
			$name = __( 'Student', 'gospel-music-mastery' );
		}

		$status = isset( $row['status'] ) ? sanitize_key( (string) $row['status'] ) : 'active';
		$level_raw = isset( $row['learning_level'] ) ? (string) $row['learning_level'] : '';
		$level     = self::normalize_level( $level_raw );

		$image = self::resolve_image(
			isset( $row['profile_image'] ) ? (string) $row['profile_image'] : '',
			'assets/img/team/02.jpg'
		);

		$joined = '—';
		$period = 'year';
		if ( ! empty( $row['created_at'] ) && '0000-00-00 00:00:00' !== $row['created_at'] ) {
			$ts = strtotime( (string) $row['created_at'] );
			if ( $ts ) {
				$joined = wp_date( 'M j, Y', $ts );
				$period = self::period_bucket( $ts );
			}
		}

		$classes  = isset( $row['class_count'] ) ? absint( $row['class_count'] ) : 0;
		$lessons  = isset( $row['lesson_count'] ) ? absint( $row['lesson_count'] ) : 0;
		$bookings = isset( $row['booking_count'] ) ? absint( $row['booking_count'] ) : 0;
		if ( ! $classes && $bookings ) {
			$classes = $bookings;
		}

		$instruments = isset( $row['preferred_instruments'] ) ? (string) $row['preferred_instruments'] : '';
		$bio         = isset( $row['bio'] ) ? (string) $row['bio'] : '';
		$goals       = isset( $row['learning_goals'] ) ? (string) $row['learning_goals'] : '';

		return array(
			'id'              => absint( $row['id'] ),
			'user_id'         => absint( isset( $row['user_id'] ) ? $row['user_id'] : 0 ),
			'name'            => $name,
			'first_name'      => $first,
			'last_name'       => $last,
			'email'           => isset( $row['email'] ) ? (string) $row['email'] : '',
			'phone'           => isset( $row['phone'] ) ? (string) $row['phone'] : '',
			'learning_level'  => $level_raw ? $level_raw : __( '—', 'gospel-music-mastery' ),
			'level'           => $level,
			'level_label'     => self::level_label( $level ),
			'instruments'     => $instruments ? $instruments : '—',
			'learning_goals'  => $goals,
			'bio'             => $bio,
			'classes'         => $classes,
			'lessons'         => $lessons,
			'bookings'        => $bookings,
			'status'          => $status,
			'status_label'    => self::status_label( $status ),
			'status_class'    => self::status_badge_class( $status ),
			'image'           => $image,
			'joined'          => $joined,
			'period'          => $period,
		);
	}

	/**
	 * @param array<int, string> $where  Where.
	 * @param array<int, mixed>  $params Params.
	 * @param string             $period Period key.
	 * @return void
	 */
	private static function append_period_filter( &$where, &$params, $period ) {
		$period = sanitize_key( $period );
		$today  = wp_date( 'Y-m-d' );

		switch ( $period ) {
			case 'today':
				$where[]  = 'DATE(s.created_at) = %s';
				$params[] = $today;
				break;
			case 'month':
				$where[]  = 'DATE(s.created_at) >= %s';
				$params[] = wp_date( 'Y-m-01' );
				break;
			case 'year':
				$where[]  = 'DATE(s.created_at) >= %s';
				$params[] = wp_date( 'Y-01-01' );
				break;
			default:
				break;
		}
	}

	/**
	 * @param int $ts Timestamp.
	 * @return string
	 */
	private static function period_bucket( $ts ) {
		$today = strtotime( wp_date( 'Y-m-d' ) . ' 00:00:00' );
		if ( $ts >= $today ) {
			return 'today';
		}
		$month = strtotime( wp_date( 'Y-m-01' ) . ' 00:00:00' );
		if ( $ts >= $month ) {
			return 'month';
		}
		$year = strtotime( wp_date( 'Y-01-01' ) . ' 00:00:00' );
		if ( $ts >= $year ) {
			return 'year';
		}
		return 'year';
	}

	/**
	 * @param int $student_id ID.
	 * @return array<string, mixed>|null
	 */
	private static function get_raw_row( $student_id ) {
		global $wpdb;
		$table = GMM_Database::table( 'students' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", absint( $student_id ) ),
			ARRAY_A
		);
		return is_array( $row ) ? $row : null;
	}

	/**
	 * @param string $raw Path/URL/ID.
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
	 * @param string $level Level.
	 * @return string
	 */
	private static function normalize_level( $level ) {
		$l = strtolower( trim( (string) $level ) );
		if ( false !== strpos( $l, 'begin' ) ) {
			return 'beginner';
		}
		if ( false !== strpos( $l, 'inter' ) ) {
			return 'intermediate';
		}
		if ( false !== strpos( $l, 'advanc' ) ) {
			return 'advanced';
		}
		return in_array( $l, array( 'beginner', 'intermediate', 'advanced' ), true ) ? $l : 'all';
	}

	/**
	 * @param string $level Level slug.
	 * @return string
	 */
	private static function level_label( $level ) {
		$labels = array(
			'beginner'     => __( 'Beginner', 'gospel-music-mastery' ),
			'intermediate' => __( 'Intermediate', 'gospel-music-mastery' ),
			'advanced'     => __( 'Advanced', 'gospel-music-mastery' ),
		);
		return isset( $labels[ $level ] ) ? $labels[ $level ] : __( '—', 'gospel-music-mastery' );
	}

	/**
	 * @param string $status Status.
	 * @return string
	 */
	private static function status_label( $status ) {
		$labels = array(
			'active'    => __( 'Active', 'gospel-music-mastery' ),
			'inactive'  => __( 'Inactive', 'gospel-music-mastery' ),
			'suspended' => __( 'Suspended', 'gospel-music-mastery' ),
			'pending'   => __( 'Pending', 'gospel-music-mastery' ),
		);
		return isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
	}

	/**
	 * @param string $status Status.
	 * @return string
	 */
	private static function status_badge_class( $status ) {
		$map = array(
			'active'    => 'is-confirmed',
			'inactive'  => 'is-inactive',
			'suspended' => 'is-suspended',
			'pending'   => 'is-pending',
		);
		return isset( $map[ $status ] ) ? $map[ $status ] : 'is-confirmed';
	}

	/**
	 * @return string
	 */
	private static function trash_status() {
		return class_exists( 'GMM_Security' ) ? GMM_Security::soft_delete_status() : 'trash';
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
			'active'    => 0,
			'new'       => 0,
			'suspended' => 0,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function empty_filters() {
		return array(
			'search'   => '',
			'status'   => 'all',
			'level'    => 'all',
			'period'   => 'all',
			'page'     => 1,
			'per_page' => self::PER_PAGE,
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
		return array_merge(
			array( 'items' => array() ),
			self::empty_pagination()
		);
	}
}
