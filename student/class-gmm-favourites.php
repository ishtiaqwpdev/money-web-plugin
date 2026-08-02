<?php
/**
 * Student favourite teachers.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Favourites
 *
 * Uses gmm_favourites table. Students only manage their own favourites.
 */
class GMM_Favourites {

	/**
	 * Add a teacher to favourites.
	 *
	 * @param int $teacher_id gmm_teachers.id.
	 * @param int $user_id    WP user ID.
	 * @return int|WP_Error Favourite row ID or error.
	 */
	public static function add_favourite( $teacher_id, $user_id = 0 ) {
		$user_id    = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$teacher_id = absint( $teacher_id );

		$auth = self::authorize( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		if ( ! $teacher_id || ! self::teacher_exists( $teacher_id ) ) {
			return new WP_Error( 'gmm_invalid_teacher', __( 'Teacher not found.', 'gospel-music-mastery' ) );
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return new WP_Error( 'gmm_no_profile', __( 'Student profile not found.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table = GMM_Database::table( 'favourites' );

		$exists = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE student_id = %d AND teacher_id = %d LIMIT 1",
				$student_id,
				$teacher_id
			)
		);

		if ( $exists ) {
			return (int) $exists;
		}

		$inserted = $wpdb->insert(
			$table,
			array(
				'student_id' => $student_id,
				'teacher_id' => $teacher_id,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%s' )
		);

		if ( ! $inserted ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not add favourite.', 'gospel-music-mastery' ) );
		}

		return (int) $wpdb->insert_id;
	}

	/**
	 * Remove a favourite teacher.
	 *
	 * @param int $teacher_id gmm_teachers.id.
	 * @param int $user_id    WP user ID.
	 * @return true|WP_Error
	 */
	public static function remove_favourite( $teacher_id, $user_id = 0 ) {
		$user_id    = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$teacher_id = absint( $teacher_id );

		$auth = self::authorize( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return new WP_Error( 'gmm_no_profile', __( 'Student profile not found.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'favourites' );
		$deleted = $wpdb->delete(
			$table,
			array(
				'student_id' => $student_id,
				'teacher_id' => $teacher_id,
			),
			array( '%d', '%d' )
		);

		if ( false === $deleted ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not remove favourite.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * Favourite teachers for student (with teacher profile fields).
	 *
	 * @param int $user_id WP user ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_favourite_teachers( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! GMM_Student::can_view_profile( $user_id ) ) {
			return array();
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return array();
		}

		global $wpdb;
		$fav      = GMM_Database::table( 'favourites' );
		$teachers = GMM_Database::table( 'teachers' );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT f.id AS favourite_id, f.created_at AS favourited_at, t.*
				FROM {$fav} f
				INNER JOIN {$teachers} t ON t.id = f.teacher_id
				WHERE f.student_id = %d
				ORDER BY f.created_at DESC",
				$student_id
			),
			ARRAY_A
		);

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @param int $user_id WP user ID.
	 * @return true|WP_Error
	 */
	private static function authorize( $user_id ) {
		if ( ! is_user_logged_in() ) {
			return new WP_Error( 'gmm_not_logged_in', __( 'You must be logged in.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_gmm_profile' ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_cap', __( 'Missing capability.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_options' ) && get_current_user_id() !== absint( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only manage your own favourites.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_options' ) && ! gmm_is_student( $user_id ) ) {
			return new WP_Error( 'gmm_not_student', __( 'Student role required.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * Whether teacher is favourited by a student.
	 *
	 * @param int $teacher_id gmm_teachers.id.
	 * @param int $user_id    WP user ID.
	 * @return bool
	 */
	public static function is_favourite( $teacher_id, $user_id = 0 ) {
		if ( class_exists( 'GMM_Teacher_Profile_Public' ) ) {
			return GMM_Teacher_Profile_Public::is_favourite( $teacher_id, $user_id );
		}
		$user_id    = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$teacher_id = absint( $teacher_id );
		if ( ! $user_id || ! $teacher_id || ! class_exists( 'GMM_Student' ) ) {
			return false;
		}
		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return false;
		}
		global $wpdb;
		$table = GMM_Database::table( 'favourites' );
		$id    = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table} WHERE student_id = %d AND teacher_id = %d LIMIT 1",
				$student_id,
				$teacher_id
			)
		);
		return ! empty( $id );
	}

	/**
	 * @param int $teacher_id Teacher row ID.
	 * @return bool
	 */
	private static function teacher_exists( $teacher_id ) {
		global $wpdb;
		$table = GMM_Database::table( 'teachers' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT id, status FROM {$table} WHERE id = %d LIMIT 1", $teacher_id ),
			ARRAY_A
		);
		if ( ! is_array( $row ) ) {
			return false;
		}
		$status = sanitize_key( (string) $row['status'] );
		return in_array( $status, array( 'approved', 'active' ), true );
	}
}
