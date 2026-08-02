<?php
/**
 * The core plugin class.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Plugin
 *
 * Bootstraps the plugin, loads text domain, and prepares future module loading.
 */
class GMM_Plugin {

	/**
	 * Hook loader.
	 *
	 * @var GMM_Loader
	 */
	protected $loader;

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	protected $version;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->version = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';
		$this->loader  = new GMM_Loader();

		$this->define_locale();
		$this->define_asset_hooks();
		$this->define_shortcode_hooks();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_portal_hooks();
	}

	/**
	 * Register CSS/JS asset enqueue hooks.
	 *
	 * @return void
	 */
	private function define_asset_hooks() {
		GMM_Assets::register_hooks( $this->loader );
	}

	/**
	 * Register shortcode system on init.
	 *
	 * @return void
	 */
	private function define_shortcode_hooks() {
		GMM_Shortcodes::register_hooks( $this->loader );
	}

	/**
	 * Load plugin text domain for translations.
	 *
	 * @return void
	 */
	private function define_locale() {
		$this->loader->add_action( 'plugins_loaded', $this, 'load_textdomain' );
	}

	/**
	 * Register wp-admin menu, pages, and settings.
	 *
	 * @return void
	 */
	private function define_admin_hooks() {
		GMM_Settings::register_hooks( $this->loader );
		GMM_Admin_Menu::register_hooks( $this->loader );
		GMM_Admin_Pages::register_hooks( $this->loader );
		GMM_Admin_Settings::register_hooks( $this->loader );
		GMM_Admin_Dashboard::register_hooks( $this->loader );
		GMM_Admin_Teachers::register_hooks( $this->loader );
		GMM_Admin_Students::register_hooks( $this->loader );
		GMM_Admin_Classes::register_hooks( $this->loader );
		GMM_Admin_Bookings::register_hooks( $this->loader );
		GMM_Admin_Payments::register_hooks( $this->loader );
	}

	/**
	 * Placeholder for future public-facing hooks.
	 *
	 * @return void
	 */
	private function define_public_hooks() {
		// Future: load public/ classes and register hooks via $this->loader.
	}

	/**
	 * Student / teacher portal module hooks.
	 *
	 * @return void
	 */
	private function define_portal_hooks() {
		GMM_Security::register_hooks( $this->loader );
		GMM_Teacher::register_hooks( $this->loader );
		GMM_Teacher_Auth::register_hooks( $this->loader );
		GMM_Teacher_Dashboard::register_hooks( $this->loader );
		GMM_Teacher_Profile::register_hooks( $this->loader );
		GMM_Teacher_Classes::register_hooks( $this->loader );
		GMM_Teacher_Availability::register_hooks( $this->loader );
		GMM_Teacher_Bookings::register_hooks( $this->loader );
		GMM_Teacher_Earnings::register_hooks( $this->loader );
		GMM_Student::register_hooks( $this->loader );
		GMM_Student_Auth::register_hooks( $this->loader );
		GMM_Student_Dashboard::register_hooks( $this->loader );
		GMM_Student_Profile::register_hooks( $this->loader );
		GMM_Student_Payments::register_hooks( $this->loader );
		GMM_Booking::register_hooks( $this->loader );
		GMM_Payment::register_hooks( $this->loader );
		GMM_Auth::register_hooks( $this->loader );
		GMM_Ajax::register_hooks( $this->loader );
		GMM_Search::register_hooks( $this->loader );
		GMM_Teacher_Search::register_hooks( $this->loader );
		GMM_Teacher_Profile_Public::register_hooks( $this->loader );
		GMM_Booking_Flow::register_hooks( $this->loader );
		GMM_Notifications::register_hooks( $this->loader );
		GMM_Reviews::register_hooks( $this->loader );
		GMM_Media::register_hooks( $this->loader );
		GMM_Analytics::register_hooks( $this->loader );
	}

	/**
	 * Load the plugin text domain.
	 *
	 * @return void
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'gospel-music-mastery',
			false,
			dirname( GMM_BASENAME ) . '/languages'
		);
	}

	/**
	 * Run the loader to execute registered hooks.
	 *
	 * @return void
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * Retrieve the loader instance.
	 *
	 * @return GMM_Loader
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the plugin version.
	 *
	 * @return string
	 */
	public function get_version() {
		return $this->version;
	}
}
