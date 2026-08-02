<?php
/**
 * Plugin CSS and JavaScript asset management.
 *
 * Loads frozen Gospel Music Mastery / Eduka design assets from the plugin
 * directory via GMM_URL, plus thin gmm-* integration layers.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Assets
 *
 * Registers and conditionally enqueues plugin + design assets.
 * Does not modify theme files.
 */
class GMM_Assets {

	/**
	 * Whether frontend assets were already enqueued this request.
	 *
	 * @var bool
	 */
	private static $frontend_loaded = false;

	/**
	 * Whether dashboard assets were already enqueued this request.
	 *
	 * @var bool
	 */
	private static $dashboard_loaded = false;

	/**
	 * Whether admin-only CSS was enqueued.
	 *
	 * @var bool
	 */
	private static $admin_loaded = false;

	/**
	 * Whether styles/scripts have been registered this request.
	 *
	 * @var bool
	 */
	private static $registered = false;

	/**
	 * Forced load requested by a shortcode after the main enqueue phase.
	 *
	 * @var bool
	 */
	private static $force_print_late = false;

	/**
	 * Register enqueue hooks with WordPress.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		// Resolve page context before enqueue (queried object is ready on `wp`).
		$loader->add_action( 'wp', $instance, 'prime_page_context', 5 );
		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_public', 5 );
		$loader->add_action( 'admin_enqueue_scripts', $instance, 'maybe_enqueue_admin', 5 );
		$loader->add_action( 'wp_footer', $instance, 'print_late_assets', 1 );
		$loader->add_action( 'admin_footer', $instance, 'print_late_assets', 1 );
	}

	/**
	 * Warm page-detection caches once the main query is available.
	 *
	 * @return void
	 */
	public function prime_page_context() {
		self::is_gmm_page();
		self::is_gmm_dashboard_page();
	}

	/**
	 * Conditionally enqueue public/frontend assets.
	 *
	 * @return void
	 */
	public function maybe_enqueue_public() {
		if ( self::is_gmm_page() || self::is_frontend_context() ) {
			self::enqueue_frontend();
		}

		if ( self::is_gmm_dashboard_page() || self::is_portal_dashboard_context() ) {
			self::enqueue_dashboard();
		}
	}

	/**
	 * Conditionally enqueue admin dashboard assets.
	 *
	 * @param string $hook Current admin page hook.
	 * @return void
	 */
	public function maybe_enqueue_admin( $hook = '' ) {
		if ( self::is_admin_dashboard_context( $hook ) ) {
			self::enqueue_dashboard();
			self::enqueue_admin();
		}
	}

	/**
	 * Absolute URL under the plugin assets directory.
	 *
	 * @param string $relative Path relative to assets/ (e.g. css/gmm-core.css).
	 * @return string
	 */
	public static function url( $relative ) {
		$relative = ltrim( str_replace( '\\', '/', (string) $relative ), '/' );
		if ( false !== strpos( $relative, '..' ) ) {
			return '';
		}
		if ( '' === $relative ) {
			return trailingslashit( GMM_URL . 'assets' );
		}
		return GMM_URL . 'assets/' . $relative;
	}

