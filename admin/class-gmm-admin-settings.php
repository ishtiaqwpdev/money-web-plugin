<?php
/**
 * Basic WordPress Settings API UI for Gospel Music Mastery.
 *
 * Native wp-admin style only. Management UIs stay on plugin dashboard pages.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Admin_Settings
 */
class GMM_Admin_Settings {

	const GROUP = 'gmm_settings_group';
	const PAGE  = 'gmm-settings';

	const OPTION_GENERAL    = 'gmm_general_settings';
	const OPTION_COMMISSION = 'gmm_commission_settings';
	const OPTION_PAYMENT    = 'gmm_payment_settings';
	const OPTION_EMAIL      = 'gmm_email_settings';
	const OPTION_DASHBOARD  = 'gmm_dashboard_settings';
	const OPTION_SECURITY   = 'gmm_security_settings';

	/**
	 * @param GMM_Loader $loader Loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_action( 'admin_init', $instance, 'register_settings' );
	}

	/**
	 * Register options + Settings API sections/fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		$this->register_option( self::OPTION_GENERAL, array( 'GMM_Settings', 'sanitize_general' ), array( 'GMM_Settings', 'default_general' ) );
		$this->register_option( self::OPTION_PAYMENT, array( 'GMM_Settings', 'sanitize_payment' ), array( 'GMM_Settings', 'default_payment' ) );
		$this->register_option( self::OPTION_EMAIL, array( 'GMM_Settings', 'sanitize_email' ), array( 'GMM_Settings', 'default_email' ) );
		$this->register_option( self::OPTION_SECURITY, array( 'GMM_Settings', 'sanitize_security' ), array( 'GMM_Settings', 'default_security' ) );

		// Keep commission option registered for existing payment/earnings code (synced from payment tab).
		$this->register_option( self::OPTION_COMMISSION, array( 'GMM_Settings', 'sanitize_commission' ), array( 'GMM_Settings', 'default_commission' ) );
		// Keep dashboard option registered for BC (not shown in basic settings UI).
		$this->register_option( self::OPTION_DASHBOARD, array( 'GMM_Settings', 'sanitize_dashboard' ), array( 'GMM_Settings', 'default_dashboard' ) );

		/* ---- General ---- */
		add_settings_section( 'gmm_general_section', __( 'General Settings', 'gospel-music-mastery' ), array( $this, 'render_general_section' ), self::PAGE . '-general' );
		add_settings_field( 'gmm_plugin_status', __( 'Plugin Status', 'gospel-music-mastery' ), array( $this, 'render_plugin_status_field' ), self::PAGE . '-general', 'gmm_general_section' );
		add_settings_field( 'gmm_default_currency', __( 'Default Currency', 'gospel-music-mastery' ), array( $this, 'render_default_currency_field' ), self::PAGE . '-general', 'gmm_general_section' );
		add_settings_field( 'gmm_timezone', __( 'Timezone', 'gospel-music-mastery' ), array( $this, 'render_timezone_field' ), self::PAGE . '-general', 'gmm_general_section' );

		/* ---- Payment ---- */
		add_settings_section( 'gmm_payment_section', __( 'Payment Settings', 'gospel-music-mastery' ), array( $this, 'render_payment_section' ), self::PAGE . '-payment' );
		add_settings_field( 'gmm_currency', __( 'Currency', 'gospel-music-mastery' ), array( $this, 'render_currency_field' ), self::PAGE . '-payment', 'gmm_payment_section' );
		add_settings_field( 'gmm_commission_percent', __( 'Commission Percentage', 'gospel-music-mastery' ), array( $this, 'render_commission_field' ), self::PAGE . '-payment', 'gmm_payment_section' );
		add_settings_field( 'gmm_minimum_withdrawal', __( 'Minimum Withdrawal Amount', 'gospel-music-mastery' ), array( $this, 'render_minimum_withdrawal_field' ), self::PAGE . '-payment', 'gmm_payment_section' );
		add_settings_field( 'gmm_payment_mode', __( 'Payment Mode', 'gospel-music-mastery' ), array( $this, 'render_payment_mode_field' ), self::PAGE . '-payment', 'gmm_payment_section' );

