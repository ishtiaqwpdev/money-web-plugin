<?php
/**
 * Student registration, login, and dashboard access.
 *
 * Wires [gmm_student_register] / [gmm_student_login] to frozen templates
 * without changing markup, CSS, or theme files.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Student_Auth
 */
class GMM_Student_Auth {

	const NONCE_ACTION = 'gmm_auth_action';
	const NONCE_FIELD  = 'gmm_auth_nonce';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		// Priority 5 — before legacy GMM_Auth student handlers.
		$loader->add_action( 'admin_post_nopriv_gmm_student_register', $instance, 'handle_register', 5 );
		$loader->add_action( 'admin_post_gmm_student_register', $instance, 'handle_register', 5 );
		$loader->add_action( 'admin_post_nopriv_gmm_student_login', $instance, 'handle_login', 5 );
		$loader->add_action( 'admin_post_gmm_student_login', $instance, 'handle_login', 5 );

		$loader->add_action( 'wp_ajax_nopriv_gmm_student_register', $instance, 'ajax_register' );
		$loader->add_action( 'wp_ajax_gmm_student_register', $instance, 'ajax_register' );
		$loader->add_action( 'wp_ajax_nopriv_gmm_student_login', $instance, 'ajax_login' );
		$loader->add_action( 'wp_ajax_gmm_student_login', $instance, 'ajax_login' );

