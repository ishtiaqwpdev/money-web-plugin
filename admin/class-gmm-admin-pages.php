<?php
/**
 * Admin page rendering for GMM.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Admin_Pages
 *
 * Loads templates/admin/* inside wp-admin with capability checks.
 */
class GMM_Admin_Pages {

	/**
	 * Map of page keys to template paths (relative to templates/).
	 *
	 * @var array<string, string>
	 */
	private static $page_map = array(
		'dashboard' => 'admin/dashboard',
		'teachers'  => 'admin/teachers',
		'students'  => 'admin/students',
		'classes'   => 'admin/classes',
		'bookings'  => 'admin/bookings',
		'payments'  => 'admin/payments',
		'programs'  => 'admin/programs',
		'blog'      => 'admin/blog',
		'settings'  => 'admin/settings',
	);

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		// Pages are invoked via menu callbacks; no extra hooks required here.
		unset( $loader );
	}

	/**
	 * @return void
	 */
	public static function render_dashboard() {
		gmm_admin_load_page( 'dashboard' );
	}

	/**
	 * @return void
	 */
	public static function render_teachers() {
		gmm_admin_load_page( 'teachers' );
	}

	/**
	 * @return void
	 */
	public static function render_students() {
		gmm_admin_load_page( 'students' );
	}

	/**
	 * @return void
	 */
	public static function render_classes() {
		gmm_admin_load_page( 'classes' );
	}

	/**
	 * @return void
	 */
	public static function render_bookings() {
		gmm_admin_load_page( 'bookings' );
	}

	/**
	 * @return void
	 */
	public static function render_payments() {
		gmm_admin_load_page( 'payments' );
	}

	/**
	 * @return void
	 */
	public static function render_programs() {
		gmm_admin_load_page( 'programs' );
	}

	/**
	 * @return void
	 */
	public static function render_blog() {
		gmm_admin_load_page( 'blog' );
	}

	/**
	 * Native WordPress settings screen (basic config only).
	 *
	 * Management UIs remain on plugin dashboard pages / frozen frontend templates.
	 *
	 * @return void
	 */
	public static function render_settings() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'You do not have permission to manage Gospel Music Mastery settings.', 'gospel-music-mastery' ),
				esc_html__( 'Access Denied', 'gospel-music-mastery' ),
				array( 'response' => 403 )
			);
		}

		if ( class_exists( 'GMM_Admin_Settings' ) ) {
			GMM_Admin_Settings::render_page();
			return;
		}

		gmm_admin_load_page( 'settings' );
	}

	/**
	 * Render an admin page by key.
	 *
	 * @param string $page Page key (dashboard, teachers, …).
	 * @return void
	 */
	public static function render( $page ) {
		$page = sanitize_key( $page );

		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die(
				esc_html__( 'You do not have permission to access Gospel Music Mastery admin pages.', 'gospel-music-mastery' ),
				esc_html__( 'Access Denied', 'gospel-music-mastery' ),
				array( 'response' => 403 )
			);
		}

		if ( ! isset( self::$page_map[ $page ] ) ) {
			wp_die(
				esc_html__( 'Invalid Gospel Music Mastery admin page.', 'gospel-music-mastery' ),
				esc_html__( 'Not Found', 'gospel-music-mastery' ),
				array( 'response' => 404 )
			);
		}

		$template = self::$page_map[ $page ];

		/**
		 * Fires before a GMM admin page is rendered.
		 *
		 * @since 1.0.0
		 * @param string $page     Page key.
		 * @param string $template Template path.
		 */
		do_action( 'gmm_admin_before_page', $page, $template );

		$args = array(
			'stats'   => array(),
			'users'   => array(),
			'reports' => array(),
			'is_wp_admin' => true,
		);

		/**
		 * Filter admin template args.
		 *
		 * @since 1.0.0
		 * @param array<string, mixed> $args Args.
		 * @param string               $page Page key.
		 */
		$args = apply_filters( 'gmm_admin_page_args', $args, $page );

		if ( 'settings' === $page && class_exists( 'GMM_Settings' ) ) {
			$args['gmm_settings'] = array(
				'general'    => GMM_Settings::get_group( GMM_Settings::OPTION_GENERAL ),
				'commission' => GMM_Settings::get_group( GMM_Settings::OPTION_COMMISSION ),
				'payment'    => GMM_Settings::get_group( GMM_Settings::OPTION_PAYMENT ),
				'email'      => GMM_Settings::get_group( GMM_Settings::OPTION_EMAIL ),
				'dashboard'  => GMM_Settings::get_group( GMM_Settings::OPTION_DASHBOARD ),
				'security'   => GMM_Settings::get_group( GMM_Settings::OPTION_SECURITY ),
			);
		}

		echo '<div class="wrap gmm-admin-wrap" id="gmm-admin-' . esc_attr( $page ) . '">';
		echo '<h1 class="gmm-admin-screen-title screen-reader-text">' . esc_html( self::get_page_title( $page ) ) . '</h1>';

		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Template escapes its own output.
		echo gmm_get_template( $template, $args );

		echo '</div>';

		/**
		 * Fires after a GMM admin page is rendered.
		 *
		 * @since 1.0.0
		 * @param string $page Page key.
		 */
		do_action( 'gmm_admin_after_page', $page );
	}

	/**
	 * Human-readable page title.
	 *
	 * @param string $page Page key.
	 * @return string
	 */
	private static function get_page_title( $page ) {
		$titles = array(
			'dashboard' => __( 'Dashboard', 'gospel-music-mastery' ),
			'teachers'  => __( 'Teachers', 'gospel-music-mastery' ),
			'students'  => __( 'Students', 'gospel-music-mastery' ),
			'classes'   => __( 'Classes', 'gospel-music-mastery' ),
			'bookings'  => __( 'Bookings', 'gospel-music-mastery' ),
			'payments'  => __( 'Payments', 'gospel-music-mastery' ),
			'programs'  => __( 'Programs', 'gospel-music-mastery' ),
			'blog'      => __( 'Blog', 'gospel-music-mastery' ),
			'settings'  => __( 'Settings', 'gospel-music-mastery' ),
		);

		return isset( $titles[ $page ] ) ? $titles[ $page ] : __( 'Gospel Music Mastery', 'gospel-music-mastery' );
	}
}
