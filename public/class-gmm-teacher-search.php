<?php
/**
 * Frontend teacher search & filter controller.
 *
 * Powers [gmm_teacher_search] → templates/public/teachers.php
 * without changing the frozen marketplace UI.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Teacher_Search
 */
class GMM_Teacher_Search {

	const NONCE_ACTION = 'gmm_teacher_search';
	const NONCE_FIELD  = 'gmm_teacher_search_nonce';
	const PER_PAGE     = 8;
	const CACHE_GROUP  = 'gmm_teacher_search';

	/**
	 * Register hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 25, 2 );
		$loader->add_action( 'wp_ajax_gmm_teacher_search', $instance, 'ajax_search' );
		$loader->add_action( 'wp_ajax_nopriv_gmm_teacher_search', $instance, 'ajax_search' );
		$loader->add_action( 'wp_enqueue_scripts', $instance, 'maybe_enqueue_assets', 40 );

		$loader->add_action( 'gmm_teacher_approved', $instance, 'flush_cache', 10, 0 );
		$loader->add_action( 'gmm_teacher_rejected', $instance, 'flush_cache', 10, 0 );
		$loader->add_action( 'gmm_student_profile_updated', $instance, 'flush_cache', 10, 0 );
	}

	/**
	 * Inject listing vars into [gmm_teacher_search].
	 *
	 * @param array<string, mixed> $args Args.
	 * @param string               $tag  Tag.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		if ( 'gmm_teacher_search' !== $tag ) {
			return $args;
		}
		return array_merge( is_array( $args ) ? $args : array(), self::get_template_vars() );
	}

	/**
	 * Template variables for the teachers marketplace.
	 *
	 * @param array<string, mixed> $request Optional request overrides.
	 * @return array<string, mixed>
	 */
	public static function get_template_vars( $request = array() ) {
		$filters = self::parse_request( $request ? $request : self::request_from_query() );
		$result  = self::search( $filters );
		$cards   = array();

		foreach ( ( isset( $result['items'] ) ? $result['items'] : array() ) as $row ) {
			$card = self::format_card( $row );
			if ( $card ) {
				$cards[] = $card;
			}
		}

		return array(
			'filters'        => $filters,
			'search_result'  => $result,
			'teachers'       => $cards,
			'total_teachers' => isset( $result['total'] ) ? (int) $result['total'] : 0,
			'pagination'     => array(
				'page'        => isset( $result['page'] ) ? (int) $result['page'] : 1,
				'total_pages' => isset( $result['total_pages'] ) ? (int) $result['total_pages'] : 0,
				'has_prev'    => ! empty( $result['has_prev'] ),
				'has_next'    => ! empty( $result['has_next'] ),
				'prev_page'   => isset( $result['prev_page'] ) ? $result['prev_page'] : null,
				'next_page'   => isset( $result['next_page'] ) ? $result['next_page'] : null,
			),
			'profile_base'   => self::profile_base_url(),
			'booking_base'   => self::booking_base_url(),
			'nonce'          => wp_create_nonce( self::NONCE_ACTION ),
		);
	}

	/**
	 * Run teacher search (approved only).
	 *
	 * @param array<string, mixed> $args Args.
	 * @return array<string, mixed>
	 */
	public static function search( $args = array() ) {
		$args = self::parse_request( $args );

		$search_args = array(
			'search'         => $args['search'],
			'name'           => $args['name'],
			'specialization' => $args['specialization'],
			'instrument'     => $args['instrument'],
			'instruments'    => $args['instruments'],
			'experience'     => $args['experience'],
			'category'       => $args['category'],
			'rating'         => $args['rating'],
			'sort'           => $args['sort'],
			'price_min'      => $args['price_min'],
			'price_max'      => $args['price_max'],
			'page'           => $args['page'],
			'per_page'       => $args['per_page'],
			'public'         => true,
			'statuses'       => array( 'approved', 'active' ),
		);

		if ( class_exists( 'GMM_Search' ) ) {
			$result = GMM_Search::search_teachers( $search_args );
		} else {
			$result = array(
				'items'       => array(),
				'total'       => 0,
				'page'        => 1,
				'per_page'    => self::PER_PAGE,
				'total_pages' => 0,
				'has_prev'    => false,
				'has_next'    => false,
				'prev_page'   => null,
				'next_page'   => null,
			);
		}

		return is_array( $result ) ? $result : array();
	}