	/**
	 * Register all plugin styles and scripts (without enqueuing).
	 *
	 * @return void
	 */
	public static function register() {
		if ( self::$registered ) {
			return;
		}

		$version = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';

		// Frozen design stack (copied into plugin assets/).
		wp_register_style( 'gmm-design-bootstrap', self::url( 'css/bootstrap.min.css' ), array(), $version );
		wp_register_style( 'gmm-design-fontawesome', self::url( 'css/all-fontawesome.min.css' ), array(), $version );
		wp_register_style( 'gmm-design-animate', self::url( 'css/animate.min.css' ), array(), $version );
		wp_register_style( 'gmm-design-magnific', self::url( 'css/magnific-popup.min.css' ), array(), $version );
		wp_register_style( 'gmm-design-owl', self::url( 'css/owl.carousel.min.css' ), array(), $version );
		wp_register_style( 'gmm-design-style', self::url( 'css/style.css' ), array( 'gmm-design-bootstrap' ), $version );
		wp_register_style( 'gmm-design-gospel', self::url( 'css/gospel-custom.css' ), array( 'gmm-design-style' ), $version );
		wp_register_style( 'gmm-design-animations-css', self::url( 'css/gospel-animations.css' ), array( 'gmm-design-gospel' ), $version );
		wp_register_style( 'gmm-design-form-feedback', self::url( 'css/form-feedback.css' ), array( 'gmm-design-gospel' ), $version );
		wp_register_style( 'gmm-design-responsive', self::url( 'css/gospel-responsive.css' ), array( 'gmm-design-gospel' ), $version );

		$design_deps = array(
			'gmm-design-bootstrap',
			'gmm-design-fontawesome',
			'gmm-design-animate',
			'gmm-design-magnific',
			'gmm-design-owl',
			'gmm-design-style',
			'gmm-design-gospel',
			'gmm-design-animations-css',
			'gmm-design-form-feedback',
			'gmm-design-responsive',
		);

		// Integration layers — always via GMM_URL.
		wp_register_style( 'gmm-core-style', self::url( 'css/gmm-core.css' ), $design_deps, $version );
		wp_register_style( 'gmm-components-style', self::url( 'css/gmm-components.css' ), array( 'gmm-core-style' ), $version );
		wp_register_style( 'gmm-frontend-style', self::url( 'css/gmm-frontend.css' ), array( 'gmm-core-style', 'gmm-components-style' ), $version );
		wp_register_style( 'gmm-dashboard-style', self::url( 'css/gmm-dashboard.css' ), array( 'gmm-core-style', 'gmm-components-style' ), $version );
		wp_register_style( 'gmm-admin-style', self::url( 'css/gmm-admin.css' ), array( 'gmm-dashboard-style' ), $version );

		// Design JS (theme stack) — jQuery from WordPress core.
		wp_register_script( 'gmm-design-bootstrap', self::url( 'js/bootstrap.bundle.min.js' ), array( 'jquery' ), $version, true );
		wp_register_script( 'gmm-design-wow', self::url( 'js/wow.min.js' ), array( 'jquery' ), $version, true );
		wp_register_script( 'gmm-design-owl', self::url( 'js/owl.carousel.min.js' ), array( 'jquery' ), $version, true );
		wp_register_script( 'gmm-design-magnific', self::url( 'js/jquery.magnific-popup.min.js' ), array( 'jquery' ), $version, true );
		wp_register_script( 'gmm-design-appear', self::url( 'js/jquery.appear.min.js' ), array( 'jquery' ), $version, true );
		wp_register_script( 'gmm-design-isotope', self::url( 'js/isotope.pkgd.min.js' ), array( 'jquery' ), $version, true );
		wp_register_script(
			'gmm-design-main',
			self::url( 'js/main.js' ),
			array(
				'jquery',
				'gmm-design-bootstrap',
				'gmm-design-owl',
				'gmm-design-wow',
				'gmm-design-magnific',
				'gmm-design-appear',
				'gmm-design-isotope',
			),
			$version,
			true
		);
		wp_register_script( 'gmm-design-animations', self::url( 'js/animations.js' ), array( 'jquery' ), $version, true );
		wp_register_script( 'gmm-design-form-validation', self::url( 'js/form-validation.js' ), array( 'jquery' ), $version, true );

		// Plugin JS namespace.
		wp_register_script( 'gmm-core-script', self::url( 'js/gmm-core.js' ), array(), $version, true );
		wp_register_script( 'gmm-ajax-script', self::url( 'js/gmm-ajax.js' ), array( 'gmm-core-script' ), $version, true );
		wp_register_script( 'gmm-form-script', self::url( 'js/gmm-forms.js' ), array( 'gmm-core-script', 'jquery' ), $version, true );
		wp_register_script( 'gmm-animations-script', self::url( 'js/gmm-animations.js' ), array( 'gmm-core-script' ), $version, true );
		wp_register_script( 'gmm-dashboard-script', self::url( 'js/gmm-dashboard.js' ), array( 'gmm-core-script', 'jquery', 'gmm-ajax-script' ), $version, true );

		wp_register_script(
			'gmm-chartjs',
			'https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js',
			array(),
			'4.4.1',
			true
		);

		self::$registered = true;
	}

