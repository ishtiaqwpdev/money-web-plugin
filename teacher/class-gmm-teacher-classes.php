<?php
/**
 * Teacher class (course) CRUD.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Teacher_Classes
 *
 * Teachers may only manage classes where teacher_id matches their profile.
 */
class GMM_Teacher_Classes {

	/**
	 * Create a class for the current teacher.
	 *
	 * @param array<string, mixed> $data    Class fields.
	 * @param int                  $user_id WP user ID.
	 * @return int|WP_Error New class ID or error.
	 */
	public static function create_class( $data, $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		$auth = self::authorize_manage( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( class_exists( 'GMM_Teacher_Auth' ) && ! GMM_Teacher_Auth::is_approved( $user_id ) ) {
			return new WP_Error( 'gmm_pending', __( 'Your account is waiting for approval.', 'gospel-music-mastery' ) );
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return new WP_Error( 'gmm_no_profile', __( 'Teacher profile not found.', 'gospel-music-mastery' ) );
		}

		$clean = self::sanitize_class_fields( $data, true );
		if ( empty( $clean['title'] ) ) {
			return new WP_Error( 'gmm_title_required', __( 'Class title is required.', 'gospel-music-mastery' ) );
		}

		$now = current_time( 'mysql' );
		$row = array_merge(
			array(
				'teacher_id'  => $teacher_id,
				'title'       => '',
				'description' => '',
				'category'    => '',
				'difficulty'  => '',
				'duration'    => 0,
				'price'       => 0,
				'image'       => '',
				'status'      => 'draft',
				'featured'    => 0,
				'created_at'  => $now,
				'updated_at'  => $now,
			),
			$clean
		);
		$row['teacher_id'] = $teacher_id;

		global $wpdb;
		$table    = GMM_Database::table( 'classes' );
		$inserted = $wpdb->insert( $table, $row );

		if ( ! $inserted ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not create class.', 'gospel-music-mastery' ) );
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Update own class.
	 *
	 * @param int                  $class_id Class ID.
	 * @param array<string, mixed> $data     Fields.
	 * @param int                  $user_id  WP user ID.
	 * @return true|WP_Error
	 */
	public static function update_class( $class_id, $data, $user_id = 0 ) {
		$user_id  = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$class_id = absint( $class_id );

		$auth = self::authorize_manage( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( ! self::owns_class( $class_id, $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only update your own classes.', 'gospel-music-mastery' ) );
		}

		$clean = self::sanitize_class_fields( $data, false );
		if ( empty( $clean ) ) {
			return new WP_Error( 'gmm_no_fields', __( 'No valid class fields to update.', 'gospel-music-mastery' ) );
		}

		$clean['updated_at'] = current_time( 'mysql' );

		global $wpdb;
		$table   = GMM_Database::table( 'classes' );
		$updated = $wpdb->update(
			$table,
			$clean,
			array( 'id' => $class_id ),
			null,
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not update class.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * Delete own class.
	 *
	 * @param int $class_id Class ID.
	 * @param int $user_id  WP user ID.
	 * @return true|WP_Error
	 */
	public static function delete_class( $class_id, $user_id = 0 ) {
		$user_id  = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$class_id = absint( $class_id );

		$auth = self::authorize_manage( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( ! self::owns_class( $class_id, $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only delete your own classes.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'classes' );
		$deleted = $wpdb->delete( $table, array( 'id' => $class_id ), array( '%d' ) );

		if ( false === $deleted ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not delete class.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * List classes for a teacher.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_teacher_classes( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id || ! GMM_Teacher::can_view_profile( $user_id ) ) {
			return array();
		}

		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );
		if ( ! $teacher_id ) {
			return array();
		}

		global $wpdb;
		$table = GMM_Database::table( 'classes' );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table} WHERE teacher_id = %d ORDER BY created_at DESC",
				$teacher_id
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @param int $user_id WP user ID.
	 * @return true|WP_Error
	 */
	private static function authorize_manage( $user_id ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'gmm_not_logged_in', __( 'You must be logged in.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_gmm_classes' ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_cap', __( 'Missing class management capability.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== absint( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only manage your own classes.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_options' ) && ! gmm_is_teacher( $user_id ) ) {
			return new WP_Error( 'gmm_not_teacher', __( 'Teacher role required.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * @param int $class_id Class ID.
	 * @param int $user_id  WP user ID.
	 * @return bool
	 */
	private static function owns_class( $class_id, $user_id ) {
		$teacher_id = GMM_Teacher::get_teacher_id( $user_id );

		if ( ! $teacher_id || ! $class_id ) {
			return false;
		}

		global $wpdb;
		$table = GMM_Database::table( 'classes' );
		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE id = %d AND teacher_id = %d LIMIT 1",
				$class_id,
				$teacher_id
			)
		);

		return ! empty( $found );
	}

	/**
	 * @param array<string, mixed> $data   Raw.
	 * @param bool                 $create Require title when creating.
	 * @return array<string, mixed>
	 */
	private static function sanitize_class_fields( $data, $create = false ) {
		$data  = is_array( $data ) ? $data : array();
		$clean = array();

		if ( array_key_exists( 'title', $data ) || $create ) {
			$clean['title'] = sanitize_text_field( isset( $data['title'] ) ? (string) $data['title'] : '' );
		}
		if ( array_key_exists( 'description', $data ) ) {
			$clean['description'] = wp_kses_post( (string) $data['description'] );
		}
		if ( array_key_exists( 'category', $data ) ) {
			$clean['category'] = sanitize_text_field( (string) $data['category'] );
		}
		if ( array_key_exists( 'difficulty', $data ) ) {
			$clean['difficulty'] = sanitize_text_field( (string) $data['difficulty'] );
		}
		if ( array_key_exists( 'duration', $data ) ) {
			$clean['duration'] = absint( $data['duration'] );
		}
		if ( array_key_exists( 'price', $data ) ) {
			$clean['price'] = round( max( 0, (float) $data['price'] ), 2 );
		}
		if ( array_key_exists( 'image', $data ) ) {
			$clean['image'] = esc_url_raw( (string) $data['image'] );
		}
		if ( array_key_exists( 'status', $data ) ) {
			$status = sanitize_key( (string) $data['status'] );
			if ( in_array( $status, array( 'draft', 'published', 'pending', 'archived' ), true ) ) {
				$clean['status'] = $status;
			}
		}
		if ( array_key_exists( 'featured', $data ) ) {
			$clean['featured'] = empty( $data['featured'] ) ? 0 : 1;
		}

		return $clean;
	}
}
