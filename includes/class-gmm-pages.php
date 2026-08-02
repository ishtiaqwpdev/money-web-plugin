<?php
/**
 * Automatic WordPress page creation for GMM shortcodes.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Pages
 *
 * Creates required front-end pages once on activation. Never deletes pages.
 */
class GMM_Pages {

	/**
	 * Option key storing created page metadata.
	 *
	 * @var string
	 */
	const OPTION_KEY = 'gmm_created_pages';

	/**
	 * Create all required pages if missing.
	 *
	 * Safe to call multiple times — skips existing pages / option entries.
	 *
	 * @return array<string, array<string, mixed>> Updated gmm_created_pages map.
	 */
	public static function create_pages() {
		if ( ! self::current_user_can_create() ) {
			return self::get_stored_pages();
		}

		$definitions = self::get_page_definitions();
		$stored      = self::get_stored_pages();
		$changed     = false;

		foreach ( $definitions as $key => $def ) {
			// Already tracked and page still exists — skip.
			if ( isset( $stored[ $key ]['page_id'] ) ) {
				$existing_id = absint( $stored[ $key ]['page_id'] );
				if ( $existing_id && self::page_exists( $existing_id ) ) {
					continue;
				}
			}

			$page_id = self::find_existing_page_id( $def['slug'], $def['shortcode'] );

			if ( ! $page_id ) {
				$page_id = self::insert_page( $def );
			}

			if ( ! $page_id ) {
				continue;
			}

			$stored[ $key ] = array(
				'page_id'   => absint( $page_id ),
				'page_name' => sanitize_text_field( $def['title'] ),
				'shortcode' => sanitize_text_field( $def['shortcode'] ),
				'slug'      => sanitize_title( $def['slug'] ),
			);
			$changed = true;
		}

		if ( $changed || false === get_option( self::OPTION_KEY, false ) ) {
			update_option( self::OPTION_KEY, $stored, false );
		}

		/**
		 * Fires after GMM page creation/sync runs.
		 *
		 * @since 1.0.0
		 * @param array<string, array<string, mixed>> $stored Stored page map.
		 */
		do_action( 'gmm_pages_created', $stored );

		return $stored;
	}

	/**
	 * Retrieve stored page map.
	 *
	 * @return array<string, array<string, mixed>>
	 */
	public static function get_stored_pages() {
		$stored = get_option( self::OPTION_KEY, array() );
		return is_array( $stored ) ? $stored : array();
	}

	/**
	 * Get page ID for a logical key (e.g. student_dashboard).
	 *
	 * @param string $key Page definition key.
	 * @return int
	 */
	public static function get_page_id( $key ) {
		$stored = self::get_stored_pages();
		if ( ! isset( $stored[ $key ]['page_id'] ) ) {
			return 0;
		}
		return absint( $stored[ $key ]['page_id'] );
	}

	/**
	 * Get permalink for a logical page key.
	 *
	 * @param string $key Page definition key.
	 * @return string
	 */
	public static function get_page_url( $key ) {
		$page_id = self::get_page_id( $key );
		if ( ! $page_id ) {
			return '';
		}
		$url = get_permalink( $page_id );
		return $url ? esc_url_raw( $url ) : '';
	}

