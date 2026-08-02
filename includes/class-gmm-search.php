<?php
/**
 * Search and filter system for Gospel Music Mastery.
 *
 * Custom tables use prepared $wpdb queries (not WP_Query).
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Search
 *
 * Teacher, class, program, and admin search/filter logic with pagination.
 */
class GMM_Search {

	const DEFAULT_PER_PAGE = 12;
	const MAX_PER_PAGE     = 100;

	/**
	 * Allowed teacher/class categories.
	 *
	 * @var array<int, string>
	 */
	const CATEGORIES = array(
		'piano',
		'vocals',
		'guitar',
		'drums',
		'music-theory',
		'theory',
	);

	/**
	 * Allowed difficulty levels.
	 *
	 * @var array<int, string>
	 */
	const DIFFICULTIES = array(
		'beginner',
		'intermediate',
		'advanced',
	);

	/**
	 * Register shortcodes (no templates/UI yet).
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_action( 'init', $instance, 'register_shortcodes', 20 );
	}

	/**
	 * Prepare search shortcodes.
	 *
	 * @return void
	 */
	public function register_shortcodes() {
		add_shortcode( 'gmm_teacher_search', array( $this, 'shortcode_teacher_search' ) );
		add_shortcode( 'gmm_class_search', array( $this, 'shortcode_class_search' ) );
		add_shortcode( 'gmm_program_search', array( $this, 'shortcode_program_search' ) );
	}

