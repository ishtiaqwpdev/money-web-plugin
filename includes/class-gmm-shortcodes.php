<?php
/**
 * Plugin shortcode registration and rendering.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Shortcodes
 *
 * Registers all gmm_* shortcodes and routes them through gmm_get_template().
 * Templates are converted from the frozen HTML design.
 */
class GMM_Shortcodes {

	/**
	 * Access role: public (login/register).
	 *
	 * @var string
	 */
	const ACCESS_PUBLIC = 'public';

	/**
	 * Access role: student portal.
	 *
	 * @var string
	 */
	const ACCESS_STUDENT = 'student';

	/**
	 * Access role: teacher portal.
	 *
	 * @var string
	 */
	const ACCESS_TEACHER = 'teacher';

	/**
	 * Access role: administrator portal.
	 *
	 * @var string
	 */
	const ACCESS_ADMIN = 'admin';

	/**
	 * Map of shortcode tag => config.
	 *
	 * @var array<string, array<string, string>>
	 */
	private $shortcodes = array();

	/**
	 * Constructor — builds the shortcode map.
	 */
	public function __construct() {
		$this->shortcodes = $this->get_shortcode_map();
	}

	/**
	 * Register hooks with the plugin loader.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_action( 'init', $instance, 'register_shortcodes', 10 );
	}

	/**
	 * Register every GMM shortcode on init.
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		foreach ( array_keys( $this->shortcodes ) as $tag ) {
			add_shortcode( $tag, array( $this, 'render_shortcode' ) );
		}
	}

	/**
	 * Shortcode callback — WordPress passes ($atts, $content, $tag).
	 *
	 * @param array<string, mixed>|string $atts    Shortcode attributes.
	 * @param string|null                 $content Enclosed content.
	 * @param string                      $tag     Shortcode name.
	 * @return string
	 */
	public function render_shortcode( $atts = array(), $content = null, $tag = '' ) {
		$tag = is_string( $tag ) ? $tag : '';

		if ( ! isset( $this->shortcodes[ $tag ] ) ) {
			return '';
		}

		$config   = $this->shortcodes[ $tag ];
		$template = $config['template'];
		$access   = $config['access'];
		$assets   = isset( $config['assets'] ) ? $config['assets'] : 'frontend';

		$atts = shortcode_atts(
			array(
				'class' => '',
			),
			is_array( $atts ) ? $atts : array(),
			$tag
		);

		// Access control preparation (full ACL later).
		if ( ! $this->user_can_access( $access ) ) {
			return $this->access_denied_markup( $access );
		}

		$this->maybe_load_assets( $assets );

		/**
		 * Filter template args before load.
		 *
		 * @since 1.0.0
		 * @param array<string, mixed> $args Template args.
		 * @param string               $tag  Shortcode tag.
		 */
		$args = apply_filters(
			'gmm_shortcode_template_args',
			array(
				'shortcode' => $tag,
				'atts'      => $atts,
				'access'    => $access,
			),
			$tag
		);

		$output = gmm_get_template( $template, $args );

		// Fallback if template file is missing.
		if ( '' === $output ) {
			$output = $this->placeholder_markup( $tag, $template );
		}

		// If styles never made it into <head>, inject critical design CSS links once.
		$output = $this->maybe_prepend_asset_links( $output, $assets );

		/**
		 * Filter shortcode HTML before wrap (auth form wiring, etc.).
		 *
		 * @since 1.0.0
		 * @param string $output HTML.
		 * @param string $tag    Shortcode tag.
		 */
		$output = apply_filters( 'gmm_shortcode_html', $output, $tag );

		$extra_class = isset( $atts['class'] ) ? sanitize_html_class( $atts['class'] ) : '';
		$classes     = trim( 'gmm-shortcode gmm-shortcode--' . sanitize_html_class( $tag ) . ' ' . $extra_class );

		return sprintf(
			'<div class="%1$s" data-gmm-shortcode="%2$s">%3$s</div>',
			esc_attr( $classes ),
			esc_attr( $tag ),
			$output
		);
	}

	/**
	 * Prepared access checks — student / teacher / admin portals.
	 * Full permission matrix intentionally deferred.
	 *
	 * @param string $access Access key.
	 * @return bool
	 */
	public function user_can_access( $access ) {
		$allowed = true;

		switch ( $access ) {
			case self::ACCESS_PUBLIC:
				$allowed = true;
				break;

			case self::ACCESS_STUDENT:
				// Prepared: students (admins often preview). Full ACL later.
				$allowed = function_exists( 'gmm_is_student' ) && ( gmm_is_student() || gmm_is_admin() );
				break;

			case self::ACCESS_TEACHER:
				$allowed = function_exists( 'gmm_is_teacher' ) && ( gmm_is_teacher() || gmm_is_admin() );
				break;

			case self::ACCESS_ADMIN:
				$allowed = function_exists( 'gmm_is_admin' ) && gmm_is_admin();
				break;

			default:
				$allowed = false;
				break;
		}

		/**
		 * Filter shortcode access (preparation hook for full permissions).
		 *
		 * @since 1.0.0
		 * @param bool   $allowed Whether access is allowed.
		 * @param string $access  Access role key.
		 */
		return (bool) apply_filters( 'gmm_shortcode_user_can_access', $allowed, $access );
	}

