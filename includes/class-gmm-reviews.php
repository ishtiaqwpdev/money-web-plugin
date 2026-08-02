<?php
/**
 * Reviews and rating system for Gospel Music Mastery.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Reviews
 *
 * Student reviews, teacher/class ratings, and admin moderation.
 */
class GMM_Reviews {

	const STATUS_PENDING  = 'pending';
	const STATUS_APPROVED = 'approved';
	const STATUS_REJECTED = 'rejected';

	const NONCE_ACTION = 'gmm_review_action';

	const COMMENT_MIN = 10;
	const COMMENT_MAX = 1000;

	/**
	 * Allowed review statuses.
	 *
	 * @var array<int, string>
	 */
	const STATUSES = array(
		self::STATUS_PENDING,
		self::STATUS_APPROVED,
		self::STATUS_REJECTED,
	);

	/**
	 * Register hooks and shortcodes.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_action( 'init', $instance, 'register_shortcodes', 20 );
	}

	/**
	 * Prepare review shortcodes (no templates / UI yet).
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		add_shortcode( 'gmm_teacher_reviews', array( $this, 'shortcode_teacher_reviews' ) );
		add_shortcode( 'gmm_student_reviews', array( $this, 'shortcode_student_reviews' ) );
		add_shortcode( 'gmm_add_review', array( $this, 'shortcode_add_review' ) );
	}

	/**
	 * [gmm_teacher_reviews] — data prepared; markup deferred.
	 *
	 * @param array<string, mixed>|string $atts Attributes.
	 * @return string
	 */
	public function shortcode_teacher_reviews( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'teacher_id' => 0,
				'class_id'   => 0,
				'limit'      => 20,
			),
			is_array( $atts ) ? $atts : array(),
			'gmm_teacher_reviews'
		);

		$teacher_id = absint( $atts['teacher_id'] );
		$class_id   = absint( $atts['class_id'] );
		$limit      = absint( $atts['limit'] );

		$reviews = array();
		if ( $class_id ) {
			$reviews = self::get_class_reviews( $class_id, array( 'status' => self::STATUS_APPROVED, 'limit' => $limit ) );
		} elseif ( $teacher_id ) {
			$reviews = self::get_teacher_reviews( $teacher_id, array( 'status' => self::STATUS_APPROVED, 'limit' => $limit ) );
		}

		/**
		 * Filter prepared teacher review shortcode data (UI later).
		 *
		 * @since 1.0.0
		 * @param array<string, mixed> $payload Prepared payload.
		 * @param array<string, mixed> $atts    Shortcode atts.
		 */
		$payload = apply_filters(
			'gmm_teacher_reviews_shortcode_data',
			array(
				'teacher_id' => $teacher_id,
				'class_id'   => $class_id,
				'reviews'    => $reviews,
				'nonce'      => wp_create_nonce( self::NONCE_ACTION ),
			),
			$atts
		);

		/**
		 * Filter HTML for teacher reviews shortcode. Empty until templates wired.
		 *
		 * @since 1.0.0
		 * @param string               $html    HTML.
		 * @param array<string, mixed> $payload Data.
		 */
		return (string) apply_filters( 'gmm_teacher_reviews_shortcode_html', '', $payload );
	}

	/**
	 * [gmm_student_reviews] — own reviews list data prepared.
	 *
	 * @param array<string, mixed>|string $atts Attributes.
	 * @return string
	 */
	public function shortcode_student_reviews( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'limit' => 50,
			),
			is_array( $atts ) ? $atts : array(),
			'gmm_student_reviews'
		);

		$user_id = get_current_user_id();
		$reviews = ( $user_id && gmm_is_student( $user_id ) )
			? self::get_student_reviews( $user_id, array( 'limit' => absint( $atts['limit'] ) ) )
			: array();

		$payload = apply_filters(
			'gmm_student_reviews_shortcode_data',
			array(
				'user_id'  => $user_id,
				'reviews'  => $reviews,
				'nonce'    => wp_create_nonce( self::NONCE_ACTION ),
			),
			$atts
		);

		return (string) apply_filters( 'gmm_student_reviews_shortcode_html', '', $payload );
	}

	/**
	 * [gmm_add_review] — form data prepared; no UI yet.
	 *
	 * @param array<string, mixed>|string $atts Attributes.
	 * @return string
	 */
	public function shortcode_add_review( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'teacher_id' => 0,
				'class_id'   => 0,
				'booking_id' => 0,
			),
			is_array( $atts ) ? $atts : array(),
			'gmm_add_review'
		);

		$payload = apply_filters(
			'gmm_add_review_shortcode_data',
			array(
				'teacher_id' => absint( $atts['teacher_id'] ),
				'class_id'   => absint( $atts['class_id'] ),
				'booking_id' => absint( $atts['booking_id'] ),
				'can_review' => self::current_student_can_review(
					absint( $atts['teacher_id'] ),
					absint( $atts['class_id'] ),
					absint( $atts['booking_id'] )
				),
				'nonce'      => wp_create_nonce( self::NONCE_ACTION ),
			),
			$atts
		);

		return (string) apply_filters( 'gmm_add_review_shortcode_html', '', $payload );
	}

	/**
	 * Create a student review.
	 *
	 * @param array<string, mixed> $data  teacher_id, class_id, rating, comment; optional booking_id.
	 * @param string               $nonce Optional nonce.
	 * @param int                  $user_id Optional WP user ID.
	 * @return int|WP_Error Review ID.
	 */
	public static function create( $data, $nonce = '', $user_id = 0 ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$auth    = self::authorize_student( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		$validated = self::validate_review_input( $data, true );
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return new WP_Error( 'gmm_no_profile', __( 'Student profile not found.', 'gospel-music-mastery' ) );
		}

		$eligible = self::student_eligible_for_review(
			$student_id,
			$validated['teacher_id'],
			$validated['class_id'],
			$validated['booking_id']
		);
		if ( is_wp_error( $eligible ) ) {
			return $eligible;
		}

		if ( self::find_duplicate( $student_id, $validated['teacher_id'], $validated['class_id'] ) ) {
			return new WP_Error( 'gmm_duplicate', __( 'You have already reviewed this class.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table = GMM_Database::table( 'reviews' );

		$inserted = $wpdb->insert(
			$table,
			array(
				'student_id' => $student_id,
				'teacher_id' => $validated['teacher_id'],
				'class_id'   => $validated['class_id'],
				'rating'     => $validated['rating'],
				'comment'    => $validated['comment'],
				'status'     => self::STATUS_PENDING,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%d', '%d', '%d', '%s', '%s', '%s' )
		);

		if ( ! $inserted ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not save review.', 'gospel-music-mastery' ) );
		}

		$review_id = (int) $wpdb->insert_id;
		$row       = self::get_review( $review_id );

		/**
		 * Fires after a review is created (pending moderation).
		 *
		 * @since 1.0.0
		 * @param int                  $review_id Review ID.
		 * @param array<string, mixed> $row       Review row.
		 */
		do_action( 'gmm_review_created', $review_id, is_array( $row ) ? $row : array() );

		return $review_id;
	}

	/**
	 * Update own review (student only). Resets status to pending.
	 *
	 * @param int                  $review_id Review ID.
	 * @param array<string, mixed> $data      rating, comment.
	 * @param string               $nonce     Optional nonce.
	 * @param int                  $user_id   WP user ID.
	 * @return true|WP_Error
	 */
	public static function update( $review_id, $data, $nonce = '', $user_id = 0 ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$auth    = self::authorize_student( $user_id );
		if ( is_wp_error( $auth ) ) {
			return $auth;
		}

		$review_id = absint( $review_id );
		$review    = self::get_review( $review_id );
		if ( ! $review ) {
			return new WP_Error( 'gmm_not_found', __( 'Review not found.', 'gospel-music-mastery' ) );
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id || (int) $review['student_id'] !== (int) $student_id ) {
			return new WP_Error( 'gmm_forbidden', __( 'You can only edit your own reviews.', 'gospel-music-mastery' ) );
		}

		$validated = self::validate_review_input(
			array_merge(
				array(
					'teacher_id' => $review['teacher_id'],
					'class_id'   => $review['class_id'],
				),
				is_array( $data ) ? $data : array()
			),
			false
		);
		if ( is_wp_error( $validated ) ) {
			return $validated;
		}

		$was_approved = ( self::STATUS_APPROVED === $review['status'] );

		global $wpdb;
		$table   = GMM_Database::table( 'reviews' );
		$updated = $wpdb->update(
			$table,
			array(
				'rating'  => $validated['rating'],
				'comment' => $validated['comment'],
				'status'  => self::STATUS_PENDING,
			),
			array( 'id' => $review_id ),
			array( '%d', '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not update review.', 'gospel-music-mastery' ) );
		}

		if ( $was_approved ) {
			self::calculate_teacher_rating( absint( $review['teacher_id'] ) );
			self::calculate_class_rating( absint( $review['class_id'] ) );
		}

		return true;
	}

	/**
	 * Delete own review (student) or any review (admin).
	 *
	 * Teachers cannot delete reviews.
	 *
	 * @param int    $review_id Review ID.
	 * @param string $nonce     Optional nonce.
	 * @param int    $user_id   WP user ID.
	 * @return true|WP_Error
	 */
	public static function delete( $review_id, $nonce = '', $user_id = 0 ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		$user_id   = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$review_id = absint( $review_id );
		$review    = self::get_review( $review_id );

		if ( ! $review ) {
			return new WP_Error( 'gmm_not_found', __( 'Review not found.', 'gospel-music-mastery' ) );
		}

		$is_admin = self::is_admin_moderator( $user_id );
		$owns     = false;

		if ( ! $is_admin ) {
			$auth = self::authorize_student( $user_id );
			if ( is_wp_error( $auth ) ) {
				return new WP_Error( 'gmm_forbidden', __( 'You cannot delete this review.', 'gospel-music-mastery' ) );
			}
			$student_id = GMM_Student::get_student_id( $user_id );
			$owns       = $student_id && (int) $review['student_id'] === (int) $student_id;
			if ( ! $owns ) {
				return new WP_Error( 'gmm_forbidden', __( 'You can only delete your own reviews.', 'gospel-music-mastery' ) );
			}
		}

		// Explicit: teachers may not delete.
		if ( ! $is_admin && ! $owns && gmm_is_teacher( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'Teachers cannot delete reviews.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'reviews' );
		$deleted = $wpdb->delete( $table, array( 'id' => $review_id ), array( '%d' ) );

		if ( false === $deleted ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not delete review.', 'gospel-music-mastery' ) );
		}

		self::calculate_teacher_rating( absint( $review['teacher_id'] ) );
		self::calculate_class_rating( absint( $review['class_id'] ) );

		return true;
	}

	/**
	 * Student views own reviews.
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $args    Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_student_reviews( $user_id = 0, $args = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $user_id || ! self::can_view_student_reviews( $user_id ) ) {
			return array();
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return array();
		}

		$args               = is_array( $args ) ? $args : array();
		$args['student_id'] = $student_id;

		return self::query_reviews( $args );
	}

	/**
	 * Approved (or filtered) reviews for a teacher profile.
	 *
	 * @param int                  $teacher_id Teacher row ID.
	 * @param array<string, mixed> $args       Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_teacher_reviews( $teacher_id, $args = array() ) {
		$teacher_id = absint( $teacher_id );
		if ( ! $teacher_id ) {
			return array();
		}

		$args               = is_array( $args ) ? $args : array();
		$args['teacher_id'] = $teacher_id;

		if ( empty( $args['status'] ) && ! self::is_admin_moderator() ) {
			// Teachers may see all reviews on own profile; public sees approved only.
			if ( self::current_user_owns_teacher( $teacher_id ) ) {
				// No status filter — teacher views own profile reviews.
			} else {
				$args['status'] = self::STATUS_APPROVED;
			}
		}

		return self::query_reviews( $args );
	}

	/**
	 * Reviews for a class.
	 *
	 * @param int                  $class_id Class ID.
	 * @param array<string, mixed> $args     Filters.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_class_reviews( $class_id, $args = array() ) {
		$class_id = absint( $class_id );
		if ( ! $class_id ) {
			return array();
		}

		$args             = is_array( $args ) ? $args : array();
		$args['class_id'] = $class_id;

		if ( empty( $args['status'] ) && ! self::is_admin_moderator() ) {
			$class = self::get_class_row( $class_id );
			if ( $class && self::current_user_owns_teacher( absint( $class['teacher_id'] ) ) ) {
				// Teacher of class may view all statuses.
			} else {
				$args['status'] = self::STATUS_APPROVED;
			}
		}

		return self::query_reviews( $args );
	}

	/**
	 * Admin: get all reviews.
	 *
	 * @param array<string, mixed> $args Filters.
	 * @return array<int, array<string, mixed>>|WP_Error
	 */
	public static function admin_get_reviews( $args = array() ) {
		if ( ! self::is_admin_moderator() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Admin access required.', 'gospel-music-mastery' ) );
		}
		return self::query_reviews( is_array( $args ) ? $args : array() );
	}

	/**
	 * Admin approve review.
	 *
	 * @param int    $review_id Review ID.
	 * @param string $nonce     Optional nonce.
	 * @return true|WP_Error
	 */
	public static function admin_approve( $review_id, $nonce = '' ) {
		return self::admin_set_status( $review_id, self::STATUS_APPROVED, $nonce, 'gmm_review_approved' );
	}

	/**
	 * Admin reject review.
	 *
	 * @param int    $review_id Review ID.
	 * @param string $nonce     Optional nonce.
	 * @return true|WP_Error
	 */
	public static function admin_reject( $review_id, $nonce = '' ) {
		return self::admin_set_status( $review_id, self::STATUS_REJECTED, $nonce, 'gmm_review_rejected' );
	}

	/**
	 * Admin delete review.
	 *
	 * @param int    $review_id Review ID.
	 * @param string $nonce     Optional nonce.
	 * @return true|WP_Error
	 */
	public static function admin_delete( $review_id, $nonce = '' ) {
		if ( ! self::is_admin_moderator() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Admin access required.', 'gospel-music-mastery' ) );
		}
		return self::delete( $review_id, $nonce, get_current_user_id() );
	}

	/**
	 * Calculate and store average teacher rating (approved reviews only).
	 *
	 * @param int $teacher_id Teacher row ID.
	 * @return float Average rating.
	 */
	public static function calculate_teacher_rating( $teacher_id ) {
		$teacher_id = absint( $teacher_id );
		if ( ! $teacher_id ) {
			return 0.0;
		}

		$avg = self::average_rating(
			array(
				'teacher_id' => $teacher_id,
				'status'     => self::STATUS_APPROVED,
			)
		);

		global $wpdb;
		$table = GMM_Database::table( 'teachers' );
		$wpdb->update(
			$table,
			array(
				'rating'     => $avg,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $teacher_id ),
			array( '%f', '%s' ),
			array( '%d' )
		);

		/**
		 * Fires after teacher rating is recalculated.
		 *
		 * @since 1.0.0
		 * @param int   $teacher_id Teacher ID.
		 * @param float $avg        Average.
		 */
		do_action( 'gmm_teacher_rating_updated', $teacher_id, $avg );

		return $avg;
	}

	/**
	 * Calculate and store average class rating on gmm_classes.
	 *
	 * @param int $class_id Class ID.
	 * @return float Average rating.
	 */
	public static function calculate_class_rating( $class_id ) {
		$class_id = absint( $class_id );
		if ( ! $class_id ) {
			return 0.0;
		}

		$avg = self::average_rating(
			array(
				'class_id' => $class_id,
				'status'   => self::STATUS_APPROVED,
			)
		);

		global $wpdb;
		$table = GMM_Database::table( 'classes' );
		$wpdb->update(
			$table,
			array(
				'rating'     => $avg,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $class_id ),
			array( '%f', '%s' ),
			array( '%d' )
		);

		/**
		 * Fires after class rating is recalculated.
		 *
		 * @since 1.0.0
		 * @param int   $class_id Class ID.
		 * @param float $avg      Average.
		 */
		do_action( 'gmm_class_rating_updated', $class_id, $avg );

		return $avg;
	}

	/**
	 * Single review row.
	 *
	 * @param int $review_id Review ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_review( $review_id ) {
		$review_id = absint( $review_id );
		if ( ! $review_id ) {
			return null;
		}

		global $wpdb;
		$table = GMM_Database::table( 'reviews' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $review_id ),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * Whether current student may leave a review for teacher/class.
	 *
	 * @param int $teacher_id Teacher ID.
	 * @param int $class_id   Class ID.
	 * @param int $booking_id Optional booking ID.
	 * @return bool
	 */
	public static function current_student_can_review( $teacher_id, $class_id, $booking_id = 0 ) {
		$user_id = get_current_user_id();
		if ( ! $user_id || ! gmm_is_student( $user_id ) ) {
			return false;
		}
		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return false;
		}
		if ( self::find_duplicate( $student_id, absint( $teacher_id ), absint( $class_id ) ) ) {
			return false;
		}
		$result = self::student_eligible_for_review( $student_id, absint( $teacher_id ), absint( $class_id ), absint( $booking_id ) );
		return ! is_wp_error( $result );
	}

	/**
	 * Verify review nonce.
	 *
	 * @param string $nonce Nonce.
	 * @return bool
	 */
	public static function verify_nonce( $nonce ) {
		return (bool) wp_verify_nonce( (string) $nonce, self::NONCE_ACTION );
	}

	/**
	 * Admin status transition.
	 *
	 * @param int    $review_id Review ID.
	 * @param string $status    New status.
	 * @param string $nonce     Optional nonce.
	 * @param string $hook      Action hook name.
	 * @return true|WP_Error
	 */
	private static function admin_set_status( $review_id, $status, $nonce, $hook ) {
		if ( '' !== $nonce && ! self::verify_nonce( $nonce ) ) {
			return new WP_Error( 'gmm_nonce', __( 'Invalid security token.', 'gospel-music-mastery' ) );
		}

		if ( ! self::is_admin_moderator() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Admin access required.', 'gospel-music-mastery' ) );
		}

		$review_id = absint( $review_id );
		$review    = self::get_review( $review_id );
		if ( ! $review ) {
			return new WP_Error( 'gmm_not_found', __( 'Review not found.', 'gospel-music-mastery' ) );
		}

		$status = sanitize_key( $status );
		if ( ! in_array( $status, self::STATUSES, true ) ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid review status.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table   = GMM_Database::table( 'reviews' );
		$updated = $wpdb->update(
			$table,
			array( 'status' => $status ),
			array( 'id' => $review_id ),
			array( '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not update review.', 'gospel-music-mastery' ) );
		}

		self::calculate_teacher_rating( absint( $review['teacher_id'] ) );
		self::calculate_class_rating( absint( $review['class_id'] ) );

		$row = self::get_review( $review_id );

		/**
		 * Dynamic review moderation hook (approved / rejected).
		 *
		 * @since 1.0.0
		 * @param int                  $review_id Review ID.
		 * @param array<string, mixed> $row       Review row.
		 */
		do_action( $hook, $review_id, is_array( $row ) ? $row : array() );

		return true;
	}

	/**
	 * Validate create/update input.
	 *
	 * @param array<string, mixed> $data   Input.
	 * @param bool                 $create Require teacher/class.
	 * @return array<string, mixed>|WP_Error
	 */
	private static function validate_review_input( $data, $create = true ) {
		$data = is_array( $data ) ? $data : array();

		$teacher_id = isset( $data['teacher_id'] ) ? absint( $data['teacher_id'] ) : 0;
		$class_id   = isset( $data['class_id'] ) ? absint( $data['class_id'] ) : 0;
		$booking_id = isset( $data['booking_id'] ) ? absint( $data['booking_id'] ) : 0;
		$rating     = isset( $data['rating'] ) ? absint( $data['rating'] ) : 0;
		$comment    = isset( $data['comment'] ) ? sanitize_textarea_field( (string) $data['comment'] ) : '';

		if ( $create && ( ! $teacher_id || ! $class_id ) ) {
			return new WP_Error( 'gmm_invalid', __( 'Teacher and class are required.', 'gospel-music-mastery' ) );
		}

		if ( $rating < 1 || $rating > 5 ) {
			return new WP_Error( 'gmm_invalid_rating', __( 'Rating must be between 1 and 5.', 'gospel-music-mastery' ) );
		}

		$length = strlen( $comment );
		if ( $length < self::COMMENT_MIN ) {
			return new WP_Error(
				'gmm_comment_short',
				sprintf(
					/* translators: %d: minimum characters */
					__( 'Comment must be at least %d characters.', 'gospel-music-mastery' ),
					self::COMMENT_MIN
				)
			);
		}
		if ( $length > self::COMMENT_MAX ) {
			return new WP_Error(
				'gmm_comment_long',
				sprintf(
					/* translators: %d: maximum characters */
					__( 'Comment must be at most %d characters.', 'gospel-music-mastery' ),
					self::COMMENT_MAX
				)
			);
		}

		if ( $create ) {
			if ( ! self::teacher_exists( $teacher_id ) ) {
				return new WP_Error( 'gmm_invalid_teacher', __( 'Teacher not found.', 'gospel-music-mastery' ) );
			}
			$class = self::get_class_row( $class_id );
			if ( ! $class ) {
				return new WP_Error( 'gmm_invalid_class', __( 'Class not found.', 'gospel-music-mastery' ) );
			}
			if ( (int) $class['teacher_id'] !== (int) $teacher_id ) {
				return new WP_Error( 'gmm_mismatch', __( 'Class does not belong to this teacher.', 'gospel-music-mastery' ) );
			}
		}

		return array(
			'teacher_id' => $teacher_id,
			'class_id'   => $class_id,
			'booking_id' => $booking_id,
			'rating'     => $rating,
			'comment'    => $comment,
		);
	}

	/**
	 * Require completed booking (attended lesson) for this student/teacher/class.
	 *
	 * @param int $student_id Student row ID.
	 * @param int $teacher_id Teacher row ID.
	 * @param int $class_id   Class ID.
	 * @param int $booking_id Optional specific booking.
	 * @return true|WP_Error
	 */
	private static function student_eligible_for_review( $student_id, $teacher_id, $class_id, $booking_id = 0 ) {
		$student_id = absint( $student_id );
		$teacher_id = absint( $teacher_id );
		$class_id   = absint( $class_id );
		$booking_id = absint( $booking_id );

		if ( ! $student_id || ! $teacher_id || ! $class_id ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid review target.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$completed = GMM_Booking::STATUS_COMPLETED;

		if ( $booking_id ) {
			$row = $wpdb->get_row(
				$wpdb->prepare(
					"SELECT id, student_id, teacher_id, class_id, booking_status
					FROM {$bookings}
					WHERE id = %d
					LIMIT 1",
					$booking_id
				),
				ARRAY_A
			);

			if ( ! is_array( $row ) ) {
				return new WP_Error( 'gmm_booking_missing', __( 'Booking not found.', 'gospel-music-mastery' ) );
			}
			if ( (int) $row['student_id'] !== $student_id
				|| (int) $row['teacher_id'] !== $teacher_id
				|| (int) $row['class_id'] !== $class_id ) {
				return new WP_Error( 'gmm_ownership', __( 'Booking does not match this review.', 'gospel-music-mastery' ) );
			}
			if ( $completed !== $row['booking_status'] ) {
				return new WP_Error( 'gmm_not_completed', __( 'You can only review completed lessons.', 'gospel-music-mastery' ) );
			}

			return true;
		}

		$found = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$bookings}
				WHERE student_id = %d
				AND teacher_id = %d
				AND class_id = %d
				AND booking_status = %s
				LIMIT 1",
				$student_id,
				$teacher_id,
				$class_id,
				$completed
			)
		);

		if ( ! $found ) {
			return new WP_Error( 'gmm_not_eligible', __( 'You can only review classes you have completed.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * Existing review for student+teacher+class.
	 *
	 * @param int $student_id Student ID.
	 * @param int $teacher_id Teacher ID.
	 * @param int $class_id   Class ID.
	 * @return int Existing review ID or 0.
	 */
	private static function find_duplicate( $student_id, $teacher_id, $class_id ) {
		global $wpdb;
		$table = GMM_Database::table( 'reviews' );
		$id    = $wpdb->get_var(
			$wpdb->prepare(
				"SELECT id FROM {$table}
				WHERE student_id = %d AND teacher_id = %d AND class_id = %d
				LIMIT 1",
				absint( $student_id ),
				absint( $teacher_id ),
				absint( $class_id )
			)
		);
		return absint( $id );
	}

	/**
	 * Query reviews with filters.
	 *
	 * @param array<string, mixed> $args Filters.
	 * @return array<int, array<string, mixed>>
	 */
	private static function query_reviews( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'student_id' => 0,
				'teacher_id' => 0,
				'class_id'   => 0,
				'status'     => '',
				'limit'      => 50,
				'offset'     => 0,
			)
		);

		$limit  = absint( $args['limit'] );
		$offset = absint( $args['offset'] );
		if ( $limit < 1 ) {
			$limit = 50;
		}
		if ( $limit > 200 ) {
			$limit = 200;
		}

		global $wpdb;
		$table  = GMM_Database::table( 'reviews' );
		$where  = array( '1=1' );
		$params = array();

		if ( absint( $args['student_id'] ) ) {
			$where[]  = 'student_id = %d';
			$params[] = absint( $args['student_id'] );
		}
		if ( absint( $args['teacher_id'] ) ) {
			$where[]  = 'teacher_id = %d';
			$params[] = absint( $args['teacher_id'] );
		}
		if ( absint( $args['class_id'] ) ) {
			$where[]  = 'class_id = %d';
			$params[] = absint( $args['class_id'] );
		}
		if ( '' !== $args['status'] ) {
			$status = sanitize_key( (string) $args['status'] );
			if ( in_array( $status, self::STATUSES, true ) ) {
				$where[]  = 'status = %s';
				$params[] = $status;
			}
		}

		$sql      = 'SELECT * FROM ' . $table . ' WHERE ' . implode( ' AND ', $where )
			. ' ORDER BY created_at DESC, id DESC LIMIT %d OFFSET %d';
		$params[] = $limit;
		$params[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Average of approved (or filtered) ratings.
	 *
	 * @param array<string, mixed> $args Filters.
	 * @return float
	 */
	private static function average_rating( $args ) {
		$args = wp_parse_args(
			$args,
			array(
				'teacher_id' => 0,
				'class_id'   => 0,
				'status'     => self::STATUS_APPROVED,
			)
		);

		global $wpdb;
		$table  = GMM_Database::table( 'reviews' );
		$where  = array( '1=1' );
		$params = array();

		if ( absint( $args['teacher_id'] ) ) {
			$where[]  = 'teacher_id = %d';
			$params[] = absint( $args['teacher_id'] );
		}
		if ( absint( $args['class_id'] ) ) {
			$where[]  = 'class_id = %d';
			$params[] = absint( $args['class_id'] );
		}
		if ( '' !== $args['status'] ) {
			$where[]  = 'status = %s';
			$params[] = sanitize_key( (string) $args['status'] );
		}

		$sql = 'SELECT AVG(rating) FROM ' . $table . ' WHERE ' . implode( ' AND ', $where );

		if ( empty( $params ) ) {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$avg = $wpdb->get_var( $sql );
		} else {
			// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			$avg = $wpdb->get_var( $wpdb->prepare( $sql, $params ) );
		}

		return round( (float) $avg, 2 );
	}

	/**
	 * Student auth check.
	 *
	 * @param int $user_id WP user ID.
	 * @return true|WP_Error
	 */
	private static function authorize_student( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id || ! is_user_logged_in() ) {
			return new WP_Error( 'gmm_not_logged_in', __( 'You must be logged in.', 'gospel-music-mastery' ) );
		}
		if ( (int) get_current_user_id() !== $user_id && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'Invalid user context.', 'gospel-music-mastery' ) );
		}
		if ( ! gmm_is_student( $user_id ) ) {
			return new WP_Error( 'gmm_not_student', __( 'Only students can manage reviews.', 'gospel-music-mastery' ) );
		}
		return true;
	}

	/**
	 * Whether user can view a student's review list.
	 *
	 * @param int $user_id Target user.
	 * @return bool
	 */
	private static function can_view_student_reviews( $user_id ) {
		$current = get_current_user_id();
		if ( ! $current ) {
			return false;
		}
		if ( (int) $current === (int) $user_id && gmm_is_student( $user_id ) ) {
			return true;
		}
		return self::is_admin_moderator( $current );
	}

	/**
	 * Admin moderator check.
	 *
	 * @param int $user_id Optional user.
	 * @return bool
	 */
	private static function is_admin_moderator( $user_id = 0 ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id ) {
			return false;
		}
		if ( user_can( $user_id, 'manage_options' ) ) {
			return true;
		}
		return user_can( $user_id, 'manage_gmm_reviews' );
	}

	/**
	 * Whether current user owns the teacher row.
	 *
	 * @param int $teacher_id Teacher row ID.
	 * @return bool
	 */
	private static function current_user_owns_teacher( $teacher_id ) {
		$user_id = get_current_user_id();
		if ( ! $user_id || ! class_exists( 'GMM_Teacher' ) ) {
			return false;
		}
		$own = GMM_Teacher::get_teacher_id( $user_id );
		return $own && (int) $own === (int) $teacher_id;
	}

	/**
	 * Teacher exists.
	 *
	 * @param int $teacher_id Teacher ID.
	 * @return bool
	 */
	private static function teacher_exists( $teacher_id ) {
		global $wpdb;
		$table = GMM_Database::table( 'teachers' );
		$id    = $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$table} WHERE id = %d LIMIT 1", absint( $teacher_id ) )
		);
		return (bool) $id;
	}

	/**
	 * Class row.
	 *
	 * @param int $class_id Class ID.
	 * @return array<string, mixed>|null
	 */
	private static function get_class_row( $class_id ) {
		global $wpdb;
		$table = GMM_Database::table( 'classes' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", absint( $class_id ) ),
			ARRAY_A
		);
		return is_array( $row ) ? $row : null;
	}
}
