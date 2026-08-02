<?php
/**
 * Fired during plugin activation.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Activator
 *
 * Handles activation tasks including database installation.
 */
class GMM_Activator {

	/**
	 * Activate the plugin.
	 *
	 * Order: database → roles → pages → defaults.
	 *
	 * @return void
	 */
	public static function activate() {
		try {
			self::install_database();
			self::register_roles();
			self::create_pages();
			self::set_default_settings();
		} catch ( Throwable $e ) {
			if ( defined( 'WP_DEBUG' ) && WP_DEBUG && defined( 'WP_DEBUG_LOG' ) && WP_DEBUG_LOG ) {
				// phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log
				error_log( 'GMM activation error: ' . $e->getMessage() );
			}
			// Continue — do not fatal the site; partial activation is recoverable on next load/activate.
		}

		/**
		 * Fires after Gospel Music Mastery plugin activation tasks complete.
		 *
		 * @since 1.0.0
		 */
		do_action( 'gmm_plugin_activated' );

		flush_rewrite_rules();
	}

	/**
	 * Create or update custom database tables via GMM_Database.
	 *
	 * Does not delete existing data.
	 *
	 * @return void
	 */
	private static function install_database() {
		if ( ! class_exists( 'GMM_Database' ) ) {
			require_once GMM_PATH . 'includes/class-gmm-database.php';
		}

		GMM_Database::install();
	}

	/**
	 * Register custom teacher / student roles and capabilities.
	 *
	 * Does not remove roles or reassign users.
	 *
	 * @return void
	 */
	private static function register_roles() {
		if ( ! class_exists( 'GMM_Roles' ) ) {
			require_once GMM_PATH . 'includes/class-gmm-roles.php';
		}

		GMM_Roles::register();
	}

	/**
	 * Create required front-end pages with embedded GMM shortcodes.
	 *
	 * Skips duplicates; stores IDs in gmm_created_pages.
	 *
	 * @return void
	 */
	private static function create_pages() {
		if ( ! class_exists( 'GMM_Pages' ) ) {
			require_once GMM_PATH . 'includes/class-gmm-pages.php';
		}

		GMM_Pages::create_pages();
	}

	/**
	 * Store default plugin options (non-destructive).
	 *
	 * @return void
	 */
	private static function set_default_settings() {
		if ( false === get_option( 'gmm_plugin_version', false ) ) {
			add_option( 'gmm_plugin_version', GMM_VERSION, '', false );
		} else {
			update_option( 'gmm_plugin_version', GMM_VERSION );
		}

		if ( ! class_exists( 'GMM_Settings' ) ) {
			require_once GMM_PATH . 'includes/class-gmm-settings.php';
		}
		GMM_Settings::ensure_defaults();

		if ( false === get_option( 'gmm_settings', false ) ) {
			add_option(
				'gmm_settings',
				array(
					'initialized' => 1,
				),
				'',
				false
			);
		}
	}
}
