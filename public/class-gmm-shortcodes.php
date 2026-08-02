<?php
/**
 * Frontend shortcode registration and routing.
 *
 * Powers all [gmm_*] shortcodes → templates/* without changing frozen UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Shortcodes
 *
 * Registers every frontend shortcode, enforces access control, loads templates
 * safely, and requests assets only when a GMM shortcode renders.
 */
class GMM_Shortcodes {

	const ACCESS_PUBLIC  = 'public';
	const ACCESS_STUDENT = 'student';
	const ACCESS_TEACHER = 'teacher';
	const ACCESS_ADMIN   = 'admin';

	/**
	 * Map of shortcode tag => config.
	 *
	 * @var array<string, array<string, string>>
	 */
	private $shortcodes = array();

	/**
	 * Singleton for static helpers.
	 *
	 * @var self|null
	 */
	private static $instance = null;

	/**
	 * Constructor — builds the shortcode map.
	 */
	public function __construct() {
		$this->shortcodes = $this->get_shortcode_map();
		self::$instance   = $this;
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
		$loader->add_action( 'template_redirect', $instance, 'maybe_protect_pages', 8 );
		$loader->add_action( 'admin_init', $instance, 'maybe_sync_pages', 20 );
		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_discovery_args', 15, 2 );
	}

