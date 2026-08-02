<?php
/**
 * Teacher Availability Calendar controller.
 *
 * Manages gmm_availability for the owning teacher, calendar events,
 * and AJAX for templates/teacher/availability.php (frozen UI).
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Teacher_Availability
 */
class GMM_Teacher_Availability {

	const NONCE_ACTION = 'gmm_teacher_availability_action';
	const NONCE_FIELD  = 'gmm_teacher_availability_nonce';
	const STATUS_AVAILABLE = 'available';
	const STATUS_OPEN      = 'open'; // Legacy synonym for bookable.
	const STATUS_BOOKED    = 'booked';
	const STATUS_BLOCKED   = 'blocked';
	const STATUS_CLOSED    = 'closed';
	const REPEAT_META      = 'gmm_availability_repeat_weekly';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 25, 2 );

		$loader->add_action( 'wp_ajax_gmm_teacher_availability_add', $instance, 'ajax_add' );
		$loader->add_action( 'wp_ajax_gmm_teacher_availability_update', $instance, 'ajax_update' );
		$loader->add_action( 'wp_ajax_gmm_teacher_availability_delete', $instance, 'ajax_delete' );
		$loader->add_action( 'wp_ajax_gmm_teacher_availability_list', $instance, 'ajax_list' );
		$loader->add_action( 'wp_ajax_gmm_teacher_availability_save', $instance, 'ajax_save' );

		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 40 );

		$loader->add_action( 'gmm_booking_confirmed', $instance, 'flush_on_booking', 10, 1 );
		$loader->add_action( 'gmm_booking_cancelled', $instance, 'flush_on_booking', 10, 1 );
		$loader->add_action( 'gmm_booking_completed', $instance, 'flush_on_booking', 10, 1 );
	}

	/**
	 * Inject vars into [gmm_teacher_availability].
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Shortcode.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_teacher_availability' !== $tag ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * Whether current user may manage availability.
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

		if ( ! self::user_can_manage( $user_id ) ) {
			$pending = function_exists( 'gmm_is_teacher' ) && gmm_is_teacher( $user_id )
				&& class_exists( 'GMM_Teacher_Auth' )
				&& ! GMM_Teacher_Auth::is_approved( $user_id );

			return array(
				'gmm_teacher_denied'  => true,
				'gmm_teacher_pending' => $pending,
				'availability'        => array(),
				'calendar_events'     => array(),
				'slots'               => array(),
				'booked_dates'        => array(),
				'logout_url'          => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
			);
		}

		$from   = gmdate( 'Y-m-01', strtotime( '-1 month', current_time( 'timestamp' ) ) );
		$to     = gmdate( 'Y-m-t', strtotime( '+3 months', current_time( 'timestamp' ) ) );
		$slots  = self::get_availability( $user_id, array( 'from' => $from, 'to' => $to, 'limit' => 500 ) );
		$events = self::get_calendar_events( $user_id, array( 'from' => $from, 'to' => $to ) );
		$profile = class_exists( 'GMM_Teacher_Dashboard' )
			? GMM_Teacher_Dashboard::get_profile_summary( $user_id )
			: array();
		$dash = class_exists( 'GMM_Teacher_Dashboard' )
			? GMM_Teacher_Dashboard::get_statistics( $user_id )
			: array();

		return array(
			'gmm_teacher_denied'  => false,
			'gmm_teacher_pending' => false,
			'availability'        => $slots,
			'calendar_events'     => $events,
			'slots'               => array_map( array( __CLASS__, 'format_slot_for_ui' ), $slots ),
			'booked_dates'        => self::get_booked_date_keys( $user_id, $from, $to ),
			'repeat_weekly'       => (bool) get_user_meta( $user_id, self::REPEAT_META, true ),
			'profile_summary'     => $profile,
			'logout_url'          => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
			'user_name'           => isset( $profile['name'] ) ? $profile['name'] : '',
			'user_first_name'     => isset( $profile['first_name'] ) ? $profile['first_name'] : '',
			'profile_stats'       => array(
				'rating'   => isset( $profile['rating'] ) ? (float) $profile['rating'] : 0,
				'students' => isset( $dash['total_students'] ) ? (int) $dash['total_students'] : 0,
				'classes'  => isset( $dash['total_classes'] ) ? (int) $dash['total_classes'] : 0,
			),
			'links'               => array(
				'classes' => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teacher_classes' ) : '',
			),
		);
	}

	/**
	 * Add availability slot (default status: available).
	 *
	 * @param array<string, mixed> $data    Fields.
	 * @param int                  $user_id WP user ID.
	 * @return int|WP_Error
	 */
	public static function add_availability( $data, $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		$auth = self::authorize( $user_id );
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

		$validated = self::validate_fields( $data, true );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$dup = self::find_duplicate(
			$teacher_id,
			$validated['available_date'],
			$validated['start_time'],
			$validated['end_time']
		);
		if ( $dup ) {
			return new WP_Error( 'gmm_duplicate', __( 'This time slot already exists for the selected date.', 'gospel-music-mastery' ) );
		}

		$row = array(
			'teacher_id'     => $teacher_id,
			'available_date' => $validated['available_date'],
			'start_time'     => $validated['start_time'],
			'end_time'       => $validated['end_time'],
			'status'         => self::STATUS_AVAILABLE,
			'created_at'     => current_time( 'mysql' ),
		);

		global $wpdb;
		$table    = GMM_Database::table( 'availability' );
		$inserted = $wpdb->insert( $table, $row );

		if ( ! $inserted ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not add availability.', 'gospel-music-mastery' ) );
		}

		$id = (int) $wpdb->insert_id;

		/**
		 * Fires after a teacher adds availability.
		 *
		 * @param int                  $id      Slot ID.
		 * @param array<string, mixed> $row     Row.
		 * @param int                  $user_id User ID.
		 */
		do_action( 'gmm_teacher_availability_added', $id, $row, $user_id );

		self::flush_related( $user_id );
		return $id;
	}

	/**
	 * Update own availability slot.
	 *
	 * @param int                  $availability_id Row ID.
	 * @param array<string, mixed> $data            Fields.
	 * @param int                  $user_id         WP user ID.
	 * @return true|WP_Error
	 */
	public static function update_availability( $availability_id, $data, $user_id = 0 ) {
		$user_id         = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$availability_id = absint( $availability_id );

		$auth = self::authorize( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( ! self::owns_slot( $availability_id, $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only update your own availability.', 'gospel-music-mastery' ) );
		}

		$existing = self::get_raw_row( $availability_id );
		if ( ! $existing ) {
			return new WP_Error( 'gmm_missing', __( 'Availability slot not found.', 'gospel-music-mastery' ) );
		}

		$validated = self::validate_fields( $data, false, $existing );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}
		if ( empty( $validated ) ) {
			return new WP_Error( 'gmm_no_fields', __( 'No valid availability fields to update.', 'gospel-music-mastery' ) );
		}

		$date  = isset( $validated['available_date'] ) ? $validated['available_date'] : $existing['available_date'];
		$start = isset( $validated['start_time'] ) ? $validated['start_time'] : $existing['start_time'];
		$end   = isset( $validated['end_time'] ) ? $validated['end_time'] : $existing['end_time'];

		if ( $end <= $start ) {
			return new WP_Error( 'gmm_time_range', __( 'End time must be after start time.', 'gospel-music-mastery' ) );
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		$dup        = self::find_duplicate( $teacher_id, $date, $start, $end, $availability_id );
		if ( $dup ) {
			return new WP_Error( 'gmm_duplicate', __( 'This time slot already exists for the selected date.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'availability' );
		$updated = $wpdb->update(
			$table,
			$validated,
			array( 'id' => $availability_id ),
			null,
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not update availability.', 'gospel-music-mastery' ) );
		}

		do_action( 'gmm_teacher_availability_updated', $availability_id, $validated, $user_id );
		self::flush_related( $user_id );
		return true;
	}

	/**
	 * Delete own availability slot (confirmation enforced by AJAX).
	 *
	 * @param int $availability_id Row ID.
	 * @param int $user_id         WP user ID.
	 * @return true|WP_Error
	 */
	public static function delete_availability( $availability_id, $user_id = 0 ) {
		$user_id         = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$availability_id = absint( $availability_id );

		$auth = self::authorize( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( ! self::owns_slot( $availability_id, $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only delete your own availability.', 'gospel-music-mastery' ) );
		}

		$existing = self::get_raw_row( $availability_id );
		if ( ! $existing ) {
			return new WP_Error( 'gmm_missing', __( 'Availability slot not found.', 'gospel-music-mastery' ) );
		}

		$block = self::active_booking_in_slot( $existing );
		if ( is_wp_error( $block ) ) {
			return $block;
		}

		global $wpdb;
		$table   = GMM_Database::table( 'availability' );
		$deleted = $wpdb->delete( $table, array( 'id' => $availability_id ), array( '%d' ) );

		if ( false === $deleted ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not delete availability.', 'gospel-music-mastery' ) );
		}

		do_action( 'gmm_teacher_availability_deleted', $availability_id, $existing, $user_id );
		self::flush_related( $user_id );
		return true;
	}

	/**
	 * Get availability for a teacher.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Optional filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_availability( $user_id = 0, $args = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id || ! GMM_Teacher::can_view_profile( $user_id ) ) {
			return array();
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}

		$args = wp_parse_args(
			is_array( $args ) ? $args : array(),
			array(
				'from'   => '',
				'to'     => '',
				'status' => '',
				'limit'  => 200,
			)
		);

		global $wpdb;
		$table  = GMM_Database::table( 'availability' );
		$sql    = "SELECT * FROM {$table} WHERE teacher_id = %d";
		$params = array( $teacher_id );

		if ( ! empty( $args['from'] ) ) {
			$sql     .= ' AND available_date >= %s';
			$params[] = self::sanitize_date( (string) $args['from'] );
		}
		if ( ! empty( $args['to'] ) ) {
			$sql     .= ' AND available_date <= %s';
			$params[] = self::sanitize_date( (string) $args['to'] );
		}
		if ( ! empty( $args['status'] ) ) {
			$status = sanitize_key( (string) $args['status'] );
			if ( in_array( $status, array( 'available', 'open' ), true ) ) {
				$sql     .= ' AND status IN (%s,%s)';
				$params[] = self::STATUS_AVAILABLE;
				$params[] = self::STATUS_OPEN;
			} else {
				$sql     .= ' AND status = %s';
				$params[] = $status;
			}
		}

		$sql     .= ' ORDER BY available_date ASC, start_time ASC LIMIT %d';
		$params[] = min( absint( $args['limit'] ), 500 );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Calendar events for the frozen UI.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Date range.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_calendar_events( $user_id = 0, $args = array() ) {
		$rows   = self::get_availability( $user_id, $args );
		$events = array();

		foreach ( $rows as $row ) {
			if ( ! self::is_bookable_status( isset( $row['status'] ) ? $row['status'] : '' ) ) {
				continue;
			}
			$events[] = array(
				'id'     => (int) $row['id'],
				'title'  => __( 'Available Slot', 'gospel-music-mastery' ),
				'date'   => (string) $row['available_date'],
				'start'  => self::format_time_label( (string) $row['start_time'] ),
				'end'    => self::format_time_label( (string) $row['end_time'] ),
				'status' => self::normalize_status( (string) $row['status'] ),
			);
		}

		return $events;
	}

	/**
	 * Whether a status is bookable (open window).
	 *
	 * @param string $status Status.
	 * @return bool
	 */
	public static function is_bookable_status( $status ) {
		$status = sanitize_key( (string) $status );
		return in_array( $status, array( self::STATUS_AVAILABLE, self::STATUS_OPEN ), true );
	}

	/**
	 * Bookable status SQL list for booking engine.
	 *
	 * @return array<int, string>
	 */
	public static function bookable_statuses() {
		return array( self::STATUS_AVAILABLE, self::STATUS_OPEN );
	}

	/**
	 * Prepare recurring weekly copies (no UI — helper only).
	 *
	 * @param int $user_id WP user ID.
	 * @param int $weeks   Weeks ahead.
	 * @return array<int, array<string, mixed>> Proposed rows (not inserted).
	 */
	public static function prepare_recurring_availability( $user_id = 0, $weeks = 4 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$weeks   = max( 1, min( 12, absint( $weeks ) ) );
		$slots   = self::get_availability(
			$user_id,
			array(
				'from'   => current_time( 'Y-m-d' ),
				'to'     => current_time( 'Y-m-d' ),
				'status' => 'available',
				'limit'  => 50,
			)
		);

		$proposed = array();
		foreach ( $slots as $slot ) {
			for ( $i = 1; $i <= $weeks; $i++ ) {
				$ts = strtotime( (string) $slot['available_date'] . ' +' . $i . ' weeks' );
				if ( ! $ts ) {
					continue;
				}
				$proposed[] = array(
					'available_date' => wp_date( 'Y-m-d', $ts ),
					'start_time'     => (string) $slot['start_time'],
					'end_time'       => (string) $slot['end_time'],
					'status'         => self::STATUS_AVAILABLE,
					'source_id'      => (int) $slot['id'],
				);
			}
		}

		/**
		 * Filter prepared recurring availability proposals (not persisted).
		 *
		 * @param array<int, array<string, mixed>> $proposed Proposals.
		 * @param int                              $user_id  User.
		 * @param int                              $weeks    Weeks.
		 */
		return apply_filters( 'gmm_teacher_availability_recurring_proposals', $proposed, $user_id, $weeks );
	}

	/**
	 * AJAX: add slot.
	 *
	 * @return void
	 */
	public function ajax_add() {
		$this->verify_ajax();
		$user_id = get_current_user_id();
		$data    = $this->collect_fields();

		$result = self::add_availability( $data, $user_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		$row = self::get_raw_row( (int) $result );
		wp_send_json_success(
			array(
				'message' => __( 'Time slot added.', 'gospel-music-mastery' ),
				'id'      => (int) $result,
				'slot'    => $row ? self::format_slot_for_ui( $row ) : null,
				'events'  => self::get_calendar_events( $user_id ),
			)
		);
	}

	/**
	 * AJAX: update slot.
	 *
	 * @return void
	 */
	public function ajax_update() {
		$this->verify_ajax();
		$user_id = get_current_user_id();
		$id      = isset( $_POST['availability_id'] ) ? absint( $_POST['availability_id'] ) : ( isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$data    = $this->collect_fields();

		$result = self::update_availability( $id, $data, $user_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		$row = self::get_raw_row( $id );
		wp_send_json_success(
			array(
				'message' => __( 'Time slot updated.', 'gospel-music-mastery' ),
				'id'      => $id,
				'slot'    => $row ? self::format_slot_for_ui( $row ) : null,
				'events'  => self::get_calendar_events( $user_id ),
			)
		);
	}

	/**
	 * AJAX: delete slot (requires confirm=1).
	 *
	 * @return void
	 */
	public function ajax_delete() {
		$this->verify_ajax();
		$user_id = get_current_user_id();
		$id      = isset( $_POST['availability_id'] ) ? absint( $_POST['availability_id'] ) : ( isset( $_POST['id'] ) ? absint( $_POST['id'] ) : 0 ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$confirm = ! empty( $_POST['confirm'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( ! $confirm ) {
			wp_send_json_error( array( 'message' => __( 'Please confirm deletion.', 'gospel-music-mastery' ) ), 400 );
		}

		$result = self::delete_availability( $id, $user_id );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Time slot removed.', 'gospel-music-mastery' ),
				'id'      => $id,
				'events'  => self::get_calendar_events( $user_id ),
			)
		);
	}

	/**
	 * AJAX: refresh calendar/slots.
	 *
	 * @return void
	 */
	public function ajax_list() {
		$this->verify_ajax();
		$user_id = get_current_user_id();
		$from    = isset( $_REQUEST['from'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['from'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$to      = isset( $_REQUEST['to'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['to'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		$args = array( 'limit' => 500 );
		if ( $from ) {
			$args['from'] = $from;
		}
		if ( $to ) {
			$args['to'] = $to;
		}

		$slots = self::get_availability( $user_id, $args );
		wp_send_json_success(
			array(
				'slots'        => array_map( array( __CLASS__, 'format_slot_for_ui' ), $slots ),
				'events'       => self::get_calendar_events( $user_id, $args ),
				'booked_dates' => self::get_booked_date_keys(
					$user_id,
					$from ? $from : gmdate( 'Y-m-01', strtotime( '-1 month', current_time( 'timestamp' ) ) ),
					$to ? $to : gmdate( 'Y-m-t', strtotime( '+3 months', current_time( 'timestamp' ) ) )
				),
			)
		);
	}

	/**
	 * AJAX: save preferences (repeat weekly prep) + refresh.
	 *
	 * @return void
	 */
	public function ajax_save() {
		$this->verify_ajax();
		$user_id = get_current_user_id();
		$repeat  = ! empty( $_POST['repeat_weekly'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

		update_user_meta( $user_id, self::REPEAT_META, $repeat ? 1 : 0 );

		$proposals = array();
		if ( $repeat ) {
			$proposals = self::prepare_recurring_availability( $user_id, 4 );
		}

		/**
		 * Fires when teacher saves availability preferences.
		 *
		 * @param int                              $user_id   User.
		 * @param bool                             $repeat    Repeat flag.
		 * @param array<int, array<string, mixed>> $proposals Recurring proposals (not auto-inserted).
		 */
		do_action( 'gmm_teacher_availability_saved', $user_id, $repeat, $proposals );

		self::flush_related( $user_id );

		wp_send_json_success(
			array(
				'message'             => __( 'Availability saved successfully.', 'gospel-music-mastery' ),
				'repeat_weekly'       => $repeat,
				'recurring_prepared'  => count( $proposals ),
				'slots'               => array_map( array( __CLASS__, 'format_slot_for_ui' ), self::get_availability( $user_id ) ),
				'events'              => self::get_calendar_events( $user_id ),
			)
		);
	}

	/**
	 * Enqueue calendar script.
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
		if ( ! has_shortcode( $content, 'gmm_teacher_availability' ) && false === strpos( $content, 'gmm_teacher_availability' ) ) {
			return;
		}

		$version = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';
		wp_enqueue_script(
			'gmm-teacher-availability',
			GMM_URL . 'assets/js/gmm-teacher-availability.js',
			array( 'gmm-core-script' ),
			$version,
			true
		);

		$vars = self::get_template_vars();
		wp_localize_script(
			'gmm-teacher-availability',
			'GMM_TEACHER_AVAILABILITY',
			array(
				'nonce'      => wp_create_nonce( self::NONCE_ACTION ),
				'nonceField' => self::NONCE_FIELD,
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'slots'      => isset( $vars['slots'] ) ? $vars['slots'] : array(),
				'events'     => isset( $vars['calendar_events'] ) ? $vars['calendar_events'] : array(),
				'bookedDates'=> isset( $vars['booked_dates'] ) ? $vars['booked_dates'] : array(),
				'repeatWeekly' => ! empty( $vars['repeat_weekly'] ),
				'actions'    => array(
					'add'    => 'gmm_teacher_availability_add',
					'update' => 'gmm_teacher_availability_update',
					'delete' => 'gmm_teacher_availability_delete',
					'list'   => 'gmm_teacher_availability_list',
					'save'   => 'gmm_teacher_availability_save',
				),
				'i18n'       => array(
					'added'     => __( 'Time slot added.', 'gospel-music-mastery' ),
					'updated'   => __( 'Time slot updated.', 'gospel-music-mastery' ),
					'deleted'   => __( 'Time slot removed.', 'gospel-music-mastery' ),
					'saved'     => __( 'Availability saved successfully.', 'gospel-music-mastery' ),
					'error'     => __( 'Something went wrong. Please try again.', 'gospel-music-mastery' ),
					'confirm'   => __( 'Remove this time slot?', 'gospel-music-mastery' ),
					'needDate'  => __( 'Please select a date from the calendar.', 'gospel-music-mastery' ),
					'needTimes' => __( 'Please select both start and end times.', 'gospel-music-mastery' ),
					'timeOrder' => __( 'End time must be after start time.', 'gospel-music-mastery' ),
					'needSlots' => __( 'Please add at least one available time slot before saving.', 'gospel-music-mastery' ),
					'empty'     => __( 'No time slots added yet. Select a date and add your available hours.', 'gospel-music-mastery' ),
					'available' => __( 'Available', 'gospel-music-mastery' ),
				),
			)
		);
	}

	/**
	 * Flush caches when bookings change.
	 *
	 * @param mixed $booking Booking id or row.
	 * @return void
	 */
	public function flush_on_booking( $booking = null ) {
		$user_id = get_current_user_id();
		if ( is_array( $booking ) && ! empty( $booking['teacher_id'] ) ) {
			global $wpdb;
			$table   = GMM_Database::table( 'teachers' );
			$user_id = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT user_id FROM {$table} WHERE id = %d LIMIT 1", absint( $booking['teacher_id'] ) )
			);
		}
		self::flush_related( $user_id );
	}

	/**
	 * @param int $user_id WP user ID.
	 * @return true|WP_Error
	 */
	private static function authorize( $user_id ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'gmm_not_logged_in', __( 'You must be logged in.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_gmm_availability' ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_cap', __( 'Missing availability capability.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== absint( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only manage your own availability.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_options' ) && ! gmm_is_teacher( $user_id ) ) {
			return new WP_Error( 'gmm_not_teacher', __( 'Teacher role required.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * @param int $availability_id Row ID.
	 * @param int $user_id         WP user ID.
	 * @return bool
	 */
	public static function owns_slot( $availability_id, $user_id ) {
		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id || ! $availability_id ) {
			return false;
		}

		global $wpdb;
		$table = GMM_Database::table( 'availability' );
		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE id = %d AND teacher_id = %d LIMIT 1",
				$availability_id,
				$teacher_id
			)
		);

		return ! empty( $found );
	}

	/**
	 * Validate fields.
	 *
	 * @param array<string, mixed>      $data     Raw.
	 * @param bool                      $create   Creating.
	 * @param array<string, mixed>|null $existing Existing row on update.
	 * @return array<string, mixed>|WP_Error
	 */
	private static function validate_fields( $data, $create = false, $existing = null ) {
		$data  = is_array( $data ) ? $data : array();
		$clean = array();

		$date_raw = '';
		if ( isset( $data['available_date'] ) ) {
			$date_raw = (string) $data['available_date'];
		} elseif ( isset( $data['date'] ) ) {
			$date_raw = (string) $data['date'];
		}

		if ( $create || '' !== $date_raw ) {
			$date = self::sanitize_date( $date_raw );
			if ( ! $date ) {
				return new WP_Error( 'gmm_date_required', __( 'A valid date is required.', 'gospel-music-mastery' ) );
			}
			$today = current_time( 'Y-m-d' );
			if ( $date < $today ) {
				return new WP_Error( 'gmm_past_date', __( 'Past dates are not allowed.', 'gospel-music-mastery' ) );
			}
			$clean['available_date'] = $date;
		}

		$start_raw = null;
		if ( array_key_exists( 'start_time', $data ) ) {
			$start_raw = (string) $data['start_time'];
		}
		if ( $create || null !== $start_raw ) {
			$start = self::sanitize_time( (string) $start_raw );
			if ( ! $start ) {
				return new WP_Error( 'gmm_start_required', __( 'Start time is required.', 'gospel-music-mastery' ) );
			}
			$clean['start_time'] = $start;
		}

		$end_raw = null;
		if ( array_key_exists( 'end_time', $data ) ) {
			$end_raw = (string) $data['end_time'];
		}
		if ( $create || null !== $end_raw ) {
			$end = self::sanitize_time( (string) $end_raw );
			if ( ! $end ) {
				return new WP_Error( 'gmm_end_required', __( 'End time is required.', 'gospel-music-mastery' ) );
			}
			$clean['end_time'] = $end;
		}

		$start_cmp = isset( $clean['start_time'] ) ? $clean['start_time'] : ( is_array( $existing ) ? $existing['start_time'] : '' );
		$end_cmp   = isset( $clean['end_time'] ) ? $clean['end_time'] : ( is_array( $existing ) ? $existing['end_time'] : '' );
		if ( $start_cmp && $end_cmp && $end_cmp <= $start_cmp ) {
			return new WP_Error( 'gmm_time_range', __( 'End time must be after start time.', 'gospel-music-mastery' ) );
		}

		if ( array_key_exists( 'status', $data ) ) {
			$status = sanitize_key( (string) $data['status'] );
			$allowed = array(
				self::STATUS_AVAILABLE,
				self::STATUS_OPEN,
				self::STATUS_BOOKED,
				self::STATUS_BLOCKED,
				self::STATUS_CLOSED,
			);
			if ( ! in_array( $status, $allowed, true ) ) {
				return new WP_Error( 'gmm_status_invalid', __( 'Invalid availability status.', 'gospel-music-mastery' ) );
			}
			// Normalize open → available for consistency.
			$clean['status'] = ( self::STATUS_OPEN === $status ) ? self::STATUS_AVAILABLE : $status;
		}

		return $clean;
	}

	/**
	 * @param int    $teacher_id Teacher ID.
	 * @param string $date       Y-m-d.
	 * @param string $start      H:i:s.
	 * @param string $end        H:i:s.
	 * @param int    $exclude_id Exclude ID.
	 * @return int Duplicate ID or 0.
	 */
	private static function find_duplicate( $teacher_id, $date, $start, $end, $exclude_id = 0 ) {
		global $wpdb;
		$table = GMM_Database::table( 'availability' );

		// Exact match or overlapping window on same day.
		$sql = "SELECT id FROM {$table}
			WHERE teacher_id = %d
			AND available_date = %s
			AND start_time < %s
			AND end_time > %s";
		$params = array( $teacher_id, $date, $end, $start );

		if ( $exclude_id ) {
			$sql     .= ' AND id <> %d';
			$params[] = absint( $exclude_id );
		}

		$sql .= ' LIMIT 1';

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$found = $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
		return absint( $found );
	}

	/**
	 * Block delete when active bookings fall inside the slot.
	 *
	 * @param array<string, mixed> $slot Slot row.
	 * @return true|WP_Error
	 */
	private static function active_booking_in_slot( $slot ) {
		$teacher_id = isset( $slot['teacher_id'] ) ? absint( $slot['teacher_id'] ) : 0;
		$date       = isset( $slot['available_date'] ) ? (string) $slot['available_date'] : '';
		$start      = isset( $slot['start_time'] ) ? (string) $slot['start_time'] : '';
		$end        = isset( $slot['end_time'] ) ? (string) $slot['end_time'] : '';

		if ( ! $teacher_id || ! $date ) {
			return true;
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$rows     = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, booking_time, duration FROM {$bookings}
				WHERE teacher_id = %d
				AND booking_date = %s
				AND booking_status NOT IN ('cancelled','rejected','refunded')",
				$teacher_id,
				$date
			),
			ARRAY_A
		);

		if ( ! is_array( $rows ) || empty( $rows ) ) {
			return true;
		}

		$slot_start = strtotime( $date . ' ' . $start );
		$slot_end   = strtotime( $date . ' ' . $end );

		foreach ( $rows as $row ) {
			$b_start = strtotime( $date . ' ' . $row['booking_time'] );
			$dur     = absint( $row['duration'] ) > 0 ? absint( $row['duration'] ) : 60;
			$b_end   = $b_start + ( $dur * MINUTE_IN_SECONDS );
			if ( $b_start < $slot_end && $b_end > $slot_start ) {
				return new WP_Error(
					'gmm_has_bookings',
					__( 'This slot has an active booking and cannot be deleted.', 'gospel-music-mastery' )
				);
			}
		}

		return true;
	}

	/**
	 * @param int    $user_id WP user.
	 * @param string $from    From.
	 * @param string $to      To.
	 * @return array<int, string>
	 */
	private static function get_booked_date_keys( $user_id, $from, $to ) {
		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$from     = self::sanitize_date( $from );
		$to       = self::sanitize_date( $to );

		$sql    = "SELECT DISTINCT booking_date FROM {$bookings}
			WHERE teacher_id = %d
			AND booking_status NOT IN ('cancelled','rejected','refunded')";
		$params = array( $teacher_id );

		if ( $from ) {
			$sql     .= ' AND booking_date >= %s';
			$params[] = $from;
		}
		if ( $to ) {
			$sql     .= ' AND booking_date <= %s';
			$params[] = $to;
		}

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$dates = $wpdb->get_col( $wpdb->prepare( $sql, $params ) );
		return is_array( $dates ) ? array_values( array_filter( array_map( 'strval', $dates ) ) ) : array();
	}

	/**
	 * @param array<string, mixed> $row Row.
	 * @return array<string, mixed>
	 */
	public static function format_slot_for_ui( $row ) {
		if ( ! is_array( $row ) || empty( $row['id'] ) ) {
			return array();
		}

		$date   = (string) $row['available_date'];
		$ts     = strtotime( $date . ' 12:00:00' );
		$status = self::normalize_status( isset( $row['status'] ) ? (string) $row['status'] : self::STATUS_AVAILABLE );

		return array(
			'id'         => (int) $row['id'],
			'dateKey'    => $date,
			'dateLabel'  => $ts ? wp_date( 'l, F j, Y', $ts ) : $date,
			'dayName'    => $ts ? wp_date( 'l', $ts ) : '',
			'start'      => self::format_time_label( (string) $row['start_time'] ),
			'end'        => self::format_time_label( (string) $row['end_time'] ),
			'start_time' => (string) $row['start_time'],
			'end_time'   => (string) $row['end_time'],
			'status'     => $status,
			'statusLabel'=> self::status_label( $status ),
			'title'      => __( 'Available Slot', 'gospel-music-mastery' ),
		);
	}

	/**
	 * @param string $status Status.
	 * @return string
	 */
	private static function normalize_status( $status ) {
		$status = sanitize_key( $status );
		if ( self::STATUS_OPEN === $status ) {
			return self::STATUS_AVAILABLE;
		}
		return $status ? $status : self::STATUS_AVAILABLE;
	}

	/**
	 * @param string $status Status.
	 * @return string
	 */
	private static function status_label( $status ) {
		$map = array(
			'available' => __( 'Available', 'gospel-music-mastery' ),
			'booked'    => __( 'Booked', 'gospel-music-mastery' ),
			'blocked'   => __( 'Unavailable', 'gospel-music-mastery' ),
			'closed'    => __( 'Unavailable', 'gospel-music-mastery' ),
		);
		$status = self::normalize_status( $status );
		return isset( $map[ $status ] ) ? $map[ $status ] : __( 'Available', 'gospel-music-mastery' );
	}

	/**
	 * @param string $time H:i:s.
	 * @return string 12h label matching select options.
	 */
	private static function format_time_label( $time ) {
		$ts = strtotime( '1970-01-01 ' . $time );
		if ( ! $ts ) {
			return $time;
		}
		return strtoupper( gmdate( 'h:i A', $ts ) );
	}

	/**
	 * @param string $date Date.
	 * @return string Y-m-d or empty.
	 */
	private static function sanitize_date( $date ) {
		$date = sanitize_text_field( $date );
		if ( preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $date, $m ) ) {
			if ( checkdate( (int) $m[2], (int) $m[3], (int) $m[1] ) ) {
				return sprintf( '%04d-%02d-%02d', (int) $m[1], (int) $m[2], (int) $m[3] );
			}
			return '';
		}
		$ts = strtotime( $date );
		if ( ! $ts ) {
			return '';
		}
		return wp_date( 'Y-m-d', $ts );
	}

	/**
	 * @param string $time Time (24h or 12h).
	 * @return string H:i:s or empty.
	 */
	private static function sanitize_time( $time ) {
		$time = trim( sanitize_text_field( $time ) );
		if ( '' === $time ) {
			return '';
		}

		if ( preg_match( '/^(\d{1,2}):(\d{2})\s*(AM|PM)$/i', $time, $m ) ) {
			$h = (int) $m[1];
			$i = (int) $m[2];
			$p = strtoupper( $m[3] );
			if ( 'PM' === $p && 12 !== $h ) {
				$h += 12;
			}
			if ( 'AM' === $p && 12 === $h ) {
				$h = 0;
			}
			return sprintf( '%02d:%02d:00', min( 23, $h ), min( 59, $i ) );
		}

		if ( preg_match( '/^(\d{1,2}):(\d{2})(?::(\d{2}))?$/', $time, $m ) ) {
			$h = min( 23, absint( $m[1] ) );
			$i = min( 59, absint( $m[2] ) );
			$s = isset( $m[3] ) ? min( 59, absint( $m[3] ) ) : 0;
			return sprintf( '%02d:%02d:%02d', $h, $i, $s );
		}

		$ts = strtotime( $time );
		return $ts ? gmdate( 'H:i:s', $ts ) : '';
	}

	/**
	 * @param int $id Row ID.
	 * @return array<string, mixed>|null
	 */
	private static function get_raw_row( $id ) {
		$id = absint( $id );
		if ( ! $id ) {
			return null;
		}
		global $wpdb;
		$table = GMM_Database::table( 'availability' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $id ),
			ARRAY_A
		);
		return is_array( $row ) ? $row : null;
	}

	/**
	 * @param int $user_id User ID.
	 * @return void
	 */
	private static function flush_related( $user_id = 0 ) {
		$user_id = absint( $user_id );
		if ( $user_id && class_exists( 'GMM_Teacher_Dashboard' ) ) {
			GMM_Teacher_Dashboard::flush_cache( $user_id );
		}
	}

	/**
	 * @return void
	 */
	private function verify_ajax() {
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
	private function collect_fields() {
		$src = wp_unslash( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$out = array();

		if ( isset( $src['available_date'] ) ) {
			$out['available_date'] = $src['available_date'];
		} elseif ( isset( $src['date'] ) ) {
			$out['date'] = $src['date'];
		} elseif ( isset( $src['dateKey'] ) ) {
			$out['date'] = $src['dateKey'];
		}

		if ( isset( $src['start_time'] ) ) {
			$out['start_time'] = $src['start_time'];
		} elseif ( isset( $src['start'] ) ) {
			$out['start_time'] = $src['start'];
		}

		if ( isset( $src['end_time'] ) ) {
			$out['end_time'] = $src['end_time'];
		} elseif ( isset( $src['end'] ) ) {
			$out['end_time'] = $src['end'];
		}

		if ( isset( $src['status'] ) ) {
			$out['status'] = $src['status'];
		}

		return $out;
	}
}
