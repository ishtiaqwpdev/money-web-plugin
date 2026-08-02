<?php
/**
 * Teacher profile management controller.
 *
 * Loads/updates own gmm_teachers row, email, image, and password prep
 * for templates/teacher/profile.php without changing the frozen UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Teacher_Profile
 */
class GMM_Teacher_Profile {

	const NONCE_ACTION = 'gmm_teacher_profile_update';
	const NONCE_FIELD  = 'gmm_teacher_profile_nonce';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 25, 2 );
		$loader->add_filter( 'gmm_shortcode_html', $instance, 'enhance_profile_form_html', 20, 2 );

		$loader->add_action( 'admin_post_gmm_teacher_profile_update', $instance, 'handle_profile_update' );
		$loader->add_action( 'wp_ajax_gmm_teacher_profile_update', $instance, 'ajax_profile_update' );
		$loader->add_action( 'wp_ajax_gmm_teacher_profile_image', $instance, 'ajax_profile_image' );
		$loader->add_action( 'wp_ajax_gmm_teacher_password_update', $instance, 'ajax_password_update' );

		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 40 );
	}

	/**
	 * Inject profile vars into [gmm_teacher_profile].
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Shortcode.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_teacher_profile' !== $tag ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * Whether current user may manage this teacher profile.
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
		if ( ! function_exists( 'gmm_is_teacher' ) || ! gmm_is_teacher( $user_id ) ) {
			return false;
		}
		if ( class_exists( 'GMM_Teacher_Auth' ) && ! GMM_Teacher_Auth::is_approved( $user_id ) ) {
			return false;
		}
		return true;
	}

	/**
	 * Template variables for the profile page.
	 *
	 * @param int $user_id Optional WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_template_vars( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! self::user_can_manage( $user_id ) ) {
			$pending = function_exists( 'gmm_is_teacher' ) && gmm_is_teacher( $user_id )
				&& class_exists( 'GMM_Teacher_Auth' )
				&& ! GMM_Teacher_Auth::is_approved( $user_id );

			return array(
				'gmm_teacher_denied'  => true,
				'gmm_teacher_pending' => $pending,
				'profile'             => array(),
				'profile_completion'  => array( 'percent' => 0, 'items' => array() ),
				'logout_url'          => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
			);
		}

		$profile = self::get_profile_data( $user_id );
		$stats   = class_exists( 'GMM_Teacher_Dashboard' )
			? GMM_Teacher_Dashboard::get_statistics( $user_id )
			: array();

		$flash_success = self::consume_flash( 'success' );
		$flash_error   = self::consume_flash( 'error' );

		return array(
			'gmm_teacher_denied'  => false,
			'gmm_teacher_pending' => false,
			'profile'             => $profile,
			'profile_completion'  => self::get_profile_completion( $user_id ),
			'profile_stats'       => array(
				'rating'   => isset( $profile['rating'] ) ? (float) $profile['rating'] : 0,
				'students' => isset( $stats['total_students'] ) ? (int) $stats['total_students'] : 0,
				'classes'  => isset( $stats['total_classes'] ) ? (int) $stats['total_classes'] : 0,
			),
			'user_name'           => isset( $profile['display_name'] ) ? $profile['display_name'] : '',
			'user_first_name'     => isset( $profile['first_name'] ) ? $profile['first_name'] : '',
			'logout_url'          => function_exists( 'gmm_logout_url' ) ? gmm_logout_url( home_url( '/' ) ) : wp_logout_url( home_url( '/' ) ),
			'flash_success'       => $flash_success,
			'flash_error'         => $flash_error,
			'skill_options'       => self::skill_options(),
		);
	}

	/**
	 * Load profile fields for the logged-in teacher.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_profile_data( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! self::user_can_manage( $user_id ) ) {
			return array();
		}

		$row  = class_exists( 'GMM_Teacher' ) ? GMM_Teacher::get_profile( $user_id ) : null;
		$row  = is_array( $row ) ? $row : array();
		$user = get_userdata( $user_id );

		$image = '';
		if ( ! empty( $row['profile_image'] ) && function_exists( 'gmm_get_media_url' ) ) {
			$image = gmm_get_media_url( $row['profile_image'], 'medium' );
		}
		if ( ! $image ) {
			$image = function_exists( 'gmm_design_asset_url' )
				? gmm_design_asset_url( 'assets/img/team/01.jpg' )
				: '';
		}

		$first = isset( $row['first_name'] ) ? (string) $row['first_name'] : '';
		$last  = isset( $row['last_name'] ) ? (string) $row['last_name'] : '';
		$name  = trim( $first . ' ' . $last );
		if ( '' === $name && $user ) {
			$name = $user->display_name;
		}

		$display = (string) get_user_meta( $user_id, 'gmm_display_name', true );
		if ( '' === $display ) {
			$display = $name ? $name : ( $user ? $user->display_name : '' );
		}

		$social = get_user_meta( $user_id, 'gmm_teacher_social', true );
		$social = is_array( $social ) ? $social : array();

		$timezone = (string) get_user_meta( $user_id, 'gmm_teacher_timezone', true );
		if ( '' === $timezone ) {
			$timezone = 'Eastern Time';
		}

		return array(
			'id'              => isset( $row['id'] ) ? absint( $row['id'] ) : 0,
			'user_id'         => $user_id,
			'first_name'      => $first,
			'last_name'       => $last,
			'display_name'    => $display,
			'username'        => $user ? $user->user_login : '',
			'email'           => isset( $row['email'] ) && $row['email']
				? (string) $row['email']
				: ( $user ? $user->user_email : '' ),
			'phone'           => isset( $row['phone'] ) ? (string) $row['phone'] : '',
			'specialization'  => isset( $row['specialization'] ) ? (string) $row['specialization'] : '',
			'skill'           => isset( $row['specialization'] ) ? (string) $row['specialization'] : '',
			'experience'      => isset( $row['experience'] ) ? (string) $row['experience'] : '',
			'bio'             => isset( $row['bio'] ) ? (string) $row['bio'] : '',
			'biography'       => isset( $row['bio'] ) ? (string) $row['bio'] : '',
			'rating'          => isset( $row['rating'] ) ? (float) $row['rating'] : 0.0,
			'status'          => isset( $row['status'] ) ? sanitize_key( (string) $row['status'] ) : '',
			'profile_image'   => isset( $row['profile_image'] ) ? (string) $row['profile_image'] : '',
			'image_url'       => $image,
			'timezone'        => $timezone,
			'facebook'        => isset( $social['facebook'] ) ? (string) $social['facebook'] : '',
			'instagram'       => isset( $social['instagram'] ) ? (string) $social['instagram'] : '',
			'youtube'         => isset( $social['youtube'] ) ? (string) $social['youtube'] : '',
			'website'         => isset( $social['website'] ) ? (string) $social['website'] : '',
		);
	}

	/**
	 * Update teacher profile (own data only).
	 *
	 * @param int                  $user_id User ID.
	 * @param array<string, mixed> $data    Fields.
	 * @return true|WP_Error
	 */
	public static function update_profile( $user_id = 0, $data = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$data    = is_array( $data ) ? $data : array();

		if ( ! self::user_can_manage( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot edit this teacher profile.', 'gospel-music-mastery' ) );
		}

		$mapped = self::normalize_incoming_fields( $data );

		// Email update (WP user + teachers table).
		if ( array_key_exists( 'email', $mapped ) ) {
			$email_result = self::update_email( $user_id, $mapped['email'] );
			if ( is_wp_error( $email_result ) ) {
				return $email_result;
			}
		}

		$table_fields = array();
		foreach ( array( 'first_name', 'last_name', 'phone', 'specialization', 'experience', 'bio', 'email', 'profile_image' ) as $key ) {
			if ( array_key_exists( $key, $mapped ) ) {
				$table_fields[ $key ] = $mapped[ $key ];
			}
		}

		if ( ! empty( $table_fields ) ) {
			// Ensure profile_image stored as attachment ID string only.
			if ( isset( $table_fields['profile_image'] ) ) {
				$table_fields['profile_image'] = (string) absint( $table_fields['profile_image'] );
				if ( '0' === $table_fields['profile_image'] ) {
					$table_fields['profile_image'] = '';
				}
			}

			$result = class_exists( 'GMM_Teacher' )
				? GMM_Teacher::update_profile( $user_id, $table_fields )
				: new WP_Error( 'gmm_missing', __( 'Teacher system unavailable.', 'gospel-music-mastery' ) );

			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}

		// Sync WP user names / display name.
		$user_update = array( 'ID' => $user_id );
		if ( isset( $mapped['first_name'] ) ) {
			$user_update['first_name'] = $mapped['first_name'];
		}
		if ( isset( $mapped['last_name'] ) ) {
			$user_update['last_name'] = $mapped['last_name'];
		}
		if ( isset( $mapped['display_name'] ) && '' !== $mapped['display_name'] ) {
			$user_update['display_name'] = $mapped['display_name'];
			update_user_meta( $user_id, 'gmm_display_name', $mapped['display_name'] );
		} elseif ( isset( $mapped['first_name'] ) || isset( $mapped['last_name'] ) ) {
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

		if ( isset( $mapped['timezone'] ) ) {
			update_user_meta( $user_id, 'gmm_teacher_timezone', $mapped['timezone'] );
		}

		$social_keys = array( 'facebook', 'instagram', 'youtube', 'website' );
		$has_social  = false;
		$social      = get_user_meta( $user_id, 'gmm_teacher_social', true );
		$social      = is_array( $social ) ? $social : array();
		foreach ( $social_keys as $key ) {
			if ( array_key_exists( $key, $mapped ) ) {
				$social[ $key ] = $mapped[ $key ];
				$has_social     = true;
			}
		}
		if ( $has_social ) {
			update_user_meta( $user_id, 'gmm_teacher_social', $social );
		}

		if ( class_exists( 'GMM_Teacher_Dashboard' ) ) {
			GMM_Teacher_Dashboard::flush_cache( $user_id );
		}

		/**
		 * Fires after a teacher profile is updated.
		 *
		 * @param int                  $user_id User ID.
		 * @param array<string, mixed> $mapped  Normalized fields.
		 */
		do_action( 'gmm_teacher_profile_updated', $user_id, $mapped );

		return true;
	}

	/**
	 * Update WordPress + teacher email safely.
	 *
	 * @param int    $user_id User ID.
	 * @param string $email   New email.
	 * @return true|WP_Error
	 */
	public static function update_email( $user_id, $email ) {
		$user_id = absint( $user_id );
		$email   = sanitize_email( (string) $email );

		if ( ! self::user_can_manage( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot edit this teacher profile.', 'gospel-music-mastery' ) );
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
	 * Upload / set profile image as attachment ID.
	 *
	 * @param int    $user_id  User ID.
	 * @param string $file_key $_FILES key.
	 * @return int|WP_Error Attachment ID.
	 */
	public static function update_profile_image( $user_id = 0, $file_key = 'profile_image' ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! self::user_can_manage( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot edit this teacher profile.', 'gospel-music-mastery' ) );
		}

		$teacher_id = class_exists( 'GMM_Teacher' ) ? GMM_Teacher::get_teacher_id( $user_id ) : 0;
		if ( ! $teacher_id ) {
			return new WP_Error( 'gmm_no_profile', __( 'Teacher profile not found.', 'gospel-music-mastery' ) );
		}

		if ( empty( $_FILES[ $file_key ]['tmp_name'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return new WP_Error( 'gmm_no_file', __( 'No file was uploaded.', 'gospel-music-mastery' ) );
		}

		if ( class_exists( 'GMM_Media' ) ) {
			$result = GMM_Media::upload_image( $file_key, 'teacher_profile', $teacher_id, '' );
			if ( is_wp_error( $result ) ) {
				return $result;
			}
			$attachment_id = isset( $result['id'] ) ? absint( $result['id'] ) : 0;
		} else {
			$attachment_id = self::handle_direct_image_upload( $file_key, $user_id );
			if ( is_wp_error( $attachment_id ) ) {
				return $attachment_id;
			}
			GMM_Teacher::update_profile( $user_id, array( 'profile_image' => (string) $attachment_id ) );
		}

		if ( ! $attachment_id ) {
			return new WP_Error( 'gmm_upload_failed', __( 'Image upload failed.', 'gospel-music-mastery' ) );
		}

		do_action( 'gmm_teacher_profile_updated', $user_id, array( 'profile_image' => $attachment_id ) );

		return $attachment_id;
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

		// Keep the user logged in after password change.
		wp_set_current_user( $user_id );
		wp_set_auth_cookie( $user_id, true, is_ssl() );

		do_action( 'gmm_teacher_password_updated', $user_id );

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
		$row     = class_exists( 'GMM_Teacher' ) ? GMM_Teacher::get_profile( $user_id ) : null;
		$row     = is_array( $row ) ? $row : array();

		$items = array(
			array(
				'key'   => 'image',
				'label' => __( 'Profile Image', 'gospel-music-mastery' ),
				'done'  => ! empty( $row['profile_image'] ),
			),
			array(
				'key'   => 'bio',
				'label' => __( 'Bio', 'gospel-music-mastery' ),
				'done'  => ! empty( $row['bio'] ),
			),
			array(
				'key'   => 'specialization',
				'label' => __( 'Specialization', 'gospel-music-mastery' ),
				'done'  => ! empty( $row['specialization'] ),
			),
			array(
				'key'   => 'experience',
				'label' => __( 'Experience', 'gospel-music-mastery' ),
				'done'  => ! empty( $row['experience'] ),
			),
			array(
				'key'   => 'phone',
				'label' => __( 'Phone', 'gospel-music-mastery' ),
				'done'  => ! empty( $row['phone'] ),
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
	 * Public-safe teacher profile (listing / detail pages).
	 *
	 * @param int $teacher_id gmm_teachers.id OR WP user_id when $by_user true.
	 * @param bool $by_user   Whether ID is WP user ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_public_profile( $teacher_id, $by_user = false ) {
		$teacher_id = absint( $teacher_id );
		if ( ! $teacher_id ) {
			return null;
		}

		global $wpdb;
		$table = GMM_Database::table( 'teachers' );

		if ( $by_user ) {
			$row = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d LIMIT 1", $teacher_id ),
				ARRAY_A
			);
		} else {
			$row = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $teacher_id ),
				ARRAY_A
			);
		}

		if ( ! is_array( $row ) ) {
			return null;
		}

		$status = sanitize_key( (string) $row['status'] );
		if ( ! in_array( $status, array( 'active', 'approved' ), true ) ) {
			return null;
		}

		$image = '';
		if ( ! empty( $row['profile_image'] ) && function_exists( 'gmm_get_media_url' ) ) {
			$image = gmm_get_media_url( $row['profile_image'], 'medium' );
		}

		$first = isset( $row['first_name'] ) ? (string) $row['first_name'] : '';
		$last  = isset( $row['last_name'] ) ? (string) $row['last_name'] : '';

		return array(
			'id'             => absint( $row['id'] ),
			'user_id'        => absint( $row['user_id'] ),
			'name'           => trim( $first . ' ' . $last ),
			'first_name'     => $first,
			'last_name'      => $last,
			'specialization' => isset( $row['specialization'] ) ? (string) $row['specialization'] : '',
			'experience'     => isset( $row['experience'] ) ? (string) $row['experience'] : '',
			'bio'            => isset( $row['bio'] ) ? wp_kses_post( $row['bio'] ) : '',
			'rating'         => isset( $row['rating'] ) ? (float) $row['rating'] : 0.0,
			'image_url'      => $image,
			'status'         => $status,
		);
	}

	/**
	 * Wire form action + nonce without editing template markup on disk.
	 *
	 * @param string $html HTML.
	 * @param string $tag  Shortcode.
	 * @return string
	 */
	public function enhance_profile_form_html( $html, $tag ) {
		if ( 'gmm_teacher_profile' !== $tag || ! is_string( $html ) || '' === $html ) {
			return $html;
		}

		$action = esc_url( admin_url( 'admin-post.php' ) );
		$nonce  = wp_create_nonce( self::NONCE_ACTION );
		$hidden = '<input type="hidden" name="action" value="gmm_teacher_profile_update" />'
			. '<input type="hidden" name="' . esc_attr( self::NONCE_FIELD ) . '" value="' . esc_attr( $nonce ) . '" />'
			. '<input type="hidden" name="gmm_redirect" value="' . esc_attr( self::current_url() ) . '" />'
			. '<input type="file" id="profile-photo" name="profile_image" accept="image/jpeg,image/png,image/webp,image/jpg" class="gmm-sr-only" style="position:absolute;left:-9999px;width:1px;height:1px;opacity:0;" tabindex="-1" aria-hidden="true" />';

		$html = preg_replace(
			'/(<form\b[^>]*\bid=["\']teacher-profile-form["\'][^>]*\baction=["\'])([^"\']*)(["\'])/i',
			'$1' . $action . '$3',
			$html,
			1
		);

		// Ensure multipart for image upload.
		if ( ! preg_match( '/id=["\']teacher-profile-form["\'][^>]*enctype=/i', $html ) ) {
			$html = preg_replace(
				'/(<form\b[^>]*\bid=["\']teacher-profile-form["\'])/i',
				'$1 enctype="multipart/form-data"',
				$html,
				1
			);
		}

		$html = preg_replace(
			'/(<form\b[^>]*\bid=["\']teacher-profile-form["\'][^>]*>)/i',
			'$1' . $hidden,
			is_string( $html ) ? $html : '',
			1
		);

		// Real success copy.
		$html = preg_replace(
			'/(id=["\']profile-success["\'][^>]*>\s*(?:<i[^>]*>.*?<\/i>\s*)?<span>)(.*?)(<\/span>)/is',
			'$1' . esc_html__( 'Profile changes saved successfully.', 'gospel-music-mastery' ) . '$3',
			is_string( $html ) ? $html : '',
			1
		);

		return is_string( $html ) ? $html : '';
	}

	/**
	 * admin-post profile save.
	 *
	 * @return void
	 */
	public function handle_profile_update() {
		check_admin_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$back = isset( $_POST['gmm_redirect'] ) ? esc_url_raw( wp_unslash( $_POST['gmm_redirect'] ) ) : home_url( '/' );
		$user_id = get_current_user_id();

		if ( ! self::user_can_manage( $user_id ) ) {
			self::redirect_flash( $back, 'error', __( 'Your account is waiting for approval.', 'gospel-music-mastery' ) );
		}

		$data = wp_unslash( $_POST );

		// Optional image upload in same request.
		if ( ! empty( $_FILES['profile_image']['tmp_name'] ) ) {
			$img = self::update_profile_image( $user_id, 'profile_image' );
			if ( is_wp_error( $img ) ) {
				self::redirect_flash( $back, 'error', $img->get_error_message() );
			}
		}

		$result = self::update_profile( $user_id, $data );
		if ( is_wp_error( $result ) ) {
			self::redirect_flash( $back, 'error', $result->get_error_message() );
		}

		self::redirect_flash( $back, 'success', __( 'Profile changes saved successfully.', 'gospel-music-mastery' ) );
	}

	/**
	 * AJAX profile update.
	 *
	 * @return void
	 */
	public function ajax_profile_update() {
		check_ajax_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$user_id = get_current_user_id();
		if ( ! self::user_can_manage( $user_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Your account is waiting for approval.', 'gospel-music-mastery' ) ), 403 );
		}

		$result = self::update_profile( $user_id, wp_unslash( $_POST ) );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'code'    => $result->get_error_code(),
					'message' => $result->get_error_message(),
				),
				400
			);
		}

		wp_send_json_success(
			array(
				'message'    => __( 'Profile changes saved successfully.', 'gospel-music-mastery' ),
				'profile'    => self::get_profile_data( $user_id ),
				'completion' => self::get_profile_completion( $user_id ),
			)
		);
	}

	/**
	 * AJAX profile image upload.
	 *
	 * @return void
	 */
	public function ajax_profile_image() {
		check_ajax_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$user_id = get_current_user_id();
		if ( ! self::user_can_manage( $user_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Your account is waiting for approval.', 'gospel-music-mastery' ) ), 403 );
		}

		$file_key = ! empty( $_FILES['profile_image'] ) ? 'profile_image' : ( ! empty( $_FILES['profile_photo'] ) ? 'profile_photo' : 'profile_image' );
		$result   = self::update_profile_image( $user_id, $file_key );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'code'    => $result->get_error_code(),
					'message' => $result->get_error_message(),
				),
				400
			);
		}

		$url = function_exists( 'gmm_get_media_url' ) ? gmm_get_media_url( $result, 'medium' ) : wp_get_attachment_image_url( $result, 'medium' );

		wp_send_json_success(
			array(
				'message'       => __( 'Profile image updated.', 'gospel-music-mastery' ),
				'attachment_id' => (int) $result,
				'url'           => $url ? $url : '',
				'completion'    => self::get_profile_completion( $user_id ),
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

		$user_id = get_current_user_id();
		if ( ! self::user_can_manage( $user_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Your account is waiting for approval.', 'gospel-music-mastery' ) ), 403 );
		}

		$current = isset( $_POST['current_password'] ) ? (string) wp_unslash( $_POST['current_password'] ) : '';
		$new     = isset( $_POST['new_password'] ) ? (string) wp_unslash( $_POST['new_password'] ) : '';
		$confirm = isset( $_POST['confirm_password'] ) ? (string) wp_unslash( $_POST['confirm_password'] ) : '';

		$result = self::update_password( $user_id, $current, $new, $confirm );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error(
				array(
					'code'    => $result->get_error_code(),
					'message' => $result->get_error_message(),
				),
				400
			);
		}

		wp_send_json_success(
			array(
				'message' => __( 'Password updated successfully.', 'gospel-music-mastery' ),
			)
		);
	}

	/**
	 * Localize profile AJAX script.
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
		if ( ! has_shortcode( $content, 'gmm_teacher_profile' ) && false === strpos( $content, 'gmm_teacher_profile' ) ) {
			return;
		}

		$version = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';
		wp_enqueue_script(
			'gmm-teacher-profile',
			GMM_URL . 'assets/js/gmm-teacher-profile.js',
			array( 'gmm-core-script' ),
			$version,
			true
		);

		wp_localize_script(
			'gmm-teacher-profile',
			'GMM_TEACHER_PROFILE',
			array(
				'nonce'   => wp_create_nonce( self::NONCE_ACTION ),
				'nonceField' => self::NONCE_FIELD,
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'actions' => array(
					'update'   => 'gmm_teacher_profile_update',
					'image'    => 'gmm_teacher_profile_image',
					'password' => 'gmm_teacher_password_update',
				),
				'i18n'    => array(
					'saved'  => __( 'Profile changes saved successfully.', 'gospel-music-mastery' ),
					'image'  => __( 'Profile image updated.', 'gospel-music-mastery' ),
					'error'  => __( 'Could not save profile. Please try again.', 'gospel-music-mastery' ),
				),
			)
		);
	}

	/**
	 * Map form field names to DB fields.
	 *
	 * @param array<string, mixed> $data Raw.
	 * @return array<string, mixed>
	 */
	private static function normalize_incoming_fields( $data ) {
		$out = array();

		if ( isset( $data['first_name'] ) ) {
			$out['first_name'] = sanitize_text_field( (string) $data['first_name'] );
		}
		if ( isset( $data['last_name'] ) ) {
			$out['last_name'] = sanitize_text_field( (string) $data['last_name'] );
		}
		if ( isset( $data['phone'] ) ) {
			$out['phone'] = sanitize_text_field( (string) $data['phone'] );
		}
		if ( isset( $data['email'] ) ) {
			$out['email'] = sanitize_email( (string) $data['email'] );
		}

		// Skill select → specialization.
		if ( isset( $data['skill'] ) && '' !== $data['skill'] ) {
			$out['specialization'] = sanitize_text_field( (string) $data['skill'] );
		} elseif ( isset( $data['specialization'] ) ) {
			$out['specialization'] = sanitize_text_field( (string) $data['specialization'] );
		}

		if ( isset( $data['experience'] ) ) {
			$out['experience'] = sanitize_text_field( (string) $data['experience'] );
		}

		if ( isset( $data['biography'] ) ) {
			$out['bio'] = sanitize_textarea_field( (string) $data['biography'] );
		} elseif ( isset( $data['bio'] ) ) {
			$out['bio'] = sanitize_textarea_field( (string) $data['bio'] );
		}

		if ( isset( $data['display_name'] ) ) {
			$out['display_name'] = sanitize_text_field( (string) $data['display_name'] );
		}
		if ( isset( $data['timezone'] ) ) {
			$out['timezone'] = sanitize_text_field( (string) $data['timezone'] );
		}

		foreach ( array( 'facebook', 'instagram', 'youtube', 'website' ) as $key ) {
			if ( isset( $data[ $key ] ) ) {
				$out[ $key ] = esc_url_raw( (string) $data[ $key ] );
			}
		}

		if ( isset( $data['profile_image'] ) && is_numeric( $data['profile_image'] ) ) {
			$out['profile_image'] = absint( $data['profile_image'] );
		}

		return $out;
	}

	/**
	 * Fallback image upload when media class linking fails.
	 *
	 * @param string $file_key File key.
	 * @param int    $user_id  User ID.
	 * @return int|WP_Error
	 */
	private static function handle_direct_image_upload( $file_key, $user_id ) {
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$file = isset( $_FILES[ $file_key ] ) ? $_FILES[ $file_key ] : null; // phpcs:ignore WordPress.Security.NonceVerification.Missing,WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		if ( ! $file || empty( $file['tmp_name'] ) ) {
			return new WP_Error( 'gmm_no_file', __( 'No file was uploaded.', 'gospel-music-mastery' ) );
		}

		$size = isset( $file['size'] ) ? (int) $file['size'] : 0;
		if ( $size > 5 * MB_IN_BYTES ) {
			return new WP_Error( 'gmm_file_size', __( 'Image must be 5MB or smaller.', 'gospel-music-mastery' ) );
		}

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

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		$attachment_id = absint( $attachment_id );
		wp_update_post(
			array(
				'ID'          => $attachment_id,
				'post_author' => absint( $user_id ),
			)
		);
		update_post_meta( $attachment_id, '_gmm_media', 1 );

		return $attachment_id;
	}

	/**
	 * Frozen skill select options (for selected state).
	 *
	 * @return array<int, string>
	 */
	private static function skill_options() {
		return array(
			'Gospel Piano Instructor',
			'Worship Piano Instructor',
			'Keyboard Instructor',
			'Organ Instructor',
			'Vocal Coach',
			'Gospel Vocal Instructor',
			'Worship Vocal Coach',
			'Choir Director',
			'Backing Vocals Coach',
			'Guitar Instructor',
			'Acoustic Guitar Instructor',
			'Electric Guitar Instructor',
			'Bass Guitar Instructor',
			'Drums Instructor',
			'Percussion Instructor',
			'Worship Leader',
			'Worship Leadership Coach',
			'Music Director',
			'Band Director',
			'Music Theory Instructor',
			'Songwriting Coach',
			'Music Production Instructor',
			'Audio Engineering Instructor',
			'Violin Instructor',
			'Saxophone Instructor',
			'Trumpet Instructor',
			'Flute Instructor',
			'General Music Teacher',
		);
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
	 * @param string $url  Redirect.
	 * @param string $type success|error.
	 * @param string $msg  Message.
	 * @return void
	 */
	private static function redirect_flash( $url, $type, $msg ) {
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key = 'gmm_tprof_' . sanitize_key( $type ) . '_' . substr( md5( $ip . wp_salt() ), 0, 12 );
		set_transient( $key, sanitize_text_field( $msg ), 60 );
		wp_safe_redirect( add_query_arg( 'gmm_profile', sanitize_key( $type ), $url ? $url : home_url( '/' ) ) );
		exit;
	}

	/**
	 * @param string $type success|error.
	 * @return string
	 */
	private static function consume_flash( $type ) {
		if ( empty( $_GET['gmm_profile'] ) || sanitize_key( wp_unslash( $_GET['gmm_profile'] ) ) !== $type ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return '';
		}
		$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '';
		$key = 'gmm_tprof_' . sanitize_key( $type ) . '_' . substr( md5( $ip . wp_salt() ), 0, 12 );
		$msg = get_transient( $key );
		delete_transient( $key );
		return is_string( $msg ) ? $msg : '';
	}
}