		$loader->add_filter( 'gmm_shortcode_html', $instance, 'enhance_auth_html', 15, 2 );
		$loader->add_action( 'template_redirect', $instance, 'maybe_redirect_protected_pages', 5 );
		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 40 );
	}

	/**
	 * Register a student: WP user (gmm_student) + gmm_students row (active).
	 *
	 * @param array<string, mixed> $data  Registration fields.
	 * @param string               $nonce Optional nonce (verified when non-empty).
	 * @return int|WP_Error User ID.
	 */
	public static function register( $data, $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		$data  = is_array( $data ) ? $data : array();
		$clean = self::sanitize_registration( $data );
		if ( is_wp_error( $clean ) ) {
			return $clean;
		}

		$user_id = self::create_wp_user( $clean );
		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		$profile = self::create_student_profile( $user_id, $clean );
		if ( is_wp_error( $profile ) ) {
			return $profile;
		}

		/**
		 * Fires after student registration (email system may listen later).
		 *
		 * @since 1.0.0
		 * @param int                  $user_id User ID.
		 * @param array<string, mixed> $clean   Sanitized registration data.
		 */
		do_action( 'gmm_student_registered', $user_id, $clean );

		/**
		 * Fires after any GMM user registration.
		 *
		 * @since 1.0.0
		 * @param int                  $user_id User ID.
		 * @param array<string, mixed> $clean   Sanitized fields.
		 * @param string               $role    Role key.
		 */
		do_action( 'gmm_user_registered', $user_id, $clean, GMM_Roles::ROLE_STUDENT );

		return $user_id;
	}

	/**
	 * Student login via wp_signon.
	 *
	 * @param string $login    Username or email.
	 * @param string $password Password.
	 * @param bool   $remember Remember me.
	 * @param string $nonce    Optional nonce.
	 * @return WP_User|WP_Error
	 */
	public static function login( $login, $password, $remember = false, $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		if ( class_exists( 'GMM_Auth' ) ) {
			return GMM_Auth::login_user( $login, $password, $remember, GMM_Roles::ROLE_STUDENT, '' );
		}

		$login    = sanitize_text_field( (string) $login );
		$password = (string) $password;

		if ( '' === $login || '' === $password ) {
			return new WP_Error( 'gmm_empty', __( 'Username and password are required.', 'gospel-music-mastery' ) );
		}

		if ( is_email( $login ) ) {
			$user_by_email = get_user_by( 'email', $login );
			if ( $user_by_email ) {
				$login = $user_by_email->user_login;
			}
		}

		$user = wp_signon(
			array(
				'user_login'    => $login,
				'user_password' => $password,
				'remember'      => (bool) $remember,
			),
			is_ssl()
		);

		if ( is_wp_error( $user ) ) {
			return new WP_Error( 'gmm_login_failed', __( 'Invalid login credentials.', 'gospel-music-mastery' ) );
		}

		if ( ! user_can( $user, 'manage_options' ) && ! in_array( GMM_Roles::ROLE_STUDENT, (array) $user->roles, true ) ) {
			wp_logout();
			return new WP_Error( 'gmm_wrong_portal', __( 'This account cannot sign in through this portal.', 'gospel-music-mastery' ) );
		}

		wp_set_current_user( $user->ID );
		do_action( 'gmm_user_logged_in', $user );

		return $user;
	}

	/**
	 * Whether user may access the student dashboard / portal pages.
	 *
	 * @param int $user_id Optional WP user ID.
	 * @return bool
	 */
	public static function can_access_dashboard( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id || ! is_user_logged_in() ) {
			return false;
		}

		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}

		$user = get_userdata( $user_id );
		if ( ! ( $user instanceof WP_User ) ) {
			return false;
		}

		if ( ! in_array( GMM_Roles::ROLE_STUDENT, (array) $user->roles, true ) ) {
			return false;
		}

		// Soft-check profile status when present.
		if ( class_exists( 'GMM_Student' ) ) {
			$profile = GMM_Student::get_profile( $user_id );
			if ( is_array( $profile ) && isset( $profile['status'] ) ) {
				$status = sanitize_key( (string) $profile['status'] );
				if ( in_array( $status, array( 'inactive', 'suspended', 'trash', 'deleted' ), true ) ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Require dashboard access or redirect to student login.
	 *
	 * @return void
	 */
	public static function require_dashboard_access() {
		if ( self::can_access_dashboard() ) {
			return;
		}

		$login = class_exists( 'GMM_Pages' ) ? GMM_Pages::get_page_url( 'student_login' ) : '';
		$login = $login ? $login : wp_login_url();
		wp_safe_redirect( $login );
		exit;
	}

	/**
	 * Verify auth nonce.
	 *
	 * @param string $nonce Nonce.
	 * @return bool
	 */
	public static function verify_nonce( $nonce ) {
		return (bool) wp_verify_nonce( (string) $nonce, self::NONCE_ACTION );
	}

	/**
	 * admin-post registration handler.
	 *
	 * @return void
	 */
	public function handle_register() {
		$back = isset( $_POST['gmm_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['gmm_redirect'] ) ) : home_url( '/' );

		check_admin_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$nonce  = isset( $_POST[ self::NONCE_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ) : '';
		$result = self::register( wp_unslash( $_POST ), $nonce );

		if ( is_wp_error( $result ) ) {
			self::redirect_with_flash( $back, 'error', $result->get_error_message() );
		}

		$user = get_userdata( (int) $result );
		if ( $user ) {
			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID, true, is_ssl() );
			$dest = class_exists( 'GMM_Auth' ) ? GMM_Auth::get_login_redirect_url( $user ) : self::dashboard_url();
			wp_safe_redirect( $dest );
			exit;
		}

		self::redirect_with_flash( $back, 'success', __( 'Registration successful.', 'gospel-music-mastery' ) );
	}

	/**
	 * admin-post login handler.
	 *
	 * @return void
	 */
	public function handle_login() {
		$back = isset( $_POST['gmm_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['gmm_redirect'] ) ) : home_url( '/' );

		check_admin_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$nonce    = isset( $_POST[ self::NONCE_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ) : '';
		$login    = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
		$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
		$remember = ! empty( $_POST['rememberme'] );

		$user = self::login( $login, $password, $remember, $nonce );

		if ( is_wp_error( $user ) ) {
			self::redirect_with_flash( $back, 'error', $user->get_error_message() );
		}

		if ( ! self::can_access_dashboard( $user->ID ) ) {
			wp_logout();
			self::redirect_with_flash( $back, 'error', __( 'Your student account cannot access the dashboard.', 'gospel-music-mastery' ) );
		}

		$dest = class_exists( 'GMM_Auth' ) ? GMM_Auth::get_login_redirect_url( $user ) : self::dashboard_url();
		wp_safe_redirect( $dest );
		exit;
	}

	/**
	 * AJAX student registration.
	 *
	 * @return void
	 */
	public function ajax_register() {
		check_ajax_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$result = self::register( wp_unslash( $_POST ), '' );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'code'    => $result->get_error_code(),
					'message' => $result->get_error_message(),
				),
				400
			);
		}

		$user = get_userdata( (int) $result );
		if ( $user ) {
			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID, true, is_ssl() );
		}

		$redirect = class_exists( 'GMM_Auth' ) && $user
			? GMM_Auth::get_login_redirect_url( $user )
			: self::dashboard_url();

		wp_send_json_success(
			array(
				'user_id'  => (int) $result,
				'message'  => __( 'Registration successful.', 'gospel-music-mastery' ),
				'redirect' => $redirect,
				'profile'  => function_exists( 'gmm_get_student_profile' )
					? gmm_get_student_profile( (int) $result )
					: null,
			)
		);
	}

	/**
	 * AJAX student login.
	 *
	 * @return void
	 */
	public function ajax_login() {
		check_ajax_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$login    = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
		$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
		$remember = ! empty( $_POST['rememberme'] );

		$user = self::login( $login, $password, $remember, '' );

		if ( is_wp_error( $user ) ) {
			wp_send_json_error(
				array(
					'code'    => $user->get_error_code(),
					'message' => $user->get_error_message(),
				),
				400
			);
		}

		if ( ! self::can_access_dashboard( $user->ID ) ) {
			wp_logout();
			wp_send_json_error(
				array(
					'code'    => 'gmm_dashboard',
					'message' => __( 'Your student account cannot access the dashboard.', 'gospel-music-mastery' ),
				),
				403
			);
		}

		$redirect = class_exists( 'GMM_Auth' )
			? GMM_Auth::get_login_redirect_url( $user )
			: self::dashboard_url();

		wp_send_json_success(
			array(
				'user_id'  => (int) $user->ID,
				'message'  => __( 'Login successful.', 'gospel-music-mastery' ),
				'redirect' => $redirect,
			)
		);
	}

	/**
	 * Enhance frozen auth forms: action, nonce, links, flash messages.
	 * Also inject optional learning fields using existing form classes.
	 *
	 * @param string $html HTML.
	 * @param string $tag  Shortcode.
	 * @return string
	 */
	public function enhance_auth_html( $html, $tag ) {
		$map = array(
			'gmm_student_register' => array(
				'form_id'       => 'student-register-form',
				'action'        => 'gmm_student_register',
				'error_id'      => 'register-error',
				'error_text_id' => 'register-error-text',
				'is_register'   => true,
			),
			'gmm_student_login'    => array(
				'form_id'       => 'student-login-form',
				'action'        => 'gmm_student_login',
				'error_id'      => 'student-login-error',
				'error_text_id' => 'student-login-error-text',
				'is_register'   => false,
			),
		);

		if ( ! isset( $map[ $tag ] ) || '' === $html ) {
			return $html;
		}

		$cfg    = $map[ $tag ];
		$action = esc_url( admin_url( 'admin-post.php' ) );
		$nonce  = wp_create_nonce( self::NONCE_ACTION );
		$hidden = '<input type="hidden" name="action" value="' . esc_attr( $cfg['action'] ) . '" />'
			. '<input type="hidden" name="' . esc_attr( self::NONCE_FIELD ) . '" value="' . esc_attr( $nonce ) . '" />'
			. '<input type="hidden" name="gmm_redirect" value="' . esc_attr( self::current_url() ) . '" />'
			. '<input type="hidden" name="_wp_http_referer" value="' . esc_attr( self::current_url() ) . '" />';

		$html = preg_replace(
			'/(<form\b[^>]*\bid=["\']' . preg_quote( $cfg['form_id'], '/' ) . '["\'][^>]*\baction=["\'])([^"\']*)(["\'])/i',
			'$1' . $action . '$3',
			$html,
			1
		);

		if ( false === strpos( $html, 'name="' . self::NONCE_FIELD . '"' ) ) {
			$html = preg_replace(
				'/(<form\b[^>]*\bid=["\']' . preg_quote( $cfg['form_id'], '/' ) . '["\'][^>]*>)/i',
				'$1' . $hidden,
				$html,
				1
			);
		}

		if ( ! empty( $cfg['is_register'] ) && false === strpos( $html, 'name="phone"' ) ) {
			$extra = self::render_optional_profile_fields();
			$html  = preg_replace(
				'/(<div class="agreement-area">)/',
				$extra . '$1',
				$html,
				1
			);
		}

		if ( false !== strpos( $tag, 'login' ) ) {
			$html = preg_replace(
				'/(id=["\']remember-me["\'][^>]*)(>)/i',
				'$1 name="rememberme" value="1"$2',
				$html,
				1
			);

			$reset = class_exists( 'GMM_Auth' )
				? GMM_Auth::get_password_reset_url( self::current_url() )
				: wp_lostpassword_url();
			$html  = preg_replace(
				'/href=["\']forgot-password\.html[^"\']*["\']/',
				'href="' . esc_url( $reset ) . '"',
				$html,
				1
			);

			$home = home_url( '/' );
			$html = preg_replace(
				'/href=["\']login\.html["\']/',
				'href="' . esc_url( $home ) . '"',
				$html,
				1
			);
		}

		if ( ! empty( $cfg['is_register'] ) ) {
			$agreement = function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'student_agreement' ) : '';
			if ( ! $agreement && class_exists( 'GMM_Pages' ) ) {
				$agreement = GMM_Pages::get_page_url( 'student_agreement' );
			}
			if ( $agreement ) {
				$html = preg_replace(
					'/href=["\']student-agreement\.html["\']/',
					'href="' . esc_url( $agreement ) . '"',
					$html,
					1
				);
			}
		}

		$error = self::consume_flash( 'error' );
		if ( $error ) {
			$html = preg_replace(
				'/(id=["\']' . preg_quote( $cfg['error_id'], '/' ) . '["\'][^>]*)\s+hidden/i',
				'$1',
				$html,
				1
			);
			$html = preg_replace(
				'/(id=["\']' . preg_quote( $cfg['error_text_id'], '/' ) . '["\'][^>]*>)(.*?)(<\/span>)/is',
				'$1' . esc_html( $error ) . '$3',
				$html,
				1
			);
		}

		$success = self::consume_flash( 'success' );
		if ( $success && preg_match( '/id=["\']register-success["\']/', $html ) ) {
			$html = preg_replace(
				'/(id=["\']register-success["\'][^>]*)\s+hidden/i',
				'$1',
				$html,
				1
			);
			$html = preg_replace(
				'/(id=["\']register-success["\'][^>]*>\s*(?:<i[^>]*>.*?<\/i>\s*)?<span>)(.*?)(<\/span>)/is',
				'$1' . esc_html( $success ) . '$3',
				$html,
				1
			);
		}

		return $html;
	}

	/**
	 * Redirect guests away from student portal pages to login.
	 *
	 * @return void
	 */
	public function maybe_redirect_protected_pages() {
		if ( is_admin() || wp_doing_ajax() || ! is_singular() ) {
			return;
		}

		$post = get_queried_object();
		if ( ! ( $post instanceof WP_Post ) ) {
			return;
		}

		$content = (string) $post->post_content;
		$protected = array(
			'gmm_student_dashboard',
			'gmm_student_profile',
			'gmm_student_lessons',
			'gmm_student_bookings',
			'gmm_student_favourites',
			'gmm_student_payments',
			'gmm_student_settings',
			'gmm_booking_form',
		);

		$needs = false;
		foreach ( $protected as $tag ) {
			if ( has_shortcode( $content, $tag ) || false !== strpos( $content, '[' . $tag ) ) {
				$needs = true;
				break;
			}
		}

		if ( ! $needs ) {
			return;
		}

		if ( self::can_access_dashboard() ) {
			return;
		}

		$login = class_exists( 'GMM_Pages' ) ? GMM_Pages::get_page_url( 'student_login' ) : '';
		$login = $login ? $login : wp_login_url( get_permalink( $post ) );
		wp_safe_redirect( $login );
		exit;
	}

	/**
	 * Enqueue lightweight auth AJAX script on student auth pages.
	 *
	 * @return void
	 */
	public function maybe_enqueue_assets() {
		if ( ! class_exists( 'GMM_Assets' ) || ! GMM_Assets::is_gmm_page() ) {
			return;
		}

		$post    = get_queried_object();
		$content = ( $post instanceof WP_Post ) ? (string) $post->post_content : '';
		$needed  = has_shortcode( $content, 'gmm_student_register' )
			|| has_shortcode( $content, 'gmm_student_login' )
			|| false !== strpos( $content, 'gmm_student_register' )
			|| false !== strpos( $content, 'gmm_student_login' );

		if ( ! $needed ) {
			return;
		}

		$version = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';
		wp_enqueue_script(
			'gmm-student-auth',
			GMM_URL . 'assets/js/gmm-student-auth.js',
			array( 'gmm-core-script' ),
			$version,
			true
		);

		wp_localize_script(
			'gmm-student-auth',
			'GMM_STUDENT_AUTH',
			array(
				'nonce'      => wp_create_nonce( self::NONCE_ACTION ),
				'nonceField' => self::NONCE_FIELD,
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'actions'    => array(
					'register' => 'gmm_student_register',
					'login'    => 'gmm_student_login',
				),
				'i18n'       => array(
					'error'   => __( 'Something went wrong. Please try again.', 'gospel-music-mastery' ),
					'success' => __( 'Registration successful.', 'gospel-music-mastery' ),
				),
			)
		);
	}

	/**
	 * Sanitize registration payload.
	 *
	 * @param array<string, mixed> $data Raw.
	 * @return array<string, string>|WP_Error
	 */
	private static function sanitize_registration( $data ) {
		$first = isset( $data['first_name'] ) ? sanitize_text_field( (string) $data['first_name'] ) : '';
		$last  = isset( $data['last_name'] ) ? sanitize_text_field( (string) $data['last_name'] ) : '';
		$email = isset( $data['email'] ) ? sanitize_email( (string) $data['email'] ) : '';
		$user  = isset( $data['username'] ) ? sanitize_user( (string) $data['username'], true ) : '';
		$pass  = isset( $data['password'] ) ? (string) $data['password'] : '';
		$pass2 = isset( $data['confirm_password'] ) ? (string) $data['confirm_password'] : '';
		$phone = isset( $data['phone'] ) ? sanitize_text_field( (string) $data['phone'] ) : '';
		$level = isset( $data['learning_level'] ) ? sanitize_text_field( (string) $data['learning_level'] ) : '';
		$goals = isset( $data['learning_goals'] ) ? sanitize_textarea_field( (string) $data['learning_goals'] ) : '';
		$instr = isset( $data['preferred_instruments'] ) ? sanitize_textarea_field( (string) $data['preferred_instruments'] ) : '';

		// Ignore any client-supplied role.
		unset( $data['role'], $data['user_role'] );

		if ( '' === $first || '' === $last || '' === $email || '' === $user || '' === $pass ) {
			return new WP_Error( 'gmm_required', __( 'Please fill in all required fields.', 'gospel-music-mastery' ) );
		}

		if ( empty( $data['agree_agreement'] ) ) {
			return new WP_Error( 'gmm_agreement', __( 'You must agree to the Student Enrollment Agreement.', 'gospel-music-mastery' ) );
		}

		if ( ! is_email( $email ) ) {
			return new WP_Error( 'gmm_email', __( 'Please enter a valid email address.', 'gospel-music-mastery' ) );
		}

		if ( email_exists( $email ) ) {
			return new WP_Error( 'gmm_email_exists', __( 'An account with this email already exists.', 'gospel-music-mastery' ) );
		}

		if ( username_exists( $user ) ) {
			return new WP_Error( 'gmm_user_exists', __( 'This username is already taken.', 'gospel-music-mastery' ) );
		}

		if ( $pass2 && $pass !== $pass2 ) {
			return new WP_Error( 'gmm_password_mismatch', __( 'Passwords do not match.', 'gospel-music-mastery' ) );
		}

		$strength = class_exists( 'GMM_Auth' )
			? GMM_Auth::validate_password_strength( $pass )
			: self::validate_password_strength( $pass );
		if ( is_wp_error( $strength ) ) {
			return $strength;
		}

		return array(
			'first_name'            => $first,
			'last_name'             => $last,
			'email'                 => $email,
			'username'              => $user,
			'password'              => $pass,
			'phone'                 => $phone,
			'learning_level'        => $level,
			'learning_goals'        => $goals,
			'preferred_instruments' => $instr,
		);
	}

	/**
	 * Fallback password strength.
	 *
	 * @param string $password Password.
	 * @return true|WP_Error
	 */
	private static function validate_password_strength( $password ) {
		$password = (string) $password;
		if ( strlen( $password ) < 8 ) {
			return new WP_Error( 'gmm_weak_password', __( 'Password must be at least 8 characters.', 'gospel-music-mastery' ) );
		}
		if ( ! preg_match( '/[A-Za-z]/', $password ) || ! preg_match( '/[0-9]/', $password ) ) {
			return new WP_Error( 'gmm_weak_password', __( 'Password must include letters and numbers.', 'gospel-music-mastery' ) );
		}
		return true;
	}

	/**
	 * Create WP user with role gmm_student (password hashed by WP).
	 *
	 * @param array<string, string> $clean Sanitized fields.
	 * @return int|WP_Error
	 */
	private static function create_wp_user( $clean ) {
		$role = GMM_Roles::ROLE_STUDENT;

		$user_id = wp_insert_user(
			array(
				'user_login'   => $clean['username'],
				'user_email'   => $clean['email'],
				'user_pass'    => $clean['password'],
				'first_name'   => $clean['first_name'],
				'last_name'    => $clean['last_name'],
				'display_name' => trim( $clean['first_name'] . ' ' . $clean['last_name'] ),
				'role'         => $role,
			)
		);

		if ( is_wp_error( $user_id ) ) {
			$code = $user_id->get_error_code();
			if ( 'existing_user_email' === $code ) {
				return new WP_Error( 'gmm_email_exists', __( 'An account with this email already exists.', 'gospel-music-mastery' ) );
			}
			if ( 'existing_user_login' === $code ) {
				return new WP_Error( 'gmm_user_exists', __( 'This username is already taken.', 'gospel-music-mastery' ) );
			}
			return $user_id;
		}

		$user = new WP_User( (int) $user_id );
		$user->set_role( $role );

		return (int) $user_id;
	}

	/**
	 * Insert gmm_students profile row.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $data    Profile fields.
	 * @return true|WP_Error
	 */
	private static function create_student_profile( $user_id, $data ) {
		global $wpdb;
		$table = GMM_Database::table( 'students' );
		$now   = current_time( 'mysql' );

		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE user_id = %d LIMIT 1", $user_id ) );
		if ( $exists ) {
			return true;
		}

		$ok = $wpdb->insert(
			$table,
			array(
				'user_id'               => absint( $user_id ),
				'first_name'            => sanitize_text_field( isset( $data['first_name'] ) ? $data['first_name'] : '' ),
				'last_name'             => sanitize_text_field( isset( $data['last_name'] ) ? $data['last_name'] : '' ),
				'email'                 => sanitize_email( isset( $data['email'] ) ? $data['email'] : '' ),
				'phone'                 => sanitize_text_field( isset( $data['phone'] ) ? $data['phone'] : '' ),
				'profile_image'         => '',
				'learning_level'        => sanitize_text_field( isset( $data['learning_level'] ) ? $data['learning_level'] : '' ),
				'learning_goals'        => sanitize_textarea_field( isset( $data['learning_goals'] ) ? $data['learning_goals'] : '' ),
				'preferred_instruments' => sanitize_textarea_field( isset( $data['preferred_instruments'] ) ? $data['preferred_instruments'] : '' ),
				'bio'                   => '',
				'status'                => 'active',
				'created_at'            => $now,
				'updated_at'            => $now,
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' )
		);

		return $ok ? true : new WP_Error( 'gmm_profile', __( 'Could not create student profile.', 'gospel-music-mastery' ) );
	}

	/**
	 * Optional profile fields injected with existing form classes (no redesign).
	 *
	 * @return string
	 */
	private static function render_optional_profile_fields() {
		ob_start();
		?>
							<div class="form-group">
								<label for="reg-phone"><?php esc_html_e( 'Phone', 'gospel-music-mastery' ); ?></label>
								<input type="tel" class="form-control" id="reg-phone" name="phone"
									placeholder="<?php esc_attr_e( 'Enter phone number', 'gospel-music-mastery' ); ?>" autocomplete="tel">
								<span class="field-feedback" data-for="reg-phone"></span>
							</div>
							<div class="form-group">
								<label for="reg-learning-level"><?php esc_html_e( 'Learning Level', 'gospel-music-mastery' ); ?></label>
								<select class="form-control form-select" id="reg-learning-level" name="learning_level">
									<option value=""><?php esc_html_e( 'Select level', 'gospel-music-mastery' ); ?></option>
									<option value="Beginner"><?php esc_html_e( 'Beginner', 'gospel-music-mastery' ); ?></option>
									<option value="Intermediate"><?php esc_html_e( 'Intermediate', 'gospel-music-mastery' ); ?></option>
									<option value="Advanced"><?php esc_html_e( 'Advanced', 'gospel-music-mastery' ); ?></option>
								</select>
								<span class="field-feedback" data-for="reg-learning-level"></span>
							</div>
							<div class="form-group">
								<label for="reg-learning-goals"><?php esc_html_e( 'Learning Goals', 'gospel-music-mastery' ); ?></label>
								<textarea class="form-control" id="reg-learning-goals" name="learning_goals" rows="3"
									placeholder="<?php esc_attr_e( 'What do you want to learn?', 'gospel-music-mastery' ); ?>"></textarea>
								<span class="field-feedback" data-for="reg-learning-goals"></span>
							</div>
							<div class="form-group">
								<label for="reg-instruments"><?php esc_html_e( 'Preferred Instruments', 'gospel-music-mastery' ); ?></label>
								<input type="text" class="form-control" id="reg-instruments" name="preferred_instruments"
									placeholder="<?php esc_attr_e( 'e.g. Piano, Voice, Guitar', 'gospel-music-mastery' ); ?>">
								<span class="field-feedback" data-for="reg-instruments"></span>
							</div>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Student dashboard URL.
	 *
	 * @return string
	 */
	private static function dashboard_url() {
		$url = class_exists( 'GMM_Pages' ) ? GMM_Pages::get_page_url( 'student_dashboard' ) : '';
		return $url ? $url : home_url( '/' );
	}

	/**
	 * Current request URL.
	 *
	 * @return string
	 */
	private static function current_url() {
		$scheme = is_ssl() ? 'https' : 'http';
		$host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$uri    = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		return $host ? ( $scheme . '://' . $host . $uri ) : home_url( '/' );
	}

	/**
	 * @param string $url     Redirect.
	 * @param string $type    error|success.
	 * @param string $message Message.
	 * @return void
	 */
	private static function redirect_with_flash( $url, $type, $message ) {
		$key = 'gmm_auth_' . sanitize_key( $type ) . '_' . self::client_key();
		set_transient( $key, sanitize_text_field( $message ), 60 );
		wp_safe_redirect( add_query_arg( 'gmm_auth', sanitize_key( $type ), $url ? $url : home_url( '/' ) ) );
		exit;
	}

	/**
	 * @param string $type error|success.
	 * @return string
	 */
	private static function consume_flash( $type ) {
		if ( empty( $_GET['gmm_auth'] ) || sanitize_key( wp_unslash( $_GET['gmm_auth'] ) ) !== $type ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return '';
		}
		$key = 'gmm_auth_' . sanitize_key( $type ) . '_' . self::client_key();
		$msg = get_transient( $key );
		delete_transient( $key );
		return is_string( $msg ) ? $msg : '';
	}

	/**
	 * @return string
	 */
	private static function client_key() {
		$ip = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		return substr( md5( $ip . wp_salt() ), 0, 12 );
	}
}
