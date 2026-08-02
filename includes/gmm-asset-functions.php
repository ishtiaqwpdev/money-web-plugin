<?php
/**
 * Asset loading helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Load GMM frontend CSS/JS (safe to call multiple times).
 *
 * @return void
 */
function gmm_load_frontend_assets() {
	if ( ! class_exists( 'GMM_Assets' ) ) {
		return;
	}
	GMM_Assets::enqueue_frontend();
}

/**
 * Load GMM dashboard CSS/JS for admin / teacher / student portals.
 *
 * @return void
 */
function gmm_load_dashboard_assets() {
	if ( ! class_exists( 'GMM_Assets' ) ) {
		return;
	}
	GMM_Assets::enqueue_dashboard();
}
