<?php
/**
 * Authentication: registration, login, logout, access helpers.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Auth
 *
 * Core auth for gmm_student / gmm_teacher roles. No social login or 2FA.
 */
class GMM_Auth {

	const NONCE_ACTION = 'gmm_auth_action';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		$loader->add_action( 'admin_post_nopriv_gmm_student_register', $instance, 'handle_student_register' );
		$loader->add_action( 'admin_post_gmm_student_register', $instance, 'handle_student_register' );
		// Teacher registration is owned by GMM_Teacher_Auth.
		$loader->add_action( 'admin_post_nopriv_gmm_student_login', $instance, 'handle_student_login' );
		$loader->add_action( 'admin_post_gmm_student_login', $instance, 'handle_student_login' );
		$loader->add_action( 'admin_post_nopriv_gmm_teacher_login', $instance, 'handle_teacher_login' );
		$loader->add_action( 'admin_post_gmm_teacher_login', $instance, 'handle_teacher_login' );
		$loader->add_action( 'admin_post_gmm_logout', $instance, 'handle_logout' );
		$loader->add_action( 'admin_post_nopriv_gmm_logout', $instance, 'handle_logout' );
		$loader->add_action( 'init', $instance, 'maybe_handle_logout_query', 5 );

		$loader->add_filter( 'gmm_shortcode_html', $instance, 'enhance_auth_shortcode_html', 10, 2 );
	}

	/**
	 * Register a student user + gmm_students row.
	 *
	 * @param array<string, mixed> $data  Registration fields.
	 * @param string               $nonce Optional nonce.
	 * @return int|WP_Error User ID.
	 */
	public static function student_register( $data, $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		$data = is_array( $data ) ? $data : array();
		$clean = self::sanitize_registration_common( $data );
		if ( is_wp_error( $clean ) ) {
			return $clean;
		}

		$phone       = isset( $data['phone'] ) ? sanitize_text_field( (string) $data['phone'] ) : '';
		$level       = isset( $data['learning_level'] ) ? sanitize_text_field( (string) $data['learning_level'] ) : '';
		$instruments = isset( $data['preferred_instruments'] ) ? sanitize_textarea_field( (string) $data['preferred_instruments'] ) : '';

		$user_id = self::create_wp_user(
			$clean['username'],
			$clean['email'],
			$clean['password'],
			$clean['first_name'],
			$clean['last_name'],
			GMM_Roles::ROLE_STUDENT
		);

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		$profile = self::create_student_profile(
			$user_id,
			array(
				'first_name'            => $clean['first_name'],
				'last_name'             => $clean['last_name'],
				'email'                 => $clean['email'],
				'phone'                 => $phone,
				'learning_level'        => $level,
				'preferred_instruments' => $instruments,
			)
		);

		if ( is_wp_error( $profile ) ) {
			// User exists; profile failed — keep user, return error for retry of profile.
			return $profile;
		}

		/**
		 * Fires after student registration.
		 *
		 * @since 1.0.0
		 * @param int                  $user_id User ID.
		 * @param array<string, mixed> $clean   Sanitized core fields.
		 */
		do_action( 'gmm_student_registered', $user_id, $clean );

		/**
		 * Fires after any GMM user registration.
		 *
		 * @since 1.0.0
		 * @param int                  $user_id User ID.
		 * @param array<string, mixed> $clean   Sanitized core fields.
		 * @param string               $role    Role key (gmm_student|gmm_teacher).
		 */
		do_action( 'gmm_user_registered', $user_id, $clean, GMM_Roles::ROLE_STUDENT );

		return $user_id;
	}

	/**
	 * Register a teacher user + gmm_teachers row.
	 *
	 * Delegates to GMM_Teacher_Auth (pending approval flow).
	 *
	 * @param array<string, mixed> $data  Registration fields.
	 * @param string               $nonce Optional nonce.
	 * @return int|WP_Error User ID.
	 */
	public static function teacher_register( $data, $nonce = '' ) {
		if ( class_exists( 'GMM_Teacher_Auth' ) ) {
			return GMM_Teacher_Auth::register( $data, $nonce );
		}
		return new WP_Error( 'gmm_missing', __( 'Teacher registration system unavailable.', 'gospel-music-mastery' ) );
	}

	/**
	 * Log in a user via wp_signon.
	 *
	 * @param string $login       Username or email.
	 * @param string $password    Password.
	 * @param bool   $remember    Remember me.
	 * @param string $expect_role Optional gmm_student|gmm_teacher|admin.
	 * @param string $nonce       Optional nonce.
	 * @return WP_User|WP_Error
	 */
	public static function login_user( $login, $password, $remember = false, $expect_role = '', $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		$login    = sanitize_text_field( (string) $login );
		$password = (string) $password;

		if ( '' === $login || '' === $password ) {
			return new WP_Error( 'gmm_empty', __( 'Username and password are required.', 'gospel-music-mastery' ) );
		}

		// Allow email login.
		if ( is_email( $login ) ) {
			$user_by_email = get_user_by( 'email', $login );
			if ( $user_by_email ) {
				$login = $user_by_email->user_login;
			}
		}

		$creds = array(
			'user_login'    => $login,
			'user_password' => $password,
			'remember'      => (bool) $remember,
		);

		$user = wp_signon( $creds, is_ssl() );

		if ( is_wp_error( $user ) ) {
			return new WP_Error( 'gmm_login_failed', __( 'Invalid login credentials.', 'gospel-music-mastery' ) );
		}

		if ( $expect_role && ! self::user_matches_expected_role( $user, $expect_role ) ) {
			wp_logout();
			return new WP_Error( 'gmm_wrong_portal', __( 'This account cannot sign in through this portal.', 'gospel-music-mastery' ) );
		}

		wp_set_current_user( $user->ID );

		/**
		 * Fires after successful GMM login.
		 *
		 * @since 1.0.0
		 * @param WP_User $user User object.
		 */
		do_action( 'gmm_user_logged_in', $user );

		return $user;
	}

	/**
	 * Log out current user.
	 *
	 * @param string $nonce Optional nonce.
	 * @return void
	 */
	public static function logout_user( $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return;
		}

		if ( is_user_logged_in() ) {
			wp_logout();
		}

		/**
		 * Fires after GMM logout.
		 *
		 * @since 1.0.0
		 */
		do_action( 'gmm_user_logged_out' );
	}

	/**
	 * Redirect URL after login by role.
	 *
	 * @param WP_User|int $user User.
	 * @return string
	 */
	public static function get_login_redirect_url( $user ) {
		if ( is_numeric( $user ) ) {
			$user = get_userdata( (int) $user );
		}

		if ( ! ( $user instanceof WP_User ) ) {
			return home_url( '/' );
		}

		if ( user_can( $user, 'manage_options' ) ) {
			return admin_url();
		}

		if ( in_array( GMM_Roles::ROLE_TEACHER, (array) $user->roles, true ) ) {
			$url = class_exists( 'GMM_Pages' ) ? GMM_Pages::get_page_url( 'teacher_dashboard' ) : '';
			return $url ? $url : home_url( '/' );
		}

		if ( in_array( GMM_Roles::ROLE_STUDENT, (array) $user->roles, true ) ) {
			$url = class_exists( 'GMM_Pages' ) ? GMM_Pages::get_page_url( 'student_dashboard' ) : '';
			return $url ? $url : home_url( '/' );
		}

		return home_url( '/' );
	}

	/**
	 * Require any logged-in user.
	 *
	 * @param string $redirect_to Login URL if guest.
	 * @return void
	 */
	public static function require_login( $redirect_to = '' ) {
		if ( is_user_logged_in() ) {
			return;
		}

		$login = $redirect_to ? $redirect_to : self::get_default_login_url();
		wp_safe_redirect( $login );
		exit;
	}

	/**
	 * Require student role (admins allowed).
	 *
	 * @return void
	 */
	public static function require_student() {
		self::require_login( class_exists( 'GMM_Pages' ) ? GMM_Pages::get_page_url( 'student_login' ) : wp_login_url() );

		if ( current_user_can( 'manage_options' ) || gmm_is_student() ) {
			return;
		}

		wp_safe_redirect( class_exists( 'GMM_Pages' ) ? GMM_Pages::get_page_url( 'student_login' ) : home_url( '/' ) );
		exit;
	}

	/**
	 * Require teacher role (admins allowed).
	 *
	 * @return void
	 */
	public static function require_teacher() {
		self::require_login( class_exists( 'GMM_Pages' ) ? GMM_Pages::get_page_url( 'teacher_login' ) : wp_login_url() );

		if ( current_user_can( 'manage_options' ) ) {
			return;
		}

		if ( ! gmm_is_teacher() ) {
			wp_safe_redirect( class_exists( 'GMM_Pages' ) ? GMM_Pages::get_page_url( 'teacher_login' ) : home_url( '/' ) );
			exit;
		}

		if ( class_exists( 'GMM_Teacher_Auth' ) && ! GMM_Teacher_Auth::is_approved( get_current_user_id() ) ) {
			$back = class_exists( 'GMM_Pages' ) ? GMM_Pages::get_page_url( 'teacher_login' ) : home_url( '/' );
			self::redirect_with_flash( $back, 'error', __( 'Your account is waiting for approval.', 'gospel-music-mastery' ) );
		}
	}

	/**
	 * Password strength check (preparation for forms).
	 *
	 * @param string $password Password.
	 * @return true|WP_Error
	 */
	public static function validate_password_strength( $password ) {
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
	 * Password reset URL preparation (uses WP core lostpassword).
	 *
	 * @param string $redirect_after Optional redirect after reset flow.
	 * @return string
	 */
	public static function get_password_reset_url( $redirect_after = '' ) {
		$redirect = $redirect_after ? esc_url_raw( $redirect_after ) : home_url( '/' );
		return wp_lostpassword_url( $redirect );
	}

	/**
	 * Trigger WP password reset email (preparation helper).
	 *
	 * @param string $login Username or email.
	 * @return true|WP_Error
	 */
	public static function request_password_reset( $login ) {
		$login = sanitize_text_field( (string) $login );
		if ( '' === $login ) {
			return new WP_Error( 'gmm_empty', __( 'Username or email is required.', 'gospel-music-mastery' ) );
		}

		$result = retrieve_password( $login );
		return is_wp_error( $result ) ? $result : true;
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
	 * Nonce field HTML.
	 *
	 * @return string
	 */
	public static function nonce_field() {
		return wp_nonce_field( self::NONCE_ACTION, 'gmm_auth_nonce', true, false );
	}

	/**
	 * Enhance auth shortcode HTML: form action + hidden fields (templates unchanged on disk).
	 *
	 * @param string $html Shortcode HTML.
	 * @param string $tag  Shortcode tag.
	 * @return string
	 */
	public function enhance_auth_shortcode_html( $html, $tag ) {
		$map = array(
			'gmm_student_register' => array(
				'form_id' => 'student-register-form',
				'action'  => 'gmm_student_register',
				'error_id'=> 'register-error',
				'error_text_id' => 'register-error-text',
			),
			'gmm_teacher_register' => array(
				'form_id' => 'teacher-register-form',
				'action'  => 'gmm_teacher_register',
				'error_id'=> 'register-error',
				'error_text_id' => 'register-error-text',
			),
			'gmm_student_login'    => array(
				'form_id' => 'student-login-form',
				'action'  => 'gmm_student_login',
				'error_id'=> 'student-login-error',
				'error_text_id' => 'student-login-error-text',
			),
			'gmm_teacher_login'    => array(
				'form_id' => 'teacher-login-form',
				'action'  => 'gmm_teacher_login',
				'error_id'=> 'teacher-login-error',
				'error_text_id' => 'teacher-login-error-text',
			),
		);

		if ( ! isset( $map[ $tag ] ) ) {
			return $html;
		}

		$cfg    = $map[ $tag ];
		$action = esc_url( admin_url( 'admin-post.php' ) );
		$nonce  = wp_create_nonce( self::NONCE_ACTION );
		$hidden = '<input type="hidden" name="action" value="' . esc_attr( $cfg['action'] ) . '" />'
			. '<input type="hidden" name="gmm_auth_nonce" value="' . esc_attr( $nonce ) . '" />'
			. '<input type="hidden" name="gmm_redirect" value="' . esc_attr( self::current_url() ) . '" />';

		// Point form to admin-post.php.
		$html = preg_replace(
			'/(<form\b[^>]*\bid=["\']' . preg_quote( $cfg['form_id'], '/' ) . '["\'][^>]*\baction=["\'])([^"\']*)(["\'])/i',
			'$1' . $action . '$3',
			$html,
			1
		);

		// If action attribute missing pattern failed, try replace action="#" generically inside that form — fallback append hidden after opening form tag.
		$html = preg_replace(
			'/(<form\b[^>]*\bid=["\']' . preg_quote( $cfg['form_id'], '/' ) . '["\'][^>]*>)/i',
			'$1' . $hidden,
			$html,
			1
		);

		// Remember-me name for login forms.
		if ( false !== strpos( $tag, 'login' ) ) {
			$html = preg_replace(
				'/(id=["\']remember-me["\'][^>]*)(>)/i',
				'$1 name="rememberme" value="1"$2',
				$html,
				1
			);
			$html = preg_replace(
				'/(id=["\']keep-signed-in["\'][^>]*)(>)/i',
				'$1 name="rememberme" value="1"$2',
				$html,
				1
			);
		}

		$error = self::consume_auth_flash( 'error' );
		if ( $error ) {
			if ( preg_match( '/id=["\']' . preg_quote( $cfg['error_id'], '/' ) . '["\']/', $html ) ) {
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
			} else {
				$notice = '<div class="gospel-alert gospel-alert-error" role="alert"><i class="far fa-circle-exclamation"></i> <span>' . esc_html( $error ) . '</span></div>';
				$html   = preg_replace(
					'/(<form\b[^>]*\bid=["\']' . preg_quote( $cfg['form_id'], '/' ) . '["\'])/i',
					$notice . '$1',
					$html,
					1
				);
			}
		}

		$success = self::consume_auth_flash( 'success' );
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

	/** @return void */
	public function handle_student_register() {
		$this->process_register( 'student' );
	}

	/** @return void */
	public function handle_student_login() {
		$this->process_login( 'student' );
	}

	/** @return void */
	public function handle_teacher_login() {
		$this->process_login( 'teacher' );
	}

	/** @return void */
	public function handle_logout() {
		$nonce = isset( $_REQUEST['gmm_auth_nonce'] ) ? sanitize_text_field( wp_unslash( $_REQUEST['gmm_auth_nonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		self::logout_user( $nonce );
		$redirect = isset( $_REQUEST['gmm_redirect'] ) ? esc_url_raw( wp_unslash( $_REQUEST['gmm_redirect'] ) ) : home_url( '/' ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		wp_safe_redirect( $redirect ? $redirect : home_url( '/' ) );
		exit;
	}

	/**
	 * Support ?gmm_logout=1&_wpnonce=
	 *
	 * @return void
	 */
	public function maybe_handle_logout_query() {
		if ( empty( $_GET['gmm_logout'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}

		$nonce = isset( $_GET['_wpnonce'] ) ? sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! wp_verify_nonce( $nonce, self::NONCE_ACTION ) ) {
			return;
		}

		self::logout_user();
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}

	/**
	 * @param string $type student|teacher.
	 * @return void
	 */
	private function process_register( $type ) {
		$nonce = isset( $_POST['gmm_auth_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['gmm_auth_nonce'] ) ) : '';
		$back  = isset( $_POST['gmm_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['gmm_redirect'] ) ) : home_url( '/' );

		if ( ! self::verify_nonce( $nonce ) ) {
			self::redirect_with_flash( $back, 'error', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		$data = wp_unslash( $_POST );

		$result = ( 'teacher' === $type )
			? self::teacher_register( $data, $nonce )
			: self::student_register( $data, $nonce );

		if ( is_wp_error( $result ) ) {
			self::redirect_with_flash( $back, 'error', $result->get_error_message() );
		}

		// Auto-login after register.
		$user = get_userdata( (int) $result );
		if ( $user ) {
			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID, true, is_ssl() );

			if ( 'teacher' === $type && class_exists( 'GMM_Teacher_Auth' ) && ! GMM_Teacher_Auth::is_approved( (int) $result ) ) {
				self::redirect_with_flash(
					$back,
					'success',
					__( 'Registration successful. Your account is waiting for approval.', 'gospel-music-mastery' )
				);
			}

			$dest = self::get_login_redirect_url( $user );
			wp_safe_redirect( $dest );
			exit;
		}

		self::redirect_with_flash( $back, 'success', __( 'Registration successful.', 'gospel-music-mastery' ) );
	}

	/**
	 * @param string $type student|teacher.
	 * @return void
	 */
	private function process_login( $type ) {
		$nonce = isset( $_POST['gmm_auth_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['gmm_auth_nonce'] ) ) : '';
		$back  = isset( $_POST['gmm_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['gmm_redirect'] ) ) : home_url( '/' );

		$login    = isset( $_POST['username'] ) ? sanitize_text_field( wp_unslash( $_POST['username'] ) ) : '';
		$password = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';
		$remember = ! empty( $_POST['rememberme'] );

		$expect = ( 'teacher' === $type ) ? GMM_Roles::ROLE_TEACHER : GMM_Roles::ROLE_STUDENT;
		$user   = self::login_user( $login, $password, $remember, $expect, $nonce );

		if ( is_wp_error( $user ) ) {
			self::redirect_with_flash( $back, 'error', $user->get_error_message() );
		}

		wp_safe_redirect( self::get_login_redirect_url( $user ) );
		exit;
	}

	/**
	 * @param array<string, mixed> $data Raw.
	 * @return array<string, string>|WP_Error
	 */
	private static function sanitize_registration_common( $data ) {
		$first = isset( $data['first_name'] ) ? sanitize_text_field( (string) $data['first_name'] ) : '';
		$last  = isset( $data['last_name'] ) ? sanitize_text_field( (string) $data['last_name'] ) : '';
		$email = isset( $data['email'] ) ? sanitize_email( (string) $data['email'] ) : '';
		$user  = isset( $data['username'] ) ? sanitize_user( (string) $data['username'], true ) : '';
		$pass  = isset( $data['password'] ) ? (string) $data['password'] : '';
		$pass2 = isset( $data['confirm_password'] ) ? (string) $data['confirm_password'] : '';

		if ( '' === $first || '' === $last || '' === $email || '' === $user || '' === $pass ) {
			return new WP_Error( 'gmm_required', __( 'Please fill in all required fields.', 'gospel-music-mastery' ) );
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

		$strength = self::validate_password_strength( $pass );
		if ( is_wp_error( $strength ) ) {
			return $strength;
		}

		// Never allow clients to choose role.
		return array(
			'first_name' => $first,
			'last_name'  => $last,
			'email'      => $email,
			'username'   => $user,
			'password'   => $pass,
		);
	}

	/**
	 * Create WP user with hashed password via wp_insert_user.
	 *
	 * @param string $username Username.
	 * @param string $email    Email.
	 * @param string $password Plain password (hashed by WP).
	 * @param string $first    First name.
	 * @param string $last     Last name.
	 * @param string $role     Role key.
	 * @return int|WP_Error
	 */
	private static function create_wp_user( $username, $email, $password, $first, $last, $role ) {
		$allowed_roles = array( GMM_Roles::ROLE_STUDENT, GMM_Roles::ROLE_TEACHER );
		if ( ! in_array( $role, $allowed_roles, true ) ) {
			return new WP_Error( 'gmm_role', __( 'Invalid role.', 'gospel-music-mastery' ) );
		}

		$user_id = wp_insert_user(
			array(
				'user_login'   => $username,
				'user_email'   => $email,
				'user_pass'    => $password,
				'first_name'   => $first,
				'last_name'    => $last,
				'display_name' => trim( $first . ' ' . $last ),
				'role'         => $role,
			)
		);

		if ( is_wp_error( $user_id ) ) {
			return $user_id;
		}

		// Ensure role (prevent default subscriber leftover).
		$user = new WP_User( $user_id );
		$user->set_role( $role );

		return (int) $user_id;
	}

	/**
	 * @param int                  $user_id User ID.
	 * @param array<string, mixed> $data    Profile.
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
				'first_name'            => sanitize_text_field( $data['first_name'] ),
				'last_name'             => sanitize_text_field( $data['last_name'] ),
				'email'                 => sanitize_email( $data['email'] ),
				'phone'                 => sanitize_text_field( isset( $data['phone'] ) ? $data['phone'] : '' ),
				'profile_image'         => '',
				'learning_level'        => sanitize_text_field( isset( $data['learning_level'] ) ? $data['learning_level'] : '' ),
				'learning_goals'        => '',
				'preferred_instruments' => sanitize_textarea_field( isset( $data['preferred_instruments'] ) ? $data['preferred_instruments'] : '' ),
				'bio'                   => '',
				'status'                => 'active',
				'created_at'            => $now,
				'updated_at'            => $now,
			)
		);

		return $ok ? true : new WP_Error( 'gmm_profile', __( 'Could not create student profile.', 'gospel-music-mastery' ) );
	}

	/**
	 * @param int                  $user_id User ID.
	 * @param array<string, mixed> $data    Profile.
	 * @return true|WP_Error
	 */
	private static function create_teacher_profile( $user_id, $data ) {
		global $wpdb;
		$table = GMM_Database::table( 'teachers' );
		$now   = current_time( 'mysql' );

		$exists = $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE user_id = %d LIMIT 1", $user_id ) );
		if ( $exists ) {
			return true;
		}

		$ok = $wpdb->insert(
			$table,
			array(
				'user_id'        => absint( $user_id ),
				'first_name'     => sanitize_text_field( $data['first_name'] ),
				'last_name'      => sanitize_text_field( $data['last_name'] ),
				'email'          => sanitize_email( $data['email'] ),
				'phone'          => sanitize_text_field( isset( $data['phone'] ) ? $data['phone'] : '' ),
				'profile_image'  => '',
				'bio'            => isset( $data['bio'] ) ? wp_kses_post( $data['bio'] ) : '',
				'specialization' => sanitize_text_field( isset( $data['specialization'] ) ? $data['specialization'] : '' ),
				'experience'     => sanitize_text_field( isset( $data['experience'] ) ? $data['experience'] : '' ),
				'rating'         => 0,
				'status'         => 'pending',
				'created_at'     => $now,
				'updated_at'     => $now,
			)
		);

		return $ok ? true : new WP_Error( 'gmm_profile', __( 'Could not create teacher profile.', 'gospel-music-mastery' ) );
	}

	/**
	 * @param WP_User $user        User.
	 * @param string  $expect_role Role key or 'admin'.
	 * @return bool
	 */
	private static function user_matches_expected_role( $user, $expect_role ) {
		// Admins may use any portal for support.
		if ( user_can( $user, 'manage_options' ) ) {
			return true;
		}

		if ( 'admin' === $expect_role ) {
			return false;
		}

		return in_array( $expect_role, (array) $user->roles, true );
	}

	/**
	 * @return string
	 */
	private static function get_default_login_url() {
		$url = class_exists( 'GMM_Pages' ) ? GMM_Pages::get_page_url( 'student_login' ) : '';
		return $url ? $url : wp_login_url();
	}

	/**
	 * @return string
	 */
	private static function current_url() {
		$scheme = is_ssl() ? 'https' : 'http';
		$host   = isset( $_SERVER['HTTP_HOST'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
		$uri    = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '/';
		return $host ? ( $scheme . '://' . $host . $uri ) : home_url( '/' );
	}

	/**
	 * @param string $url     Redirect URL.
	 * @param string $type    error|success.
	 * @param string $message Message.
	 * @return void
	 */
	private static function redirect_with_flash( $url, $type, $message ) {
		$key = 'gmm_auth_' . sanitize_key( $type );
		set_transient( $key . '_' . self::client_key(), sanitize_text_field( $message ), 60 );
		wp_safe_redirect( add_query_arg( 'gmm_auth', sanitize_key( $type ), $url ? $url : home_url( '/' ) ) );
		exit;
	}

	/**
	 * @param string $type error|success.
	 * @return string
	 */
	private static function consume_auth_flash( $type ) {
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