	/**
	 * Page definitions: title, slug, shortcode content.
	 *
	 * @return array<string, array<string, string>>
	 */
	public static function get_page_definitions() {
		$defs = array(
			// Student.
			'student_login'      => array(
				'title'     => 'Student Login',
				'slug'      => 'student-login',
				'shortcode' => '[gmm_student_login]',
			),
			'student_register'   => array(
				'title'     => 'Student Register',
				'slug'      => 'student-register',
				'shortcode' => '[gmm_student_register]',
			),
			'student_dashboard'  => array(
				'title'     => 'Student Dashboard',
				'slug'      => 'student-dashboard',
				'shortcode' => '[gmm_student_dashboard]',
			),
			'student_profile'    => array(
				'title'     => 'Student Profile',
				'slug'      => 'student-profile',
				'shortcode' => '[gmm_student_profile]',
			),
			'student_lessons'    => array(
				'title'     => 'Student Lessons',
				'slug'      => 'student-lessons',
				'shortcode' => '[gmm_student_lessons]',
			),
			'student_bookings'   => array(
				'title'     => 'Student Bookings',
				'slug'      => 'student-bookings',
				'shortcode' => '[gmm_student_bookings]',
			),
			'student_favourites' => array(
				'title'     => 'Student Favourite Teachers',
				'slug'      => 'student-favourite-teachers',
				'shortcode' => '[gmm_student_favourites]',
			),
			'student_payments'   => array(
				'title'     => 'Student Payments',
				'slug'      => 'student-payments',
				'shortcode' => '[gmm_student_payments]',
			),
			'student_settings'   => array(
				'title'     => 'Student Settings',
				'slug'      => 'student-settings',
				'shortcode' => '[gmm_student_settings]',
			),

			// Public.
			'teachers'           => array(
				'title'     => 'Find Teachers',
				'slug'      => 'teachers',
				'shortcode' => '[gmm_teacher_search]',
			),
			'teacher_public_profile' => array(
				'title'     => 'Teacher Profile',
				'slug'      => 'teacher-public-profile',
				'shortcode' => '[gmm_teacher_public_profile]',
			),
			'booking_form'       => array(
				'title'     => 'Book a Lesson',
				'slug'      => 'book-lesson',
				'shortcode' => '[gmm_booking_form]',
			),
			'class_search'       => array(
				'title'     => 'Find Classes',
				'slug'      => 'classes',
				'shortcode' => '[gmm_class_search]',
			),
			'program_search'     => array(
				'title'     => 'Programs',
				'slug'      => 'programs',
				'shortcode' => '[gmm_program_search]',
			),
			'reviews'            => array(
				'title'     => 'Reviews',
				'slug'      => 'reviews',
				'shortcode' => '[gmm_reviews]',
			),

			// Teacher.
			'teacher_login'        => array(
				'title'     => 'Teacher Login',
				'slug'      => 'teacher-login',
				'shortcode' => '[gmm_teacher_login]',
			),
			'teacher_register'     => array(
				'title'     => 'Teacher Register',
				'slug'      => 'teacher-register',
				'shortcode' => '[gmm_teacher_register]',
			),
			'teacher_dashboard'    => array(
				'title'     => 'Teacher Dashboard',
				'slug'      => 'teacher-dashboard',
				'shortcode' => '[gmm_teacher_dashboard]',
			),
			'teacher_profile'      => array(
				'title'     => 'Teacher Profile',
				'slug'      => 'teacher-profile',
				'shortcode' => '[gmm_teacher_profile]',
			),
			'teacher_classes'      => array(
				'title'     => 'Teacher Classes',
				'slug'      => 'teacher-classes',
				'shortcode' => '[gmm_teacher_classes]',
			),
			'teacher_bookings'     => array(
				'title'     => 'Teacher Bookings',
				'slug'      => 'teacher-bookings',
				'shortcode' => '[gmm_teacher_bookings]',
			),
			'teacher_availability' => array(
				'title'     => 'Teacher Availability',
				'slug'      => 'teacher-availability',
				'shortcode' => '[gmm_teacher_availability]',
			),
			'teacher_withdrawals'  => array(
				'title'     => 'Teacher Withdrawals',
				'slug'      => 'teacher-withdrawals',
				'shortcode' => '[gmm_teacher_withdrawals]',
			),
			'teacher_settings'     => array(
				'title'     => 'Teacher Settings',
				'slug'      => 'teacher-settings',
				'shortcode' => '[gmm_teacher_settings]',
			),

			// Admin (gmm- prefix avoids WP / generic "admin" slug clashes).
			'admin_dashboard' => array(
				'title'     => 'Admin Dashboard',
				'slug'      => 'gmm-admin-dashboard',
				'shortcode' => '[gmm_admin_dashboard]',
			),
			'admin_teachers'  => array(
				'title'     => 'Admin Teachers',
				'slug'      => 'gmm-admin-teachers',
				'shortcode' => '[gmm_admin_teachers]',
			),
			'admin_students'  => array(
				'title'     => 'Admin Students',
				'slug'      => 'gmm-admin-students',
				'shortcode' => '[gmm_admin_students]',
			),
			'admin_classes'   => array(
				'title'     => 'Admin Classes',
				'slug'      => 'gmm-admin-classes',
				'shortcode' => '[gmm_admin_classes]',
			),
			'admin_bookings'  => array(
				'title'     => 'Admin Bookings',
				'slug'      => 'gmm-admin-bookings',
				'shortcode' => '[gmm_admin_bookings]',
			),
			'admin_payments'  => array(
				'title'     => 'Admin Payments',
				'slug'      => 'gmm-admin-payments',
				'shortcode' => '[gmm_admin_payments]',
			),
			'admin_programs'  => array(
				'title'     => 'Admin Programs',
				'slug'      => 'gmm-admin-programs',
				'shortcode' => '[gmm_admin_programs]',
			),
			'admin_settings'  => array(
				'title'     => 'Admin Settings',
				'slug'      => 'gmm-admin-settings',
				'shortcode' => '[gmm_admin_settings]',
			),
		);

		/**
		 * Filter page definitions before creation.
		 *
		 * @since 1.0.0
		 * @param array<string, array<string, string>> $defs Definitions.
		 */
		return apply_filters( 'gmm_page_definitions', $defs );
	}

