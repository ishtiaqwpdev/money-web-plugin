<?php
/**
 * PayPal gateway placeholder — not implemented.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_PayPal
 *
 * Future PayPal integration stub. No API calls.
 */
class GMM_PayPal {

	/**
	 * Whether the gateway is configured.
	 *
	 * @return bool
	 */
	public static function is_ready() {
		return false;
	}

	/**
	 * Placeholder charge method.
	 *
	 * @param array<string, mixed> $args Charge args.
	 * @return WP_Error
	 */
	public static function charge( $args = array() ) {
		unset( $args );
		return new WP_Error(
			'gmm_paypal_not_implemented',
			__( 'PayPal gateway is not connected yet.', 'gospel-music-mastery' )
		);
	}
}
