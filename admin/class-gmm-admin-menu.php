<?php
/**
 * WordPress admin menu registration for GMM.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Admin_Menu
 *
 * Registers the top-level Gospel Music Mastery menu and submenus.
 */
class GMM_Admin_Menu {

	/**
	 * Capability required for all GMM admin screens.
	 *
	 * @var string
	 */
	const CAPABILITY = 'manage_options';

	/**
	 * Parent menu slug.
	 *
	 * @var string
	 */
	const PARENT_SLUG = 'gmm-dashboard';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_action( 'admin_menu', $instance, 'register_menus', 9 );
	}

	/**
	 * Add top-level and submenu pages.
	 *
	 * @return void
	 */
	public function register_menus() {
		add_menu_page(
			__( 'Gospel Music Mastery', 'gospel-music-mastery' ),
			__( 'Gospel Music Mastery', 'gospel-music-mastery' ),
			self::CAPABILITY,
			self::PARENT_SLUG,
			array( 'GMM_Admin_Pages', 'render_dashboard' ),
			'dashicons-welcome-learn-more',
			58
		);

		$submenus = self::get_submenu_definitions();

		foreach ( $submenus as $item ) {
			add_submenu_page(
				self::PARENT_SLUG,
				$item['title'],
				$item['menu_title'],
				self::CAPABILITY,
				$item['slug'],
				$item['callback']
			);
		}
	}

	/**
	 * Submenu definitions.
	 *
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_submenu_definitions() {
		return array(
			array(
				'title'      => __( 'Dashboard', 'gospel-music-mastery' ),
				'menu_title' => __( 'Dashboard', 'gospel-music-mastery' ),
				'slug'       => 'gmm-dashboard',
				'callback'   => array( 'GMM_Admin_Pages', 'render_dashboard' ),
			),
			array(
				'title'      => __( 'Teachers', 'gospel-music-mastery' ),
				'menu_title' => __( 'Teachers', 'gospel-music-mastery' ),
				'slug'       => 'gmm-teachers',
				'callback'   => array( 'GMM_Admin_Pages', 'render_teachers' ),
			),
			array(
				'title'      => __( 'Students', 'gospel-music-mastery' ),
				'menu_title' => __( 'Students', 'gospel-music-mastery' ),
				'slug'       => 'gmm-students',
				'callback'   => array( 'GMM_Admin_Pages', 'render_students' ),
			),
			array(
				'title'      => __( 'Classes', 'gospel-music-mastery' ),
				'menu_title' => __( 'Classes', 'gospel-music-mastery' ),
				'slug'       => 'gmm-classes',
				'callback'   => array( 'GMM_Admin_Pages', 'render_classes' ),
			),
			array(
				'title'      => __( 'Bookings', 'gospel-music-mastery' ),
				'menu_title' => __( 'Bookings', 'gospel-music-mastery' ),
				'slug'       => 'gmm-bookings',
				'callback'   => array( 'GMM_Admin_Pages', 'render_bookings' ),
			),
			array(
				'title'      => __( 'Payments', 'gospel-music-mastery' ),
				'menu_title' => __( 'Payments', 'gospel-music-mastery' ),
				'slug'       => 'gmm-payments',
				'callback'   => array( 'GMM_Admin_Pages', 'render_payments' ),
			),
			array(
				'title'      => __( 'Programs', 'gospel-music-mastery' ),
				'menu_title' => __( 'Programs', 'gospel-music-mastery' ),
				'slug'       => 'gmm-programs',
				'callback'   => array( 'GMM_Admin_Pages', 'render_programs' ),
			),
			array(
				'title'      => __( 'Settings', 'gospel-music-mastery' ),
				'menu_title' => __( 'Settings', 'gospel-music-mastery' ),
				'slug'       => 'gmm-settings',
				'callback'   => array( 'GMM_Admin_Pages', 'render_settings' ),
			),
		);
	}

	/**
	 * Whether the current admin screen is a GMM plugin page.
	 *
	 * @param string $hook Optional admin hook suffix from admin_enqueue_scripts.
	 * @return bool
	 */
	public static function is_gmm_admin_screen( $hook = '' ) {
		if ( ! is_admin() ) {
			return false;
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Read-only screen detection.
		$page = isset( $_GET['page'] ) ? sanitize_key( wp_unslash( $_GET['page'] ) ) : '';

		if ( '' !== $page && 0 === strpos( $page, 'gmm-' ) ) {
			return true;
		}

		$hook = is_string( $hook ) ? $hook : '';
		if ( '' !== $hook && ( false !== strpos( $hook, 'gmm-' ) || false !== strpos( $hook, 'gmm_' ) ) ) {
			return true;
		}

		return false;
	}
}
