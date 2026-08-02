<?php
/**
 * Settings helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Retrieve a plugin setting safely.
 *
 * @param string $key     Alias (e.g. commission_rate) or dotted path (e.g. general.site_name).
 * @param mixed  $default Default when missing.
 * @return mixed
 */
function gmm_get_setting( $key, $default = null ) {
	if ( ! class_exists( 'GMM_Settings' ) ) {
		return $default;
	}
	return GMM_Settings::get( $key, $default );
}

/**
 * Retrieve a full settings group.
 *
 * @param string $option Option key (e.g. gmm_general_settings) or short name (general).
 * @return array<string, mixed>
 */
function gmm_get_settings_group( $option ) {
	if ( ! class_exists( 'GMM_Settings' ) ) {
		return array();
	}
	$map = array(
		'general'    => GMM_Settings::OPTION_GENERAL,
		'commission' => GMM_Settings::OPTION_COMMISSION,
		'payment'    => GMM_Settings::OPTION_PAYMENT,
		'email'      => GMM_Settings::OPTION_EMAIL,
		'dashboard'  => GMM_Settings::OPTION_DASHBOARD,
		'security'   => GMM_Settings::OPTION_SECURITY,
	);
	$key = isset( $map[ $option ] ) ? $map[ $option ] : $option;
	return GMM_Settings::get_group( $key );
}