	/**
	 * Format a DB row into card data for the frozen markup.
	 *
	 * @param array<string, mixed> $row Row.
	 * @return array<string, mixed>|null
	 */
	public static function format_card( $row ) {
		if ( ! is_array( $row ) || empty( $row['id'] ) ) {
			return null;
		}

		$status = isset( $row['status'] ) ? sanitize_key( (string) $row['status'] ) : '';
		if ( ! in_array( $status, array( 'approved', 'active' ), true ) ) {
			return null;
		}

		$first = isset( $row['first_name'] ) ? (string) $row['first_name'] : '';
		$last  = isset( $row['last_name'] ) ? (string) $row['last_name'] : '';
		$name  = trim( $first . ' ' . $last );
		if ( '' === $name ) {
			$name = __( 'Gospel Teacher', 'gospel-music-mastery' );
		}

		$specialization = isset( $row['specialization'] ) ? (string) $row['specialization'] : '';
		$experience     = isset( $row['experience'] ) ? (string) $row['experience'] : '';
		$rating         = isset( $row['rating'] ) ? (float) $row['rating'] : 0.0;
		$students       = isset( $row['student_count'] ) ? absint( $row['student_count'] ) : 0;
		$classes        = isset( $row['class_count'] ) ? absint( $row['class_count'] ) : 0;
		$min_price      = isset( $row['min_price'] ) && '' !== $row['min_price'] && null !== $row['min_price']
			? (float) $row['min_price']
			: null;

		$image = '';
		if ( ! empty( $row['profile_image'] ) && function_exists( 'gmm_get_media_url' ) ) {
			$image = gmm_get_media_url( $row['profile_image'], 'medium' );
		}
		if ( ! $image ) {
			$image = function_exists( 'gmm_design_asset_url' )
				? gmm_design_asset_url( 'assets/img/team/01.jpg' )
				: '';
		}

		$bio = isset( $row['bio'] ) ? wp_strip_all_tags( (string) $row['bio'] ) : '';
		$bio = $bio ? wp_trim_words( $bio, 18, '…' ) : '';

		$instrument = self::detect_instrument( $specialization . ' ' . $bio );
		$id         = absint( $row['id'] );
		$profile    = self::profile_url( $id );
		$booking    = self::booking_url( $id );
		$can_book   = class_exists( 'GMM_Admin_Teachers' )
			? GMM_Admin_Teachers::can_receive_bookings( $id )
			: true;

		return array(
			'id'             => $id,
			'name'           => $name,
			'first_name'     => $first,
			'last_name'      => $last,
			'specialization' => $specialization ? $specialization : __( 'Gospel Music Instructor', 'gospel-music-mastery' ),
			'category'       => $specialization ? $specialization : __( 'Gospel Music', 'gospel-music-mastery' ),
			'experience'     => $experience ? $experience : __( 'Experience available on profile', 'gospel-music-mastery' ),
			'rating'         => $rating,
			'rating_stars'   => self::rating_stars( $rating ),
			'rating_display' => number_format_i18n( $rating, 1 ),
			'students'       => $students,
			'classes'        => $classes,
			'price'          => $min_price,
			'price_display'  => null !== $min_price ? '$' . number_format_i18n( $min_price, 0 ) : '',
			'image_url'      => $image,
			'bio'            => $bio,
			'instrument'     => $instrument,
			'profile_url'    => $profile,
			'booking_url'    => $can_book ? $booking : '',
			'can_book'       => $can_book,
			'status'         => $status,
		);
	}

