<?php
/**
 * Teacher earnings and withdrawal management.
 *
 * Supplies data for templates/teacher/withdrawals.php (frozen UI).
 * Commission from gmm_commission_settings via GMM_Payment::calculate_split().
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Teacher_Earnings
 *
 * Teachers may only view/request against their own earnings.
 */
class GMM_Teacher_Earnings {

	const NONCE_ACTION = 'gmm_teacher_earnings_action';
	const NONCE_FIELD  = 'gmm_teacher_earnings_nonce';

	const STATUS_PENDING  = 'pending';
	const STATUS_APPROVED = 'approved';
	const STATUS_REJECTED = 'rejected';
	const STATUS_PAID     = 'paid';

	/**
	 * Allowed withdrawal statuses.
	 *
	 * @var array<int, string>
	 */
	const WITHDRAWAL_STATUSES = array(
		self::STATUS_PENDING,
		self::STATUS_APPROVED,
		self::STATUS_REJECTED,
		self::STATUS_PAID,
	);

	/**
	 * Statuses that reserve available balance.
	 *
	 * @var array<int, string>
	 */
	const BALANCE_RESERVED_STATUSES = array(
		self::STATUS_PENDING,
		self::STATUS_APPROVED,
		self::STATUS_PAID,
	);

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 25, 2 );
		$loader->add_filter( 'gmm_shortcode_html', $instance, 'enhance_withdrawals_html', 20, 2 );

		$loader->add_action( 'wp_ajax_gmm_teacher_withdrawal_request', $instance, 'ajax_withdrawal_request' );
		$loader->add_action( 'wp_ajax_gmm_teacher_earnings_refresh', $instance, 'ajax_earnings_refresh' );
		$loader->add_action( 'wp_ajax_gmm_teacher_withdrawal_history', $instance, 'ajax_withdrawal_history' );
		$loader->add_action( 'wp_ajax_gmm_teacher_transactions', $instance, 'ajax_transactions' );

		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 40 );

		$loader->add_action( 'gmm_payment_completed', $instance, 'flush_on_payment_change', 20, 2 );
		$loader->add_action( 'gmm_payment_refunded', $instance, 'flush_on_payment_change', 20, 2 );
		$loader->add_action( 'gmm_admin_payment_status_updated', $instance, 'flush_on_admin_payment', 20, 2 );
	}

	/**
	 * Inject vars into [gmm_teacher_withdrawals].
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Shortcode.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_teacher_withdrawals' !== $tag ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * Enhance frozen markup: nonce, min amount, account details, earnings history.
	 *
	 * @param string $html HTML.
	 * @param string $tag  Tag.
	 * @return string
	 */
	public function enhance_withdrawals_html( $html, $tag ) {
		if ( 'gmm_teacher_withdrawals' !== $tag || '' === $html ) {
			return $html;
		}
		if ( ! self::user_can_manage() ) {
			return $html;
		}

		$min = self::get_minimum_withdrawal();
		$min_fmt = number_format_i18n( $min, 2 );

		if ( false === strpos( $html, 'name="' . self::NONCE_FIELD . '"' ) ) {
			$nonce = wp_nonce_field( self::NONCE_ACTION, self::NONCE_FIELD, true, false );
			$extra = $nonce . '<input type="hidden" name="account_details" id="withdrawal-account-details" value="' . esc_attr( self::get_default_account_details( get_current_user_id() ) ) . '" />';
			$html  = preg_replace(
				'/(<form[^>]*id="withdrawal-request-form"[^>]*>)/',
				'$1' . $extra,
				$html,
				1
			);
		}

		$html = preg_replace(
			'/(min=")50(")/',
			'$1' . esc_attr( (string) $min ) . '$2',
			$html,
			1
		);

		$html = preg_replace(
			'/Minimum withdrawal amount:\s*\$50/',
			'Minimum withdrawal amount: $' . esc_html( $min_fmt ),
			$html,
			1
		);

		if ( false === strpos( $html, 'id="gmm-earnings-history"' ) ) {
			$block = self::render_earnings_history_section();
			if ( preg_match( '/(<p class="td-empty-state" id="withdrawal-empty"[^>]*>.*?<\/p>\s*<\/section>)/s', $html ) ) {
				$html = preg_replace(
					'/(<p class="td-empty-state" id="withdrawal-empty"[^>]*>.*?<\/p>\s*<\/section>)/s',
					'$1' . $block,
					$html,
					1
				);
			} else {
				$html .= $block;
			}
		}

		return $html;
	}

	/**
	 * Template variables for withdrawals page.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_template_vars( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		$earnings    = self::get_earnings( $user_id );
		$history     = self::get_withdrawal_history( $user_id, array( 'limit' => 100 ) );
		$transactions = self::get_transactions( $user_id, array( 'limit' => 50 ) );

		$min = self::get_minimum_withdrawal();

		return array(
			'earnings'            => $earnings,
			'transactions'        => $transactions,
			'withdrawal_history'  => $history,
			'min_withdrawal'      => $min,
			'account_details'     => self::get_default_account_details( $user_id ),
			'can_request'         => self::user_can_manage( $user_id ),
		);
	}

	/**
	 * Whether current user may manage own earnings/withdrawals.
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
		return current_user_can( 'manage_gmm_profile' ) || current_user_can( 'manage_gmm_bookings' );
	}

	/**
	 * Verify nonce.
	 *
	 * @param string $nonce Nonce.
	 * @return bool
	 */
	public static function verify_nonce( $nonce ) {
		return (bool) wp_verify_nonce( (string) $nonce, self::NONCE_ACTION );
	}

	/**
	 * Minimum withdrawal from gmm_commission_settings.
	 *
	 * @return float
	 */
	public static function get_minimum_withdrawal() {
		$min = 50.0;
		if ( function_exists( 'gmm_get_setting' ) ) {
			$min = (float) gmm_get_setting( 'minimum_withdrawal', 50 );
		}
		return max( 0, round( $min, 2 ) );
	}

	/**
	 * Default account details for Stripe payouts.
	 *
	 * @param int $user_id WP user ID.
	 * @return string
	 */
	public static function get_default_account_details( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$stripe  = get_user_meta( $user_id, 'gmm_stripe_account_id', true );
		if ( is_string( $stripe ) && '' !== trim( $stripe ) ) {
			return sanitize_text_field( $stripe );
		}
		$email = '';
		$user  = get_userdata( $user_id );
		if ( $user && ! empty( $user->user_email ) ) {
			$email = (string) $user->user_email;
		}
		return $email ? 'Stripe · ' . $email : 'Stripe Connected Account';
	}

	/**
	 * Earnings summary with commission split + available balance.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_earnings( $user_id = 0 ) {
		$percent = class_exists( 'GMM_Payment' ) ? GMM_Payment::get_commission_percent() : 10.0;
		$empty   = array(
			'total_earnings'       => 0.0,
			'pending_earnings'     => 0.0,
			'completed_earnings'   => 0.0,
			'available_balance'    => 0.0,
			'withdrawn_amount'     => 0.0,
			'pending_withdrawals'  => 0.0,
			'platform_commission'  => 0.0,
			'commission_percent'   => $percent,
			'min_withdrawal'       => self::get_minimum_withdrawal(),
		);

		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id || ! is_user_logged_in() ) {
			return $empty;
		}

		if ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== $user_id ) {
			return $empty;
		}

		if ( ! class_exists( 'GMM_Teacher' ) || ! GMM_Teacher::can_view_profile( $user_id ) ) {
			return $empty;
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return $empty;
		}

		global $wpdb;
		$payments = GMM_Database::table( 'payments' );

		// Single aggregate query for lesson payments (excludes legacy withdrawal payment rows).
		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT
					COALESCE(SUM(CASE WHEN payment_status = 'completed' THEN amount ELSE 0 END),0) AS completed_gross,
					COALESCE(SUM(CASE WHEN payment_status = 'pending' THEN amount ELSE 0 END),0) AS pending_gross
				FROM {$payments}
				WHERE teacher_id = %d
				AND payment_method <> %s",
				$teacher_id,
				'withdrawal'
			),
			ARRAY_A
		);

		$completed_gross = isset( $row['completed_gross'] ) ? (float) $row['completed_gross'] : 0.0;
		$pending_gross   = isset( $row['pending_gross'] ) ? (float) $row['pending_gross'] : 0.0;

		$completed_split = class_exists( 'GMM_Payment' )
			? GMM_Payment::calculate_split( $completed_gross )
			: array(
				'teacher_earnings' => $completed_gross,
				'commission'       => 0,
			);

		$pending_split = class_exists( 'GMM_Payment' )
			? GMM_Payment::calculate_split( $pending_gross )
			: array(
				'teacher_earnings' => $pending_gross,
				'commission'       => 0,
			);

		$teacher_completed = (float) $completed_split['teacher_earnings'];
		$teacher_pending   = (float) $pending_split['teacher_earnings'];

		$wd_sums = self::sum_withdrawals_by_status( $teacher_id );
		$withdrawn = isset( $wd_sums[ self::STATUS_PAID ] ) ? (float) $wd_sums[ self::STATUS_PAID ] : 0.0;
		$pending_wd = ( isset( $wd_sums[ self::STATUS_PENDING ] ) ? (float) $wd_sums[ self::STATUS_PENDING ] : 0.0 )
			+ ( isset( $wd_sums[ self::STATUS_APPROVED ] ) ? (float) $wd_sums[ self::STATUS_APPROVED ] : 0.0 );

		$reserved = $withdrawn + $pending_wd;
		$available = max( 0, round( $teacher_completed - $reserved, 2 ) );

		return array(
			'total_earnings'      => round( $teacher_completed + $teacher_pending, 2 ),
			'pending_earnings'    => round( $teacher_pending, 2 ),
			'completed_earnings'  => round( $teacher_completed, 2 ),
			'available_balance'   => $available,
			'withdrawn_amount'    => round( $withdrawn, 2 ),
			'pending_withdrawals' => round( $pending_wd, 2 ),
			'platform_commission' => round( (float) $completed_split['commission'], 2 ),
			'commission_percent'  => $percent,
			'min_withdrawal'      => self::get_minimum_withdrawal(),
		);
	}

	/**
	 * Sum withdrawal amounts grouped by status for a teacher.
	 *
	 * @param int $teacher_id Teacher row ID.
	 * @return array<string, float>
	 */
	private static function sum_withdrawals_by_status( $teacher_id ) {
		$teacher_id = absint( $teacher_id );
		$out        = array(
			self::STATUS_PENDING  => 0.0,
			self::STATUS_APPROVED => 0.0,
			self::STATUS_REJECTED => 0.0,
			self::STATUS_PAID     => 0.0,
		);

		if ( ! $teacher_id || ! self::withdrawals_table_exists() ) {
			return $out;
		}

		global $wpdb;
		$table = GMM_Database::table( 'withdrawals' );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT status, COALESCE(SUM(amount),0) AS total
				FROM {$table}
				WHERE teacher_id = %d
				GROUP BY status",
				$teacher_id
			),
			ARRAY_A
		);

		if ( ! is_array( $rows ) ) {
			return $out;
		}

		foreach ( $rows as $r ) {
			$status = isset( $r['status'] ) ? sanitize_key( $r['status'] ) : '';
			if ( isset( $out[ $status ] ) ) {
				$out[ $status ] = (float) $r['total'];
			}
		}

		return $out;
	}

	/**
	 * Whether gmm_withdrawals table exists.
	 *
	 * @return bool
	 */
	public static function withdrawals_table_exists() {
		global $wpdb;
		$table  = GMM_Database::table( 'withdrawals' );
		$found  = $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $table ) );
		return ( $found === $table );
	}

	/**
	 * Payment transactions for teacher (own only) with class/student context.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_transactions( $user_id = 0, $args = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id || ! is_user_logged_in() ) {
			return array();
		}

		if ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== $user_id ) {
			return array();
		}

		if ( ! class_exists( 'GMM_Teacher' ) || ! GMM_Teacher::can_view_profile( $user_id ) ) {
			return array();
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}

		global $wpdb;
		$payments  = GMM_Database::table( 'payments' );
		$bookings  = GMM_Database::table( 'bookings' );
		$classes   = GMM_Database::table( 'classes' );
		$students  = GMM_Database::table( 'students' );

		$sql = "SELECT p.*,
				c.title AS class_name,
				TRIM(CONCAT(COALESCE(s.first_name,''),' ',COALESCE(s.last_name,''))) AS student_name
			FROM {$payments} p
			LEFT JOIN {$bookings} b ON b.id = p.booking_id
			LEFT JOIN {$classes} c ON c.id = b.class_id
			LEFT JOIN {$students} s ON s.id = p.student_id
			WHERE p.teacher_id = %d
			AND p.payment_method <> %s";

		$params = array( $teacher_id, 'withdrawal' );

		if ( ! empty( $args['status'] ) ) {
			$sql     .= ' AND p.payment_status = %s';
			$params[] = sanitize_key( $args['status'] );
		}

		$sql     .= ' ORDER BY p.created_at DESC LIMIT %d';
		$params[] = isset( $args['limit'] ) ? min( absint( $args['limit'] ), 200 ) : 100;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$out = array();
		foreach ( $rows as $row ) {
			$amount = isset( $row['amount'] ) ? (float) $row['amount'] : 0.0;
			$split  = class_exists( 'GMM_Payment' )
				? GMM_Payment::calculate_split( $amount )
				: array(
					'commission'       => 0.0,
					'teacher_earnings' => $amount,
					'commission_percent' => 0.0,
				);

			$id = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
			$out[] = array(
				'id'                => $id,
				'transaction_id'    => isset( $row['transaction_id'] ) && $row['transaction_id']
					? (string) $row['transaction_id']
					: 'PAY-' . $id,
				'class_name'        => isset( $row['class_name'] ) && $row['class_name']
					? (string) $row['class_name']
					: __( 'Lesson', 'gospel-music-mastery' ),
				'student_name'      => isset( $row['student_name'] ) && trim( (string) $row['student_name'] )
					? trim( (string) $row['student_name'] )
					: __( 'Student', 'gospel-music-mastery' ),
				'amount'            => round( $amount, 2 ),
				'commission'        => round( (float) $split['commission'], 2 ),
				'teacher_share'     => round( (float) $split['teacher_earnings'], 2 ),
				'commission_percent'=> isset( $split['commission_percent'] ) ? (float) $split['commission_percent'] : 0.0,
				'payment_status'    => isset( $row['payment_status'] ) ? sanitize_key( $row['payment_status'] ) : '',
				'payment_method'    => isset( $row['payment_method'] ) ? (string) $row['payment_method'] : '',
				'created_at'        => isset( $row['created_at'] ) ? (string) $row['created_at'] : '',
				'date_label'        => ! empty( $row['created_at'] )
					? date_i18n( get_option( 'date_format' ), strtotime( $row['created_at'] ) )
					: '',
				'split'             => $split,
				'raw'               => $row,
			);
		}

		return $out;
	}

	/**
	 * Create a withdrawal request.
	 *
	 * @param array<string, mixed> $data  Request fields.
	 * @param string               $nonce Optional nonce.
	 * @return int|WP_Error Withdrawal ID.
	 */
	public static function create_withdrawal_request( $data, $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Security check failed.', 'gospel-music-mastery' ) );
		}

		$user_id = get_current_user_id();
		if ( ! empty( $data['user_id'] ) && current_user_can( 'manage_options' ) ) {
			$user_id = absint( $data['user_id'] );
		}

		if ( ! self::user_can_manage( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You do not have permission to request a withdrawal.', 'gospel-music-mastery' ) );
		}

		if ( ! self::withdrawals_table_exists() ) {
			if ( class_exists( 'GMM_Database' ) ) {
				GMM_Database::install();
			}
			if ( ! self::withdrawals_table_exists() ) {
				return new WP_Error( 'gmm_db', __( 'Withdrawals table is not available.', 'gospel-music-mastery' ) );
			}
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return new WP_Error( 'gmm_no_teacher', __( 'Teacher profile not found.', 'gospel-music-mastery' ) );
		}

		$amount = isset( $data['amount'] ) ? round( (float) $data['amount'], 2 ) : 0.0;
		$method = isset( $data['payment_method'] ) ? sanitize_text_field( (string) $data['payment_method'] ) : '';
		$account = isset( $data['account_details'] ) ? sanitize_textarea_field( (string) $data['account_details'] ) : '';

		if ( '' === $account ) {
			$account = self::get_default_account_details( $user_id );
		}

		$min = self::get_minimum_withdrawal();

		if ( $amount <= 0 ) {
			return new WP_Error( 'gmm_amount', __( 'Please enter a valid withdrawal amount.', 'gospel-music-mastery' ) );
		}
		if ( $amount < $min ) {
			return new WP_Error(
				'gmm_min',
				sprintf(
					/* translators: %s: minimum amount */
					__( 'Minimum withdrawal amount is $%s.', 'gospel-music-mastery' ),
					number_format_i18n( $min, 2 )
				)
			);
		}
		if ( '' === $method ) {
			return new WP_Error( 'gmm_method', __( 'Please select a payment method.', 'gospel-music-mastery' ) );
		}
		if ( '' === trim( $account ) ) {
			return new WP_Error( 'gmm_account', __( 'Account details are required.', 'gospel-music-mastery' ) );
		}

		$earnings = self::get_earnings( $user_id );
		$available = isset( $earnings['available_balance'] ) ? (float) $earnings['available_balance'] : 0.0;

		if ( $amount > $available + 0.001 ) {
			return new WP_Error(
				'gmm_balance',
				sprintf(
					/* translators: %s: available balance */
					__( 'Insufficient available balance. You can withdraw up to $%s.', 'gospel-music-mastery' ),
					number_format_i18n( $available, 2 )
				)
			);
		}

		$now = current_time( 'mysql' );

		global $wpdb;
		$table = GMM_Database::table( 'withdrawals' );

		$inserted = $wpdb->insert(
			$table,
			array(
				'teacher_id'      => $teacher_id,
				'amount'          => $amount,
				'payment_method'  => $method,
				'account_details' => $account,
				'status'          => self::STATUS_PENDING,
				'admin_note'      => '',
				'created_at'      => $now,
				'updated_at'      => $now,
			),
			array( '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( false === $inserted ) {
			return new WP_Error( 'gmm_db', __( 'Could not create withdrawal request.', 'gospel-music-mastery' ) );
		}

		$withdrawal_id = (int) $wpdb->insert_id;
		$row           = self::get_withdrawal_row( $withdrawal_id );

		self::flush_caches( $user_id );

		/**
		 * Fires when a teacher requests a withdrawal.
		 *
		 * @param int                  $withdrawal_id Withdrawal ID.
		 * @param array<string, mixed> $row           Row data.
		 * @param int                  $user_id       WP user ID.
		 */
		do_action( 'gmm_withdrawal_requested', $withdrawal_id, is_array( $row ) ? $row : array(), $user_id );

		return $withdrawal_id;
	}

	/**
	 * Withdrawal history for a teacher.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_withdrawal_history( $user_id = 0, $args = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id || ! is_user_logged_in() ) {
			return array();
		}

		if ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== $user_id ) {
			return array();
		}

		if ( ! class_exists( 'GMM_Teacher' ) || ! GMM_Teacher::can_view_profile( $user_id ) ) {
			return array();
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id || ! self::withdrawals_table_exists() ) {
			return array();
		}

		global $wpdb;
		$table = GMM_Database::table( 'withdrawals' );

		$sql    = "SELECT * FROM {$table} WHERE teacher_id = %d";
		$params = array( $teacher_id );

		if ( ! empty( $args['status'] ) ) {
			$status = sanitize_key( $args['status'] );
			// UI filter aliases.
			if ( 'completed' === $status ) {
				$status = self::STATUS_PAID;
			} elseif ( 'failed' === $status ) {
				$status = self::STATUS_REJECTED;
			}
			if ( in_array( $status, self::WITHDRAWAL_STATUSES, true ) ) {
				$sql     .= ' AND status = %s';
				$params[] = $status;
			}
		}

		$sql     .= ' ORDER BY created_at DESC LIMIT %d';
		$params[] = isset( $args['limit'] ) ? min( absint( $args['limit'] ), 200 ) : 100;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );
		if ( ! is_array( $rows ) ) {
			return array();
		}

		$out = array();
		foreach ( $rows as $row ) {
			$out[] = self::format_withdrawal_row( $row );
		}

		return $out;
	}

	/**
	 * Single withdrawal row by ID.
	 *
	 * @param int $withdrawal_id ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_withdrawal_row( $withdrawal_id ) {
		$withdrawal_id = absint( $withdrawal_id );
		if ( ! $withdrawal_id || ! self::withdrawals_table_exists() ) {
			return null;
		}

		global $wpdb;
		$table = GMM_Database::table( 'withdrawals' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $withdrawal_id ),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Format withdrawal for UI.
	 *
	 * @param array<string, mixed> $row DB row.
	 * @return array<string, mixed>
	 */
	public static function format_withdrawal_row( $row ) {
		$status = isset( $row['status'] ) ? sanitize_key( $row['status'] ) : self::STATUS_PENDING;
		$amount = isset( $row['amount'] ) ? (float) $row['amount'] : 0.0;
		$created = isset( $row['created_at'] ) ? (string) $row['created_at'] : '';

		$ui_status = self::map_status_to_ui_filter( $status );
		$labels    = self::status_labels();

		return array(
			'id'              => isset( $row['id'] ) ? absint( $row['id'] ) : 0,
			'teacher_id'      => isset( $row['teacher_id'] ) ? absint( $row['teacher_id'] ) : 0,
			'amount'          => round( $amount, 2 ),
			'amount_label'    => '$' . number_format_i18n( $amount, 2 ),
			'payment_method'  => isset( $row['payment_method'] ) ? (string) $row['payment_method'] : '',
			'account_details' => isset( $row['account_details'] ) ? (string) $row['account_details'] : '',
			'status'          => $status,
			'status_label'    => isset( $labels[ $status ] ) ? $labels[ $status ] : ucfirst( $status ),
			'ui_filter'       => $ui_status,
			'badge_class'     => self::status_badge_class( $status ),
			'admin_note'      => isset( $row['admin_note'] ) ? (string) $row['admin_note'] : '',
			'admin_response'  => isset( $row['admin_note'] ) && '' !== trim( (string) $row['admin_note'] )
				? (string) $row['admin_note']
				: '',
			'created_at'      => $created,
			'updated_at'      => isset( $row['updated_at'] ) ? (string) $row['updated_at'] : '',
			'date_label'      => $created ? date_i18n( get_option( 'date_format' ), strtotime( $created ) ) : '',
		);
	}

	/**
	 * Map DB status to frozen tab filter (completed|pending|failed).
	 *
	 * @param string $status DB status.
	 * @return string
	 */
	public static function map_status_to_ui_filter( $status ) {
		$status = sanitize_key( $status );
		if ( self::STATUS_PAID === $status ) {
			return 'completed';
		}
		if ( self::STATUS_REJECTED === $status ) {
			return 'failed';
		}
		return 'pending';
	}

	/**
	 * Badge class for frozen UI.
	 *
	 * @param string $status Status.
	 * @return string
	 */
	public static function status_badge_class( $status ) {
		$status = sanitize_key( $status );
		if ( self::STATUS_PAID === $status || self::STATUS_APPROVED === $status ) {
			return 'is-confirmed';
		}
		if ( self::STATUS_REJECTED === $status ) {
			return 'is-cancelled';
		}
		return 'is-pending';
	}

	/**
	 * Human labels.
	 *
	 * @return array<string, string>
	 */
	public static function status_labels() {
		return array(
			self::STATUS_PENDING  => __( 'Pending', 'gospel-music-mastery' ),
			self::STATUS_APPROVED => __( 'Approved', 'gospel-music-mastery' ),
			self::STATUS_REJECTED => __( 'Rejected', 'gospel-music-mastery' ),
			self::STATUS_PAID     => __( 'Completed', 'gospel-music-mastery' ),
		);
	}

	/**
	 * Admin: approve withdrawal (prepared for admin UI).
	 *
	 * @param int    $withdrawal_id ID.
	 * @param string $note          Optional admin note.
	 * @param string $nonce         Optional nonce (admin may use own).
	 * @return true|WP_Error
	 */
	public static function approve_withdrawal( $withdrawal_id, $note = '', $nonce = '' ) {
		return self::admin_update_status( $withdrawal_id, self::STATUS_APPROVED, $note, $nonce, 'gmm_withdrawal_approved' );
	}

	/**
	 * Admin: reject withdrawal.
	 *
	 * @param int    $withdrawal_id ID.
	 * @param string $note          Optional reason.
	 * @param string $nonce         Optional nonce.
	 * @return true|WP_Error
	 */
	public static function reject_withdrawal( $withdrawal_id, $note = '', $nonce = '' ) {
		return self::admin_update_status( $withdrawal_id, self::STATUS_REJECTED, $note, $nonce, 'gmm_withdrawal_rejected' );
	}

	/**
	 * Admin: mark withdrawal as paid.
	 *
	 * @param int    $withdrawal_id ID.
	 * @param string $note          Optional note.
	 * @param string $nonce         Optional nonce.
	 * @return true|WP_Error
	 */
	public static function mark_withdrawal_paid( $withdrawal_id, $note = '', $nonce = '' ) {
		return self::admin_update_status( $withdrawal_id, self::STATUS_PAID, $note, $nonce, 'gmm_withdrawal_paid' );
	}

	/**
	 * Shared admin status update.
	 *
	 * @param int    $withdrawal_id ID.
	 * @param string $status        New status.
	 * @param string $note          Admin note.
	 * @param string $nonce         Unused reserved.
	 * @param string $hook          Action hook name.
	 * @return true|WP_Error
	 */
	private static function admin_update_status( $withdrawal_id, $status, $note, $nonce, $hook ) {
		unset( $nonce );

		if ( ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'Administrator access required.', 'gospel-music-mastery' ) );
		}

		$withdrawal_id = absint( $withdrawal_id );
		$status        = sanitize_key( $status );

		if ( ! $withdrawal_id || ! in_array( $status, self::WITHDRAWAL_STATUSES, true ) ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid withdrawal update.', 'gospel-music-mastery' ) );
		}

		$row = self::get_withdrawal_row( $withdrawal_id );
		if ( ! $row ) {
			return new WP_Error( 'gmm_missing', __( 'Withdrawal not found.', 'gospel-music-mastery' ) );
		}

		$update = array(
			'status'     => $status,
			'updated_at' => current_time( 'mysql' ),
		);
		$formats = array( '%s', '%s' );

		if ( '' !== (string) $note ) {
			$update['admin_note'] = sanitize_textarea_field( (string) $note );
			$formats[]            = '%s';
		}

		global $wpdb;
		$table  = GMM_Database::table( 'withdrawals' );
		$result = $wpdb->update( $table, $update, array( 'id' => $withdrawal_id ), $formats, array( '%d' ) );

		if ( false === $result ) {
			return new WP_Error( 'gmm_db', __( 'Could not update withdrawal.', 'gospel-music-mastery' ) );
		}

		$fresh = self::get_withdrawal_row( $withdrawal_id );
		$wp_user = 0;
		if ( ! empty( $row['teacher_id'] ) && class_exists( 'GMM_Teacher' ) ) {
			$wp_user = self::teacher_user_id( absint( $row['teacher_id'] ) );
			if ( $wp_user ) {
				self::flush_caches( $wp_user );
			}
		}

		/**
		 * Dynamic admin withdrawal hooks.
		 *
		 * @param int                  $withdrawal_id ID.
		 * @param array<string, mixed> $fresh         Updated row.
		 */
		do_action( $hook, $withdrawal_id, is_array( $fresh ) ? $fresh : array() );

		return true;
	}

	/**
	 * Resolve WP user ID from teacher row ID.
	 *
	 * @param int $teacher_id Teacher ID.
	 * @return int
	 */
	private static function teacher_user_id( $teacher_id ) {
		$teacher_id = absint( $teacher_id );
		if ( ! $teacher_id ) {
			return 0;
		}
		global $wpdb;
		$table = GMM_Database::table( 'teachers' );
		return (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT user_id FROM {$table} WHERE id = %d", $teacher_id )
		);
	}

	/**
	 * AJAX: create withdrawal request.
	 *
	 * @return void
	 */
	public function ajax_withdrawal_request() {
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ) : '';
		if ( ! self::verify_nonce( $nonce ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'gospel-music-mastery' ) ), 403 );
		}

		if ( ! self::user_can_manage() ) {
			wp_send_json_error( array( 'message' => __( 'You do not have permission to request a withdrawal.', 'gospel-music-mastery' ) ), 403 );
		}

		$result = self::create_withdrawal_request(
			array(
				'amount'          => isset( $_POST['amount'] ) ? wp_unslash( $_POST['amount'] ) : 0,
				'payment_method'  => isset( $_POST['payment_method'] ) ? wp_unslash( $_POST['payment_method'] ) : '',
				'account_details' => isset( $_POST['account_details'] ) ? wp_unslash( $_POST['account_details'] ) : '',
			),
			$nonce
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		$user_id = get_current_user_id();
		wp_send_json_success(
			array(
				'message'            => __( 'Withdrawal request submitted successfully.', 'gospel-music-mastery' ),
				'withdrawal_id'      => (int) $result,
				'earnings'           => self::get_earnings( $user_id ),
				'withdrawal_history' => self::get_withdrawal_history( $user_id ),
			)
		);
	}

	/**
	 * AJAX: refresh earnings summary.
	 *
	 * @return void
	 */
	public function ajax_earnings_refresh() {
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ) : '';
		if ( ! self::verify_nonce( $nonce ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'gospel-music-mastery' ) ), 403 );
		}

		if ( ! self::user_can_manage() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'gospel-music-mastery' ) ), 403 );
		}

		$user_id = get_current_user_id();
		wp_send_json_success(
			array(
				'earnings'           => self::get_earnings( $user_id ),
				'withdrawal_history' => self::get_withdrawal_history( $user_id ),
				'transactions'       => self::get_transactions( $user_id, array( 'limit' => 50 ) ),
			)
		);
	}

	/**
	 * AJAX: withdrawal history (status refresh).
	 *
	 * @return void
	 */
	public function ajax_withdrawal_history() {
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ) : '';
		if ( ! self::verify_nonce( $nonce ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'gospel-music-mastery' ) ), 403 );
		}

		if ( ! self::user_can_manage() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'gospel-music-mastery' ) ), 403 );
		}

		$args = array();
		if ( ! empty( $_POST['status'] ) ) {
			$args['status'] = sanitize_key( wp_unslash( $_POST['status'] ) );
		}

		wp_send_json_success(
			array(
				'withdrawal_history' => self::get_withdrawal_history( get_current_user_id(), $args ),
			)
		);
	}

	/**
	 * AJAX: earnings transactions list.
	 *
	 * @return void
	 */
	public function ajax_transactions() {
		$nonce = isset( $_POST[ self::NONCE_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ) : '';
		if ( ! self::verify_nonce( $nonce ) ) {
			wp_send_json_error( array( 'message' => __( 'Security check failed.', 'gospel-music-mastery' ) ), 403 );
		}

		if ( ! self::user_can_manage() ) {
			wp_send_json_error( array( 'message' => __( 'Permission denied.', 'gospel-music-mastery' ) ), 403 );
		}

		wp_send_json_success(
			array(
				'transactions' => self::get_transactions( get_current_user_id(), array( 'limit' => 50 ) ),
			)
		);
	}

	/**
	 * Enqueue withdrawals/earnings script.
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
		if ( ! has_shortcode( $content, 'gmm_teacher_withdrawals' ) && false === strpos( $content, 'gmm_teacher_withdrawals' ) ) {
			return;
		}

		$version = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';
		wp_enqueue_script(
			'gmm-teacher-earnings',
			GMM_URL . 'assets/js/gmm-teacher-earnings.js',
			array( 'gmm-core-script' ),
			$version,
			true
		);

		$vars = self::get_template_vars();
		wp_localize_script(
			'gmm-teacher-earnings',
			'GMM_TEACHER_EARNINGS',
			array(
				'nonce'      => wp_create_nonce( self::NONCE_ACTION ),
				'nonceField' => self::NONCE_FIELD,
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'earnings'   => isset( $vars['earnings'] ) ? $vars['earnings'] : self::get_earnings(),
				'minAmount'  => self::get_minimum_withdrawal(),
				'actions'    => array(
					'request'      => 'gmm_teacher_withdrawal_request',
					'refresh'      => 'gmm_teacher_earnings_refresh',
					'history'      => 'gmm_teacher_withdrawal_history',
					'transactions' => 'gmm_teacher_transactions',
				),
				'i18n'       => array(
					'success'   => __( 'Withdrawal request submitted successfully.', 'gospel-music-mastery' ),
					'error'     => __( 'Something went wrong. Please try again.', 'gospel-music-mastery' ),
					'invalid'   => __( 'Please enter a valid withdrawal amount.', 'gospel-music-mastery' ),
					'empty'     => __( 'No withdrawal history available yet.', 'gospel-music-mastery' ),
					'earnEmpty' => __( 'No earnings transactions yet.', 'gospel-music-mastery' ),
				),
			)
		);
	}

	/**
	 * Flush caches when payment changes.
	 *
	 * @param int                  $payment_id Payment ID.
	 * @param array<string, mixed> $row        Payment row.
	 * @return void
	 */
	public function flush_on_payment_change( $payment_id, $row = array() ) {
		unset( $payment_id );
		$user_id = 0;
		if ( is_array( $row ) && ! empty( $row['teacher_id'] ) ) {
			$user_id = self::teacher_user_id( absint( $row['teacher_id'] ) );
		}
		self::flush_caches( $user_id );
	}

	/**
	 * Flush on admin payment status update.
	 *
	 * @param int    $payment_id Payment ID.
	 * @param string $status     Status.
	 * @return void
	 */
	public function flush_on_admin_payment( $payment_id, $status = '' ) {
		unset( $status );
		$payment_id = absint( $payment_id );
		if ( ! $payment_id ) {
			return;
		}
		global $wpdb;
		$table = GMM_Database::table( 'payments' );
		$tid   = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT teacher_id FROM {$table} WHERE id = %d", $payment_id )
		);
		if ( $tid ) {
			self::flush_caches( self::teacher_user_id( $tid ) );
		}
	}

	/**
	 * Flush teacher dashboard cache.
	 *
	 * @param int $user_id WP user ID.
	 * @return void
	 */
	public static function flush_caches( $user_id = 0 ) {
		$user_id = absint( $user_id );
		if ( $user_id && class_exists( 'GMM_Teacher_Dashboard' ) ) {
			GMM_Teacher_Dashboard::flush_cache( $user_id );
		}
		if ( class_exists( 'GMM_Admin_Payments' ) && method_exists( 'GMM_Admin_Payments', 'flush_cache' ) ) {
			GMM_Admin_Payments::flush_cache();
		}
	}

	/**
	 * Render earnings history section (same card/table classes as frozen UI).
	 *
	 * @return string
	 */
	private static function render_earnings_history_section() {
		$transactions = self::get_transactions( get_current_user_id(), array( 'limit' => 50 ) );
		$has          = ! empty( $transactions );

		ob_start();
		?>
						<section class="td-card" id="gmm-earnings-history">
							<div class="td-card-head">
								<div>
									<h3><?php esc_html_e( 'Earnings History', 'gospel-music-mastery' ); ?></h3>
									<p><?php esc_html_e( 'Lesson payments after platform commission.', 'gospel-music-mastery' ); ?></p>
								</div>
							</div>
							<div class="table-responsive">
								<table class="table td-table" id="earnings-transactions-table">
									<thead>
										<tr>
											<th><?php esc_html_e( 'Transaction ID', 'gospel-music-mastery' ); ?></th>
											<th><?php esc_html_e( 'Class', 'gospel-music-mastery' ); ?></th>
											<th><?php esc_html_e( 'Student', 'gospel-music-mastery' ); ?></th>
											<th><?php esc_html_e( 'Amount', 'gospel-music-mastery' ); ?></th>
											<th><?php esc_html_e( 'Commission', 'gospel-music-mastery' ); ?></th>
											<th><?php esc_html_e( 'Your Share', 'gospel-music-mastery' ); ?></th>
											<th><?php esc_html_e( 'Status', 'gospel-music-mastery' ); ?></th>
											<th><?php esc_html_e( 'Date', 'gospel-music-mastery' ); ?></th>
										</tr>
									</thead>
									<tbody id="earnings-transactions-tbody">
<?php if ( $has ) : ?>
<?php foreach ( $transactions as $tx ) : ?>
										<tr>
											<td data-label="<?php esc_attr_e( 'Transaction ID', 'gospel-music-mastery' ); ?>"><?php echo esc_html( $tx['transaction_id'] ); ?></td>
											<td data-label="<?php esc_attr_e( 'Class', 'gospel-music-mastery' ); ?>"><?php echo esc_html( $tx['class_name'] ); ?></td>
											<td data-label="<?php esc_attr_e( 'Student', 'gospel-music-mastery' ); ?>"><?php echo esc_html( $tx['student_name'] ); ?></td>
											<td data-label="<?php esc_attr_e( 'Amount', 'gospel-music-mastery' ); ?>"><strong>$<?php echo esc_html( number_format_i18n( (float) $tx['amount'], 2 ) ); ?></strong></td>
											<td data-label="<?php esc_attr_e( 'Commission', 'gospel-music-mastery' ); ?>">$<?php echo esc_html( number_format_i18n( (float) $tx['commission'], 2 ) ); ?></td>
											<td data-label="<?php esc_attr_e( 'Your Share', 'gospel-music-mastery' ); ?>"><strong>$<?php echo esc_html( number_format_i18n( (float) $tx['teacher_share'], 2 ) ); ?></strong></td>
											<td data-label="<?php esc_attr_e( 'Status', 'gospel-music-mastery' ); ?>"><?php echo esc_html( ucfirst( (string) $tx['payment_status'] ) ); ?></td>
											<td data-label="<?php esc_attr_e( 'Date', 'gospel-music-mastery' ); ?>"><?php echo esc_html( $tx['date_label'] ); ?></td>
										</tr>
<?php endforeach; ?>
<?php endif; ?>
									</tbody>
								</table>
							</div>
							<p class="td-empty-state" id="earnings-transactions-empty"<?php echo $has ? ' hidden' : ''; ?>>
								<?php esc_html_e( 'No earnings transactions yet.', 'gospel-music-mastery' ); ?>
							</p>
						</section>
		<?php
		return (string) ob_get_clean();
	}
}
