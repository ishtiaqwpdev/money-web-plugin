<?php
/**
 * Fired during plugin deactivation.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Deactivator
 *
 * Safe deactivation — does not delete user data, options, or custom roles.
 */
class GMM_Deactivator {

	/**
	 * Deactivate the plugin.
	 *
	 * Roles (gmm_teacher, gmm_student) are intentionally preserved so
	 * assigned users are not orphaned.
	 *
	 * Pages created by GMM_Pages are intentionally NOT deleted.
	 * Option gmm_created_pages is preserved.
	 *
	 * @return void
	 */
	public static function deactivate() {
		/**
		 * Fires before Gospel Music Mastery plugin deactivation cleanup.
		 *
		 * @since 1.0.0
		 */
		do_action( 'gmm_plugin_deactivating' );

		// Intentionally do NOT call remove_role() — preserve users and caps.
		// Intentionally do NOT delete pages or gmm_created_pages content.

		flush_rewrite_rules();

		/**
		 * Fires after Gospel Music Mastery plugin deactivation.
		 *
		 * @since 1.0.0
		 */
		do_action( 'gmm_plugin_deactivated' );
	}
}