	/**
	 * Enqueue shared design CSS/JS once.
	 *
	 * @return void
	 */
	private static function enqueue_design_stack() {
		self::register();

		$styles = array(
			'gmm-design-bootstrap',
			'gmm-design-fontawesome',
			'gmm-design-animate',
			'gmm-design-magnific',
			'gmm-design-owl',
			'gmm-design-style',
			'gmm-design-gospel',
			'gmm-design-animations-css',
			'gmm-design-form-feedback',
			'gmm-design-responsive',
			'gmm-core-style',
			'gmm-components-style',
		);

		foreach ( $styles as $handle ) {
			wp_enqueue_style( $handle );
		}

		wp_enqueue_script( 'gmm-design-bootstrap' );
		wp_enqueue_script( 'gmm-design-wow' );
		wp_enqueue_script( 'gmm-design-owl' );
		wp_enqueue_script( 'gmm-design-magnific' );
		wp_enqueue_script( 'gmm-design-appear' );
		wp_enqueue_script( 'gmm-design-isotope' );
		wp_enqueue_script( 'gmm-design-main' );
		wp_enqueue_script( 'gmm-design-animations' );
		wp_enqueue_script( 'gmm-design-form-validation' );
	}

	/**
	 * Enqueue frontend (public) assets once.
	 *
	 * @return void
	 */
	public static function enqueue_frontend() {
		if ( self::$frontend_loaded ) {
			self::mark_late_print_needed();
			return;
		}

		self::enqueue_design_stack();

		wp_enqueue_style( 'gmm-frontend-style' );

		wp_enqueue_script( 'gmm-core-script' );
		wp_enqueue_script( 'gmm-ajax-script' );
		wp_enqueue_script( 'gmm-form-script' );
		wp_enqueue_script( 'gmm-animations-script' );

		self::localize_core();
		self::mark_late_print_needed();

		self::$frontend_loaded = true;

		/**
		 * Fires after GMM frontend assets are enqueued.
		 *
		 * @since 1.0.0
		 */
		do_action( 'gmm_frontend_assets_loaded' );
	}

	/**
	 * Enqueue dashboard assets once (admin / teacher / student portals).
	 *
	 * @return void
	 */
	public static function enqueue_dashboard() {
		if ( self::$dashboard_loaded ) {
			self::mark_late_print_needed();
			return;
		}

		self::enqueue_design_stack();

		wp_enqueue_style( 'gmm-dashboard-style' );

		wp_enqueue_script( 'gmm-core-script' );
		wp_enqueue_script( 'gmm-ajax-script' );
		wp_enqueue_script( 'gmm-form-script' );
		wp_enqueue_script( 'gmm-animations-script' );
		wp_enqueue_script( 'gmm-dashboard-script' );

		self::localize_core();
		self::mark_late_print_needed();

		self::$dashboard_loaded = true;

		/**
		 * Fires after GMM dashboard assets are enqueued.
		 *
		 * @since 1.0.0
		 */
		do_action( 'gmm_dashboard_assets_loaded' );
	}

	/**
	 * Enqueue WP Admin–only GMM styles (never on the public frontend).
	 *
	 * @return void
	 */
	public static function enqueue_admin() {
		if ( ! is_admin() ) {
			return;
		}

		if ( self::$admin_loaded ) {
			self::mark_late_print_needed();
			return;
		}

		self::register();
		wp_enqueue_style( 'gmm-admin-style' );
		self::mark_late_print_needed();
		self::$admin_loaded = true;

		/**
		 * Fires after GMM admin assets are enqueued.
		 *
		 * @since 1.0.0
		 */
		do_action( 'gmm_admin_assets_loaded' );
	}

