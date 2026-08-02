<?php
/**
 * Teacher Class Management controller.
 *
 * CRUD for own gmm_classes rows, AJAX, image upload, featured request,
 * and template data for [gmm_teacher_classes] without changing frozen UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Teacher_Classes
 */
class GMM_Teacher_Classes {

	const NONCE_ACTION = 'gmm_teacher_class_action';
	const NONCE_FIELD  = 'gmm_teacher_class_nonce';
	const FEATURED_OPTION = 'gmm_featured_class_requests';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 25, 2 );
		$loader->add_filter( 'gmm_shortcode_html', $instance, 'enhance_classes_html', 20, 2 );

		$loader->add_action( 'wp_ajax_gmm_teacher_class_create', $instance, 'ajax_create' );
		$loader->add_action( 'wp_ajax_gmm_teacher_class_update', $instance, 'ajax_update' );
		$loader->add_action( 'wp_ajax_gmm_teacher_class_delete', $instance, 'ajax_delete' );
		$loader->add_action( 'wp_ajax_gmm_teacher_class_get', $instance, 'ajax_get' );
		$loader->add_action( 'wp_ajax_gmm_teacher_class_list', $instance, 'ajax_list' );
		$loader->add_action( 'wp_ajax_gmm_teacher_class_image', $instance, 'ajax_image' );
		$loader->add_action( 'wp_ajax_gmm_teacher_class_duplicate', $instance, 'ajax_duplicate' );

		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 40 );

		$loader->add_action( 'gmm_class_approved', $instance, 'flush_on_admin_class_change', 10, 2 );
		$loader->add_action( 'gmm_class_rejected', $instance, 'flush_on_admin_class_change', 10, 2 );
		$loader->add_action( 'gmm_class_deleted', $instance, 'flush_on_admin_class_change', 10, 2 );
	}

	/**
	 * Inject vars into [gmm_teacher_classes].
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Shortcode.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_teacher_classes' !== $tag ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * Wire create/edit anchors and inject form modal (no redesign of cards).
	 *
	 * @param string $html Shortcode HTML.
	 * @param string $tag  Tag.
	 * @return string
	 */
	public function enhance_classes_html( $html, $tag ) {
		if ( 'gmm_teacher_classes' !== $tag || '' === $html ) {
			return $html;
		}
		if ( empty( $GLOBALS['gmm_teacher_classes_can_manage'] ) && ! self::user_can_manage() ) {
			return $html;
		}

		$html = preg_replace(
			'#href=("|\')teacher-onboarding-class\.html\1#i',
			'href="#class-form-modal" data-bs-toggle="modal" data-gmm-class-action="create"',
			$html
		);

		if ( false === strpos( $html, 'id="class-form-modal"' ) ) {
			$html .= self::render_class_form_modal();
		}

		return $html;
	}

	/**
	 * Whether current user may manage classes.
	 *
	 * @param int $user_id Target user.
	 * @return bool
	 */
	public static function user_can_manage( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id || ! is_user_logged_in() ) {
			return false;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		if ( get_current_user_id() !== $user_id ) {
			return false;
		}
		if ( ! function_exists( 'gmm_is_teacher' ) || ! gmm_is_teacher( $user_id ) ) {
			return false;
		}
		if ( class_exists( 'GMM_Teacher_Auth' ) && ! GMM_Teacher_Auth::is_approved( $user_id ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Template variables.
	 *
	 * @param int $user_id Optional WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_template_vars( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		$GLOBALS['gmm_teacher_classes_can_manage'] = self::user_can_manage( $user_id );

		if ( ! self::user_can_manage( $user_id ) ) {
			$pending = function_exists( 'gmm_is_teacher' ) && gmm_is_teacher( $user_id )
				&& class_exists( 'GMM_Teacher_Auth' )
				&& ! GMM_Teacher_Auth::is_approved( $user_id );

			return array(
				'gmm_teacher_denied'  => true,
				'gmm_teacher_pending' => $pending,
				'classes'             => array(),
				'class_cards'         => array(),
				'class_stats'         => self::empty_stats(),
				'class_filters'       => self::default_filters(),
				'logout_url'          => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
			);
		}

		$filters = self::parse_filters_from_request();
		$cards   = self::get_teacher_class_cards( $user_id, $filters );
		$stats   = self::get_class_stats( $user_id );
		$profile = class_exists( 'GMM_Teacher_Dashboard' )
			? GMM_Teacher_Dashboard::get_profile_summary( $user_id )
			: array();
		$dash    = class_exists( 'GMM_Teacher_Dashboard' )
			? GMM_Teacher_Dashboard::get_statistics( $user_id )
			: array();

		return array(
			'gmm_teacher_denied'  => false,
			'gmm_teacher_pending' => false,
			'classes'             => self::get_teacher_classes( $user_id, $filters ),
			'class_cards'         => $cards,
			'class_stats'         => $stats,
			'class_filters'       => $filters,
			'class_categories'    => self::category_options(),
			'class_difficulties'  => self::difficulty_options(),
			'class_durations'     => self::duration_options(),
			'profile_summary'     => $profile,
			'logout_url'          => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
			'user_name'           => isset( $profile['name'] ) ? $profile['name'] : '',
			'user_first_name'     => isset( $profile['first_name'] ) ? $profile['first_name'] : '',
			'profile_stats'       => array(
				'rating'   => isset( $profile['rating'] ) ? (float) $profile['rating'] : 0,
				'students' => isset( $dash['total_students'] ) ? (int) $dash['total_students'] : 0,
				'classes'  => isset( $stats['total'] ) ? (int) $stats['total'] : 0,
			),
		);
	}

	/**
	 * Create a class for the current teacher (status: pending).
	 *
	 * @param array<string, mixed> $data    Class fields.
	 * @param int                  $user_id WP user ID.
	 * @return int|WP_Error New class ID or error.
	 */
	public static function create_class( $data, $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		$auth = self::authorize_manage( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( class_exists( 'GMM_Teacher_Auth' ) && ! GMM_Teacher_Auth::is_approved( $user_id ) ) {
			return new WP_Error( 'gmm_pending', __( 'Your account is waiting for approval.', 'gospel-music-mastery' ) );
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return new WP_Error( 'gmm_no_profile', __( 'Teacher profile not found.', 'gospel-music-mastery' ) );
		}

		$validated = self::validate_class_fields( $data, true );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$featured_request = ! empty( $data['featured_request'] ) || ! empty( $data['request_featured'] );

		$now = current_time( 'mysql' );
		$row = array_merge(
			array(
				'teacher_id'  => $teacher_id,
				'title'       => '',
				'description' => '',
				'category'    => '',
				'difficulty'  => '',
				'duration'    => 0,
				'price'       => 0,
				'image'       => '',
				'status'      => 'pending',
				'featured'    => 0,
				'created_at'  => $now,
				'updated_at'  => $now,
			),
			$validated
		);
		$row['teacher_id'] = $teacher_id;
		$row['status']     = 'pending';
		$row['featured']   = 0;

		global $wpdb;
		$table    = GMM_Database::table( 'classes' );
		$inserted = $wpdb->insert( $table, $row );

		if ( ! $inserted ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not create class.', 'gospel-music-mastery' ) );
		}

		$class_id = (int) $wpdb->insert_id;

		if ( $featured_request ) {
			self::request_featured( $class_id, $user_id );
		}

		/**
		 * Fires after a teacher creates a class (pending admin review).
		 *
		 * @param int                  $class_id Class ID.
		 * @param array<string, mixed> $row      Inserted row.
		 * @param int                  $user_id  WP user ID.
		 */
		do_action( 'gmm_teacher_class_created', $class_id, $row, $user_id );

		self::flush_related_caches( $user_id );
		if ( class_exists( 'GMM_Admin_Classes' ) ) {
			GMM_Admin_Classes::flush_cache();
		}

		return $class_id;
	}

	/**
	 * Update own class.
	 *
	 * @param int                  $class_id Class ID.
	 * @param array<string, mixed> $data     Fields.
	 * @param int                  $user_id  WP user ID.
	 * @return true|WP_Error
	 */
	public static function update_class( $class_id, $data, $user_id = 0 ) {
		$user_id  = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$class_id = absint( $class_id );

		$auth = self::authorize_manage( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( ! self::owns_class( $class_id, $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only update your own classes.', 'gospel-music-mastery' ) );
		}

		$existing = self::get_raw_row( $class_id );
		if ( ! $existing || self::is_trashed( $existing ) ) {
			return new WP_Error( 'gmm_missing', __( 'Class not found.', 'gospel-music-mastery' ) );
		}

		$validated = self::validate_class_fields( $data, false );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}
		if ( empty( $validated ) && empty( $data['featured_request'] ) && empty( $data['request_featured'] ) ) {
			return new WP_Error( 'gmm_no_fields', __( 'No valid class fields to update.', 'gospel-music-mastery' ) );
		}

		// Teachers cannot self-approve; resubmit rejected classes for review.
		$prev_status = isset( $existing['status'] ) ? sanitize_key( (string) $existing['status'] ) : '';
		if ( 'rejected' === $prev_status ) {
			$validated['status'] = 'pending';
		} elseif ( isset( $validated['status'] ) ) {
			unset( $validated['status'] );
		}
		unset( $validated['featured'] );

		$validated['updated_at'] = current_time( 'mysql' );

		global $wpdb;
		$table   = GMM_Database::table( 'classes' );
		$updated = $wpdb->update(
			$table,
			$validated,
			array( 'id' => $class_id ),
			null,
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not update class.', 'gospel-music-mastery' ) );
		}

		if ( ! empty( $data['featured_request'] ) || ! empty( $data['request_featured'] ) ) {
			self::request_featured( $class_id, $user_id );
		}

		/**
		 * Fires after a teacher updates a class.
		 *
		 * @param int                  $class_id Class ID.
		 * @param array<string, mixed> $data     Updated fields.
		 * @param int                  $user_id  WP user ID.
		 */
		do_action( 'gmm_teacher_class_updated', $class_id, $validated, $user_id );

		self::flush_related_caches( $user_id );
		if ( class_exists( 'GMM_Admin_Classes' ) ) {
			GMM_Admin_Classes::flush_cache();
		}

		return true;
	}

	/**
	 * Soft-delete own class (confirmation + ownership enforced by callers).
	 *
	 * @param int $class_id Class ID.
	 * @param int $user_id  WP user ID.
	 * @return true|WP_Error
	 */
	public static function delete_class( $class_id, $user_id = 0 ) {
		$user_id  = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$class_id = absint( $class_id );

		$auth = self::authorize_manage( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( ! self::owns_class( $class_id, $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only delete your own classes.', 'gospel-music-mastery' ) );
		}

		$existing = self::get_raw_row( $class_id );
		if ( ! $existing || self::is_trashed( $existing ) ) {
			return new WP_Error( 'gmm_missing', __( 'Class not found.', 'gospel-music-mastery' ) );
		}

		$block = self::active_booking_block( $class_id );
		if ( is_wp_error( $block ) ) {
			return $block;
		}

		$trash = self::trash_status();

		global $wpdb;
		$table   = GMM_Database::table( 'classes' );
		$updated = $wpdb->update(
			$table,
			array(
				'status'     => $trash,
				'featured'   => 0,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $class_id ),
			array( '%s', '%d', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not delete class.', 'gospel-music-mastery' ) );
		}

		self::clear_featured_request( $class_id );

		/**
		 * Fires after a teacher soft-deletes a class.
		 *
		 * @param int                  $class_id Class ID.
		 * @param array<string, mixed> $existing Prior row.
		 * @param int                  $user_id  WP user ID.
		 */
		do_action( 'gmm_teacher_class_deleted', $class_id, $existing, $user_id );
		do_action( 'gmm_class_deleted', $class_id, $existing );

		self::flush_related_caches( $user_id );
		if ( class_exists( 'GMM_Admin_Classes' ) ) {
			GMM_Admin_Classes::flush_cache();
		}

		return true;
	}

	/**
	 * List classes for a teacher (excludes trash).
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $filters Optional search/status/category.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_teacher_classes( $user_id = 0, $filters = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id || ! GMM_Teacher::can_view_profile( $user_id ) ) {
			return array();
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}

		$filters = wp_parse_args(
			is_array( $filters ) ? $filters : array(),
			array(
				'search'   => '',
				'status'   => 'all',
				'category' => '',
			)
		);

		global $wpdb;
		$table    = GMM_Database::table( 'classes' );
		$bookings = GMM_Database::table( 'bookings' );
		$trash    = self::trash_status();

		$sql    = "SELECT c.*,
			(SELECT COUNT(DISTINCT b.student_id) FROM {$bookings} b
				WHERE b.class_id = c.id AND b.student_id > 0
				AND b.booking_status NOT IN ('cancelled','rejected')) AS student_count
			FROM {$table} c
			WHERE c.teacher_id = %d AND c.status <> %s";
		$params = array( $teacher_id, $trash );

		$status = sanitize_key( (string) $filters['status'] );
		if ( $status && 'all' !== $status ) {
			$map = self::filter_status_to_db( $status );
			if ( count( $map ) === 1 ) {
				$sql     .= ' AND c.status = %s';
				$params[] = $map[0];
			} elseif ( count( $map ) > 1 ) {
				$placeholders = implode( ',', array_fill( 0, count( $map ), '%s' ) );
				$sql         .= " AND c.status IN ({$placeholders})";
				$params       = array_merge( $params, $map );
			}
		}

		$category = sanitize_text_field( (string) $filters['category'] );
		if ( '' !== $category ) {
			$sql     .= ' AND c.category = %s';
			$params[] = $category;
		}

		$search = sanitize_text_field( (string) $filters['search'] );
		if ( '' !== $search ) {
			$like     = '%' . $wpdb->esc_like( $search ) . '%';
			$sql     .= ' AND (c.title LIKE %s OR c.description LIKE %s OR c.category LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		$sql .= ' ORDER BY c.created_at DESC';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Formatted cards for the frozen template.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $filters Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_teacher_class_cards( $user_id = 0, $filters = array() ) {
		$rows  = self::get_teacher_classes( $user_id, $filters );
		$cards = array();
		foreach ( $rows as $row ) {
			$cards[] = self::format_class_card( $row );
		}
		return $cards;
	}

	/**
	 * Class counts for stats strip.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, int>
	 */
	public static function get_class_stats( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return self::empty_stats();
		}

		global $wpdb;
		$table = GMM_Database::table( 'classes' );
		$trash = self::trash_status();

		$total = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE teacher_id = %d AND status <> %s",
				$teacher_id,
				$trash
			)
		);

		$published = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE teacher_id = %d AND status IN ('approved','published')",
				$teacher_id
			)
		);

		$pending = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE teacher_id = %d AND status = %s",
				$teacher_id,
				'pending'
			)
		);

		$drafts = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE teacher_id = %d AND status IN ('draft','rejected')",
				$teacher_id
			)
		);

		return array(
			'total'     => $total,
			'published' => $published,
			'pending'   => $pending,
			'drafts'    => $drafts,
		);
	}

	/**
	 * Queue a featured-class request for admin review.
	 *
	 * @param int $class_id Class ID.
	 * @param int $user_id  WP user ID.
	 * @return void
	 */
	public static function request_featured( $class_id, $user_id = 0 ) {
		$class_id = absint( $class_id );
		$user_id  = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! $class_id ) {
			return;
		}

		$requests = get_option( self::FEATURED_OPTION, array() );
		if ( ! is_array( $requests ) ) {
			$requests = array();
		}

		$requests[ $class_id ] = array(
			'class_id'     => $class_id,
			'user_id'      => $user_id,
			'teacher_id'   => (int) GMM_Teacher::get_teacher_id( $user_id ),
			'requested_at' => current_time( 'mysql' ),
			'status'       => 'pending',
		);

		update_option( self::FEATURED_OPTION, $requests, false );

		/**
		 * Fires when a teacher requests featured placement.
		 *
		 * @param int $class_id Class ID.
		 * @param int $user_id  WP user ID.
		 */
		do_action( 'gmm_teacher_class_featured_requested', $class_id, $user_id );
	}

	/**
	 * AJAX: create.
	 *
	 * @return void
	 */
	public function ajax_create() {
		$this->verify_ajax_nonce();
		$user_id = get_current_user_id();
		$data    = $this->collect_request_fields();

		$image_error = $this->maybe_attach_uploaded_image( $data, 0, $user_id );
		if ( is_wp_error( $image_error ) ) {
			wp_send_json_error( array( 'message' => $image_error->get_error_message() ), 400 );
		}

		$result = self::create_class( $data, $user_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		$class_id = (int) $result;

		// Image uploaded after create (media context requires class ID).
		if ( ! empty( $_FILES['class_image']['tmp_name'] ) && empty( $data['image'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$up = $this->upload_class_image_file( $class_id, $user_id );
			if ( is_wp_error( $up ) ) {
				wp_send_json_error(
					array(
						'message'  => $up->get_error_message(),
						'class_id' => $class_id,
					),
					400
				);
			}
		}

		$card = self::format_class_card( self::get_raw_row( $class_id ) );
		wp_send_json_success(
			array(
				'message' => __( 'Class created and submitted for review.', 'gospel-music-mastery' ),
				'class_id'=> $class_id,
				'card'    => $card,
				'stats'   => self::get_class_stats( $user_id ),
			)
		);
	}

	/**
	 * AJAX: update.
	 *
	 * @return void
	 */
	public function ajax_update() {
		$this->verify_ajax_nonce();
		$user_id  = get_current_user_id();
		$class_id = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$data     = $this->collect_request_fields();

		if ( ! empty( $_FILES['class_image']['tmp_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$up = $this->upload_class_image_file( $class_id, $user_id );
			if ( is_wp_error( $up ) ) {
				wp_send_json_error( array( 'message' => $up->get_error_message() ), 400 );
			}
			$data['image'] = (string) $up;
		}

		$result = self::update_class( $class_id, $data, $user_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		$card = self::format_class_card( self::get_raw_row( $class_id ) );
		wp_send_json_success(
			array(
				'message' => __( 'Class updated successfully.', 'gospel-music-mastery' ),
				'class_id'=> $class_id,
				'card'    => $card,
				'stats'   => self::get_class_stats( $user_id ),
			)
		);
	}

	/**
	 * AJAX: delete (requires confirm=1).
	 *
	 * @return void
	 */
	public function ajax_delete() {
		$this->verify_ajax_nonce();
		$user_id  = get_current_user_id();
		$class_id = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$confirm  = ! empty( $_POST['confirm'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( ! $confirm ) {
			wp_send_json_error( array( 'message' => __( 'Please confirm deletion.', 'gospel-music-mastery' ) ), 400 );
		}

		$result = self::delete_class( $class_id, $user_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		wp_send_json_success(
			array(
				'message'  => __( 'Class deleted.', 'gospel-music-mastery' ),
				'class_id' => $class_id,
				'stats'    => self::get_class_stats( $user_id ),
			)
		);
	}

	/**
	 * AJAX: get one class for edit/view.
	 *
	 * @return void
	 */
	public function ajax_get() {
		$this->verify_ajax_nonce();
		$user_id  = get_current_user_id();
		$class_id = isset( $_REQUEST['class_id'] ) ? absint( $_REQUEST['class_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( ! self::owns_class( $class_id, $user_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You can only view your own classes.', 'gospel-music-mastery' ) ), 403 );
		}

		$row = self::get_raw_row( $class_id );
		if ( ! $row || self::is_trashed( $row ) ) {
			wp_send_json_error( array( 'message' => __( 'Class not found.', 'gospel-music-mastery' ) ), 404 );
		}

		wp_send_json_success(
			array(
				'card' => self::format_class_card( $row ),
				'raw'  => array(
					'id'               => (int) $row['id'],
					'title'            => (string) $row['title'],
					'description'      => (string) $row['description'],
					'category'         => (string) $row['category'],
					'difficulty'       => (string) $row['difficulty'],
					'duration'         => (int) $row['duration'],
					'price'            => (float) $row['price'],
					'image'            => (string) $row['image'],
					'status'           => (string) $row['status'],
					'featured_request' => self::has_featured_request( $class_id ),
				),
			)
		);
	}

	/**
	 * AJAX: refresh list / stats (status refresh).
	 *
	 * @return void
	 */
	public function ajax_list() {
		$this->verify_ajax_nonce();
		$user_id = get_current_user_id();
		$filters = self::parse_filters_from_request();

		wp_send_json_success(
			array(
				'cards'   => self::get_teacher_class_cards( $user_id, $filters ),
				'stats'   => self::get_class_stats( $user_id ),
				'filters' => $filters,
			)
		);
	}

	/**
	 * AJAX: upload/replace class image.
	 *
	 * @return void
	 */
	public function ajax_image() {
		$this->verify_ajax_nonce();
		$user_id  = get_current_user_id();
		$class_id = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( ! self::owns_class( $class_id, $user_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You can only update your own classes.', 'gospel-music-mastery' ) ), 403 );
		}

		$attachment_id = $this->upload_class_image_file( $class_id, $user_id );
		if ( is_wp_error( $attachment_id ) ) {
			wp_send_json_error( array( 'message' => $attachment_id->get_error_message() ), 400 );
		}

		$url = self::resolve_image_url( (string) $attachment_id );
		wp_send_json_success(
			array(
				'message'       => __( 'Class image updated.', 'gospel-music-mastery' ),
				'attachment_id' => (int) $attachment_id,
				'url'           => $url,
				'card'          => self::format_class_card( self::get_raw_row( $class_id ) ),
			)
		);
	}

	/**
	 * AJAX: duplicate own class (new pending copy).
	 *
	 * @return void
	 */
	public function ajax_duplicate() {
		$this->verify_ajax_nonce();
		$user_id  = get_current_user_id();
		$class_id = isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( ! self::owns_class( $class_id, $user_id ) ) {
			wp_send_json_error( array( 'message' => __( 'You can only duplicate your own classes.', 'gospel-music-mastery' ) ), 403 );
		}

		$row = self::get_raw_row( $class_id );
		if ( ! $row || self::is_trashed( $row ) ) {
			wp_send_json_error( array( 'message' => __( 'Class not found.', 'gospel-music-mastery' ) ), 404 );
		}

		$data = array(
			'title'       => (string) $row['title'] . ' (Copy)',
			'description' => (string) $row['description'],
			'category'    => (string) $row['category'],
			'difficulty'  => (string) $row['difficulty'],
			'duration'    => (int) $row['duration'],
			'price'       => (float) $row['price'],
			'image'       => (string) $row['image'],
		);

		$result = self::create_class( $data, $user_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		wp_send_json_success(
			array(
				'message'  => __( 'Class duplicated and submitted for review.', 'gospel-music-mastery' ),
				'class_id' => (int) $result,
				'card'     => self::format_class_card( self::get_raw_row( (int) $result ) ),
				'stats'    => self::get_class_stats( $user_id ),
			)
		);
	}

	/**
	 * Enqueue teacher classes script.
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

		$post    = get_queried_object();
		$content = ( $post instanceof WP_Post ) ? (string) $post->post_content : '';
		if ( ! has_shortcode( $content, 'gmm_teacher_classes' ) && false === strpos( $content, 'gmm_teacher_classes' ) ) {
			return;
		}

		$version = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';
		wp_enqueue_script(
			'gmm-teacher-classes',
			GMM_URL . 'assets/js/gmm-teacher-classes.js',
			array( 'gmm-core-script' ),
			$version,
			true
		);

		wp_localize_script(
			'gmm-teacher-classes',
			'GMM_TEACHER_CLASSES',
			array(
				'nonce'      => wp_create_nonce( self::NONCE_ACTION ),
				'nonceField' => self::NONCE_FIELD,
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'actions'    => array(
					'create'    => 'gmm_teacher_class_create',
					'update'    => 'gmm_teacher_class_update',
					'delete'    => 'gmm_teacher_class_delete',
					'get'       => 'gmm_teacher_class_get',
					'list'      => 'gmm_teacher_class_list',
					'image'     => 'gmm_teacher_class_image',
					'duplicate' => 'gmm_teacher_class_duplicate',
				),
				'i18n'       => array(
					'created'  => __( 'Class created and submitted for review.', 'gospel-music-mastery' ),
					'updated'  => __( 'Class updated successfully.', 'gospel-music-mastery' ),
					'deleted'  => __( 'Class deleted.', 'gospel-music-mastery' ),
					'error'    => __( 'Something went wrong. Please try again.', 'gospel-music-mastery' ),
					'confirm'  => __( 'Are you sure you want to delete this class?', 'gospel-music-mastery' ),
					'empty'    => __( 'No classes created yet.', 'gospel-music-mastery' ),
				),
			)
		);
	}

	/**
	 * Flush teacher caches when admin changes class status.
	 *
	 * @param int                  $class_id Class ID.
	 * @param array<string, mixed> $row      Row.
	 * @return void
	 */
	public function flush_on_admin_class_change( $class_id, $row = array() ) {
		unset( $class_id );
		$user_id = 0;
		if ( is_array( $row ) && ! empty( $row['teacher_id'] ) ) {
			global $wpdb;
			$table   = GMM_Database::table( 'teachers' );
			$user_id = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT user_id FROM {$table} WHERE id = %d LIMIT 1",
					absint( $row['teacher_id'] )
				)
			);
		}
		self::flush_related_caches( $user_id );
	}

	/**
	 * @param int $user_id WP user ID.
	 * @return true|WP_Error
	 */
	private static function authorize_manage( $user_id ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'gmm_not_logged_in', __( 'You must be logged in.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_gmm_classes' ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_cap', __( 'Missing class management capability.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== absint( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only manage your own classes.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_options' ) && ! gmm_is_teacher( $user_id ) ) {
			return new WP_Error( 'gmm_not_teacher', __( 'Teacher role required.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * @param int $class_id Class ID.
	 * @param int $user_id  WP user ID.
	 * @return bool
	 */
	public static function owns_class( $class_id, $user_id ) {
		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );

		if ( ! $teacher_id || ! $class_id ) {
			return false;
		}

		global $wpdb;
		$table = GMM_Database::table( 'classes' );
		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE id = %d AND teacher_id = %d LIMIT 1",
				$class_id,
				$teacher_id
			)
		);

		return ! empty( $found );
	}

	/**
	 * Validate + sanitize class fields.
	 *
	 * @param array<string, mixed> $data   Raw.
	 * @param bool                 $create Full required set when creating.
	 * @return array<string, mixed>|WP_Error
	 */
	private static function validate_class_fields( $data, $create = false ) {
		$data  = is_array( $data ) ? $data : array();
		$clean = array();

		$title = array_key_exists( 'title', $data ) || array_key_exists( 'class_title', $data )
			? sanitize_text_field( (string) ( isset( $data['title'] ) ? $data['title'] : $data['class_title'] ) )
			: null;
		if ( $create || null !== $title ) {
			if ( null === $title || '' === $title ) {
				return new WP_Error( 'gmm_title_required', __( 'Class title is required.', 'gospel-music-mastery' ) );
			}
			$clean['title'] = $title;
		}

		$desc = null;
		if ( array_key_exists( 'description', $data ) ) {
			$desc = sanitize_textarea_field( (string) $data['description'] );
		}
		if ( $create || null !== $desc ) {
			if ( null === $desc || '' === trim( (string) $desc ) ) {
				return new WP_Error( 'gmm_description_required', __( 'Class description is required.', 'gospel-music-mastery' ) );
			}
			$clean['description'] = $desc;
		}

		$category = null;
		if ( array_key_exists( 'category', $data ) ) {
			$category = sanitize_text_field( (string) $data['category'] );
		}
		if ( $create || null !== $category ) {
			if ( null === $category || '' === $category ) {
				return new WP_Error( 'gmm_category_required', __( 'Category is required.', 'gospel-music-mastery' ) );
			}
			$allowed = self::category_options();
			if ( ! in_array( $category, $allowed, true ) ) {
				return new WP_Error( 'gmm_category_invalid', __( 'Please select a valid category.', 'gospel-music-mastery' ) );
			}
			$clean['category'] = $category;
		}

		if ( array_key_exists( 'difficulty', $data ) ) {
			$difficulty = sanitize_text_field( (string) $data['difficulty'] );
			$allowed_d  = self::difficulty_options();
			if ( '' !== $difficulty && ! in_array( $difficulty, $allowed_d, true ) ) {
				return new WP_Error( 'gmm_difficulty_invalid', __( 'Please select a valid difficulty.', 'gospel-music-mastery' ) );
			}
			$clean['difficulty'] = $difficulty;
		} elseif ( $create ) {
			$clean['difficulty'] = '';
		}

		if ( array_key_exists( 'duration', $data ) || array_key_exists( 'class_duration', $data ) ) {
			$raw_duration = isset( $data['duration'] ) ? $data['duration'] : $data['class_duration'];
			$duration     = self::parse_duration( $raw_duration );
			if ( $duration <= 0 ) {
				return new WP_Error( 'gmm_duration_invalid', __( 'Please select a valid duration.', 'gospel-music-mastery' ) );
			}
			$clean['duration'] = $duration;
		} elseif ( $create ) {
			return new WP_Error( 'gmm_duration_invalid', __( 'Please select a valid duration.', 'gospel-music-mastery' ) );
		}

		if ( array_key_exists( 'price', $data ) || array_key_exists( 'class_price', $data ) ) {
			$raw_price = isset( $data['price'] ) ? $data['price'] : $data['class_price'];
			$price     = round( max( 0, floatval( $raw_price ) ), 2 );
			if ( $price <= 0 ) {
				return new WP_Error( 'gmm_price_invalid', __( 'Please enter a valid price.', 'gospel-music-mastery' ) );
			}
			$clean['price'] = $price;
		} elseif ( $create ) {
			return new WP_Error( 'gmm_price_invalid', __( 'Please enter a valid price.', 'gospel-music-mastery' ) );
		}

		if ( array_key_exists( 'image', $data ) || array_key_exists( 'image_id', $data ) ) {
			$raw_image = isset( $data['image'] ) ? $data['image'] : $data['image_id'];
			$image_id  = absint( $raw_image );
			if ( $image_id ) {
				$post = get_post( $image_id );
				if ( ! $post || 'attachment' !== $post->post_type ) {
					return new WP_Error( 'gmm_image_invalid', __( 'Invalid class image.', 'gospel-music-mastery' ) );
				}
				if ( ! wp_attachment_is_image( $image_id ) ) {
					return new WP_Error( 'gmm_image_invalid', __( 'Class image must be a valid image file.', 'gospel-music-mastery' ) );
				}
				$clean['image'] = (string) $image_id;
			} elseif ( '' === (string) $raw_image ) {
				$clean['image'] = '';
			} else {
				return new WP_Error( 'gmm_image_invalid', __( 'Invalid class image.', 'gospel-music-mastery' ) );
			}
		}

		return $clean;
	}

	/**
	 * @param mixed $raw Duration string or int.
	 * @return int Minutes.
	 */
	private static function parse_duration( $raw ) {
		if ( is_numeric( $raw ) ) {
			return absint( $raw );
		}
		if ( preg_match( '/(\d+)/', (string) $raw, $m ) ) {
			return absint( $m[1] );
		}
		return 0;
	}

	/**
	 * @param array<string, mixed>|null $row Row.
	 * @return array<string, mixed>
	 */
	private static function format_class_card( $row ) {
		if ( ! is_array( $row ) || empty( $row['id'] ) ) {
			return array();
		}

		$db_status  = isset( $row['status'] ) ? sanitize_key( (string) $row['status'] ) : 'pending';
		$ui_status  = self::db_status_to_ui( $db_status );
		$duration   = isset( $row['duration'] ) ? absint( $row['duration'] ) : 0;
		$price      = isset( $row['price'] ) ? (float) $row['price'] : 0.0;
		$rating     = isset( $row['rating'] ) ? (float) $row['rating'] : 0.0;
		$students   = isset( $row['student_count'] ) ? absint( $row['student_count'] ) : self::count_students( (int) $row['id'] );
		$title      = isset( $row['title'] ) ? (string) $row['title'] : '';
		$category   = isset( $row['category'] ) ? (string) $row['category'] : '';
		$difficulty = isset( $row['difficulty'] ) ? (string) $row['difficulty'] : '';
		$image_url  = self::resolve_image_url( isset( $row['image'] ) ? (string) $row['image'] : '' );
		$badge      = self::status_badge_class( $ui_status );
		$label      = self::status_label( $ui_status );

		return array(
			'id'              => (int) $row['id'],
			'title'           => $title,
			'description'     => isset( $row['description'] ) ? (string) $row['description'] : '',
			'category'        => $category,
			'difficulty'      => $difficulty,
			'duration'        => $duration,
			'duration_label'  => $duration ? sprintf( '%d Minutes', $duration ) : '—',
			'price'           => $price,
			'price_label'     => '$' . number_format_i18n( $price, $price == floor( $price ) ? 0 : 2 ),
			'students'        => $students,
			'students_label'  => sprintf( '%d Students', $students ),
			'rating'          => $rating,
			'rating_display'  => $rating > 0 ? self::stars_html( $rating ) : '—',
			'status'          => $db_status,
			'ui_status'       => $ui_status,
			'status_label'    => $label,
			'badge_class'     => $badge,
			'image_url'       => $image_url,
			'image_id'        => absint( isset( $row['image'] ) ? $row['image'] : 0 ),
			'featured'        => ! empty( $row['featured'] ),
			'featured_request'=> self::has_featured_request( (int) $row['id'] ),
			'view_text'       => self::build_view_text( $row, $students, $rating ),
		);
	}

	/**
	 * @param array<string, mixed> $row      Row.
	 * @param int                  $students Students.
	 * @param float                $rating   Rating.
	 * @return string
	 */
	private static function build_view_text( $row, $students, $rating ) {
		$parts = array(
			isset( $row['title'] ) ? (string) $row['title'] : '',
			isset( $row['category'] ) ? (string) $row['category'] : '',
			isset( $row['difficulty'] ) ? (string) $row['difficulty'] : '',
			! empty( $row['duration'] ) ? absint( $row['duration'] ) . ' Minutes' : '',
			isset( $row['price'] ) ? '$' . number_format_i18n( (float) $row['price'], 2 ) : '',
			$students . ' Students',
			$rating > 0 ? number_format_i18n( $rating, 1 ) . ' rating' : 'No ratings yet',
			self::status_label( self::db_status_to_ui( isset( $row['status'] ) ? (string) $row['status'] : '' ) ),
		);
		$parts = array_filter( array_map( 'trim', $parts ) );
		return implode( ' · ', $parts );
	}

	/**
	 * @param float $rating Rating.
	 * @return string Plain stars for frozen markup.
	 */
	private static function stars_html( $rating ) {
		$full = (int) round( max( 0, min( 5, (float) $rating ) ) );
		return str_repeat( '★', $full ) . str_repeat( '☆', 5 - $full );
	}

	/**
	 * @param string $raw Attachment ID or URL.
	 * @return string
	 */
	private static function resolve_image_url( $raw ) {
		$raw = trim( (string) $raw );
		if ( '' === $raw ) {
			return gmm_design_asset_url( 'assets/img/course/01.jpg' );
		}
		if ( ctype_digit( $raw ) ) {
			$url = wp_get_attachment_image_url( absint( $raw ), 'medium_large' );
			if ( $url ) {
				return $url;
			}
		}
		if ( filter_var( $raw, FILTER_VALIDATE_URL ) ) {
			return esc_url_raw( $raw );
		}
		return gmm_design_asset_url( 'assets/img/course/01.jpg' );
	}

	/**
	 * @param string $db_status DB status.
	 * @return string UI filter key.
	 */
	private static function db_status_to_ui( $db_status ) {
		$db_status = sanitize_key( $db_status );
		if ( in_array( $db_status, array( 'approved', 'published' ), true ) ) {
			return 'published';
		}
		if ( 'rejected' === $db_status ) {
			return 'draft';
		}
		if ( in_array( $db_status, array( 'pending', 'draft', 'scheduled' ), true ) ) {
			return $db_status;
		}
		return 'pending';
	}

	/**
	 * @param string $ui_status Filter key.
	 * @return array<int, string>
	 */
	private static function filter_status_to_db( $ui_status ) {
		switch ( sanitize_key( $ui_status ) ) {
			case 'published':
				return array( 'approved', 'published' );
			case 'pending':
				return array( 'pending' );
			case 'draft':
				return array( 'draft', 'rejected' );
			case 'scheduled':
				return array( 'scheduled' );
			default:
				return array();
		}
	}

	/**
	 * @param string $ui_status UI status.
	 * @return string
	 */
	private static function status_badge_class( $ui_status ) {
		$map = array(
			'published' => 'is-published',
			'pending'   => 'is-pending',
			'draft'     => 'is-draft',
			'scheduled' => 'is-scheduled',
		);
		return isset( $map[ $ui_status ] ) ? $map[ $ui_status ] : 'is-pending';
	}

	/**
	 * @param string $ui_status UI status.
	 * @return string
	 */
	private static function status_label( $ui_status ) {
		$map = array(
			'published' => __( 'Published', 'gospel-music-mastery' ),
			'pending'   => __( 'Pending', 'gospel-music-mastery' ),
			'draft'     => __( 'Draft', 'gospel-music-mastery' ),
			'scheduled' => __( 'Scheduled', 'gospel-music-mastery' ),
		);
		return isset( $map[ $ui_status ] ) ? $map[ $ui_status ] : __( 'Pending', 'gospel-music-mastery' );
	}

	/**
	 * @param int $class_id Class ID.
	 * @return int
	 */
	private static function count_students( $class_id ) {
		$class_id = absint( $class_id );
		if ( ! $class_id ) {
			return 0;
		}
		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		return (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT student_id) FROM {$bookings}
				WHERE class_id = %d AND student_id > 0
				AND booking_status NOT IN ('cancelled','rejected')",
				$class_id
			)
		);
	}

	/**
	 * Block delete when upcoming confirmed bookings exist.
	 *
	 * @param int $class_id Class ID.
	 * @return true|WP_Error
	 */
	private static function active_booking_block( $class_id ) {
		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$today    = current_time( 'Y-m-d' );
		$count    = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$bookings}
				WHERE class_id = %d
				AND booking_date >= %s
				AND booking_status IN ('pending','confirmed','upcoming','scheduled')",
				$class_id,
				$today
			)
		);
		if ( $count > 0 ) {
			return new WP_Error(
				'gmm_has_bookings',
				__( 'This class has upcoming bookings. Cancel or complete them before deleting.', 'gospel-music-mastery' )
			);
		}
		return true;
	}

	/**
	 * @param int $class_id Class ID.
	 * @return array<string, mixed>|null
	 */
	private static function get_raw_row( $class_id ) {
		$class_id = absint( $class_id );
		if ( ! $class_id ) {
			return null;
		}
		global $wpdb;
		$table = GMM_Database::table( 'classes' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $class_id ),
			ARRAY_A
		);
		return is_array( $row ) ? $row : null;
	}

	/**
	 * @param array<string, mixed> $row Row.
	 * @return bool
	 */
	private static function is_trashed( $row ) {
		$status = isset( $row['status'] ) ? sanitize_key( (string) $row['status'] ) : '';
		return $status === self::trash_status();
	}

	/**
	 * @return string
	 */
	private static function trash_status() {
		return class_exists( 'GMM_Security' ) ? GMM_Security::soft_delete_status() : 'trash';
	}

	/**
	 * @param int $class_id Class ID.
	 * @return bool
	 */
	private static function has_featured_request( $class_id ) {
		$requests = get_option( self::FEATURED_OPTION, array() );
		return is_array( $requests ) && isset( $requests[ absint( $class_id ) ] );
	}

	/**
	 * @param int $class_id Class ID.
	 * @return void
	 */
	private static function clear_featured_request( $class_id ) {
		$class_id = absint( $class_id );
		$requests = get_option( self::FEATURED_OPTION, array() );
		if ( ! is_array( $requests ) || ! isset( $requests[ $class_id ] ) ) {
			return;
		}
		unset( $requests[ $class_id ] );
		update_option( self::FEATURED_OPTION, $requests, false );
	}

	/**
	 * @param int $user_id WP user ID.
	 * @return void
	 */
	private static function flush_related_caches( $user_id = 0 ) {
		$user_id = absint( $user_id );
		if ( $user_id && class_exists( 'GMM_Teacher_Dashboard' ) ) {
			GMM_Teacher_Dashboard::flush_cache( $user_id );
		}
	}

	/**
	 * @return array<string, int>
	 */
	private static function empty_stats() {
		return array(
			'total'     => 0,
			'published' => 0,
			'pending'   => 0,
			'drafts'    => 0,
		);
	}

	/**
	 * @return array<string, string>
	 */
	private static function default_filters() {
		return array(
			'search'   => '',
			'status'   => 'all',
			'category' => '',
		);
	}

	/**
	 * @return array<string, string>
	 */
	private static function parse_filters_from_request() {
		$filters = self::default_filters();
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['gmm_class_search'] ) ) {
			$filters['search'] = sanitize_text_field( wp_unslash( $_REQUEST['gmm_class_search'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['gmm_class_status'] ) ) {
			$filters['status'] = sanitize_key( wp_unslash( $_REQUEST['gmm_class_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['gmm_class_category'] ) ) {
			$filters['category'] = sanitize_text_field( wp_unslash( $_REQUEST['gmm_class_category'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['search'] ) && '' === $filters['search'] ) {
			$filters['search'] = sanitize_text_field( wp_unslash( $_REQUEST['search'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['status'] ) && 'all' === $filters['status'] ) {
			$filters['status'] = sanitize_key( wp_unslash( $_REQUEST['status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['category'] ) && '' === $filters['category'] ) {
			$filters['category'] = sanitize_text_field( wp_unslash( $_REQUEST['category'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		return $filters;
	}

	/**
	 * @return array<int, string>
	 */
	public static function category_options() {
		return array(
			'Gospel Piano',
			'Hammond Organ',
			'Vocal Training',
			'Guitar',
			'Bass Guitar',
			'Drums & Percussion',
			'Choir Direction',
			'Music Theory',
			'Worship Leadership',
		);
	}

	/**
	 * @return array<int, string>
	 */
	public static function difficulty_options() {
		return array( 'Beginner', 'Intermediate', 'Advanced' );
	}

	/**
	 * @return array<int, int>
	 */
	public static function duration_options() {
		return array( 30, 45, 60, 90 );
	}

	/**
	 * @return void
	 */
	private function verify_ajax_nonce() {
		$nonce = '';
		if ( isset( $_REQUEST[ self::NONCE_FIELD ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$nonce = sanitize_text_field( wp_unslash( $_REQUEST[ self::NONCE_FIELD ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( isset( $_REQUEST['nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$nonce = sanitize_text_field( wp_unslash( $_REQUEST['nonce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'gospel-music-mastery' ) ), 403 );
		}
		if ( ! self::user_can_manage() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'gospel-music-mastery' ) ), 403 );
		}
	}

	/**
	 * @return array<string, mixed>
	 */
	private function collect_request_fields() {
		$src = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$out = array();

		$map = array(
			'title'            => array( 'title', 'class_title' ),
			'description'      => array( 'description', 'class_description' ),
			'category'         => array( 'category', 'class_category' ),
			'difficulty'       => array( 'difficulty', 'class_difficulty' ),
			'duration'         => array( 'duration', 'class_duration' ),
			'price'            => array( 'price', 'class_price' ),
			'image'            => array( 'image', 'image_id', 'class_image_id' ),
			'featured_request' => array( 'featured_request', 'request_featured' ),
		);

		foreach ( $map as $key => $aliases ) {
			foreach ( $aliases as $alias ) {
				if ( array_key_exists( $alias, $src ) ) {
					$out[ $key ] = $src[ $alias ];
					break;
				}
			}
		}

		return $out;
	}

	/**
	 * Pre-create image attachment when ID provided; file handled after create.
	 *
	 * @param array<string, mixed> $data    By ref fields.
	 * @param int                  $class_id Class ID (0 on create).
	 * @param int                  $user_id  User.
	 * @return true|WP_Error
	 */
	private function maybe_attach_uploaded_image( &$data, $class_id, $user_id ) {
		unset( $class_id, $user_id );
		if ( ! empty( $data['image'] ) ) {
			$check = self::validate_class_fields( array( 'image' => $data['image'] ), false );
			if ( is_wp_error( $check ) ) {
				return $check;
			}
		}
		return true;
	}

	/**
	 * Upload class image via GMM_Media or direct handle.
	 *
	 * @param int $class_id Class ID.
	 * @param int $user_id  User ID.
	 * @return int|WP_Error Attachment ID.
	 */
	private function upload_class_image_file( $class_id, $user_id ) {
		$class_id = absint( $class_id );
		if ( ! $class_id || ! self::owns_class( $class_id, $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only update your own classes.', 'gospel-music-mastery' ) );
		}

		if ( empty( $_FILES['class_image'] ) && empty( $_FILES['gmm_file'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return new WP_Error( 'gmm_no_file', __( 'No image file was uploaded.', 'gospel-music-mastery' ) );
		}

		$file_key = ! empty( $_FILES['class_image'] ) ? 'class_image' : 'gmm_file'; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( class_exists( 'GMM_Media' ) ) {
			$result = GMM_Media::upload_image( $file_key, 'class_image', $class_id, '' );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
			if ( is_array( $result ) ) {
				if ( ! empty( $result['id'] ) ) {
					return absint( $result['id'] );
				}
				if ( ! empty( $result['attachment_id'] ) ) {
					return absint( $result['attachment_id'] );
				}
			}
			if ( is_numeric( $result ) ) {
				return absint( $result );
			}
		}

		return self::handle_direct_image_upload( $file_key, $user_id, $class_id );
	}

	/**
	 * @param string $file_key File key.
	 * @param int    $user_id  User.
	 * @param int    $class_id Class.
	 * @return int|WP_Error
	 */
	private static function handle_direct_image_upload( $file_key, $user_id, $class_id ) {
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$file = isset( $_FILES[ $file_key ] ) ? $_FILES[ $file_key ] : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! $file || empty( $file['tmp_name'] ) ) {
			return new WP_Error( 'gmm_no_file', __( 'No file was uploaded.', 'gospel-music-mastery' ) );
		}

		$size = isset( $file['size'] ) ? (int) $file['size'] : 0;
		if ( $size > 5 * MB_IN_BYTES ) {
			return new WP_Error( 'gmm_file_size', __( 'Image must be 5MB or smaller.', 'gospel-music-mastery' ) );
		}

		$attachment_id = media_handle_upload(
			$file_key,
			0,
			array(),
			array(
				'test_form' => false,
				'mimes'     => array(
					'jpg|jpeg|jpe' => 'image/jpeg',
					'png'          => 'image/png',
					'webp'         => 'image/webp',
				),
			)
		);

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		$attachment_id = absint( $attachment_id );
		wp_update_post(
			array(
				'ID'          => $attachment_id,
				'post_author' => absint( $user_id ),
			)
		);
		update_post_meta( $attachment_id, '_gmm_media', 1 );
		update_post_meta( $attachment_id, '_gmm_class_id', absint( $class_id ) );

		global $wpdb;
		$table = GMM_Database::table( 'classes' );
		$wpdb->update(
			$table,
			array(
				'image'      => (string) $attachment_id,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => absint( $class_id ) ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		return $attachment_id;
	}

	/**
	 * Create/edit modal markup (uses existing modal / form classes).
	 *
	 * @return string
	 */
	private static function render_class_form_modal() {
		$categories  = self::category_options();
		$difficulties = self::difficulty_options();
		$durations   = self::duration_options();
		$nonce       = wp_create_nonce( self::NONCE_ACTION );

		ob_start();
		?>
	<div class="modal fade gospel-demo-modal" id="class-form-modal" tabindex="-1" aria-labelledby="class-form-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered modal-lg">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="class-form-title"><?php echo esc_html__( 'Create New Class', 'gospel-music-mastery' ); ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__( 'Close', 'gospel-music-mastery' ); ?>"></button>
				</div>
				<form id="gmm-teacher-class-form" enctype="multipart/form-data" novalidate>
					<input type="hidden" name="<?php echo esc_attr( self::NONCE_FIELD ); ?>" value="<?php echo esc_attr( $nonce ); ?>">
					<input type="hidden" name="class_id" id="gmm-class-id" value="">
					<input type="hidden" name="image_id" id="gmm-class-image-id" value="">
					<div class="modal-body">
						<div class="gospel-alert gospel-alert-error" id="gmm-class-form-error" hidden>
							<i class="far fa-circle-exclamation"></i>
							<span id="gmm-class-form-error-text"></span>
						</div>
						<div class="form-group">
							<label for="gmm-class-title"><?php echo esc_html__( 'Class Title', 'gospel-music-mastery' ); ?></label>
							<input type="text" class="form-control" id="gmm-class-title" name="title" required>
						</div>
						<div class="form-group">
							<label for="gmm-class-category"><?php echo esc_html__( 'Category', 'gospel-music-mastery' ); ?></label>
							<select class="form-control form-select" id="gmm-class-category" name="category" required>
								<option value=""><?php echo esc_html__( 'Select category', 'gospel-music-mastery' ); ?></option>
								<?php foreach ( $categories as $cat ) : ?>
									<option value="<?php echo esc_attr( $cat ); ?>"><?php echo esc_html( $cat ); ?></option>
								<?php endforeach; ?>
							</select>
						</div>
						<div class="form-group">
							<label for="gmm-class-description"><?php echo esc_html__( 'Description', 'gospel-music-mastery' ); ?></label>
							<textarea class="form-control" id="gmm-class-description" name="description" rows="4" maxlength="1000" required></textarea>
						</div>
						<div class="row">
							<div class="col-md-4">
								<div class="form-group">
									<label for="gmm-class-price"><?php echo esc_html__( 'Price', 'gospel-music-mastery' ); ?></label>
									<input type="number" class="form-control" id="gmm-class-price" name="price" min="1" step="0.01" required>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label for="gmm-class-duration"><?php echo esc_html__( 'Duration', 'gospel-music-mastery' ); ?></label>
									<select class="form-control form-select" id="gmm-class-duration" name="duration" required>
										<option value=""><?php echo esc_html__( 'Select duration', 'gospel-music-mastery' ); ?></option>
										<?php foreach ( $durations as $mins ) : ?>
											<option value="<?php echo esc_attr( (string) $mins ); ?>"><?php echo esc_html( $mins . ' Minutes' ); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
							<div class="col-md-4">
								<div class="form-group">
									<label for="gmm-class-difficulty"><?php echo esc_html__( 'Difficulty', 'gospel-music-mastery' ); ?></label>
									<select class="form-control form-select" id="gmm-class-difficulty" name="difficulty">
										<option value=""><?php echo esc_html__( 'Select difficulty', 'gospel-music-mastery' ); ?></option>
										<?php foreach ( $difficulties as $diff ) : ?>
											<option value="<?php echo esc_attr( $diff ); ?>"><?php echo esc_html( $diff ); ?></option>
										<?php endforeach; ?>
									</select>
								</div>
							</div>
						</div>
						<div class="form-group">
							<label for="gmm-class-image"><?php echo esc_html__( 'Class Image', 'gospel-music-mastery' ); ?></label>
							<input type="file" class="form-control" id="gmm-class-image" name="class_image" accept=".jpg,.jpeg,.png,image/jpeg,image/png,image/webp">
							<p class="photo-hint"><?php echo esc_html__( 'JPG, PNG, or WebP. Maximum size: 5MB.', 'gospel-music-mastery' ); ?></p>
							<img src="" alt="" id="gmm-class-image-preview" hidden style="max-width:160px;margin-top:8px;">
						</div>
						<div class="form-check">
							<input class="form-check-input" type="checkbox" value="1" id="gmm-class-featured-request" name="featured_request">
							<label class="form-check-label" for="gmm-class-featured-request"><?php echo esc_html__( 'Request featured placement (admin approval required)', 'gospel-music-mastery' ); ?></label>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal"><?php echo esc_html__( 'Cancel', 'gospel-music-mastery' ); ?></button>
						<button type="submit" class="theme-btn" id="gmm-class-form-submit"><?php echo esc_html__( 'Save Class', 'gospel-music-mastery' ); ?></button>
					</div>
				</form>
			</div>
		</div>
	</div>
		<?php
		return (string) ob_get_clean();
	}
}