	/**
	 * Parse / sanitize request filters.
	 *
	 * @param array<string, mixed> $raw Raw.
	 * @return array<string, mixed>
	 */
	public static function parse_request( $raw = array() ) {
		$raw = is_array( $raw ) ? $raw : array();

		$instruments = array();
		if ( isset( $raw['instruments'] ) && is_array( $raw['instruments'] ) ) {
			$instruments = array_map( 'sanitize_text_field', $raw['instruments'] );
		} elseif ( isset( $raw['instrument'] ) ) {
			if ( is_array( $raw['instrument'] ) ) {
				$instruments = array_map( 'sanitize_text_field', $raw['instrument'] );
			} elseif ( '' !== (string) $raw['instrument'] ) {
				$instruments = array_map( 'trim', explode( ',', (string) $raw['instrument'] ) );
				$instruments = array_map( 'sanitize_text_field', $instruments );
			}
		}
		$instruments = array_values( array_unique( array_filter( $instruments ) ) );

		$allowed_inst = array( 'piano', 'guitar', 'vocals', 'drums', 'organ', 'theory', 'music-theory' );
		$instruments  = array_values(
			array_filter(
				$instruments,
				function ( $v ) use ( $allowed_inst ) {
					return in_array( strtolower( $v ), $allowed_inst, true );
				}
			)
		);

		// Selecting every instrument (or none) means "no instrument filter".
		$core_inst = array( 'piano', 'guitar', 'vocals', 'drums', 'organ', 'theory' );
		$selected  = array_map( 'strtolower', $instruments );
		$selected  = array_values( array_unique( array_map( function ( $v ) {
			return 'music-theory' === $v ? 'theory' : $v;
		}, $selected ) ) );
		sort( $selected );
		$all_sorted = $core_inst;
		sort( $all_sorted );
		if ( empty( $selected ) || $selected === $all_sorted ) {
			$instruments = array();
		}

		$sort = isset( $raw['sort'] ) ? sanitize_key( (string) $raw['sort'] ) : 'newest';
		$sort_map = array(
			'recommended'   => 'most_students',
			'rating'        => 'highest_rated',
			'highest_rated' => 'highest_rated',
			'newest'        => 'newest',
			'most_students' => 'most_students',
			'most_classes'  => 'most_classes',
			'price-low'     => 'price_low',
			'price_low'     => 'price_low',
			'price-high'    => 'price_high',
			'price_high'    => 'price_high',
		);
		$sort = isset( $sort_map[ $sort ] ) ? $sort_map[ $sort ] : 'newest';

		$experience = '';
		if ( ! empty( $raw['experience'] ) ) {
			$experience = sanitize_key( (string) $raw['experience'] );
		} elseif ( ! empty( $raw['level'] ) ) {
			$level = sanitize_key( (string) $raw['level'] );
			if ( 'beginner' === $level ) {
				$experience = 'beginner';
			} elseif ( in_array( $level, array( 'intermediate', 'advanced', 'experienced' ), true ) ) {
				$experience = 'experienced';
			}
		}

		$rating = isset( $raw['rating'] ) ? sanitize_text_field( (string) $raw['rating'] ) : '';
		if ( '' === $rating || '0' === $rating ) {
			$rating = '';
		}

		$price_max = null;
		if ( isset( $raw['price_max'] ) && '' !== $raw['price_max'] ) {
			$price_max = max( 0, (float) $raw['price_max'] );
		} elseif ( isset( $raw['price'] ) && '' !== $raw['price'] ) {
			$price_max = max( 0, (float) $raw['price'] );
		}

		$per_page = isset( $raw['per_page'] ) ? absint( $raw['per_page'] ) : self::PER_PAGE;
		if ( $per_page < 1 ) {
			$per_page = self::PER_PAGE;
		}
		$per_page = min( 48, $per_page );

		return array(
			'search'         => isset( $raw['search'] ) ? sanitize_text_field( (string) $raw['search'] ) : '',
			'name'           => isset( $raw['name'] ) ? sanitize_text_field( (string) $raw['name'] ) : '',
			'specialization' => isset( $raw['specialization'] ) ? sanitize_text_field( (string) $raw['specialization'] ) : '',
			'instrument'     => ! empty( $instruments ) ? $instruments[0] : '',
			'instruments'    => $instruments,
			'experience'     => $experience,
			'category'       => isset( $raw['category'] ) ? sanitize_text_field( (string) $raw['category'] ) : '',
			'rating'         => $rating,
			'sort'           => $sort,
			'price_min'      => null,
			'price_max'      => $price_max,
			'page'           => max( 1, absint( isset( $raw['page'] ) ? $raw['page'] : 1 ) ),
			'per_page'       => $per_page,
			'availability'   => isset( $raw['availability'] ) ? $raw['availability'] : array(),
		);
	}