		/* ---- Email ---- */
		add_settings_section( 'gmm_email_section', __( 'Email Settings', 'gospel-music-mastery' ), array( $this, 'render_email_section' ), self::PAGE . '-email' );
		add_settings_field( 'gmm_from_name', __( 'Sender Name', 'gospel-music-mastery' ), array( $this, 'render_from_name_field' ), self::PAGE . '-email', 'gmm_email_section' );
		add_settings_field( 'gmm_from_email', __( 'Sender Email', 'gospel-music-mastery' ), array( $this, 'render_from_email_field' ), self::PAGE . '-email', 'gmm_email_section' );
		add_settings_field( 'gmm_emails_enabled', __( 'Enable Email Notifications', 'gospel-music-mastery' ), array( $this, 'render_emails_enabled_field' ), self::PAGE . '-email', 'gmm_email_section' );
		add_settings_field( 'gmm_notify_new_registration', __( 'New Registration', 'gospel-music-mastery' ), array( $this, 'render_notify_new_registration_field' ), self::PAGE . '-email', 'gmm_email_section' );
		add_settings_field( 'gmm_notify_teacher_approval', __( 'Teacher Approval', 'gospel-music-mastery' ), array( $this, 'render_notify_teacher_approval_field' ), self::PAGE . '-email', 'gmm_email_section' );
		add_settings_field( 'gmm_notify_booking_updates', __( 'Booking Updates', 'gospel-music-mastery' ), array( $this, 'render_notify_booking_updates_field' ), self::PAGE . '-email', 'gmm_email_section' );
		add_settings_field( 'gmm_notify_payment_updates', __( 'Payment Updates', 'gospel-music-mastery' ), array( $this, 'render_notify_payment_updates_field' ), self::PAGE . '-email', 'gmm_email_section' );

