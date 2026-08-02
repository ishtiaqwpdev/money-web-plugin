<?php
/**
 * Admin teacher management controller.
 *
 * Supplies list/search/filter/pagination and status actions for
 * templates/admin/teachers.php without changing the frozen UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Admin_Teachers
 */
class GMM_Admin_Teachers {

	const PER_PAGE    = 8;
	const CACHE_GROUP = 'gmm_admin_teachers';
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
		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 30 );
		$loader->add_action( 'admin_enqueue_scripts', $instance, 'maybe_enqueue_admin_assets', 30 );
	}

	/**
	 * Inject into [gmm_admin_teachers].
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Shortcode.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_admin_teachers' !== $tag ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * Inject into wp-admin teachers screen.
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $page Page key.
	 * @return array<string, mixed>
	 */
	public function inject_admin_page_args( $args, $page ) {
		if ( 'teachers' !== $page ) {
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
	 * Template variables for teachers.php.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_template_vars() {
		if ( ! self::user_can_manage() ) {
			return array(
				'gmm_admin_denied' => true,
				'teachers'         => array(),
				'teacher_stats'    => self::empty_stats(),
				'filters'          => self::empty_filters(),
				'pagination'       => self::empty_pagination(),
			);
		}

		$filters = self::get_request_filters();
		$list    = self::list_teachers( $filters );

		return array(
			'gmm_admin_denied' => false,
			'teachers'         => isset( $list['items'] ) ? $list['items'] : array(),
			'teacher_stats'    => self::get_stats(),
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
			'logout_url'       => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
			'last_login_label' => self::format_last_login(),
		);
	}

	/**
	 * Read filters from GET (server-side list) — no nonce required for read.
	 *
	 * @return array<string, mixed>
	 */
	public static function get_request_filters() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only list filters.
		$search = isset( $_GET['at_search'] ) ? sanitize_text_field( wp_unslash( $_GET['at_search'] ) ) : '';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$status = isset( $_GET['at_status'] ) ? sanitize_key( wp_unslash( $_GET['at_status'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$specialty = isset( $_GET['at_specialty'] ) ? sanitize_key( wp_unslash( $_GET['at_specialty'] ) ) : 'all';
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$page = isset( $_GET['at_page'] ) ? absint( $_GET['at_page'] ) : 1;

		$allowed_status = array( 'all', 'pending', 'approved', 'rejected', 'suspended' );
		if ( ! in_array( $status, $allowed_status, true ) ) {
			$status = 'all';
		}

		$allowed_spec = array( 'all', 'piano', 'vocals', 'drums', 'guitar', 'theory' );
		if ( ! in_array( $specialty, $allowed_spec, true ) ) {
			$specialty = 'all';
		}

		return array(
			'search'      => $search,
			'status'      => $status,
			'specialty'   => $specialty,
			'page'        => max( 1, $page ),
			'per_page'    => self::PER_PAGE,
		);
	}

	/**
	 * Paginated teacher list for admin UI.
	 *
	 * @param array<string, mixed> $args Filters.
	 * @return array<string, mixed>
	 */
	public static function list_teachers( $args = array() ) {
		if ( ! self::user_can_manage() ) {
			return self::empty_list();
		}

		$args = wp_parse_args(
			$args,
			array(
				'search'    => '',
				'status'    => 'all',
				'specialty' => 'all',
				'page'      => 1,
				'per_page'  => self::PER_PAGE,
			)
		);

		$page     = max( 1, absint( $args['page'] ) );
		$per_page = max( 1, min( 50, absint( $args['per_page'] ) ) );
		$offset   = ( $page - 1 ) * $per_page;

		global $wpdb;
		$teachers = GMM_Database::table( 'teachers' );
		$bookings = GMM_Database::table( 'bookings' );
		$classes  = GMM_Database::table( 'classes' );

		$where  = array( "t.status <> %s" );
		$params = array( self::trash_status() );

		$search = trim( (string) $args['search'] );
		if ( '' !== $search ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$where[]  = '(t.first_name LIKE %s OR t.last_name LIKE %s OR CONCAT(t.first_name, \' \', t.last_name) LIKE %s OR t.email LIKE %s OR t.specialization LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$db_statuses = self::ui_status_to_db( (string) $args['status'] );
		if ( ! empty( $db_statuses ) ) {
			$placeholders = implode( ',', array_fill( 0, count( $db_statuses ), '%s' ) );
			$where[]      = "t.status IN ({$placeholders})";
			foreach ( $db_statuses as $st ) {
				$params[] = $st;
			}
		}

		$specialty = sanitize_key( (string) $args['specialty'] );
		if ( $specialty && 'all' !== $specialty ) {
			$label = self::specialty_label( $specialty );
			$like  = '%' . $wpdb->esc_like( $specialty ) . '%';
			if ( $label ) {
				$like2    = '%' . $wpdb->esc_like( $label ) . '%';
				$where[]  = '(t.specialization LIKE %s OR t.specialization LIKE %s)';
				$params[] = $like;
				$params[] = $like2;
			} else {
				$where[]  = 't.specialization LIKE %s';
				$params[] = $like;
			}
		}

		$where_sql = implode( ' AND ', $where );

		$count_sql = "SELECT COUNT(*) FROM {$teachers} t WHERE {$where_sql}";
		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$total = (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) );

		$sql = "SELECT t.*,
			(SELECT COUNT(DISTINCT b.student_id) FROM {$bookings} b
				WHERE b.teacher_id = t.id AND b.booking_status IN ('confirmed','completed','upcoming')) AS student_count,
			(SELECT COUNT(*) FROM {$classes} c
				WHERE c.teacher_id = t.id AND c.status <> %s) AS class_count
			FROM {$teachers} t
			WHERE {$where_sql}
			ORDER BY t.created_at DESC, t.id DESC
			LIMIT %d OFFSET %d";

		$qparams   = array_merge( array( self::trash_status() ), $params );
		$qparams[] = $per_page;
		$qparams[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $qparams ), ARRAY_A );
		$rows = is_array( $rows ) ? $rows : array();

		$items = array();
		foreach ( $rows as $row ) {
			$items[] = self::format_teacher_row( $row );
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
	 * Aggregate status counts (excludes trash).
	 *
	 * @return array<string, int>
	 */
	public static function get_stats() {
		$cached = get_transient( self::CACHE_GROUP . '_stats' );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;
		$table = GMM_Database::table( 'teachers' );
		$trash = self::trash_status();

		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE status <> %s",
				$trash
			)
		);

		$pending = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE status = %s",
				'pending'
			)
		);

		$approved = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE status IN (%s, %s)",
				'active',
				'approved'
			)
		);

		$suspended = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE status = %s",
				'suspended'
			)
		);

		$stats = array(
			'total'     => $total,
			'pending'   => $pending,
			'approved'  => $approved,
			'suspended' => $suspended,
		);

		set_transient( self::CACHE_GROUP . '_stats', $stats, self::CACHE_TTL );
		return $stats;
	}

	/**
	 * Admin-only teacher profile payload.
	 *
	 * @param int $teacher_id Teacher row ID.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function get_profile( $teacher_id ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$teacher_id = absint( $teacher_id );
		if ( ! $teacher_id ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid teacher.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$teachers = GMM_Database::table( 'teachers' );
		$row      = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$teachers} WHERE id = %d LIMIT 1", $teacher_id ),
			ARRAY_A
		);

		if ( ! is_array( $row ) ) {
			return new WP_Error( 'gmm_missing', __( 'Teacher not found.', 'gospel-music-mastery' ) );
		}

		$formatted = self::format_teacher_row( $row );

		$classes_t  = GMM_Database::table( 'classes' );
		$bookings_t = GMM_Database::table( 'bookings' );
		$reviews_t  = GMM_Database::table( 'reviews' );
		$payments_t = GMM_Database::table( 'payments' );

		$class_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, title, category, status, price, created_at FROM {$classes_t}
				WHERE teacher_id = %d AND status <> %s
				ORDER BY created_at DESC LIMIT 20",
				$teacher_id,
				self::trash_status()
			),
			ARRAY_A
		);

		$booking_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, student_id, booking_date, booking_time, amount, booking_status, payment_status
				FROM {$bookings_t}
				WHERE teacher_id = %d
				ORDER BY booking_date DESC, id DESC LIMIT 20",
				$teacher_id
			),
			ARRAY_A
		);

		$review_rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, rating, comment, status, created_at FROM {$reviews_t}
				WHERE teacher_id = %d
				ORDER BY created_at DESC LIMIT 20",
				$teacher_id
			),
			ARRAY_A
		);

		$earnings_total = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$payments_t}
				WHERE teacher_id = %d AND payment_status IN ('completed','paid')",
				$teacher_id
			)
		);

		$earnings_pending = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$payments_t}
				WHERE teacher_id = %d AND payment_status = %s",
				$teacher_id,
				'pending'
			)
		);

		$user_id = absint( $row['user_id'] );
		$reason  = $user_id ? (string) get_user_meta( $user_id, 'gmm_rejection_reason', true ) : '';

		return array(
			'teacher'   => $formatted,
			'classes'   => is_array( $class_rows ) ? $class_rows : array(),
			'bookings'  => is_array( $booking_rows ) ? $booking_rows : array(),
			'reviews'   => is_array( $review_rows ) ? $review_rows : array(),
			'earnings'  => array(
				'total'   => round( $earnings_total, 2 ),
				'pending' => round( $earnings_pending, 2 ),
			),
			'rejection_reason' => $reason,
		);
	}

	/**
	 * Approve teacher (status → active / approved in UI).
	 *
	 * @param int $teacher_id Teacher ID.
	 * @return true|WP_Error
	 */
	public static function approve( $teacher_id ) {
		return self::set_status( $teacher_id, 'active' );
	}

	/**
	 * Reject teacher.
	 *
	 * @param int    $teacher_id Teacher ID.
	 * @param string $reason     Optional reason.
	 * @return true|WP_Error
	 */
	public static function reject( $teacher_id, $reason = '' ) {
		$result = self::set_status( $teacher_id, 'rejected' );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$reason = sanitize_textarea_field( (string) $reason );
		if ( '' !== $reason ) {
			$row = self::get_raw_row( $teacher_id );
			if ( $row && ! empty( $row['user_id'] ) ) {
				update_user_meta( absint( $row['user_id'] ), 'gmm_rejection_reason', $reason );
			}
		}

		return true;
	}

	/**
	 * Suspend teacher (blocks new bookings).
	 *
	 * @param int $teacher_id Teacher ID.
	 * @return true|WP_Error
	 */
	public static function suspend( $teacher_id ) {
		return self::set_status( $teacher_id, 'suspended' );
	}

	/**
	 * Soft-delete teacher plugin data. Does not delete WP user unless filtered.
	 *
	 * @param int $teacher_id Teacher ID.
	 * @return true|WP_Error
	 */
	public static function delete_teacher( $teacher_id ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$teacher_id = absint( $teacher_id );
		$row        = self::get_raw_row( $teacher_id );
		if ( ! $row ) {
			return new WP_Error( 'gmm_missing', __( 'Teacher not found.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$teachers = GMM_Database::table( 'teachers' );
		$classes  = GMM_Database::table( 'classes' );
		$trash    = self::trash_status();

		$updated = $wpdb->update(
			$teachers,
			array(
				'status'     => $trash,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $teacher_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db', __( 'Could not delete teacher.', 'gospel-music-mastery' ) );
		}

		// Soft-delete related classes (plugin data only).
		$wpdb->update(
			$classes,
			array(
				'status'     => $trash,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'teacher_id' => $teacher_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		$user_id = absint( $row['user_id'] );
		if ( $user_id ) {
			update_user_meta( $user_id, 'gmm_teacher_status', $trash );

			/**
			 * Whether to permanently delete the linked WordPress user.
			 * Default false — never remove WP accounts without explicit permission.
			 *
			 * @param bool $delete     Whether to delete.
			 * @param int  $teacher_id Teacher row ID.
			 * @param int  $user_id    WP user ID.
			 */
			$delete_user = (bool) apply_filters( 'gmm_admin_delete_teacher_wp_user', false, $teacher_id, $user_id );
			if ( $delete_user && current_user_can( 'delete_users' ) && get_current_user_id() !== $user_id ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
				wp_delete_user( $user_id );
			}
		}

		/**
		 * Fires after a teacher is soft-deleted by admin.
		 *
		 * @param int                  $teacher_id Teacher ID.
		 * @param array<string, mixed> $row        Original row.
		 */
		do_action( 'gmm_teacher_deleted', $teacher_id, $row );

		self::flush_cache();
		return true;
	}

	/**
	 * Bulk status update.
	 *
	 * @param array<int, int> $ids    Teacher IDs.
	 * @param string          $action approve|reject|suspend.
	 * @param string          $reason Optional reject reason.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function bulk_action( $ids, $action, $reason = '' ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$action = sanitize_key( $action );
		$ids    = array_values( array_unique( array_filter( array_map( 'absint', (array) $ids ) ) ) );
		if ( empty( $ids ) ) {
			return new WP_Error( 'gmm_invalid', __( 'No teachers selected.', 'gospel-music-mastery' ) );
		}

		$ok    = 0;
		$fail  = 0;
		$errors = array();

		foreach ( $ids as $id ) {
			switch ( $action ) {
				case 'approve':
					$result = self::approve( $id );
					break;
				case 'reject':
					$result = self::reject( $id, $reason );
					break;
				case 'suspend':
					$result = self::suspend( $id );
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
	 * Core status writer + hooks.
	 *
	 * @param int    $teacher_id Teacher ID.
	 * @param string $status     DB status.
	 * @return true|WP_Error
	 */
	public static function set_status( $teacher_id, $status ) {
		if ( ! self::user_can_manage() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}

		$teacher_id = absint( $teacher_id );
		$status     = sanitize_key( $status );
		$allowed    = array( 'pending', 'active', 'approved', 'inactive', 'rejected', 'suspended' );

		if ( ! $teacher_id || ! in_array( $status, $allowed, true ) ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid teacher or status.', 'gospel-music-mastery' ) );
		}

		$row = self::get_raw_row( $teacher_id );
		if ( ! $row ) {
			return new WP_Error( 'gmm_missing', __( 'Teacher not found.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'teachers' );
		$updated = $wpdb->update(
			$table,
			array(
				'status'     => $status,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $teacher_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db', __( 'Could not update teacher.', 'gospel-music-mastery' ) );
		}

		$row['status'] = $status;
		$user_id       = absint( $row['user_id'] );
		if ( $user_id ) {
			update_user_meta( $user_id, 'gmm_teacher_status', $status );
		}

		if ( in_array( $status, array( 'active', 'approved' ), true ) ) {
			/**
			 * Fires when a teacher is approved.
			 *
			 * @param int                  $teacher_id Teacher ID.
			 * @param array<string, mixed> $row        Teacher row.
			 */
			do_action( 'gmm_teacher_approved', $teacher_id, $row );
		} elseif ( in_array( $status, array( 'rejected', 'inactive' ), true ) ) {
			/**
			 * Fires when a teacher is rejected.
			 *
			 * @param int                  $teacher_id Teacher ID.
			 * @param array<string, mixed> $row        Teacher row.
			 */
			do_action( 'gmm_teacher_rejected', $teacher_id, $row );
		} elseif ( 'suspended' === $status ) {
			/**
			 * Fires when a teacher is suspended.
			 *
			 * @param int                  $teacher_id Teacher ID.
			 * @param array<string, mixed> $row        Teacher row.
			 */
			do_action( 'gmm_teacher_suspended', $teacher_id, $row );
		}

		self::flush_cache();
		return true;
	}

	/**
	 * Whether teacher may receive new bookings.
	 *
	 * @param int $teacher_id Teacher ID.
	 * @return bool
	 */
	public static function can_receive_bookings( $teacher_id ) {
		$row = self::get_raw_row( absint( $teacher_id ) );
		if ( ! $row ) {
			return false;
		}
		$status = sanitize_key( (string) $row['status'] );
		return in_array( $status, array( 'active', 'approved' ), true );
	}

	/**
	 * Map UI filter status → DB status list.
	 *
	 * @param string $ui_status UI value.
	 * @return array<int, string>
	 */
	public static function ui_status_to_db( $ui_status ) {
		$ui_status = sanitize_key( $ui_status );
		switch ( $ui_status ) {
			case 'pending':
				return array( 'pending' );
			case 'approved':
				return array( 'active', 'approved' );
			case 'rejected':
				return array( 'rejected', 'inactive' );
			case 'suspended':
				return array( 'suspended' );
			case 'all':
			default:
				return array();
		}
	}

	/**
	 * Map DB status → UI status key.
	 *
	 * @param string $db_status DB value.
	 * @return string
	 */
	public static function db_status_to_ui( $db_status ) {
		$db_status = sanitize_key( $db_status );
		$map       = array(
			'pending'   => 'pending',
			'active'    => 'approved',
			'approved'  => 'approved',
			'rejected'  => 'rejected',
			'inactive'  => 'rejected',
			'suspended' => 'suspended',
			'trash'     => 'rejected',
		);
		return isset( $map[ $db_status ] ) ? $map[ $db_status ] : 'pending';
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
	 * Front-end enqueue when shortcode present.
	 *
	 * @return void
	 */
	public function maybe_enqueue_assets() {
		if ( ! self::user_can_manage() ) {
			return;
		}
		if ( ! class_exists( 'GMM_Assets' ) || ! GMM_Assets::is_gmm_page() ) {
			return;
		}
		$post = get_queried_object();
		$content = ( $post instanceof WP_Post ) ? (string) $post->post_content : '';
		if ( ! has_shortcode( $content, 'gmm_admin_teachers' ) && false === strpos( $content, 'gmm_admin_teachers' ) ) {
			return;
		}
		self::enqueue_assets();
	}

	/**
	 * wp-admin teachers screen.
	 *
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
		if ( 'gmm-teachers' !== $page && false === strpos( (string) $hook, 'gmm-teachers' ) ) {
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
			'gmm-admin-teachers',
			GMM_URL . 'assets/js/gmm-admin-teachers.js',
			array( 'gmm-core-script', 'gmm-ajax-script' ),
			$version,
			true
		);

		wp_localize_script(
			'gmm-admin-teachers',
			'GMM_ADMIN_TEACHERS',
			array(
				'nonce'   => wp_create_nonce( 'gmm_nonce' ),
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'i18n'    => array(
					'approved'      => __( 'Teacher approved.', 'gospel-music-mastery' ),
					'rejected'      => __( 'Teacher rejected.', 'gospel-music-mastery' ),
					'suspended'     => __( 'Teacher suspended.', 'gospel-music-mastery' ),
					'deleted'       => __( 'Teacher deleted.', 'gospel-music-mastery' ),
					'confirmDelete' => __( 'Delete this teacher and related plugin data? The WordPress user account will not be removed.', 'gospel-music-mastery' ),
					'rejectPrompt'  => __( 'Optional rejection reason:', 'gospel-music-mastery' ),
					'error'         => __( 'Action failed. Please try again.', 'gospel-music-mastery' ),
					'bulkDone'      => __( 'Bulk action completed.', 'gospel-music-mastery' ),
				),
			)
		);
	}

	/**
	 * Format list/modal row.
	 *
	 * @param array<string, mixed> $row DB row.
	 * @return array<string, mixed>
	 */
	private static function format_teacher_row( $row ) {
		$first = isset( $row['first_name'] ) ? (string) $row['first_name'] : '';
		$last  = isset( $row['last_name'] ) ? (string) $row['last_name'] : '';
		$name  = trim( $first . ' ' . $last );
		if ( '' === $name ) {
			$name = __( 'Teacher', 'gospel-music-mastery' );
		}

		$db_status = isset( $row['status'] ) ? sanitize_key( (string) $row['status'] ) : 'pending';
		$ui_status = self::db_status_to_ui( $db_status );
		$spec_raw  = isset( $row['specialization'] ) ? (string) $row['specialization'] : '';
		$specialty = self::detect_specialty_slug( $spec_raw );

		$image = self::resolve_image(
			isset( $row['profile_image'] ) ? (string) $row['profile_image'] : '',
			'assets/img/team/01.jpg'
		);

		$joined = '';
		if ( ! empty( $row['created_at'] ) && '0000-00-00 00:00:00' !== $row['created_at'] ) {
			$ts = strtotime( (string) $row['created_at'] );
			if ( $ts ) {
				$joined = wp_date( 'M j, Y', $ts );
			}
		}

		$rating = isset( $row['rating'] ) ? number_format( (float) $row['rating'], 1 ) : '0.0';
		$students = isset( $row['student_count'] ) ? absint( $row['student_count'] ) : 0;
		$classes  = isset( $row['class_count'] ) ? absint( $row['class_count'] ) : 0;

		return array(
			'id'             => absint( $row['id'] ),
			'user_id'        => absint( isset( $row['user_id'] ) ? $row['user_id'] : 0 ),
			'name'           => $name,
			'first_name'     => $first,
			'last_name'      => $last,
			'email'          => isset( $row['email'] ) ? (string) $row['email'] : '',
			'phone'          => isset( $row['phone'] ) ? (string) $row['phone'] : '',
			'bio'            => isset( $row['bio'] ) ? (string) $row['bio'] : '',
			'specialization' => $spec_raw ? $spec_raw : __( 'General', 'gospel-music-mastery' ),
			'specialty'      => $specialty,
			'experience'     => isset( $row['experience'] ) && $row['experience'] ? (string) $row['experience'] : '—',
			'rating'         => $rating,
			'students'       => $students,
			'classes'        => $classes,
			'status'         => $ui_status,
			'db_status'      => $db_status,
			'status_label'   => self::status_label( $ui_status ),
			'status_class'   => self::status_badge_class( $ui_status ),
			'image'          => $image,
			'joined'         => $joined ? $joined : '—',
		);
	}

	/**
	 * @param int $teacher_id ID.
	 * @return array<string, mixed>|null
	 */
	private static function get_raw_row( $teacher_id ) {
		global $wpdb;
		$table = GMM_Database::table( 'teachers' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", absint( $teacher_id ) ),
			ARRAY_A
		);
		return is_array( $row ) ? $row : null;
	}

	/**
	 * @param string $raw Attachment/path/URL.
	 * @param string $fallback Design path.
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
		if ( $raw && false === strpos( $raw, '://' ) ) {
			if ( function_exists( 'gmm_design_asset_url' ) ) {
				return gmm_design_asset_url( ltrim( $raw, '/' ) );
			}
		}
		return function_exists( 'gmm_design_asset_url' ) ? gmm_design_asset_url( $fallback ) : '';
	}

	/**
	 * @param string $spec Specialization text.
	 * @return string
	 */
	private static function detect_specialty_slug( $spec ) {
		$lower = strtolower( (string) $spec );
		$keys  = array( 'piano', 'vocals', 'vocal', 'drums', 'drum', 'guitar', 'theory' );
		foreach ( $keys as $key ) {
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
	 * @param string $slug Specialty slug.
	 * @return string
	 */
	private static function specialty_label( $slug ) {
		$labels = array(
			'piano'  => 'Piano',
			'vocals' => 'Vocal',
			'drums'  => 'Drum',
			'guitar' => 'Guitar',
			'theory' => 'Theory',
		);
		return isset( $labels[ $slug ] ) ? $labels[ $slug ] : '';
	}

	/**
	 * @param string $ui_status UI status.
	 * @return string
	 */
	private static function status_label( $ui_status ) {
		$labels = array(
			'pending'   => __( 'Pending', 'gospel-music-mastery' ),
			'approved'  => __( 'Approved', 'gospel-music-mastery' ),
			'rejected'  => __( 'Rejected', 'gospel-music-mastery' ),
			'suspended' => __( 'Suspended', 'gospel-music-mastery' ),
		);
		return isset( $labels[ $ui_status ] ) ? $labels[ $ui_status ] : $ui_status;
	}

	/**
	 * @param string $ui_status UI status.
	 * @return string
	 */
	private static function status_badge_class( $ui_status ) {
		$map = array(
			'pending'   => 'is-pending',
			'approved'  => 'is-confirmed',
			'rejected'  => 'is-cancelled',
			'suspended' => 'is-suspended',
		);
		return isset( $map[ $ui_status ] ) ? $map[ $ui_status ] : 'is-pending';
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
			'pending'   => 0,
			'approved'  => 0,
			'suspended' => 0,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function empty_filters() {
		return array(
			'search'    => '',
			'status'    => 'all',
			'specialty' => 'all',
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
		return array_merge(
			array( 'items' => array() ),
			self::empty_pagination()
		);
	}
}