	/**
	 * AJAX search / filter / sort / pagination.
	 *
	 * @return void
	 */
	public function ajax_search() {
		check_ajax_referer( self::NONCE_ACTION, self::NONCE_FIELD );

		$raw = wp_unslash( $_REQUEST ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( isset( $raw['instruments'] ) && is_string( $raw['instruments'] ) ) {
			$raw['instruments'] = array_filter( array_map( 'trim', explode( ',', $raw['instruments'] ) ) );
		}
		if ( isset( $raw['instrument'] ) && is_string( $raw['instrument'] ) && false !== strpos( $raw['instrument'], ',' ) ) {
			$raw['instruments'] = array_filter( array_map( 'trim', explode( ',', $raw['instrument'] ) ) );
		}

		$vars   = self::get_template_vars( $raw );
		$cards  = isset( $vars['teachers'] ) ? $vars['teachers'] : array();
		$result = isset( $vars['search_result'] ) ? $vars['search_result'] : array();

		$html = '';
		foreach ( $cards as $teacher ) {
			$html .= self::render_card_html( $teacher );
		}

		wp_send_json_success(
			array(
				'message'     => __( 'Teachers loaded.', 'gospel-music-mastery' ),
				'items'       => $cards,
				'html'        => $html,
				'total'       => isset( $result['total'] ) ? absint( $result['total'] ) : 0,
				'page'        => isset( $result['page'] ) ? absint( $result['page'] ) : 1,
				'per_page'    => isset( $result['per_page'] ) ? absint( $result['per_page'] ) : self::PER_PAGE,
				'total_pages' => isset( $result['total_pages'] ) ? absint( $result['total_pages'] ) : 0,
				'has_prev'    => ! empty( $result['has_prev'] ),
				'has_next'    => ! empty( $result['has_next'] ),
				'prev_page'   => isset( $result['prev_page'] ) ? $result['prev_page'] : null,
				'next_page'   => isset( $result['next_page'] ) ? $result['next_page'] : null,
				'pagination'  => self::render_pagination_html( $vars['pagination'] ),
			)
		);
	}

	/**
	 * Enqueue marketplace script.
	 *
	 * @return void
	 */
	public function maybe_enqueue_assets() {
		if ( ! class_exists( 'GMM_Assets' ) || ! GMM_Assets::is_gmm_page() ) {
			return;
		}

		$post    = get_queried_object();
		$content = ( $post instanceof WP_Post ) ? (string) $post->post_content : '';
		$needed  = has_shortcode( $content, 'gmm_teacher_search' )
			|| false !== strpos( $content, 'gmm_teacher_search' );

		if ( ! $needed ) {
			return;
		}

		$version = defined( 'GMM_VERSION' ) ? GMM_VERSION : '1.0.0';
		wp_enqueue_script(
			'gmm-teacher-search',
			GMM_URL . 'assets/js/gmm-teacher-search.js',
			array( 'gmm-core-script' ),
			$version,
			true
		);

		wp_localize_script(
			'gmm-teacher-search',
			'GMM_TEACHER_SEARCH',
			array(
				'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
				'nonce'      => wp_create_nonce( self::NONCE_ACTION ),
				'nonceField' => self::NONCE_FIELD,
				'action'     => 'gmm_teacher_search',
				'perPage'    => self::PER_PAGE,
				'i18n'       => array(
					'error'   => __( 'Could not load teachers. Please try again.', 'gospel-music-mastery' ),
					'found'   => __( 'teachers found', 'gospel-music-mastery' ),
					'loading' => __( 'Searching…', 'gospel-music-mastery' ),
				),
			)
		);
	}

	/**
	 * Flush transient search cache.
	 *
	 * @return void
	 */
	public function flush_cache() {
		delete_transient( 'gmm_search_teachers_flush' );
		/**
		 * Allow object-cache backends to clear teacher search entries.
		 */
		do_action( 'gmm_teacher_search_cache_flush' );
	}

	/**
	 * Render one tm-card (frozen classes).
	 *
	 * @param array<string, mixed> $t Card.
	 * @return string
	 */
	public static function render_card_html( $t ) {
		$t = is_array( $t ) ? $t : array();
		$name = isset( $t['name'] ) ? $t['name'] : '';
		$spec = isset( $t['specialization'] ) ? $t['specialization'] : '';
		$img  = isset( $t['image_url'] ) ? $t['image_url'] : '';
		$stars = isset( $t['rating_stars'] ) ? $t['rating_stars'] : '★★★★★';
		$rating = isset( $t['rating_display'] ) ? $t['rating_display'] : '0.0';
		$students = isset( $t['students'] ) ? (int) $t['students'] : 0;
		$exp = isset( $t['experience'] ) ? $t['experience'] : '';
		$price = isset( $t['price_display'] ) ? $t['price_display'] : '';
		$profile = isset( $t['profile_url'] ) ? $t['profile_url'] : '#';
		$booking = isset( $t['booking_url'] ) ? $t['booking_url'] : '';
		$inst = isset( $t['instrument'] ) ? $t['instrument'] : '';
		$rid = isset( $t['rating'] ) ? (int) round( (float) $t['rating'] ) : 0;
		$price_num = isset( $t['price'] ) && null !== $t['price'] ? (float) $t['price'] : 0;

		ob_start();
		?>
                            <article class="tm-card" data-name="<?php echo esc_attr( strtolower( $name ) ); ?>" data-specialization="<?php echo esc_attr( strtolower( $spec ) ); ?>"
                                data-instrument="<?php echo esc_attr( $inst ); ?>" data-level="" data-price="<?php echo esc_attr( (string) $price_num ); ?>" data-rating="<?php echo esc_attr( (string) $rid ); ?>">
                                <div class="tm-card-media">
                                    <img src="<?php echo esc_url( $img ); ?>" alt="<?php echo esc_attr( $name ); ?>">
                                </div>
                                <div class="tm-card-body">
                                    <h3><?php echo esc_html( $name ); ?></h3>
                                    <p class="tm-specialty"><?php echo esc_html( $spec ); ?></p>
                                    <div class="tm-card-meta">
                                        <span class="tm-rating"><?php echo esc_html( $stars ); ?> <strong><?php echo esc_html( $rating ); ?></strong></span>
                                        <span><i class="far fa-users"></i> <?php echo esc_html( (string) $students ); ?> Students</span>
                                        <span><i class="far fa-briefcase"></i> <?php echo esc_html( $exp ); ?></span>
                                    </div>
                                    <div class="tm-card-footer">
										<?php if ( $price ) : ?>
                                        <strong class="tm-price"><?php echo esc_html( $price ); ?> <small>/ Lesson</small></strong>
										<?php else : ?>
                                        <strong class="tm-price"><small><?php esc_html_e( 'See profile', 'gospel-music-mastery' ); ?></small></strong>
										<?php endif; ?>
                                        <div class="tm-card-actions">
                                            <a href="<?php echo esc_url( $profile ); ?>" class="theme-btn theme-btn-outline">View Profile</a>
											<?php if ( $booking ) : ?>
                                            <a href="<?php echo esc_url( $booking ); ?>" class="theme-btn">Book Lesson</a>
											<?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </article>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * Pagination HTML (frozen pagination classes).
	 *
	 * @param array<string, mixed> $p Pagination.
	 * @return string
	 */
	public static function render_pagination_html( $p ) {
		$p = is_array( $p ) ? $p : array();
		$page = isset( $p['page'] ) ? max( 1, (int) $p['page'] ) : 1;
		$total = isset( $p['total_pages'] ) ? max( 0, (int) $p['total_pages'] ) : 0;
		if ( $total < 2 ) {
			return '';
		}

		$start = max( 1, $page - 2 );
		$end   = min( $total, $page + 2 );

		ob_start();
		?>
                        <nav aria-label="Teachers pagination" id="tm-pagination">
                            <ul class="pagination">
                                <li class="page-item<?php echo empty( $p['has_prev'] ) ? ' disabled' : ''; ?>">
                                    <a class="page-link" href="#" data-page="<?php echo esc_attr( (string) ( ! empty( $p['prev_page'] ) ? $p['prev_page'] : 1 ) ); ?>" aria-label="Previous"><i class="far fa-angle-left"></i></a>
                                </li>
								<?php for ( $i = $start; $i <= $end; $i++ ) : ?>
                                <li class="page-item<?php echo (int) $i === (int) $page ? ' active' : ''; ?>">
                                    <a class="page-link" href="#" data-page="<?php echo esc_attr( (string) $i ); ?>"><?php echo esc_html( (string) $i ); ?></a>
                                </li>
								<?php endfor; ?>
                                <li class="page-item<?php echo empty( $p['has_next'] ) ? ' disabled' : ''; ?>">
                                    <a class="page-link" href="#" data-page="<?php echo esc_attr( (string) ( ! empty( $p['next_page'] ) ? $p['next_page'] : $total ) ); ?>" aria-label="Next"><i class="far fa-angle-right"></i></a>
                                </li>
                            </ul>
                        </nav>
		<?php
		return (string) ob_get_clean();
	}

	/**
	 * @return array<string, mixed>
	 */
	private static function request_from_query() {
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$get = wp_unslash( $_GET );
		return is_array( $get ) ? $get : array();
	}

	/**
	 * @param float $rating Rating.
	 * @return string
	 */
	private static function rating_stars( $rating ) {
		$full = (int) round( (float) $rating );
		$full = min( 5, max( 0, $full ) );
		return str_repeat( '★', $full ) . str_repeat( '☆', 5 - $full );
	}

	/**
	 * @param string $haystack Text.
	 * @return string
	 */
	private static function detect_instrument( $haystack ) {
		$hay = strtolower( (string) $haystack );
		$map = array( 'piano', 'guitar', 'vocals', 'vocal', 'drums', 'organ', 'theory' );
		foreach ( $map as $key ) {
			if ( false !== strpos( $hay, $key ) ) {
				return 'vocal' === $key ? 'vocals' : $key;
			}
		}
		return '';
	}

	/**
	 * @param int $teacher_id ID.
	 * @return string
	 */
	private static function profile_url( $teacher_id ) {
		$base = self::profile_base_url();
		return $base ? add_query_arg( 'teacher_id', absint( $teacher_id ), $base ) : '#';
	}

	/**
	 * @param int $teacher_id ID.
	 * @return string
	 */
	private static function booking_url( $teacher_id ) {
		$base = self::booking_base_url();
		return $base ? add_query_arg( 'teacher_id', absint( $teacher_id ), $base ) : '';
	}

	/**
	 * @return string
	 */
	private static function profile_base_url() {
		if ( function_exists( 'gmm_get_page_link' ) ) {
			$url = gmm_get_page_link( 'teacher_public_profile' );
			if ( $url ) {
				return $url;
			}
			$url = gmm_get_page_link( 'booking_form' );
			if ( $url ) {
				return $url;
			}
		}
		return home_url( '/' );
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
		return '';
	}
}
