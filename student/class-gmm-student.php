<?php
/**
 * Student profile and dashboard data.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Student
 *
 * Student-owned data access for gmm_students and shortcode data injection.
 */
class GMM_Student {

	/**
	 * Register shortcode data hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();
		$loader->add_filter( 'gmm_shortcode_template_args', $instance, 'inject_shortcode_args', 10, 2 );
	}

	/**
	 * Inject student template variables. UI unchanged.
	 *
	 * @param array<string, mixed> $args Template args.
	 * @param string               $tag  Shortcode tag.
	 * @return array<string, mixed>
	 */
	public function inject_shortcode_args( $args, $tag ) {
		$student_tags = array(
			'gmm_student_dashboard',
			'gmm_student_profile',
			'gmm_student_lessons',
			'gmm_student_bookings',
			'gmm_student_favourites',
			'gmm_student_payments',
			'gmm_student_settings',
		);

		if ( ! in_array( $tag, $student_tags, true ) ) {
			return $args;
		}

		$user_id = get_current_user_id();
		$profile = gmm_get_student_profile( $user_id );

		$args['student_profile'] = is_array( $profile ) ? $profile : array();
		$args['user_name']       = self::format_display_name( $profile );
		$args['user_first_name'] = isset( $profile['first_name'] ) && $profile['first_name']
			? $profile['first_name']
			: ( isset( $args['user_first_name'] ) ? $args['user_first_name'] : '' );

		switch ( $tag ) {
			case 'gmm_student_dashboard':
				// GMM_Student_Dashboard injects full payload at priority 25.
				if ( ! class_exists( 'GMM_Student_Dashboard' ) ) {
					$args['dashboard_data'] = gmm_get_student_dashboard_data( $user_id );
				}
				break;
			case 'gmm_student_lessons':
				$args['lessons'] = class_exists( 'GMM_Student_Lessons' )
					? GMM_Student_Lessons::get_student_lessons( $user_id )
					: array();
				break;
			case 'gmm_student_bookings':
				$args['bookings'] = class_exists( 'GMM_Student_Bookings' )
					? GMM_Student_Bookings::get_bookings( $user_id )
					: array();
				break;
			case 'gmm_student_favourites':
				$args['favourites'] = class_exists( 'GMM_Favourites' )
					? GMM_Favourites::get_favourite_teachers( $user_id )
					: array();
				break;
			case 'gmm_student_payments':
				// Enriched by GMM_Student_Payments::inject_shortcode_args (priority 30).
				break;
		}

		return $args;
	}

	/**
	 * Get student profile by WP user ID.
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, mixed>|null
	 */
	public static function get_profile( $user_id = 0 ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}
		if ( ! $user_id || ! self::can_view_profile( $user_id ) ) {
			return null;
		}