	/**
	 * If assets were requested after wp_head, print them in the footer.
	 *
	 * @return void
	 */
	private static function mark_late_print_needed() {
		if ( did_action( 'wp_print_styles' ) || did_action( 'admin_print_styles' ) ) {
			self::$force_print_late = true;
		}
	}

	/**
	 * Print styles/scripts that were enqueued too late for the document head.
	 *
	 * @return void
	 */
	public function print_late_assets() {
		if ( ! self::$force_print_late ) {
			return;
		}

		self::$force_print_late = false;

		$styles = array(
			'gmm-design-bootstrap',
			'gmm-design-fontawesome',
			'gmm-design-animate',
			'gmm-design-magnific',
			'gmm-design-owl',
			'gmm-design-style',
			'gmm-design-gospel',
			'gmm-design-animations-css',
			'gmm-design-form-feedback',
			'gmm-design-responsive',
			'gmm-core-style',
			'gmm-components-style',
			'gmm-frontend-style',
			'gmm-dashboard-style',
			'gmm-admin-style',
		);

		if ( function_exists( 'wp_print_styles' ) ) {
			wp_print_styles( $styles );
		}
	}

	/**
	 * Localize shared data onto the core script.
	 *
	 * @return void
	 */
	private static function localize_core() {
		static $localized = false;

		if ( $localized ) {
			return;
		}

		if ( ! wp_script_is( 'gmm-core-script', 'enqueued' ) && ! wp_script_is( 'gmm-core-script', 'registered' ) ) {
			return;
		}

		wp_localize_script(
			'gmm-core-script',
			'GMM_DATA',
			array(
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'nonce'           => wp_create_nonce( 'gmm_nonce' ),
				'plugin_url'      => defined( 'GMM_URL' ) ? GMM_URL : '',
				'assets_url'      => self::url( '' ),
				'user_id'         => absint( get_current_user_id() ),
				'current_user_id' => absint( get_current_user_id() ),
				'media'           => class_exists( 'GMM_Media' ) ? GMM_Media::get_frontend_config() : array(),
			)
		);

		$localized = true;
	}

	/**
	 * Whether the current front request is any GMM shortcode page.
	 *
	 * @return bool
	 */
	public static function is_gmm_page() {
		static $cache = null;
		if ( null !== $cache ) {
			return $cache;
		}

		$cache = self::current_post_has_gmm_shortcode() || self::is_gmm_created_page();

		/**
		 * Filter whether the current front request is a GMM page.
		 *
		 * @since 1.0.0
		 * @param bool $is_gmm Default detection.
		 */
		$cache = (bool) apply_filters( 'gmm_is_gmm_page', $cache );
		return $cache;
	}

	/**
	 * Whether current page is a portal dashboard (student/teacher/admin shortcodes).
	 *
	 * @return bool
	 */
	public static function is_gmm_dashboard_page() {
		static $cache = null;
		if ( null !== $cache ) {
			return $cache;
		}

		$dashboard_tags = array(
			'gmm_student_dashboard',
			'gmm_student_profile',
			'gmm_student_lessons',
			'gmm_student_bookings',
			'gmm_student_favourites',
			'gmm_student_payments',
			'gmm_student_settings',
			'gmm_teacher_dashboard',
			'gmm_teacher_profile',
			'gmm_teacher_classes',
			'gmm_teacher_bookings',
			'gmm_teacher_availability',
			'gmm_teacher_withdrawals',
			'gmm_teacher_settings',
			'gmm_admin_dashboard',
			'gmm_admin_teachers',
			'gmm_admin_students',
			'gmm_admin_classes',
			'gmm_admin_bookings',
			'gmm_admin_payments',
			'gmm_admin_programs',
			'gmm_admin_blog',
			'gmm_admin_settings',
		);

		$cache = self::current_post_has_gmm_shortcode( $dashboard_tags );

		/**
		 * Filter portal dashboard context.
		 *
		 * @since 1.0.0
		 * @param bool $is_dashboard Default detection.
		 */
		$cache = (bool) apply_filters( 'gmm_is_gmm_dashboard_page', $cache );
		return $cache;
	}

