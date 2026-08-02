<?php
/**
 * Student profile management controller.
 *
 * Loads/updates own gmm_students row, email, image, password, and preferences
 * for templates/student/profile.php (and settings prep) without changing frozen UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Student_Profile
 */
class GMM_Student_Profile {

	const NONCE_ACTION = 'gmm_student_profile_update';
	const NONCE_FIELD  = 'gmm_student_profile_nonce';
	const META_PREFS   = 'gmm_student_preferences';
	const META_SOCIAL  = 'gmm_student_social';
	const META_EXTRA   = 'gmm_student_extra';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 25, 2 );
		$loader->add_filter( 'gmm_shortcode_html', $instance, 'enhance_profile_html', 20, 2 );

		$loader->add_action( 'admin_post_gmm_student_profile_update', $instance, 'handle_profile_update' );
		$loader->add_action( 'wp_ajax_gmm_student_profile_update', $instance, 'ajax_profile_update' );
		$loader->add_action( 'wp_ajax_gmm_student_profile_image', $instance, 'ajax_profile_image' );
		$loader->add_action( 'wp_ajax_gmm_student_profile_image_remove', $instance, 'ajax_profile_image_remove' );
		$loader->add_action( 'wp_ajax_gmm_student_password_update', $instance, 'ajax_password_update' );
		$loader->add_action( 'wp_ajax_gmm_student_preferences_update', $instance, 'ajax_preferences_update' );

		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 40 );
	}

	/**
	 * Inject vars into profile / settings shortcodes.
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Shortcode.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_student_profile' === $tag ) {
			return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
		}
		if ( 'gmm_student_settings' === $tag ) {
			return array_merge( is_array( $args ) ? $args : array(), self::get_settings_vars() );
		}
		return $args;
	}

	/**
	 * Whether current user may manage this student profile.
	 *
	 * @param int $user_id Target user ID.
	 * @return bool
	 */
	public static function user_can_manage( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id || ! is_user_logged_in() ) {
			return false;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		if ( get_current_user_id() !== $user_id ) {
			return false;
		}
		if ( ! function_exists( 'gmm_is_student' ) || ! gmm_is_student( $user_id ) ) {
			return false;
		}
		if ( function_exists( 'gmm_student_can_access_dashboard' ) ) {
			return gmm_student_can_access_dashboard( $user_id );
		}
		return current_user_can( 'manage_gmm_profile' );
	}

	/**
	 * Template vars for profile page.
	 *
	 * @param int $user_id Optional WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_template_vars( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$logout  = function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) );

		if ( ! self::user_can_manage( $user_id ) ) {
			return array(
				'gmm_student_denied' => true,
				'profile'            => array(),
				'profile_completion' => array( 'percent' => 0, 'items' => array() ),
				'logout_url'         => $logout,
			);
		}

		$profile = self::get_profile_data( $user_id );

		return array(
			'gmm_student_denied' => false,
			'profile'            => $profile,
			'profile_completion' => self::get_profile_completion( $user_id ),
			'preferences'        => self::get_preferences( $user_id ),
			'user_name'          => isset( $profile['display_name'] ) ? $profile['display_name'] : '',
			'user_first_name'    => isset( $profile['first_name'] ) ? $profile['first_name'] : '',
			'logout_url'         => $logout,
			'flash_success'      => self::consume_flash( 'success' ),
			'flash_error'        => self::consume_flash( 'error' ),
		);
	}

	/**
	 * Template vars for settings page (preferences + profile snapshot).
	 *
	 * @param int $user_id Optional WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_settings_vars( $user_id = 0 ) {
		$vars = self::get_template_vars( $user_id );
		$vars['preferences'] = self::get_preferences( $user_id );
		return $vars;
	}

	/**
	 * Load profile fields for the logged-in student.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_profile_data( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! self::user_can_manage( $user_id ) ) {
			return array();
		}

		$row  = class_exists( 'GMM_Student' ) ? GMM_Student::get_profile( $user_id ) : null;
		$row  = is_array( $row ) ? $row : array();
		$user = get_userdata( $user_id );

		$image = '';
		if ( ! empty( $row['profile_image'] ) && function_exists( 'gmm_get_media_url' ) ) {
			$image = gmm_get_media_url( $row['profile_image'], 'medium' );
		}
		if ( ! $image ) {
			$image = function_exists( 'gmm_design_asset_url' )
				? gmm_design_asset_url( 'assets/img/team/02.jpg' )
				: '';
		}

		$first = isset( $row['first_name'] ) ? (string) $row['first_name'] : ( $user ? (string) $user->first_name : '' );
		$last  = isset( $row['last_name'] ) ? (string) $row['last_name'] : ( $user ? (string) $user->last_name : '' );
		$name  = trim( $first . ' ' . $last );
		if ( '' === $name && $user ) {
			$name = $user->display_name;
		}

		$instruments_raw = isset( $row['preferred_instruments'] ) ? (string) $row['preferred_instruments'] : '';
		$instruments     = array_filter( array_map( 'trim', preg_split( '/[,;\|]/', $instruments_raw ) ) );

		$extra  = get_user_meta( $user_id, self::META_EXTRA, true );
		$extra  = is_array( $extra ) ? $extra : array();
		$social = get_user_meta( $user_id, self::META_SOCIAL, true );
		$social = is_array( $social ) ? $social : array();

		return array(
			'id'                    => isset( $row['id'] ) ? absint( $row['id'] ) : 0,
			'user_id'               => $user_id,
			'first_name'            => $first,
			'last_name'             => $last,
			'display_name'          => $name ? $name : __( 'Student', 'gospel-music-mastery' ),
			'username'              => $user ? (string) $user->user_login : '',
			'email'                 => isset( $row['email'] ) && $row['email']
				? (string) $row['email']
				: ( $user ? (string) $user->user_email : '' ),
			'phone'                 => isset( $row['phone'] ) ? (string) $row['phone'] : '',
			'learning_level'        => isset( $row['learning_level'] ) ? (string) $row['learning_level'] : '',
			'learning_goals'        => isset( $row['learning_goals'] ) ? (string) $row['learning_goals'] : '',
			'preferred_instruments' => $instruments_raw,
			'instruments'           => array_values( $instruments ),
			'bio'                   => isset( $row['bio'] ) ? (string) $row['bio'] : '',
			'status'                => isset( $row['status'] ) ? sanitize_key( (string) $row['status'] ) : 'active',
			'status_label'          => self::status_label( isset( $row['status'] ) ? $row['status'] : 'active' ),
			'profile_image'         => isset( $row['profile_image'] ) ? (string) $row['profile_image'] : '',
			'image_url'             => $image,
			'country'               => isset( $extra['country'] ) ? (string) $extra['country'] : '',
			'timezone'              => isset( $extra['timezone'] ) ? (string) $extra['timezone'] : '',
			'music_style'           => isset( $extra['music_style'] ) ? (string) $extra['music_style'] : '',
			'facebook'              => isset( $social['facebook'] ) ? (string) $social['facebook'] : '',
			'instagram'             => isset( $social['instagram'] ) ? (string) $social['instagram'] : '',
			'youtube'               => isset( $social['youtube'] ) ? (string) $social['youtube'] : '',
		);
	}

	/**
	 * Update student profile fields.
	 *
	 * @param int                  $user_id User ID.
	 * @param array<string, mixed> $data    Fields.
	 * @param string               $nonce   Optional nonce.
	 * @return true|WP_Error
	 */
	public static function update_profile( $user_id = 0, $data = array(), $nonce = '' ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		if ( ! self::user_can_manage( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot edit this student profile.', 'gospel-music-mastery' ) );
		}

		$data   = is_array( $data ) ? $data : array();
		$mapped = self::normalize_input( $data );

		if ( isset( $mapped['email'] ) ) {
			$email_result = self::update_email( $user_id, $mapped['email'] );
			if ( is_wp_error( $email_result ) ) {
				return $email_result;
			}
		}

		if ( isset( $mapped['username'] ) && '' !== $mapped['username'] ) {
			$user_result = self::update_username( $user_id, $mapped['username'] );
			if ( is_wp_error( $user_result ) ) {
				return $user_result;
			}
		}

		$table_fields = array();
		foreach ( array( 'first_name', 'last_name', 'phone', 'learning_level', 'learning_goals', 'preferred_instruments', 'bio' ) as $key ) {
			if ( array_key_exists( $key, $mapped ) ) {
				$table_fields[ $key ] = $mapped[ $key ];
			}
		}
		if ( isset( $mapped['email'] ) ) {
			$table_fields['email'] = $mapped['email'];
		}

		if ( ! empty( $table_fields ) ) {
			if ( ! class_exists( 'GMM_Student' ) ) {
				return new WP_Error( 'gmm_missing', __( 'Student system unavailable.', 'gospel-music-mastery' ) );
			}
			$result = GMM_Student::update_profile( $user_id, $table_fields );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		$user_update = array( 'ID' => $user_id );
		if ( isset( $mapped['first_name'] ) ) {
			$user_update['first_name'] = $mapped['first_name'];
		}
		if ( isset( $mapped['last_name'] ) ) {
			$user_update['last_name'] = $mapped['last_name'];
		}
		if ( isset( $mapped['first_name'] ) || isset( $mapped['last_name'] ) ) {
			$profile = self::get_profile_data( $user_id );
			$dn      = trim(
				( isset( $mapped['first_name'] ) ? $mapped['first_name'] : ( isset( $profile['first_name'] ) ? $profile['first_name'] : '' ) )
				. ' '
				. ( isset( $mapped['last_name'] ) ? $mapped['last_name'] : ( isset( $profile['last_name'] ) ? $profile['last_name'] : '' ) )
			);
			if ( $dn ) {
				$user_update['display_name'] = $dn;
			}
		}
		if ( count( $user_update ) > 1 ) {
			$wp = wp_update_user( $user_update );
			if ( is_wp_error( $wp ) ) {
				return $wp;
			}
		}

		$extra_keys = array( 'country', 'timezone', 'music_style' );
		$extra      = get_user_meta( $user_id, self::META_EXTRA, true );
		$extra      = is_array( $extra ) ? $extra : array();
		$has_extra  = false;
		foreach ( $extra_keys as $key ) {
			if ( array_key_exists( $key, $mapped ) ) {
				$extra[ $key ] = $mapped[ $key ];
				$has_extra     = true;
			}
		}
		if ( $has_extra ) {
			update_user_meta( $user_id, self::META_EXTRA, $extra );
		}

		$social_keys = array( 'facebook', 'instagram', 'youtube' );
		$social      = get_user_meta( $user_id, self::META_SOCIAL, true );
		$social      = is_array( $social ) ? $social : array();
		$has_social  = false;
		foreach ( $social_keys as $key ) {
			if ( array_key_exists( $key, $mapped ) ) {
				$social[ $key ] = $mapped[ $key ];
				$has_social     = true;
			}
		}
		if ( $has_social ) {
			update_user_meta( $user_id, self::META_SOCIAL, $social );
		}

		if ( class_exists( 'GMM_Student_Dashboard' ) ) {
			GMM_Student_Dashboard::flush_cache( $user_id );
		}

		/**
		 * Fires after a student profile is updated.
		 *
		 * @param int                  $user_id User ID.
		 * @param array<string, mixed> $mapped  Normalized fields.
		 */
		do_action( 'gmm_student_profile_updated', $user_id, $mapped );

		return true;
	}

	/**
	 * Update WordPress + student email safely.
	 *
	 * @param int    $user_id User ID.
	 * @param string $email   New email.
	 * @return true|WP_Error
	 */
	public static function update_email( $user_id, $email ) {
		$user_id = absint( $user_id );
		$email   = sanitize_email( (string) $email );

		if ( ! self::user_can_manage( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot edit this student profile.', 'gospel-music-mastery' ) );
		}

		if ( ! is_email( $email ) ) {
			return new WP_Error( 'gmm_email', __( 'Please enter a valid email address.', 'gospel-music-mastery' ) );
		}

		$existing = email_exists( $email );
		if ( $existing && (int) $existing !== (int) $user_id ) {
			return new WP_Error( 'gmm_email_exists', __( 'An account with this email already exists.', 'gospel-music-mastery' ) );
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return new WP_Error( 'gmm_user', __( 'User not found.', 'gospel-music-mastery' ) );
		}

		if ( strtolower( $user->user_email ) !== strtolower( $email ) ) {
			$updated = wp_update_user(
				array(
					'ID'         => $user_id,
					'user_email' => $email,
				)
			);
			if ( is_wp_error( $updated ) ) {
				return $updated;
			}
		}

		return true;
	}

	/**
	 * Update username safely.
	 *
	 * @param int    $user_id  User ID.
	 * @param string $username New username.
	 * @return true|WP_Error
	 */
	public static function update_username( $user_id, $username ) {
		$user_id  = absint( $user_id );
		$username = sanitize_user( (string) $username, true );

		if ( ! self::user_can_manage( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot edit this student profile.', 'gospel-music-mastery' ) );
		}

		if ( '' === $username || strlen( $username ) < 3 ) {
			return new WP_Error( 'gmm_username', __( 'Please enter a valid username.', 'gospel-music-mastery' ) );
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return new WP_Error( 'gmm_user', __( 'User not found.', 'gospel-music-mastery' ) );
		}

		if ( strtolower( $user->user_login ) === strtolower( $username ) ) {
			return true;
		}

		$exists = username_exists( $username );
		if ( $exists && (int) $exists !== (int) $user_id ) {
			return new WP_Error( 'gmm_user_exists', __( 'This username is already taken.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$updated = $wpdb->update(
			$wpdb->users,
			array( 'user_login' => $username ),
			array( 'ID' => $user_id ),
			array( '%s' ),
			array( '%d' )
		);
		if ( false === $updated ) {
			return new WP_Error( 'gmm_username', __( 'Could not update username.', 'gospel-music-mastery' ) );
		}

		clean_user_cache( $user_id );
		return true;
	}

	/**
	 * Upload / set profile image as attachment ID.
	 *
	 * @param int    $user_id  User ID.
	 * @param string $file_key $_FILES key.
	 * @return int|WP_Error Attachment ID.
	 */
	public static function update_profile_image( $user_id = 0, $file_key = 'profile_photo' ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! self::user_can_manage( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot edit this student profile.', 'gospel-music-mastery' ) );
		}

		$student_id = class_exists( 'GMM_Student' ) ? GMM_Student::get_student_id( $user_id ) : 0;
		if ( ! $student_id ) {
			return new WP_Error( 'gmm_no_profile', __( 'Student profile not found.', 'gospel-music-mastery' ) );
		}

		if ( empty( $_FILES[ $file_key ]['tmp_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			// Accept alternate key names.
			if ( ! empty( $_FILES['profile_image']['tmp_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
				$file_key = 'profile_image';
			} else {
				return new WP_Error( 'gmm_no_file', __( 'No file was uploaded.', 'gospel-music-mastery' ) );
			}
		}

		if ( class_exists( 'GMM_Media' ) ) {
			$result = GMM_Media::upload_image( $file_key, 'student_profile', $student_id, '' );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
			$attachment_id = isset( $result['id'] ) ? absint( $result['id'] ) : 0;
		} else {
			$attachment_id = self::handle_direct_image_upload( $file_key, $user_id );
			if ( is_wp_error( $attachment_id ) ) {
				return $attachment_id;
			}
			GMM_Student::update_profile( $user_id, array( 'profile_image' => (string) $attachment_id ) );
		}

		if ( ! $attachment_id ) {
			return new WP_Error( 'gmm_upload_failed', __( 'Image upload failed.', 'gospel-music-mastery' ) );
		}

		if ( class_exists( 'GMM_Student_Dashboard' ) ) {
			GMM_Student_Dashboard::flush_cache( $user_id );
		}

		do_action( 'gmm_student_profile_updated', $user_id, array( 'profile_image' => $attachment_id ) );

		return $attachment_id;
	}

	/**
	 * Remove profile image.
	 *
	 * @param int $user_id User ID.
	 * @return true|WP_Error
	 */
	public static function remove_profile_image( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! self::user_can_manage( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot edit this student profile.', 'gospel-music-mastery' ) );
		}

		$result = GMM_Student::update_profile( $user_id, array( 'profile_image' => '' ) );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		if ( class_exists( 'GMM_Student_Dashboard' ) ) {
			GMM_Student_Dashboard::flush_cache( $user_id );
		}

		do_action( 'gmm_student_profile_updated', $user_id, array( 'profile_image' => '' ) );
		return true;
	}

	/**
	 * Change password with current password verification.
	 *
	 * @param int    $user_id          User ID.
	 * @param string $current_password Current password.
	 * @param string $new_password     New password.
	 * @param string $confirm_password Confirm password.
	 * @return true|WP_Error
	 */
	public static function update_password( $user_id, $current_password, $new_password, $confirm_password = '' ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! self::user_can_manage( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot change this password.', 'gospel-music-mastery' ) );
		}

		$user = get_userdata( $user_id );
		if ( ! $user ) {
			return new WP_Error( 'gmm_user', __( 'User not found.', 'gospel-music-mastery' ) );
		}

		if ( '' === (string) $current_password || '' === (string) $new_password ) {
			return new WP_Error( 'gmm_required', __( 'Please fill in all password fields.', 'gospel-music-mastery' ) );
		}

		if ( $confirm_password && (string) $new_password !== (string) $confirm_password ) {
			return new WP_Error( 'gmm_password_mismatch', __( 'Passwords do not match.', 'gospel-music-mastery' ) );
		}

		if ( ! wp_check_password( (string) $current_password, $user->user_pass, $user_id ) ) {
			return new WP_Error( 'gmm_bad_password', __( 'Current password is incorrect.', 'gospel-music-mastery' ) );
		}

		if ( class_exists( 'GMM_Auth' ) ) {
			$strength = GMM_Auth::validate_password_strength( (string) $new_password );
			if ( is_wp_error( $strength ) ) {
				return $strength;
			}
		} elseif ( strlen( (string) $new_password ) < 8 ) {
			return new WP_Error( 'gmm_weak_password', __( 'Password must be at least 8 characters.', 'gospel-music-mastery' ) );
		}

		wp_set_password( (string) $new_password, $user_id );
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true, is_ssl() );

		do_action( 'gmm_student_password_updated', $user_id );

		return true;
	}

	/**
	 * Profile completion percentage.
	 *
	 * @param int $user_id User ID.
	 * @return array{percent:int,items:array<int,array<string,mixed>>,done:int,total:int}
	 */
	public static function get_profile_completion( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$row     = class_exists( 'GMM_Student' ) ? GMM_Student::get_profile( $user_id ) : null;
		$row     = is_array( $row ) ? $row : array();

		$items = array(
			array(
				'key'   => 'image',
				'label' => __( 'Profile Image', 'gospel-music-mastery' ),
				'done'  => ! empty( $row['profile_image'] ),
			),
			array(
				'key'   => 'phone',
				'label' => __( 'Phone', 'gospel-music-mastery' ),
				'done'  => ! empty( $row['phone'] ),
			),
			array(
				'key'   => 'learning_level',
				'label' => __( 'Learning Level', 'gospel-music-mastery' ),
				'done'  => ! empty( $row['learning_level'] ),
			),
			array(
				'key'   => 'goals',
				'label' => __( 'Learning Goals', 'gospel-music-mastery' ),
				'done'  => ! empty( $row['learning_goals'] ),
			),
			array(
				'key'   => 'instruments',
				'label' => __( 'Preferred Instruments', 'gospel-music-mastery' ),
				'done'  => ! empty( $row['preferred_instruments'] ),
			),
		);

		$done  = 0;
		$total = count( $items );
		foreach ( $items as $item ) {
			if ( ! empty( $item['done'] ) ) {
				$done++;
			}
		}

		return array(
			'percent' => $total ? (int) round( ( $done / $total ) * 100 ) : 0,
			'items'   => $items,
			'done'    => $done,
			'total'   => $total,
		);
	}

	/**
	 * Get notification / account preferences.
	 *
	 * @param int $user_id User ID.
	 * @return array<string, mixed>
	 */
	public static function get_preferences( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$defaults = array(
			'email_notifications' => true,
			'lesson_reminders'    => true,
			'booking_updates'     => true,
			'teacher_messages'    => true,
			'payment_alerts'      => false,
		);
		$prefs = get_user_meta( $user_id, self::META_PREFS, true );
		$prefs = is_array( $prefs ) ? $prefs : array();
		return wp_parse_args( $prefs, $defaults );
	}

	/**
	 * Save notification preferences.
	 *
	 * @param int                  $user_id User ID.
	 * @param array<string, mixed> $data    Prefs.
	 * @return true|WP_Error
	 */
	public static function update_preferences( $user_id = 0, $data = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! self::user_can_manage( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot update these preferences.', 'gospel-music-mastery' ) );
		}

		$data  = is_array( $data ) ? $data : array();
		$prefs = self::get_preferences( $user_id );
		$keys  = array( 'email_notifications', 'lesson_reminders', 'booking_updates', 'teacher_messages', 'payment_alerts' );
		foreach ( $keys as $key ) {
			if ( array_key_exists( $key, $data ) ) {
				$prefs[ $key ] = (bool) $data[ $key ];
			}
		}
		update_user_meta( $user_id, self::META_PREFS, $prefs );
		do_action( 'gmm_student_preferences_updated', $user_id, $prefs );
		return true;
	}

	/**
	 * Verify nonce.
	 *
	 * @param string $nonce Nonce.
	 * @return bool
	 */
	public static function verify_nonce( $nonce ) {
		return (bool) wp_verify_nonce( (string) $nonce, self::NONCE_ACTION );
	}

	/**
	 * Enhance forms: nonce, action, password card.
	 *
	 * @param string $html HTML.
	 * @param string $tag  Tag.
	 * @return string
	 */
	public function enhance_profile_html( $html, $tag ) {
		if ( ! in_array( $tag, array( 'gmm_student_profile', 'gmm_student_settings' ), true ) || '' === $html ) {
			return $html;
		}
		if ( ! self::user_can_manage() ) {
			return $html;
		}

		$nonce  = wp_create_nonce( self::NONCE_ACTION );
		$action = esc_url( admin_url( 'admin-post.php' ) );
		$hidden = '<input type="hidden" name="action" value="gmm_student_profile_update" />'
			. '<input type="hidden" name="' . esc_attr( self::NONCE_FIELD ) . '" value="' . esc_attr( $nonce ) . '" />'
			. '<input type="hidden" name="gmm_redirect" value="' . esc_attr( self::current_url() ) . '" />';

		if ( 'gmm_student_profile' === $tag ) {
			if ( false === strpos( $html, 'name="' . self::NONCE_FIELD . '"' ) ) {
				$html = preg_replace(
					'/(<form\b[^>]*\bid=["\']student-profile-form["\'][^>]*>)/i',
					'$1' . $hidden,
					$html,
					1
				);
				$html = preg_replace(
					'/(<form\b[^>]*\bid=["\']student-profile-form["\'][^>]*\baction=["\'])([^"\']*)(["\'])/i',
					'$1' . $action . '$3',
					$html,
					1
				);
			}

			if ( false === strpos( $html, 'id="gmm-student-password-form"' ) ) {
				$pwd = self::render_password_section();
				$html = preg_replace(
					'/(<div class="sd-card-actions sd-profile-form-actions">)/',
					$pwd . '$1',
					$html,
					1
				);
			}
		}

		if ( 'gmm_student_settings' === $tag ) {
			$settings_hidden = '<input type="hidden" name="' . esc_attr( self::NONCE_FIELD ) . '" value="' . esc_attr( $nonce ) . '" />';
			foreach ( array( 'ss-profile-form', 'ss-password-form', 'ss-notifications-form' ) as $fid ) {
				if ( false === strpos( $html, 'id="' . $fid . '"' ) ) {
					continue;
				}
				if ( false === strpos( $html, 'data-gmm-nonce="' . $fid . '"' ) ) {
					$html = preg_replace(
						'/(<form\b[^>]*\bid=["\']' . preg_quote( $fid, '/' ) . '["\'][^>]*>)/i',
						'$1' . $settings_hidden . '<input type="hidden" data-gmm-nonce="' . esc_attr( $fid ) . '" value="1" />',
						$html,
						1
					);
				}
			}

			// Add name attributes to notification toggles when missing.
			$map = array(
				'ss-toggle-email'    => 'email_notifications',
				'ss-toggle-lessons'  => 'lesson_reminders',
				'ss-toggle-bookings' => 'booking_updates',
				'ss-toggle-messages' => 'teacher_messages',
				'ss-toggle-payments' => 'payment_alerts',
			);
			foreach ( $map as $id => $name ) {
				if ( false !== strpos( $html, 'name="' . $name . '"' ) ) {
					continue;
				}
				$html = preg_replace(
					'/(id=["\']' . preg_quote( $id, '/' ) . '["\'][^>]*)(>)/i',
					'$1 name="' . esc_attr( $name ) . '" value="1"$2',
					$html,
					1
				);
			}
		}

		return $html;
	}

	/**
	 * admin-post profile update.
	 *
	 * @return void
	 */
	public function handle_profile_update() {
		$back = isset( $_POST['gmm_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['gmm_redirect'] ) ) : home_url( '/' );
		check_admin_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$nonce  = isset( $_POST[ self::NONCE_FIELD ] ) ? sanitize_text_field( wp_unslash( $_POST[ self::NONCE_FIELD ] ) ) : '';
		$result = self::update_profile( get_current_user_id(), wp_unslash( $_POST ), $nonce );

		if ( is_wp_error( $result ) ) {
			self::redirect_with_flash( $back, 'error', $result->get_error_message() );
		}

		self::redirect_with_flash( $back, 'success', __( 'Profile changes saved successfully.', 'gospel-music-mastery' ) );
	}

	/**
	 * AJAX profile update.
	 *
	 * @return void
	 */
	public function ajax_profile_update() {
		check_ajax_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$result = self::update_profile( get_current_user_id(), wp_unslash( $_POST ), '' );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'code'    => $result->get_error_code(),
					'message' => $result->get_error_message(),
				),
				400
			);
		}

		$user_id = get_current_user_id();
		wp_send_json_success(
			array(
				'message'    => __( 'Profile changes saved successfully.', 'gospel-music-mastery' ),
				'profile'    => self::get_profile_data( $user_id ),
				'completion' => self::get_profile_completion( $user_id ),
			)
		);
	}

	/**
	 * AJAX image upload.
	 *
	 * @return void
	 */
	public function ajax_profile_image() {
		check_ajax_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$result = self::update_profile_image( get_current_user_id(), 'profile_photo' );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'code'    => $result->get_error_code(),
					'message' => $result->get_error_message(),
				),
				400
			);
		}

		$url = function_exists( 'gmm_get_media_url' ) ? gmm_get_media_url( $result, 'medium' ) : wp_get_attachment_url( $result );
		wp_send_json_success(
			array(
				'message'       => __( 'Profile photo updated.', 'gospel-music-mastery' ),
				'attachment_id' => (int) $result,
				'url'           => $url ? $url : '',
				'completion'    => self::get_profile_completion( get_current_user_id() ),
			)
		);
	}

	/**
	 * AJAX remove image.
	 *
	 * @return void
	 */
	public function ajax_profile_image_remove() {
		check_ajax_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$result = self::remove_profile_image( get_current_user_id() );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		$fallback = function_exists( 'gmm_design_asset_url' )
			? gmm_design_asset_url( 'assets/img/team/02.jpg' )
			: '';

		wp_send_json_success(
			array(
				'message' => __( 'Profile photo removed.', 'gospel-music-mastery' ),
				'url'     => $fallback,
			)
		);
	}

	/**
	 * AJAX password update.
	 *
	 * @return void
	 */
	public function ajax_password_update() {
		check_ajax_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$result = self::update_password(
			get_current_user_id(),
			isset( $_POST['current_password'] ) ? (string) wp_unslash( $_POST['current_password'] ) : '',
			isset( $_POST['new_password'] ) ? (string) wp_unslash( $_POST['new_password'] ) : '',
			isset( $_POST['confirm_password'] ) ? (string) wp_unslash( $_POST['confirm_password'] ) : ''
		);

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'code'    => $result->get_error_code(),
					'message' => $result->get_error_message(),
				),
				400
			);
		}

		wp_send_json_success( array( 'message' => __( 'Password updated successfully.', 'gospel-music-mastery' ) ) );
	}

	/**
	 * AJAX preferences update.
	 *
	 * @return void
	 */
	public function ajax_preferences_update() {
		check_ajax_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$keys = array( 'email_notifications', 'lesson_reminders', 'booking_updates', 'teacher_messages', 'payment_alerts' );
		$data = array();
		foreach ( $keys as $key ) {
			$data[ $key ] = ! empty( $_POST[ $key ] );
		}

		$result = self::update_preferences( get_current_user_id(), $data );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		wp_send_json_success(
			array(
				'message'     => __( 'Preferences saved successfully.', 'gospel-music-mastery' ),
				'preferences' => self::get_preferences( get_current_user_id() ),
			)
		);
	}

	/**
	 * Enqueue profile / settings script.
	 *
	 * @return void
	 */
	public function maybe_enqueue_assets() {
		if ( ! self::user_can_manage() ) {
			return;
		}
		if ( ! class_exists( 'GMM_Assets' ) || ! GMM_Assets::is_gmm_page() ) {
			return;
		}

		$post    = get_queried_object();
		$content = ( $post instanceof WP_Post ) ? (string) $post->post_content : '';
		$needed  = has_shortcode( $content, 'gmm_student_profile' )
			|| has_shortcode( $content, 'gmm_student_settings' )
			|| false !== strpos( $content, 'gmm_student_profile' )
			|| false !== strpos( $content, 'gmm_student_settings' );

		if ( ! $needed ) {
			return;
		}

		$version = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';
		wp_enqueue_script(
			'gmm-student-profile',
			GMM_URL . 'assets/js/gmm-student-profile.js',
			array( 'gmm-core-script' ),
			$version,
			true
		);

		$vars = self::get_template_vars();
		wp_localize_script(
			'gmm-student-profile',
			'GMM_STUDENT_PROFILE',
			array(
				'nonce'      => wp_create_nonce( self::NONCE_ACTION ),
				'nonceField' => self::NONCE_FIELD,
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'profile'    => isset( $vars['profile'] ) ? $vars['profile'] : array(),
				'actions'    => array(
					'update'      => 'gmm_student_profile_update',
					'image'       => 'gmm_student_profile_image',
					'imageRemove' => 'gmm_student_profile_image_remove',
					'password'    => 'gmm_student_password_update',
					'preferences' => 'gmm_student_preferences_update',
				),
				'i18n'       => array(
					'saved'    => __( 'Profile changes saved successfully.', 'gospel-music-mastery' ),
					'image'    => __( 'Profile photo updated.', 'gospel-music-mastery' ),
					'password' => __( 'Password updated successfully.', 'gospel-music-mastery' ),
					'prefs'    => __( 'Preferences saved successfully.', 'gospel-music-mastery' ),
					'error'    => __( 'Something went wrong. Please try again.', 'gospel-music-mastery' ),
				),
			)
		);
	}

	/**
	 * Normalize form input.
	 *
	 * @param array<string, mixed> $data Raw.
	 * @return array<string, mixed>
	 */
	private static function normalize_input( $data ) {
		$out = array();

		if ( isset( $data['first_name'] ) ) {
			$out['first_name'] = sanitize_text_field( (string) $data['first_name'] );
		}
		if ( isset( $data['last_name'] ) ) {
			$out['last_name'] = sanitize_text_field( (string) $data['last_name'] );
		}
		if ( isset( $data['email'] ) ) {
			$out['email'] = sanitize_email( (string) $data['email'] );
		}
		if ( isset( $data['username'] ) ) {
			$out['username'] = sanitize_user( (string) $data['username'], true );
		}
		if ( isset( $data['phone'] ) ) {
			$out['phone'] = sanitize_text_field( (string) $data['phone'] );
		}

		$level = '';
		if ( isset( $data['learning_level'] ) ) {
			$level = sanitize_text_field( (string) $data['learning_level'] );
		} elseif ( isset( $data['experience_level'] ) ) {
			$level = sanitize_text_field( (string) $data['experience_level'] );
		}
		if ( '' !== $level || isset( $data['learning_level'] ) || isset( $data['experience_level'] ) ) {
			$out['learning_level'] = $level;
		}

		if ( isset( $data['learning_goals'] ) ) {
			$out['learning_goals'] = sanitize_textarea_field( (string) $data['learning_goals'] );
		}

		if ( isset( $data['instruments'] ) && is_array( $data['instruments'] ) ) {
			$inst = array_map( 'sanitize_text_field', wp_unslash( $data['instruments'] ) );
			$inst = array_filter( $inst );
			$out['preferred_instruments'] = implode( ', ', $inst );
		} elseif ( isset( $data['preferred_instruments'] ) ) {
			$out['preferred_instruments'] = sanitize_textarea_field( (string) $data['preferred_instruments'] );
		}

		if ( isset( $data['about_me'] ) ) {
			$out['bio'] = sanitize_textarea_field( (string) $data['about_me'] );
		} elseif ( isset( $data['bio'] ) ) {
			$out['bio'] = sanitize_textarea_field( (string) $data['bio'] );
		}

		foreach ( array( 'country', 'timezone', 'music_style' ) as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$out[ $key ] = sanitize_text_field( (string) $data[ $key ] );
			}
		}

		foreach ( array( 'facebook', 'instagram', 'youtube' ) as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$out[ $key ] = esc_url_raw( (string) $data[ $key ] );
			}
		}

		// Never allow role / status manipulation from student form.
		unset( $out['role'], $out['status'], $out['user_role'] );

		return $out;
	}

	/**
	 * Direct image upload fallback.
	 *
	 * @param string $file_key File key.
	 * @param int    $user_id  User ID.
	 * @return int|WP_Error
	 */
	private static function handle_direct_image_upload( $file_key, $user_id ) {
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		if ( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
		}

		$file = isset( $_FILES[ $file_key ] ) ? $_FILES[ $file_key ] : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! $file || empty( $file['tmp_name'] ) ) {
			return new WP_Error( 'gmm_no_file', __( 'No file was uploaded.', 'gospel-music-mastery' ) );
		}

		$check = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
		$allowed = array( 'image/jpeg', 'image/png', 'image/jpg', 'image/webp' );
		$type    = isset( $check['type'] ) ? $check['type'] : ( isset( $file['type'] ) ? $file['type'] : '' );
		if ( ! in_array( $type, $allowed, true ) ) {
			return new WP_Error( 'gmm_file_type', __( 'Only JPG and PNG images are allowed.', 'gospel-music-mastery' ) );
		}
		if ( ! empty( $file['size'] ) && (int) $file['size'] > 5 * MB_IN_BYTES ) {
			return new WP_Error( 'gmm_file_size', __( 'Image must be 5MB or smaller.', 'gospel-music-mastery' ) );
		}

		$upload = wp_handle_upload( $file, array( 'test_form' => false ) );
		if ( isset( $upload['error'] ) ) {
			return new WP_Error( 'gmm_upload', $upload['error'] );
		}

		$attachment = array(
			'post_mime_type' => $upload['type'],
			'post_title'     => sanitize_file_name( wp_basename( $upload['file'] ) ),
			'post_content'   => '',
			'post_status'    => 'inherit',
			'post_author'    => $user_id,
		);
		$attach_id = wp_insert_attachment( $attachment, $upload['file'] );
		if ( is_wp_error( $attach_id ) || ! $attach_id ) {
			return new WP_Error( 'gmm_upload_failed', __( 'Image upload failed.', 'gospel-music-mastery' ) );
		}

		$meta = wp_generate_attachment_metadata( $attach_id, $upload['file'] );
		wp_update_attachment_metadata( $attach_id, $meta );

		return (int) $attach_id;
	}

	/**
	 * Password form section (existing card classes).
	 *
	 * @return string
	 */
	private static function render_password_section() {
		ob_start();
		?>
							<section class="sd-card" id="gmm-student-password-card">
								<div class="sd-card-head">
									<div>
										<h3><?php esc_html_e( 'Password Security', 'gospel-music-mastery' ); ?></h3>
										<p><?php esc_html_e( 'Choose a strong password to keep your student account secure.', 'gospel-music-mastery' ); ?></p>
									</div>
								</div>
								<form action="#" method="post" id="gmm-student-password-form" class="settings-password-form" novalidate>
									<div class="form-group">
										<label for="gmm-current-password"><?php esc_html_e( 'Current Password', 'gospel-music-mastery' ); ?></label>
										<input type="password" class="form-control" id="gmm-current-password" name="current_password" autocomplete="current-password">
									</div>
									<div class="row">
										<div class="col-md-6">
											<div class="form-group">
												<label for="gmm-new-password"><?php esc_html_e( 'New Password', 'gospel-music-mastery' ); ?></label>
												<input type="password" class="form-control" id="gmm-new-password" name="new_password" autocomplete="new-password">
											</div>
										</div>
										<div class="col-md-6">
											<div class="form-group">
												<label for="gmm-confirm-password"><?php esc_html_e( 'Confirm Password', 'gospel-music-mastery' ); ?></label>
												<input type="password" class="form-control" id="gmm-confirm-password" name="confirm_password" autocomplete="new-password">
											</div>
										</div>
									</div>
									<button type="submit" class="theme-btn"><i class="far fa-key"></i> <?php esc_html_e( 'Update Password', 'gospel-music-mastery' ); ?></button>
								</form>
							</section>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Status label.
	 *
	 * @param string $status Status.
	 * @return string
	 */
	private static function status_label( $status ) {
		$status = sanitize_key( (string) $status );
		$map    = array(
			'active'    => __( 'Active', 'gospel-music-mastery' ),
			'inactive'  => __( 'Inactive', 'gospel-music-mastery' ),
			'suspended' => __( 'Suspended', 'gospel-music-mastery' ),
			'pending'   => __( 'Pending', 'gospel-music-mastery' ),
		);
		return isset( $map[ $status ] ) ? $map[ $status ] : ucfirst( $status );
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
	 * @param string $url     Redirect.
	 * @param string $type    error|success.
	 * @param string $message Message.
	 * @return void
	 */
	private static function redirect_with_flash( $url, $type, $message ) {
		$key = 'gmm_student_profile_' . sanitize_key( $type ) . '_' . self::client_key();
		set_transient( $key, sanitize_text_field( $message ), 60 );
		wp_safe_redirect( add_query_arg( 'gmm_profile', sanitize_key( $type ), $url ? $url : home_url( '/' ) ) );
		exit;
	}

	/**
	 * @param string $type error|success.
	 * @return string
	 */
	private static function consume_flash( $type ) {
		if ( empty( $_GET['gmm_profile'] ) || sanitize_key( wp_unslash( $_GET['gmm_profile'] ) ) !== $type ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return '';
		}
		$key = 'gmm_student_profile_' . sanitize_key( $type ) . '_' . self::client_key();
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
