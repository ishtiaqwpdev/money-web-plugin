<?php
/**
 * Teacher registration and approval gating.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Teacher_Auth
 *
 * Handles teacher registration into WordPress + gmm_teachers (status pending),
 * and blocks dashboard / class creation until admin approval.
 */
class GMM_Teacher_Auth {

	const NONCE_ACTION = 'gmm_auth_action';
	const PENDING_MESSAGE = 'Your account is waiting for approval.';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		$loader->add_action( 'admin_post_nopriv_gmm_teacher_register', $instance, 'handle_register', 5 );
		$loader->add_action( 'admin_post_gmm_teacher_register', $instance, 'handle_register', 5 );
		$loader->add_action( 'wp_ajax_nopriv_gmm_teacher_register', $instance, 'ajax_register' );
		$loader->add_action( 'wp_ajax_gmm_teacher_register', $instance, 'ajax_register' );

		$loader->add_filter( 'gmm_shortcode_user_can_access', $instance, 'gate_teacher_access', 25, 2 );
		$loader->add_filter( 'gmm_shortcode_access_denied_message', $instance, 'pending_access_message', 25, 2 );
		$loader->add_filter( 'gmm_shortcode_html', $instance, 'enhance_register_html', 15, 2 );
	}

	/**
	 * Register a teacher: WP user (gmm_teacher) + gmm_teachers row (pending).
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

		$profile_image = self::resolve_profile_image( $data, $user_id );

		$profile = self::create_teacher_profile(
			$user_id,
			array(
				'first_name'     => $clean['first_name'],
				'last_name'      => $clean['last_name'],
				'email'          => $clean['email'],
				'phone'          => $clean['phone'],
				'specialization' => $clean['specialization'],
				'experience'     => $clean['experience'],
				'bio'            => $clean['bio'],
				'profile_image'  => $profile_image,
			)
		);

		if ( is_wp_error( $profile ) ) {
			return $profile;
		}

		update_user_meta( $user_id, 'gmm_teacher_status', 'pending' );

		/**
		 * Fires after teacher registration (email system may listen later).
		 *
		 * @since 1.0.0
		 * @param int                  $user_id User ID.
		 * @param array<string, mixed> $clean   Sanitized registration data.
		 */
		do_action( 'gmm_teacher_registered', $user_id, $clean );

		/**
		 * Fires after any GMM user registration.
		 *
		 * @since 1.0.0
		 * @param int                  $user_id User ID.
		 * @param array<string, mixed> $clean   Sanitized fields.
		 * @param string               $role    Role key.
		 */
		do_action( 'gmm_user_registered', $user_id, $clean, GMM_Roles::ROLE_TEACHER );

		return $user_id;
	}

	/**
	 * Whether the teacher account is approved for portal access.
	 *
	 * @param int $user_id Optional WP user ID.
	 * @return bool
	 */
	public static function is_approved( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}

		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}

		$user = get_userdata( $user_id );
		if ( ! ( $user instanceof WP_User ) || ! in_array( GMM_Roles::ROLE_TEACHER, (array) $user->roles, true ) ) {
			return false;
		}

		$meta = sanitize_key( (string) get_user_meta( $user_id, 'gmm_teacher_status', true ) );
		if ( in_array( $meta, array( 'active', 'approved' ), true ) ) {
			return true;
		}
		if ( in_array( $meta, array( 'pending', 'rejected', 'inactive', 'suspended', 'trash' ), true ) ) {
			return false;
		}

		global $wpdb;
		$table  = GMM_Database::table( 'teachers' );
		$status = $wpdb->get_var(
			$wpdb->prepare( "SELECT status FROM {$table} WHERE user_id = %d LIMIT 1", $user_id )
		);
		$status = sanitize_key( (string) $status );

		return in_array( $status, array( 'active', 'approved' ), true );
	}

	/**
	 * Whether teacher may create classes / use dashboard features.
	 *
	 * @param int $user_id Optional WP user ID.
	 * @return bool
	 */
	public static function can_use_dashboard( $user_id = 0 ) {
		return self::is_approved( $user_id );
	}

	/**
	 * admin-post registration handler (runs before legacy Auth handler).
	 *
	 * @return void
	 */
	public function handle_register() {
		$nonce = isset( $_POST['gmm_auth_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['gmm_auth_nonce'] ) ) : '';
		$back  = isset( $_POST['gmm_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['gmm_redirect'] ) ) : home_url( '/' );

		if ( ! self::verify_nonce( $nonce ) ) {
			self::redirect_with_flash( $back, 'error', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		$result = self::register( wp_unslash( $_POST ), $nonce );

		if ( is_wp_error( $result ) ) {
			self::redirect_with_flash( $back, 'error', $result->get_error_message() );
		}

		$user = get_userdata( (int) $result );
		if ( $user ) {
			wp_set_current_user( $user->ID );
			wp_set_auth_cookie( $user->ID, true, is_ssl() );
		}

		if ( ! self::is_approved( (int) $result ) ) {
			self::redirect_with_flash(
				$back,
				'success',
				__( 'Registration successful. Your account is waiting for approval.', 'gospel-music-mastery' )
			);
		}

		$dest = class_exists( 'GMM_Auth' ) ? GMM_Auth::get_login_redirect_url( $user ) : home_url( '/' );
		wp_safe_redirect( $dest );
		exit;
	}

	/**
	 * AJAX teacher registration.
	 *
	 * @return void
	 */
	public function ajax_register() {
		check_ajax_referer( self::NONCE_ACTION, 'gmm_auth_nonce' );

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

		$pending = ! self::is_approved( (int) $result );

		wp_send_json_success(
			array(
				'user_id'  => (int) $result,
				'status'   => $pending ? 'pending' : 'approved',
				'message'  => $pending
					? __( 'Registration successful. Your account is waiting for approval.', 'gospel-music-mastery' )
					: __( 'Registration successful.', 'gospel-music-mastery' ),
				'redirect' => $pending
					? ''
					: ( class_exists( 'GMM_Auth' ) ? GMM_Auth::get_login_redirect_url( $user ) : home_url( '/' ) ),
			)
		);
	}

	/**
	 * Block unapproved teachers from teacher shortcodes.
	 *
	 * @param bool   $allowed Allowed.
	 * @param string $access  Access key.
	 * @return bool
	 */
	public function gate_teacher_access( $allowed, $access ) {
		if ( ! $allowed || 'teacher' !== $access ) {
			return $allowed;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return $allowed;
		}
		if ( ! function_exists( 'gmm_is_teacher' ) || ! gmm_is_teacher() ) {
			return $allowed;
		}
		return self::is_approved( get_current_user_id() );
	}

	/**
	 * Pending-approval notice for teacher shortcodes.
	 *
	 * @param string $message Default message.
	 * @param string $access  Access key.
	 * @return string
	 */
	public function pending_access_message( $message, $access ) {
		if ( 'teacher' !== $access ) {
			return $message;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return $message;
		}
		if ( ! function_exists( 'gmm_is_teacher' ) || ! gmm_is_teacher() ) {
			return $message;
		}
		if ( self::is_approved( get_current_user_id() ) ) {
			return $message;
		}
		return __( 'Your account is waiting for approval.', 'gospel-music-mastery' );
	}

	/**
	 * Wire register form + real success copy without changing template markup on disk.
	 *
	 * @param string $html Shortcode HTML.
	 * @param string $tag  Shortcode tag.
	 * @return string
	 */
	public function enhance_register_html( $html, $tag ) {
		if ( 'gmm_teacher_register' !== $tag || ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		$success_text = esc_html__( 'Registration successful. Your account is waiting for approval.', 'gospel-music-mastery' );
		$html         = preg_replace(
			'/(id=["\']register-success["\'][^>]*>\s*(?:<i[^>]*>.*?<\/i>\s*)?<span>)(.*?)(<\/span>)/is',
			'$1' . $success_text . '$3',
			$html,
			1
		);

		$extra = '<input type="hidden" name="gmm_web_register" value="1" />';
		$html  = preg_replace(
			'/(<form\b[^>]*\bid=["\']teacher-register-form["\'][^>]*>)/i',
			'$1' . $extra,
			is_string( $html ) ? $html : '',
			1
		);

		return is_string( $html ) ? $html : '';
	}

	/**
	 * Sanitize + validate registration payload.
	 *
	 * @param array<string, mixed> $data Raw data.
	 * @return array<string, string>|WP_Error
	 */
	private static function sanitize_registration( $data ) {
		$first = isset( $data['first_name'] ) ? sanitize_text_field( (string) $data['first_name'] ) : '';
		$last  = isset( $data['last_name'] ) ? sanitize_text_field( (string) $data['last_name'] ) : '';
		$email = isset( $data['email'] ) ? sanitize_email( (string) $data['email'] ) : '';
		$user  = isset( $data['username'] ) ? sanitize_user( (string) $data['username'], true ) : '';
		$pass  = isset( $data['password'] ) ? (string) $data['password'] : '';
		$pass2 = isset( $data['confirm_password'] ) ? (string) $data['confirm_password'] : '';

		$phone           = isset( $data['phone'] ) ? sanitize_text_field( (string) $data['phone'] ) : '';
		$specialization  = isset( $data['specialization'] ) ? sanitize_text_field( (string) $data['specialization'] ) : '';
		$experience      = isset( $data['experience'] ) ? sanitize_text_field( (string) $data['experience'] ) : '';
		$bio             = isset( $data['bio'] ) ? wp_kses_post( (string) $data['bio'] ) : '';

		if ( '' === $first || '' === $last || '' === $email || '' === $pass ) {
			return new WP_Error( 'gmm_required', __( 'Please fill in all required fields.', 'gospel-music-mastery' ) );
		}

		if ( '' === $user ) {
			$user = self::username_from_email( $email );
		}

		if ( ! $user ) {
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

		if ( class_exists( 'GMM_Auth' ) ) {
			$strength = GMM_Auth::validate_password_strength( $pass );
			if ( is_wp_error( $strength ) ) {
				return $strength;
			}
		} elseif ( strlen( $pass ) < 8 || ! preg_match( '/[A-Za-z]/', $pass ) || ! preg_match( '/[0-9]/', $pass ) ) {
			return new WP_Error( 'gmm_weak_password', __( 'Invalid password. Use at least 8 characters with letters and numbers.', 'gospel-music-mastery' ) );
		}

		if ( ! empty( $data['gmm_web_register'] ) && empty( $data['agree_agreement'] ) ) {
			return new WP_Error( 'gmm_agreement', __( 'You must agree to the Teacher Independent Contractor Agreement.', 'gospel-music-mastery' ) );
		}

		// Never allow client-chosen roles.
		return array(
			'first_name'     => $first,
			'last_name'      => $last,
			'email'          => $email,
			'username'       => $user,
			'password'       => $pass,
			'phone'          => $phone,
			'specialization' => $specialization,
			'experience'     => $experience,
			'bio'            => $bio,
		);
	}

	/**
	 * Create WP user with gmm_teacher role only.
	 *
	 * @param array<string, string> $clean Sanitized fields.
	 * @return int|WP_Error
	 */
	private static function create_wp_user( $clean ) {
		$user_id = wp_insert_user(
			array(
				'user_login'   => $clean['username'],
				'user_email'   => $clean['email'],
				'user_pass'    => $clean['password'],
				'first_name'   => $clean['first_name'],
				'last_name'    => $clean['last_name'],
				'display_name' => trim( $clean['first_name'] . ' ' . $clean['last_name'] ),
				'role'         => GMM_Roles::ROLE_TEACHER,
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
		$user->set_role( GMM_Roles::ROLE_TEACHER );

		return (int) $user_id;
	}

	/**
	 * Insert gmm_teachers row with pending status.
	 *
	 * @param int                  $user_id User ID.
	 * @param array<string, mixed> $data    Profile fields.
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

		$image = '';
		if ( ! empty( $data['profile_image'] ) ) {
			$image = (string) absint( $data['profile_image'] );
			if ( '0' === $image ) {
				$image = '';
			}
		}

		$ok = $wpdb->insert(
			$table,
			array(
				'user_id'        => absint( $user_id ),
				'first_name'     => sanitize_text_field( $data['first_name'] ),
				'last_name'      => sanitize_text_field( $data['last_name'] ),
				'email'          => sanitize_email( $data['email'] ),
				'phone'          => sanitize_text_field( isset( $data['phone'] ) ? $data['phone'] : '' ),
				'profile_image'  => $image,
				'bio'            => isset( $data['bio'] ) ? wp_kses_post( $data['bio'] ) : '',
				'specialization' => sanitize_text_field( isset( $data['specialization'] ) ? $data['specialization'] : '' ),
				'experience'     => sanitize_text_field( isset( $data['experience'] ) ? $data['experience'] : '' ),
				'rating'         => 0,
				'status'         => 'pending',
				'created_at'     => $now,
				'updated_at'     => $now,
			)
		);

		if ( ! $ok ) {
			return new WP_Error( 'gmm_profile', __( 'Could not create teacher profile.', 'gospel-music-mastery' ) );
		}

		if ( class_exists( 'GMM_Admin_Teachers' ) ) {
			GMM_Admin_Teachers::flush_cache();
		}

		return true;
	}

	/**
	 * Resolve profile image as attachment ID only (upload or posted ID).
	 *
	 * @param array<string, mixed> $data    Raw request.
	 * @param int                  $user_id New user ID.
	 * @return int Attachment ID or 0.
	 */
	private static function resolve_profile_image( $data, $user_id ) {
		$posted = 0;
		if ( isset( $data['profile_image'] ) ) {
			$posted = absint( $data['profile_image'] );
		} elseif ( isset( $data['profile_image_id'] ) ) {
			$posted = absint( $data['profile_image_id'] );
		}

		if ( $posted && 'attachment' === get_post_type( $posted ) ) {
			wp_update_post(
				array(
					'ID'          => $posted,
					'post_author' => absint( $user_id ),
				)
			);
			return $posted;
		}

		$file_key = '';
		foreach ( array( 'profile_image', 'profile_photo', 'teacher_profile_image' ) as $key ) {
			if ( ! empty( $_FILES[ $key ]['tmp_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$file_key = $key;
				break;
			}
		}

		if ( '' === $file_key ) {
			return 0;
		}

		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$prev_user = get_current_user_id();
		wp_set_current_user( $user_id );

		$attachment_id = media_handle_upload(
			$file_key,
			0,
			array(),
			array(
				'test_form' => false,
				'mimes'     => array(
					'jpg|jpeg|jpe' => 'image/jpeg',
					'png'          => 'image/png',
					'webp'         => 'image/webp',
					'gif'          => 'image/gif',
				),
			)
		);

		wp_set_current_user( $prev_user );

		if ( is_wp_error( $attachment_id ) ) {
			return 0;
		}

		$attachment_id = absint( $attachment_id );
		if ( $attachment_id ) {
			update_post_meta( $attachment_id, '_gmm_media', 1 );
		}

		return $attachment_id;
	}

	/**
	 * @param string $email Email.
	 * @return string
	 */
	private static function username_from_email( $email ) {
		$base = sanitize_user( strstr( $email, '@', true ), true );
		if ( '' === $base ) {
			$base = 'teacher';
		}
		return self::unique_username( $base );
	}

	/**
	 * @param string $base Base username.
	 * @return string
	 */
	private static function unique_username( $base ) {
		$base = sanitize_user( $base, true );
		if ( '' === $base ) {
			$base = 'teacher';
		}
		if ( ! username_exists( $base ) ) {
			return $base;
		}
		for ( $i = 1; $i < 100; $i++ ) {
			$try = $base . $i;
			if ( ! username_exists( $try ) ) {
				return $try;
			}
		}
		return '';
	}

	/**
	 * @param string $nonce Nonce.
	 * @return bool
	 */
	public static function verify_nonce( $nonce ) {
		return (bool) wp_verify_nonce( (string) $nonce, self::NONCE_ACTION );
	}

	/**
	 * @param string $url     Redirect.
	 * @param string $type    error|success.
	 * @param string $message Message.
	 * @return void
	 */
	private static function redirect_with_flash( $url, $type, $message ) {
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key = 'gmm_auth_' . sanitize_key( $type ) . '_' . substr( md5( $ip . wp_salt() ), 0, 12 );
		set_transient( $key, sanitize_text_field( $message ), 60 );
		wp_safe_redirect( add_query_arg( 'gmm_auth', sanitize_key( $type ), $url ? $url : home_url( '/' ) ) );
		exit;
	}
}
