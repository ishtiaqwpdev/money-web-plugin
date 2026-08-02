<?php
/**
 * Central plugin settings and configuration.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Settings
 *
 * Owns option defaults, sanitization, retrieval, and activation defaults.
 */
class GMM_Settings {

	const GROUP = 'gmm_settings_group';

	const OPTION_GENERAL     = 'gmm_general_settings';
	const OPTION_COMMISSION  = 'gmm_commission_settings';
	const OPTION_PAYMENT     = 'gmm_payment_settings';
	const OPTION_EMAIL       = 'gmm_email_settings';
	const OPTION_DASHBOARD   = 'gmm_dashboard_settings';
	const OPTION_SECURITY    = 'gmm_security_settings';

	/**
	 * Flat key aliases for gmm_get_setting().
	 *
	 * @var array<string, array{0:string,1:string}>
	 */
	const ALIASES = array(
		'website_name'              => array( self::OPTION_GENERAL, 'site_name' ),
		'site_name'                 => array( self::OPTION_GENERAL, 'site_name' ),
		'plugin_status'             => array( self::OPTION_GENERAL, 'plugin_status' ),
		'default_dashboard'         => array( self::OPTION_GENERAL, 'default_dashboard' ),
		'default_currency'          => array( self::OPTION_GENERAL, 'default_currency' ),
		'timezone'                  => array( self::OPTION_GENERAL, 'timezone' ),
		'commission_rate'           => array( self::OPTION_COMMISSION, 'commission_percent' ),
		'commission_percent'        => array( self::OPTION_COMMISSION, 'commission_percent' ),
		'minimum_withdrawal'        => array( self::OPTION_COMMISSION, 'minimum_withdrawal' ),
		'min_withdrawal'            => array( self::OPTION_COMMISSION, 'minimum_withdrawal' ),
		'teacher_payout_status'     => array( self::OPTION_COMMISSION, 'teacher_payout_status' ),
		'payment_mode'              => array( self::OPTION_PAYMENT, 'payment_mode' ),
		'currency'                  => array( self::OPTION_PAYMENT, 'currency' ),
		'payment_provider'          => array( self::OPTION_PAYMENT, 'payment_provider' ),
		'stripe_enabled'            => array( self::OPTION_PAYMENT, 'stripe_enabled' ),
		'paypal_enabled'            => array( self::OPTION_PAYMENT, 'paypal_enabled' ),
		'sender_name'               => array( self::OPTION_EMAIL, 'from_name' ),
		'from_name'                 => array( self::OPTION_EMAIL, 'from_name' ),
		'sender_email'              => array( self::OPTION_EMAIL, 'from_email' ),
		'from_email'                => array( self::OPTION_EMAIL, 'from_email' ),
		'emails_enabled'            => array( self::OPTION_EMAIL, 'emails_enabled' ),
		'enable_email_notifications'=> array( self::OPTION_EMAIL, 'emails_enabled' ),
		'enable_charts'             => array( self::OPTION_DASHBOARD, 'enable_charts' ),
		'enable_animations'         => array( self::OPTION_DASHBOARD, 'enable_animations' ),
		'items_per_page'            => array( self::OPTION_DASHBOARD, 'items_per_page' ),
		'enable_nonce_protection'   => array( self::OPTION_SECURITY, 'enable_nonce_protection' ),
		'enable_user_verification'  => array( self::OPTION_SECURITY, 'enable_user_verification' ),
		'session_timeout'           => array( self::OPTION_SECURITY, 'session_timeout' ),
	);

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_filter( 'option_page_capability_' . self::GROUP, $instance, 'option_capability' );
		$loader->add_action( 'plugins_loaded', $instance, 'maybe_ensure_defaults', 5 );
	}

	/**
	 * Only administrators may save settings.
	 *
	 * @return string
	 */
	public function option_capability() {
		return 'manage_options';
	}

	/**
	 * Ensure defaults exist after updates (non-destructive).
	 *
	 * @return void
	 */
	public function maybe_ensure_defaults() {
		self::ensure_defaults();
	}

	/**
	 * Whether current user can manage settings.
	 *
	 * @return bool
	 */
	public static function can_manage() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * All option keys.
	 *
	 * @return array<int, string>
	 */
	public static function get_option_keys() {
		return array(
			self::OPTION_GENERAL,
			self::OPTION_COMMISSION,
			self::OPTION_PAYMENT,
			self::OPTION_EMAIL,
			self::OPTION_DASHBOARD,
			self::OPTION_SECURITY,
		);
	}

	/**
	 * Defaults map keyed by option name.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_all_defaults() {
		return array(
			self::OPTION_GENERAL    => self::default_general(),
			self::OPTION_COMMISSION => self::default_commission(),
			self::OPTION_PAYMENT    => self::default_payment(),
			self::OPTION_EMAIL      => self::default_email(),
			self::OPTION_DASHBOARD  => self::default_dashboard(),
			self::OPTION_SECURITY   => self::default_security(),
		);
	}

	/**
	 * Create default options on activation without overwriting existing values.
	 *
	 * @return void
	 */
	public static function ensure_defaults() {
		foreach ( self::get_all_defaults() as $option => $defaults ) {
			$stored = get_option( $option, null );

			if ( null === $stored || false === $stored ) {
				add_option( $option, $defaults, '', false );
				continue;
			}

			if ( ! is_array( $stored ) ) {
				continue;
			}

			// Merge so new keys appear; existing user values win.
			$merged = self::array_merge_recursive_distinct( $defaults, $stored );
			if ( $merged !== $stored ) {
				update_option( $option, $merged, false );
			}
		}

		// Keep payment.currency aligned with general.default_currency when payment currency empty.
		$general = self::get_group( self::OPTION_GENERAL );
		$payment = self::get_group( self::OPTION_PAYMENT );
		if ( empty( $payment['currency'] ) && ! empty( $general['default_currency'] ) ) {
			$payment['currency'] = $general['default_currency'];
			update_option( self::OPTION_PAYMENT, $payment, false );
		}

		// Sync legacy payment commission into commission option if commission missing stored value.
		$commission = get_option( self::OPTION_COMMISSION, null );
		$payment    = get_option( self::OPTION_PAYMENT, array() );
		if ( is_array( $payment ) && isset( $payment['commission_percent'] ) && is_array( $commission ) ) {
			// Prefer dedicated commission option; leave as-is.
		}
	}

	/**
	 * Get a full settings group with defaults applied.
	 *
	 * @param string $option Option key.
	 * @return array<string, mixed>
	 */
	public static function get_group( $option ) {
		$option   = sanitize_key( (string) $option );
		$defaults = self::get_all_defaults();
		if ( ! isset( $defaults[ $option ] ) ) {
			$stored = get_option( $option, array() );
			return is_array( $stored ) ? $stored : array();
		}
		$stored = get_option( $option, array() );
		$stored = is_array( $stored ) ? $stored : array();
		return self::array_merge_recursive_distinct( $defaults[ $option ], $stored );
	}

	/**
	 * Retrieve a setting by alias or dotted path.
	 *
	 * Examples:
	 * - gmm_get_setting( 'commission_rate' )
	 * - gmm_get_setting( 'general.site_name' )
	 * - gmm_get_setting( 'gmm_email_settings.from_email' )
	 *
	 * @param string $key     Alias or dotted path.
	 * @param mixed  $default Default when missing.
	 * @return mixed
	 */
	public static function get( $key, $default = null ) {
		$key = is_string( $key ) ? trim( $key ) : '';

		if ( '' === $key ) {
			return $default;
		}

		// Alias.
		if ( isset( self::ALIASES[ $key ] ) ) {
			list( $option, $field ) = self::ALIASES[ $key ];
			$group = self::get_group( $option );
			return array_key_exists( $field, $group ) ? $group[ $field ] : $default;
		}

		// Dotted path: general.site_name or gmm_general_settings.site_name.
		if ( false !== strpos( $key, '.' ) ) {
			$parts = explode( '.', $key, 2 );
			$opt   = self::normalize_option_key( $parts[0] );
			$field = sanitize_key( $parts[1] );
			$group = self::get_group( $opt );
			return array_key_exists( $field, $group ) ? $group[ $field ] : $default;
		}

		// Whole option group when key is an option name.
		if ( in_array( $key, self::get_option_keys(), true ) ) {
			return self::get_group( $key );
		}

		// Preference under email.
		$email = self::get_group( self::OPTION_EMAIL );
		if ( isset( $email['preferences'][ $key ] ) ) {
			return $email['preferences'][ $key ];
		}

		return $default;
	}

	/**
	 * Normalize short group names to option keys.
	 *
	 * @param string $key Short or full key.
	 * @return string
	 */
	private static function normalize_option_key( $key ) {
		$map = array(
			'general'    => self::OPTION_GENERAL,
			'commission' => self::OPTION_COMMISSION,
			'payment'    => self::OPTION_PAYMENT,
			'email'      => self::OPTION_EMAIL,
			'dashboard'  => self::OPTION_DASHBOARD,
			'security'   => self::OPTION_SECURITY,
		);
		$key = sanitize_key( $key );
		return isset( $map[ $key ] ) ? $map[ $key ] : $key;
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function default_general() {
		$tz = function_exists( 'wp_timezone_string' ) ? wp_timezone_string() : 'UTC';
		if ( ! $tz ) {
			$tz = 'UTC';
		}
		return array(
			'site_name'         => 'Gospel Music Mastery',
			'plugin_status'     => 'enabled',
			'default_dashboard' => 'student',
			'default_currency'  => 'USD',
			'timezone'          => sanitize_text_field( $tz ),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function default_commission() {
		return array(
			'commission_percent'    => 10,
			'minimum_withdrawal'    => 50,
			'teacher_payout_status' => 'enabled',
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function default_payment() {
		$commission = self::default_commission();
		return array(
			'payment_mode'         => 'test',
			'currency'             => 'USD',
			'commission_percent'   => isset( $commission['commission_percent'] ) ? $commission['commission_percent'] : 10,
			'minimum_withdrawal'   => isset( $commission['minimum_withdrawal'] ) ? $commission['minimum_withdrawal'] : 50,
			'payment_provider'     => 'stripe',
			'stripe_enabled'       => 'yes',
			'paypal_enabled'       => 'no',
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function default_email() {
		$admin_email = get_option( 'admin_email', '' );
		return array(
			'from_name'       => 'Gospel Music Mastery',
			'from_email'      => is_email( $admin_email ) ? $admin_email : '',
			'emails_enabled'  => 'yes',
			'notify_students' => 'yes',
			'notify_teachers' => 'yes',
			'notify_admins'   => 'yes',
			'preferences'     => array(
				'new_registration'   => 'yes',
				'teacher_approval'   => 'yes',
				'booking_created'    => 'yes',
				'payment_completed'  => 'yes',
				'review_submitted'   => 'yes',
				'student_registration' => 'yes',
				'teacher_registration' => 'yes',
				'teacher_approved'     => 'yes',
				'teacher_rejected'     => 'yes',
				'teacher_application'  => 'yes',
				'booking_confirmed'    => 'yes',
				'booking_cancelled'    => 'yes',
				'lesson_completed'     => 'yes',
				'payment_refunded'     => 'yes',
				'payment_activity'     => 'yes',
				'refund_request'       => 'yes',
			),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function default_dashboard() {
		return array(
			'enable_charts'     => 'yes',
			'enable_animations' => 'yes',
			'items_per_page'    => 20,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function default_security() {
		return array(
			'enable_nonce_protection'  => 'yes',
			'enable_user_verification' => 'yes',
			'session_timeout'          => 60, // Minutes.
		);
	}

	/**
	 * Sanitize general settings.
	 *
	 * @param mixed $input Raw.
	 * @return array<string, mixed>
	 */
	public static function sanitize_general( $input ) {
		if ( ! is_array( $input ) || empty( $input ) ) {
			return self::get_group( self::OPTION_GENERAL );
		}
		$out = self::default_general();
		$out = wp_parse_args( self::get_group( self::OPTION_GENERAL ), $out );

		if ( isset( $input['site_name'] ) ) {
			$out['site_name'] = sanitize_text_field( (string) $input['site_name'] );
		} elseif ( isset( $input['website_name'] ) ) {
			$out['site_name'] = sanitize_text_field( (string) $input['website_name'] );
		}

		if ( isset( $input['plugin_status'] ) ) {
			$status = sanitize_key( (string) $input['plugin_status'] );
			$out['plugin_status'] = in_array( $status, array( 'enabled', 'disabled' ), true ) ? $status : 'enabled';
		}

		if ( isset( $input['default_dashboard'] ) ) {
			$dash = sanitize_key( (string) $input['default_dashboard'] );
			$out['default_dashboard'] = in_array( $dash, array( 'student', 'teacher' ), true ) ? $dash : 'student';
		}

		if ( isset( $input['default_currency'] ) ) {
			$out['default_currency'] = self::sanitize_currency( $input['default_currency'] );
		}

		if ( isset( $input['timezone'] ) ) {
			$tz = sanitize_text_field( (string) $input['timezone'] );
			$out['timezone'] = $tz ? $tz : 'UTC';
		}

		return $out;
	}

	/**
	 * Sanitize commission settings.
	 *
	 * @param mixed $input Raw.
	 * @return array<string, mixed>
	 */
	public static function sanitize_commission( $input ) {
		if ( ! is_array( $input ) || empty( $input ) ) {
			return self::get_group( self::OPTION_COMMISSION );
		}
		$input = is_array( $input ) ? $input : array();
		$out   = self::default_commission();
		$out   = wp_parse_args( self::get_group( self::OPTION_COMMISSION ), $out );

		if ( isset( $input['commission_percent'] ) || isset( $input['commission_rate'] ) ) {
			$raw = isset( $input['commission_percent'] ) ? $input['commission_percent'] : $input['commission_rate'];
			$out['commission_percent'] = self::sanitize_commission_percent( $raw );
		}

		if ( isset( $input['minimum_withdrawal'] ) ) {
			$out['minimum_withdrawal'] = max( 0, round( (float) $input['minimum_withdrawal'], 2 ) );
		}

		if ( isset( $input['teacher_payout_status'] ) ) {
			$status = sanitize_key( (string) $input['teacher_payout_status'] );
			$out['teacher_payout_status'] = in_array( $status, array( 'enabled', 'disabled' ), true ) ? $status : 'enabled';
		}

		// Keep legacy payment option in sync (non-destructive to other payment keys).
		$payment = self::get_group( self::OPTION_PAYMENT );
		$payment['commission_percent'] = $out['commission_percent'];
		update_option( self::OPTION_PAYMENT, $payment, false );

		return $out;
	}

	/**
	 * Sanitize payment settings.
	 *
	 * @param mixed $input Raw.
	 * @return array<string, mixed>
	 */
	public static function sanitize_payment( $input ) {
		if ( ! is_array( $input ) || empty( $input ) ) {
			return self::get_group( self::OPTION_PAYMENT );
		}
		$input = is_array( $input ) ? $input : array();
		$out   = self::default_payment();

		// Preserve existing values when a single tab posts partial fields.
		$existing = self::get_group( self::OPTION_PAYMENT );
		$out      = wp_parse_args( is_array( $existing ) ? $existing : array(), $out );

		$commission = self::get_group( self::OPTION_COMMISSION );
		if ( ! isset( $out['commission_percent'] ) && isset( $commission['commission_percent'] ) ) {
			$out['commission_percent'] = self::sanitize_commission_percent( $commission['commission_percent'] );
		}
		if ( ! isset( $out['minimum_withdrawal'] ) && isset( $commission['minimum_withdrawal'] ) ) {
			$out['minimum_withdrawal'] = max( 0, round( (float) $commission['minimum_withdrawal'], 2 ) );
		}

		if ( isset( $input['payment_mode'] ) ) {
			$mode = sanitize_key( (string) $input['payment_mode'] );
			$out['payment_mode'] = in_array( $mode, array( 'test', 'live' ), true ) ? $mode : 'test';
		}

		if ( isset( $input['currency'] ) ) {
			$out['currency'] = self::sanitize_currency( $input['currency'] );
		}

		if ( isset( $input['payment_provider'] ) ) {
			$provider = sanitize_key( (string) $input['payment_provider'] );
			$out['payment_provider'] = in_array( $provider, array( 'stripe', 'paypal' ), true ) ? $provider : 'stripe';
		}

		foreach ( array( 'stripe_enabled', 'paypal_enabled' ) as $flag ) {
			if ( isset( $input[ $flag ] ) ) {
				$out[ $flag ] = self::sanitize_yes_no( $input[ $flag ] );
			}
		}

		if ( isset( $input['commission_percent'] ) ) {
			$out['commission_percent'] = self::sanitize_commission_percent( $input['commission_percent'] );
		}

		if ( isset( $input['minimum_withdrawal'] ) ) {
			$out['minimum_withdrawal'] = max( 0, round( (float) $input['minimum_withdrawal'], 2 ) );
		}

		// Sync dedicated commission option used by earnings/payment engines.
		$commission_out = self::default_commission();
		$commission_out = wp_parse_args( is_array( $commission ) ? $commission : array(), $commission_out );
		$commission_out['commission_percent'] = isset( $out['commission_percent'] )
			? self::sanitize_commission_percent( $out['commission_percent'] )
			: 10;
		$commission_out['minimum_withdrawal'] = isset( $out['minimum_withdrawal'] )
			? max( 0, round( (float) $out['minimum_withdrawal'], 2 ) )
			: 50;
		update_option( self::OPTION_COMMISSION, $commission_out, false );

		return $out;
	}

	/**
	 * Sanitize email settings.
	 *
	 * @param mixed $input Raw.
	 * @return array<string, mixed>
	 */
	public static function sanitize_email( $input ) {
		if ( ! is_array( $input ) || empty( $input ) ) {
			return self::get_group( self::OPTION_EMAIL );
		}
		$input = is_array( $input ) ? $input : array();
		$out   = self::default_email();
		$existing = self::get_group( self::OPTION_EMAIL );
		if ( is_array( $existing ) ) {
			$out = wp_parse_args( $existing, $out );
			if ( isset( $existing['preferences'] ) && is_array( $existing['preferences'] ) ) {
				$out['preferences'] = wp_parse_args( $existing['preferences'], $out['preferences'] );
			}
		}

		if ( isset( $input['from_name'] ) ) {
			$out['from_name'] = sanitize_text_field( (string) $input['from_name'] );
		} elseif ( isset( $input['sender_name'] ) ) {
			$out['from_name'] = sanitize_text_field( (string) $input['sender_name'] );
		}

		$email_raw = null;
		if ( isset( $input['from_email'] ) ) {
			$email_raw = $input['from_email'];
		} elseif ( isset( $input['sender_email'] ) ) {
			$email_raw = $input['sender_email'];
		}
		if ( null !== $email_raw ) {
			$email = sanitize_email( (string) $email_raw );
			if ( is_email( $email ) ) {
				$out['from_email'] = $email;
			}
		}

		foreach ( array( 'emails_enabled', 'notify_students', 'notify_teachers', 'notify_admins' ) as $flag ) {
			if ( isset( $input[ $flag ] ) ) {
				$out[ $flag ] = self::sanitize_yes_no( $input[ $flag ] );
			}
		}

		// Top-level notification shortcuts from task.
		$pref_map = array(
			'notify_new_registration'  => 'new_registration',
			'notify_teacher_approval'  => 'teacher_approval',
			'notify_booking_created'   => 'booking_created',
			'notify_booking_updates'   => 'booking_created',
			'notify_payment_completed' => 'payment_completed',
			'notify_payment_updates'   => 'payment_completed',
			'notify_review_submitted'  => 'review_submitted',
		);
		foreach ( $pref_map as $field => $pref_key ) {
			if ( isset( $input[ $field ] ) ) {
				$out['preferences'][ $pref_key ] = self::sanitize_yes_no( $input[ $field ] );
			}
		}

		if ( isset( $input['preferences'] ) && is_array( $input['preferences'] ) ) {
			foreach ( $input['preferences'] as $key => $value ) {
				$key = sanitize_key( (string) $key );
				if ( ! $key ) {
					continue;
				}
				$out['preferences'][ $key ] = self::sanitize_yes_no( $value );
			}
		}

		// Keep legacy preference keys aligned with task-facing controls.
		if ( isset( $out['preferences']['new_registration'] ) ) {
			$out['preferences']['student_registration'] = $out['preferences']['new_registration'];
			$out['preferences']['teacher_registration'] = $out['preferences']['new_registration'];
		}
		if ( isset( $out['preferences']['teacher_approval'] ) ) {
			$out['preferences']['teacher_approved'] = $out['preferences']['teacher_approval'];
		}
		if ( isset( $out['preferences']['booking_created'] ) ) {
			$out['preferences']['booking_confirmed'] = $out['preferences']['booking_created'];
			$out['preferences']['booking_cancelled'] = $out['preferences']['booking_created'];
			$out['preferences']['lesson_completed']  = $out['preferences']['booking_created'];
		}
		if ( isset( $out['preferences']['payment_completed'] ) ) {
			$out['preferences']['payment_activity'] = $out['preferences']['payment_completed'];
			$out['preferences']['payment_refunded'] = $out['preferences']['payment_completed'];
		}

		return $out;
	}

	/**
	 * Sanitize dashboard settings.
	 *
	 * @param mixed $input Raw.
	 * @return array<string, mixed>
	 */
	public static function sanitize_dashboard( $input ) {
		if ( ! is_array( $input ) || empty( $input ) ) {
			return self::get_group( self::OPTION_DASHBOARD );
		}
		$input = is_array( $input ) ? $input : array();
		$out   = self::default_dashboard();
		$out   = wp_parse_args( self::get_group( self::OPTION_DASHBOARD ), $out );

		foreach ( array( 'enable_charts', 'enable_animations' ) as $flag ) {
			if ( isset( $input[ $flag ] ) ) {
				$out[ $flag ] = self::sanitize_yes_no( $input[ $flag ] );
			}
		}

		if ( isset( $input['items_per_page'] ) ) {
			$n = absint( $input['items_per_page'] );
			$out['items_per_page'] = in_array( $n, array( 10, 20, 50 ), true ) ? $n : 20;
		}

		return $out;
	}

	/**
	 * Sanitize security settings.
	 *
	 * @param mixed $input Raw.
	 * @return array<string, mixed>
	 */
	public static function sanitize_security( $input ) {
		if ( ! is_array( $input ) || empty( $input ) ) {
			return self::get_group( self::OPTION_SECURITY );
		}
		$input = is_array( $input ) ? $input : array();
		$out   = self::default_security();
		$out   = wp_parse_args( self::get_group( self::OPTION_SECURITY ), $out );

		foreach ( array( 'enable_nonce_protection', 'enable_user_verification' ) as $flag ) {
			if ( isset( $input[ $flag ] ) ) {
				$out[ $flag ] = self::sanitize_yes_no( $input[ $flag ] );
			}
		}

		if ( isset( $input['session_timeout'] ) ) {
			$out['session_timeout'] = max( 5, min( 1440, absint( $input['session_timeout'] ) ) );
		}

		return $out;
	}

	/**
	 * @param mixed $value Raw.
	 * @return float
	 */
	public static function sanitize_commission_percent( $value ) {
		return max( 0, min( 100, (float) $value ) );
	}

	/**
	 * @param mixed $value Raw.
	 * @return string
	 */
	public static function sanitize_currency( $value ) {
		$currency = strtoupper( sanitize_text_field( (string) $value ) );
		$currency = preg_replace( '/[^A-Z]/', '', $currency );
		$currency = substr( (string) $currency, 0, 3 );
		return '' !== $currency ? $currency : 'USD';
	}

	/**
	 * @param mixed $value Raw.
	 * @return string yes|no
	 */
	public static function sanitize_yes_no( $value ) {
		$v = is_bool( $value ) ? ( $value ? 'yes' : 'no' ) : sanitize_key( (string) $value );
		if ( in_array( $v, array( '1', 'true', 'on', 'enabled' ), true ) ) {
			return 'yes';
		}
		return ( 'yes' === $v ) ? 'yes' : 'no';
	}

	/**
	 * Recursive merge where later array wins leaf values; arrays merge by key.
	 *
	 * @param array<string, mixed> $defaults Defaults.
	 * @param array<string, mixed> $stored   Stored.
	 * @return array<string, mixed>
	 */
	private static function array_merge_recursive_distinct( array $defaults, array $stored ) {
		$merged = $defaults;
		foreach ( $stored as $key => $value ) {
			if ( is_array( $value ) && isset( $merged[ $key ] ) && is_array( $merged[ $key ] ) ) {
				$merged[ $key ] = self::array_merge_recursive_distinct( $merged[ $key ], $value );
			} else {
				$merged[ $key ] = $value;
			}
		}
		return $merged;
	}
}
