<?php
/**
 * Student lesson views (bookings as lessons).
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Student_Lessons
 *
 * Read-only lesson access for the owning student.
 */
class GMM_Student_Lessons {

	/**
	 * All lessons (bookings) for student.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_student_lessons( $user_id = 0 ) {
		return self::query_lessons( $user_id, array() );
	}

	/**
	 * Upcoming lessons.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_upcoming_lessons( $user_id = 0 ) {
		return self::query_lessons(
			$user_id,
			array(
				'upcoming' => true,
				'statuses' => array( 'pending', 'confirmed', 'upcoming' ),
			)
		);
	}

	/**
	 * Completed lessons.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_completed_lessons( $user_id = 0 ) {
		return self::query_lessons(
			$user_id,
			array(
				'statuses' => array( 'completed' ),
			)
		);
	}

	/**
	 * Single lesson with teacher + class details (own only).
	 *
	 * @param int $booking_id Booking ID.
	 * @param int $user_id    WP user ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_lesson_details( $booking_id, $user_id = 0 ) {
		$user_id    = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$booking_id = absint( $booking_id );

		if ( ! $booking_id || ! self::authorize_view( $user_id ) ) {
			return null;
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return null;
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$teachers = GMM_Database::table( 'teachers' );
		$classes  = GMM_Database::table( 'classes' );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT b.*,
					t.first_name AS teacher_first_name,
					t.last_name AS teacher_last_name,
					t.email AS teacher_email,
					t.profile_image AS teacher_image,
					t.specialization AS teacher_specialization,
					c.title AS class_title,
					c.description AS class_description,
					c.category AS class_category,
					c.difficulty AS class_difficulty,
					c.image AS class_image
				FROM {$bookings} b
				LEFT JOIN {$teachers} t ON t.id = b.teacher_id
				LEFT JOIN {$classes} c ON c.id = b.class_id
				WHERE b.id = %d AND b.student_id = %d
				LIMIT 1",
				$booking_id,
				$student_id
			),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Filters.
	 * @return array<int, array<string, mixed>>
	 */
	private static function query_lessons( $user_id, $args ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! self::authorize_view( $user_id ) ) {
			return array();
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return array();
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$teachers = GMM_Database::table( 'teachers' );
		$classes  = GMM_Database::table( 'classes' );

		$sql    = "SELECT b.*,
				t.first_name AS teacher_first_name,
				t.last_name AS teacher_last_name,
				t.profile_image AS teacher_image,
				c.title AS class_title,
				c.category AS class_category
			FROM {$bookings} b
			LEFT JOIN {$teachers} t ON t.id = b.teacher_id
			LEFT JOIN {$classes} c ON c.id = b.class_id
			WHERE b.student_id = %d";
		$params = array( $student_id );

		if ( ! empty( $args['upcoming'] ) ) {
			$sql     .= ' AND b.booking_date >= %s';
			$params[] = current_time( 'Y-m-d' );
		}

		if ( ! empty( $args['statuses'] ) && is_array( $args['statuses'] ) ) {
			$statuses = array_map( 'sanitize_key', $args['statuses'] );
			$statuses = array_filter( $statuses );
			if ( $statuses ) {
				$placeholders = implode( ',', array_fill( 0, count( $statuses ), '%s' ) );
				$sql         .= " AND b.booking_status IN ({$placeholders})";
				$params       = array_merge( $params, $statuses );
			}
		}

		$sql     .= ' ORDER BY b.booking_date DESC, b.booking_time DESC LIMIT %d';
		$params[] = isset( $args['limit'] ) ? min( absint( $args['limit'] ), 200 ) : 100;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * @param int $user_id WP user ID.
	 * @return bool
	 */
	private static function authorize_view( $user_id ) {
		if ( ! is_user_logged_in() || ! GMM_Student::can_view_profile( $user_id ) ) {
			return false;
		}
		if ( ! current_user_can( 'manage_gmm_bookings' ) && ! current_user_can( 'manage_options' ) && ! current_user_can( 'manage_gmm_profile' ) ) {
			return false;
		}
		return true;
	}
}
