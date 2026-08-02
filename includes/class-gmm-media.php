<?php
/**
 * Media upload system for Gospel Music Mastery.
 *
 * Uses the WordPress Media Library only (no custom file storage).
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Media
 *
 * Image/video uploads, validation, attachment linking, and safe delete.
 */
class GMM_Media {

	const NONCE_ACTION = 'gmm_nonce';

	const IMAGE_MAX_BYTES = 5242880;  // 5MB.
	const VIDEO_MAX_BYTES = 52428800; // 50MB.

	/**
	 * Upload contexts → table / column mapping.
	 *
	 * @var array<string, array<string, string>>
	 */
	const CONTEXTS = array(
		'teacher_profile' => array(
			'table'  => 'teachers',
			'column' => 'profile_image',
			'type'   => 'image',
		),
		'student_profile' => array(
			'table'  => 'students',
			'column' => 'profile_image',
			'type'   => 'image',
		),
		'class_image'     => array(
			'table'  => 'classes',
			'column' => 'image',
			'type'   => 'image',
		),
		'class_video'     => array(
			'table'  => 'classes',
			'column' => 'video',
			'type'   => 'video',
		),
		'program_image'   => array(
			'table'  => 'programs',
			'column' => 'image',
			'type'   => 'image',
		),
		'blog_image'      => array(
			'table'  => 'blog_posts',
			'column' => 'image',
			'type'   => 'image',
		),
		'teacher_video'   => array(
			'table'  => 'teachers',
			'column' => 'intro_video',
			'type'   => 'video',
		),
	);

	/**
	 * Allowed image extensions.
	 *
	 * @var array<int, string>
	 */
	const IMAGE_EXTS = array( 'jpg', 'jpeg', 'png', 'webp' );

	/**
	 * Allowed image mime types.
	 *
	 * @var array<string, string>
	 */
	const IMAGE_MIMES = array(
		'jpg|jpeg|jpe' => 'image/jpeg',
		'png'          => 'image/png',
		'webp'         => 'image/webp',
	);

	/**
	 * Allowed video extensions.
	 *
	 * @var array<int, string>
	 */
	const VIDEO_EXTS = array( 'mp4', 'webm', 'mov' );

	/**
	 * Allowed video mime types.
	 *
	 * @var array<string, string>
	 */
	const VIDEO_MIMES = array(
		'mp4|m4v' => 'video/mp4',
		'webm'    => 'video/webm',
		'mov|qt'  => 'video/quicktime',
	);

