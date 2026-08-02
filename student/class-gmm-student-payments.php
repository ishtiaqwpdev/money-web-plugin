<?php
/**
 * Student payment history and transaction controller.
 *
 * Powers [gmm_student_payments] → templates/student/payments.php
 * without changing the frozen payments UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Student_Payments
 *
 * Students may only view their own payment rows. No gateway.
 */
class GMM_Student_Payments {

	const NONCE_ACTION = 'gmm_student_payments';
	const NONCE_FIELD  = 'gmm_student_payments_nonce';
	const CACHE_GROUP  = 'gmm_student_payments';
	const CACHE_TTL    = 60;

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 30, 2 );
		$loader->add_filter( 'gmm_shortcode_html', $instance, 'enhance_payments_html', 20, 2 );
		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 40 );

		$loader->add_action( 'wp_ajax_gmm_student_payments_list', $instance, 'ajax_list' );
		$loader->add_action( 'wp_ajax_gmm_student_payments_details', $instance, 'ajax_details' );
		$loader->add_action( 'wp_ajax_gmm_student_payments_receipt', $instance, 'ajax_receipt' );

		$loader->add_action( 'gmm_payment_created', $instance, 'flush_cache_hook', 20, 2 );
		$loader->add_action( 'gmm_payment_completed', $instance, 'flush_cache_hook', 20, 2 );
		$loader->add_action( 'gmm_payment_failed', $instance, 'flush_cache_hook', 20, 2 );
		$loader->add_action( 'gmm_payment_refunded', $instance, 'flush_cache_hook', 20, 2 );
		$loader->add_action( 'gmm_booking_created', $instance, 'flush_cache_hook', 20, 2 );
		$loader->add_action( 'gmm_booking_cancelled', $instance, 'flush_cache_hook', 20, 2 );
	}

	/**
	 * Inject vars into [gmm_student_payments].
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Tag.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_student_payments' !== $tag ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * Inject Refunded tab + date period filter (existing class names only).
	 *
	 * @param string $html HTML.
	 * @param string $tag  Tag.
	 * @return string
	 */
	public function enhance_payments_html( $html, $tag ) {
		if ( 'gmm_student_payments' !== $tag || '' === $html ) {
			return $html;
		}
		if ( ! self::user_can_view() ) {
			return $html;
		}

		if ( false === strpos( $html, 'data-filter="refunded"' ) ) {
			$html = preg_replace(
				'/(data-filter="failed"[^>]*>Failed<\/button>)/',
				'$1' . "\n" . '<button type="button" class="sl-tab" data-filter="refunded" role="tab" aria-selected="false">Refunded</button>',
				$html,
				1
			);
		}

		if ( false === strpos( $html, 'id="sp-date-filter"' ) ) {
			$filters = self::render_date_filter_markup();
			$html    = preg_replace(
				'/(<div class="sl-tabs sp-tabs"[^>]*>.*?<\/div>)/s',
				'$1' . $filters,
				$html,
				1
			);
		}

		return $html;
	}

	/**
	 * Whether current user may view student payments.
	 *
	 * @param int $user_id Optional.
	 * @return bool
	 */
	public static function user_can_view( $user_id = 0 ) {
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
		if ( ! function_exists( 'gmm_is_student' ) || ! gmm_is_student( $user_id ) ) {
			return false;
		}
		if ( function_exists( 'gmm_student_can_access_dashboard' ) ) {
			return (bool) gmm_student_can_access_dashboard( $user_id );
		}
		return self::authorize_view( $user_id );
	}

	/**
	 * Template variables.
	 *
	 * @param int                  $user_id User ID.
	 * @param array<string, mixed> $filters Optional filters.
	 * @return array<string, mixed>
	 */
	public static function get_template_vars( $user_id = 0, $filters = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$filters = self::parse_filters( $filters ? $filters : self::filters_from_request() );

		if ( ! self::user_can_view( $user_id ) ) {
			return array(
				'payment_rows'    => array(),
				'payment_stats'   => self::empty_stats(),
				'payment_filters' => $filters,
				'payments'        => array(),
				'teachers_url'    => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teachers' ) : '',
				'booking_nonce'   => wp_create_nonce( self::NONCE_ACTION ),
			);
		}

		$rows  = self::get_payment_rows( $user_id, $filters );
		$stats = self::get_payment_stats( $user_id );

		$profile = class_exists( 'GMM_Student' ) ? GMM_Student::get_profile( $user_id ) : null;
		$name    = '';
		$first   = '';
		$email   = '';
		$avatar  = function_exists( 'gmm_design_asset_url' ) ? gmm_design_asset_url( 'assets/img/team/02.jpg' ) : '';

		if ( is_array( $profile ) ) {
			$first = isset( $profile['first_name'] ) ? (string) $profile['first_name'] : '';
			$last  = isset( $profile['last_name'] ) ? (string) $profile['last_name'] : '';
			$name  = trim( $first . ' ' . $last );
			$email = isset( $profile['email'] ) ? (string) $profile['email'] : '';
			if ( ! empty( $profile['profile_image'] ) && function_exists( 'gmm_get_media_url' ) ) {
				$img = gmm_get_media_url( $profile['profile_image'], 'thumbnail' );
				if ( $img ) {
					$avatar = $img;
				}
			}
		}
		if ( '' === $name ) {
			$user  = get_userdata( $user_id );
			$name  = $user ? $user->display_name : __( 'Student', 'gospel-music-mastery' );
			$first = $name;
			if ( ! $email && $user ) {
				$email = $user->user_email;
			}
		}

		$billing = self::get_billing_info( $user_id, $profile );

		return array(
			'user_name'         => $name,
			'user_first_name'   => $first ? $first : $name,
			'user_email'        => $email,
			'user_avatar'       => $avatar,
			'payment_rows'      => $rows,
			'payments'          => $rows,
			'payment_stats'     => $stats,
			'payment_filters'   => $filters,
			'billing_info'      => $billing,
			'teachers_url'      => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teachers' ) : '',
			'booking_form_url'  => function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'booking_form' ) : '',
			'payment_nonce'     => wp_create_nonce( self::NONCE_ACTION ),
		);
	}

	/**
	 * Payment history for student (enriched rows).
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_payment_history( $user_id = 0, $args = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! self::user_can_view( $user_id ) && ! self::authorize_view( $user_id ) ) {
			return array();
		}

		return self::query_payments( $user_id, self::parse_filters( $args ) );
	}

	/**
	 * Formatted payment rows for UI.
	 *
	 * @param int                  $user_id User ID.
	 * @param array<string, mixed> $args    Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_payment_rows( $user_id = 0, $args = array() ) {
		$raw = self::get_payment_history( $user_id, $args );
		$out = array();
		foreach ( $raw as $row ) {
			$formatted = self::format_payment_row( $row );
			if ( $formatted ) {
				$out[] = $formatted;
			}
		}
		return $out;
	}

	/**
	 * Single transaction (own only) — raw joined row.
	 *
	 * @param int $payment_id Payment ID.
	 * @param int $user_id    WP user ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_transaction_details( $payment_id, $user_id = 0 ) {
		$user_id    = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$payment_id = absint( $payment_id );

		if ( ! $payment_id || ! self::user_can_view( $user_id ) ) {
			return null;
		}

		$student_id = class_exists( 'GMM_Student' ) ? GMM_Student::get_student_id( $user_id ) : 0;
		if ( ! $student_id ) {
			return null;
		}

		$rows = self::query_payments(
			$user_id,
			array(
				'payment_id' => $payment_id,
				'limit'      => 1,
			)
		);

		return ! empty( $rows[0] ) ? $rows[0] : null;
	}

	/**
	 * Rich transaction details for student (payment + booking + teacher + class + timeline).
	 *
	 * @param int $payment_id Payment ID.
	 * @param int $user_id    WP user ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_student_transaction_details( $payment_id, $user_id = 0 ) {
		$row = self::get_transaction_details( $payment_id, $user_id );
		if ( ! $row ) {
			return null;
		}

		$formatted = self::format_payment_row( $row );
		$receipt   = self::build_receipt_from_row( $row, $user_id );
		$timeline  = self::build_timeline( $row );
		$refund    = self::get_refund_status_for_payment( absint( $row['id'] ) );

		return array(
			'payment'  => array(
				'id'             => absint( $row['id'] ),
				'transaction_id' => isset( $row['transaction_id'] ) ? (string) $row['transaction_id'] : '',
				'amount'         => isset( $row['amount'] ) ? (float) $row['amount'] : 0.0,
				'payment_method' => isset( $row['payment_method'] ) ? (string) $row['payment_method'] : '',
				'payment_status' => isset( $row['payment_status'] ) ? (string) $row['payment_status'] : '',
				'created_at'     => isset( $row['created_at'] ) ? (string) $row['created_at'] : '',
			),
			'booking'  => array(
				'id'             => isset( $row['booking_id'] ) ? absint( $row['booking_id'] ) : 0,
				'booking_date'   => isset( $row['booking_date'] ) ? (string) $row['booking_date'] : '',
				'booking_time'   => isset( $row['booking_time'] ) ? (string) $row['booking_time'] : '',
				'booking_status' => isset( $row['booking_status'] ) ? (string) $row['booking_status'] : '',
				'payment_status' => isset( $row['booking_payment_status'] ) ? (string) $row['booking_payment_status'] : '',
				'duration'       => isset( $row['booking_duration'] ) ? absint( $row['booking_duration'] ) : 0,
			),
			'teacher'  => array(
				'id'   => isset( $row['teacher_id'] ) ? absint( $row['teacher_id'] ) : 0,
				'name' => isset( $formatted['teacher_name'] ) ? (string) $formatted['teacher_name'] : '',
			),
			'class'    => array(
				'id'    => isset( $row['class_id'] ) ? absint( $row['class_id'] ) : 0,
				'title' => isset( $formatted['class_name'] ) ? (string) $formatted['class_name'] : '',
			),
			'student'  => array(
				'name'  => isset( $formatted['student_name'] ) ? (string) $formatted['student_name'] : '',
				'email' => isset( $formatted['student_email'] ) ? (string) $formatted['student_email'] : '',
			),
			'refund'   => $refund,
			'timeline' => $timeline,
			'receipt'  => $receipt,
			'row'      => $formatted,
		);
	}

	/**
	 * Receipt-oriented payload for a payment (structure only — no PDF).
	 *
	 * @param int $payment_id Payment ID.
	 * @param int $user_id    WP user ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_receipt( $payment_id, $user_id = 0 ) {
		$row = self::get_transaction_details( $payment_id, $user_id );
		if ( ! $row ) {
			return null;
		}
		return self::build_receipt_from_row( $row, $user_id ? $user_id : get_current_user_id() );
	}

	/**
	 * Alias used by payment helpers.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_student_payments( $user_id = 0, $args = array() ) {
		return self::get_payment_history( $user_id, $args );
	}

	/**
	 * Alias for single transaction.
	 *
	 * @param int $payment_id Payment ID.
	 * @param int $user_id    WP user ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_student_transaction( $payment_id, $user_id = 0 ) {
		return self::get_transaction_details( $payment_id, $user_id );
	}

	/**
	 * Aggregate stats for payments page + dashboard.
	 *
	 * @param int $user_id User ID.
	 * @return array<string, mixed>
	 */
	public static function get_payment_stats( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! self::user_can_view( $user_id ) ) {
			return self::empty_stats();
		}

		$student_id = class_exists( 'GMM_Student' ) ? GMM_Student::get_student_id( $user_id ) : 0;
		if ( ! $student_id ) {
			return self::empty_stats();
		}

		$cache_key = 'stats_' . $student_id;
		$cached    = wp_cache_get( $cache_key, self::CACHE_GROUP );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;
		$table = GMM_Database::table( 'payments' );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT payment_status, COALESCE(SUM(amount),0) AS total, COUNT(*) AS cnt
				FROM {$table}
				WHERE student_id = %d
				GROUP BY payment_status",
				$student_id
			),
			ARRAY_A
		);
		$rows = is_array( $rows ) ? $rows : array();

		$stats = self::empty_stats();
		foreach ( $rows as $row ) {
			$status = isset( $row['payment_status'] ) ? sanitize_key( (string) $row['payment_status'] ) : '';
			$total  = isset( $row['total'] ) ? (float) $row['total'] : 0.0;
			$cnt    = isset( $row['cnt'] ) ? (int) $row['cnt'] : 0;
			$stats['total_count'] += $cnt;

			if ( in_array( $status, array( 'completed', 'paid', 'success' ), true ) ) {
				$stats['total_spent']         += $total;
				$stats['completed_count']     += $cnt;
				$stats['completed_amount']    += $total;
			} elseif ( 'pending' === $status ) {
				$stats['pending_count']  += $cnt;
				$stats['pending_amount'] += $total;
			} elseif ( 'failed' === $status ) {
				$stats['failed_count'] += $cnt;
			} elseif ( in_array( $status, array( 'refunded', 'refund' ), true ) ) {
				$stats['refund_count']  += $cnt;
				$stats['refund_amount'] += $total;
			}
		}

		$stats['total_spent']      = round( $stats['total_spent'], 2 );
		$stats['pending_amount']   = round( $stats['pending_amount'], 2 );
		$stats['completed_amount'] = round( $stats['completed_amount'], 2 );
		$stats['refund_amount']    = round( $stats['refund_amount'], 2 );
		$stats['total_spent_label'] = '$' . number_format_i18n( $stats['total_spent'], 0 );

		wp_cache_set( $cache_key, $stats, self::CACHE_GROUP, self::CACHE_TTL );
		return $stats;
	}

	/**
	 * AJAX: filtered payment list.
	 *
	 * @return void
	 */
	public function ajax_list() {
		$this->guard_ajax();

		$filters = self::parse_filters(
			array(
				'status' => isset( $_POST['status'] ) ? sanitize_key( wp_unslash( $_POST['status'] ) ) : 'all', // phpcs:ignore WordPress.Security.NonceVerification.Missing
				'period' => isset( $_POST['period'] ) ? sanitize_key( wp_unslash( $_POST['period'] ) ) : 'all', // phpcs:ignore WordPress.Security.NonceVerification.Missing
				'limit'  => isset( $_POST['limit'] ) ? absint( wp_unslash( $_POST['limit'] ) ) : 100, // phpcs:ignore WordPress.Security.NonceVerification.Missing
			)
		);

		$user_id = get_current_user_id();
		$rows    = self::get_payment_rows( $user_id, $filters );
		$stats   = self::get_payment_stats( $user_id );

		wp_send_json_success(
			array(
				'rows'    => $rows,
				'stats'   => $stats,
				'filters' => $filters,
				'html'    => self::render_rows_html( $rows ),
			)
		);
	}

	/**
	 * AJAX: transaction details.
	 *
	 * @return void
	 */
	public function ajax_details() {
		$this->guard_ajax();

		$payment_id = isset( $_POST['payment_id'] ) ? absint( wp_unslash( $_POST['payment_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$details    = self::get_student_transaction_details( $payment_id, get_current_user_id() );

		if ( ! $details ) {
			wp_send_json_error( array( 'message' => __( 'Transaction not found.', 'gospel-music-mastery' ) ), 404 );
		}

		wp_send_json_success( array( 'transaction' => $details ) );
	}

	/**
	 * AJAX: receipt payload.
	 *
	 * @return void
	 */
	public function ajax_receipt() {
		$this->guard_ajax();

		$payment_id = isset( $_POST['payment_id'] ) ? absint( wp_unslash( $_POST['payment_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Missing
		$receipt    = self::get_receipt( $payment_id, get_current_user_id() );

		if ( ! $receipt ) {
			wp_send_json_error( array( 'message' => __( 'Receipt not found.', 'gospel-music-mastery' ) ), 404 );
		}

		wp_send_json_success( array( 'receipt' => $receipt ) );
	}

	/**
	 * Enqueue payments script.
	 *
	 * @return void
	 */
	public function maybe_enqueue_assets() {
		if ( ! class_exists( 'GMM_Assets' ) || ! GMM_Assets::is_gmm_page() ) {
			return;
		}
		if ( ! self::user_can_view() ) {
			return;
		}

		$post    = get_queried_object();
		$content = ( $post instanceof WP_Post ) ? (string) $post->post_content : '';
		$needed  = has_shortcode( $content, 'gmm_student_payments' )
			|| false !== strpos( $content, 'gmm_student_payments' );

		if ( ! $needed ) {
			return;
		}

		$version = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';
		wp_enqueue_script(
			'gmm-student-payments',
			GMM_URL . 'assets/js/gmm-student-payments.js',
			array( 'gmm-core-script' ),
			$version,
			true
		);

		$vars = self::get_template_vars();
		wp_localize_script(
			'gmm-student-payments',
			'GMM_STUDENT_PAYMENTS',
			array(
				'nonce'      => wp_create_nonce( self::NONCE_ACTION ),
				'nonceField' => self::NONCE_FIELD,
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'actions'    => array(
					'list'    => 'gmm_student_payments_list',
					'details' => 'gmm_student_payments_details',
					'receipt' => 'gmm_student_payments_receipt',
				),
				'filters'    => isset( $vars['payment_filters'] ) ? $vars['payment_filters'] : array(),
				'stats'      => isset( $vars['payment_stats'] ) ? $vars['payment_stats'] : self::empty_stats(),
				'urls'       => array(
					'teachers' => isset( $vars['teachers_url'] ) ? (string) $vars['teachers_url'] : '',
					'booking'  => isset( $vars['booking_form_url'] ) ? (string) $vars['booking_form_url'] : '',
				),
				'i18n'       => array(
					'error'   => __( 'Something went wrong. Please try again.', 'gospel-music-mastery' ),
					'empty'   => __( 'No payment history available yet.', 'gospel-music-mastery' ),
					'loading' => __( 'Loading…', 'gospel-music-mastery' ),
					'receipt' => __( 'Receipt ready (print available). PDF export coming later.', 'gospel-music-mastery' ),
				),
			)
		);
	}

	/**
	 * Flush payment cache.
	 *
	 * @param mixed $a Unused.
	 * @param mixed $b Unused.
	 * @return void
	 */
	public function flush_cache_hook( $a = null, $b = null ) {
		unset( $a, $b );
		$user_id = get_current_user_id();
		if ( $user_id && class_exists( 'GMM_Student' ) ) {
			$sid = GMM_Student::get_student_id( $user_id );
			if ( $sid ) {
				wp_cache_delete( 'stats_' . $sid, self::CACHE_GROUP );
			}
		}
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

		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid security token.', 'gospel-music-mastery' ) ), 403 );
		}

		if ( ! self::user_can_view() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in as a student.', 'gospel-music-mastery' ) ), 403 );
		}
	}

	/**
	 * @param array<string, mixed> $args Args.
	 * @return array<string, mixed>
	 */
	public static function parse_filters( $args = array() ) {
		$args = is_array( $args ) ? $args : array();

		$status = isset( $args['status'] ) ? sanitize_key( (string) $args['status'] ) : 'all';
		if ( ! in_array( $status, array( 'all', 'pending', 'completed', 'failed', 'refunded' ), true ) ) {
			$status = 'all';
		}

		$period = isset( $args['period'] ) ? sanitize_key( (string) $args['period'] ) : 'all';
		if ( ! in_array( $period, array( 'all', 'today', 'month', 'year' ), true ) ) {
			$period = 'all';
		}

		return array(
			'status'     => $status,
			'period'     => $period,
			'payment_id' => isset( $args['payment_id'] ) ? absint( $args['payment_id'] ) : 0,
			'limit'      => isset( $args['limit'] ) ? min( absint( $args['limit'] ), 200 ) : 100,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function filters_from_request() {
		$status = isset( $_GET['status'] ) ? sanitize_key( wp_unslash( $_GET['status'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$period = isset( $_GET['period'] ) ? sanitize_key( wp_unslash( $_GET['period'] ) ) : 'all'; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return self::parse_filters(
			array(
				'status' => $status,
				'period' => $period,
			)
		);
	}

	/**
	 * Query payments with booking/teacher/class joins.
	 *
	 * @param int                  $user_id User ID.
	 * @param array<string, mixed> $filters Filters.
	 * @return array<int, array<string, mixed>>
	 */
	private static function query_payments( $user_id, $filters ) {
		$student_id = class_exists( 'GMM_Student' ) ? GMM_Student::get_student_id( $user_id ) : 0;
		if ( ! $student_id ) {
			return array();
		}

		$filters = self::parse_filters( $filters );

		global $wpdb;
		$payments = GMM_Database::table( 'payments' );
		$bookings = GMM_Database::table( 'bookings' );
		$teachers = GMM_Database::table( 'teachers' );
		$classes  = GMM_Database::table( 'classes' );
		$students = GMM_Database::table( 'students' );

		$sql = "SELECT p.*,
				b.booking_date,
				b.booking_time,
				b.duration AS booking_duration,
				b.booking_status,
				b.payment_status AS booking_payment_status,
				b.class_id,
				b.notes AS booking_notes,
				t.first_name AS teacher_first_name,
				t.last_name AS teacher_last_name,
				t.profile_image AS teacher_image,
				c.title AS class_title,
				s.first_name AS student_first_name,
				s.last_name AS student_last_name,
				s.email AS student_email
			FROM {$payments} p
			LEFT JOIN {$bookings} b ON b.id = p.booking_id
			LEFT JOIN {$teachers} t ON t.id = p.teacher_id
			LEFT JOIN {$classes} c ON c.id = b.class_id
			LEFT JOIN {$students} s ON s.id = p.student_id
			WHERE p.student_id = %d";

		$params = array( $student_id );

		if ( ! empty( $filters['payment_id'] ) ) {
			$sql     .= ' AND p.id = %d';
			$params[] = absint( $filters['payment_id'] );
		}

		if ( 'all' !== $filters['status'] ) {
			if ( 'completed' === $filters['status'] ) {
				$sql .= " AND p.payment_status IN ('completed','paid','success')";
			} elseif ( 'refunded' === $filters['status'] ) {
				$sql .= " AND p.payment_status IN ('refunded','refund')";
			} else {
				$sql     .= ' AND p.payment_status = %s';
				$params[] = $filters['status'];
			}
		}

		$today = current_time( 'Y-m-d' );
		if ( 'today' === $filters['period'] ) {
			$sql     .= ' AND DATE(p.created_at) = %s';
			$params[] = $today;
		} elseif ( 'month' === $filters['period'] ) {
			$sql     .= ' AND YEAR(p.created_at) = %d AND MONTH(p.created_at) = %d';
			$params[] = (int) current_time( 'Y' );
			$params[] = (int) current_time( 'n' );
		} elseif ( 'year' === $filters['period'] ) {
			$sql     .= ' AND YEAR(p.created_at) = %d';
			$params[] = (int) current_time( 'Y' );
		}

		$sql     .= ' ORDER BY p.created_at DESC, p.id DESC LIMIT %d';
		$params[] = $filters['limit'];

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @param array<string, mixed> $row DB row.
	 * @return array<string, mixed>|null
	 */
	public static function format_payment_row( $row ) {
		if ( ! is_array( $row ) || empty( $row['id'] ) ) {
			return null;
		}

		$status = isset( $row['payment_status'] ) ? sanitize_key( (string) $row['payment_status'] ) : 'pending';
		if ( in_array( $status, array( 'paid', 'success' ), true ) ) {
			$status = 'completed';
		}
		if ( 'refund' === $status ) {
			$status = 'refunded';
		}

		$amount = isset( $row['amount'] ) ? (float) $row['amount'] : 0.0;
		$txn    = isset( $row['transaction_id'] ) && $row['transaction_id']
			? (string) $row['transaction_id']
			: ( 'GMM-' . absint( $row['id'] ) );

		$teacher = trim(
			( isset( $row['teacher_first_name'] ) ? (string) $row['teacher_first_name'] : '' ) . ' ' .
			( isset( $row['teacher_last_name'] ) ? (string) $row['teacher_last_name'] : '' )
		);
		if ( '' === $teacher ) {
			$teacher = __( 'Teacher', 'gospel-music-mastery' );
		}

		$student = trim(
			( isset( $row['student_first_name'] ) ? (string) $row['student_first_name'] : '' ) . ' ' .
			( isset( $row['student_last_name'] ) ? (string) $row['student_last_name'] : '' )
		);

		$class_name = ! empty( $row['class_title'] )
			? (string) $row['class_title']
			: __( 'Lesson', 'gospel-music-mastery' );

		$created = isset( $row['created_at'] ) ? (string) $row['created_at'] : '';
		$method  = isset( $row['payment_method'] ) && $row['payment_method']
			? (string) $row['payment_method']
			: __( 'Card', 'gospel-music-mastery' );

		$badge = 'is-pending';
		if ( 'completed' === $status ) {
			$badge = 'is-confirmed';
		} elseif ( 'failed' === $status ) {
			$badge = 'is-cancelled';
		} elseif ( 'refunded' === $status ) {
			$badge = 'is-cancelled';
		}

		$refund = self::get_refund_status_for_payment( absint( $row['id'] ) );

		return array(
			'id'                     => absint( $row['id'] ),
			'booking_id'             => isset( $row['booking_id'] ) ? absint( $row['booking_id'] ) : 0,
			'transaction_id'         => $txn,
			'status'                 => $status,
			'status_label'           => self::status_label( $status ),
			'badge_class'            => $badge,
			'amount'                 => $amount,
			'amount_label'           => '$' . number_format_i18n( $amount, 0 ),
			'payment_method'         => $method,
			'method_label'           => ucfirst( $method ),
			'teacher_name'           => $teacher,
			'student_name'           => $student,
			'student_email'          => isset( $row['student_email'] ) ? (string) $row['student_email'] : '',
			'class_name'             => $class_name,
			'created_at'             => $created,
			'date_label'             => $created ? date_i18n( 'F j, Y', strtotime( $created ) ) : '',
			'booking_status'         => isset( $row['booking_status'] ) ? sanitize_key( (string) $row['booking_status'] ) : '',
			'booking_status_label'   => self::booking_status_label( isset( $row['booking_status'] ) ? (string) $row['booking_status'] : '' ),
			'booking_payment_status' => isset( $row['booking_payment_status'] ) ? sanitize_key( (string) $row['booking_payment_status'] ) : '',
			'booking_date'           => isset( $row['booking_date'] ) ? (string) $row['booking_date'] : '',
			'booking_time'           => isset( $row['booking_time'] ) ? (string) $row['booking_time'] : '',
			'refund_status'          => isset( $refund['status'] ) ? (string) $refund['status'] : '',
			'refund_label'           => isset( $refund['label'] ) ? (string) $refund['label'] : '',
			'can_view_receipt'       => true,
		);
	}

	/**
	 * @param array<string, mixed> $row     Row.
	 * @param int                  $user_id User.
	 * @return array<string, mixed>
	 */
	private static function build_receipt_from_row( $row, $user_id ) {
		$formatted = self::format_payment_row( $row );
		$profile   = class_exists( 'GMM_Student' ) ? GMM_Student::get_profile( $user_id ) : null;
		$student   = $formatted && ! empty( $formatted['student_name'] ) ? $formatted['student_name'] : '';

		if ( '' === $student && is_array( $profile ) ) {
			$student = trim(
				( isset( $profile['first_name'] ) ? $profile['first_name'] : '' ) . ' ' .
				( isset( $profile['last_name'] ) ? $profile['last_name'] : '' )
			);
		}

		$txn = isset( $row['transaction_id'] ) && $row['transaction_id']
			? (string) $row['transaction_id']
			: ( 'GMM-' . absint( $row['id'] ) );

		$created = isset( $row['created_at'] ) ? (string) $row['created_at'] : '';

		return array(
			'receipt_id'     => 'GMM-R-' . absint( $row['id'] ),
			'transaction_id' => $txn,
			'payment_id'     => absint( $row['id'] ),
			'booking_id'     => isset( $row['booking_id'] ) ? absint( $row['booking_id'] ) : 0,
			'student_name'   => $student,
			'student_email'  => is_array( $profile ) && isset( $profile['email'] ) ? (string) $profile['email'] : ( isset( $row['student_email'] ) ? (string) $row['student_email'] : '' ),
			'teacher_name'   => $formatted ? $formatted['teacher_name'] : '',
			'class_name'     => $formatted ? $formatted['class_name'] : '',
			'amount'         => isset( $row['amount'] ) ? (float) $row['amount'] : 0.0,
			'amount_label'   => '$' . number_format_i18n( isset( $row['amount'] ) ? (float) $row['amount'] : 0, 2 ),
			'date'           => $created,
			'date_label'     => $created ? date_i18n( 'F j, Y', strtotime( $created ) ) : '',
			'payment_method' => isset( $row['payment_method'] ) ? (string) $row['payment_method'] : '',
			'payment_status' => isset( $row['payment_status'] ) ? (string) $row['payment_status'] : '',
			'pdf_ready'      => false,
		);
	}

	/**
	 * @param array<string, mixed> $row Row.
	 * @return array<int, array<string, string>>
	 */
	private static function build_timeline( $row ) {
		$timeline = array();
		$created  = isset( $row['created_at'] ) ? (string) $row['created_at'] : '';
		$status   = isset( $row['payment_status'] ) ? sanitize_key( (string) $row['payment_status'] ) : 'pending';

		$timeline[] = array(
			'key'   => 'created',
			'label' => __( 'Payment record created', 'gospel-music-mastery' ),
			'date'  => $created ? date_i18n( 'M j, Y g:i A', strtotime( $created ) ) : '',
		);

		if ( in_array( $status, array( 'completed', 'paid', 'success' ), true ) ) {
			$timeline[] = array(
				'key'   => 'completed',
				'label' => __( 'Payment completed', 'gospel-music-mastery' ),
				'date'  => $created ? date_i18n( 'M j, Y g:i A', strtotime( $created ) ) : '',
			);
		} elseif ( 'failed' === $status ) {
			$timeline[] = array(
				'key'   => 'failed',
				'label' => __( 'Payment failed', 'gospel-music-mastery' ),
				'date'  => $created ? date_i18n( 'M j, Y g:i A', strtotime( $created ) ) : '',
			);
		} elseif ( in_array( $status, array( 'refunded', 'refund' ), true ) ) {
			$timeline[] = array(
				'key'   => 'refunded',
				'label' => __( 'Refund completed', 'gospel-music-mastery' ),
				'date'  => $created ? date_i18n( 'M j, Y g:i A', strtotime( $created ) ) : '',
			);
		} else {
			$timeline[] = array(
				'key'   => 'pending',
				'label' => __( 'Awaiting payment confirmation', 'gospel-music-mastery' ),
				'date'  => '',
			);
		}

		$booking_status = isset( $row['booking_status'] ) ? sanitize_key( (string) $row['booking_status'] ) : '';
		if ( $booking_status ) {
			$timeline[] = array(
				'key'   => 'booking',
				'label' => sprintf(
					/* translators: %s: booking status */
					__( 'Lesson status: %s', 'gospel-music-mastery' ),
					self::booking_status_label( $booking_status )
				),
				'date'  => '',
			);
		}

		$refund = self::get_refund_status_for_payment( absint( $row['id'] ) );
		if ( ! empty( $refund['status'] ) ) {
			$timeline[] = array(
				'key'   => 'refund_' . $refund['status'],
				'label' => $refund['label'],
				'date'  => isset( $refund['date'] ) ? (string) $refund['date'] : '',
			);
		}

		return $timeline;
	}

	/**
	 * Refund status display (view only).
	 *
	 * @param int $payment_id Payment ID.
	 * @return array<string, string>
	 */
	public static function get_refund_status_for_payment( $payment_id ) {
		$payment_id = absint( $payment_id );
		if ( ! $payment_id ) {
			return array(
				'status' => '',
				'label'  => '',
				'date'   => '',
			);
		}

		$requests = get_option( 'gmm_refund_requests', array() );
		$requests = is_array( $requests ) ? $requests : array();
		$match    = null;

		foreach ( $requests as $req ) {
			if ( ! is_array( $req ) ) {
				continue;
			}
			if ( isset( $req['payment_id'] ) && absint( $req['payment_id'] ) === $payment_id ) {
				$match = $req;
			}
		}

		if ( ! $match ) {
			return array(
				'status' => '',
				'label'  => '',
				'date'   => '',
			);
		}

		$status = isset( $match['status'] ) ? sanitize_key( (string) $match['status'] ) : 'requested';
		$labels = array(
			'requested' => __( 'Refund requested', 'gospel-music-mastery' ),
			'approved'  => __( 'Refund approved', 'gospel-music-mastery' ),
			'completed' => __( 'Refund completed', 'gospel-music-mastery' ),
			'rejected'  => __( 'Refund rejected', 'gospel-music-mastery' ),
		);

		$date = '';
		if ( ! empty( $match['requested_at'] ) ) {
			$date = date_i18n( 'M j, Y g:i A', strtotime( (string) $match['requested_at'] ) );
		}

		return array(
			'status' => $status,
			'label'  => isset( $labels[ $status ] ) ? $labels[ $status ] : ucfirst( $status ),
			'date'   => $date,
		);
	}

	/**
	 * @param int                      $user_id User.
	 * @param array<string, mixed>|null $profile Profile.
	 * @return array<string, string>
	 */
	private static function get_billing_info( $user_id, $profile ) {
		$meta = get_user_meta( $user_id, 'gmm_student_billing', true );
		$meta = is_array( $meta ) ? $meta : array();

		$name = '';
		if ( is_array( $profile ) ) {
			$name = trim(
				( isset( $profile['first_name'] ) ? $profile['first_name'] : '' ) . ' ' .
				( isset( $profile['last_name'] ) ? $profile['last_name'] : '' )
			);
		}

		return array(
			'full_name' => isset( $meta['full_name'] ) ? (string) $meta['full_name'] : $name,
			'email'     => isset( $meta['email'] ) ? (string) $meta['email'] : ( is_array( $profile ) && isset( $profile['email'] ) ? (string) $profile['email'] : '' ),
			'country'   => isset( $meta['country'] ) ? (string) $meta['country'] : '',
			'address'   => isset( $meta['address'] ) ? (string) $meta['address'] : '',
			'city'      => isset( $meta['city'] ) ? (string) $meta['city'] : '',
			'zip'       => isset( $meta['zip'] ) ? (string) $meta['zip'] : '',
		);
	}

	/**
	 * @return string
	 */
	private static function render_date_filter_markup() {
		ob_start();
		?>
		<div class="sp-filter-bar" id="sp-filter-bar">
			<label class="visually-hidden" for="sp-date-filter"><?php esc_html_e( 'Date filter', 'gospel-music-mastery' ); ?></label>
			<select class="form-control form-select" id="sp-date-filter" name="period">
				<option value="all"><?php esc_html_e( 'All dates', 'gospel-music-mastery' ); ?></option>
				<option value="today"><?php esc_html_e( 'Today', 'gospel-music-mastery' ); ?></option>
				<option value="month"><?php esc_html_e( 'This Month', 'gospel-music-mastery' ); ?></option>
				<option value="year"><?php esc_html_e( 'This Year', 'gospel-music-mastery' ); ?></option>
			</select>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Server-rendered rows for AJAX refresh (same classes as template).
	 *
	 * @param array<int, array<string, mixed>> $rows Rows.
	 * @return string
	 */
	public static function render_rows_html( $rows ) {
		ob_start();
		foreach ( $rows as $row ) {
			$status  = isset( $row['status'] ) ? (string) $row['status'] : 'pending';
			$badge   = isset( $row['badge_class'] ) ? (string) $row['badge_class'] : 'is-pending';
			$pid     = isset( $row['id'] ) ? (int) $row['id'] : 0;
			$txn     = isset( $row['transaction_id'] ) ? (string) $row['transaction_id'] : '';
			$teacher = isset( $row['teacher_name'] ) ? (string) $row['teacher_name'] : '';
			$lesson  = isset( $row['class_name'] ) ? (string) $row['class_name'] : '';
			$amount  = isset( $row['amount_label'] ) ? (string) $row['amount_label'] : '';
			$method  = isset( $row['method_label'] ) ? (string) $row['method_label'] : '';
			$date    = isset( $row['date_label'] ) ? (string) $row['date_label'] : '';
			$student = isset( $row['student_name'] ) ? (string) $row['student_name'] : '';
			$label   = isset( $row['status_label'] ) ? (string) $row['status_label'] : ucfirst( $status );
			?>
			<tr class="sp-row" data-status="<?php echo esc_attr( $status ); ?>"
				data-payment-id="<?php echo esc_attr( (string) $pid ); ?>"
				data-id="<?php echo esc_attr( $txn ); ?>"
				data-date="<?php echo esc_attr( $date ); ?>"
				data-student="<?php echo esc_attr( $student ); ?>"
				data-teacher="<?php echo esc_attr( $teacher ); ?>"
				data-lesson="<?php echo esc_attr( $lesson ); ?>"
				data-amount="<?php echo esc_attr( $amount ); ?>"
				data-method="<?php echo esc_attr( $method ); ?>"
				data-booking-id="<?php echo esc_attr( (string) ( isset( $row['booking_id'] ) ? (int) $row['booking_id'] : 0 ) ); ?>"
				data-booking-status="<?php echo esc_attr( isset( $row['booking_status_label'] ) ? (string) $row['booking_status_label'] : '' ); ?>"
				data-refund="<?php echo esc_attr( isset( $row['refund_label'] ) ? (string) $row['refund_label'] : '' ); ?>">
				<td data-label="Date"><?php echo esc_html( $date ); ?></td>
				<td data-label="Teacher"><?php echo esc_html( $teacher ); ?></td>
				<td data-label="Lesson"><?php echo esc_html( $lesson ); ?></td>
				<td data-label="Amount"><strong><?php echo esc_html( $amount ); ?></strong></td>
				<td data-label="Payment Method"><?php echo esc_html( $method ); ?></td>
				<td data-label="Status"><span class="sb-badge <?php echo esc_attr( $badge ); ?>"><?php echo esc_html( $label ); ?></span></td>
				<td data-label="Action">
					<div class="sb-actions">
						<button type="button" class="theme-btn theme-btn-outline sd-action-btn sp-view-receipt" data-payment-id="<?php echo esc_attr( (string) $pid ); ?>">View Receipt</button>
						<button type="button" class="theme-btn theme-btn-outline sd-action-btn sp-download-invoice" data-payment-id="<?php echo esc_attr( (string) $pid ); ?>">Download Invoice</button>
					</div>
				</td>
			</tr>
			<?php
		}
		return (string) ob_get_clean();
	}

	/**
	 * @param string $status Status.
	 * @return string
	 */
	private static function status_label( $status ) {
		$map = array(
			'pending'   => __( 'Pending', 'gospel-music-mastery' ),
			'completed' => __( 'Completed', 'gospel-music-mastery' ),
			'failed'    => __( 'Failed', 'gospel-music-mastery' ),
			'refunded'  => __( 'Refunded', 'gospel-music-mastery' ),
		);
		return isset( $map[ $status ] ) ? $map[ $status ] : ucfirst( $status );
	}

	/**
	 * @param string $status Booking status.
	 * @return string
	 */
	private static function booking_status_label( $status ) {
		$status = sanitize_key( $status );
		$map    = array(
			'pending'   => __( 'Pending confirmation', 'gospel-music-mastery' ),
			'confirmed' => __( 'Lesson confirmed', 'gospel-music-mastery' ),
			'completed' => __( 'Lesson completed', 'gospel-music-mastery' ),
			'cancelled' => __( 'Lesson cancelled', 'gospel-music-mastery' ),
		);
		return isset( $map[ $status ] ) ? $map[ $status ] : ( $status ? ucfirst( $status ) : '' );
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function empty_stats() {
		return array(
			'total_spent'         => 0.0,
			'total_spent_label'   => '$0',
			'total_count'         => 0,
			'completed_count'     => 0,
			'completed_amount'    => 0.0,
			'pending_count'       => 0,
			'pending_amount'      => 0.0,
			'failed_count'        => 0,
			'refund_count'        => 0,
			'refund_amount'       => 0.0,
		);
	}

	/**
	 * @param int $user_id WP user ID.
	 * @return bool
	 */
	private static function authorize_view( $user_id ) {
		if ( ! is_user_logged_in() || ! class_exists( 'GMM_Student' ) || ! GMM_Student::can_view_profile( $user_id ) ) {
			return false;
		}
		return current_user_can( 'manage_gmm_bookings' )
			|| current_user_can( 'manage_gmm_profile' )
			|| current_user_can( 'manage_options' );
	}
}
