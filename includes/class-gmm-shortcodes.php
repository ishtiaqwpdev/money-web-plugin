<?php
/**
 * Backward-compatible loader for the shortcode controller.
 *
 * Canonical class lives at public/class-gmm-shortcodes.php.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'GMM_Shortcodes' ) ) {
	require_once GMM_PATH . 'public/class-gmm-shortcodes.php';
}