	/**
	 * Register every GMM shortcode via add_shortcode().
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		foreach ( array_keys( $this->shortcodes ) as $tag ) {
			add_shortcode( $tag, array( $this, 'render_shortcode' ) );
		}

		/**
		 * Fires after all GMM shortcodes are registered.
		 *
		 * @since 1.0.0
		 * @param string[] $tags Registered tags.
		 */
		do_action( 'gmm_shortcodes_registered', array_keys( $this->shortcodes ) );
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
				'class'      => '',
				'teacher_id' => 0,
				'class_id'   => 0,
				'program_id' => 0,
			),
			is_array( $atts ) ? $atts : array(),
			$tag
		);

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
				'shortcode'  => $tag,
				'atts'       => $atts,
				'access'     => $access,
				'teacher_id' => absint( $atts['teacher_id'] ),
				'class_id'   => absint( $atts['class_id'] ),
				'program_id' => absint( $atts['program_id'] ),
			),
			$tag
		);

		$output = function_exists( 'gmm_get_template' )
			? gmm_get_template( $template, $args )
			: gmm_load_template( $template, $args );

		if ( '' === $output ) {
			$output = $this->placeholder_markup( $tag, $template );
		}

		$output = $this->maybe_prepend_asset_links( $output, $assets );

		/**
		 * Filter shortcode HTML before wrap.
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
			$output // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- templates escape their own output.
		);
	}

	/**
	 * Access checks for student / teacher / admin portals.
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
				$allowed = function_exists( 'gmm_student_can_access_dashboard' )
					? (bool) gmm_student_can_access_dashboard()
					: ( function_exists( 'gmm_is_student' ) && ( gmm_is_student() || ( function_exists( 'gmm_is_admin' ) && gmm_is_admin() ) ) );
				break;

			case self::ACCESS_TEACHER:
				if ( function_exists( 'gmm_is_admin' ) && gmm_is_admin() ) {
					$allowed = true;
				} elseif ( function_exists( 'gmm_teacher_can_access_dashboard' ) ) {
					$allowed = (bool) gmm_teacher_can_access_dashboard();
				} else {
					$allowed = function_exists( 'gmm_is_teacher' ) && gmm_is_teacher();
				}
				break;

			case self::ACCESS_ADMIN:
				$allowed = function_exists( 'gmm_is_admin' ) && gmm_is_admin();
				break;

			default:
				$allowed = false;
				break;
		}

		/**
		 * Filter shortcode access.
		 *
		 * @since 1.0.0
		 * @param bool   $allowed Whether access is allowed.
		 * @param string $access  Access role key.
		 */
		return (bool) apply_filters( 'gmm_shortcode_user_can_access', $allowed, $access );
	}

	/**
	 * Protect singular pages that only contain role-gated shortcodes.
	 *
	 * Complements student/teacher auth redirects with admin + generic ACL.
	 *
	 * @return void
	 */
	public function maybe_protect_pages() {
		if ( is_admin() || wp_doing_ajax() || ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( ! ( $post instanceof WP_Post ) || empty( $post->post_content ) ) {
			return;
		}

		$content = (string) $post->post_content;
		$found   = null;

		foreach ( $this->shortcodes as $tag => $config ) {
			if ( self::ACCESS_PUBLIC === $config['access'] ) {
				continue;
			}
			if ( has_shortcode( $content, $tag ) || false !== strpos( $content, '[' . $tag ) ) {
				$found = $config['access'];
				break;
			}
		}

		if ( null === $found ) {
			return;
		}

		if ( $this->user_can_access( $found ) ) {
			return;
		}

		// Guests: redirect to the matching login page when available.
		if ( ! is_user_logged_in() ) {
			$login = $this->login_url_for_access( $found );
			if ( $login ) {
				wp_safe_redirect( $login );
				exit;
			}
		}

		// Logged-in wrong role: leave shortcode to render the permission notice.
	}

	/**
	 * Sync missing GMM pages without overwriting existing ones.
	 *
	 * @return void
	 */
	public function maybe_sync_pages() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		if ( ! class_exists( 'GMM_Pages' ) ) {
			return;
		}

		$defs   = GMM_Pages::get_page_definitions();
		$stored = GMM_Pages::get_stored_pages();

		// Only sync when new definitions appear — never overwrite existing pages.
		if ( count( $stored ) >= count( $defs ) ) {
			return;
		}

		GMM_Pages::create_pages();
	}

	/**
	 * Inject discovery data for public listing shortcodes.
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Tag.
	 * @return array<string, mixed>
	 */
	public function inject_discovery_args( $args, $tag ) {
		$args = is_array( $args ) ? $args : array();

		if ( 'gmm_class_search' === $tag ) {
			$items = array();
			if ( class_exists( 'GMM_Search' ) ) {
				$result = GMM_Search::search_classes(
					array(
						'public'   => true,
						'statuses' => array( 'approved', 'published', 'active' ),
						'per_page' => 24,
						'page'     => 1,
					)
				);
				$rows = isset( $result['items'] ) && is_array( $result['items'] ) ? $result['items'] : array();
				foreach ( $rows as $row ) {
					$teacher = trim(
						( isset( $row['teacher_first_name'] ) ? (string) $row['teacher_first_name'] : '' ) . ' ' .
						( isset( $row['teacher_last_name'] ) ? (string) $row['teacher_last_name'] : '' )
					);
					$price = isset( $row['price'] ) ? (float) $row['price'] : 0.0;
					$items[] = array(
						'title'         => isset( $row['title'] ) ? (string) $row['title'] : '',
						'teacher_name'  => $teacher,
						'price_display' => '$' . number_format_i18n( $price, 0 ),
						'url'           => function_exists( 'gmm_get_page_link' )
							? add_query_arg(
								array(
									'teacher_id' => isset( $row['teacher_id'] ) ? absint( $row['teacher_id'] ) : 0,
									'class_id'   => isset( $row['id'] ) ? absint( $row['id'] ) : 0,
								),
								gmm_get_page_link( 'teacher_public_profile' )
							)
							: '#',
					);
				}
				$args['total_classes'] = isset( $result['total'] ) ? (int) $result['total'] : count( $items );
			}
			$args['classes'] = $items;
		}

		if ( 'gmm_program_search' === $tag ) {
			$items = array();
			if ( class_exists( 'GMM_Search' ) ) {
				$result = GMM_Search::search_programs(
					array(
						'public'   => true,
						'statuses' => array( 'published', 'active', 'approved' ),
						'per_page' => 24,
						'page'     => 1,
					)
				);
				$rows = isset( $result['items'] ) && is_array( $result['items'] ) ? $result['items'] : array();
				foreach ( $rows as $row ) {
					$items[] = array(
						'title'    => isset( $row['title'] ) ? (string) $row['title'] : '',
						'category' => isset( $row['category'] ) ? (string) $row['category'] : '',
						'url'      => '#',
					);
				}
				$args['total_programs'] = isset( $result['total'] ) ? (int) $result['total'] : count( $items );
			}
			$args['programs'] = $items;
		}

		if ( 'gmm_reviews' === $tag ) {
			$items = array();
			if ( class_exists( 'GMM_Reviews' ) && method_exists( 'GMM_Reviews', 'get_recent_approved_reviews' ) ) {
				$rows = GMM_Reviews::get_recent_approved_reviews( 12 );
			} else {
				$rows = self::query_recent_reviews( 12 );
			}
			foreach ( $rows as $row ) {
				$items[] = array(
					'student_name' => isset( $row['student_name'] ) ? (string) $row['student_name'] : __( 'Student', 'gospel-music-mastery' ),
					'teacher_name' => isset( $row['teacher_name'] ) ? (string) $row['teacher_name'] : '',
					'comment'      => isset( $row['comment'] ) ? (string) $row['comment'] : '',
					'rating'       => isset( $row['rating'] ) ? (float) $row['rating'] : 0,
				);
			}
			$args['reviews']       = $items;
			$args['total_reviews'] = count( $items );
		}

		return $args;
	}

	/**
	 * Recent approved reviews with names (fallback query).
	 *
	 * @param int $limit Limit.
	 * @return array<int, array<string, mixed>>
	 */
	private static function query_recent_reviews( $limit = 12 ) {
		if ( ! class_exists( 'GMM_Database' ) ) {
			return array();
		}

		global $wpdb;
		$reviews  = GMM_Database::table( 'reviews' );
		$students = GMM_Database::table( 'students' );
		$teachers = GMM_Database::table( 'teachers' );
		$limit    = max( 1, min( 50, absint( $limit ) ) );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT r.rating, r.comment,
					s.first_name AS student_first_name, s.last_name AS student_last_name,
					t.first_name AS teacher_first_name, t.last_name AS teacher_last_name
				FROM {$reviews} r
				LEFT JOIN {$students} s ON s.id = r.student_id
				LEFT JOIN {$teachers} t ON t.id = r.teacher_id
				WHERE r.status = %s
				ORDER BY r.created_at DESC
				LIMIT %d",
				'approved',
				$limit
			),
			ARRAY_A
		);
		$rows = is_array( $rows ) ? $rows : array();
		$out  = array();

		foreach ( $rows as $row ) {
			$out[] = array(
				'student_name' => trim(
					( isset( $row['student_first_name'] ) ? (string) $row['student_first_name'] : '' ) . ' ' .
					( isset( $row['student_last_name'] ) ? (string) $row['student_last_name'] : '' )
				),
				'teacher_name' => trim(
					( isset( $row['teacher_first_name'] ) ? (string) $row['teacher_first_name'] : '' ) . ' ' .
					( isset( $row['teacher_last_name'] ) ? (string) $row['teacher_last_name'] : '' )
				),
				'comment'      => isset( $row['comment'] ) ? (string) $row['comment'] : '',
				'rating'       => isset( $row['rating'] ) ? (float) $row['rating'] : 0,
			);
		}

		return $out;
	}

	/**
	 * List registered shortcode tags.
	 *
	 * @return string[]
	 */
	public function get_registered_tags() {
		return array_keys( $this->shortcodes );
	}

	/**
	 * Static map accessor.
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function get_map() {
		if ( self::$instance ) {
			return self::$instance->shortcodes;
		}
		$tmp = new self();
		return $tmp->shortcodes;
	}

	/**
	 * Whether a tag is a registered GMM shortcode.
	 *
	 * @param string $tag Tag.
	 * @return bool
	 */
	public static function is_registered( $tag ) {
		$map = self::get_map();
		return isset( $map[ sanitize_key( $tag ) ] );
	}

	/* -------------------------------------------------------------------------
	 * Internals
	 * ---------------------------------------------------------------------- */

	/**
	 * Escaped access-denied notice with optional login link.
	 *
	 * @param string $access Access role key.
	 * @return string
	 */
	private function access_denied_markup( $access ) {
		switch ( $access ) {
			case self::ACCESS_STUDENT:
				$message = __( 'This page is only available to logged-in students.', 'gospel-music-mastery' );
				break;
			case self::ACCESS_TEACHER:
				$message = __( 'This page is only available to approved teachers.', 'gospel-music-mastery' );
				break;
			case self::ACCESS_ADMIN:
				$message = __( 'This page is only available to administrators.', 'gospel-music-mastery' );
				break;
			default:
				$message = __( 'You do not have permission to view this content.', 'gospel-music-mastery' );
				break;
		}

		/**
		 * Filter access-denied message for shortcodes.
		 *
		 * @since 1.0.0
		 * @param string $message Default message.
		 * @param string $access  Access role key.
		 */
		$message = apply_filters( 'gmm_shortcode_access_denied_message', $message, $access );

		$login = $this->login_url_for_access( $access );
		$link  = '';
		if ( $login && ! is_user_logged_in() ) {
			$link = sprintf(
				' <a href="%1$s">%2$s</a>',
				esc_url( $login ),
				esc_html__( 'Sign in', 'gospel-music-mastery' )
			);
		}

		return sprintf(
			'<div class="gmm-notice gmm-notice--error" role="alert"><p>%1$s%2$s</p></div>',
			esc_html( $message ),
			$link // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped above.
		);
	}

	/**
	 * Login URL for a protected access level.
	 *
	 * @param string $access Access key.
	 * @return string
	 */
	private function login_url_for_access( $access ) {
		$key = '';
		if ( self::ACCESS_STUDENT === $access ) {
			$key = 'student_login';
		} elseif ( self::ACCESS_TEACHER === $access ) {
			$key = 'teacher_login';
		}

		$url = '';
		if ( $key && class_exists( 'GMM_Pages' ) ) {
			$url = GMM_Pages::get_page_url( $key );
		}
		if ( ! $url ) {
			$url = wp_login_url( get_permalink() ? get_permalink() : home_url( '/' ) );
		}

		return $url ? esc_url_raw( $url ) : '';
	}

	/**
	 * Admin-only missing-template notice.
	 *
	 * @param string $tag      Shortcode tag.
	 * @param string $template Template path key.
	 * @return string
	 */
	private function placeholder_markup( $tag, $template ) {
		if ( ! current_user_can( 'manage_options' ) ) {
			return sprintf(
				'<div class="gmm-notice gmm-notice--error" role="alert"><p>%s</p></div>',
				esc_html__( 'This page is temporarily unavailable.', 'gospel-music-mastery' )
			);
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
	 * Inject stylesheet links when WP never printed GMM CSS in the head.
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
		$map = array(
			// Auth.
			'gmm_student_login'    => array(
				'template' => 'student/login',
				'access'   => self::ACCESS_PUBLIC,
				'assets'   => 'frontend',
			),
			'gmm_student_register' => array(
				'template' => 'student/register',
				'access'   => self::ACCESS_PUBLIC,
				'assets'   => 'frontend',
			),
			'gmm_teacher_login'    => array(
				'template' => 'teacher/login',
				'access'   => self::ACCESS_PUBLIC,
				'assets'   => 'frontend',
			),
			'gmm_teacher_register' => array(
				'template' => 'teacher/register',
				'access'   => self::ACCESS_PUBLIC,
				'assets'   => 'frontend',
			),

			// Student portal.
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
			'gmm_student_payments'   => array(
				'template' => 'student/payments',
				'access'   => self::ACCESS_STUDENT,
				'assets'   => 'dashboard',
			),
			'gmm_student_favourites' => array(
				'template' => 'student/favourites',
				'access'   => self::ACCESS_STUDENT,
				'assets'   => 'dashboard',
			),
			'gmm_student_settings'   => array(
				'template' => 'student/settings',
				'access'   => self::ACCESS_STUDENT,
				'assets'   => 'dashboard',
			),

			// Teacher portal.
			'gmm_teacher_dashboard'    => array(
				'template' => 'teacher/dashboard',
				'access'   => self::ACCESS_TEACHER,
				'assets'   => 'dashboard',
			),
			'gmm_teacher_profile'      => array(
				'template' => 'teacher/profile',
				'access'   => self::ACCESS_TEACHER,
				'assets'   => 'dashboard',
			),
			'gmm_teacher_classes'      => array(
				'template' => 'teacher/classes',
				'access'   => self::ACCESS_TEACHER,
				'assets'   => 'dashboard',
			),
			'gmm_teacher_bookings'     => array(
				'template' => 'teacher/bookings',
				'access'   => self::ACCESS_TEACHER,
				'assets'   => 'dashboard',
			),
			'gmm_teacher_availability' => array(
				'template' => 'teacher/availability',
				'access'   => self::ACCESS_TEACHER,
				'assets'   => 'dashboard',
			),
			'gmm_teacher_withdrawals'  => array(
				'template' => 'teacher/withdrawals',
				'access'   => self::ACCESS_TEACHER,
				'assets'   => 'dashboard',
			),
			'gmm_teacher_settings'     => array(
				'template' => 'teacher/settings',
				'access'   => self::ACCESS_TEACHER,
				'assets'   => 'dashboard',
			),

			// Public discovery.
			'gmm_teacher_search'         => array(
				'template' => 'public/teachers',
				'access'   => self::ACCESS_PUBLIC,
				'assets'   => 'frontend',
			),
			'gmm_teacher_public_profile' => array(
				'template' => 'public/teacher-profile',
				'access'   => self::ACCESS_PUBLIC,
				'assets'   => 'frontend',
			),
			'gmm_class_search'           => array(
				'template' => 'public/classes',
				'access'   => self::ACCESS_PUBLIC,
				'assets'   => 'frontend',
			),
			'gmm_program_search'         => array(
				'template' => 'public/programs',
				'access'   => self::ACCESS_PUBLIC,
				'assets'   => 'frontend',
			),
			'gmm_booking_form'           => array(
				'template' => 'public/booking-form',
				'access'   => self::ACCESS_STUDENT,
				'assets'   => 'frontend',
			),
			'gmm_reviews'                => array(
				'template' => 'public/reviews',
				'access'   => self::ACCESS_PUBLIC,
				'assets'   => 'frontend',
			),

			// Admin portal.
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
			'gmm_admin_settings'  => array(
				'template' => 'admin/settings',
				'access'   => self::ACCESS_ADMIN,
				'assets'   => 'dashboard',
			),
		);

		/**
		 * Filter the shortcode → template map.
		 *
		 * @since 1.0.0
		 * @param array<string, array<string, string>> $map Map.
		 */
		return apply_filters( 'gmm_shortcode_map', $map );
	}
}
