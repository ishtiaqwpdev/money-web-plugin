<?php
/**
 * Admin class management controller.
 *
 * Supplies list/search/filter/pagination and approve/reject/edit/featured
 * actions for templates/admin/classes.php without changing the frozen UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Admin_Classes
 */
class GMM_Admin_Classes {

	const PER_PAGE    = 8;
	const CACHE_GROUP = 'gmm_admin_classes';
	const CACHE_TTL   = 60;

	/**
	 * Statuses visible on the public frontend.
	 *
	 * @return array<int, string>
	 */
	public static function public_statuses() {
		return array( 'approved', 'published' );
	}

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
		if ( 'gmm_admin_classes' !== $tag ) {
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
		if ( 'classes' !== $page ) {
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
				'gmm_admin_denied' => true,
				'classes'          => array(),
				'class_stats'      => self::empty_stats(),
				'filters'          => self::empty_filters(),
				'pagination'       => self::empty_pagination(),
				'featured_classes' => array(),
			);
		}

		$filters = self::get_request_filters();
		$list    = self::list_classes( $filters );

		return array(
			'gmm_admin_denied' => false,
			'classes'          => isset( $list['items'] ) ? $list['items'] : array(),
			'class_stats'      => self::get_stats(),
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
			'featured_classes' => self::get_featured_classes( 8 ),
			'logout_url'       => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
			'last_login_label' => self::format_last_login(),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function get_request_filters() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$search = isset( $_GET['ac_search'] ) ? sanitize_text_field( wp_unslash( $_GET['ac_search'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = isset( $_GET['ac_status'] ) ? sanitize_key( wp_unslash( $_GET['ac_status'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$category = isset( $_GET['ac_category'] ) ? sanitize_key( wp_unslash( $_GET['ac_category'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$difficulty = isset( $_GET['ac_difficulty'] ) ? sanitize_key( wp_unslash( $_GET['ac_difficulty'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['ac_page'] ) ? absint( $_GET['ac_page'] ) : 1;

		if ( ! in_array( $status, array( 'all', 'draft', 'pending', 'approved', 'rejected' ), true ) ) {
			$status = 'all';
		}
		if ( ! in_array( $category, array( 'all', 'piano', 'vocals', 'guitar', 'drums', 'theory' ), true ) ) {
			$category = 'all';
		}
		if ( ! in_array( $difficulty, array( 'all', 'beginner', 'intermediate', 'advanced' ), true ) ) {
			$difficulty = 'all';
		}

		return array(
			'search'     => $search,
			'status'     => $status,
			'category'   => $category,
			'difficulty' => $difficulty,
			'page'       => max( 1, $page ),
			'per_page'   => self::PER_PAGE,
		);
	}

	/**
	 * @param array<string, mixed> $args Args.
	 * @return array<string, mixed>
	 */
	public static function list_classes( $args = array() ) {
		if ( ! self::user_can_manage() ) {
			return self::empty_list();
		}

		$args = wp_parse_args(
			$args,
			array(
				'search'     => '',
				'status'     => 'all',
				'category'   => 'all',
				'difficulty' => 'all',
				'page'       => 1,
				'per_page'   => self::PER_PAGE,
			)
		);

		$page     = max( 1, absint( $args['page'] ) );
		$per_page = max( 1, min( 50, absint( $args['per_page'] ) ) );
		$offset   = ( $page - 1 ) * $per_page;

		global $wpdb;
		$classes  = GMM_Database::table( 'classes' );
		$teachers = GMM_Database::table( 'teachers' );
		$bookings = GMM_Database::table( 'bookings' );
		$payments = GMM_Database::table( 'payments' );

		$where  = array( 'c.status <> %s' );
		$params = array( self::trash_status() );

		$search = trim( (string) $args['search'] );
		if ( '' !== $search ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '(c.title LIKE %s OR c.category LIKE %s OR t.first_name LIKE %s OR t.last_name LIKE %s OR CONCAT(t.first_name, \' \', t.last_name) LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$db_statuses = self::ui_status_to_db( (string) $args['status'] );
		if ( ! empty( $db_statuses ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $db_statuses ), '%s' ) );
			$where[]      = "c.status IN ({$placeholders})";
			foreach ( $db_statuses as $st ) {
				$params[] = $st;
			}
		}

		$category = sanitize_key( (string) $args['category'] );
		if ( $category && 'all' !== $category ) {
			$label = self::category_label( $category );
			$like  = '%' . $wpdb->esc_like( $category ) . '%';
			if ( $label ) {
				$like2    = '%' . $wpdb->esc_like( $label ) . '%';
				$where[]  = '(c.category LIKE %s OR c.category LIKE %s)';
				$params[] = $like;
				$params[] = $like2;
			} else {
				$where[]  = 'c.category LIKE %s';
				$params[] = $like;
			}
		}

		$difficulty = sanitize_key( (string) $args['difficulty'] );
		if ( $difficulty && 'all' !== $difficulty ) {
			$where[]  = 'LOWER(c.difficulty) = %s';
			$params[] = $difficulty;
		}

		$where_sql = implode( ' AND ', $where );

		$count_sql = "SELECT COUNT(*) FROM {$classes} c LEFT JOIN {$teachers} t ON t.id = c.teacher_id WHERE {$where_sql}";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) );

		$sql = "SELECT c.*, t.first_name AS teacher_first_name, t.last_name AS teacher_last_name, t.email AS teacher_email,
			(SELECT COUNT(DISTINCT b.student_id) FROM {$bookings} b WHERE b.class_id = c.id) AS student_count,
			(SELECT COUNT(*) FROM {$bookings} b WHERE b.class_id = c.id) AS booking_count,
			(SELECT COALESCE(SUM(p.amount),0) FROM {$payments} p WHERE p.teacher_id = c.teacher_id AND p.payment_status IN ('completed','paid') AND EXISTS (
				SELECT 1 FROM {$bookings} b2 WHERE b2.id = p.booking_id AND b2.class_id = c.id
			)) AS revenue
			FROM {$classes} c
			LEFT JOIN {$teachers} t ON t.id = c.teacher_id
			WHERE {$where_sql}
			ORDER BY c.created_at DESC, c.id DESC
			LIMIT %d OFFSET %d";

		$qparams   = $params;
		$qparams[] = $per_page;
		$qparams[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $qparams ), ARRAY_A );
		$rows = is_array( $rows ) ? $rows : array();

		$items = array();
		foreach ( $rows as $row ) {
			$items[] = self::format_class_row( $row );
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
		$table = GMM_Database::table( 'classes' );
		$trash = self::trash_status();

		$total = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status <> %s", $trash )
		);
		$approved = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE status IN (%s, %s)",
				'approved',
				'published'
			)
		);
		$pending = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status = %s", 'pending' )
		);
		$rejected = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE status = %s", 'rejected' )
		);

		$stats = array(
			'total'    => $total,
			'approved' => $approved,
			'pending'  => $pending,
			'rejected' => $rejected,
		);

		set_transient( self::CACHE_GROUP . '_stats', $stats, self::CACHE_TTL );
		return $stats;
	}

	/**
	 * @param int $limit Limit.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_featured_classes( $limit = 8 ) {
		$limit = max( 1, min( 20, absint( $limit ) ) );
		global $wpdb;
		$classes  = GMM_Database::table( 'classes' );
		$teachers = GMM_Database::table( 'teachers' );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT c.*, t.first_name AS teacher_first_name, t.last_name AS teacher_last_name
				FROM {$classes} c
				LEFT JOIN {$teachers} t ON t.id = c.teacher_id
				WHERE c.featured = 1 AND c.status <> %s
				ORDER BY c.updated_at DESC, c.id DESC
				LIMIT %d",
				self::trash_status(),
				$limit
			),
			ARRAY_A
		);

		$items = array();
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$items[] = self::format_class_row( $row );
			}
		}
		return $items;
	}

	/**
	 * @param int $class_id Class ID.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function get_profile( $class_id ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$class_id = absint( $class_id );
		$row      = self::get_raw_row( $class_id );
		if ( ! $row ) {
			return new WP_Error( 'gmm_missing', __( 'Class not found.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$teachers = GMM_Database::table( 'teachers' );
		$bookings = GMM_Database::table( 'bookings' );
		$reviews  = GMM_Database::table( 'reviews' );
		$payments = GMM_Database::table( 'payments' );

		$teacher = null;
		if ( ! empty( $row['teacher_id'] ) ) {
			$teacher = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$teachers} WHERE id = %d LIMIT 1", absint( $row['teacher_id'] ) ),
				ARRAY_A
			);
		}

		$booking_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, student_id, booking_date, amount, booking_status, payment_status
				FROM {$bookings} WHERE class_id = %d
				ORDER BY booking_date DESC LIMIT 20",
				$class_id
			),
			ARRAY_A
		);

		$review_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, rating, comment, status, created_at FROM {$reviews}
				WHERE class_id = %d ORDER BY created_at DESC LIMIT 20",
				$class_id
			),
			ARRAY_A
		);

		$revenue = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(p.amount),0) FROM {$payments} p
				INNER JOIN {$bookings} b ON b.id = p.booking_id
				WHERE b.class_id = %d AND p.payment_status IN ('completed','paid')",
				$class_id
			)
		);

		$row['teacher_first_name'] = is_array( $teacher ) ? $teacher['first_name'] : '';
		$row['teacher_last_name']  = is_array( $teacher ) ? $teacher['last_name'] : '';
		$row['teacher_email']      = is_array( $teacher ) ? $teacher['email'] : '';
		$row['revenue']            = $revenue;

		$formatted = self::format_class_row( $row );

		return array(
			'class'    => $formatted,
			'teacher'  => is_array( $teacher ) ? $teacher : array(),
			'bookings' => is_array( $booking_rows ) ? $booking_rows : array(),
			'reviews'  => is_array( $review_rows ) ? $review_rows : array(),
			'revenue'  => round( $revenue, 2 ),
		);
	}

	/**
	 * @param int $class_id Class ID.
	 * @return true|WP_Error
	 */
	public static function approve( $class_id ) {
		return self::set_status( $class_id, 'approved' );
	}

	/**
	 * @param int    $class_id Class ID.
	 * @param string $reason   Reason.
	 * @return true|WP_Error
	 */
	public static function reject( $class_id, $reason = '' ) {
		$result = self::set_status( $class_id, 'rejected' );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		$reason = sanitize_textarea_field( (string) $reason );
		if ( '' !== $reason ) {
			update_option( 'gmm_class_reject_reason_' . absint( $class_id ), $reason, false );
		}
		return true;
	}

	/**
	 * @param int    $class_id Class ID.
	 * @param string $status   Status.
	 * @return true|WP_Error
	 */
	public static function set_status( $class_id, $status ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$class_id = absint( $class_id );
		$status   = sanitize_key( $status );
		$allowed  = array( 'draft', 'pending', 'approved', 'published', 'rejected', 'archived' );

		if ( ! $class_id || ! in_array( $status, $allowed, true ) ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid class or status.', 'gospel-music-mastery' ) );
		}

		$row = self::get_raw_row( $class_id );
		if ( ! $row ) {
			return new WP_Error( 'gmm_missing', __( 'Class not found.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'classes' );
		$updated = $wpdb->update(
			$table,
			array(
				'status'     => $status,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $class_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db', __( 'Could not update class.', 'gospel-music-mastery' ) );
		}

		$row['status'] = $status;

		if ( in_array( $status, array( 'approved', 'published' ), true ) ) {
			/**
			 * Fires when a class is approved.
			 *
			 * @param int                  $class_id Class ID.
			 * @param array<string, mixed> $row      Class row.
			 */
			do_action( 'gmm_class_approved', $class_id, $row );
		} elseif ( 'rejected' === $status ) {
			/**
			 * Fires when a class is rejected.
			 *
			 * @param int                  $class_id Class ID.
			 * @param array<string, mixed> $row      Class row.
			 */
			do_action( 'gmm_class_rejected', $class_id, $row );
		}

		self::flush_cache();
		return true;
	}

	/**
	 * @param int                  $class_id Class ID.
	 * @param array<string, mixed> $data     Fields.
	 * @return true|WP_Error
	 */
	public static function edit_class( $class_id, $data ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$class_id = absint( $class_id );
		$row      = self::get_raw_row( $class_id );
		if ( ! $row ) {
			return new WP_Error( 'gmm_missing', __( 'Class not found.', 'gospel-music-mastery' ) );
		}

		$data   = is_array( $data ) ? $data : array();
		$update = array( 'updated_at' => current_time( 'mysql' ) );

		if ( array_key_exists( 'title', $data ) ) {
			$title = sanitize_text_field( (string) $data['title'] );
			if ( '' === $title ) {
				return new WP_Error( 'gmm_title', __( 'Title is required.', 'gospel-music-mastery' ) );
			}
			$update['title'] = $title;
		}
		if ( array_key_exists( 'description', $data ) ) {
			$update['description'] = wp_kses_post( (string) $data['description'] );
		}
		if ( array_key_exists( 'category', $data ) ) {
			$update['category'] = sanitize_text_field( (string) $data['category'] );
		}
		if ( array_key_exists( 'difficulty', $data ) ) {
			$diff = sanitize_key( (string) $data['difficulty'] );
			if ( in_array( $diff, array( 'beginner', 'intermediate', 'advanced' ), true ) ) {
				$update['difficulty'] = $diff;
			}
		}
		if ( array_key_exists( 'duration', $data ) ) {
			$update['duration'] = absint( $data['duration'] );
		}
		if ( array_key_exists( 'price', $data ) ) {
			$update['price'] = round( max( 0, (float) $data['price'] ), 2 );
		}
		if ( array_key_exists( 'image', $data ) ) {
			$update['image'] = esc_url_raw( (string) $data['image'] );
		}
		if ( array_key_exists( 'status', $data ) ) {
			$status = sanitize_key( (string) $data['status'] );
			if ( in_array( $status, array( 'draft', 'pending', 'approved', 'published', 'rejected', 'archived' ), true ) ) {
				$update['status'] = $status;
			}
		}

		if ( count( $update ) < 2 ) {
			return new WP_Error( 'gmm_no_fields', __( 'No valid fields to update.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'classes' );
		$updated = $wpdb->update( $table, $update, array( 'id' => $class_id ) );
		if ( false === $updated ) {
			return new WP_Error( 'gmm_db', __( 'Could not update class.', 'gospel-music-mastery' ) );
		}

		if ( isset( $update['status'] ) ) {
			$merged = array_merge( $row, $update );
			if ( in_array( $update['status'], array( 'approved', 'published' ), true ) ) {
				do_action( 'gmm_class_approved', $class_id, $merged );
			} elseif ( 'rejected' === $update['status'] ) {
				do_action( 'gmm_class_rejected', $class_id, $merged );
			}
		}

		do_action( 'gmm_admin_class_updated', $class_id, $update );
		self::flush_cache();
		return true;
	}

	/**
	 * @param int  $class_id Class ID.
	 * @param bool $featured Featured.
	 * @return true|WP_Error
	 */
	public static function set_featured( $class_id, $featured ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$class_id = absint( $class_id );
		if ( ! self::get_raw_row( $class_id ) ) {
			return new WP_Error( 'gmm_missing', __( 'Class not found.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'classes' );
		$updated = $wpdb->update(
			$table,
			array(
				'featured'   => $featured ? 1 : 0,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $class_id ),
			array( '%d', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db', __( 'Could not update featured flag.', 'gospel-music-mastery' ) );
		}

		do_action( 'gmm_class_featured_updated', $class_id, (bool) $featured );
		self::flush_cache();
		return true;
	}

	/**
	 * Soft-delete class. Does not touch teacher account.
	 *
	 * @param int $class_id Class ID.
	 * @return true|WP_Error
	 */
	public static function delete_class( $class_id ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$class_id = absint( $class_id );
		$row      = self::get_raw_row( $class_id );
		if ( ! $row ) {
			return new WP_Error( 'gmm_missing', __( 'Class not found.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'classes' );
		$updated = $wpdb->update(
			$table,
			array(
				'status'     => self::trash_status(),
				'featured'   => 0,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $class_id ),
			array( '%s', '%d', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db', __( 'Could not delete class.', 'gospel-music-mastery' ) );
		}

		do_action( 'gmm_class_deleted', $class_id, $row );
		self::flush_cache();
		return true;
	}

	/**
	 * @param array<int, int> $ids    IDs.
	 * @param string          $action Action.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function bulk_action( $ids, $action ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$action = sanitize_key( $action );
		$ids    = array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );
		if ( empty( $ids ) ) {
			return new WP_Error( 'gmm_invalid', __( 'No classes selected.', 'gospel-music-mastery' ) );
		}

		$ok     = 0;
		$fail   = 0;
		$errors = array();

		foreach ( $ids as $id ) {
			switch ( $action ) {
				case 'approve':
					$result = self::approve( $id );
					break;
				case 'reject':
					$result = self::reject( $id );
					break;
				case 'delete':
					$result = self::delete_class( $id );
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
		if ( ! has_shortcode( $content, 'gmm_admin_classes' ) && false === strpos( $content, 'gmm_admin_classes' ) ) {
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
		if ( 'gmm-classes' !== $page && false === strpos( (string) $hook, 'gmm-classes' ) ) {
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
			'gmm-admin-classes',
			GMM_URL . 'assets/js/gmm-admin-classes.js',
			array( 'gmm-core-script', 'gmm-ajax-script' ),
			$version,
			true
		);

		wp_localize_script(
			'gmm-admin-classes',
			'GMM_ADMIN_CLASSES',
			array(
				'nonce'   => wp_create_nonce( 'gmm_nonce' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'i18n'    => array(
					'approved'      => __( 'Class approved.', 'gospel-music-mastery' ),
					'rejected'      => __( 'Class rejected.', 'gospel-music-mastery' ),
					'updated'       => __( 'Class updated.', 'gospel-music-mastery' ),
					'deleted'       => __( 'Class deleted.', 'gospel-music-mastery' ),
					'featured'      => __( 'Featured status updated.', 'gospel-music-mastery' ),
					'confirmDelete' => __( 'Delete this class? Related plugin data will be soft-deleted. The teacher account will not be removed.', 'gospel-music-mastery' ),
					'rejectPrompt'  => __( 'Optional rejection reason:', 'gospel-music-mastery' ),
					'error'         => __( 'Action failed. Please try again.', 'gospel-music-mastery' ),
					'bulkDone'      => __( 'Bulk action completed.', 'gospel-music-mastery' ),
				),
			)
		);
	}

	/**
	 * @param string $ui_status UI status.
	 * @return array<int, string>
	 */
	public static function ui_status_to_db( $ui_status ) {
		switch ( sanitize_key( $ui_status ) ) {
			case 'draft':
				return array( 'draft' );
			case 'pending':
				return array( 'pending' );
			case 'approved':
				return array( 'approved', 'published' );
			case 'rejected':
				return array( 'rejected' );
			default:
				return array();
		}
	}

	/**
	 * @param string $db_status DB status.
	 * @return string
	 */
	public static function db_status_to_ui( $db_status ) {
		$db_status = sanitize_key( $db_status );
		if ( 'published' === $db_status ) {
			return 'approved';
		}
		return $db_status;
	}

	/**
	 * @param array<string, mixed> $row Row.
	 * @return array<string, mixed>
	 */
	private static function format_class_row( $row ) {
		$title = isset( $row['title'] ) ? (string) $row['title'] : __( 'Class', 'gospel-music-mastery' );
		$tfirst = isset( $row['teacher_first_name'] ) ? (string) $row['teacher_first_name'] : '';
		$tlast  = isset( $row['teacher_last_name'] ) ? (string) $row['teacher_last_name'] : '';
		$teacher = trim( $tfirst . ' ' . $tlast );
		if ( '' === $teacher ) {
			$teacher = __( 'Teacher', 'gospel-music-mastery' );
		}

		$db_status = isset( $row['status'] ) ? sanitize_key( (string) $row['status'] ) : 'draft';
		$ui_status = self::db_status_to_ui( $db_status );
		$cat_raw   = isset( $row['category'] ) ? (string) $row['category'] : '';
		$category  = self::detect_category_slug( $cat_raw );
		$diff      = isset( $row['difficulty'] ) ? sanitize_key( (string) $row['difficulty'] ) : '';
		$duration  = isset( $row['duration'] ) ? absint( $row['duration'] ) : 0;
		$price     = isset( $row['price'] ) ? (float) $row['price'] : 0.0;
		$rating    = isset( $row['rating'] ) ? number_format( (float) $row['rating'], 1 ) : '0.0';
		$students  = isset( $row['student_count'] ) ? absint( $row['student_count'] ) : 0;
		$bookings  = isset( $row['booking_count'] ) ? absint( $row['booking_count'] ) : 0;
		$revenue   = isset( $row['revenue'] ) ? round( (float) $row['revenue'], 2 ) : 0.0;
		$featured  = ! empty( $row['featured'] );

		$image = self::resolve_image(
			isset( $row['image'] ) ? (string) $row['image'] : '',
			'assets/img/course/01.jpg'
		);

		$created = '—';
		if ( ! empty( $row['created_at'] ) && '0000-00-00 00:00:00' !== $row['created_at'] ) {
			$ts = strtotime( (string) $row['created_at'] );
			if ( $ts ) {
				$created = wp_date( 'M j, Y', $ts );
			}
		}

		return array(
			'id'              => absint( $row['id'] ),
			'teacher_id'      => absint( isset( $row['teacher_id'] ) ? $row['teacher_id'] : 0 ),
			'title'           => $title,
			'teacher'         => $teacher,
			'teacher_email'   => isset( $row['teacher_email'] ) ? (string) $row['teacher_email'] : '',
			'description'     => isset( $row['description'] ) ? (string) $row['description'] : '',
			'category'        => $category,
			'category_label'  => $cat_raw ? $cat_raw : self::category_label( $category ),
			'difficulty'      => $diff,
			'difficulty_label'=> self::difficulty_label( $diff ),
			'duration'        => $duration,
			'duration_label'  => $duration ? sprintf( '%d Minutes', $duration ) : '—',
			'price'           => $price,
			'price_label'     => '$' . number_format( $price, 0 ),
			'rating'          => $rating,
			'students'        => $students,
			'bookings'        => $bookings,
			'revenue'         => $revenue,
			'status'          => $ui_status,
			'db_status'       => $db_status,
			'status_label'    => self::status_label( $ui_status ),
			'status_class'    => self::status_badge_class( $ui_status ),
			'featured'        => $featured,
			'image'           => $image,
			'created'         => $created,
		);
	}

	/**
	 * @param int $class_id ID.
	 * @return array<string, mixed>|null
	 */
	private static function get_raw_row( $class_id ) {
		global $wpdb;
		$table = GMM_Database::table( 'classes' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", absint( $class_id ) ),
			ARRAY_A
		);
		return is_array( $row ) ? $row : null;
	}

	/**
	 * @param string $raw Raw.
	 * @param string $fallback Fallback.
	 * @return string
	 */
	private static function resolve_image( $raw, $fallback ) {
		$raw = is_string( $raw ) ? trim( $raw ) : '';
		if ( $raw && ctype_digit( $raw ) ) {
			$url = wp_get_attachment_image_url( absint( $raw ), 'medium' );
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
	 * @param string $cat Category text.
	 * @return string
	 */
	private static function detect_category_slug( $cat ) {
		$lower = strtolower( (string) $cat );
		foreach ( array( 'piano', 'vocals', 'vocal', 'guitar', 'drums', 'drum', 'theory' ) as $key ) {
			if ( false !== strpos( $lower, $key ) ) {
				if ( 'vocal' === $key ) {
					return 'vocals';
				}
				if ( 'drum' === $key ) {
					return 'drums';
				}
				return $key;
			}
		}
		return 'all';
	}

	/**
	 * @param string $slug Slug.
	 * @return string
	 */
	private static function category_label( $slug ) {
		$labels = array(
			'piano'  => 'Gospel Piano',
			'vocals' => 'Vocals',
			'guitar' => 'Guitar',
			'drums'  => 'Drums',
			'theory' => 'Music Theory',
		);
		return isset( $labels[ $slug ] ) ? $labels[ $slug ] : '';
	}

	/**
	 * @param string $diff Diff.
	 * @return string
	 */
	private static function difficulty_label( $diff ) {
		$labels = array(
			'beginner'     => __( 'Beginner', 'gospel-music-mastery' ),
			'intermediate' => __( 'Intermediate', 'gospel-music-mastery' ),
			'advanced'     => __( 'Advanced', 'gospel-music-mastery' ),
		);
		return isset( $labels[ $diff ] ) ? $labels[ $diff ] : ( $diff ? $diff : '—' );
	}

	/**
	 * @param string $status Status.
	 * @return string
	 */
	private static function status_label( $status ) {
		$labels = array(
			'draft'    => __( 'Draft', 'gospel-music-mastery' ),
			'pending'  => __( 'Pending', 'gospel-music-mastery' ),
			'approved' => __( 'Approved', 'gospel-music-mastery' ),
			'rejected' => __( 'Rejected', 'gospel-music-mastery' ),
		);
		return isset( $labels[ $status ] ) ? $labels[ $status ] : $status;
	}

	/**
	 * @param string $status Status.
	 * @return string
	 */
	private static function status_badge_class( $status ) {
		$map = array(
			'draft'    => 'is-inactive',
			'pending'  => 'is-pending',
			'approved' => 'is-confirmed',
			'rejected' => 'is-cancelled',
		);
		return isset( $map[ $status ] ) ? $map[ $status ] : 'is-pending';
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
			'total'    => 0,
			'approved' => 0,
			'pending'  => 0,
			'rejected' => 0,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function empty_filters() {
		return array(
			'search'     => '',
			'status'     => 'all',
			'category'   => 'all',
			'difficulty' => 'all',
			'page'       => 1,
			'per_page'   => self::PER_PAGE,
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