	/**
	 * Escaped access-denied notice (no full login redirect yet).
	 *
	 * @param string $access Access role key.
	 * @return string
	 */
	private function access_denied_markup( $access ) {
		$message = __( 'You do not have permission to view this content.', 'gospel-music-mastery' );

		/**
		 * Filter access-denied message for shortcodes.
		 *
		 * @since 1.0.0
		 * @param string $message Default message.
		 * @param string $access  Access role key.
		 */
		$message = apply_filters( 'gmm_shortcode_access_denied_message', $message, $access );

		return sprintf(
			'<div class="gmm-notice gmm-notice--error" role="alert"><p>%s</p></div>',
			esc_html( $message )
		);
	}

	/**
	 * Safe placeholder until real templates are added.
	 *
	 * @param string $tag      Shortcode tag.
	 * @param string $template Template path key.
	 * @return string
	 */
	private function placeholder_markup( $tag, $template ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return '';
		}

		return sprintf(
			'<div class="gmm-notice gmm-notice--info"><p>%s</p></div>',
			esc_html(
				sprintf(
					/* translators: 1: shortcode tag, 2: template path */
					__( 'GMM shortcode [%1$s] is registered. Template pending: %2$s', 'gospel-music-mastery' ),
					$tag,
					$template . '.php'
				)
			)
		);
	}

	/**
	 * Load matching asset bundle when a shortcode renders.
	 *
	 * @param string $assets frontend|dashboard.
	 * @return void
	 */
	private function maybe_load_assets( $assets ) {
		if ( 'dashboard' === $assets && function_exists( 'gmm_load_dashboard_assets' ) ) {
			gmm_load_dashboard_assets();
			return;
		}

		if ( function_exists( 'gmm_load_frontend_assets' ) ) {
			gmm_load_frontend_assets();
		}
	}

	/**
	 * Inject stylesheet <link> tags when WP never printed GMM CSS in the head.
	 *
	 * Guarantees the frozen design CSS is present even if page detection failed
	 * during wp_enqueue_scripts.
	 *
	 * @param string $output Shortcode HTML.
	 * @param string $assets frontend|dashboard.
	 * @return string
	 */
	private function maybe_prepend_asset_links( $output, $assets ) {
		static $injected = false;

		if ( $injected ) {
			return $output;
		}

		// Styles already printed in head — nothing to do.
		if ( wp_style_is( 'gmm-core-style', 'done' ) || wp_style_is( 'gmm-design-gospel', 'done' ) ) {
			return $output;
		}

		if ( ! defined( 'GMM_URL' ) || ! defined( 'GMM_VERSION' ) ) {
			return $output;
		}

		$version = GMM_VERSION;
		$base    = trailingslashit( GMM_URL ) . 'assets/css/';

		$files = array(
			'bootstrap.min.css',
			'all-fontawesome.min.css',
			'animate.min.css',
			'magnific-popup.min.css',
			'owl.carousel.min.css',
			'style.css',
			'gospel-custom.css',
			'gospel-animations.css',
			'form-feedback.css',
			'gospel-responsive.css',
			'gmm-core.css',
			'gmm-components.css',
		);

		if ( 'dashboard' === $assets ) {
			$files[] = 'gmm-dashboard.css';
		} else {
			$files[] = 'gmm-frontend.css';
		}

		$links = array();
		foreach ( $files as $file ) {
			$path = GMM_PATH . 'assets/css/' . $file;
			if ( ! is_readable( $path ) ) {
				continue;
			}
			$url     = esc_url( $base . $file . '?ver=' . rawurlencode( (string) $version ) );
			$links[] = '<link rel="stylesheet" id="gmm-inline-' . esc_attr( sanitize_title( $file ) ) . '-css" href="' . $url . '" media="all" />';
		}

		if ( empty( $links ) ) {
			return $output;
		}

		$injected = true;

		return implode( "\n", $links ) . "\n" . $output;
	}

	/**
	 * Full map of gmm_* shortcodes → template + access.
	 *
	 * @return array<string, array<string, string>>
	 */
	private function get_shortcode_map() {
		return array(
			// Student.
			'gmm_student_login'      => array(
				'template' => 'student/login',
				'access'   => self::ACCESS_PUBLIC,
				'assets'   => 'frontend',
			),
			'gmm_student_register'   => array(
				'template' => 'student/register',
				'access'   => self::ACCESS_PUBLIC,
				'assets'   => 'frontend',
			),
			'gmm_student_dashboard'  => array(
				'template' => 'student/dashboard',
				'access'   => self::ACCESS_STUDENT,
				'assets'   => 'dashboard',
			),
			'gmm_student_profile'    => array(
				'template' => 'student/profile',
				'access'   => self::ACCESS_STUDENT,
				'assets'   => 'dashboard',
			),
			'gmm_student_lessons'    => array(
				'template' => 'student/lessons',
				'access'   => self::ACCESS_STUDENT,
				'assets'   => 'dashboard',
			),
			'gmm_student_bookings'   => array(
				'template' => 'student/bookings',
				'access'   => self::ACCESS_STUDENT,
				'assets'   => 'dashboard',
			),
			'gmm_booking_form'       => array(
				'template' => 'student/booking-form',
				'access'   => self::ACCESS_STUDENT,
				'assets'   => 'frontend',
			),
			'gmm_student_favourites' => array(
				'template' => 'student/favourites',
				'access'   => self::ACCESS_STUDENT,
				'assets'   => 'dashboard',
			),
			'gmm_student_payments'   => array(
				'template' => 'student/payments',
				'access'   => self::ACCESS_STUDENT,
				'assets'   => 'dashboard',
			),
			'gmm_student_settings'   => array(
				'template' => 'student/settings',
				'access'   => self::ACCESS_STUDENT,
				'assets'   => 'dashboard',
			),

			// Teacher.
			'gmm_teacher_login'         => array(
				'template' => 'teacher/login',
				'access'   => self::ACCESS_PUBLIC,
				'assets'   => 'frontend',
			),
			'gmm_teacher_register'      => array(
				'template' => 'teacher/register',
				'access'   => self::ACCESS_PUBLIC,
				'assets'   => 'frontend',
			),
			'gmm_teacher_dashboard'     => array(
				'template' => 'teacher/dashboard',
				'access'   => self::ACCESS_TEACHER,
				'assets'   => 'dashboard',
			),
			'gmm_teacher_profile'       => array(
				'template' => 'teacher/profile',
				'access'   => self::ACCESS_TEACHER,
				'assets'   => 'dashboard',
			),
			'gmm_teacher_classes'       => array(
				'template' => 'teacher/classes',
				'access'   => self::ACCESS_TEACHER,
				'assets'   => 'dashboard',
			),
			'gmm_teacher_bookings'      => array(
				'template' => 'teacher/bookings',
				'access'   => self::ACCESS_TEACHER,
				'assets'   => 'dashboard',
			),
			'gmm_teacher_availability'  => array(
				'template' => 'teacher/availability',
				'access'   => self::ACCESS_TEACHER,
				'assets'   => 'dashboard',
			),
			'gmm_teacher_withdrawals'   => array(
				'template' => 'teacher/withdrawals',
				'access'   => self::ACCESS_TEACHER,
				'assets'   => 'dashboard',
			),
			'gmm_teacher_settings'      => array(
				'template' => 'teacher/settings',
				'access'   => self::ACCESS_TEACHER,
				'assets'   => 'dashboard',
			),

			// Admin.
			'gmm_admin_dashboard' => array(
				'template' => 'admin/dashboard',
				'access'   => self::ACCESS_ADMIN,
				'assets'   => 'dashboard',
			),
			'gmm_admin_teachers'  => array(
				'template' => 'admin/teachers',
				'access'   => self::ACCESS_ADMIN,
				'assets'   => 'dashboard',
			),
			'gmm_admin_students'  => array(
				'template' => 'admin/students',
				'access'   => self::ACCESS_ADMIN,
				'assets'   => 'dashboard',
			),
			'gmm_admin_classes'   => array(
				'template' => 'admin/classes',
				'access'   => self::ACCESS_ADMIN,
				'assets'   => 'dashboard',
			),
			'gmm_admin_bookings'  => array(
				'template' => 'admin/bookings',
				'access'   => self::ACCESS_ADMIN,
				'assets'   => 'dashboard',
			),
			'gmm_admin_payments'  => array(
				'template' => 'admin/payments',
				'access'   => self::ACCESS_ADMIN,
				'assets'   => 'dashboard',
			),
			'gmm_admin_programs'  => array(
				'template' => 'admin/programs',
				'access'   => self::ACCESS_ADMIN,
				'assets'   => 'dashboard',
			),
			'gmm_admin_blog'      => array(
				'template' => 'admin/blog',
				'access'   => self::ACCESS_ADMIN,
				'assets'   => 'dashboard',
			),
			'gmm_admin_settings'  => array(
				'template' => 'admin/settings',
				'access'   => self::ACCESS_ADMIN,
				'assets'   => 'dashboard',
			),
		);
	}

	/**
	 * List registered shortcode tags (for debugging / future admin UI).
	 *
	 * @return string[]
	 */
	public function get_registered_tags() {
		return array_keys( $this->shortcodes );
	}
}