	/**
	 * Whether the current user may create pages (activation context).
	 *
	 * @return bool
	 */
	private static function current_user_can_create() {
		return current_user_can( 'publish_pages' ) || current_user_can( 'manage_options' );
	}

	/**
	 * Check if a published/draft page ID still exists.
	 *
	 * @param int $page_id Page ID.
	 * @return bool
	 */
	private static function page_exists( $page_id ) {
		$page = get_post( $page_id );
		return ( $page instanceof WP_Post && 'page' === $page->post_type && 'trash' !== $page->post_status );
	}

	/**
	 * Find an existing page by slug or shortcode content to avoid duplicates.
	 *
	 * @param string $slug      Desired slug.
	 * @param string $shortcode Shortcode body.
	 * @return int Page ID or 0.
	 */
	private static function find_existing_page_id( $slug, $shortcode ) {
		$slug = sanitize_title( $slug );

		$page = get_page_by_path( $slug, OBJECT, 'page' );
		if ( $page instanceof WP_Post ) {
			return (int) $page->ID;
		}

		// Match a page that already contains this exact shortcode.
		$query = new WP_Query(
			array(
				'post_type'              => 'page',
				'post_status'            => array( 'publish', 'draft', 'private' ),
				's'                      => $shortcode,
				'posts_per_page'         => 5,
				'no_found_rows'          => true,
				'update_post_meta_cache' => false,
				'update_post_term_cache' => false,
			)
		);

		if ( $query->have_posts() ) {
			foreach ( $query->posts as $post ) {
				if ( $post instanceof WP_Post && false !== strpos( $post->post_content, $shortcode ) ) {
					wp_reset_postdata();
					return (int) $post->ID;
				}
			}
		}

		wp_reset_postdata();
		return 0;
	}

	/**
	 * Insert a new WordPress page with the shortcode as content.
	 *
	 * @param array<string, string> $def Page definition.
	 * @return int New page ID or 0 on failure.
	 */
	private static function insert_page( $def ) {
		$title     = sanitize_text_field( $def['title'] );
		$slug      = sanitize_title( $def['slug'] );
		$shortcode = sanitize_text_field( $def['shortcode'] );

		// Content is a known plugin shortcode only — not user HTML.
		$content = $shortcode;

		$page_id = wp_insert_post(
			array(
				'post_title'     => $title,
				'post_name'      => $slug,
				'post_content'   => $content,
				'post_status'    => 'publish',
				'post_type'      => 'page',
				'post_author'    => get_current_user_id() ? get_current_user_id() : 1,
				'comment_status' => 'closed',
				'ping_status'    => 'closed',
			),
			true
		);

		if ( is_wp_error( $page_id ) || ! $page_id ) {
			return 0;
		}

		return (int) $page_id;
	}
}
