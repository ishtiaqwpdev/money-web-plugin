<?php
/**
 * Admin helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load a GMM wp-admin page template by key.
 *
 * @param string $page Page key: dashboard|teachers|students|classes|bookings|payments|programs|settings.
 * @return void
 */
function gmm_admin_load_page( $page ) {
	if ( ! class_exists( 'GMM_Admin_Pages' ) ) {
		return;
	}

	GMM_Admin_Pages::render( $page );
}

/**
 * Whether the current request is a GMM plugin admin screen.
 *
 * @param string $hook Optional admin hook.
 * @return bool
 */
function gmm_is_plugin_admin_page( $hook = '' ) {
	if ( class_exists( 'GMM_Admin_Menu' ) ) {
		return GMM_Admin_Menu::is_gmm_admin_screen( $hook );
	}
	return false;
}