	/**
	 * Register AJAX hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_action( 'wp_ajax_gmm_upload_image', $instance, 'ajax_upload_image' );
		$loader->add_action( 'wp_ajax_gmm_upload_video', $instance, 'ajax_upload_video' );
		$loader->add_action( 'wp_ajax_gmm_delete_media', $instance, 'ajax_delete_media' );
	}

	/**
	 * AJAX: upload image.
	 *
	 * @return void
	 */
	public function ajax_upload_image() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'gospel-music-mastery' ) ), 403 );
		}

		$context   = sanitize_key( (string) self::request_value( 'context' ) );
		$object_id = absint( self::request_value( 'object_id' ) );
		$file_key  = sanitize_key( (string) self::request_value( 'file_key' ) );
		if ( ! $file_key ) {
			$file_key = 'gmm_file';
		}

		$result = self::upload_image( $file_key, $context, $object_id, '' );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		wp_send_json_success(
			array(
				'message'    => __( 'Image uploaded.', 'gospel-music-mastery' ),
				'attachment' => $result,
			)
		);
	}

	/**
	 * AJAX: upload video.
	 *
	 * @return void
	 */
	public function ajax_upload_video() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'gospel-music-mastery' ) ), 403 );
		}

		$context   = sanitize_key( (string) self::request_value( 'context' ) );
		$object_id = absint( self::request_value( 'object_id' ) );
		$file_key  = sanitize_key( (string) self::request_value( 'file_key' ) );
		if ( ! $file_key ) {
			$file_key = 'gmm_file';
		}

		$result = self::upload_video( $file_key, $context, $object_id, '' );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		wp_send_json_success(
			array(
				'message'    => __( 'Video uploaded.', 'gospel-music-mastery' ),
				'attachment' => $result,
			)
		);
	}

	/**
	 * AJAX: delete media reference / attachment.
	 *
	 * @return void
	 */
	public function ajax_delete_media() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );

		if ( ! is_user_logged_in() ) {
			wp_send_json_error( array( 'message' => __( 'You must be logged in.', 'gospel-music-mastery' ) ), 403 );
		}

		$attachment_id = absint( self::request_value( 'attachment_id' ) );
		$context       = sanitize_key( (string) self::request_value( 'context' ) );
		$object_id     = absint( self::request_value( 'object_id' ) );

		$result = self::delete_media( $attachment_id, $context, $object_id, '' );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Media removed.', 'gospel-music-mastery' ),
				'data'    => $result,
			)
		);
	}

	/**
	 * Upload an image into the Media Library and optionally link it.
	 *
	 * @param string $file_key  $_FILES key.
	 * @param string $context   Context key.
	 * @param int    $object_id Related row ID (teacher/student/class/program/blog).
	 * @param string $nonce     Optional nonce (empty when already verified via AJAX).
	 * @return array<string, mixed>|WP_Error Attachment preview payload.
	 */
	public static function upload_image( $file_key, $context, $object_id = 0, $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		$auth = self::authorize_upload( $context, $object_id, 'image' );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		$validated = self::validate_upload_file( $file_key, 'image' );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$attachment_id = self::handle_media_upload( $file_key, self::IMAGE_MIMES );
		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		$linked = self::link_attachment( $context, $object_id, $attachment_id );
		if ( is_wp_error( $linked ) ) {
			return $linked;
		}

		/**
		 * Fires after a GMM image upload.
		 *
		 * @since 1.0.0
		 * @param int    $attachment_id Attachment ID.
		 * @param string $context       Context.
		 * @param int    $object_id     Object ID.
		 */
		do_action( 'gmm_image_uploaded', $attachment_id, $context, $object_id );

		return self::get_attachment_preview( $attachment_id );
	}

	/**
	 * Upload a video into the Media Library and optionally link it.
	 *
	 * @param string $file_key  $_FILES key.
	 * @param string $context   Context key.
	 * @param int    $object_id Related row ID.
	 * @param string $nonce     Optional nonce.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function upload_video( $file_key, $context, $object_id = 0, $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		$auth = self::authorize_upload( $context, $object_id, 'video' );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		$validated = self::validate_upload_file( $file_key, 'video' );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$attachment_id = self::handle_media_upload( $file_key, self::VIDEO_MIMES );
		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		$linked = self::link_attachment( $context, $object_id, $attachment_id );
		if ( is_wp_error( $linked ) ) {
			return $linked;
		}

		/**
		 * Fires after a GMM video upload.
		 *
		 * @since 1.0.0
		 * @param int    $attachment_id Attachment ID.
		 * @param string $context       Context.
		 * @param int    $object_id     Object ID.
		 */
		do_action( 'gmm_video_uploaded', $attachment_id, $context, $object_id );

		return self::get_attachment_preview( $attachment_id );
	}

	/**
	 * Delete media reference; delete attachment only if owned and not shared.
	 *
	 * @param int    $attachment_id Attachment ID.
	 * @param string $context       Context.
	 * @param int    $object_id     Object ID.
	 * @param string $nonce         Optional nonce.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function delete_media( $attachment_id, $context = '', $object_id = 0, $nonce = '' ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		$attachment_id = absint( $attachment_id );
		$context       = sanitize_key( (string) $context );
		$object_id     = absint( $object_id );

		if ( ! $attachment_id || ! get_post( $attachment_id ) || 'attachment' !== get_post_type( $attachment_id ) ) {
			return new WP_Error( 'gmm_not_found', __( 'Media not found.', 'gospel-music-mastery' ) );
		}

		if ( $context ) {
			$auth = self::authorize_upload( $context, $object_id, isset( self::CONTEXTS[ $context ]['type'] ) ? self::CONTEXTS[ $context ]['type'] : 'image' );
			if ( is_wp_error( $auth ) ) {
				return $auth;
			}
			self::clear_reference( $context, $object_id, $attachment_id );
		} elseif ( ! self::user_owns_attachment( $attachment_id ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot delete this media.', 'gospel-music-mastery' ) );
		}

		$shared      = self::count_references( $attachment_id ) > 0;
		$deleted_file = false;

		if ( ! $shared && ( self::user_owns_attachment( $attachment_id ) || current_user_can( 'manage_options' ) ) ) {
			$deleted = wp_delete_attachment( $attachment_id, true );
			$deleted_file = (bool) $deleted;
		}

		/**
		 * Fires after GMM media delete handling.
		 *
		 * @since 1.0.0
		 * @param int  $attachment_id Attachment ID.
		 * @param bool $deleted_file  Whether file was removed.
		 * @param bool $shared        Whether references remain.
		 */
		do_action( 'gmm_media_deleted', $attachment_id, $deleted_file, $shared );

		return array(
			'attachment_id' => $attachment_id,
			'deleted_file'  => $deleted_file,
			'shared'        => $shared,
			'reference_cleared' => (bool) $context,
		);
	}

	/**
	 * Resolve stored value (attachment ID or legacy URL) to a URL.
	 *
	 * @param mixed  $stored Stored field value.
	 * @param string $size   Image size.
	 * @return string
	 */
	public static function get_media_url( $stored, $size = 'full' ) {
		$stored = is_string( $stored ) || is_numeric( $stored ) ? trim( (string) $stored ) : '';
		if ( '' === $stored ) {
			return '';
		}

		if ( ctype_digit( $stored ) ) {
			$id  = absint( $stored );
			$url = wp_get_attachment_image_url( $id, $size );
			if ( ! $url ) {
				$url = wp_get_attachment_url( $id );
			}
			return $url ? esc_url_raw( $url ) : '';
		}

		return esc_url_raw( $stored );
	}

	/**
	 * Attachment preview payload for frontend (no design change).
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_attachment_preview( $attachment_id ) {
		$attachment_id = absint( $attachment_id );
		if ( ! $attachment_id || ! get_post( $attachment_id ) ) {
			return null;
		}

		$mime  = (string) get_post_mime_type( $attachment_id );
		$type  = ( 0 === strpos( $mime, 'video/' ) ) ? 'video' : 'image';
		$url   = wp_get_attachment_url( $attachment_id );
		$thumb = '';

		if ( 'image' === $type ) {
			$thumb = wp_get_attachment_image_url( $attachment_id, 'medium' );
			if ( ! $thumb ) {
				$thumb = $url;
			}
		} else {
			$thumb = wp_get_attachment_image_url( $attachment_id, 'medium' );
		}

		return array(
			'id'        => $attachment_id,
			'url'       => $url ? esc_url_raw( $url ) : '',
			'thumb'     => $thumb ? esc_url_raw( $thumb ) : '',
			'mime'      => sanitize_mime_type( $mime ),
			'type'      => $type,
			'filename'  => sanitize_file_name( basename( get_attached_file( $attachment_id ) ? get_attached_file( $attachment_id ) : '' ) ),
			'title'     => sanitize_text_field( get_the_title( $attachment_id ) ),
		);
	}

	/**
	 * Config for frontend preview / upload preparation (localize later).
	 *
	 * @return array<string, mixed>
	 */
	public static function get_frontend_config() {
		return array(
			'image_max_bytes' => self::IMAGE_MAX_BYTES,
			'video_max_bytes' => self::VIDEO_MAX_BYTES,
			'image_exts'      => self::IMAGE_EXTS,
			'video_exts'      => self::VIDEO_EXTS,
			'actions'         => array(
				'upload_image' => 'gmm_upload_image',
				'upload_video' => 'gmm_upload_video',
				'delete_media' => 'gmm_delete_media',
			),
			'contexts'        => array_keys( self::CONTEXTS ),
		);
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
	 * Validate $_FILES entry before upload.
	 *
	 * @param string $file_key File key.
	 * @param string $type     image|video.
	 * @return true|WP_Error
	 */
	private static function validate_upload_file( $file_key, $type ) {
		$file_key = sanitize_key( (string) $file_key );

		if ( ! $file_key || empty( $_FILES[ $file_key ] ) || ! is_array( $_FILES[ $file_key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
			return new WP_Error( 'gmm_no_file', __( 'No file was uploaded.', 'gospel-music-mastery' ) );
		}

		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		$file = $_FILES[ $file_key ];

		if ( ! empty( $file['error'] ) && UPLOAD_ERR_OK !== (int) $file['error'] ) {
			return new WP_Error( 'gmm_upload_error', self::upload_error_message( (int) $file['error'] ) );
		}

		$name = isset( $file['name'] ) ? sanitize_file_name( (string) $file['name'] ) : '';
		$size = isset( $file['size'] ) ? absint( $file['size'] ) : 0;
		$tmp  = isset( $file['tmp_name'] ) ? (string) $file['tmp_name'] : '';

		if ( ! $name || ! $tmp || ! is_uploaded_file( $tmp ) ) {
			return new WP_Error( 'gmm_invalid_file', __( 'Invalid upload.', 'gospel-music-mastery' ) );
		}

		$check = wp_check_filetype_and_ext( $tmp, $name, 'image' === $type ? self::IMAGE_MIMES : self::VIDEO_MIMES );
		$ext   = isset( $check['ext'] ) ? strtolower( (string) $check['ext'] ) : '';
		$mime  = isset( $check['type'] ) ? (string) $check['type'] : '';

		$allowed_exts = 'image' === $type ? self::IMAGE_EXTS : self::VIDEO_EXTS;
		$max          = 'image' === $type ? self::IMAGE_MAX_BYTES : self::VIDEO_MAX_BYTES;

		if ( ! $ext || ! in_array( $ext, $allowed_exts, true ) ) {
			return new WP_Error( 'gmm_filetype', __( 'File type is not allowed.', 'gospel-music-mastery' ) );
		}

		if ( ! $mime ) {
			return new WP_Error( 'gmm_mime', __( 'Could not verify file type.', 'gospel-music-mastery' ) );
		}

		if ( $size < 1 || $size > $max ) {
			return new WP_Error(
				'gmm_filesize',
				sprintf(
					/* translators: %s: max size label */
					__( 'File exceeds the maximum size of %s.', 'gospel-music-mastery' ),
					size_format( $max )
				)
			);
		}

		return true;
	}

	/**
	 * Run media_handle_upload with restricted mimes.
	 *
	 * @param string                $file_key File key.
	 * @param array<string, string> $mimes    Allowed mimes.
	 * @return int|WP_Error Attachment ID.
	 */
	private static function handle_media_upload( $file_key, $mimes ) {
		if ( ! function_exists( 'media_handle_upload' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$overrides = array(
			'test_form' => false,
			'mimes'     => $mimes,
		);

		/**
		 * Temporarily raise upload limit awareness for validation already done.
		 * WordPress still enforces server limits.
		 */
		$attachment_id = media_handle_upload( $file_key, 0, array(), $overrides );

		if ( is_wp_error( $attachment_id ) ) {
			return $attachment_id;
		}

		$attachment_id = absint( $attachment_id );
		if ( ! $attachment_id ) {
			return new WP_Error( 'gmm_upload_failed', __( 'Upload failed.', 'gospel-music-mastery' ) );
		}

		update_post_meta( $attachment_id, '_gmm_media', 1 );

		return $attachment_id;
	}

	/**
	 * Permission check for a context.
	 *
	 * @param string $context   Context.
	 * @param int    $object_id Object ID.
	 * @param string $type      image|video.
	 * @return true|WP_Error
	 */
	private static function authorize_upload( $context, $object_id, $type ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'gmm_not_logged_in', __( 'You must be logged in.', 'gospel-music-mastery' ) );
		}

		$context   = sanitize_key( (string) $context );
		$object_id = absint( $object_id );
		$type      = sanitize_key( (string) $type );

		if ( ! isset( self::CONTEXTS[ $context ] ) ) {
			return new WP_Error( 'gmm_invalid_context', __( 'Invalid upload context.', 'gospel-music-mastery' ) );
		}

		if ( self::CONTEXTS[ $context ]['type'] !== $type ) {
			return new WP_Error( 'gmm_type_mismatch', __( 'Media type does not match this context.', 'gospel-music-mastery' ) );
		}

		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}

		$user_id = get_current_user_id();

		switch ( $context ) {
			case 'teacher_profile':
			case 'teacher_video':
				if ( ! gmm_is_teacher( $user_id ) ) {
					return new WP_Error( 'gmm_forbidden', __( 'Teacher access required.', 'gospel-music-mastery' ) );
				}
				$teacher_id = class_exists( 'GMM_Teacher' ) ? GMM_Teacher::get_teacher_id( $user_id ) : 0;
				if ( $object_id && $teacher_id && (int) $object_id !== (int) $teacher_id ) {
					return new WP_Error( 'gmm_forbidden', __( 'You can only update your own profile media.', 'gospel-music-mastery' ) );
				}
				if ( ! $object_id && ! $teacher_id ) {
					return new WP_Error( 'gmm_no_profile', __( 'Teacher profile not found.', 'gospel-music-mastery' ) );
				}
				return true;

			case 'student_profile':
				if ( ! gmm_is_student( $user_id ) ) {
					return new WP_Error( 'gmm_forbidden', __( 'Student access required.', 'gospel-music-mastery' ) );
				}
				$student_id = class_exists( 'GMM_Student' ) ? GMM_Student::get_student_id( $user_id ) : 0;
				if ( $object_id && $student_id && (int) $object_id !== (int) $student_id ) {
					return new WP_Error( 'gmm_forbidden', __( 'You can only update your own profile media.', 'gospel-music-mastery' ) );
				}
				if ( ! $object_id && ! $student_id ) {
					return new WP_Error( 'gmm_no_profile', __( 'Student profile not found.', 'gospel-music-mastery' ) );
				}
				return true;

			case 'class_image':
			case 'class_video':
				if ( ! $object_id ) {
					return new WP_Error( 'gmm_invalid', __( 'Class ID is required.', 'gospel-music-mastery' ) );
				}
				if ( ! self::user_owns_class( $object_id, $user_id ) ) {
					return new WP_Error( 'gmm_forbidden', __( 'You cannot modify this class media.', 'gospel-music-mastery' ) );
				}
				return true;

			case 'program_image':
			case 'blog_image':
				if ( ! current_user_can( 'manage_options' ) ) {
					return new WP_Error( 'gmm_forbidden', __( 'Admin access required.', 'gospel-music-mastery' ) );
				}
				return true;

			default:
				return new WP_Error( 'gmm_forbidden', __( 'Permission denied.', 'gospel-music-mastery' ) );
		}
	}

	/**
	 * Save attachment ID onto the related row.
	 *
	 * @param string $context       Context.
	 * @param int    $object_id     Object ID.
	 * @param int    $attachment_id Attachment ID.
	 * @return true|WP_Error
	 */
	private static function link_attachment( $context, $object_id, $attachment_id ) {
		$context       = sanitize_key( $context );
		$attachment_id = absint( $attachment_id );

		if ( ! isset( self::CONTEXTS[ $context ] ) || ! $attachment_id ) {
			return new WP_Error( 'gmm_invalid', __( 'Could not link media.', 'gospel-music-mastery' ) );
		}

		$object_id = self::resolve_object_id( $context, $object_id );
		if ( ! $object_id && in_array( $context, array( 'class_image', 'class_video', 'program_image', 'blog_image' ), true ) ) {
			return new WP_Error( 'gmm_invalid', __( 'Target record is required.', 'gospel-music-mastery' ) );
		}

		// Profile contexts can resolve from current user when object_id omitted.
		if ( ! $object_id ) {
			return new WP_Error( 'gmm_invalid', __( 'Target profile not found.', 'gospel-music-mastery' ) );
		}

		$map    = self::CONTEXTS[ $context ];
		$table  = GMM_Database::table( $map['table'] );
		$column = preg_replace( '/[^a-z0-9_]/i', '', $map['column'] );

		// Clear previous reference first (does not delete shared files).
		$previous = self::get_stored_attachment_id( $table, $column, $object_id );
		if ( $previous && (int) $previous !== (int) $attachment_id ) {
			// Leave previous file if shared; only overwrite reference.
		}

		global $wpdb;
		$data = array( $column => (string) $attachment_id );
		$fmt  = array( '%s' );

		if ( in_array( $map['table'], array( 'teachers', 'students', 'classes', 'programs', 'blog_posts' ), true ) ) {
			$data['updated_at'] = current_time( 'mysql' );
			$fmt[]              = '%s';
		}

		$updated = $wpdb->update( $table, $data, array( 'id' => $object_id ), $fmt, array( '%d' ) );
		if ( false === $updated ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not save media reference.', 'gospel-music-mastery' ) );
		}

		update_post_meta( $attachment_id, '_gmm_context', $context );
		update_post_meta( $attachment_id, '_gmm_object_id', $object_id );

		return true;
	}

	/**
	 * Resolve object ID for profile contexts.
	 *
	 * @param string $context   Context.
	 * @param int    $object_id Provided ID.
	 * @return int
	 */
	private static function resolve_object_id( $context, $object_id ) {
		$object_id = absint( $object_id );
		if ( $object_id ) {
			return $object_id;
		}

		$user_id = get_current_user_id();
		if ( 'teacher_profile' === $context || 'teacher_video' === $context ) {
			return class_exists( 'GMM_Teacher' ) ? absint( GMM_Teacher::get_teacher_id( $user_id ) ) : 0;
		}
		if ( 'student_profile' === $context ) {
			return class_exists( 'GMM_Student' ) ? absint( GMM_Student::get_student_id( $user_id ) ) : 0;
		}
		return 0;
	}

	/**
	 * Clear a media reference if it matches the attachment.
	 *
	 * @param string $context       Context.
	 * @param int    $object_id     Object ID.
	 * @param int    $attachment_id Attachment ID.
	 * @return void
	 */
	private static function clear_reference( $context, $object_id, $attachment_id ) {
		if ( ! isset( self::CONTEXTS[ $context ] ) ) {
			return;
		}

		$object_id = self::resolve_object_id( $context, $object_id );
		if ( ! $object_id ) {
			return;
		}

		$map    = self::CONTEXTS[ $context ];
		$table  = GMM_Database::table( $map['table'] );
		$column = preg_replace( '/[^a-z0-9_]/i', '', $map['column'] );
		$stored = self::get_stored_attachment_id( $table, $column, $object_id );

		if ( (int) $stored !== (int) $attachment_id ) {
			return;
		}

		global $wpdb;
		$data = array( $column => '' );
		$fmt  = array( '%s' );
		if ( in_array( $map['table'], array( 'teachers', 'students', 'classes', 'programs', 'blog_posts' ), true ) ) {
			$data['updated_at'] = current_time( 'mysql' );
			$fmt[]              = '%s';
		}
		$wpdb->update( $table, $data, array( 'id' => $object_id ), $fmt, array( '%d' ) );
	}

	/**
	 * Count GMM table references to an attachment ID.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return int
	 */
	private static function count_references( $attachment_id ) {
		$attachment_id = absint( $attachment_id );
		if ( ! $attachment_id ) {
			return 0;
		}

		$id = (string) $attachment_id;
		global $wpdb;
		$count = 0;

		$checks = array(
			array( GMM_Database::table( 'teachers' ), 'profile_image' ),
			array( GMM_Database::table( 'teachers' ), 'intro_video' ),
			array( GMM_Database::table( 'students' ), 'profile_image' ),
			array( GMM_Database::table( 'classes' ), 'image' ),
			array( GMM_Database::table( 'classes' ), 'video' ),
			array( GMM_Database::table( 'programs' ), 'image' ),
			array( GMM_Database::table( 'blog_posts' ), 'image' ),
		);

		foreach ( $checks as $pair ) {
			$table  = $pair[0];
			$column = $pair[1];
			// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
			$found = $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$table} WHERE {$column} = %s", $id ) );
			$count += absint( $found );
		}

		return $count;
	}

	/**
	 * Read stored attachment ID from a row.
	 *
	 * @param string $table  Table.
	 * @param string $column Column.
	 * @param int    $id     Row ID.
	 * @return int
	 */
	private static function get_stored_attachment_id( $table, $column, $id ) {
		global $wpdb;
		$column = preg_replace( '/[^a-z0-9_]/i', '', $column );
		// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		$value = $wpdb->get_var( $wpdb->prepare( "SELECT {$column} FROM {$table} WHERE id = %d LIMIT 1", absint( $id ) ) );
		$value = is_string( $value ) ? trim( $value ) : '';
		return ( $value && ctype_digit( $value ) ) ? absint( $value ) : 0;
	}

	/**
	 * Whether current user owns a class.
	 *
	 * @param int $class_id Class ID.
	 * @param int $user_id  User ID.
	 * @return bool
	 */
	private static function user_owns_class( $class_id, $user_id ) {
		if ( ! gmm_is_teacher( $user_id ) || ! class_exists( 'GMM_Teacher' ) ) {
			return false;
		}
		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return false;
		}
		global $wpdb;
		$table = GMM_Database::table( 'classes' );
		$owner = $wpdb->get_var(
			$wpdb->prepare( "SELECT teacher_id FROM {$table} WHERE id = %d LIMIT 1", absint( $class_id ) )
		);
		return (int) $owner === (int) $teacher_id;
	}

	/**
	 * Whether current user uploaded the attachment.
	 *
	 * @param int $attachment_id Attachment ID.
	 * @return bool
	 */
	private static function user_owns_attachment( $attachment_id ) {
		$post = get_post( absint( $attachment_id ) );
		return $post && (int) $post->post_author === (int) get_current_user_id();
	}

	/**
	 * Map PHP upload error codes.
	 *
	 * @param int $code Error code.
	 * @return string
	 */
	private static function upload_error_message( $code ) {
		switch ( $code ) {
			case UPLOAD_ERR_INI_SIZE:
			case UPLOAD_ERR_FORM_SIZE:
				return __( 'The uploaded file is too large.', 'gospel-music-mastery' );
			case UPLOAD_ERR_PARTIAL:
				return __( 'The file was only partially uploaded.', 'gospel-music-mastery' );
			case UPLOAD_ERR_NO_FILE:
				return __( 'No file was uploaded.', 'gospel-music-mastery' );
			default:
				return __( 'File upload error.', 'gospel-music-mastery' );
		}
	}

	/**
	 * Read request value (POST preferred).
	 *
	 * @param string $key Key.
	 * @return mixed
	 */
	private static function request_value( $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( isset( $_POST[ $key ] ) ) {
			// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
			return wp_unslash( $_POST[ $key ] );
		}
		return '';
	}
}
