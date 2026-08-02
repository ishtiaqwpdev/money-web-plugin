<?php
/**
 * Stripe gateway placeholder — not implemented.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Stripe
 *
 * Future Stripe integration stub. No API calls.
 */
class GMM_Stripe {

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
			'gmm_stripe_not_implemented',
			__( 'Stripe gateway is not connected yet.', 'gospel-music-mastery' )
		);
	}
}
