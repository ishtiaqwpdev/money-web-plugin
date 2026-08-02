<?php
/**
 * Public teacher profile + reviews controller.
 *
 * Powers [gmm_teacher_public_profile] → templates/public/teacher-profile.php
 * without changing the frozen public profile UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Teacher_Profile_Public
 */
class GMM_Teacher_Profile_Public {

	const NONCE_ACTION = 'gmm_teacher_public_profile';
	const NONCE_FIELD  = 'gmm_teacher_public_nonce';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 25, 2 );
		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 40 );
		$loader->add_filter( 'document_title_parts', $instance, 'filter_document_title', 20 );
		$loader->add_action( 'wp_head', $instance, 'output_seo_meta', 5 );

		$loader->add_action( 'wp_ajax_gmm_public_teacher_favourite', $instance, 'ajax_favourite' );
		$loader->add_action( 'wp_ajax_gmm_public_teacher_review_submit', $instance, 'ajax_review_submit' );
		$loader->add_action( 'wp_ajax_gmm_public_teacher_reviews', $instance, 'ajax_reviews_load' );
		$loader->add_action( 'wp_ajax_nopriv_gmm_public_teacher_reviews', $instance, 'ajax_reviews_load' );
	}

	/**
	 * Inject vars into shortcode template.
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Tag.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_teacher_public_profile' !== $tag ) {
			return $args;
		}
		$teacher_id = self::resolve_teacher_id( is_array( $args ) ? $args : array() );
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars( $teacher_id ) );
	}

	/**
	 * Resolve teacher ID from shortcode atts / query string.
	 *
	 * @param array<string, mixed> $atts Atts.
	 * @return int
	 */
	public static function resolve_teacher_id( $atts = array() ) {
		$atts = is_array( $atts ) ? $atts : array();
		if ( ! empty( $atts['atts'] ) && is_array( $atts['atts'] ) ) {
			$atts = array_merge( $atts, $atts['atts'] );
		}

		$id = 0;
		if ( ! empty( $atts['teacher_id'] ) ) {
			$id = absint( $atts['teacher_id'] );
		}
		if ( ! $id && ! empty( $_GET['teacher_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$id = absint( wp_unslash( $_GET['teacher_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		if ( ! $id && ! empty( $_GET['teacher'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$id = absint( wp_unslash( $_GET['teacher'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		}
		return $id;
	}

	/**
	 * Full template payload.
	 *
	 * @param int $teacher_id Teacher row ID.
	 * @return array<string, mixed>
	 */
	public static function get_template_vars( $teacher_id = 0 ) {
		$teacher_id = absint( $teacher_id );
		$profile    = $teacher_id ? self::get_profile( $teacher_id ) : null;

		if ( ! $profile ) {
			return array(
				'teacher_missing' => true,
				'teacher'         => array(),
				'classes'         => array(),
				'reviews'         => array(),
				'slots'           => array(),
				'related'         => array(),
				'is_favourite'    => false,
				'can_review'      => false,
				'review_form'     => array(),
				'booking_url'     => self::booking_base_url(),
				'teachers_url'    => self::teachers_url(),
				'seo'             => array(),
				'nonce'           => wp_create_nonce( self::NONCE_ACTION ),
			);
		}

		$rating = self::get_rating_summary( $teacher_id, false );
		$classes = self::get_classes( $teacher_id );
		$reviews = self::get_reviews( $teacher_id, array( 'limit' => 12 ) );
		$slots   = self::get_upcoming_slots( $teacher_id, 6 );
		$related = self::get_related_teachers( $teacher_id, 3 );

		$user_id     = get_current_user_id();
		$is_fav      = ( $user_id && function_exists( 'gmm_is_student' ) && gmm_is_student( $user_id ) )
			? self::is_favourite( $teacher_id, $user_id )
			: false;
		$reviewable  = self::get_reviewable_classes( $teacher_id, $user_id );
		$can_review  = ! empty( $reviewable );

		$booking = self::booking_url( $teacher_id );

		return array(
			'teacher_missing' => false,
			'teacher_id'      => $teacher_id,
			'teacher'         => $profile,
			'classes'         => $classes,
			'reviews'         => $reviews,
			'slots'           => $slots,
			'related'         => $related,
			'rating_summary'  => $rating,
			'is_favourite'    => $is_fav,
			'can_favourite'   => $user_id && function_exists( 'gmm_is_student' ) && gmm_is_student( $user_id ),
			'can_review'      => $can_review,
			'reviewable_classes' => $reviewable,
			'review_form'     => array(
				'teacher_id' => $teacher_id,
				'can_review' => $can_review,
				'classes'    => $reviewable,
				'nonce'      => wp_create_nonce( class_exists( 'GMM_Reviews' ) ? GMM_Reviews::NONCE_ACTION : self::NONCE_ACTION ),
			),
			'booking_url'     => $booking,
			'teachers_url'    => self::teachers_url(),
			'seo'             => self::build_seo( $profile, $rating ),
			'nonce'           => wp_create_nonce( self::NONCE_ACTION ),
		);
	}

	/**
	 * Completed classes the current student may still review.
	 *
	 * @param int $teacher_id Teacher ID.
	 * @param int $user_id    WP user ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_reviewable_classes( $teacher_id, $user_id = 0 ) {
		$teacher_id = absint( $teacher_id );
		$user_id    = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! $teacher_id || ! $user_id || ! function_exists( 'gmm_is_student' ) || ! gmm_is_student( $user_id ) ) {
			return array();
		}
		if ( ! class_exists( 'GMM_Student' ) || ! class_exists( 'GMM_Reviews' ) ) {
			return array();
		}

		$student_id = GMM_Student::get_student_id( $user_id );
		if ( ! $student_id ) {
			return array();
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$classes  = GMM_Database::table( 'classes' );
		$completed = class_exists( 'GMM_Booking' ) ? GMM_Booking::STATUS_COMPLETED : 'completed';

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT DISTINCT b.class_id, b.id AS booking_id, c.title
				FROM {$bookings} b
				LEFT JOIN {$classes} c ON c.id = b.class_id
				WHERE b.student_id = %d
				AND b.teacher_id = %d
				AND b.booking_status = %s
				AND b.class_id > 0
				ORDER BY b.booking_date DESC
				LIMIT 20",
				$student_id,
				$teacher_id,
				$completed
			),
			ARRAY_A
		);
		$rows = is_array( $rows ) ? $rows : array();
		$out  = array();

		foreach ( $rows as $row ) {
			$class_id = absint( $row['class_id'] );
			if ( ! $class_id ) {
				continue;
			}
			if ( ! GMM_Reviews::current_student_can_review( $teacher_id, $class_id, absint( $row['booking_id'] ) ) ) {
				continue;
			}
			$out[] = array(
				'class_id'   => $class_id,
				'booking_id' => absint( $row['booking_id'] ),
				'title'      => ! empty( $row['title'] ) ? (string) $row['title'] : sprintf( /* translators: %d class id */ __( 'Class #%d', 'gospel-music-mastery' ), $class_id ),
			);
		}

		return $out;
	}

	/**
	 * Load approved teacher public profile.
	 *
	 * @param int $teacher_id Teacher ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_profile( $teacher_id ) {
		$teacher_id = absint( $teacher_id );
		if ( ! $teacher_id ) {
			return null;
		}

		if ( class_exists( 'GMM_Teacher_Profile' ) ) {
			$base = GMM_Teacher_Profile::get_public_profile( $teacher_id, false );
			if ( ! is_array( $base ) ) {
				return null;
			}
		} else {
			$base = null;
		}

		global $wpdb;
		$table = GMM_Database::table( 'teachers' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $teacher_id ),
			ARRAY_A
		);
		if ( ! is_array( $row ) ) {
			return null;
		}

		$status = sanitize_key( (string) $row['status'] );
		if ( ! in_array( $status, array( 'approved', 'active' ), true ) ) {
			return null;
		}

		$stats = self::get_stats( $teacher_id );
		$image = '';
		if ( ! empty( $row['profile_image'] ) && function_exists( 'gmm_get_media_url' ) ) {
			$image = gmm_get_media_url( $row['profile_image'], 'large' );
		}
		if ( ! $image ) {
			$image = function_exists( 'gmm_design_asset_url' )
				? gmm_design_asset_url( 'assets/img/team/01.jpg' )
				: '';
		}

		$first = isset( $row['first_name'] ) ? (string) $row['first_name'] : '';
		$last  = isset( $row['last_name'] ) ? (string) $row['last_name'] : '';
		$name  = trim( $first . ' ' . $last );
		if ( ! $name && is_array( $base ) && ! empty( $base['name'] ) ) {
			$name = (string) $base['name'];
		}
		if ( ! $name ) {
			$name = __( 'Gospel Teacher', 'gospel-music-mastery' );
		}

		$specialization = isset( $row['specialization'] ) ? (string) $row['specialization'] : '';
		$experience     = isset( $row['experience'] ) ? (string) $row['experience'] : '';
		$bio            = isset( $row['bio'] ) ? (string) $row['bio'] : '';
		$rating         = isset( $row['rating'] ) ? (float) $row['rating'] : 0.0;
		$video          = isset( $row['intro_video'] ) ? (string) $row['intro_video'] : '';
		$video_url      = '';
		if ( $video && function_exists( 'gmm_get_media_url' ) ) {
			$video_url = gmm_get_media_url( $video );
		} elseif ( $video && filter_var( $video, FILTER_VALIDATE_URL ) ) {
			$video_url = esc_url_raw( $video );
		}

		$skills = self::parse_skills( $specialization . ', ' . $bio );

		return array(
			'id'               => $teacher_id,
			'user_id'          => isset( $row['user_id'] ) ? absint( $row['user_id'] ) : 0,
			'name'             => $name,
			'first_name'       => $first,
			'last_name'        => $last,
			'specialization'   => $specialization ? $specialization : __( 'Gospel Music Instructor', 'gospel-music-mastery' ),
			'experience'       => $experience ? $experience : __( 'Experience on request', 'gospel-music-mastery' ),
			'bio'              => $bio,
			'bio_html'         => $bio ? wpautop( esc_html( $bio ) ) : '',
			'rating'           => $rating,
			'rating_stars'     => self::stars( $rating ),
			'rating_display'   => number_format_i18n( $rating, 1 ),
			'students'         => isset( $stats['students'] ) ? (int) $stats['students'] : 0,
			'classes'          => isset( $stats['classes'] ) ? (int) $stats['classes'] : 0,
			'image_url'        => $image,
			'video_url'        => $video_url,
			'video_poster'     => function_exists( 'gmm_design_asset_url' ) ? gmm_design_asset_url( 'assets/img/video/01.jpg' ) : '',
			'skills'           => $skills,
			'status'           => $status,
			'profile_url'      => self::profile_url( $teacher_id ),
			'booking_url'      => self::booking_url( $teacher_id ),
		);
	}

	/**
	 * Approved classes for teacher.
	 *
	 * @param int $teacher_id Teacher ID.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_classes( $teacher_id ) {
		$teacher_id = absint( $teacher_id );
		if ( ! $teacher_id ) {
			return array();
		}

		global $wpdb;
		$table = GMM_Database::table( 'classes' );
		$rows  = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE teacher_id = %d AND status IN ('approved','published','active')
				ORDER BY featured DESC, created_at DESC, id DESC
				LIMIT 24",
				$teacher_id
			),
			ARRAY_A
		);
		$rows = is_array( $rows ) ? $rows : array();

		$out = array();
		foreach ( $rows as $row ) {
			$image = '';
			if ( ! empty( $row['image'] ) && function_exists( 'gmm_get_media_url' ) ) {
				$image = gmm_get_media_url( $row['image'], 'medium' );
			}
			if ( ! $image ) {
				$image = function_exists( 'gmm_design_asset_url' )
					? gmm_design_asset_url( 'assets/img/course/01.jpg' )
					: '';
			}

			$id       = absint( $row['id'] );
			$rating   = isset( $row['rating'] ) ? (float) $row['rating'] : 0.0;
			$duration = isset( $row['duration'] ) ? absint( $row['duration'] ) : 0;
			$price    = isset( $row['price'] ) ? (float) $row['price'] : 0.0;
			$title    = isset( $row['title'] ) ? (string) $row['title'] : '';

			$out[] = array(
				'id'             => $id,
				'title'          => $title,
				'category'       => isset( $row['category'] ) ? (string) $row['category'] : '',
				'difficulty'     => isset( $row['difficulty'] ) ? (string) $row['difficulty'] : '',
				'duration'       => $duration,
				'duration_label' => $duration ? sprintf( /* translators: %d minutes */ __( '%d Minutes', 'gospel-music-mastery' ), $duration ) : '',
				'price'          => $price,
				'price_display'  => '$' . number_format_i18n( $price, 0 ),
				'rating'         => $rating,
				'rating_stars'   => self::stars( $rating ),
				'image_url'      => $image,
				'booking_url'    => self::booking_url( $teacher_id, $id ),
			);
		}

		return $out;
	}

	/**
	 * Approved reviews with student names.
	 *
	 * @param int                  $teacher_id Teacher ID.
	 * @param array<string, mixed> $args       Args.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_reviews( $teacher_id, $args = array() ) {
		$teacher_id = absint( $teacher_id );
		if ( ! $teacher_id || ! class_exists( 'GMM_Reviews' ) ) {
			return array();
		}

		$args = wp_parse_args(
			$args,
			array(
				'status' => 'approved',
				'limit'  => 12,
				'offset' => 0,
			)
		);

		$rows = GMM_Reviews::get_teacher_reviews( $teacher_id, $args );
		$out  = array();

		foreach ( $rows as $row ) {
			$student_id = isset( $row['student_id'] ) ? absint( $row['student_id'] ) : 0;
			$student    = self::get_student_public( $student_id );
			$rating     = isset( $row['rating'] ) ? (float) $row['rating'] : 0.0;
			$created    = isset( $row['created_at'] ) ? (string) $row['created_at'] : '';

			$out[] = array(
				'id'           => isset( $row['id'] ) ? absint( $row['id'] ) : 0,
				'student_name' => $student['name'],
				'student_image'=> $student['image_url'],
				'rating'       => $rating,
				'rating_stars' => self::stars( $rating ),
				'comment'      => isset( $row['comment'] ) ? (string) $row['comment'] : '',
				'date'         => $created,
				'date_display' => $created ? mysql2date( get_option( 'date_format' ), $created ) : '',
			);
		}

		return $out;
	}

	/**
	 * Rating summary (average + total). Optionally recalculate & persist.
	 *
	 * @param int  $teacher_id Teacher ID.
	 * @param bool $recalc     Recalculate from reviews.
	 * @return array{average:float,total:int}
	 */
	public static function get_rating_summary( $teacher_id, $recalc = false ) {
		$teacher_id = absint( $teacher_id );
		$empty      = array(
			'average' => 0.0,
			'total'   => 0,
		);
		if ( ! $teacher_id ) {
			return $empty;
		}

		if ( $recalc && class_exists( 'GMM_Reviews' ) ) {
			$avg = GMM_Reviews::calculate_teacher_rating( $teacher_id );
		} else {
			global $wpdb;
			$table = GMM_Database::table( 'teachers' );
			$avg   = (float) $wpdb->get_var(
				$wpdb->prepare( "SELECT rating FROM {$table} WHERE id = %d LIMIT 1", $teacher_id )
			);
		}

		$total = 0;
		if ( class_exists( 'GMM_Reviews' ) && method_exists( 'GMM_Reviews', 'count_teacher_reviews' ) ) {
			$total = (int) GMM_Reviews::count_teacher_reviews( $teacher_id );
		} else {
			global $wpdb;
			$reviews = GMM_Database::table( 'reviews' );
			$total   = (int) $wpdb->get_var(
				$wpdb->prepare(
					"SELECT COUNT(*) FROM {$reviews} WHERE teacher_id = %d AND status = %s",
					$teacher_id,
					'approved'
				)
			);
		}

		return array(
			'average' => round( (float) $avg, 2 ),
			'total'   => $total,
		);
	}

	/**
	 * Whether teacher is favourited by student.
	 *
	 * @param int $teacher_id Teacher ID.
	 * @param int $user_id    WP user ID.
	 * @return bool
	 */
	public static function is_favourite( $teacher_id, $user_id = 0 ) {
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
	 * Upcoming availability slots.
	 *
	 * @param int $teacher_id Teacher ID.
	 * @param int $limit      Max.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_upcoming_slots( $teacher_id, $limit = 6 ) {
		$teacher_id = absint( $teacher_id );
		$limit      = max( 1, min( 20, absint( $limit ) ) );
		if ( ! $teacher_id ) {
			return array();
		}

		global $wpdb;
		$table = GMM_Database::table( 'availability' );
		$today = current_time( 'Y-m-d' );

		$rows = $wpdb->get_results(
			$wpdb->prepare(
				"SELECT * FROM {$table}
				WHERE teacher_id = %d
				AND available_date >= %s
				AND status IN ('available','open','bookable')
				ORDER BY available_date ASC, start_time ASC
				LIMIT %d",
				$teacher_id,
				$today,
				$limit
			),
			ARRAY_A
		);
		$rows = is_array( $rows ) ? $rows : array();

		$out = array();
		foreach ( $rows as $row ) {
			$date = isset( $row['available_date'] ) ? (string) $row['available_date'] : '';
			$start = isset( $row['start_time'] ) ? (string) $row['start_time'] : '';
			$end   = isset( $row['end_time'] ) ? (string) $row['end_time'] : '';
			$date_label = $date ? mysql2date( 'l, F j, Y', $date . ' 00:00:00' ) : '';
			$start_label = $start ? date_i18n( 'g:i A', strtotime( $start ) ) : '';
			$end_label   = $end ? date_i18n( 'g:i A', strtotime( $end ) ) : '';

			$out[] = array(
				'id'          => isset( $row['id'] ) ? absint( $row['id'] ) : 0,
				'date'        => $date,
				'date_label'  => $date_label,
				'start_time'  => $start,
				'end_time'    => $end,
				'time_label'  => trim( $start_label . ( $end_label ? ' - ' . $end_label : '' ) ),
				'booking_url' => add_query_arg(
					array(
						'teacher_id' => $teacher_id,
						'date'       => $date,
						'time'       => $start_label,
					),
					self::booking_base_url()
				),
			);
		}

		return $out;
	}

	/**
	 * Related approved teachers.
	 *
	 * @param int $teacher_id Exclude ID.
	 * @param int $limit      Limit.
	 * @return array<int, array<string, mixed>>
	 */
	public static function get_related_teachers( $teacher_id, $limit = 3 ) {
		$teacher_id = absint( $teacher_id );
		$limit      = max( 1, min( 6, absint( $limit ) ) );

		$args = array(
			'public'   => true,
			'statuses' => array( 'approved', 'active' ),
			'sort'     => 'highest_rated',
			'per_page' => $limit + 3,
			'page'     => 1,
		);
		$result = class_exists( 'GMM_Search' ) ? GMM_Search::search_teachers( $args ) : array( 'items' => array() );
		$items  = isset( $result['items'] ) && is_array( $result['items'] ) ? $result['items'] : array();
		$out    = array();

		foreach ( $items as $row ) {
			$id = isset( $row['id'] ) ? absint( $row['id'] ) : 0;
			if ( ! $id || $id === $teacher_id ) {
				continue;
			}
			$card = class_exists( 'GMM_Teacher_Search' ) ? GMM_Teacher_Search::format_card( $row ) : null;
			if ( $card ) {
				$out[] = $card;
			}
			if ( count( $out ) >= $limit ) {
				break;
			}
		}

		return $out;
	}

	/**
	 * AJAX favourite toggle.
	 *
	 * @return void
	 */
	public function ajax_favourite() {
		check_ajax_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		if ( ! is_user_logged_in() || ! function_exists( 'gmm_is_student' ) || ! gmm_is_student() ) {
			wp_send_json_error( array( 'message' => __( 'Student login required.', 'gospel-music-mastery' ) ), 403 );
		}

		$teacher_id = isset( $_POST['teacher_id'] ) ? absint( $_POST['teacher_id'] ) : 0;
		if ( ! $teacher_id || ! self::get_profile( $teacher_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Teacher not found.', 'gospel-music-mastery' ) ), 404 );
		}

		if ( ! class_exists( 'GMM_Favourites' ) ) {
			wp_send_json_error( array( 'message' => __( 'Favourites unavailable.', 'gospel-music-mastery' ) ), 500 );
		}

		$is_fav = self::is_favourite( $teacher_id );
		$result = $is_fav
			? GMM_Favourites::remove_favourite( $teacher_id )
			: GMM_Favourites::add_favourite( $teacher_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		wp_send_json_success(
			array(
				'message'    => $is_fav
					? __( 'Removed from favourites.', 'gospel-music-mastery' )
					: __( 'Added to favourites.', 'gospel-music-mastery' ),
				'favourite'  => ! $is_fav,
				'teacher_id' => $teacher_id,
			)
		);
	}

	/**
	 * AJAX submit review.
	 *
	 * @return void
	 */
	public function ajax_review_submit() {
		check_ajax_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		if ( ! is_user_logged_in() || ! function_exists( 'gmm_is_student' ) || ! gmm_is_student() ) {
			wp_send_json_error( array( 'message' => __( 'Student login required.', 'gospel-music-mastery' ) ), 403 );
		}

		$data = array(
			'teacher_id' => isset( $_POST['teacher_id'] ) ? absint( $_POST['teacher_id'] ) : 0,
			'class_id'   => isset( $_POST['class_id'] ) ? absint( $_POST['class_id'] ) : 0,
			'booking_id' => isset( $_POST['booking_id'] ) ? absint( $_POST['booking_id'] ) : 0,
			'rating'     => isset( $_POST['rating'] ) ? absint( $_POST['rating'] ) : 0,
			'comment'    => isset( $_POST['comment'] ) ? sanitize_textarea_field( wp_unslash( $_POST['comment'] ) ) : '',
		);

		if ( ! self::get_profile( $data['teacher_id'] ) ) {
			wp_send_json_error( array( 'message' => __( 'Teacher not found.', 'gospel-music-mastery' ) ), 404 );
		}

		$result = function_exists( 'gmm_create_review' )
			? gmm_create_review( $data, '', get_current_user_id() )
			: new WP_Error( 'gmm_missing', __( 'Reviews unavailable.', 'gospel-music-mastery' ) );

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
				'message'   => __( 'Thank you! Your review was submitted for approval.', 'gospel-music-mastery' ),
				'review_id' => (int) $result,
			)
		);
	}

	/**
	 * AJAX load reviews.
	 *
	 * @return void
	 */
	public function ajax_reviews_load() {
		check_ajax_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$teacher_id = isset( $_REQUEST['teacher_id'] ) ? absint( $_REQUEST['teacher_id'] ) : 0;
		$page       = isset( $_REQUEST['page'] ) ? max( 1, absint( $_REQUEST['page'] ) ) : 1;
		$per_page   = isset( $_REQUEST['per_page'] ) ? min( 30, max( 1, absint( $_REQUEST['per_page'] ) ) ) : 12;
		$offset     = ( $page - 1 ) * $per_page;

		if ( ! self::get_profile( $teacher_id ) ) {
			wp_send_json_error( array( 'message' => __( 'Teacher not found.', 'gospel-music-mastery' ) ), 404 );
		}

		$reviews = self::get_reviews(
			$teacher_id,
			array(
				'limit'  => $per_page,
				'offset' => $offset,
			)
		);
		$summary = self::get_rating_summary( $teacher_id, false );

		$html = '';
		foreach ( $reviews as $review ) {
			$html .= self::render_review_card( $review );
		}

		wp_send_json_success(
			array(
				'message'  => __( 'Reviews loaded.', 'gospel-music-mastery' ),
				'items'    => $reviews,
				'html'     => $html,
				'summary'  => $summary,
				'page'     => $page,
				'per_page' => $per_page,
			)
		);
	}

	/**
	 * Enqueue assets.
	 *
	 * @return void
	 */
	public function maybe_enqueue_assets() {
		if ( ! class_exists( 'GMM_Assets' ) || ! GMM_Assets::is_gmm_page() ) {
			return;
		}
		$post    = get_queried_object();
		$content = ( $post instanceof WP_Post ) ? (string) $post->post_content : '';
		if ( ! has_shortcode( $content, 'gmm_teacher_public_profile' ) && false === strpos( $content, 'gmm_teacher_public_profile' ) ) {
			return;
		}

		$teacher_id = self::resolve_teacher_id();
		$version    = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';

		wp_enqueue_script(
			'gmm-teacher-public-profile',
			GMM_URL . 'assets/js/gmm-teacher-public-profile.js',
			array( 'gmm-core-script' ),
			$version,
			true
		);

		wp_localize_script(
			'gmm-teacher-public-profile',
			'GMM_TEACHER_PUBLIC',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( self::NONCE_ACTION ),
				'nonceField' => self::NONCE_FIELD,
				'teacherId'  => $teacher_id,
				'bookingUrl' => self::booking_base_url(),
				'actions'    => array(
					'favourite' => 'gmm_public_teacher_favourite',
					'review'    => 'gmm_public_teacher_review_submit',
					'reviews'   => 'gmm_public_teacher_reviews',
				),
				'i18n'       => array(
					'error'      => __( 'Something went wrong. Please try again.', 'gospel-music-mastery' ),
					'favAdd'     => __( 'Add Favourite', 'gospel-music-mastery' ),
					'favRemove'  => __( 'Remove Favourite', 'gospel-music-mastery' ),
					'loginFav'   => __( 'Log in as a student to save favourites.', 'gospel-music-mastery' ),
					'reviewOk'   => __( 'Thank you! Your review was submitted for approval.', 'gospel-music-mastery' ),
				),
			)
		);
	}

	/**
	 * SEO title parts.
	 *
	 * @param array<string, string> $parts Parts.
	 * @return array<string, string>
	 */
	public function filter_document_title( $parts ) {
		$seo = self::current_seo();
		if ( empty( $seo['title'] ) ) {
			return $parts;
		}
		$parts['title'] = $seo['title'];
		return $parts;
	}

	/**
	 * Output basic meta description for public profile.
	 *
	 * @return void
	 */
	public function output_seo_meta() {
		$seo = self::current_seo();
		if ( empty( $seo['description'] ) ) {
			return;
		}
		echo '<meta name="description" content="' . esc_attr( $seo['description'] ) . '" />' . "\n";
		if ( ! empty( $seo['json_ld'] ) ) {
			echo '<script type="application/ld+json">' . wp_json_encode( $seo['json_ld'] ) . '</script>' . "\n";
		}
	}

	/**
	 * Render one review card (frozen classes).
	 *
	 * @param array<string, mixed> $r Review.
	 * @return string
	 */
	public static function render_review_card( $r ) {
		$r = is_array( $r ) ? $r : array();
		ob_start();
		?>
                                <article class="tp-review-card">
                                    <div class="tp-review-head">
                                        <img src="<?php echo esc_url( isset( $r['student_image'] ) ? $r['student_image'] : '' ); ?>" alt="<?php echo esc_attr( isset( $r['student_name'] ) ? $r['student_name'] : '' ); ?>">
                                        <div>
                                            <h4><?php echo esc_html( isset( $r['student_name'] ) ? $r['student_name'] : '' ); ?></h4>
                                            <span class="td-rating"><?php echo esc_html( isset( $r['rating_stars'] ) ? $r['rating_stars'] : '' ); ?></span>
											<?php if ( ! empty( $r['date_display'] ) ) : ?>
                                            <small class="tp-review-date"><?php echo esc_html( $r['date_display'] ); ?></small>
											<?php endif; ?>
                                        </div>
                                    </div>
                                    <p><?php echo esc_html( isset( $r['comment'] ) ? $r['comment'] : '' ); ?></p>
                                </article>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * @param int $teacher_id Teacher ID.
	 * @return array{students:int,classes:int}
	 */
	private static function get_stats( $teacher_id ) {
		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$classes  = GMM_Database::table( 'classes' );

		$students = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(DISTINCT student_id) FROM {$bookings}
				WHERE teacher_id = %d AND booking_status IN ('confirmed','completed','upcoming') AND student_id > 0",
				$teacher_id
			)
		);
		$class_count = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$classes}
				WHERE teacher_id = %d AND status IN ('approved','published','active')",
				$teacher_id
			)
		);

		return array(
			'students' => $students,
			'classes'  => $class_count,
		);
	}

	/**
	 * @param int $student_id Student row ID.
	 * @return array{name:string,image_url:string}
	 */
	private static function get_student_public( $student_id ) {
		$fallback = array(
			'name'      => __( 'Student', 'gospel-music-mastery' ),
			'image_url' => function_exists( 'gmm_design_asset_url' ) ? gmm_design_asset_url( 'assets/img/testimonial/01.jpg' ) : '',
		);
		$student_id = absint( $student_id );
		if ( ! $student_id ) {
			return $fallback;
		}
		global $wpdb;
		$table = GMM_Database::table( 'students' );
		$row   = $wpdb->get_row(
			$wpdb->prepare( "SELECT first_name, last_name, profile_image FROM {$table} WHERE id = %d LIMIT 1", $student_id ),
			ARRAY_A
		);
		if ( ! is_array( $row ) ) {
			return $fallback;
		}
		$name = trim( ( isset( $row['first_name'] ) ? $row['first_name'] : '' ) . ' ' . ( isset( $row['last_name'] ) ? $row['last_name'] : '' ) );
		$img  = '';
		if ( ! empty( $row['profile_image'] ) && function_exists( 'gmm_get_media_url' ) ) {
			$img = gmm_get_media_url( $row['profile_image'], 'thumbnail' );
		}
		return array(
			'name'      => $name ? $name : $fallback['name'],
			'image_url' => $img ? $img : $fallback['image_url'],
		);
	}

	/**
	 * @param string $text Text.
	 * @return array<int, string>
	 */
	private static function parse_skills( $text ) {
		$parts = preg_split( '/[,;\|\/]+/', (string) $text );
		$parts = array_filter( array_map( 'trim', is_array( $parts ) ? $parts : array() ) );
		$out   = array();
		foreach ( $parts as $p ) {
			if ( strlen( $p ) < 3 || strlen( $p ) > 40 ) {
				continue;
			}
			$out[] = $p;
			if ( count( $out ) >= 8 ) {
				break;
			}
		}
		return $out;
	}

	/**
	 * @param float $rating Rating.
	 * @return string
	 */
	private static function stars( $rating ) {
		$full = min( 5, max( 0, (int) round( (float) $rating ) ) );
		return str_repeat( '★', $full ) . str_repeat( '☆', 5 - $full );
	}

	/**
	 * @param array<string, mixed> $profile Profile.
	 * @param array<string, mixed> $rating  Summary.
	 * @return array<string, mixed>
	 */
	private static function build_seo( $profile, $rating ) {
		$name = isset( $profile['name'] ) ? (string) $profile['name'] : '';
		$spec = isset( $profile['specialization'] ) ? (string) $profile['specialization'] : '';
		$bio  = isset( $profile['bio'] ) ? wp_strip_all_tags( (string) $profile['bio'] ) : '';
		$desc = $bio ? wp_trim_words( $bio, 30, '…' ) : sprintf(
			/* translators: 1: name, 2: specialization */
			__( '%1$s — %2$s on Gospel Music Mastery.', 'gospel-music-mastery' ),
			$name,
			$spec
		);

		$avg   = isset( $rating['average'] ) ? (float) $rating['average'] : 0.0;
		$total = isset( $rating['total'] ) ? (int) $rating['total'] : 0;

		$json = array(
			'@context' => 'https://schema.org',
			'@type'    => 'Person',
			'name'     => $name,
			'jobTitle' => $spec,
			'description' => $desc,
		);
		if ( $total > 0 && $avg > 0 ) {
			$json['aggregateRating'] = array(
				'@type'       => 'AggregateRating',
				'ratingValue' => $avg,
				'reviewCount' => $total,
				'bestRating'  => 5,
				'worstRating' => 1,
			);
		}

		return array(
			'title'       => $name ? ( $name . ' - ' . __( 'Teacher Profile', 'gospel-music-mastery' ) ) : '',
			'description' => $desc,
			'name'        => $name,
			'specialization' => $spec,
			'rating'      => $avg,
			'review_count'=> $total,
			'json_ld'     => $json,
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function current_seo() {
		static $seo = null;
		if ( null !== $seo ) {
			return $seo;
		}
		$seo = array();
		$post = get_queried_object();
		if ( ! ( $post instanceof WP_Post ) ) {
			return $seo;
		}
		$content = (string) $post->post_content;
		if ( ! has_shortcode( $content, 'gmm_teacher_public_profile' ) && false === strpos( $content, 'gmm_teacher_public_profile' ) ) {
			return $seo;
		}
		$vars = self::get_template_vars( self::resolve_teacher_id() );
		$seo  = isset( $vars['seo'] ) && is_array( $vars['seo'] ) ? $vars['seo'] : array();
		return $seo;
	}

	/**
	 * @param int $teacher_id Teacher ID.
	 * @param int $class_id   Optional class.
	 * @return string
	 */
	private static function booking_url( $teacher_id, $class_id = 0 ) {
		$args = array( 'teacher_id' => absint( $teacher_id ) );
		if ( $class_id ) {
			$args['class_id'] = absint( $class_id );
		}
		$base = self::booking_base_url();
		return $base ? add_query_arg( $args, $base ) : '';
	}

	/**
	 * @param int $teacher_id Teacher ID.
	 * @return string
	 */
	private static function profile_url( $teacher_id ) {
		$base = '';
		if ( function_exists( 'gmm_get_page_link' ) ) {
			$base = gmm_get_page_link( 'teacher_public_profile' );
		}
		return $base ? add_query_arg( 'teacher_id', absint( $teacher_id ), $base ) : home_url( '/' );
	}

	/**
	 * @return string
	 */
	private static function booking_base_url() {
		if ( function_exists( 'gmm_get_page_link' ) ) {
			$url = gmm_get_page_link( 'booking_form' );
			if ( $url ) {
				return $url;
			}
			$url = gmm_get_page_link( 'student_bookings' );
			if ( $url ) {
				return $url;
			}
		}
		return home_url( '/' );
	}

	/**
	 * @return string
	 */
	private static function teachers_url() {
		if ( function_exists( 'gmm_get_page_link' ) ) {
			$url = gmm_get_page_link( 'teachers' );
			if ( $url ) {
				return $url;
			}
		}
		return home_url( '/' );
	}
}
