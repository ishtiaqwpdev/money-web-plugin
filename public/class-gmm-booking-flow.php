<?php
/**
 * Student lesson booking flow controller.
 *
 * Powers [gmm_booking_form] → templates/public/booking-form.php
 * and enhances [gmm_student_bookings] history/cancel without redesign.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Booking_Flow
 */
class GMM_Booking_Flow {

	const NONCE_ACTION = 'gmm_booking_flow';
	const NONCE_FIELD  = 'gmm_booking_flow_nonce';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 30, 2 );
		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 40 );
		$loader->add_action( 'template_redirect', $instance, 'maybe_redirect_guests', 6 );

		$loader->add_action( 'wp_ajax_gmm_booking_flow_teachers', $instance, 'ajax_load_teachers' );
		$loader->add_action( 'wp_ajax_gmm_booking_flow_classes', $instance, 'ajax_load_classes' );
		$loader->add_action( 'wp_ajax_gmm_booking_flow_slots', $instance, 'ajax_load_slots' );
		$loader->add_action( 'wp_ajax_gmm_booking_flow_create', $instance, 'ajax_create_booking' );
		$loader->add_action( 'wp_ajax_gmm_booking_flow_cancel', $instance, 'ajax_cancel_booking' );
		$loader->add_action( 'wp_ajax_gmm_booking_flow_history', $instance, 'ajax_history' );
	}

	/**
	 * Inject template vars for booking form + student history.
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Shortcode.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_booking_form' === $tag ) {
			return array_merge( is_array( $args ) ? $args : array(), self::get_form_template_vars( is_array( $args ) ? $args : array() ) );
		}
		if ( 'gmm_student_bookings' === $tag ) {
			return array_merge( is_array( $args ) ? $args : array(), self::get_history_template_vars() );
		}
		return $args;
	}

	/**
	 * Extra guest redirect for booking form (student role required).
	 *
	 * @return void
	 */
	public function maybe_redirect_guests() {
		if ( is_admin() || wp_doing_ajax() || ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( ! ( $post instanceof WP_Post ) ) {
			return;
		}

		$content = (string) $post->post_content;
		$needed  = has_shortcode( $content, 'gmm_booking_form' )
			|| false !== strpos( $content, '[gmm_booking_form' );

		if ( ! $needed ) {
			return;
		}

		if ( self::user_can_book() ) {
			return;
		}

		$login = class_exists( 'GMM_Pages' ) ? GMM_Pages::get_page_url( 'student_login' ) : '';
		$login = $login ? $login : wp_login_url( get_permalink( $post ) );
		$login = add_query_arg(
			array(
				'redirect_to' => rawurlencode( get_permalink( $post ) ),
			),
			$login
		);
		wp_safe_redirect( $login );
		exit;
	}

	/**
	 * Whether current user may book lessons.
	 *
	 * @param int $user_id Optional user.
	 * @return bool
	 */
	public static function user_can_book( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id || ! is_user_logged_in() ) {
			return false;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		if ( ! function_exists( 'gmm_is_student' ) || ! gmm_is_student( $user_id ) ) {
			return false;
		}
		if ( function_exists( 'gmm_student_can_access_dashboard' ) ) {
			return (bool) gmm_student_can_access_dashboard( $user_id );
		}
		return true;
	}

	/**
	 * Booking form template variables.
	 *
	 * @param array<string, mixed> $atts Shortcode / inject args.
	 * @return array<string, mixed>
	 */
	public static function get_form_template_vars( $atts = array() ) {
		$teacher_id = self::resolve_teacher_id( $atts );
		$class_id   = self::resolve_class_id( $atts );
		$pre_date   = self::resolve_date( $atts );
		$pre_time   = self::resolve_time( $atts );

		$teacher  = $teacher_id ? self::get_teacher_card( $teacher_id ) : null;
		$classes  = $teacher_id ? self::get_teacher_classes( $teacher_id ) : array();
		$teachers = self::list_bookable_teachers( array( 'limit' => 50 ) );

		if ( ! $teacher && ! empty( $teachers ) && ! $teacher_id ) {
			// No preselect — leave teacher empty so UI can pick via AJAX.
			$teacher = null;
		}

		$selected_class = null;
		if ( $class_id ) {
			foreach ( $classes as $c ) {
				if ( (int) $c['id'] === $class_id ) {
					$selected_class = $c;
					break;
				}
			}
		}
		if ( ! $selected_class && ! empty( $classes ) ) {
			$selected_class = $classes[0];
			$class_id       = (int) $selected_class['id'];
		}

		$confirmed = self::resolve_confirmed_booking();

		$profile_url = '';
		if ( $teacher_id && function_exists( 'gmm_get_page_link' ) ) {
			$profile_url = add_query_arg( 'teacher_id', $teacher_id, gmm_get_page_link( 'teacher_public_profile' ) );
		}

		$duration = $selected_class ? (int) $selected_class['duration'] : 60;
		$price    = $selected_class ? (float) $selected_class['price'] : 0.0;

		return array(
			'booking_teacher'     => $teacher ? $teacher : array(),
			'booking_teachers'    => $teachers,
			'booking_classes'     => $classes,
			'selected_teacher_id' => $teacher_id,
			'selected_class_id'   => $class_id,
			'selected_class'      => $selected_class ? $selected_class : array(),
			'prefill_date'        => $pre_date,
			'prefill_time'        => $pre_time,
			'default_duration'    => $duration,
			'default_price'       => $price,
			'teacher_profile_url' => $profile_url,
			'teachers_url'        => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teachers' ) : '',
			'bookings_url'        => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'student_bookings' ) : '',
			'payments_url'        => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'student_payments' ) : '',
			'confirmed_booking'   => $confirmed,
			'booking_nonce'       => wp_create_nonce( self::NONCE_ACTION ),
			'booking_engine'      => true,
		);
	}

	/**
	 * Student booking history template vars.
	 *
	 * @param int $user_id Optional.
	 * @return array<string, mixed>
	 */
	public static function get_history_template_vars( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! self::user_can_book( $user_id ) ) {
			return array(
				'booking_rows'  => array(),
				'booking_stats' => self::empty_history_stats(),
				'bookings_url'  => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'student_bookings' ) : '',
				'teachers_url'  => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teachers' ) : '',
				'booking_nonce' => wp_create_nonce( self::NONCE_ACTION ),
			);
		}

		$rows  = self::get_student_booking_rows( $user_id );
		$stats = self::build_history_stats( $rows );

		$student = class_exists( 'GMM_Student' ) ? GMM_Student::get_profile( $user_id ) : null;
		$name    = '';
		$first   = '';
		$avatar  = function_exists( 'gmm_design_asset_url' ) ? gmm_design_asset_url( 'assets/img/team/02.jpg' ) : '';

		if ( is_array( $student ) ) {
			$first = isset( $student['first_name'] ) ? (string) $student['first_name'] : '';
			$last  = isset( $student['last_name'] ) ? (string) $student['last_name'] : '';
			$name  = trim( $first . ' ' . $last );
			if ( ! empty( $student['profile_image'] ) && function_exists( 'gmm_get_media_url' ) ) {
				$img = gmm_get_media_url( $student['profile_image'], 'thumbnail' );
				if ( $img ) {
					$avatar = $img;
				}
			}
		}
		if ( '' === $name ) {
			$user = get_userdata( $user_id );
			$name = $user ? $user->display_name : __( 'Student', 'gospel-music-mastery' );
			$first = $name;
		}

		return array(
			'user_name'       => $name,
			'user_first_name' => $first ? $first : $name,
			'user_avatar'     => $avatar,
			'booking_rows'    => $rows,
			'bookings'        => $rows,
			'booking_stats'   => $stats,
			'teachers_url'    => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teachers' ) : '',
			'booking_form_url'=> function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'booking_form' ) : '',
			'lessons_url'     => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'student_lessons' ) : '',
			'booking_nonce'   => wp_create_nonce( self::NONCE_ACTION ),
		);
	}

	/**
	 * Create a student booking (amount from class; payment pending).
	 *
	 * @param array<string, mixed> $data  Booking fields.
	 * @param string               $nonce Optional flow nonce.
	 * @return int|WP_Error Booking ID.
	 */
	public static function create_student_booking( $data, $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		if ( ! self::user_can_book() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Only logged-in students can book lessons.', 'gospel-music-mastery' ) );
		}

		$data = is_array( $data ) ? $data : array();

		$teacher_id = isset( $data['teacher_id'] ) ? absint( $data['teacher_id'] ) : 0;
		$class_id   = isset( $data['class_id'] ) ? absint( $data['class_id'] ) : 0;

		if ( ! $teacher_id || ! self::teacher_is_bookable( $teacher_id ) ) {
			return new WP_Error( 'gmm_teacher_missing', __( 'Teacher not found or unavailable.', 'gospel-music-mastery' ) );
		}

		$class = self::get_class_for_teacher( $class_id, $teacher_id );
		if ( ! $class ) {
			return new WP_Error( 'gmm_class_missing', __( 'Class not found for this teacher.', 'gospel-music-mastery' ) );
		}

		$duration = isset( $data['duration'] ) ? absint( $data['duration'] ) : 0;
		if ( $duration < 1 ) {
			$duration = isset( $class['duration'] ) ? absint( $class['duration'] ) : 60;
		}
		if ( $duration < 1 ) {
			$duration = 60;
		}

		// Never trust client-submitted price.
		$amount = isset( $class['price'] ) ? round( max( 0, (float) $class['price'] ), 2 ) : 0.0;
		if ( $amount <= 0 ) {
			return new WP_Error( 'gmm_amount', __( 'Invalid class price.', 'gospel-music-mastery' ) );
		}

		$payload = array(
			'teacher_id'     => $teacher_id,
			'class_id'       => $class_id,
			'booking_date'   => isset( $data['booking_date'] ) ? $data['booking_date'] : ( isset( $data['date'] ) ? $data['date'] : '' ),
			'booking_time'   => isset( $data['booking_time'] ) ? $data['booking_time'] : ( isset( $data['time'] ) ? $data['time'] : '' ),
			'duration'       => $duration,
			'amount'         => $amount,
			'notes'          => isset( $data['notes'] ) ? $data['notes'] : '',
			'payment_status' => 'pending',
		);

		if ( ! class_exists( 'GMM_Booking' ) ) {
			return new WP_Error( 'gmm_missing', __( 'Booking engine unavailable.', 'gospel-music-mastery' ) );
		}

		$result = GMM_Booking::create( $payload, '' );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		/**
		 * Payment pipeline prep: booking created → payment pending → gateway later → confirmed.
		 *
		 * @since 1.0.0
		 * @param int                  $booking_id Booking ID.
		 * @param array<string, mixed> $payload    Create payload.
		 */
		do_action( 'gmm_booking_payment_pending', (int) $result, $payload );

		return (int) $result;
	}

	/**
	 * Cancel own pending booking; prepare refund support.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param int    $user_id    WP user.
	 * @param string $nonce      Optional nonce.
	 * @return true|WP_Error
	 */
	public static function cancel_student_booking( $booking_id, $user_id = 0, $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		$user_id    = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$booking_id = absint( $booking_id );

		if ( ! self::user_can_book( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot cancel this booking.', 'gospel-music-mastery' ) );
		}

		if ( ! $booking_id || ! class_exists( 'GMM_Booking' ) ) {
			return new WP_Error( 'gmm_not_found', __( 'Booking not found.', 'gospel-music-mastery' ) );
		}

		$details = GMM_Booking::student_view_booking( $booking_id, $user_id );
		if ( ! is_array( $details ) || empty( $details['booking']['id'] ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only cancel your own bookings.', 'gospel-music-mastery' ) );
		}

		$status = isset( $details['booking']['booking_status'] ) ? sanitize_key( (string) $details['booking']['booking_status'] ) : '';
		if ( 'pending' !== $status ) {
			return new WP_Error( 'gmm_not_cancellable', __( 'Only pending bookings can be cancelled.', 'gospel-music-mastery' ) );
		}

		$pay = isset( $details['booking']['payment_status'] ) ? sanitize_key( (string) $details['booking']['payment_status'] ) : 'pending';

		$result = GMM_Booking::student_cancel_booking( $booking_id, $user_id, '' );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		/**
		 * Prepare refund support when a paid/pending-payment booking is cancelled.
		 *
		 * @since 1.0.0
		 * @param int    $booking_id Booking ID.
		 * @param string $pay_status Prior payment status.
		 * @param int    $user_id    Student WP user.
		 */
		do_action( 'gmm_booking_refund_prepare', $booking_id, $pay, $user_id );

		return true;
	}

	/**
	 * Available calendar dates for a teacher in a month.
	 *
	 * @param int $teacher_id Teacher ID.
	 * @param int $year       Year.
	 * @param int $month      Month 1-12.
	 * @param int $duration   Lesson minutes.
	 * @return array<int, string> Y-m-d list.
	 */
	public static function get_available_dates( $teacher_id, $year, $month, $duration = 60 ) {
		$teacher_id = absint( $teacher_id );
		$year       = absint( $year );
		$month      = absint( $month );
		$duration   = max( 15, absint( $duration ) );

		if ( ! $teacher_id || $year < 2000 || $month < 1 || $month > 12 ) {
			return array();
		}

		$start = sprintf( '%04d-%02d-01', $year, $month );
		$end_ts = strtotime( $start . ' 12:00:00' );
		$end    = $end_ts ? wp_date( 'Y-m-t', $end_ts ) : $start;
		$today  = current_time( 'Y-m-d' );

		global $wpdb;
		$avail = GMM_Database::table( 'availability' );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT available_date, start_time, end_time, status
				FROM {$avail}
				WHERE teacher_id = %d
				AND available_date >= %s
				AND available_date <= %s
				AND available_date >= %s
				AND status IN ('available','open','bookable','active')
				ORDER BY available_date ASC, start_time ASC",
				$teacher_id,
				$start,
				$end,
				$today
			),
			ARRAY_A
		);
		$rows = is_array( $rows ) ? $rows : array();

		$dates = array();
		foreach ( $rows as $row ) {
			$date = isset( $row['available_date'] ) ? (string) $row['available_date'] : '';
			if ( ! $date || isset( $dates[ $date ] ) ) {
				continue;
			}
			$slots = self::get_available_slots( $teacher_id, $date, $duration );
			if ( ! empty( $slots ) ) {
				$dates[ $date ] = $date;
			}
		}

		return array_values( $dates );
	}

	/**
	 * Bookable time slots for a teacher on a date.
	 *
	 * @param int    $teacher_id Teacher ID.
	 * @param string $date       Y-m-d.
	 * @param int    $duration   Minutes.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_available_slots( $teacher_id, $date, $duration = 60 ) {
		$teacher_id = absint( $teacher_id );
		$date       = self::sanitize_date( $date );
		$duration   = max( 15, absint( $duration ) );

		if ( ! $teacher_id || ! $date ) {
			return array();
		}

		$today = current_time( 'Y-m-d' );
		if ( $date < $today ) {
			return array();
		}

		global $wpdb;
		$avail = GMM_Database::table( 'availability' );

		$windows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT id, start_time, end_time, status
				FROM {$avail}
				WHERE teacher_id = %d
				AND available_date = %s
				AND status IN ('available','open','bookable','active')
				ORDER BY start_time ASC",
				$teacher_id,
				$date
			),
			ARRAY_A
		);
		$windows = is_array( $windows ) ? $windows : array();
		if ( empty( $windows ) ) {
			return array();
		}

		$now_ts = ( $date === $today ) ? (int) current_time( 'timestamp' ) : 0;
		$out    = array();
		$seen   = array();

		foreach ( $windows as $window ) {
			$start = isset( $window['start_time'] ) ? (string) $window['start_time'] : '';
			$end   = isset( $window['end_time'] ) ? (string) $window['end_time'] : '';
			if ( ! $start || ! $end ) {
				continue;
			}

			$cursor = strtotime( $date . ' ' . $start );
			$end_ts = strtotime( $date . ' ' . $end );
			if ( ! $cursor || ! $end_ts ) {
				continue;
			}

			$step = $duration * MINUTE_IN_SECONDS;
			while ( ( $cursor + $step ) <= $end_ts ) {
				if ( $now_ts && $cursor <= $now_ts ) {
					$cursor += $step;
					continue;
				}

				$time = wp_date( 'H:i:s', $cursor );
				if ( isset( $seen[ $time ] ) ) {
					$cursor += $step;
					continue;
				}

				$check = class_exists( 'GMM_Booking' )
					? GMM_Booking::check_teacher_availability( $teacher_id, $date, $time, $duration )
					: new WP_Error( 'gmm_missing', 'missing' );

				if ( ! is_wp_error( $check ) && true === $check ) {
					$label = date_i18n( 'g:i A', $cursor );
					$out[] = array(
						'time'       => $time,
						'time_label' => $label,
						'display'    => $label,
					);
					$seen[ $time ] = true;
				}

				$cursor += $step;
			}
		}

		return $out;
	}

	/**
	 * AJAX: bookable teachers list.
	 *
	 * @return void
	 */
	public function ajax_load_teachers() {
		$this->guard_ajax();

		$search = isset( $_POST['search'] ) ? sanitize_text_field( wp_unslash( $_POST['search'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$list   = self::list_bookable_teachers(
			array(
				'search' => $search,
				'limit'  => 40,
			)
		);

		wp_send_json_success(
			array(
				'teachers' => $list,
			)
		);
	}

	/**
	 * AJAX: classes for teacher.
	 *
	 * @return void
	 */
	public function ajax_load_classes() {
		$this->guard_ajax();

		$teacher_id = isset( $_POST['teacher_id'] ) ? absint( wp_unslash( $_POST['teacher_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( ! $teacher_id || ! self::teacher_is_bookable( $teacher_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Teacher not available.', 'gospel-music-mastery' ) ), 400 );
		}

		$classes = self::get_teacher_classes( $teacher_id );
		$teacher = self::get_teacher_card( $teacher_id );

		wp_send_json_success(
			array(
				'teacher'  => $teacher ? $teacher : array(),
				'classes'  => $classes,
			)
		);
	}

	/**
	 * AJAX: dates + slots.
	 *
	 * @return void
	 */
	public function ajax_load_slots() {
		$this->guard_ajax();

		$teacher_id = isset( $_POST['teacher_id'] ) ? absint( wp_unslash( $_POST['teacher_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$date       = isset( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$year       = isset( $_POST['year'] ) ? absint( wp_unslash( $_POST['year'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$month      = isset( $_POST['month'] ) ? absint( wp_unslash( $_POST['month'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$duration   = isset( $_POST['duration'] ) ? absint( wp_unslash( $_POST['duration'] ) ) : 60; // phpcs:ignore WordPress.Security.NonceVerification.Missing

		if ( ! $teacher_id || ! self::teacher_is_bookable( $teacher_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Teacher not available.', 'gospel-music-mastery' ) ), 400 );
		}

		if ( $duration < 1 ) {
			$duration = 60;
		}

		$response = array(
			'dates' => array(),
			'slots' => array(),
		);

		if ( $year && $month ) {
			$response['dates'] = self::get_available_dates( $teacher_id, $year, $month, $duration );
		}

		$date = self::sanitize_date( $date );
		if ( $date ) {
			$response['slots'] = self::get_available_slots( $teacher_id, $date, $duration );
			$response['date']  = $date;
		}

		wp_send_json_success( $response );
	}

	/**
	 * AJAX: create booking.
	 *
	 * @return void
	 */
	public function ajax_create_booking() {
		$this->guard_ajax();

		$data = array(
			'teacher_id'   => isset( $_POST['teacher_id'] ) ? absint( wp_unslash( $_POST['teacher_id'] ) ) : 0, // phpcs:ignore WordPress.Security.NonceVerification.Missing
			'class_id'     => isset( $_POST['class_id'] ) ? absint( wp_unslash( $_POST['class_id'] ) ) : 0, // phpcs:ignore WordPress.Security.NonceVerification.Missing
			'booking_date' => isset( $_POST['booking_date'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_date'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
			'booking_time' => isset( $_POST['booking_time'] ) ? sanitize_text_field( wp_unslash( $_POST['booking_time'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
			'duration'     => isset( $_POST['duration'] ) ? absint( wp_unslash( $_POST['duration'] ) ) : 0, // phpcs:ignore WordPress.Security.NonceVerification.Missing
			'notes'        => isset( $_POST['notes'] ) ? sanitize_textarea_field( wp_unslash( $_POST['notes'] ) ) : '', // phpcs:ignore WordPress.Security.NonceVerification.Missing
		);

		$result = self::create_student_booking( $data, '' );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		$details = class_exists( 'GMM_Booking' )
			? GMM_Booking::student_view_booking( (int) $result, get_current_user_id() )
			: null;

		$summary = self::format_confirmation( $details, (int) $result );
		$pay_url = function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'student_payments' ) : '';
		$hist    = function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'student_bookings' ) : '';

		wp_send_json_success(
			array(
				'message'         => __( 'Booking created. Payment is pending.', 'gospel-music-mastery' ),
				'booking_id'      => (int) $result,
				'booking'         => $summary,
				'payment_status'  => 'pending',
				'booking_status'  => 'pending',
				'payment_url'     => $pay_url,
				'history_url'     => $hist,
				'redirect'        => $hist ? add_query_arg( 'booking_id', (int) $result, $hist ) : '',
			)
		);
	}

	/**
	 * AJAX: cancel pending booking.
	 *
	 * @return void
	 */
	public function ajax_cancel_booking() {
		$this->guard_ajax();

		$booking_id = isset( $_POST['booking_id'] ) ? absint( wp_unslash( $_POST['booking_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$result     = self::cancel_student_booking( $booking_id, get_current_user_id(), '' );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		wp_send_json_success(
			array(
				'message'    => __( 'Booking cancelled.', 'gospel-music-mastery' ),
				'booking_id' => $booking_id,
				'status'     => 'cancelled',
			)
		);
	}

	/**
	 * AJAX: refresh history rows.
	 *
	 * @return void
	 */
	public function ajax_history() {
		$this->guard_ajax();

		$vars = self::get_history_template_vars();
		wp_send_json_success(
			array(
				'rows'  => isset( $vars['booking_rows'] ) ? $vars['booking_rows'] : array(),
				'stats' => isset( $vars['booking_stats'] ) ? $vars['booking_stats'] : self::empty_history_stats(),
			)
		);
	}

	/**
	 * Enqueue booking flow scripts.
	 *
	 * @return void
	 */
	public function maybe_enqueue_assets() {
		if ( ! class_exists( 'GMM_Assets' ) || ! GMM_Assets::is_gmm_page() ) {
			return;
		}

		$post    = get_queried_object();
		$content = ( $post instanceof WP_Post ) ? (string) $post->post_content : '';
		$need_form = has_shortcode( $content, 'gmm_booking_form' )
			|| false !== strpos( $content, 'gmm_booking_form' );
		$need_hist = has_shortcode( $content, 'gmm_student_bookings' )
			|| false !== strpos( $content, 'gmm_student_bookings' );

		if ( ! $need_form && ! $need_hist ) {
			return;
		}

		if ( ! self::user_can_book() ) {
			return;
		}

		$version = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';
		$deps    = array( 'gmm-core-script' );

		if ( $need_form ) {
			wp_enqueue_script(
				'gmm-booking-flow',
				GMM_URL . 'assets/js/gmm-booking-flow.js',
				$deps,
				$version,
				true
			);

			$vars = self::get_form_template_vars();
			wp_localize_script(
				'gmm-booking-flow',
				'GMM_BOOKING_FLOW',
				array(
					'nonce'      => wp_create_nonce( self::NONCE_ACTION ),
					'nonceField' => self::NONCE_FIELD,
					'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
					'teacherId'  => isset( $vars['selected_teacher_id'] ) ? (int) $vars['selected_teacher_id'] : 0,
					'classId'    => isset( $vars['selected_class_id'] ) ? (int) $vars['selected_class_id'] : 0,
					'prefillDate'=> isset( $vars['prefill_date'] ) ? (string) $vars['prefill_date'] : '',
					'prefillTime'=> isset( $vars['prefill_time'] ) ? (string) $vars['prefill_time'] : '',
					'duration'   => isset( $vars['default_duration'] ) ? (int) $vars['default_duration'] : 60,
					'actions'    => array(
						'teachers' => 'gmm_booking_flow_teachers',
						'classes'  => 'gmm_booking_flow_classes',
						'slots'    => 'gmm_booking_flow_slots',
						'create'   => 'gmm_booking_flow_create',
						'cancel'   => 'gmm_booking_flow_cancel',
					),
					'urls'       => array(
						'teachers' => isset( $vars['teachers_url'] ) ? (string) $vars['teachers_url'] : '',
						'bookings' => isset( $vars['bookings_url'] ) ? (string) $vars['bookings_url'] : '',
						'payments' => isset( $vars['payments_url'] ) ? (string) $vars['payments_url'] : '',
						'profile'  => isset( $vars['teacher_profile_url'] ) ? (string) $vars['teacher_profile_url'] : '',
					),
					'i18n'       => array(
						'selectClass' => __( 'Please select a class, date, and time.', 'gospel-music-mastery' ),
						'noSlots'     => __( 'No time slots available for this date.', 'gospel-music-mastery' ),
						'created'     => __( 'Booking created. Payment is pending.', 'gospel-music-mastery' ),
						'error'       => __( 'Something went wrong. Please try again.', 'gospel-music-mastery' ),
						'loading'     => __( 'Loading…', 'gospel-music-mastery' ),
					),
				)
			);
		}

		if ( $need_hist ) {
			wp_enqueue_script(
				'gmm-student-bookings',
				GMM_URL . 'assets/js/gmm-student-bookings.js',
				$deps,
				$version,
				true
			);

			$hvars = self::get_history_template_vars();
			wp_localize_script(
				'gmm-student-bookings',
				'GMM_STUDENT_BOOKINGS',
				array(
					'nonce'      => wp_create_nonce( self::NONCE_ACTION ),
					'nonceField' => self::NONCE_FIELD,
					'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
					'actions'    => array(
						'cancel'  => 'gmm_booking_flow_cancel',
						'history' => 'gmm_booking_flow_history',
					),
					'urls'       => array(
						'teachers' => isset( $hvars['teachers_url'] ) ? (string) $hvars['teachers_url'] : '',
						'booking'  => isset( $hvars['booking_form_url'] ) ? (string) $hvars['booking_form_url'] : '',
						'lessons'  => isset( $hvars['lessons_url'] ) ? (string) $hvars['lessons_url'] : '',
					),
					'i18n'       => array(
						'cancelled' => __( 'Booking cancelled.', 'gospel-music-mastery' ),
						'confirm'   => __( 'Cancel this pending booking request?', 'gospel-music-mastery' ),
						'error'     => __( 'Something went wrong. Please try again.', 'gospel-music-mastery' ),
					),
				)
			);
		}
	}

	/**
	 * Verify flow nonce.
	 *
	 * @param string $nonce Nonce.
	 * @return bool
	 */
	public static function verify_nonce( $nonce ) {
		return (bool) wp_verify_nonce( (string) $nonce, self::NONCE_ACTION );
	}

	/* -------------------------------------------------------------------------
	 * Internals
	 * ---------------------------------------------------------------------- */

	/**
	 * @return void
	 */
	private function guard_ajax() {
		$nonce = '';
		if ( isset( $_POST[ self::NONCE_FIELD ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$nonce = sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		} elseif ( isset( $_POST['nonce'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			$nonce = sanitize_text_field( wp_unslash( $_POST['nonce'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		}

		if ( ! self::verify_nonce( $nonce ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'gospel-music-mastery' ) ), 403 );
		}

		if ( ! self::user_can_book() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in as a student.', 'gospel-music-mastery' ) ), 403 );
		}
	}

	/**
	 * @param array<string, mixed> $atts Args.
	 * @return int
	 */
	private static function resolve_teacher_id( $atts ) {
		$atts = is_array( $atts ) ? $atts : array();
		if ( ! empty( $atts['atts'] ) && is_array( $atts['atts'] ) ) {
			$atts = array_merge( $atts, $atts['atts'] );
		}
		$id = ! empty( $atts['teacher_id'] ) ? absint( $atts['teacher_id'] ) : 0;
		if ( ! $id && ! empty( $_GET['teacher_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$id = absint( wp_unslash( $_GET['teacher_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( ! $id && ! empty( $_GET['teacher'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$id = absint( wp_unslash( $_GET['teacher'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		return $id;
	}

	/**
	 * @param array<string, mixed> $atts Args.
	 * @return int
	 */
	private static function resolve_class_id( $atts ) {
		$atts = is_array( $atts ) ? $atts : array();
		if ( ! empty( $atts['atts'] ) && is_array( $atts['atts'] ) ) {
			$atts = array_merge( $atts, $atts['atts'] );
		}
		$id = ! empty( $atts['class_id'] ) ? absint( $atts['class_id'] ) : 0;
		if ( ! $id && ! empty( $_GET['class_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$id = absint( wp_unslash( $_GET['class_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		return $id;
	}

	/**
	 * @param array<string, mixed> $atts Args.
	 * @return string
	 */
	private static function resolve_date( $atts ) {
		$atts = is_array( $atts ) ? $atts : array();
		$raw  = ! empty( $atts['date'] ) ? (string) $atts['date'] : '';
		if ( ! $raw && ! empty( $_GET['date'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$raw = sanitize_text_field( wp_unslash( $_GET['date'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		return self::sanitize_date( $raw );
	}

	/**
	 * @param array<string, mixed> $atts Args.
	 * @return string
	 */
	private static function resolve_time( $atts ) {
		$atts = is_array( $atts ) ? $atts : array();
		$raw  = ! empty( $atts['time'] ) ? (string) $atts['time'] : '';
		if ( ! $raw && ! empty( $_GET['time'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$raw = sanitize_text_field( wp_unslash( $_GET['time'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		return sanitize_text_field( $raw );
	}

	/**
	 * @return array<string, mixed>|null
	 */
	private static function resolve_confirmed_booking() {
		$id = 0;
		if ( ! empty( $_GET['booking_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$id = absint( wp_unslash( $_GET['booking_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( ! $id || ! class_exists( 'GMM_Booking' ) ) {
			return null;
		}
		$details = GMM_Booking::student_view_booking( $id, get_current_user_id() );
		return self::format_confirmation( $details, $id );
	}

	/**
	 * @param array<string, mixed>|null $details Details.
	 * @param int                       $id      Booking ID.
	 * @return array<string, mixed>|null
	 */
	private static function format_confirmation( $details, $id ) {
		if ( ! is_array( $details ) || empty( $details['booking'] ) ) {
			return null;
		}
		$b = $details['booking'];
		$t = isset( $details['teacher'] ) ? $details['teacher'] : array();
		$c = isset( $details['class'] ) ? $details['class'] : array();

		$teacher_name = trim(
			( isset( $t['first_name'] ) ? (string) $t['first_name'] : '' ) . ' ' .
			( isset( $t['last_name'] ) ? (string) $t['last_name'] : '' )
		);
		$date = isset( $b['booking_date'] ) ? (string) $b['booking_date'] : '';
		$time = isset( $b['booking_time'] ) ? (string) $b['booking_time'] : '';

		return array(
			'id'             => $id,
			'teacher'        => $teacher_name ? $teacher_name : __( 'Teacher', 'gospel-music-mastery' ),
			'class'          => isset( $c['title'] ) ? (string) $c['title'] : __( 'Lesson', 'gospel-music-mastery' ),
			'date'           => $date,
			'date_label'     => $date ? mysql2date( 'l, F j, Y', $date . ' 00:00:00' ) : '',
			'time'           => $time,
			'time_label'     => $time ? date_i18n( 'g:i A', strtotime( $time ) ) : '',
			'status'         => isset( $b['booking_status'] ) ? (string) $b['booking_status'] : 'pending',
			'payment_status' => isset( $b['payment_status'] ) ? (string) $b['payment_status'] : 'pending',
			'duration'       => isset( $b['duration'] ) ? (int) $b['duration'] : 0,
			'amount'         => isset( $b['amount'] ) ? (float) $b['amount'] : 0.0,
		);
	}

	/**
	 * @param int $teacher_id Teacher ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_teacher_card( $teacher_id ) {
		$teacher_id = absint( $teacher_id );
		if ( ! $teacher_id || ! self::teacher_is_bookable( $teacher_id ) ) {
			return null;
		}

		global $wpdb;
		$table = GMM_Database::table( 'teachers' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $teacher_id ),
			ARRAY_A
		);
		if ( ! is_array( $row ) ) {
			return null;
		}

		$name = trim(
			( isset( $row['first_name'] ) ? (string) $row['first_name'] : '' ) . ' ' .
			( isset( $row['last_name'] ) ? (string) $row['last_name'] : '' )
		);
		$image = '';
		if ( ! empty( $row['profile_image'] ) && function_exists( 'gmm_get_media_url' ) ) {
			$image = gmm_get_media_url( $row['profile_image'], 'thumbnail' );
		}
		if ( ! $image && function_exists( 'gmm_design_asset_url' ) ) {
			$image = gmm_design_asset_url( 'assets/img/team/01.jpg' );
		}

		$role = '';
		if ( ! empty( $row['specialization'] ) ) {
			$role = (string) $row['specialization'];
		} elseif ( ! empty( $row['instruments'] ) ) {
			$role = (string) $row['instruments'];
		} else {
			$role = __( 'Gospel Music Instructor', 'gospel-music-mastery' );
		}

		$profile_url = '';
		if ( function_exists( 'gmm_get_page_link' ) ) {
			$profile_url = add_query_arg( 'teacher_id', $teacher_id, gmm_get_page_link( 'teacher_public_profile' ) );
		}

		return array(
			'id'          => $teacher_id,
			'name'        => $name ? $name : __( 'Teacher', 'gospel-music-mastery' ),
			'role'        => $role,
			'image_url'   => $image,
			'profile_url' => $profile_url,
		);
	}

	/**
	 * @param int $teacher_id Teacher ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_teacher_classes( $teacher_id ) {
		if ( class_exists( 'GMM_Teacher_Profile_Public' ) ) {
			return GMM_Teacher_Profile_Public::get_classes( $teacher_id );
		}

		$teacher_id = absint( $teacher_id );
		if ( ! $teacher_id ) {
			return array();
		}

		global $wpdb;
		$table = GMM_Database::table( 'classes' );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE teacher_id = %d AND status IN ('approved','published','active')
				ORDER BY featured DESC, id DESC LIMIT 40",
				$teacher_id
			),
			ARRAY_A
		);
		$rows = is_array( $rows ) ? $rows : array();
		$out  = array();

		foreach ( $rows as $row ) {
			$duration = isset( $row['duration'] ) ? absint( $row['duration'] ) : 60;
			$price    = isset( $row['price'] ) ? (float) $row['price'] : 0.0;
			$title    = isset( $row['title'] ) ? (string) $row['title'] : '';
			$out[]    = array(
				'id'             => absint( $row['id'] ),
				'title'          => $title,
				'difficulty'     => isset( $row['difficulty'] ) ? (string) $row['difficulty'] : '',
				'duration'       => $duration,
				'duration_label' => $duration ? sprintf( '%d Minutes', $duration ) : '',
				'price'          => $price,
				'price_display'  => '$' . number_format_i18n( $price, 0 ),
				'short'          => $title,
			);
		}

		return $out;
	}

	/**
	 * @param array<string, mixed> $args Args.
	 * @return array<int, array<string, mixed>>
	 */
	public static function list_bookable_teachers( $args = array() ) {
		$args = wp_parse_args(
			$args,
			array(
				'search' => '',
				'limit'  => 40,
			)
		);

		$search_args = array(
			'search'   => $args['search'],
			'public'   => true,
			'statuses' => array( 'approved', 'active' ),
			'per_page' => min( 50, max( 1, absint( $args['limit'] ) ) ),
			'page'     => 1,
			'sort'     => 'newest',
		);

		$result = class_exists( 'GMM_Search' ) ? GMM_Search::search_teachers( $search_args ) : array( 'items' => array() );
		$items  = isset( $result['items'] ) && is_array( $result['items'] ) ? $result['items'] : array();
		$out    = array();

		foreach ( $items as $row ) {
			$id = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
			if ( ! $id ) {
				continue;
			}
			$card = self::get_teacher_card( $id );
			if ( $card ) {
				$out[] = $card;
			}
		}

		return $out;
	}

	/**
	 * @param int $teacher_id Teacher ID.
	 * @return bool
	 */
	public static function teacher_is_bookable( $teacher_id ) {
		$teacher_id = absint( $teacher_id );
		if ( ! $teacher_id ) {
			return false;
		}
		if ( class_exists( 'GMM_Admin_Teachers' ) && method_exists( 'GMM_Admin_Teachers', 'can_receive_bookings' ) ) {
			return (bool) GMM_Admin_Teachers::can_receive_bookings( $teacher_id );
		}

		global $wpdb;
		$table  = GMM_Database::table( 'teachers' );
		$status = $wpdb->get_var(
			$wpdb->prepare( "SELECT status FROM {$table} WHERE id = %d LIMIT 1", $teacher_id )
		);
		return in_array( (string) $status, array( 'approved', 'active' ), true );
	}

	/**
	 * @param int $class_id   Class ID.
	 * @param int $teacher_id Teacher ID.
	 * @return array<string, mixed>|null
	 */
	private static function get_class_for_teacher( $class_id, $teacher_id ) {
		$class_id   = absint( $class_id );
		$teacher_id = absint( $teacher_id );
		if ( ! $class_id || ! $teacher_id ) {
			return null;
		}

		global $wpdb;
		$table = GMM_Database::table( 'classes' );
		$row   = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE id = %d AND teacher_id = %d
				AND status IN ('approved','published','active')
				LIMIT 1",
				$class_id,
				$teacher_id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * @param int $user_id User ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_student_booking_rows( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id || ! class_exists( 'GMM_Student' ) ) {
			return array();
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return array();
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$teachers = GMM_Database::table( 'teachers' );
		$classes  = GMM_Database::table( 'classes' );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT b.*,
					t.first_name AS teacher_first_name,
					t.last_name AS teacher_last_name,
					t.profile_image AS teacher_image,
					c.title AS class_title
				FROM {$bookings} b
				LEFT JOIN {$teachers} t ON t.id = b.teacher_id
				LEFT JOIN {$classes} c ON c.id = b.class_id
				WHERE b.student_id = %d
				ORDER BY b.booking_date DESC, b.booking_time DESC
				LIMIT 100",
				$student_id
			),
			ARRAY_A
		);
		$rows = is_array( $rows ) ? $rows : array();

		$out = array();
		foreach ( $rows as $row ) {
			$formatted = self::format_history_row( $row );
			if ( $formatted ) {
				$out[] = $formatted;
			}
		}
		return $out;
	}

	/**
	 * @param array<string, mixed> $row DB row.
	 * @return array<string, mixed>|null
	 */
	public static function format_history_row( $row ) {
		if ( ! is_array( $row ) || empty( $row['id'] ) ) {
			return null;
		}

		$status   = isset( $row['booking_status'] ) ? sanitize_key( (string) $row['booking_status'] ) : 'pending';
		$duration = isset( $row['duration'] ) ? absint( $row['duration'] ) : 0;
		$amount   = isset( $row['amount'] ) ? (float) $row['amount'] : 0.0;
		$teacher  = trim(
			( isset( $row['teacher_first_name'] ) ? (string) $row['teacher_first_name'] : '' ) . ' ' .
			( isset( $row['teacher_last_name'] ) ? (string) $row['teacher_last_name'] : '' )
		);
		if ( '' === $teacher ) {
			$teacher = __( 'Teacher', 'gospel-music-mastery' );
		}

		$image = '';
		if ( ! empty( $row['teacher_image'] ) && function_exists( 'gmm_get_media_url' ) ) {
			$image = gmm_get_media_url( $row['teacher_image'], 'thumbnail' );
		}
		if ( ! $image && function_exists( 'gmm_design_asset_url' ) ) {
			$image = gmm_design_asset_url( 'assets/img/team/01.jpg' );
		}

		$date = isset( $row['booking_date'] ) ? (string) $row['booking_date'] : '';
		$time = isset( $row['booking_time'] ) ? (string) $row['booking_time'] : '';
		$today = current_time( 'Y-m-d' );

		$group = $status;
		if ( in_array( $status, array( 'pending', 'confirmed' ), true ) && $date >= $today ) {
			$group = 'upcoming';
		}
		if ( 'completed' === $status ) {
			$group = 'completed';
		}
		if ( in_array( $status, array( 'cancelled', 'refunded' ), true ) ) {
			$group = 'cancelled';
		}

		$book_again = function_exists( 'gmm_get_page_link' )
			? add_query_arg(
				array(
					'teacher_id' => isset( $row['teacher_id'] ) ? absint( $row['teacher_id'] ) : 0,
					'class_id'   => isset( $row['class_id'] ) ? absint( $row['class_id'] ) : 0,
				),
				gmm_get_page_link( 'booking_form' )
			)
			: '';

		return array(
			'id'             => (int) $row['id'],
			'teacher_id'     => isset( $row['teacher_id'] ) ? (int) $row['teacher_id'] : 0,
			'class_id'       => isset( $row['class_id'] ) ? (int) $row['class_id'] : 0,
			'status'         => $status,
			'group'          => $group,
			'status_label'   => self::status_label( $status ),
			'badge_class'    => 'is-' . $status,
			'teacher_name'   => $teacher,
			'teacher_image'  => $image,
			'class_name'     => ! empty( $row['class_title'] ) ? (string) $row['class_title'] : __( 'Lesson', 'gospel-music-mastery' ),
			'booking_date'   => $date,
			'date_label'     => $date ? mysql2date( 'F j, Y', $date . ' 00:00:00' ) : '',
			'time_label'     => $time ? date_i18n( 'g:i A', strtotime( $time ) ) : '',
			'duration'       => $duration,
			'duration_label' => $duration ? sprintf( '%d Minutes', $duration ) : '—',
			'amount'         => $amount,
			'amount_label'   => '$' . number_format_i18n( $amount, 0 ),
			'payment_status' => isset( $row['payment_status'] ) ? (string) $row['payment_status'] : 'pending',
			'notes'          => isset( $row['notes'] ) && $row['notes'] ? (string) $row['notes'] : '—',
			'can_cancel'     => ( 'pending' === $status ),
			'book_again_url' => $book_again,
		);
	}

	/**
	 * @param array<int, array<string, mixed>> $rows Rows.
	 * @return array<string, int>
	 */
	private static function build_history_stats( $rows ) {
		$stats = self::empty_history_stats();
		$stats['total'] = count( $rows );
		foreach ( $rows as $row ) {
			$status = isset( $row['status'] ) ? $row['status'] : '';
			$group  = isset( $row['group'] ) ? $row['group'] : $status;
			if ( 'upcoming' === $group || in_array( $status, array( 'pending', 'confirmed' ), true ) ) {
				if ( in_array( $status, array( 'pending', 'confirmed' ), true ) ) {
					$stats['upcoming']++;
				}
			}
			if ( 'completed' === $status ) {
				$stats['completed']++;
			}
			if ( in_array( $status, array( 'cancelled', 'refunded' ), true ) ) {
				$stats['cancelled']++;
			}
		}
		return $stats;
	}

	/**
	 * @return array<string, int>
	 */
	private static function empty_history_stats() {
		return array(
			'total'     => 0,
			'upcoming'  => 0,
			'completed' => 0,
			'cancelled' => 0,
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
		return isset( $map[ $status ] ) ? $map[ $status ] : ucfirst( $status );
	}

	/**
	 * @param mixed $date Date.
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
}
