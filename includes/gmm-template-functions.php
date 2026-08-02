<?php
/**
 * Template loading helpers and design asset URLs.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Base URL for frozen HTML design assets (images/css/js referenced by templates).
 *
 * Defaults to the plugin URL (GMM_URL) so paths like assets/img/team/01.jpg
 * resolve to GMM_URL . 'assets/img/team/01.jpg'.
 * Filter `gmm_design_assets_base` to override (theme/CDN).
 * Trailing slash included.
 *
 * @return string
 */
function gmm_design_assets_url() {
	$default = defined( 'GMM_URL' ) ? GMM_URL : '';

	/**
	 * Base URL for design assets used inside plugin templates.
	 *
	 * @since 1.0.0
	 * @param string $base Plugin URL by default.
	 */
	$base = apply_filters( 'gmm_design_assets_base', $default );
	$base = is_string( $base ) ? $base : '';

	if ( '' === $base ) {
		return '';
	}

	return trailingslashit( esc_url_raw( $base ) );
}

/**
 * Build a full URL for a design asset path (e.g. assets/img/team/02.jpg).
 *
 * Also accepts assets/images/... (plugin images mirror).
 *
 * @param string $relative Relative path as in the HTML design.
 * @return string
 */
function gmm_design_asset_url( $relative ) {
	$relative = ltrim( str_replace( '\\', '/', (string) $relative ), '/' );

	// Block path traversal and absolute / protocol-relative URLs.
	if (
		'' === $relative
		|| false !== strpos( $relative, '..' )
		|| preg_match( '#^(?:[a-z][a-z0-9+.-]*:)?//#i', $relative )
		|| 0 === strpos( $relative, '/' )
	) {
		return '';
	}

	// Allow only expected design asset path characters.
	if ( ! preg_match( '#^[a-zA-Z0-9_./-]+$#', $relative ) ) {
		return '';
	}

	// Normalize legacy HTML paths (assets/img/...) to plugin images folder.
	if ( 0 === strpos( $relative, 'assets/img/' ) ) {
		$relative = 'assets/images/' . substr( $relative, strlen( 'assets/img/' ) );
	} elseif ( 0 === strpos( $relative, 'img/' ) ) {
		$relative = 'assets/images/' . substr( $relative, strlen( 'img/' ) );
	} elseif ( 0 === strpos( $relative, 'images/' ) ) {
		$relative = 'assets/images/' . substr( $relative, strlen( 'images/' ) );
	}

	if ( ! defined( 'GMM_URL' ) ) {
		return '';
	}

	// Prefer readable file under assets/images, then assets/img fallback.
	$path_under_assets = ( 0 === strpos( $relative, 'assets/' ) ) ? substr( $relative, 7 ) : $relative;
	$candidates        = array( $path_under_assets );
	if ( 0 === strpos( $path_under_assets, 'images/' ) ) {
		$candidates[] = 'img/' . substr( $path_under_assets, strlen( 'images/' ) );
	}

	foreach ( $candidates as $candidate ) {
		$fs = defined( 'GMM_PATH' ) ? GMM_PATH . 'assets/' . $candidate : '';
		if ( $fs && is_readable( $fs ) ) {
			return esc_url( GMM_URL . 'assets/' . $candidate );
		}
	}

	// Default: GMM_URL . 'assets/images/...'
	return esc_url( GMM_URL . $relative );
}

/**
 * Plugin asset URL helper (always under GMM_URL . 'assets/').
 *
 * @param string $relative Path under assets/ e.g. images/team/01.jpg or css/gmm-core.css.
 * @return string
 */
function gmm_asset_url( $relative ) {
	$relative = ltrim( str_replace( '\\', '/', (string) $relative ), '/' );
	if ( '' === $relative || false !== strpos( $relative, '..' ) ) {
		return '';
	}
	if ( 0 === strpos( $relative, 'assets/' ) ) {
		$relative = substr( $relative, 7 );
	}
	if ( ! defined( 'GMM_URL' ) ) {
		return '';
	}
	return esc_url( GMM_URL . 'assets/' . $relative );
}

/**
 * Permalink helper for GMM pages (falls back to #key if page not created yet).
 *
 * @param string $key Page key from GMM_Pages (e.g. student_dashboard).
 * @return string
 */
function gmm_get_page_link( $key ) {
	if ( class_exists( 'GMM_Pages' ) ) {
		$url = GMM_Pages::get_page_url( $key );
		if ( $url ) {
			return $url;
		}
	}
	return '#';
}

/**
 * Load a template and return HTML (preferred API).
 *
 * @param string               $template Relative key e.g. student/dashboard.
 * @param array<string, mixed> $args     Template variables.
 * @return string
 */
function gmm_get_template( $template, $args = array() ) {
	if ( class_exists( 'GMM_Template_Loader' ) ) {
		return GMM_Template_Loader::get( $template, $args );
	}

	return gmm_load_template( $template, $args );
}

/**
 * Safely load a plugin template from templates/ and return its HTML.
 *
 * Path is relative to templates/ without .php, e.g. 'student/dashboard'.
 * Prefer gmm_get_template() which adds prepared variables + override support.
 *
 * @param string               $template Relative template path (no leading slash, no .php).
 * @param array<string, mixed> $args     Optional variables extracted into template scope.
 * @return string Buffered template output (empty string if missing/invalid).
 */
function gmm_load_template( $template, $args = array() ) {
	if ( class_exists( 'GMM_Template_Loader' ) ) {
		$file = GMM_Template_Loader::locate( $template );
		if ( '' === $file ) {
			$key = GMM_Template_Loader::normalize_key( $template );
			do_action( 'gmm_missing_template', $key, GMM_PATH . 'templates/' . $key . '.php' );
			return '';
		}

		if ( ! empty( $args ) && is_array( $args ) ) {
			// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Scoped template vars only.
			extract( $args, EXTR_SKIP );
		}

		ob_start();
		include $file;
		return (string) ob_get_clean();
	}

	$template = is_string( $template ) ? $template : '';
	$template = ltrim( str_replace( '\\', '/', $template ), '/' );
	$template = preg_replace( '/\.php$/i', '', $template );

	if ( '' === $template || false !== strpos( $template, '..' ) || 0 === strpos( $template, '/' ) ) {
		return '';
	}

	if ( ! preg_match( '/^(student|teacher|admin)\/[a-z0-9_-]+$/i', $template ) ) {
		return '';
	}

	$file = GMM_PATH . 'templates/' . $template . '.php';
	$file = wp_normalize_path( $file );

	$base = wp_normalize_path( GMM_PATH . 'templates/' );
	if ( 0 !== strpos( $file, $base ) || ! is_readable( $file ) ) {
		do_action( 'gmm_missing_template', $template, $file );
		return '';
	}

	if ( ! empty( $args ) && is_array( $args ) ) {
		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Scoped template vars only.
		extract( $args, EXTR_SKIP );
	}

	ob_start();
	include $file;
	return (string) ob_get_clean();
}
