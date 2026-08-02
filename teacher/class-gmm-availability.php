<?php
/**
 * Teacher availability CRUD (compat wrapper).
 *
 * Delegates to GMM_Teacher_Availability for full calendar management.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Availability
 *
 * Back-compat API used by dashboard and teacher injectors.
 */
class GMM_Availability {

	/**
	 * Add availability slot.
	 *
	 * @param array<string, mixed> $data    Fields.
	 * @param int                  $user_id WP user ID.
	 * @return int|WP_Error
	 */
	public static function add_availability( $data, $user_id = 0 ) {
		if ( class_exists( 'GMM_Teacher_Availability' ) ) {
			return GMM_Teacher_Availability::add_availability( $data, $user_id );
		}
		return new WP_Error( 'gmm_missing', __( 'Availability management is unavailable.', 'gospel-music-mastery' ) );
	}

	/**
	 * Update own availability slot.
	 *
	 * @param int                  $availability_id Row ID.
	 * @param array<string, mixed> $data            Fields.
	 * @param int                  $user_id         WP user ID.
	 * @return true|WP_Error
	 */
	public static function update_availability( $availability_id, $data, $user_id = 0 ) {
		if ( class_exists( 'GMM_Teacher_Availability' ) ) {
			return GMM_Teacher_Availability::update_availability( $availability_id, $data, $user_id );
		}
		return new WP_Error( 'gmm_missing', __( 'Availability management is unavailable.', 'gospel-music-mastery' ) );
	}

	/**
	 * Delete own availability slot.
	 *
	 * @param int $availability_id Row ID.
	 * @param int $user_id         WP user ID.
	 * @return true|WP_Error
	 */
	public static function delete_availability( $availability_id, $user_id = 0 ) {
		if ( class_exists( 'GMM_Teacher_Availability' ) ) {
			return GMM_Teacher_Availability::delete_availability( $availability_id, $user_id );
		}
		return new WP_Error( 'gmm_missing', __( 'Availability management is unavailable.', 'gospel-music-mastery' ) );
	}

	/**
	 * Get availability for a teacher.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Optional filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_availability( $user_id = 0, $args = array() ) {
		if ( class_exists( 'GMM_Teacher_Availability' ) ) {
			return GMM_Teacher_Availability::get_availability( $user_id, $args );
		}
		return array();
	}
}
