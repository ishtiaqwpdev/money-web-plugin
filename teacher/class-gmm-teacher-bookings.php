<?php
/**
 * Teacher Booking Management controller.
 *
 * Lists/filters own gmm_bookings, confirm/cancel/complete via AJAX,
 * and supplies data for templates/teacher/bookings.php (frozen UI).
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Teacher_Bookings
 */
class GMM_Teacher_Bookings {

	const NONCE_ACTION = 'gmm_teacher_booking_action';
	const NONCE_FIELD  = 'gmm_teacher_booking_nonce';
	const REVIEW_META  = 'gmm_review_allowed_';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 25, 2 );
		$loader->add_filter( 'gmm_shortcode_html', $instance, 'enhance_bookings_html', 20, 2 );

		$loader->add_action( 'wp_ajax_gmm_teacher_booking_confirm', $instance, 'ajax_confirm' );
		$loader->add_action( 'wp_ajax_gmm_teacher_booking_cancel', $instance, 'ajax_cancel' );
		$loader->add_action( 'wp_ajax_gmm_teacher_booking_complete', $instance, 'ajax_complete' );
		$loader->add_action( 'wp_ajax_gmm_teacher_booking_details', $instance, 'ajax_details' );
		$loader->add_action( 'wp_ajax_gmm_teacher_booking_list', $instance, 'ajax_list' );
		$loader->add_action( 'wp_ajax_gmm_teacher_booking_review', $instance, 'ajax_review' );

		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 40 );

		$loader->add_action( 'gmm_booking_confirmed', $instance, 'flush_on_booking_change', 10, 2 );
		$loader->add_action( 'gmm_booking_cancelled', $instance, 'flush_on_booking_change', 10, 2 );
		$loader->add_action( 'gmm_booking_completed', $instance, 'on_booking_completed', 10, 2 );
	}

	/**
	 * Inject vars into [gmm_teacher_bookings].
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Shortcode.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_teacher_bookings' !== $tag ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * Inject filter toolbar + cancel modal (no redesign of table/cards).
	 *
	 * @param string $html HTML.
	 * @param string $tag  Tag.
	 * @return string
	 */
	public function enhance_bookings_html( $html, $tag ) {
		if ( 'gmm_teacher_bookings' !== $tag || '' === $html ) {
			return $html;
		}
		if ( ! self::user_can_manage() ) {
			return $html;
		}

		if ( false === strpos( $html, 'id="gmm-booking-filters"' ) ) {
			$filters = self::render_filter_toolbar();
			$html    = preg_replace(
				'/(<div class="booking-tabs"[^>]*>.*?<\/div>)/s',
				'$1' . $filters,
				$html,
				1
			);
		}

		if ( false === strpos( $html, 'id="booking-cancel-modal"' ) ) {
			$html .= self::render_cancel_modal();
		}

		return $html;
	}

	/**
	 * Whether current user may manage teacher bookings.
	 *
	 * @param int $user_id User ID.
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
		return current_user_can( 'manage_gmm_bookings' ) || current_user_can( 'manage_options' );
	}

	/**
	 * Template variables.
	 *
	 * @param int $user_id Optional user ID.
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
				'bookings'            => array(),
				'booking_rows'        => array(),
				'booking_stats'       => self::empty_stats(),
				'logout_url'          => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
			);
		}

		$filters = self::parse_filters_from_request();
		$rows    = self::get_bookings( $user_id, $filters );
		$stats   = self::get_booking_stats( $user_id );
		$profile = class_exists( 'GMM_Teacher_Dashboard' )
			? GMM_Teacher_Dashboard::get_profile_summary( $user_id )
			: array();
		$dash    = class_exists( 'GMM_Teacher_Dashboard' )
			? GMM_Teacher_Dashboard::get_statistics( $user_id )
			: array();

		return array(
			'gmm_teacher_denied'  => false,
			'gmm_teacher_pending' => false,
			'bookings'            => $rows,
			'booking_rows'        => array_map( array( __CLASS__, 'format_booking_row' ), $rows ),
			'booking_stats'       => $stats,
			'booking_filters'     => $filters,
			'teacher_classes'     => self::get_teacher_class_options( $user_id ),
			'profile_summary'     => $profile,
			'logout_url'          => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
			'user_name'           => isset( $profile['name'] ) ? $profile['name'] : '',
			'user_first_name'     => isset( $profile['first_name'] ) ? $profile['first_name'] : '',
			'profile_stats'       => array(
				'rating'   => isset( $profile['rating'] ) ? (float) $profile['rating'] : 0,
				'students' => isset( $stats['students'] ) ? (int) $stats['students'] : ( isset( $dash['total_students'] ) ? (int) $dash['total_students'] : 0 ),
				'classes'  => isset( $dash['total_classes'] ) ? (int) $dash['total_classes'] : 0,
			),
			'links'               => array(
				'classes' => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teacher_classes' ) : '',
			),
		);
	}

	/**
	 * List teacher bookings with student/class joins.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $filters Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_bookings( $user_id = 0, $filters = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! self::user_can_manage( $user_id ) ) {
			return array();
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}

		$filters = wp_parse_args(
			is_array( $filters ) ? $filters : array(),
			array(
				'status'   => 'all',
				'date'     => 'all',
				'class_id' => 0,
				'limit'    => 100,
			)
		);

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$students = GMM_Database::table( 'students' );
		$classes  = GMM_Database::table( 'classes' );

		$sql    = "SELECT b.*,
				s.first_name AS student_first_name,
				s.last_name AS student_last_name,
				s.profile_image AS student_image,
				s.learning_level AS student_skill,
				s.learning_goals AS student_goals,
				c.title AS class_title,
				c.category AS class_category,
				c.duration AS class_duration
			FROM {$bookings} b
			LEFT JOIN {$students} s ON s.id = b.student_id
			LEFT JOIN {$classes} c ON c.id = b.class_id
			WHERE b.teacher_id = %d";
		$params = array( $teacher_id );

		$status = sanitize_key( (string) $filters['status'] );
		if ( $status && 'all' !== $status ) {
			$sql     .= ' AND b.booking_status = %s';
			$params[] = $status;
		}

		$class_id = absint( $filters['class_id'] );
		if ( $class_id ) {
			$sql     .= ' AND b.class_id = %d';
			$params[] = $class_id;
		}

		$date_range = self::date_range_bounds( sanitize_key( (string) $filters['date'] ) );
		if ( $date_range ) {
			$sql     .= ' AND b.booking_date >= %s AND b.booking_date <= %s';
			$params[] = $date_range['from'];
			$params[] = $date_range['to'];
		}

		$sql     .= ' ORDER BY b.booking_date DESC, b.booking_time DESC LIMIT %d';
		$params[] = min( absint( $filters['limit'] ), 200 );

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Summary counts for the stats strip.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, int>
	 */
	public static function get_booking_stats( $user_id = 0 ) {
		$user_id    = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return self::empty_stats();
		}

		global $wpdb;
		$table = GMM_Database::table( 'bookings' );
		$today = current_time( 'Y-m-d' );

		$pending = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE teacher_id = %d AND booking_status = %s",
				$teacher_id,
				'pending'
			)
		);

		$upcoming = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table}
				WHERE teacher_id = %d
				AND booking_date >= %s
				AND booking_status IN ('pending','confirmed','upcoming','scheduled')",
				$teacher_id,
				$today
			)
		);

		$completed = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$table} WHERE teacher_id = %d AND booking_status = %s",
				$teacher_id,
				'completed'
			)
		);

		$students = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT student_id) FROM {$table}
				WHERE teacher_id = %d AND student_id > 0",
				$teacher_id
			)
		);

		return array(
			'pending'   => $pending,
			'upcoming'  => $upcoming,
			'completed' => $completed,
			'students'  => $students,
		);
	}

	/**
	 * Confirm pending booking.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param int    $user_id    WP user ID.
	 * @param string $nonce      Optional nonce.
	 * @return true|WP_Error
	 */
	public static function confirm_booking( $booking_id, $user_id = 0, $nonce = '' ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$auth    = self::authorize( $user_id, $nonce );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( ! self::owns_booking( $booking_id, $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only update your own bookings.', 'gospel-music-mastery' ) );
		}

		$result = class_exists( 'GMM_Booking' )
			? GMM_Booking::teacher_confirm_booking( $booking_id, $user_id, '' )
			: new WP_Error( 'gmm_missing', __( 'Booking engine unavailable.', 'gospel-music-mastery' ) );

		if ( ! is_wp_error( $result ) ) {
			self::flush_related( $user_id );
		}

		return $result;
	}

	/**
	 * Cancel booking with optional reason.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param int    $user_id    WP user ID.
	 * @param string $nonce      Optional nonce.
	 * @param string $reason     Cancellation reason.
	 * @return true|WP_Error
	 */
	public static function cancel_booking( $booking_id, $user_id = 0, $nonce = '', $reason = '' ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$auth    = self::authorize( $user_id, $nonce );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( ! self::owns_booking( $booking_id, $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only update your own bookings.', 'gospel-music-mastery' ) );
		}

		$result = class_exists( 'GMM_Booking' )
			? GMM_Booking::teacher_cancel_booking( $booking_id, $user_id, '', $reason )
			: new WP_Error( 'gmm_missing', __( 'Booking engine unavailable.', 'gospel-music-mastery' ) );

		if ( ! is_wp_error( $result ) ) {
			if ( $reason ) {
				update_option( 'gmm_booking_cancel_reason_' . absint( $booking_id ), sanitize_textarea_field( $reason ), false );
			}
			self::flush_related( $user_id );
		}

		return $result;
	}

	/**
	 * Complete confirmed lesson.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param int    $user_id    WP user ID.
	 * @param string $nonce      Optional nonce.
	 * @return true|WP_Error
	 */
	public static function complete_booking( $booking_id, $user_id = 0, $nonce = '' ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$auth    = self::authorize( $user_id, $nonce );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( ! self::owns_booking( $booking_id, $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only update your own bookings.', 'gospel-music-mastery' ) );
		}

		$result = class_exists( 'GMM_Booking' )
			? GMM_Booking::teacher_complete_booking( $booking_id, $user_id, '' )
			: new WP_Error( 'gmm_missing', __( 'Booking engine unavailable.', 'gospel-music-mastery' ) );

		if ( ! is_wp_error( $result ) ) {
			self::grant_review_permission( $booking_id );
			self::flush_related( $user_id );
		}

		return $result;
	}

	/**
	 * Teacher-safe booking details (no private payment gateway data).
	 *
	 * @param int $booking_id Booking ID.
	 * @param int $user_id    WP user ID.
	 * @return array<string, mixed>|WP_Error|null
	 */
	public static function get_booking_details( $booking_id, $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! self::user_can_manage( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}
		if ( ! self::owns_booking( $booking_id, $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only view your own bookings.', 'gospel-music-mastery' ) );
		}

		$details = class_exists( 'GMM_Booking' )
			? GMM_Booking::get_details( $booking_id, $user_id )
			: null;

		if ( is_wp_error( $details ) || ! is_array( $details ) ) {
			return $details;
		}

		// Strip private payment fields beyond booking amount/status.
		unset( $details['payment_gateway'], $details['transaction_id'], $details['card_last4'] );

		$booking = isset( $details['booking'] ) && is_array( $details['booking'] ) ? $details['booking'] : array();
		$student = isset( $details['student'] ) && is_array( $details['student'] ) ? $details['student'] : array();
		$class   = isset( $details['class'] ) && is_array( $details['class'] ) ? $details['class'] : array();

		$student_name = trim(
			( isset( $student['first_name'] ) ? (string) $student['first_name'] : '' ) . ' ' .
			( isset( $student['last_name'] ) ? (string) $student['last_name'] : '' )
		);
		if ( '' === $student_name ) {
			$student_name = __( 'Student', 'gospel-music-mastery' );
		}

		$image = self::resolve_image(
			isset( $student['image'] ) ? (string) $student['image'] : '',
			'assets/img/team/02.jpg'
		);

		$duration = isset( $booking['duration'] ) ? absint( $booking['duration'] ) : 0;
		$amount   = isset( $booking['amount'] ) ? (float) $booking['amount'] : 0.0;
		$pay      = isset( $booking['payment_status'] ) ? sanitize_key( (string) $booking['payment_status'] ) : '';
		$status   = isset( $booking['booking_status'] ) ? sanitize_key( (string) $booking['booking_status'] ) : '';

		$history = array(
			array(
				'label' => __( 'Created', 'gospel-music-mastery' ),
				'value' => ! empty( $booking['created_at'] ) ? (string) $booking['created_at'] : '—',
			),
			array(
				'label' => __( 'Updated', 'gospel-music-mastery' ),
				'value' => ! empty( $booking['updated_at'] ) ? (string) $booking['updated_at'] : '—',
			),
			array(
				'label' => __( 'Status', 'gospel-music-mastery' ),
				'value' => self::status_label( $status ),
			),
		);

		$cancel_reason = get_option( 'gmm_booking_cancel_reason_' . absint( $booking_id ), '' );
		if ( is_string( $cancel_reason ) && '' !== $cancel_reason ) {
			$history[] = array(
				'label' => __( 'Cancellation reason', 'gospel-music-mastery' ),
				'value' => $cancel_reason,
			);
		}

		$learning = '';
		$row      = self::get_joined_row( $booking_id, $user_id );
		if ( is_array( $row ) ) {
			$parts = array_filter(
				array(
					isset( $row['student_skill'] ) ? (string) $row['student_skill'] : '',
					isset( $row['student_goals'] ) ? (string) $row['student_goals'] : '',
				)
			);
			$learning = implode( ' · ', $parts );
		}

		return array(
			'id'              => absint( $booking_id ),
			'student_name'    => $student_name,
			'student_image'   => $image,
			'student_learning'=> $learning ? $learning : ( isset( $booking['notes'] ) ? (string) $booking['notes'] : '' ),
			'class_name'      => isset( $class['title'] ) && $class['title'] ? (string) $class['title'] : __( 'Lesson', 'gospel-music-mastery' ),
			'date_label'      => self::format_date_label( isset( $booking['booking_date'] ) ? (string) $booking['booking_date'] : '' ),
			'time_label'      => self::format_time_label( isset( $booking['booking_time'] ) ? (string) $booking['booking_time'] : '' ),
			'duration_label'  => $duration ? sprintf( '%d Minutes', $duration ) : '—',
			'amount_label'    => '$' . number_format_i18n( $amount, 2 ),
			'payment_label'   => self::payment_label( $pay ),
			'status'          => $status,
			'status_label'    => self::status_label( $status ),
			'notes'           => isset( $booking['notes'] ) && $booking['notes'] ? (string) $booking['notes'] : '—',
			'history'         => $history,
			'review_allowed'  => (bool) get_option( self::REVIEW_META . absint( $booking_id ), false ),
		);
	}

	/**
	 * AJAX handlers.
	 *
	 * @return void
	 */
	public function ajax_confirm() {
		$this->verify_ajax();
		$id     = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$result = self::confirm_booking( $id, get_current_user_id(), '' );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}
		$row = self::get_joined_row( $id, get_current_user_id() );
		wp_send_json_success(
			array(
				'message' => __( 'Booking confirmed.', 'gospel-music-mastery' ),
				'row'     => $row ? self::format_booking_row( $row ) : null,
				'stats'   => self::get_booking_stats( get_current_user_id() ),
			)
		);
	}

	/**
	 * @return void
	 */
	public function ajax_cancel() {
		$this->verify_ajax();
		$id      = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$confirm = ! empty( $_POST['confirm'] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$reason  = isset( $_POST['reason'] ) ? sanitize_textarea_field( wp_unslash( $_POST['reason'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( ! $confirm ) {
			wp_send_json_error( array( 'message' => __( 'Please confirm cancellation.', 'gospel-music-mastery' ) ), 400 );
		}

		$result = self::cancel_booking( $id, get_current_user_id(), '', $reason );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		$row = self::get_joined_row( $id, get_current_user_id() );
		wp_send_json_success(
			array(
				'message' => __( 'Booking cancelled.', 'gospel-music-mastery' ),
				'row'     => $row ? self::format_booking_row( $row ) : null,
				'stats'   => self::get_booking_stats( get_current_user_id() ),
			)
		);
	}

	/**
	 * @return void
	 */
	public function ajax_complete() {
		$this->verify_ajax();
		$id     = isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$result = self::complete_booking( $id, get_current_user_id(), '' );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}
		$row = self::get_joined_row( $id, get_current_user_id() );
		wp_send_json_success(
			array(
				'message'        => __( 'Lesson marked as completed.', 'gospel-music-mastery' ),
				'row'            => $row ? self::format_booking_row( $row ) : null,
				'stats'          => self::get_booking_stats( get_current_user_id() ),
				'review_allowed' => true,
			)
		);
	}

	/**
	 * @return void
	 */
	public function ajax_details() {
		$this->verify_ajax();
		$id      = isset( $_REQUEST['booking_id'] ) ? absint( $_REQUEST['booking_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$details = self::get_booking_details( $id, get_current_user_id() );
		if ( is_wp_error( $details ) ) {
			wp_send_json_error( array( 'message' => $details->get_error_message() ), 403 );
		}
		if ( ! is_array( $details ) ) {
			wp_send_json_error( array( 'message' => __( 'Booking not found.', 'gospel-music-mastery' ) ), 404 );
		}
		wp_send_json_success( array( 'details' => $details ) );
	}

	/**
	 * @return void
	 */
	public function ajax_list() {
		$this->verify_ajax();
		$filters = self::parse_filters_from_request();
		$rows    = self::get_bookings( get_current_user_id(), $filters );
		wp_send_json_success(
			array(
				'rows'    => array_map( array( __CLASS__, 'format_booking_row' ), $rows ),
				'stats'   => self::get_booking_stats( get_current_user_id() ),
				'filters' => $filters,
			)
		);
	}

	/**
	 * @return void
	 */
	public function ajax_review() {
		$this->verify_ajax();
		$id = isset( $_REQUEST['booking_id'] ) ? absint( $_REQUEST['booking_id'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! self::owns_booking( $id, get_current_user_id() ) ) {
			wp_send_json_error( array( 'message' => __( 'You can only view your own bookings.', 'gospel-music-mastery' ) ), 403 );
		}

		$review = self::get_booking_review( $id );
		wp_send_json_success(
			array(
				'review' => $review,
			)
		);
	}

	/**
	 * Enqueue script.
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
		if ( ! has_shortcode( $content, 'gmm_teacher_bookings' ) && false === strpos( $content, 'gmm_teacher_bookings' ) ) {
			return;
		}

		$version = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';
		wp_enqueue_script(
			'gmm-teacher-bookings',
			GMM_URL . 'assets/js/gmm-teacher-bookings.js',
			array( 'gmm-core-script' ),
			$version,
			true
		);

		$vars = self::get_template_vars();
		wp_localize_script(
			'gmm-teacher-bookings',
			'GMM_TEACHER_BOOKINGS',
			array(
				'nonce'      => wp_create_nonce( self::NONCE_ACTION ),
				'nonceField' => self::NONCE_FIELD,
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'stats'      => isset( $vars['booking_stats'] ) ? $vars['booking_stats'] : self::empty_stats(),
				'actions'    => array(
					'confirm'  => 'gmm_teacher_booking_confirm',
					'cancel'   => 'gmm_teacher_booking_cancel',
					'complete' => 'gmm_teacher_booking_complete',
					'details'  => 'gmm_teacher_booking_details',
					'list'     => 'gmm_teacher_booking_list',
					'review'   => 'gmm_teacher_booking_review',
				),
				'i18n'       => array(
					'confirmed' => __( 'Booking confirmed.', 'gospel-music-mastery' ),
					'cancelled' => __( 'Booking cancelled.', 'gospel-music-mastery' ),
					'completed' => __( 'Lesson marked as completed.', 'gospel-music-mastery' ),
					'error'     => __( 'Something went wrong. Please try again.', 'gospel-music-mastery' ),
					'confirmCancel' => __( 'Are you sure you want to cancel this booking?', 'gospel-music-mastery' ),
					'noReview'  => __( 'No student review yet.', 'gospel-music-mastery' ),
					'empty'     => __( 'No bookings available yet.', 'gospel-music-mastery' ),
				),
			)
		);
	}

	/**
	 * On completed: grant review permission + flush caches.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $status     Status.
	 * @return void
	 */
	public function on_booking_completed( $booking_id, $status = '' ) {
		unset( $status );
		self::grant_review_permission( absint( $booking_id ) );
		$this->flush_on_booking_change( $booking_id );
	}

	/**
	 * Flush dashboard caches when booking status changes.
	 *
	 * @param int $booking_id Booking ID.
	 * @param mixed $status Status.
	 * @return void
	 */
	public function flush_on_booking_change( $booking_id, $status = null ) {
		unset( $status );
		$user_id = 0;
		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$teachers = GMM_Database::table( 'teachers' );
		$tid      = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT teacher_id FROM {$bookings} WHERE id = %d LIMIT 1", absint( $booking_id ) )
		);
		if ( $tid ) {
			$user_id = (int) $wpdb->get_var(
				$wpdb->prepare( "SELECT user_id FROM {$teachers} WHERE id = %d LIMIT 1", $tid )
			);
		}
		self::flush_related( $user_id );
	}

	/**
	 * Format row for frozen table UI.
	 *
	 * @param array<string, mixed> $row DB row.
	 * @return array<string, mixed>
	 */
	public static function format_booking_row( $row ) {
		if ( ! is_array( $row ) || empty( $row['id'] ) ) {
			return array();
		}

		$status   = isset( $row['booking_status'] ) ? sanitize_key( (string) $row['booking_status'] ) : 'pending';
		$duration = isset( $row['duration'] ) ? absint( $row['duration'] ) : 0;
		$amount   = isset( $row['amount'] ) ? (float) $row['amount'] : 0.0;
		$pay      = isset( $row['payment_status'] ) ? sanitize_key( (string) $row['payment_status'] ) : '';

		$student = trim(
			( isset( $row['student_first_name'] ) ? (string) $row['student_first_name'] : '' ) . ' ' .
			( isset( $row['student_last_name'] ) ? (string) $row['student_last_name'] : '' )
		);
		if ( '' === $student ) {
			$student = __( 'Student', 'gospel-music-mastery' );
		}

		$class = isset( $row['class_title'] ) && $row['class_title']
			? (string) $row['class_title']
			: __( 'Lesson', 'gospel-music-mastery' );

		$image = self::resolve_image(
			isset( $row['student_image'] ) ? (string) $row['student_image'] : '',
			'assets/img/team/02.jpg'
		);

		$learning = trim(
			implode(
				' · ',
				array_filter(
					array(
						isset( $row['student_skill'] ) ? (string) $row['student_skill'] : '',
						isset( $row['student_goals'] ) ? (string) $row['student_goals'] : '',
					)
				)
			)
		);
		$notes = isset( $row['notes'] ) && $row['notes'] ? (string) $row['notes'] : ( $learning ? $learning : '—' );

		$date_label = self::format_date_label( isset( $row['booking_date'] ) ? (string) $row['booking_date'] : '' );
		$time_label = self::format_time_label( isset( $row['booking_time'] ) ? (string) $row['booking_time'] : '' );

		return array(
			'id'             => (int) $row['id'],
			'status'         => $status,
			'status_label'   => self::status_label( $status ),
			'badge_class'    => self::status_badge_class( $status ),
			'student_name'   => $student,
			'student_image'  => $image,
			'class_id'       => isset( $row['class_id'] ) ? (int) $row['class_id'] : 0,
			'class_name'     => $class,
			'booking_date'   => isset( $row['booking_date'] ) ? (string) $row['booking_date'] : '',
			'date_label'     => $date_label,
			'time_label'     => $time_label,
			'duration'       => $duration,
			'duration_label' => $duration ? sprintf( '%d Minutes', $duration ) : '—',
			'amount'         => $amount,
			'amount_label'   => '$' . number_format_i18n( $amount, 2 ),
			'payment_status' => $pay,
			'payment_label'  => self::payment_label( $pay ),
			'notes'          => $notes,
			'can_confirm'    => ( 'pending' === $status ),
			'can_cancel'     => in_array( $status, array( 'pending', 'confirmed' ), true ),
			'can_complete'   => ( 'confirmed' === $status ),
			'can_review'     => ( 'completed' === $status ),
		);
	}

	/**
	 * Grant review permission after completion.
	 *
	 * @param int $booking_id Booking ID.
	 * @return void
	 */
	public static function grant_review_permission( $booking_id ) {
		$booking_id = absint( $booking_id );
		if ( ! $booking_id ) {
			return;
		}
		update_option( self::REVIEW_META . $booking_id, 1, false );

		/**
		 * Fires when a completed booking becomes reviewable.
		 *
		 * @param int $booking_id Booking ID.
		 */
		do_action( 'gmm_booking_review_permission_granted', $booking_id );
	}

	/**
	 * @param int $user_id User.
	 * @param string $nonce Nonce (optional when already verified).
	 * @return true|WP_Error
	 */
	private static function authorize( $user_id, $nonce = '' ) {
		if ( '' !== $nonce && ! wp_verify_nonce( (string) $nonce, self::NONCE_ACTION ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}
		if ( ! self::user_can_manage( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}
		return true;
	}

	/**
	 * @param int $booking_id Booking ID.
	 * @param int $user_id    User ID.
	 * @return bool
	 */
	public static function owns_booking( $booking_id, $user_id ) {
		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		$booking_id = absint( $booking_id );
		if ( ! $teacher_id || ! $booking_id ) {
			return false;
		}
		global $wpdb;
		$table = GMM_Database::table( 'bookings' );
		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE id = %d AND teacher_id = %d LIMIT 1",
				$booking_id,
				$teacher_id
			)
		);
		return ! empty( $found );
	}

	/**
	 * @param int $booking_id Booking ID.
	 * @param int $user_id    User ID.
	 * @return array<string, mixed>|null
	 */
	private static function get_joined_row( $booking_id, $user_id ) {
		$rows = self::get_bookings(
			$user_id,
			array(
				'status' => 'all',
				'date'   => 'all',
				'limit'  => 200,
			)
		);
		foreach ( $rows as $row ) {
			if ( (int) $row['id'] === (int) $booking_id ) {
				return $row;
			}
		}

		// Fallback single query if filtered out.
		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return null;
		}
		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$students = GMM_Database::table( 'students' );
		$classes  = GMM_Database::table( 'classes' );
		$row      = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT b.*,
					s.first_name AS student_first_name,
					s.last_name AS student_last_name,
					s.profile_image AS student_image,
					s.learning_level AS student_skill,
					s.learning_goals AS student_goals,
					c.title AS class_title
				FROM {$bookings} b
				LEFT JOIN {$students} s ON s.id = b.student_id
				LEFT JOIN {$classes} c ON c.id = b.class_id
				WHERE b.id = %d AND b.teacher_id = %d
				LIMIT 1",
				absint( $booking_id ),
				$teacher_id
			),
			ARRAY_A
		);
		return is_array( $row ) ? $row : null;
	}

	/**
	 * @param int $booking_id Booking ID.
	 * @return array<string, mixed>|null
	 */
	private static function get_booking_review( $booking_id ) {
		$booking_id = absint( $booking_id );
		if ( ! $booking_id || ! class_exists( 'GMM_Database' ) ) {
			return null;
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$reviews  = GMM_Database::table( 'reviews' );

		$booking = $wpdb->get_row(
			$wpdb->prepare( "SELECT student_id, teacher_id, class_id FROM {$bookings} WHERE id = %d LIMIT 1", $booking_id ),
			ARRAY_A
		);
		if ( ! is_array( $booking ) ) {
			return null;
		}

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT rating, comment, status, created_at FROM {$reviews}
				WHERE student_id = %d AND teacher_id = %d AND class_id = %d
				ORDER BY created_at DESC LIMIT 1",
				absint( $booking['student_id'] ),
				absint( $booking['teacher_id'] ),
				absint( $booking['class_id'] )
			),
			ARRAY_A
		);

		if ( ! is_array( $row ) ) {
			return null;
		}

		$rating = isset( $row['rating'] ) ? (float) $row['rating'] : 0;
		$stars  = (int) round( max( 0, min( 5, $rating ) ) );

		return array(
			'rating'  => $rating,
			'stars'   => str_repeat( '★', $stars ) . str_repeat( '☆', 5 - $stars ),
			'comment' => isset( $row['comment'] ) ? (string) $row['comment'] : '',
			'status'  => isset( $row['status'] ) ? (string) $row['status'] : '',
		);
	}

	/**
	 * @param int $user_id User ID.
	 * @return array<int, array{id:int,title:string}>
	 */
	private static function get_teacher_class_options( $user_id ) {
		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}
		global $wpdb;
		$table = GMM_Database::table( 'classes' );
		$trash = class_exists( 'GMM_Security' ) ? GMM_Security::soft_delete_status() : 'trash';
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, title FROM {$table}
				WHERE teacher_id = %d AND status <> %s
				ORDER BY title ASC LIMIT 100",
				$teacher_id,
				$trash
			),
			ARRAY_A
		);
		$out = array();
		if ( is_array( $rows ) ) {
			foreach ( $rows as $row ) {
				$out[] = array(
					'id'    => (int) $row['id'],
					'title' => (string) $row['title'],
				);
			}
		}
		return $out;
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function parse_filters_from_request() {
		$filters = array(
			'status'   => 'all',
			'date'     => 'all',
			'class_id' => 0,
			'limit'    => 100,
		);
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['gmm_booking_status'] ) ) {
			$filters['status'] = sanitize_key( wp_unslash( $_REQUEST['gmm_booking_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( isset( $_REQUEST['status'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$filters['status'] = sanitize_key( wp_unslash( $_REQUEST['status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['gmm_booking_date'] ) ) {
			$filters['date'] = sanitize_key( wp_unslash( $_REQUEST['gmm_booking_date'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( isset( $_REQUEST['date'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$filters['date'] = sanitize_key( wp_unslash( $_REQUEST['date'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $_REQUEST['gmm_booking_class'] ) ) {
			$filters['class_id'] = absint( $_REQUEST['gmm_booking_class'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		} elseif ( isset( $_REQUEST['class_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$filters['class_id'] = absint( $_REQUEST['class_id'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		return $filters;
	}

	/**
	 * @param string $key today|week|month|all.
	 * @return array{from:string,to:string}|null
	 */
	private static function date_range_bounds( $key ) {
		$today = current_time( 'Y-m-d' );
		$ts    = current_time( 'timestamp' );
		switch ( $key ) {
			case 'today':
				return array( 'from' => $today, 'to' => $today );
			case 'week':
				$start = wp_date( 'Y-m-d', strtotime( 'monday this week', $ts ) );
				$end   = wp_date( 'Y-m-d', strtotime( 'sunday this week', $ts ) );
				return array( 'from' => $start, 'to' => $end );
			case 'month':
				return array(
					'from' => wp_date( 'Y-m-01', $ts ),
					'to'   => wp_date( 'Y-m-t', $ts ),
				);
			default:
				return null;
		}
	}

	/**
	 * @return array<string, int>
	 */
	private static function empty_stats() {
		return array(
			'pending'   => 0,
			'upcoming'  => 0,
			'completed' => 0,
			'students'  => 0,
		);
	}

	/**
	 * @param string $status Status.
	 * @return string
	 */
	private static function status_label( $status ) {
		$map = array(
			'pending'   => __( 'Pending', 'gospel-music-mastery' ),
			'confirmed' => __( 'Confirmed', 'gospel-music-mastery' ),
			'completed' => __( 'Completed', 'gospel-music-mastery' ),
			'cancelled' => __( 'Cancelled', 'gospel-music-mastery' ),
			'refunded'  => __( 'Refunded', 'gospel-music-mastery' ),
		);
		$status = sanitize_key( $status );
		return isset( $map[ $status ] ) ? $map[ $status ] : ucfirst( $status );
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
			'refunded'  => 'is-cancelled',
		);
		$status = sanitize_key( $status );
		return isset( $map[ $status ] ) ? $map[ $status ] : 'is-pending';
	}

	/**
	 * @param string $pay Payment status.
	 * @return string
	 */
	private static function payment_label( $pay ) {
		$map = array(
			'pending'   => __( 'Pending', 'gospel-music-mastery' ),
			'paid'      => __( 'Paid', 'gospel-music-mastery' ),
			'completed' => __( 'Paid', 'gospel-music-mastery' ),
			'failed'    => __( 'Failed', 'gospel-music-mastery' ),
			'refunded'  => __( 'Refunded', 'gospel-music-mastery' ),
		);
		$pay = sanitize_key( $pay );
		return isset( $map[ $pay ] ) ? $map[ $pay ] : ( $pay ? ucfirst( $pay ) : '—' );
	}

	/**
	 * @param string $date Y-m-d.
	 * @return string
	 */
	private static function format_date_label( $date ) {
		$ts = strtotime( $date . ' 12:00:00' );
		return $ts ? wp_date( 'F j, Y', $ts ) : ( $date ? $date : '—' );
	}

	/**
	 * @param string $time H:i:s.
	 * @return string
	 */
	private static function format_time_label( $time ) {
		$ts = strtotime( '1970-01-01 ' . $time );
		return $ts ? strtoupper( gmdate( 'h:i A', $ts ) ) : ( $time ? $time : '—' );
	}

	/**
	 * @param string $raw Attachment ID or URL.
	 * @param string $fallback Design asset path.
	 * @return string
	 */
	private static function resolve_image( $raw, $fallback ) {
		$raw = trim( (string) $raw );
		if ( '' === $raw ) {
			return gmm_design_asset_url( $fallback );
		}
		if ( ctype_digit( $raw ) ) {
			$url = wp_get_attachment_image_url( absint( $raw ), 'thumbnail' );
			if ( $url ) {
				return $url;
			}
		}
		if ( filter_var( $raw, FILTER_VALIDATE_URL ) ) {
			return esc_url_raw( $raw );
		}
		return gmm_design_asset_url( $fallback );
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
	 * Filter toolbar HTML (uses existing form-control classes).
	 *
	 * @return string
	 */
	private static function render_filter_toolbar() {
		$classes = self::get_teacher_class_options( get_current_user_id() );
		$filters = self::parse_filters_from_request();
		ob_start();
		?>
		<div class="row gmm-booking-filters" id="gmm-booking-filters">
			<div class="col-md-4">
				<select class="form-control form-select" id="gmm-booking-date-filter" aria-label="<?php echo esc_attr__( 'Filter by date', 'gospel-music-mastery' ); ?>">
					<option value="all"<?php selected( $filters['date'], 'all' ); ?>><?php echo esc_html__( 'All Dates', 'gospel-music-mastery' ); ?></option>
					<option value="today"<?php selected( $filters['date'], 'today' ); ?>><?php echo esc_html__( 'Today', 'gospel-music-mastery' ); ?></option>
					<option value="week"<?php selected( $filters['date'], 'week' ); ?>><?php echo esc_html__( 'This Week', 'gospel-music-mastery' ); ?></option>
					<option value="month"<?php selected( $filters['date'], 'month' ); ?>><?php echo esc_html__( 'This Month', 'gospel-music-mastery' ); ?></option>
				</select>
			</div>
			<div class="col-md-4">
				<select class="form-control form-select" id="gmm-booking-class-filter" aria-label="<?php echo esc_attr__( 'Filter by class', 'gospel-music-mastery' ); ?>">
					<option value="0"><?php echo esc_html__( 'All Classes', 'gospel-music-mastery' ); ?></option>
					<?php foreach ( $classes as $class ) : ?>
						<option value="<?php echo esc_attr( (string) $class['id'] ); ?>"<?php selected( (int) $filters['class_id'], (int) $class['id'] ); ?>><?php echo esc_html( $class['title'] ); ?></option>
					<?php endforeach; ?>
				</select>
			</div>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Cancel confirmation modal.
	 *
	 * @return string
	 */
	private static function render_cancel_modal() {
		ob_start();
		?>
	<div class="modal fade gospel-demo-modal" id="booking-cancel-modal" tabindex="-1" aria-labelledby="booking-cancel-title" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered">
			<div class="modal-content">
				<div class="modal-header">
					<h5 class="modal-title" id="booking-cancel-title"><?php echo esc_html__( 'Cancel Booking', 'gospel-music-mastery' ); ?></h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo esc_attr__( 'Close', 'gospel-music-mastery' ); ?>"></button>
				</div>
				<div class="modal-body">
					<p><?php echo esc_html__( 'Are you sure you want to cancel', 'gospel-music-mastery' ); ?> <strong id="booking-cancel-name"><?php echo esc_html__( 'this booking', 'gospel-music-mastery' ); ?></strong>?</p>
					<div class="form-group">
						<label for="booking-cancel-reason"><?php echo esc_html__( 'Cancellation reason (optional)', 'gospel-music-mastery' ); ?></label>
						<textarea class="form-control" id="booking-cancel-reason" rows="3" maxlength="500"></textarea>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal"><?php echo esc_html__( 'Keep Booking', 'gospel-music-mastery' ); ?></button>
					<button type="button" class="theme-btn" id="booking-cancel-confirm"><?php echo esc_html__( 'Cancel Booking', 'gospel-music-mastery' ); ?></button>
				</div>
			</div>
		</div>
	</div>
		<?php
		return (string) ob_get_clean();
	}
}
