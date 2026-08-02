<?php
/**
 * Template loader — resolve and load plugin templates safely.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Template_Loader
 *
 * Loads templates from plugin templates/ with optional future override support.
 * Does not touch theme template hierarchy.
 */
class GMM_Template_Loader {

	/**
	 * Locate a template file path.
	 *
	 * Lookup order:
	 * 1. Filter override path
	 * 2. Plugin templates/{slug}.php
	 *
	 * @param string $template Relative key e.g. student/dashboard.
	 * @return string Absolute path or empty string.
	 */
	public static function locate( $template ) {
		$template = self::normalize_key( $template );
		if ( '' === $template ) {
			return '';
		}

		$default = wp_normalize_path( GMM_PATH . 'templates/' . $template . '.php' );

		/**
		 * Allow locating an alternate template file (future theme/plugin overrides).
		 * Must remain under a trusted path — caller validates readability.
		 *
		 * @since 1.0.0
		 * @param string $default  Default plugin template path.
		 * @param string $template Relative template key.
		 */
		$located = apply_filters( 'gmm_locate_template', $default, $template );
		$located = is_string( $located ) ? wp_normalize_path( $located ) : '';

		if ( '' === $located || ! is_readable( $located ) ) {
			return '';
		}

		// Security: overrides must stay inside WP content or plugin dir.
		$content_dir = wp_normalize_path( WP_CONTENT_DIR );
		$plugin_dir  = wp_normalize_path( GMM_PATH );
		if ( 0 !== strpos( $located, $plugin_dir ) && 0 !== strpos( $located, $content_dir ) ) {
			return '';
		}

		return $located;
	}

	/**
	 * Load a template into a string (buffered).
	 *
	 * @param string               $template Relative key.
	 * @param array<string, mixed> $args     Variables for the template.
	 * @return string
	 */
	public static function get( $template, $args = array() ) {
		$file = self::locate( $template );
		if ( '' === $file ) {
			do_action( 'gmm_missing_template', $template, GMM_PATH . 'templates/' . self::normalize_key( $template ) . '.php' );
			return '';
		}

		$args = self::prepare_args( $template, is_array( $args ) ? $args : array() );

		/**
		 * Filter args immediately before include.
		 *
		 * @since 1.0.0
		 * @param array<string, mixed> $args     Template args.
		 * @param string               $template Template key.
		 */
		$args = apply_filters( 'gmm_get_template_args', $args, $template );

		if ( ! empty( $args ) ) {
			// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Template scope only.
			extract( $args, EXTR_SKIP );
		}

		ob_start();
		include $file;
		return (string) ob_get_clean();
	}

	/**
	 * Normalize and validate template key.
	 *
	 * @param string $template Raw key.
	 * @return string
	 */
	public static function normalize_key( $template ) {
		$template = is_string( $template ) ? $template : '';
		$template = ltrim( str_replace( '\\', '/', $template ), '/' );
		$template = preg_replace( '/\.php$/i', '', $template );

		if ( '' === $template || false !== strpos( $template, '..' ) ) {
			return '';
		}

		if ( ! preg_match( '/^(student|teacher|admin)\/[a-z0-9_-]+$/i', $template ) ) {
			return '';
		}

		return $template;
	}

	/**
	 * Prepare default demo/placeholder variables (no DB yet).
	 *
	 * @param string               $template Template key.
	 * @param array<string, mixed> $args     Incoming args.
	 * @return array<string, mixed>
	 */
	public static function prepare_args( $template, $args ) {
		$parts  = explode( '/', $template );
		$portal = isset( $parts[0] ) ? $parts[0] : '';

		$current = wp_get_current_user();
		$user    = ( isset( $args['user'] ) && $args['user'] instanceof WP_User ) ? $args['user'] : $current;

		$defaults = array(
			'user'       => $user,
			'assets_url' => gmm_design_assets_url(),
			'shortcode'  => isset( $args['shortcode'] ) ? $args['shortcode'] : '',
			'atts'       => isset( $args['atts'] ) ? $args['atts'] : array(),
			'access'     => isset( $args['access'] ) ? $args['access'] : '',
		);

		if ( 'student' === $portal ) {
			$defaults['user_name']        = self::display_name( $user, 'Sarah Johnson' );
			$defaults['user_first_name']  = self::first_name( $user, 'Sarah' );
			$defaults['student_profile']  = isset( $args['student_profile'] ) ? $args['student_profile'] : array(
				'role_label' => 'Music Student',
				'level'      => 'Intermediate',
				'focus'      => 'Gospel Piano',
				'email'      => 'sarah@example.com',
				'username'   => 'sarahjohnson',
				'avatar'     => 'assets/img/team/02.jpg',
			);
			$defaults['lessons']  = isset( $args['lessons'] ) ? $args['lessons'] : array();
			$defaults['bookings'] = isset( $args['bookings'] ) ? $args['bookings'] : array();
		}

		if ( 'teacher' === $portal ) {
			$defaults['user_name']       = self::display_name( $user, 'John Smith' );
			$defaults['user_first_name'] = self::first_name( $user, 'John' );
			$defaults['teacher_profile'] = isset( $args['teacher_profile'] ) ? $args['teacher_profile'] : array(
				'role_label' => 'Gospel Music Instructor',
				'email'      => 'john@example.com',
				'username'   => 'johnsmith',
				'avatar'     => 'assets/img/team/01.jpg',
			);
			$defaults['classes']      = isset( $args['classes'] ) ? $args['classes'] : array();
			$defaults['availability'] = isset( $args['availability'] ) ? $args['availability'] : array();
			$defaults['bookings']     = isset( $args['bookings'] ) ? $args['bookings'] : array();
		}

		if ( 'admin' === $portal ) {
			$defaults['user_name']       = self::display_name( $user, 'Administrator' );
			$defaults['user_first_name'] = self::first_name( $user, 'Admin' );
			$defaults['stats']           = isset( $args['stats'] ) ? $args['stats'] : array();
			$defaults['users']           = isset( $args['users'] ) ? $args['users'] : array();
			$defaults['reports']         = isset( $args['reports'] ) ? $args['reports'] : array();
		}

		return array_merge( $defaults, $args );
	}

	/**
	 * @param WP_User $user    User object.
	 * @param string  $fallback Demo fallback.
	 * @return string
	 */
	private static function display_name( $user, $fallback ) {
		if ( $user instanceof WP_User && $user->exists() && $user->display_name ) {
			return $user->display_name;
		}
		return $fallback;
	}

	/**
	 * @param WP_User $user    User object.
	 * @param string  $fallback Demo fallback.
	 * @return string
	 */
	private static function first_name( $user, $fallback ) {
		if ( $user instanceof WP_User && $user->exists() ) {
			$first = $user->first_name;
			if ( $first ) {
				return $first;
			}
			if ( $user->display_name ) {
				$parts = preg_split( '/\s+/', trim( $user->display_name ) );
				if ( ! empty( $parts[0] ) ) {
					return $parts[0];
				}
			}
		}
		return $fallback;
	}
}
