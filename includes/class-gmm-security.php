<?php
/**
 * Cross-cutting security helpers for Gospel Music Mastery.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Security
 *
 * Plugin enablement gate and sensitive-data stripping for public responses.
 */
class GMM_Security {

	/**
	 * Register security hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_filter( 'gmm_shortcode_user_can_access', $instance, 'gate_shortcode_when_disabled', 5, 2 );
		$loader->add_filter( 'gmm_search_teachers_result', $instance, 'strip_teacher_pii', 20, 2 );
		$loader->add_filter( 'gmm_search_classes_result', $instance, 'strip_class_pii', 20, 2 );
	}

	/**
	 * Whether the plugin is enabled in settings.
	 *
	 * @return bool
	 */
	public static function is_plugin_enabled() {
		$status = function_exists( 'gmm_get_setting' ) ? gmm_get_setting( 'plugin_status', 'enabled' ) : 'enabled';
		return ( 'disabled' !== $status );
	}

	/**
	 * Whether nonce protection setting is on (foundation flag).
	 *
	 * @return bool
	 */
	public static function nonce_protection_enabled() {
		$flag = function_exists( 'gmm_get_setting' ) ? gmm_get_setting( 'enable_nonce_protection', 'yes' ) : 'yes';
		return ( 'yes' === $flag );
	}

	/**
	 * Block shortcodes when plugin is disabled (admins can still preview).
	 *
	 * @param bool   $allowed Access flag.
	 * @param string $access  Access role key.
	 * @return bool
	 */
	public function gate_shortcode_when_disabled( $allowed, $access ) {
		unset( $access );
		if ( self::is_plugin_enabled() ) {
			return (bool) $allowed;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return (bool) $allowed;
		}
		return false;
	}

	/**
	 * Remove emails/phones from teacher search when not admin.
	 *
	 * @param array<string, mixed> $result Search result.
	 * @param array<string, mixed> $args   Args.
	 * @return array<string, mixed>
	 */
	public function strip_teacher_pii( $result, $args ) {
		if ( current_user_can( 'manage_options' ) && empty( $args['public'] ) ) {
			return $result;
		}
		if ( empty( $result['items'] ) || ! is_array( $result['items'] ) ) {
			return $result;
		}
		foreach ( $result['items'] as $i => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			unset( $result['items'][ $i ]['email'], $result['items'][ $i ]['phone'], $result['items'][ $i ]['user_id'] );
		}
		return $result;
	}

	/**
	 * Strip any accidental PII from class search rows for public context.
	 *
	 * @param array<string, mixed> $result Result.
	 * @param array<string, mixed> $args   Args.
	 * @return array<string, mixed>
	 */
	public function strip_class_pii( $result, $args ) {
		if ( current_user_can( 'manage_options' ) && empty( $args['public'] ) ) {
			return $result;
		}
		if ( empty( $result['items'] ) || ! is_array( $result['items'] ) ) {
			return $result;
		}
		foreach ( $result['items'] as $i => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			unset( $result['items'][ $i ]['email'], $result['items'][ $i ]['phone'] );
		}
		return $result;
	}

	/**
	 * Status value used for soft-deleted content rows.
	 *
	 * @return string
	 */
	public static function soft_delete_status() {
		return 'trash';
	}
}