	/**
	 * [gmm_teacher_search] — data prepared; markup deferred.
	 *
	 * @param array<string, mixed>|string $atts Attributes.
	 * @return string
	 */
	public function shortcode_teacher_search( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'category' => '',
				'rating'   => '',
				'sort'     => 'newest',
				'per_page' => self::DEFAULT_PER_PAGE,
				'page'     => 1,
			),
			is_array( $atts ) ? $atts : array(),
			'gmm_teacher_search'
		);

		$result = self::search_teachers(
			array(
				'category' => $atts['category'],
				'rating'   => $atts['rating'],
				'sort'     => $atts['sort'],
				'per_page' => $atts['per_page'],
				'page'     => $atts['page'],
				'status'   => 'active',
				'public'   => true,
			)
		);

		$payload = apply_filters(
			'gmm_teacher_search_shortcode_data',
			array(
				'atts'   => $atts,
				'result' => $result,
			),
			$atts
		);

		return (string) apply_filters( 'gmm_teacher_search_shortcode_html', '', $payload );
	}

	/**
	 * [gmm_class_search]
	 *
	 * @param array<string, mixed>|string $atts Attributes.
	 * @return string
	 */
	public function shortcode_class_search( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'category'   => '',
				'difficulty' => '',
				'sort'       => 'newest',
				'per_page'   => self::DEFAULT_PER_PAGE,
				'page'       => 1,
			),
			is_array( $atts ) ? $atts : array(),
			'gmm_class_search'
		);

		$result = self::search_classes(
			array(
				'category'   => $atts['category'],
				'difficulty' => $atts['difficulty'],
				'sort'       => $atts['sort'],
				'per_page'   => $atts['per_page'],
				'page'       => $atts['page'],
				'status'     => 'published',
				'public'     => true,
			)
		);

		$payload = apply_filters(
			'gmm_class_search_shortcode_data',
			array(
				'atts'   => $atts,
				'result' => $result,
			),
			$atts
		);

		return (string) apply_filters( 'gmm_class_search_shortcode_html', '', $payload );
	}

	/**
	 * [gmm_program_search]
	 *
	 * @param array<string, mixed>|string $atts Attributes.
	 * @return string
	 */
	public function shortcode_program_search( $atts = array() ) {
		$atts = shortcode_atts(
			array(
				'category'   => '',
				'difficulty' => '',
				'featured'   => '',
				'status'     => 'published',
				'per_page'   => self::DEFAULT_PER_PAGE,
				'page'       => 1,
			),
			is_array( $atts ) ? $atts : array(),
			'gmm_program_search'
		);

		$result = self::search_programs(
			array(
				'category'   => $atts['category'],
				'difficulty' => $atts['difficulty'],
				'featured'   => $atts['featured'],
				'status'     => $atts['status'],
				'per_page'   => $atts['per_page'],
				'page'       => $atts['page'],
				'public'     => true,
			)
		);

		$payload = apply_filters(
			'gmm_program_search_shortcode_data',
			array(
				'atts'   => $atts,
				'result' => $result,
			),
			$atts
		);

		return (string) apply_filters( 'gmm_program_search_shortcode_html', '', $payload );
	}

	/**
	 * Search teachers.
	 *
	 * @param array<string, mixed> $args Search args.
	 * @return array<string, mixed> Paginated result.
	 */
	public static function search_teachers( $args = array() ) {
		$args = self::parse_common_args( $args );

		$cache_key = self::cache_key( 'teachers', $args );
		$cached    = self::get_cache( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;
		$teachers = GMM_Database::table( 'teachers' );
		$bookings = GMM_Database::table( 'bookings' );

		$where  = array( '1=1' );
		$params = array();

		if ( $args['status'] ) {
			$where[]  = 't.status = %s';
			$params[] = $args['status'];
		}

		if ( $args['search'] ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[]  = '(t.first_name LIKE %s OR t.last_name LIKE %s OR CONCAT(t.first_name, \' \', t.last_name) LIKE %s OR t.specialization LIKE %s OR t.experience LIKE %s OR t.bio LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		// Name / specialization / instrument / experience dedicated filters.
		if ( $args['name'] ) {
			$like     = '%' . $wpdb->esc_like( $args['name'] ) . '%';
			$where[]  = '(t.first_name LIKE %s OR t.last_name LIKE %s OR CONCAT(t.first_name, \' \', t.last_name) LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		if ( $args['specialization'] ) {
			$like     = '%' . $wpdb->esc_like( $args['specialization'] ) . '%';
			$where[]  = 't.specialization LIKE %s';
			$params[] = $like;
		}

		if ( $args['instrument'] ) {
			$like     = '%' . $wpdb->esc_like( $args['instrument'] ) . '%';
			$where[]  = '(t.specialization LIKE %s OR t.bio LIKE %s)';
			$params[] = $like;
			$params[] = $like;
		}

		if ( $args['experience'] ) {
			$like     = '%' . $wpdb->esc_like( $args['experience'] ) . '%';
			$where[]  = 't.experience LIKE %s';
			$params[] = $like;
		}

		if ( $args['category'] ) {
			$cat = self::normalize_category( $args['category'] );
			if ( $cat ) {
				$like  = '%' . $wpdb->esc_like( $cat ) . '%';
				$label = self::category_label( $cat );
				if ( $label && strtolower( $label ) !== $cat ) {
					$like2    = '%' . $wpdb->esc_like( $label ) . '%';
					$where[]  = '(t.specialization LIKE %s OR t.specialization LIKE %s)';
					$params[] = $like;
					$params[] = $like2;
				} else {
					$where[]  = 't.specialization LIKE %s';
					$params[] = $like;
				}
			}
		}

		$min_rating = self::parse_min_rating( $args['rating'] );
		if ( $min_rating > 0 ) {
			$where[]  = 't.rating >= %f';
			$params[] = $min_rating;
		}

		$where_sql = implode( ' AND ', $where );

		$count_sql = "SELECT COUNT(*) FROM {$teachers} t WHERE {$where_sql}";
		$total     = empty( $params )
			? (int) $wpdb->get_var( $count_sql ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			: (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$order = self::teacher_order_by( $args['sort'] );

		$select = "SELECT t.id, t.user_id, t.first_name, t.last_name, t.specialization, t.experience, t.rating, t.status, t.profile_image, t.bio, t.created_at,
			(SELECT COUNT(DISTINCT b.student_id) FROM {$bookings} b WHERE b.teacher_id = t.id AND b.booking_status IN ('confirmed','completed')) AS student_count";

		if ( empty( $args['public'] ) ) {
			$select = "SELECT t.id, t.user_id, t.first_name, t.last_name, t.email, t.phone, t.specialization, t.experience, t.rating, t.status, t.profile_image, t.bio, t.created_at,
				(SELECT COUNT(DISTINCT b.student_id) FROM {$bookings} b WHERE b.teacher_id = t.id AND b.booking_status IN ('confirmed','completed')) AS student_count";
		}

		$sql      = "{$select} FROM {$teachers} t WHERE {$where_sql} {$order} LIMIT %d OFFSET %d";
		$qparams  = $params;
		$qparams[] = $args['per_page'];
		$qparams[] = $args['offset'];

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $qparams ), ARRAY_A );
		$rows = is_array( $rows ) ? $rows : array();

		$result = self::paginate( $rows, $total, $args );
		self::set_cache( $cache_key, $result );

		/**
		 * Filter teacher search results (caching / UI hooks).
		 *
		 * @since 1.0.0
		 * @param array<string, mixed> $result Result.
		 * @param array<string, mixed> $args   Args.
		 */
		return apply_filters( 'gmm_search_teachers_result', $result, $args );
	}

	/**
	 * Search classes.
	 *
	 * @param array<string, mixed> $args Search args.
	 * @return array<string, mixed>
	 */
	public static function search_classes( $args = array() ) {
		$args = self::parse_common_args( $args );

		$cache_key = self::cache_key( 'classes', $args );
		$cached    = self::get_cache( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;
		$classes  = GMM_Database::table( 'classes' );
		$teachers = GMM_Database::table( 'teachers' );
		$bookings = GMM_Database::table( 'bookings' );

		$where  = array( '1=1' );
		$params = array();

		if ( $args['status'] ) {
			$where[]  = 'c.status = %s';
			$params[] = $args['status'];
		}

		if ( $args['search'] ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[]  = '(c.title LIKE %s OR c.description LIKE %s OR c.category LIKE %s OR t.first_name LIKE %s OR t.last_name LIKE %s OR CONCAT(t.first_name, \' \', t.last_name) LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		if ( $args['category'] ) {
			$cat = self::normalize_category( $args['category'] );
			if ( $cat ) {
				$label    = self::category_label( $cat );
				$where[]  = '(LOWER(c.category) = %s OR c.category = %s)';
				$params[] = $cat;
				$params[] = $label ? $label : $cat;
			}
		}

		if ( $args['difficulty'] ) {
			$diff = self::normalize_difficulty( $args['difficulty'] );
			if ( $diff ) {
				$where[]  = 'LOWER(c.difficulty) = %s';
				$params[] = $diff;
			}
		}

		$min_rating = self::parse_min_rating( $args['rating'] );
		if ( $min_rating > 0 ) {
			$where[]  = 'c.rating >= %f';
			$params[] = $min_rating;
		}

		if ( '' !== $args['price_min'] && null !== $args['price_min'] ) {
			$where[]  = 'c.price >= %f';
			$params[] = (float) $args['price_min'];
		}
		if ( '' !== $args['price_max'] && null !== $args['price_max'] ) {
			$where[]  = 'c.price <= %f';
			$params[] = (float) $args['price_max'];
		}

		$where_sql = implode( ' AND ', $where );

		$count_sql = "SELECT COUNT(*) FROM {$classes} c LEFT JOIN {$teachers} t ON t.id = c.teacher_id WHERE {$where_sql}";
		$total     = empty( $params )
			? (int) $wpdb->get_var( $count_sql ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			: (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$order = self::class_order_by( $args['sort'] );

		$sql = "SELECT c.id, c.teacher_id, c.title, c.description, c.category, c.difficulty, c.duration, c.price, c.rating, c.status, c.featured, c.image, c.created_at,
			t.first_name AS teacher_first_name, t.last_name AS teacher_last_name,
			(SELECT COUNT(*) FROM {$bookings} b WHERE b.class_id = c.id) AS booking_count
			FROM {$classes} c
			LEFT JOIN {$teachers} t ON t.id = c.teacher_id
			WHERE {$where_sql}
			{$order}
			LIMIT %d OFFSET %d";

		$qparams   = $params;
		$qparams[] = $args['per_page'];
		$qparams[] = $args['offset'];

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $qparams ), ARRAY_A );
		$rows = is_array( $rows ) ? $rows : array();

		$result = self::paginate( $rows, $total, $args );
		self::set_cache( $cache_key, $result );

		return apply_filters( 'gmm_search_classes_result', $result, $args );
	}

	/**
	 * Search programs.
	 *
	 * @param array<string, mixed> $args Search args.
	 * @return array<string, mixed>
	 */
	public static function search_programs( $args = array() ) {
		$args = self::parse_common_args( $args );

		$cache_key = self::cache_key( 'programs', $args );
		$cached    = self::get_cache( $cache_key );
		if ( is_array( $cached ) ) {
			return $cached;
		}

		global $wpdb;
		$table = GMM_Database::table( 'programs' );

		$where  = array( '1=1' );
		$params = array();

		if ( $args['status'] ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}

		if ( $args['search'] ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[]  = '(title LIKE %s OR description LIKE %s OR category LIKE %s OR difficulty LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		if ( $args['category'] ) {
			$cat = sanitize_text_field( $args['category'] );
			if ( $cat ) {
				$where[]  = 'category = %s';
				$params[] = $cat;
			}
		}

		if ( $args['difficulty'] ) {
			$diff = self::normalize_difficulty( $args['difficulty'] );
			if ( $diff ) {
				$where[]  = 'LOWER(difficulty) = %s';
				$params[] = $diff;
			}
		}

		if ( '' !== $args['featured'] && null !== $args['featured'] ) {
			$where[]  = 'featured = %d';
			$params[] = absint( $args['featured'] ) ? 1 : 0;
		}

		$where_sql = implode( ' AND ', $where );

		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total     = empty( $params )
			? (int) $wpdb->get_var( $count_sql ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			: (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$order = 'ORDER BY created_at DESC, id DESC';
		if ( 'featured' === $args['sort'] ) {
			$order = 'ORDER BY featured DESC, created_at DESC';
		} elseif ( 'title' === $args['sort'] ) {
			$order = 'ORDER BY title ASC';
		}

		$sql       = "SELECT * FROM {$table} WHERE {$where_sql} {$order} LIMIT %d OFFSET %d";
		$qparams   = $params;
		$qparams[] = $args['per_page'];
		$qparams[] = $args['offset'];

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $qparams ), ARRAY_A );
		$rows = is_array( $rows ) ? $rows : array();

		$result = self::paginate( $rows, $total, $args );
		self::set_cache( $cache_key, $result );

		return apply_filters( 'gmm_search_programs_result', $result, $args );
	}

	/**
	 * Admin filter: teachers.
	 *
	 * @param array<string, mixed> $args Args.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function admin_filter_teachers( $args = array() ) {
		if ( ! self::can_admin_filter() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Admin access required.', 'gospel-music-mastery' ) );
		}
		$args['public'] = false;
		return self::search_teachers( $args );
	}

	/**
	 * Admin filter: students.
	 *
	 * @param array<string, mixed> $args Args.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function admin_filter_students( $args = array() ) {
		if ( ! self::can_admin_filter() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Admin access required.', 'gospel-music-mastery' ) );
		}

		$args = self::parse_common_args( $args );

		global $wpdb;
		$table = GMM_Database::table( 'students' );

		$where  = array( '1=1' );
		$params = array();

		if ( $args['status'] ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}
		if ( $args['search'] ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[]  = '(first_name LIKE %s OR last_name LIKE %s OR email LIKE %s OR learning_level LIKE %s OR phone LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}
		self::append_date_filters( $where, $params, $args, 'created_at' );

		return self::run_simple_table_query( $table, $where, $params, $args, 'created_at DESC, id DESC' );
	}

	/**
	 * Admin filter: classes.
	 *
	 * @param array<string, mixed> $args Args.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function admin_filter_classes( $args = array() ) {
		if ( ! self::can_admin_filter() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Admin access required.', 'gospel-music-mastery' ) );
		}
		$args['public'] = false;
		return self::search_classes( $args );
	}

	/**
	 * Admin filter: bookings.
	 *
	 * @param array<string, mixed> $args Args.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function admin_filter_bookings( $args = array() ) {
		if ( ! self::can_admin_filter() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Admin access required.', 'gospel-music-mastery' ) );
		}

		$args = self::parse_common_args( $args );

		global $wpdb;
		$table = GMM_Database::table( 'bookings' );

		$where  = array( '1=1' );
		$params = array();

		if ( $args['status'] ) {
			$where[]  = 'booking_status = %s';
			$params[] = $args['status'];
		}
		if ( $args['search'] ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[]  = '(notes LIKE %s OR CAST(id AS CHAR) LIKE %s OR CAST(student_id AS CHAR) LIKE %s OR CAST(teacher_id AS CHAR) LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}
		if ( ! empty( $args['date'] ) ) {
			$where[]  = 'booking_date = %s';
			$params[] = self::sanitize_date( $args['date'] );
		}
		if ( ! empty( $args['date_from'] ) ) {
			$where[]  = 'booking_date >= %s';
			$params[] = self::sanitize_date( $args['date_from'] );
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where[]  = 'booking_date <= %s';
			$params[] = self::sanitize_date( $args['date_to'] );
		}

		return self::run_simple_table_query( $table, $where, $params, $args, 'booking_date DESC, id DESC' );
	}

	/**
	 * Admin filter: payments.
	 *
	 * @param array<string, mixed> $args Args.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function admin_filter_payments( $args = array() ) {
		if ( ! self::can_admin_filter() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Admin access required.', 'gospel-music-mastery' ) );
		}

		$args = self::parse_common_args( $args );

		global $wpdb;
		$table = GMM_Database::table( 'payments' );

		$where  = array( '1=1' );
		$params = array();

		if ( $args['status'] ) {
			$where[]  = 'payment_status = %s';
			$params[] = $args['status'];
		}
		if ( $args['search'] ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[]  = '(transaction_id LIKE %s OR payment_method LIKE %s OR CAST(id AS CHAR) LIKE %s OR CAST(booking_id AS CHAR) LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}
		self::append_date_filters( $where, $params, $args, 'created_at' );

		return self::run_simple_table_query( $table, $where, $params, $args, 'created_at DESC, id DESC' );
	}

	/**
	 * Admin filter: blog posts.
	 *
	 * @param array<string, mixed> $args Args.
	 * @return array<string, mixed>|WP_Error
	 */
	public static function admin_filter_blogs( $args = array() ) {
		if ( ! self::can_admin_filter() ) {
			return new WP_Error( 'gmm_forbidden', __( 'Admin access required.', 'gospel-music-mastery' ) );
		}

		$args = self::parse_common_args( $args );

		global $wpdb;
		$table = GMM_Database::table( 'blog_posts' );

		$where  = array( '1=1' );
		$params = array();

		if ( $args['status'] ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}
		if ( $args['category'] ) {
			$where[]  = 'category = %s';
			$params[] = sanitize_text_field( $args['category'] );
		}
		if ( $args['search'] ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[]  = '(title LIKE %s OR content LIKE %s OR category LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}
		self::append_date_filters( $where, $params, $args, 'created_at' );

		return self::run_simple_table_query( $table, $where, $params, $args, 'created_at DESC, id DESC' );
	}

	/**
	 * Public/admin blog search (published for public).
	 *
	 * @param array<string, mixed> $args Args.
	 * @return array<string, mixed>
	 */
	public static function search_blogs( $args = array() ) {
		$args = self::parse_common_args( $args );
		if ( ! empty( $args['public'] ) && ! $args['status'] ) {
			$args['status'] = 'published';
		}

		global $wpdb;
		$table = GMM_Database::table( 'blog_posts' );

		$where  = array( '1=1' );
		$params = array();

		if ( $args['status'] ) {
			$where[]  = 'status = %s';
			$params[] = $args['status'];
		}
		if ( $args['category'] ) {
			$where[]  = 'category = %s';
			$params[] = sanitize_text_field( $args['category'] );
		}
		if ( $args['search'] ) {
			$like     = '%' . $wpdb->esc_like( $args['search'] ) . '%';
			$where[]  = '(title LIKE %s OR content LIKE %s OR category LIKE %s)';
			$params[] = $like;
			$params[] = $like;
			$params[] = $like;
		}

		return self::run_simple_table_query( $table, $where, $params, $args, 'created_at DESC, id DESC' );
	}

	/**
	 * Parse shared search arguments + pagination.
	 *
	 * @param array<string, mixed> $args Raw args.
	 * @return array<string, mixed>
	 */
	public static function parse_common_args( $args ) {
		$args = is_array( $args ) ? $args : array();

		$page     = isset( $args['page'] ) ? max( 1, absint( $args['page'] ) ) : 1;
		$per_page = isset( $args['per_page'] ) ? absint( $args['per_page'] ) : 0;
		if ( ! $per_page && isset( $args['limit'] ) ) {
			$per_page = absint( $args['limit'] );
		}
		if ( $per_page < 1 ) {
			$per_page = self::DEFAULT_PER_PAGE;
		}
		if ( $per_page > self::MAX_PER_PAGE ) {
			$per_page = self::MAX_PER_PAGE;
		}

		$price_min = null;
		$price_max = null;
		if ( isset( $args['price_min'] ) && '' !== $args['price_min'] ) {
			$price_min = max( 0, (float) $args['price_min'] );
		}
		if ( isset( $args['price_max'] ) && '' !== $args['price_max'] ) {
			$price_max = max( 0, (float) $args['price_max'] );
		}

		$featured = null;
		if ( isset( $args['featured'] ) && '' !== $args['featured'] ) {
			$featured = absint( $args['featured'] ) ? 1 : 0;
		}

		return array(
			'search'         => isset( $args['search'] ) ? sanitize_text_field( (string) $args['search'] ) : '',
			'name'           => isset( $args['name'] ) ? sanitize_text_field( (string) $args['name'] ) : '',
			'specialization' => isset( $args['specialization'] ) ? sanitize_text_field( (string) $args['specialization'] ) : '',
			'instrument'     => isset( $args['instrument'] ) ? sanitize_text_field( (string) $args['instrument'] ) : '',
			'experience'     => isset( $args['experience'] ) ? sanitize_text_field( (string) $args['experience'] ) : '',
			'category'       => isset( $args['category'] ) ? sanitize_text_field( (string) $args['category'] ) : '',
			'difficulty'     => isset( $args['difficulty'] ) ? sanitize_text_field( (string) $args['difficulty'] ) : '',
			'rating'         => isset( $args['rating'] ) ? $args['rating'] : '',
			'status'         => isset( $args['status'] ) ? sanitize_key( (string) $args['status'] ) : '',
			'sort'           => isset( $args['sort'] ) ? sanitize_key( (string) $args['sort'] ) : 'newest',
			'date'           => isset( $args['date'] ) ? sanitize_text_field( (string) $args['date'] ) : '',
			'date_from'      => isset( $args['date_from'] ) ? sanitize_text_field( (string) $args['date_from'] ) : '',
			'date_to'        => isset( $args['date_to'] ) ? sanitize_text_field( (string) $args['date_to'] ) : '',
			'price_min'      => $price_min,
			'price_max'      => $price_max,
			'featured'       => $featured,
			'public'         => ! empty( $args['public'] ),
			'page'           => $page,
			'per_page'       => $per_page,
			'limit'          => $per_page,
			'offset'         => ( $page - 1 ) * $per_page,
		);
	}

	/**
	 * Build pagination payload.
	 *
	 * @param array<int, array<string, mixed>> $items Items.
	 * @param int                              $total Total count.
	 * @param array<string, mixed>             $args  Parsed args.
	 * @return array<string, mixed>
	 */
	public static function paginate( $items, $total, $args ) {
		$total       = max( 0, absint( $total ) );
		$per_page    = absint( $args['per_page'] );
		$page        = absint( $args['page'] );
		$total_pages = $per_page > 0 ? (int) ceil( $total / $per_page ) : 0;

		return array(
			'items'       => $items,
			'total'       => $total,
			'page'        => $page,
			'per_page'    => $per_page,
			'total_pages' => $total_pages,
			'has_prev'    => $page > 1,
			'has_next'    => $total_pages > 0 && $page < $total_pages,
			'prev_page'   => $page > 1 ? $page - 1 : null,
			'next_page'   => ( $total_pages > 0 && $page < $total_pages ) ? $page + 1 : null,
		);
	}

	/**
	 * Whether current user may use admin filters.
	 *
	 * @return bool
	 */
	private static function can_admin_filter() {
		return current_user_can( 'manage_options' );
	}

	/**
	 * Run count + select for a single table.
	 *
	 * @param string               $table  Full table name.
	 * @param array<int, string>   $where  Where parts.
	 * @param array<int, mixed>    $params Params.
	 * @param array<string, mixed> $args   Parsed args.
	 * @param string               $order  ORDER BY clause without keyword.
	 * @return array<string, mixed>
	 */
	private static function run_simple_table_query( $table, $where, $params, $args, $order ) {
		global $wpdb;

		$where_sql = implode( ' AND ', $where );
		$count_sql = "SELECT COUNT(*) FROM {$table} WHERE {$where_sql}";
		$total     = empty( $params )
			? (int) $wpdb->get_var( $count_sql ) // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
			: (int) $wpdb->get_var( $wpdb->prepare( $count_sql, $params ) ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

		$order = preg_replace( '/[^a-z0-9_\s,\.]+/i', '', $order );
		$sql   = "SELECT * FROM {$table} WHERE {$where_sql} ORDER BY {$order} LIMIT %d OFFSET %d";

		$qparams   = $params;
		$qparams[] = $args['per_page'];
		$qparams[] = $args['offset'];

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $qparams ), ARRAY_A );
		$rows = is_array( $rows ) ? $rows : array();

		return self::paginate( $rows, $total, $args );
	}

	/**
	 * Append created_at date filters.
	 *
	 * @param array<int, string>   $where  Where (by ref).
	 * @param array<int, mixed>    $params Params (by ref).
	 * @param array<string, mixed> $args   Args.
	 * @param string               $column Date column.
	 * @return void
	 */
	private static function append_date_filters( &$where, &$params, $args, $column ) {
		$column = preg_replace( '/[^a-z0-9_]/i', '', $column );
		if ( ! $column ) {
			return;
		}
		if ( ! empty( $args['date'] ) ) {
			$where[]  = "DATE({$column}) = %s";
			$params[] = self::sanitize_date( $args['date'] );
		}
		if ( ! empty( $args['date_from'] ) ) {
			$where[]  = "DATE({$column}) >= %s";
			$params[] = self::sanitize_date( $args['date_from'] );
		}
		if ( ! empty( $args['date_to'] ) ) {
			$where[]  = "DATE({$column}) <= %s";
			$params[] = self::sanitize_date( $args['date_to'] );
		}
	}

	/**
	 * Teacher ORDER BY.
	 *
	 * @param string $sort Sort key.
	 * @return string
	 */
	private static function teacher_order_by( $sort ) {
		switch ( sanitize_key( $sort ) ) {
			case 'highest_rated':
			case 'rating':
				return 'ORDER BY t.rating DESC, t.created_at DESC';
			case 'most_students':
				return 'ORDER BY student_count DESC, t.rating DESC';
			case 'newest':
			default:
				return 'ORDER BY t.created_at DESC, t.id DESC';
		}
	}

	/**
	 * Class ORDER BY (includes price low/high as sort).
	 *
	 * @param string $sort Sort key.
	 * @return string
	 */
	private static function class_order_by( $sort ) {
		switch ( sanitize_key( $sort ) ) {
			case 'price_asc':
			case 'price_low':
			case 'low_to_high':
				return 'ORDER BY c.price ASC, c.created_at DESC';
			case 'price_desc':
			case 'price_high':
			case 'high_to_low':
				return 'ORDER BY c.price DESC, c.created_at DESC';
			case 'popular':
				return 'ORDER BY booking_count DESC, c.rating DESC';
			case 'highest_rated':
			case 'rating':
				return 'ORDER BY c.rating DESC, c.created_at DESC';
			case 'newest':
			default:
				return 'ORDER BY c.created_at DESC, c.id DESC';
		}
	}

	/**
	 * Normalize category slug.
	 *
	 * @param string $category Raw.
	 * @return string
	 */
	private static function normalize_category( $category ) {
		$raw = strtolower( trim( (string) $category ) );
		$raw = str_replace( array( ' ', '_' ), '-', $raw );
		$map = array(
			'music-theory' => 'music-theory',
			'theory'       => 'theory',
			'piano'        => 'piano',
			'vocals'       => 'vocals',
			'vocal'        => 'vocals',
			'guitar'       => 'guitar',
			'drums'        => 'drums',
			'drum'         => 'drums',
		);
		if ( isset( $map[ $raw ] ) ) {
			return $map[ $raw ];
		}
		return in_array( $raw, self::CATEGORIES, true ) ? $raw : sanitize_title( $raw );
	}

	/**
	 * Human label for category matching stored values.
	 *
	 * @param string $slug Slug.
	 * @return string
	 */
	private static function category_label( $slug ) {
		$labels = array(
			'piano'        => 'Piano',
			'vocals'       => 'Vocals',
			'guitar'       => 'Guitar',
			'drums'        => 'Drums',
			'music-theory' => 'Music Theory',
			'theory'       => 'Theory',
		);
		return isset( $labels[ $slug ] ) ? $labels[ $slug ] : '';
	}

	/**
	 * Normalize difficulty.
	 *
	 * @param string $difficulty Raw.
	 * @return string
	 */
	private static function normalize_difficulty( $difficulty ) {
		$d = strtolower( trim( (string) $difficulty ) );
		return in_array( $d, self::DIFFICULTIES, true ) ? $d : '';
	}

	/**
	 * Parse min rating from "4", "4+", "4 Stars+", etc.
	 *
	 * @param mixed $rating Raw.
	 * @return float
	 */
	private static function parse_min_rating( $rating ) {
		if ( '' === $rating || null === $rating ) {
			return 0.0;
		}
		if ( is_numeric( $rating ) ) {
			$n = (float) $rating;
			return min( 5, max( 0, $n ) );
		}
		if ( preg_match( '/(\d+(?:\.\d+)?)/', (string) $rating, $m ) ) {
			return min( 5, max( 0, (float) $m[1] ) );
		}
		return 0.0;
	}

	/**
	 * Sanitize Y-m-d date.
	 *
	 * @param string $date Date.
	 * @return string
	 */
	private static function sanitize_date( $date ) {
		$date = sanitize_text_field( (string) $date );
		if ( preg_match( '/^\d{4}-\d{2}-\d{2}$/', $date ) ) {
			return $date;
		}
		$ts = strtotime( $date );
		return $ts ? gmdate( 'Y-m-d', $ts ) : '';
	}

	/**
	 * Build cache key (prepared for future object cache).
	 *
	 * @param string               $type Type.
	 * @param array<string, mixed> $args Args.
	 * @return string
	 */
	private static function cache_key( $type, $args ) {
		return 'gmm_search_' . sanitize_key( $type ) . '_' . md5( wp_json_encode( $args ) );
	}

	/**
	 * Get cached result if enabled.
	 *
	 * @param string $key Key.
	 * @return array<string, mixed>|null
	 */
	private static function get_cache( $key ) {
		/**
		 * Enable search result caching (off by default).
		 *
		 * @since 1.0.0
		 * @param bool   $enabled Whether caching is on.
		 * @param string $key     Cache key.
		 */
		if ( ! apply_filters( 'gmm_search_cache_enabled', false, $key ) ) {
			return null;
		}
		$cached = get_transient( $key );
		return is_array( $cached ) ? $cached : null;
	}

	/**
	 * Store cache when enabled.
	 *
	 * @param string               $key    Key.
	 * @param array<string, mixed> $result Result.
	 * @return void
	 */
	private static function set_cache( $key, $result ) {
		if ( ! apply_filters( 'gmm_search_cache_enabled', false, $key ) ) {
			return;
		}
		$ttl = (int) apply_filters( 'gmm_search_cache_ttl', 5 * MINUTE_IN_SECONDS, $key );
		set_transient( $key, $result, max( 30, $ttl ) );
	}
}