		global $wpdb;
		$table = GMM_Database::table( 'students' );

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE user_id = %d LIMIT 1", $user_id ),
			ARRAY_A
		);

		return is_array( $row ) ? $row : null;
	}

	/**
	 * gmm_students.id for a WP user.
	 *
	 * @param int $user_id WP user ID.
	 * @return int
	 */
	public static function get_student_id( $user_id = 0 ) {
		$profile = self::get_profile( $user_id );
		return ( $profile && isset( $profile['id'] ) ) ? absint( $profile['id'] ) : 0;
	}

	/**
	 * Update student profile (owner or admin).
	 *
	 * @param int                  $user_id WP user ID.
	 * @param array<string, mixed> $data    Fields.
	 * @return true|WP_Error
	 */
	public static function update_profile( $user_id = 0, $data = array() ) {
		$user_id = absint( $user_id );
		if ( ! $user_id ) {
			$user_id = get_current_user_id();
		}

		if ( ! $user_id || ! is_user_logged_in() ) {
			return new WP_Error( 'gmm_not_logged_in', __( 'You must be logged in.', 'gospel-music-mastery' ) );
		}

		if ( ! self::can_edit_profile( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot edit this student profile.', 'gospel-music-mastery' ) );
		}

		if ( ! current_user_can( 'manage_gmm_profile' ) && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_cap', __( 'Missing profile capability.', 'gospel-music-mastery' ) );
		}

		$clean = self::sanitize_profile_fields( is_array( $data ) ? $data : array() );
		if ( empty( $clean ) ) {
			return new WP_Error( 'gmm_no_fields', __( 'No valid profile fields to update.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table    = GMM_Database::table( 'students' );
		$existing = $wpdb->get_var(
			$wpdb->prepare( "SELECT id FROM {$table} WHERE user_id = %d LIMIT 1", $user_id )
		);
		$now = current_time( 'mysql' );

		if ( $existing ) {
			$clean['updated_at'] = $now;
			$updated             = $wpdb->update(
				$table,
				$clean,
				array( 'user_id' => $user_id ),
				null,
				array( '%d' )
			);
			if ( false === $updated ) {
				return new WP_Error( 'gmm_db_error', __( 'Could not update student profile.', 'gospel-music-mastery' ) );
			}
			return true;
		}

		$insert = array_merge(
			array(
				'user_id'               => $user_id,
				'first_name'            => '',
				'last_name'             => '',
				'email'                 => '',
				'phone'                 => '',
				'profile_image'         => '',
				'learning_level'        => '',
				'learning_goals'        => '',
				'preferred_instruments' => '',
				'bio'                   => '',
				'status'                => 'active',
				'created_at'            => $now,
				'updated_at'            => $now,
			),
			$clean
		);

		if ( ! current_user_can( 'manage_options' ) ) {
			$insert['status'] = 'active';
		}

		if ( ! $wpdb->insert( $table, $insert ) ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not create student profile.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * Dashboard aggregates (templates unchanged).
	 *
	 * @param int $user_id WP user ID.
	 * @return array<string, mixed>
	 */
	public static function get_dashboard_data( $user_id = 0 ) {
		$empty = array(
			'total_lessons'       => 0,
			'upcoming_lessons'    => 0,
			'completed_lessons'   => 0,
			'favourite_teachers'  => 0,
			'total_payments'      => 0.0,
		);

		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		if ( ! $user_id || ! self::can_view_profile( $user_id ) ) {
			return $empty;
		}

		$student_id = self::get_student_id( $user_id );
		if ( ! $student_id ) {
			return $empty;
		}

		global $wpdb;
		$bookings_t = GMM_Database::table( 'bookings' );
		$payments_t = GMM_Database::table( 'payments' );
		$fav_t      = GMM_Database::table( 'favourites' );
		$today      = current_time( 'Y-m-d' );

		$total_lessons = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$bookings_t} WHERE student_id = %d", $student_id )
		);

		$upcoming = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$bookings_t}
				WHERE student_id = %d
				AND booking_date >= %s
				AND booking_status IN ('pending','confirmed','upcoming')",
				$student_id,
				$today
			)
		);

		$completed = (int) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COUNT(*) FROM {$bookings_t}
				WHERE student_id = %d AND booking_status = %s",
				$student_id,
				'completed'
			)
		);

		$favourites = (int) $wpdb->get_var(
			$wpdb->prepare( "SELECT COUNT(*) FROM {$fav_t} WHERE student_id = %d", $student_id )
		);

		$total_payments = (float) $wpdb->get_var(
			$wpdb->prepare(
				"SELECT COALESCE(SUM(amount),0) FROM {$payments_t}
				WHERE student_id = %d AND payment_status IN ('completed','paid','success')",
				$student_id
			)
		);

		return array(
			'total_lessons'      => $total_lessons,
			'upcoming_lessons'   => $upcoming,
			'completed_lessons'  => $completed,
			'favourite_teachers' => $favourites,
			'total_payments'     => round( $total_payments, 2 ),
		);
	}

	/**
	 * @param int $user_id Target user.
	 * @return bool
	 */
	public static function can_view_profile( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id || ! is_user_logged_in() ) {
			return false;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		return ( get_current_user_id() === $user_id )
			&& ( gmm_is_student( $user_id ) || current_user_can( 'manage_gmm_profile' ) );
	}

	/**
	 * @param int $user_id Target user.
	 * @return bool
	 */
	public static function can_edit_profile( $user_id ) {
		$user_id = absint( $user_id );
		if ( ! $user_id || ! is_user_logged_in() ) {
			return false;
		}
		if ( current_user_can( 'manage_options' ) ) {
			return true;
		}
		return ( get_current_user_id() === $user_id )
			&& gmm_is_student( $user_id )
			&& current_user_can( 'manage_gmm_profile' );
	}

	/**
	 * @param array<string, mixed> $data Raw.
	 * @return array<string, mixed>
	 */
	private static function sanitize_profile_fields( $data ) {
		$clean = array();

		$map = array(
			'first_name'     => 'sanitize_text_field',
			'last_name'      => 'sanitize_text_field',
			'phone'          => 'sanitize_text_field',
			'learning_level' => 'sanitize_text_field',
		);

		foreach ( $map as $key => $cb ) {
			if ( array_key_exists( $key, $data ) ) {
				$clean[ $key ] = call_user_func( $cb, (string) $data[ $key ] );
			}
		}

		if ( array_key_exists( 'profile_image', $data ) ) {
			$raw = (string) $data['profile_image'];
			if ( '' === $raw ) {
				$clean['profile_image'] = '';
			} elseif ( absint( $raw ) ) {
				$clean['profile_image'] = (string) absint( $raw );
			} else {
				$clean['profile_image'] = esc_url_raw( $raw );
			}
		}

		if ( array_key_exists( 'email', $data ) ) {
			$email = sanitize_email( (string) $data['email'] );
			if ( is_email( $email ) ) {
				$clean['email'] = $email;
			}
		}

		if ( array_key_exists( 'learning_goals', $data ) ) {
			$clean['learning_goals'] = sanitize_textarea_field( (string) $data['learning_goals'] );
		}
		if ( array_key_exists( 'preferred_instruments', $data ) ) {
			$clean['preferred_instruments'] = sanitize_textarea_field( (string) $data['preferred_instruments'] );
		}
		if ( array_key_exists( 'bio', $data ) ) {
			$clean['bio'] = wp_kses_post( (string) $data['bio'] );
		}

		if ( current_user_can( 'manage_options' ) && array_key_exists( 'status', $data ) ) {
			$status = sanitize_key( (string) $data['status'] );
			if ( in_array( $status, array( 'active', 'inactive', 'suspended', 'pending' ), true ) ) {
				$clean['status'] = $status;
			}
		}

		return $clean;
	}

	/**
	 * @param array<string, mixed>|null $profile Profile.
	 * @return string
	 */
	private static function format_display_name( $profile ) {
		if ( ! is_array( $profile ) ) {
			$user = wp_get_current_user();
			return $user->exists() ? $user->display_name : 'Student';
		}
		$name = trim(
			( isset( $profile['first_name'] ) ? $profile['first_name'] : '' ) . ' ' .
			( isset( $profile['last_name'] ) ? $profile['last_name'] : '' )
		);
		if ( $name ) {
			return $name;
		}
		return isset( $profile['email'] ) && $profile['email'] ? $profile['email'] : 'Student';
	}
}