	/**
	 * Detect GMM shortcodes in the main queried post (early, for wp_enqueue_scripts).
	 *
	 * @param array<int, string>|null $tags Optional tag allow-list.
	 * @return bool
	 */
	private static function current_post_has_gmm_shortcode( $tags = null ) {
		$post = get_queried_object();
		if ( ! $post instanceof WP_Post ) {
			$post = get_post();
		}
		if ( ! $post instanceof WP_Post || empty( $post->post_content ) ) {
			return false;
		}

		$content = (string) $post->post_content;

		if ( null === $tags ) {
			return ( false !== strpos( $content, '[gmm_' ) || false !== strpos( $content, 'gmm_' ) );
		}

		foreach ( $tags as $tag ) {
			$tag = sanitize_key( $tag );
			if ( ! $tag ) {
				continue;
			}
			if ( has_shortcode( $content, $tag ) || false !== strpos( $content, '[' . $tag ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether the current page ID was created by GMM_Pages.
	 *
	 * @return bool
	 */
	private static function is_gmm_created_page() {
		$page_id = (int) get_queried_object_id();
		if ( ! $page_id && is_singular() ) {
			$obj = get_queried_object();
			if ( $obj instanceof WP_Post ) {
				$page_id = (int) $obj->ID;
			}
		}
		if ( ! $page_id ) {
			return false;
		}

		$stored = get_option( 'gmm_created_pages', array() );
		if ( ! is_array( $stored ) ) {
			return false;
		}

		foreach ( $stored as $meta ) {
			if ( is_array( $meta ) ) {
				$stored_id = 0;
				if ( isset( $meta['page_id'] ) ) {
					$stored_id = absint( $meta['page_id'] );
				} elseif ( isset( $meta['id'] ) ) {
					$stored_id = absint( $meta['id'] );
				}
				if ( $stored_id && $stored_id === $page_id ) {
					return true;
				}
			} elseif ( is_numeric( $meta ) && (int) $meta === $page_id ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Whether current request is a GMM public frontend context.
	 *
	 * @return bool
	 */
	public static function is_frontend_context() {
		/**
		 * Filter whether GMM frontend assets should load.
		 *
		 * @since 1.0.0
		 * @param bool $load Default from page detection.
		 */
		return (bool) apply_filters( 'gmm_is_frontend_context', self::is_gmm_page() );
	}

	/**
	 * Whether current request is a teacher/student portal dashboard (front).
	 *
	 * @return bool
	 */
	public static function is_portal_dashboard_context() {
		/**
		 * Filter whether GMM portal dashboard assets should load on the front end.
		 *
		 * @since 1.0.0
		 * @param bool $load Default from page detection.
		 */
		return (bool) apply_filters( 'gmm_is_portal_dashboard_context', self::is_gmm_dashboard_page() );
	}

	/**
	 * Whether current admin screen is a GMM dashboard page.
	 *
	 * @param string $hook Admin hook suffix.
	 * @return bool
	 */
	public static function is_admin_dashboard_context( $hook = '' ) {
		$is_gmm_admin = false;

		if ( function_exists( 'gmm_is_plugin_admin_page' ) ) {
			$is_gmm_admin = gmm_is_plugin_admin_page( $hook );
		} else {
			$hook         = is_string( $hook ) ? $hook : '';
			$is_gmm_admin = ( false !== strpos( $hook, 'gmm-' ) || false !== strpos( $hook, 'gmm_' ) || false !== strpos( $hook, 'gospel-music-mastery' ) );
		}

		/**
		 * Filter whether GMM dashboard assets should load in wp-admin.
		 *
		 * @since 1.0.0
		 * @param bool   $load Default based on GMM admin screen hook.
		 * @param string $hook Admin page hook.
		 */
		return (bool) apply_filters( 'gmm_is_admin_dashboard_context', $is_gmm_admin, $hook );
	}
}