		/* ---- Security ---- */
		add_settings_section( 'gmm_security_section', __( 'Security Settings', 'gospel-music-mastery' ), array( $this, 'render_security_section' ), self::PAGE . '-security' );
		add_settings_field( 'gmm_enable_nonce_protection', __( 'Enable Nonce Protection', 'gospel-music-mastery' ), array( $this, 'render_enable_nonce_field' ), self::PAGE . '-security', 'gmm_security_section' );
		add_settings_field( 'gmm_enable_user_verification', __( 'Enable User Verification', 'gospel-music-mastery' ), array( $this, 'render_enable_user_verification_field' ), self::PAGE . '-security', 'gmm_security_section' );
		add_settings_field( 'gmm_session_timeout', __( 'Session Timeout (minutes)', 'gospel-music-mastery' ), array( $this, 'render_session_timeout_field' ), self::PAGE . '-security', 'gmm_security_section' );
	}

	/**
	 * Native wp-admin settings screen (not the frozen frontend template).
	 *
	 * @return void
	 */
	public static function render_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to manage these settings.', 'gospel-music-mastery' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Tab navigation only.
		$tab = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : 'general';
		$tabs = self::get_tabs();
		if ( ! isset( $tabs[ $tab ] ) ) {
			$tab = 'general';
		}

		$dashboard_url = self::get_dashboard_url();

		echo '<div class="wrap">';
		echo '<h1>' . esc_html__( 'Gospel Music Mastery Settings', 'gospel-music-mastery' ) . '</h1>';

		echo '<div class="notice notice-info" style="padding:12px 16px;">';
		echo '<p style="margin:0 0 10px;"><strong>' . esc_html__( 'Welcome to Gospel Music Mastery Admin Panel', 'gospel-music-mastery' ) . '</strong></p>';
		echo '<p style="margin:0 0 12px;">' . esc_html__( 'Configure essential plugin options here. Teachers, students, bookings, and payments are managed from the plugin dashboard.', 'gospel-music-mastery' ) . '</p>';
		echo '<p style="margin:0;">';
		echo '<a class="button button-primary" href="' . esc_url( $dashboard_url ) . '">' . esc_html__( 'Open Dashboard', 'gospel-music-mastery' ) . '</a> ';
		echo '<a class="button" href="' . esc_url( $dashboard_url ) . '">' . esc_html__( 'Go to GMM Dashboard', 'gospel-music-mastery' ) . '</a>';
		echo '</p>';
		echo '</div>';

		settings_errors( 'gmm_settings' );

		echo '<h2 class="nav-tab-wrapper">';
		foreach ( $tabs as $slug => $label ) {
			$url   = add_query_arg(
				array(
					'page' => self::PAGE,
					'tab'  => $slug,
				),
				admin_url( 'admin.php' )
			);
			$class = ( $tab === $slug ) ? ' nav-tab-active' : '';
			printf(
				'<a href="%1$s" class="nav-tab%2$s">%3$s</a>',
				esc_url( $url ),
				esc_attr( $class ),
				esc_html( $label )
			);
		}
		echo '</h2>';

		echo '<form method="post" action="options.php">';
		settings_fields( self::GROUP );
		do_settings_sections( self::PAGE . '-' . $tab );
		submit_button( __( 'Save Settings', 'gospel-music-mastery' ) );
		echo '</form>';
		echo '</div>';
	}

	/**
	 * Backward-compatible alias used by older page loaders.
	 *
	 * @return void
	 */
	public static function render_settings_form() {
		self::render_page();
	}

	/**
	 * @return array<string, string>
	 */
	private static function get_tabs() {
		return array(
			'general'  => __( 'General', 'gospel-music-mastery' ),
			'payment'  => __( 'Payment', 'gospel-music-mastery' ),
			'email'    => __( 'Email', 'gospel-music-mastery' ),
			'security' => __( 'Security', 'gospel-music-mastery' ),
		);
	}

	/**
	 * Link to plugin admin dashboard (WP menu or frontend shortcode page).
	 *
	 * @return string
	 */
	private static function get_dashboard_url() {
		$url = '';
		if ( function_exists( 'menu_page_url' ) ) {
			$url = menu_page_url( 'gmm-dashboard', false );
		}
		if ( ! $url && function_exists( 'gmm_get_page_link' ) ) {
			$url = gmm_get_page_link( 'admin_dashboard' );
		}
		if ( ! $url ) {
			$url = admin_url( 'admin.php?page=gmm-dashboard' );
		}
		return $url;
	}

	/**
	 * @param string   $option   Option key.
	 * @param callable $sanitize Sanitize callback.
	 * @param callable $default  Default callback.
	 * @return void
	 */
	private function register_option( $option, $sanitize, $default ) {
		register_setting(
			self::GROUP,
			$option,
			array(
				'type'              => 'array',
				'sanitize_callback' => $sanitize,
				'default'           => call_user_func( $default ),
				'show_in_rest'      => false,
			)
		);
	}

	/* ---- Sections ---- */

	/** @return void */
	public function render_general_section() {
		echo '<p class="description">' . esc_html__( 'Core plugin status and regional defaults.', 'gospel-music-mastery' ) . '</p>';
	}

	/** @return void */
	public function render_payment_section() {
		echo '<p class="description">' . esc_html__( 'Currency, commission, and payment mode. Gateway API keys are not configured here.', 'gospel-music-mastery' ) . '</p>';
	}

	/** @return void */
	public function render_email_section() {
		echo '<p class="description">' . esc_html__( 'Sender identity and notification toggles.', 'gospel-music-mastery' ) . '</p>';
	}

	/** @return void */
	public function render_security_section() {
		echo '<p class="description">' . esc_html__( 'Basic security switches for the plugin.', 'gospel-music-mastery' ) . '</p>';
	}

	/* ---- General fields ---- */

	/** @return void */
	public function render_plugin_status_field() {
		$opts = GMM_Settings::get_group( self::OPTION_GENERAL );
		$this->select_input(
			self::OPTION_GENERAL,
			'plugin_status',
			isset( $opts['plugin_status'] ) ? $opts['plugin_status'] : 'enabled',
			array(
				'enabled'  => __( 'Enabled', 'gospel-music-mastery' ),
				'disabled' => __( 'Disabled', 'gospel-music-mastery' ),
			),
			'gmm_plugin_status'
		);
	}

	/** @return void */
	public function render_default_currency_field() {
		$opts = GMM_Settings::get_group( self::OPTION_GENERAL );
		$value = isset( $opts['default_currency'] ) ? $opts['default_currency'] : 'USD';
		$this->text_input( self::OPTION_GENERAL, 'default_currency', $value, 'gmm_default_currency', 8 );
		echo '<p class="description">' . esc_html__( 'Example: USD', 'gospel-music-mastery' ) . '</p>';
	}

	/** @return void */
	public function render_timezone_field() {
		$opts = GMM_Settings::get_group( self::OPTION_GENERAL );
		$current = isset( $opts['timezone'] ) ? $opts['timezone'] : 'UTC';
		$tzs  = timezone_identifiers_list();
		echo '<select name="' . esc_attr( self::OPTION_GENERAL ) . '[timezone]" id="gmm_timezone">';
		foreach ( $tzs as $tz ) {
			printf(
				'<option value="%1$s" %2$s>%1$s</option>',
				esc_attr( $tz ),
				selected( $current, $tz, false )
			);
		}
		echo '</select>';
	}

	/* ---- Payment fields ---- */

	/** @return void */
	public function render_currency_field() {
		$opts = GMM_Settings::get_group( self::OPTION_PAYMENT );
		$value = isset( $opts['currency'] ) ? $opts['currency'] : 'USD';
		$this->text_input( self::OPTION_PAYMENT, 'currency', $value, 'gmm_currency', 8 );
	}

	/** @return void */
	public function render_commission_field() {
		$payment = GMM_Settings::get_group( self::OPTION_PAYMENT );
		$commission = GMM_Settings::get_group( self::OPTION_COMMISSION );
		$value = isset( $payment['commission_percent'] )
			? $payment['commission_percent']
			: ( isset( $commission['commission_percent'] ) ? $commission['commission_percent'] : 10 );
		printf(
			'<input type="number" class="small-text" name="%1$s[commission_percent]" id="gmm_commission_percent" value="%2$s" min="0" max="100" step="0.01" />',
			esc_attr( self::OPTION_PAYMENT ),
			esc_attr( (string) $value )
		);
		echo '<p class="description">' . esc_html__( 'Platform commission percentage (0–100).', 'gospel-music-mastery' ) . '</p>';
	}

	/** @return void */
	public function render_minimum_withdrawal_field() {
		$payment = GMM_Settings::get_group( self::OPTION_PAYMENT );
		$commission = GMM_Settings::get_group( self::OPTION_COMMISSION );
		$value = isset( $payment['minimum_withdrawal'] )
			? $payment['minimum_withdrawal']
			: ( isset( $commission['minimum_withdrawal'] ) ? $commission['minimum_withdrawal'] : 50 );
		printf(
			'<input type="number" class="small-text" name="%1$s[minimum_withdrawal]" id="gmm_minimum_withdrawal" value="%2$s" min="0" step="0.01" />',
			esc_attr( self::OPTION_PAYMENT ),
			esc_attr( (string) $value )
		);
	}

	/** @return void */
	public function render_payment_mode_field() {
		$opts = GMM_Settings::get_group( self::OPTION_PAYMENT );
		$this->select_input(
			self::OPTION_PAYMENT,
			'payment_mode',
			isset( $opts['payment_mode'] ) ? $opts['payment_mode'] : 'test',
			array(
				'test' => __( 'Test', 'gospel-music-mastery' ),
				'live' => __( 'Live', 'gospel-music-mastery' ),
			),
			'gmm_payment_mode'
		);
	}

	/* ---- Email fields ---- */

	/** @return void */
	public function render_from_name_field() {
		$opts = GMM_Settings::get_group( self::OPTION_EMAIL );
		$this->text_input( self::OPTION_EMAIL, 'from_name', isset( $opts['from_name'] ) ? $opts['from_name'] : '', 'gmm_from_name' );
	}

	/** @return void */
	public function render_from_email_field() {
		$opts = GMM_Settings::get_group( self::OPTION_EMAIL );
		printf(
			'<input type="email" class="regular-text" name="%1$s[from_email]" id="gmm_from_email" value="%2$s" />',
			esc_attr( self::OPTION_EMAIL ),
			esc_attr( isset( $opts['from_email'] ) ? $opts['from_email'] : '' )
		);
	}

	/** @return void */
	public function render_emails_enabled_field() {
		$opts = GMM_Settings::get_group( self::OPTION_EMAIL );
		$this->yes_no_select( self::OPTION_EMAIL, 'emails_enabled', isset( $opts['emails_enabled'] ) ? $opts['emails_enabled'] : 'yes', 'gmm_emails_enabled' );
	}

	/** @return void */
	public function render_notify_new_registration_field() {
		$this->preference_select( 'new_registration', 'gmm_notify_new_registration' );
	}

	/** @return void */
	public function render_notify_teacher_approval_field() {
		$this->preference_select( 'teacher_approval', 'gmm_notify_teacher_approval' );
	}

	/** @return void */
	public function render_notify_booking_updates_field() {
		$this->preference_select( 'booking_created', 'gmm_notify_booking_updates' );
	}

	/** @return void */
	public function render_notify_payment_updates_field() {
		$this->preference_select( 'payment_completed', 'gmm_notify_payment_updates' );
	}

	/* ---- Security fields ---- */

	/** @return void */
	public function render_enable_nonce_field() {
		$opts = GMM_Settings::get_group( self::OPTION_SECURITY );
		$this->yes_no_select( self::OPTION_SECURITY, 'enable_nonce_protection', isset( $opts['enable_nonce_protection'] ) ? $opts['enable_nonce_protection'] : 'yes', 'gmm_enable_nonce_protection' );
	}

	/** @return void */
	public function render_enable_user_verification_field() {
		$opts = GMM_Settings::get_group( self::OPTION_SECURITY );
		$this->yes_no_select( self::OPTION_SECURITY, 'enable_user_verification', isset( $opts['enable_user_verification'] ) ? $opts['enable_user_verification'] : 'yes', 'gmm_enable_user_verification' );
	}

	/** @return void */
	public function render_session_timeout_field() {
		$opts = GMM_Settings::get_group( self::OPTION_SECURITY );
		printf(
			'<input type="number" class="small-text" name="%1$s[session_timeout]" id="gmm_session_timeout" value="%2$s" min="5" max="1440" step="1" />',
			esc_attr( self::OPTION_SECURITY ),
			esc_attr( isset( $opts['session_timeout'] ) ? (string) $opts['session_timeout'] : '60' )
		);
		echo '<p class="description">' . esc_html__( 'Minutes before an idle session should expire.', 'gospel-music-mastery' ) . '</p>';
	}

	/* ---- BC wrappers ---- */

	/**
	 * @return array<string, mixed>
	 */
	public static function default_general() {
		return GMM_Settings::default_general();
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function default_payment() {
		return GMM_Settings::default_payment();
	}

	/**
	 * @return array<string, mixed>
	 */
	public static function default_email() {
		return GMM_Settings::default_email();
	}

	/**
	 * @param mixed $input Raw.
	 * @return array<string, mixed>
	 */
	public function sanitize_general( $input ) {
		return GMM_Settings::sanitize_general( $input );
	}

	/**
	 * @param mixed $input Raw.
	 * @return array<string, mixed>
	 */
	public function sanitize_payment( $input ) {
		return GMM_Settings::sanitize_payment( $input );
	}

	/**
	 * @param mixed $input Raw.
	 * @return array<string, mixed>
	 */
	public function sanitize_email( $input ) {
		return GMM_Settings::sanitize_email( $input );
	}

	/* ---- Field helpers ---- */

	/**
	 * @param string $option Option.
	 * @param string $key    Key.
	 * @param string $value  Value.
	 * @param string $id     Input ID.
	 * @param int    $size   Size.
	 * @return void
	 */
	private function text_input( $option, $key, $value, $id, $size = 40 ) {
		printf(
			'<input type="text" class="regular-text" name="%1$s[%2$s]" id="%3$s" value="%4$s" size="%5$d" />',
			esc_attr( $option ),
			esc_attr( $key ),
			esc_attr( $id ),
			esc_attr( (string) $value ),
			absint( $size )
		);
	}

	/**
	 * @param string                $option  Option.
	 * @param string                $key     Key.
	 * @param string                $current Current.
	 * @param array<string, string> $choices Choices.
	 * @param string                $id      ID.
	 * @return void
	 */
	private function select_input( $option, $key, $current, $choices, $id ) {
		echo '<select name="' . esc_attr( $option ) . '[' . esc_attr( $key ) . ']" id="' . esc_attr( $id ) . '">';
		foreach ( $choices as $value => $label ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( $value ),
				selected( (string) $current, (string) $value, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}

	/**
	 * @param string $option  Option.
	 * @param string $key     Key.
	 * @param string $current Current.
	 * @param string $id      ID.
	 * @return void
	 */
	private function yes_no_select( $option, $key, $current, $id ) {
		$this->select_input(
			$option,
			$key,
			$current,
			array(
				'yes' => __( 'Yes', 'gospel-music-mastery' ),
				'no'  => __( 'No', 'gospel-music-mastery' ),
			),
			$id
		);
	}

	/**
	 * @param string $pref_key Preference key.
	 * @param string $id       Field ID.
	 * @return void
	 */
	private function preference_select( $pref_key, $id ) {
		$opts = GMM_Settings::get_group( self::OPTION_EMAIL );
		$prefs = isset( $opts['preferences'] ) && is_array( $opts['preferences'] ) ? $opts['preferences'] : array();
		$current = isset( $prefs[ $pref_key ] ) ? $prefs[ $pref_key ] : 'yes';
		echo '<select name="' . esc_attr( self::OPTION_EMAIL ) . '[preferences][' . esc_attr( $pref_key ) . ']" id="' . esc_attr( $id ) . '">';
		printf( '<option value="yes" %s>%s</option>', selected( $current, 'yes', false ), esc_html__( 'Yes', 'gospel-music-mastery' ) );
		printf( '<option value="no" %s>%s</option>', selected( $current, 'no', false ), esc_html__( 'No', 'gospel-music-mastery' ) );
		echo '</select>';
	}
}
