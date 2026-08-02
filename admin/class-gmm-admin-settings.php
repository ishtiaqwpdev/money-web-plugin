<?php
/**
 * Admin Settings API registration for GMM.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Admin_Settings
 *
 * Registers WordPress Settings API fields. Defaults/sanitize live in GMM_Settings.
 */
class GMM_Admin_Settings {

	const GROUP = 'gmm_settings_group';

	const OPTION_GENERAL    = 'gmm_general_settings';
	const OPTION_COMMISSION = 'gmm_commission_settings';
	const OPTION_PAYMENT    = 'gmm_payment_settings';
	const OPTION_EMAIL      = 'gmm_email_settings';
	const OPTION_DASHBOARD  = 'gmm_dashboard_settings';
	const OPTION_SECURITY   = 'gmm_security_settings';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_action( 'admin_init', $instance, 'register_settings' );
	}

	/**
	 * Register settings, sections, and fields.
	 *
	 * @return void
	 */
	public function register_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			// Still register so options.php can save for admins; capability enforced by option_page_capability.
		}

		$this->register_option( self::OPTION_GENERAL, array( 'GMM_Settings', 'sanitize_general' ), array( 'GMM_Settings', 'default_general' ) );
		$this->register_option( self::OPTION_COMMISSION, array( 'GMM_Settings', 'sanitize_commission' ), array( 'GMM_Settings', 'default_commission' ) );
		$this->register_option( self::OPTION_PAYMENT, array( 'GMM_Settings', 'sanitize_payment' ), array( 'GMM_Settings', 'default_payment' ) );
		$this->register_option( self::OPTION_EMAIL, array( 'GMM_Settings', 'sanitize_email' ), array( 'GMM_Settings', 'default_email' ) );
		$this->register_option( self::OPTION_DASHBOARD, array( 'GMM_Settings', 'sanitize_dashboard' ), array( 'GMM_Settings', 'default_dashboard' ) );
		$this->register_option( self::OPTION_SECURITY, array( 'GMM_Settings', 'sanitize_security' ), array( 'GMM_Settings', 'default_security' ) );

		// General.
		add_settings_section( 'gmm_general_section', __( 'General Settings', 'gospel-music-mastery' ), array( $this, 'render_general_section' ), 'gmm-settings' );
		add_settings_field( 'gmm_site_name', __( 'Website Name', 'gospel-music-mastery' ), array( $this, 'render_site_name_field' ), 'gmm-settings', 'gmm_general_section' );
		add_settings_field( 'gmm_plugin_status', __( 'Plugin Status', 'gospel-music-mastery' ), array( $this, 'render_plugin_status_field' ), 'gmm-settings', 'gmm_general_section' );
		add_settings_field( 'gmm_default_dashboard', __( 'Default Dashboard', 'gospel-music-mastery' ), array( $this, 'render_default_dashboard_field' ), 'gmm-settings', 'gmm_general_section' );
		add_settings_field( 'gmm_default_currency', __( 'Default Currency', 'gospel-music-mastery' ), array( $this, 'render_default_currency_field' ), 'gmm-settings', 'gmm_general_section' );
		add_settings_field( 'gmm_timezone', __( 'Timezone', 'gospel-music-mastery' ), array( $this, 'render_timezone_field' ), 'gmm-settings', 'gmm_general_section' );

		// Commission.
		add_settings_section( 'gmm_commission_section', __( 'Commission Settings', 'gospel-music-mastery' ), array( $this, 'render_commission_section' ), 'gmm-settings' );
		add_settings_field( 'gmm_commission_percent', __( 'Platform Commission (%)', 'gospel-music-mastery' ), array( $this, 'render_commission_field' ), 'gmm-settings', 'gmm_commission_section' );
		add_settings_field( 'gmm_minimum_withdrawal', __( 'Minimum Withdrawal Amount', 'gospel-music-mastery' ), array( $this, 'render_minimum_withdrawal_field' ), 'gmm-settings', 'gmm_commission_section' );
		add_settings_field( 'gmm_teacher_payout_status', __( 'Teacher Payout Status', 'gospel-music-mastery' ), array( $this, 'render_teacher_payout_field' ), 'gmm-settings', 'gmm_commission_section' );

		// Payment.
		add_settings_section( 'gmm_payment_section', __( 'Payment Settings', 'gospel-music-mastery' ), array( $this, 'render_payment_section' ), 'gmm-settings' );
		add_settings_field( 'gmm_payment_mode', __( 'Payment Mode', 'gospel-music-mastery' ), array( $this, 'render_payment_mode_field' ), 'gmm-settings', 'gmm_payment_section' );
		add_settings_field( 'gmm_currency', __( 'Currency', 'gospel-music-mastery' ), array( $this, 'render_currency_field' ), 'gmm-settings', 'gmm_payment_section' );
		add_settings_field( 'gmm_payment_provider', __( 'Payment Provider', 'gospel-music-mastery' ), array( $this, 'render_payment_provider_field' ), 'gmm-settings', 'gmm_payment_section' );
		add_settings_field( 'gmm_stripe_enabled', __( 'Stripe (preparation)', 'gospel-music-mastery' ), array( $this, 'render_stripe_enabled_field' ), 'gmm-settings', 'gmm_payment_section' );
		add_settings_field( 'gmm_paypal_enabled', __( 'PayPal (preparation)', 'gospel-music-mastery' ), array( $this, 'render_paypal_enabled_field' ), 'gmm-settings', 'gmm_payment_section' );

		// Email.
		add_settings_section( 'gmm_email_section', __( 'Email Settings', 'gospel-music-mastery' ), array( $this, 'render_email_section' ), 'gmm-settings' );
		add_settings_field( 'gmm_from_name', __( 'Sender Name', 'gospel-music-mastery' ), array( $this, 'render_from_name_field' ), 'gmm-settings', 'gmm_email_section' );
		add_settings_field( 'gmm_from_email', __( 'Sender Email', 'gospel-music-mastery' ), array( $this, 'render_from_email_field' ), 'gmm-settings', 'gmm_email_section' );
		add_settings_field( 'gmm_emails_enabled', __( 'Enable Email Notifications', 'gospel-music-mastery' ), array( $this, 'render_emails_enabled_field' ), 'gmm-settings', 'gmm_email_section' );
		add_settings_field( 'gmm_notify_new_registration', __( 'New Registration', 'gospel-music-mastery' ), array( $this, 'render_notify_new_registration_field' ), 'gmm-settings', 'gmm_email_section' );
		add_settings_field( 'gmm_notify_teacher_approval', __( 'Teacher Approval', 'gospel-music-mastery' ), array( $this, 'render_notify_teacher_approval_field' ), 'gmm-settings', 'gmm_email_section' );
		add_settings_field( 'gmm_notify_booking_created', __( 'Booking Created', 'gospel-music-mastery' ), array( $this, 'render_notify_booking_created_field' ), 'gmm-settings', 'gmm_email_section' );
		add_settings_field( 'gmm_notify_payment_completed', __( 'Payment Completed', 'gospel-music-mastery' ), array( $this, 'render_notify_payment_completed_field' ), 'gmm-settings', 'gmm_email_section' );
		add_settings_field( 'gmm_notify_review_submitted', __( 'Review Submitted', 'gospel-music-mastery' ), array( $this, 'render_notify_review_submitted_field' ), 'gmm-settings', 'gmm_email_section' );

		// Dashboard.
		add_settings_section( 'gmm_dashboard_section', __( 'Dashboard Settings', 'gospel-music-mastery' ), array( $this, 'render_dashboard_section' ), 'gmm-settings' );
		add_settings_field( 'gmm_enable_charts', __( 'Enable Charts', 'gospel-music-mastery' ), array( $this, 'render_enable_charts_field' ), 'gmm-settings', 'gmm_dashboard_section' );
		add_settings_field( 'gmm_enable_animations', __( 'Enable Animations', 'gospel-music-mastery' ), array( $this, 'render_enable_animations_field' ), 'gmm-settings', 'gmm_dashboard_section' );
		add_settings_field( 'gmm_items_per_page', __( 'Items Per Page', 'gospel-music-mastery' ), array( $this, 'render_items_per_page_field' ), 'gmm-settings', 'gmm_dashboard_section' );

		// Security.
		add_settings_section( 'gmm_security_section', __( 'Security Settings', 'gospel-music-mastery' ), array( $this, 'render_security_section' ), 'gmm-settings' );
		add_settings_field( 'gmm_enable_nonce_protection', __( 'Enable Nonce Protection', 'gospel-music-mastery' ), array( $this, 'render_enable_nonce_field' ), 'gmm-settings', 'gmm_security_section' );
		add_settings_field( 'gmm_enable_user_verification', __( 'Enable User Verification', 'gospel-music-mastery' ), array( $this, 'render_enable_user_verification_field' ), 'gmm-settings', 'gmm_security_section' );
		add_settings_field( 'gmm_session_timeout', __( 'Session Timeout (minutes)', 'gospel-music-mastery' ), array( $this, 'render_session_timeout_field' ), 'gmm-settings', 'gmm_security_section' );
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

	/**
	 * Output Settings API form below frozen admin settings template.
	 *
	 * @return void
	 */
	public static function render_settings_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			echo '<div class="notice notice-error"><p>' . esc_html__( 'You do not have permission to manage these settings.', 'gospel-music-mastery' ) . '</p></div>';
			return;
		}

		echo '<div class="gmm-admin-settings-panel" style="max-width:900px;margin-top:24px;">';
		echo '<h2>' . esc_html__( 'Plugin configuration', 'gospel-music-mastery' ) . '</h2>';
		echo '<form method="post" action="options.php" class="gmm-admin-settings-form">';
		settings_fields( self::GROUP );
		do_settings_sections( 'gmm-settings' );
		submit_button( __( 'Save GMM Settings', 'gospel-music-mastery' ) );
		echo '</form>';
		echo '</div>';
	}

	/* ---- Section descriptions ---- */

	/** @return void */
	public function render_general_section() {
		echo '<p class="description">' . esc_html__( 'Core platform identity and defaults.', 'gospel-music-mastery' ) . '</p>';
	}

	/** @return void */
	public function render_commission_section() {
		echo '<p class="description">' . esc_html__( 'Platform commission and teacher payout rules.', 'gospel-music-mastery' ) . '</p>';
	}

	/** @return void */
	public function render_payment_section() {
		echo '<p class="description">' . esc_html__( 'Payment mode and provider preparation. APIs are not connected yet.', 'gospel-music-mastery' ) . '</p>';
	}

	/** @return void */
	public function render_email_section() {
		echo '<p class="description">' . esc_html__( 'Sender identity and notification toggles.', 'gospel-music-mastery' ) . '</p>';
	}

	/** @return void */
	public function render_dashboard_section() {
		echo '<p class="description">' . esc_html__( 'Dashboard display preferences (no design changes).', 'gospel-music-mastery' ) . '</p>';
	}

	/** @return void */
	public function render_security_section() {
		echo '<p class="description">' . esc_html__( 'Security foundation flags for future enforcement.', 'gospel-music-mastery' ) . '</p>';
	}

	/* ---- Field renderers ---- */

	/** @return void */
	public function render_site_name_field() {
		$opts = GMM_Settings::get_group( self::OPTION_GENERAL );
		$this->text_input( self::OPTION_GENERAL, 'site_name', $opts['site_name'], 'gmm_site_name' );
	}

	/** @return void */
	public function render_plugin_status_field() {
		$opts = GMM_Settings::get_group( self::OPTION_GENERAL );
		$this->select_input(
			self::OPTION_GENERAL,
			'plugin_status',
			$opts['plugin_status'],
			array(
				'enabled'  => __( 'Enabled', 'gospel-music-mastery' ),
				'disabled' => __( 'Disabled', 'gospel-music-mastery' ),
			),
			'gmm_plugin_status'
		);
	}

	/** @return void */
	public function render_default_dashboard_field() {
		$opts = GMM_Settings::get_group( self::OPTION_GENERAL );
		$this->select_input(
			self::OPTION_GENERAL,
			'default_dashboard',
			$opts['default_dashboard'],
			array(
				'student' => __( 'Student Dashboard', 'gospel-music-mastery' ),
				'teacher' => __( 'Teacher Dashboard', 'gospel-music-mastery' ),
			),
			'gmm_default_dashboard'
		);
	}

	/** @return void */
	public function render_default_currency_field() {
		$opts = GMM_Settings::get_group( self::OPTION_GENERAL );
		$this->text_input( self::OPTION_GENERAL, 'default_currency', $opts['default_currency'], 'gmm_default_currency', 3 );
	}

	/** @return void */
	public function render_timezone_field() {
		$opts = GMM_Settings::get_group( self::OPTION_GENERAL );
		$tzs  = timezone_identifiers_list();
		echo '<select name="' . esc_attr( self::OPTION_GENERAL ) . '[timezone]" id="gmm_timezone">';
		foreach ( $tzs as $tz ) {
			printf(
				'<option value="%1$s" %2$s>%1$s</option>',
				esc_attr( $tz ),
				selected( $opts['timezone'], $tz, false )
			);
		}
		echo '</select>';
	}

	/** @return void */
	public function render_commission_field() {
		$opts = GMM_Settings::get_group( self::OPTION_COMMISSION );
		printf(
			'<input type="number" class="small-text" name="%1$s[commission_percent]" id="gmm_commission_percent" value="%2$s" min="0" max="100" step="0.01" />',
			esc_attr( self::OPTION_COMMISSION ),
			esc_attr( $opts['commission_percent'] )
		);
		echo '<p class="description">' . esc_html__( 'Must be between 0 and 100.', 'gospel-music-mastery' ) . '</p>';
	}

	/** @return void */
	public function render_minimum_withdrawal_field() {
		$opts = GMM_Settings::get_group( self::OPTION_COMMISSION );
		printf(
			'<input type="number" class="small-text" name="%1$s[minimum_withdrawal]" id="gmm_minimum_withdrawal" value="%2$s" min="0" step="0.01" />',
			esc_attr( self::OPTION_COMMISSION ),
			esc_attr( $opts['minimum_withdrawal'] )
		);
	}

	/** @return void */
	public function render_teacher_payout_field() {
		$opts = GMM_Settings::get_group( self::OPTION_COMMISSION );
		$this->select_input(
			self::OPTION_COMMISSION,
			'teacher_payout_status',
			$opts['teacher_payout_status'],
			array(
				'enabled'  => __( 'Enabled', 'gospel-music-mastery' ),
				'disabled' => __( 'Disabled', 'gospel-music-mastery' ),
			),
			'gmm_teacher_payout_status'
		);
	}

	/** @return void */
	public function render_payment_mode_field() {
		$opts = GMM_Settings::get_group( self::OPTION_PAYMENT );
		$this->select_input(
			self::OPTION_PAYMENT,
			'payment_mode',
			$opts['payment_mode'],
			array(
				'test' => __( 'Test', 'gospel-music-mastery' ),
				'live' => __( 'Live', 'gospel-music-mastery' ),
			),
			'gmm_payment_mode'
		);
	}

	/** @return void */
	public function render_currency_field() {
		$opts = GMM_Settings::get_group( self::OPTION_PAYMENT );
		$this->text_input( self::OPTION_PAYMENT, 'currency', $opts['currency'], 'gmm_currency', 3 );
	}

	/** @return void */
	public function render_payment_provider_field() {
		$opts = GMM_Settings::get_group( self::OPTION_PAYMENT );
		$this->select_input(
			self::OPTION_PAYMENT,
			'payment_provider',
			$opts['payment_provider'],
			array(
				'stripe' => __( 'Stripe (preparation)', 'gospel-music-mastery' ),
				'paypal' => __( 'PayPal (preparation)', 'gospel-music-mastery' ),
			),
			'gmm_payment_provider'
		);
	}

	/** @return void */
	public function render_stripe_enabled_field() {
		$opts = GMM_Settings::get_group( self::OPTION_PAYMENT );
		$this->yes_no_select( self::OPTION_PAYMENT, 'stripe_enabled', $opts['stripe_enabled'], 'gmm_stripe_enabled' );
	}

	/** @return void */
	public function render_paypal_enabled_field() {
		$opts = GMM_Settings::get_group( self::OPTION_PAYMENT );
		$this->yes_no_select( self::OPTION_PAYMENT, 'paypal_enabled', $opts['paypal_enabled'], 'gmm_paypal_enabled' );
	}

	/** @return void */
	public function render_from_name_field() {
		$opts = GMM_Settings::get_group( self::OPTION_EMAIL );
		$this->text_input( self::OPTION_EMAIL, 'from_name', $opts['from_name'], 'gmm_from_name' );
	}

	/** @return void */
	public function render_from_email_field() {
		$opts = GMM_Settings::get_group( self::OPTION_EMAIL );
		printf(
			'<input type="email" class="regular-text" name="%1$s[from_email]" id="gmm_from_email" value="%2$s" />',
			esc_attr( self::OPTION_EMAIL ),
			esc_attr( $opts['from_email'] )
		);
	}

	/** @return void */
	public function render_emails_enabled_field() {
		$opts = GMM_Settings::get_group( self::OPTION_EMAIL );
		$this->yes_no_select( self::OPTION_EMAIL, 'emails_enabled', $opts['emails_enabled'], 'gmm_emails_enabled' );
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
	public function render_notify_booking_created_field() {
		$this->preference_select( 'booking_created', 'gmm_notify_booking_created' );
	}

	/** @return void */
	public function render_notify_payment_completed_field() {
		$this->preference_select( 'payment_completed', 'gmm_notify_payment_completed' );
	}

	/** @return void */
	public function render_notify_review_submitted_field() {
		$this->preference_select( 'review_submitted', 'gmm_notify_review_submitted' );
	}

	/** @return void */
	public function render_enable_charts_field() {
		$opts = GMM_Settings::get_group( self::OPTION_DASHBOARD );
		$this->yes_no_select( self::OPTION_DASHBOARD, 'enable_charts', $opts['enable_charts'], 'gmm_enable_charts' );
	}

	/** @return void */
	public function render_enable_animations_field() {
		$opts = GMM_Settings::get_group( self::OPTION_DASHBOARD );
		$this->yes_no_select( self::OPTION_DASHBOARD, 'enable_animations', $opts['enable_animations'], 'gmm_enable_animations' );
	}

	/** @return void */
	public function render_items_per_page_field() {
		$opts = GMM_Settings::get_group( self::OPTION_DASHBOARD );
		$this->select_input(
			self::OPTION_DASHBOARD,
			'items_per_page',
			(string) $opts['items_per_page'],
			array(
				'10' => '10',
				'20' => '20',
				'50' => '50',
			),
			'gmm_items_per_page'
		);
	}

	/** @return void */
	public function render_enable_nonce_field() {
		$opts = GMM_Settings::get_group( self::OPTION_SECURITY );
		$this->yes_no_select( self::OPTION_SECURITY, 'enable_nonce_protection', $opts['enable_nonce_protection'], 'gmm_enable_nonce_protection' );
	}

	/** @return void */
	public function render_enable_user_verification_field() {
		$opts = GMM_Settings::get_group( self::OPTION_SECURITY );
		$this->yes_no_select( self::OPTION_SECURITY, 'enable_user_verification', $opts['enable_user_verification'], 'gmm_enable_user_verification' );
	}

	/** @return void */
	public function render_session_timeout_field() {
		$opts = GMM_Settings::get_group( self::OPTION_SECURITY );
		printf(
			'<input type="number" class="small-text" name="%1$s[session_timeout]" id="gmm_session_timeout" value="%2$s" min="5" max="1440" step="1" />',
			esc_attr( self::OPTION_SECURITY ),
			esc_attr( $opts['session_timeout'] )
		);
	}

	/* ---- BC wrappers used elsewhere ---- */

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

	/* ---- Input helpers ---- */

	/**
	 * @param string $option Option.
	 * @param string $key    Field.
	 * @param mixed  $value  Value.
	 * @param string $id     Input id.
	 * @param int    $maxlen Max length.
	 * @return void
	 */
	private function text_input( $option, $key, $value, $id, $maxlen = 0 ) {
		$maxlen_attr = $maxlen > 0 ? ' maxlength="' . absint( $maxlen ) . '"' : '';
		printf(
			'<input type="text" class="regular-text" name="%1$s[%2$s]" id="%3$s" value="%4$s"%5$s />',
			esc_attr( $option ),
			esc_attr( $key ),
			esc_attr( $id ),
			esc_attr( (string) $value ),
			$maxlen_attr // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		);
	}

	/**
	 * @param string               $option  Option.
	 * @param string               $key     Field.
	 * @param string               $current Current.
	 * @param array<string,string> $choices Choices.
	 * @param string               $id      Id.
	 * @return void
	 */
	private function select_input( $option, $key, $current, $choices, $id ) {
		echo '<select name="' . esc_attr( $option ) . '[' . esc_attr( $key ) . ']" id="' . esc_attr( $id ) . '">';
		foreach ( $choices as $value => $label ) {
			printf(
				'<option value="%1$s" %2$s>%3$s</option>',
				esc_attr( (string) $value ),
				selected( (string) $current, (string) $value, false ),
				esc_html( $label )
			);
		}
		echo '</select>';
	}

	/**
	 * @param string $option  Option.
	 * @param string $key     Field.
	 * @param string $current Current yes/no.
	 * @param string $id      Id.
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
	 * @param string $pref Preference key.
	 * @param string $id   Input id.
	 * @return void
	 */
	private function preference_select( $pref, $id ) {
		$opts = GMM_Settings::get_group( self::OPTION_EMAIL );
		$val  = isset( $opts['preferences'][ $pref ] ) ? $opts['preferences'][ $pref ] : 'yes';
		echo '<select name="' . esc_attr( self::OPTION_EMAIL ) . '[preferences][' . esc_attr( $pref ) . ']" id="' . esc_attr( $id ) . '">';
		printf( '<option value="yes" %s>%s</option>', selected( $val, 'yes', false ), esc_html__( 'Yes', 'gospel-music-mastery' ) );
		printf( '<option value="no" %s>%s</option>', selected( $val, 'no', false ), esc_html__( 'No', 'gospel-music-mastery' ) );
		echo '</select>';
	}
}
