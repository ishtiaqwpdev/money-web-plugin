<?php
/**
 * AJAX request handlers for Gospel Music Mastery.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Ajax
 *
 * Registers secure wp_ajax_* actions. Returns JSON only.
 */
class GMM_Ajax {

	/**
	 * Nonce action for check_ajax_referer (matches GMM_DATA.nonce).
	 *
	 * @var string
	 */
	const NONCE_ACTION = 'gmm_nonce';

	/**
	 * Register AJAX hooks.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		// Search (logged-in).
		$loader->add_action( 'wp_ajax_gmm_search_teachers', $instance, 'search_teachers' );
		$loader->add_action( 'wp_ajax_gmm_search_students', $instance, 'search_students' );
		$loader->add_action( 'wp_ajax_gmm_search_classes', $instance, 'search_classes' );
		$loader->add_action( 'wp_ajax_gmm_search_programs', $instance, 'search_programs' );
		$loader->add_action( 'wp_ajax_gmm_search_blogs', $instance, 'search_blogs' );

		// Public teacher/class/program/blog search (directory).
		$loader->add_action( 'wp_ajax_nopriv_gmm_search_teachers', $instance, 'search_teachers_public' );
		$loader->add_action( 'wp_ajax_nopriv_gmm_search_classes', $instance, 'search_classes_public' );
		$loader->add_action( 'wp_ajax_nopriv_gmm_search_programs', $instance, 'search_programs' );
		$loader->add_action( 'wp_ajax_nopriv_gmm_search_blogs', $instance, 'search_blogs' );

		// Admin table filters.
		$loader->add_action( 'wp_ajax_gmm_filter_teachers', $instance, 'filter_teachers' );
		$loader->add_action( 'wp_ajax_gmm_filter_students', $instance, 'filter_students' );
		$loader->add_action( 'wp_ajax_gmm_filter_classes', $instance, 'filter_classes' );
		$loader->add_action( 'wp_ajax_gmm_filter_bookings', $instance, 'filter_bookings' );
		$loader->add_action( 'wp_ajax_gmm_filter_payments', $instance, 'filter_payments' );
		$loader->add_action( 'wp_ajax_gmm_filter_blogs', $instance, 'filter_blogs' );

		// Admin teacher actions.
		$loader->add_action( 'wp_ajax_gmm_approve_teacher', $instance, 'approve_teacher' );
		$loader->add_action( 'wp_ajax_gmm_reject_teacher', $instance, 'reject_teacher' );
		$loader->add_action( 'wp_ajax_gmm_suspend_teacher', $instance, 'suspend_teacher' );
		$loader->add_action( 'wp_ajax_gmm_delete_teacher', $instance, 'delete_teacher' );
		$loader->add_action( 'wp_ajax_gmm_bulk_teacher_action', $instance, 'bulk_teacher_action' );
		$loader->add_action( 'wp_ajax_gmm_get_teacher_profile', $instance, 'get_teacher_profile' );
		$loader->add_action( 'wp_ajax_gmm_update_teacher_status', $instance, 'update_teacher_status' );

		// Student actions.
		$loader->add_action( 'wp_ajax_gmm_update_student_profile', $instance, 'update_student_profile' );
		$loader->add_action( 'wp_ajax_gmm_update_student_status', $instance, 'update_student_status' );
		$loader->add_action( 'wp_ajax_gmm_admin_edit_student', $instance, 'admin_edit_student' );
		$loader->add_action( 'wp_ajax_gmm_delete_student', $instance, 'delete_student' );
		$loader->add_action( 'wp_ajax_gmm_bulk_student_action', $instance, 'bulk_student_action' );
		$loader->add_action( 'wp_ajax_gmm_get_student_profile', $instance, 'get_student_profile' );
		$loader->add_action( 'wp_ajax_gmm_toggle_favourite', $instance, 'toggle_favourite' );
		$loader->add_action( 'wp_ajax_gmm_student_cancel_booking', $instance, 'student_cancel_booking' );

		// Booking actions.
		$loader->add_action( 'wp_ajax_gmm_create_booking', $instance, 'create_booking' );
		$loader->add_action( 'wp_ajax_gmm_confirm_booking', $instance, 'confirm_booking' );
		$loader->add_action( 'wp_ajax_gmm_cancel_booking', $instance, 'cancel_booking' );
		$loader->add_action( 'wp_ajax_gmm_complete_booking', $instance, 'complete_booking' );

		// Admin content / payment.
		$loader->add_action( 'wp_ajax_gmm_approve_class', $instance, 'approve_class' );
		$loader->add_action( 'wp_ajax_gmm_reject_class', $instance, 'reject_class' );
		$loader->add_action( 'wp_ajax_gmm_admin_edit_class', $instance, 'admin_edit_class' );
		$loader->add_action( 'wp_ajax_gmm_toggle_class_featured', $instance, 'toggle_class_featured' );
		$loader->add_action( 'wp_ajax_gmm_delete_class', $instance, 'delete_class' );
		$loader->add_action( 'wp_ajax_gmm_bulk_class_action', $instance, 'bulk_class_action' );
		$loader->add_action( 'wp_ajax_gmm_get_class_profile', $instance, 'get_class_profile' );
		$loader->add_action( 'wp_ajax_gmm_delete_content', $instance, 'delete_content' );
		$loader->add_action( 'wp_ajax_gmm_update_payment_status', $instance, 'update_payment_status' );
	}

	/* -------------------------------------------------------------------------
	 * Search
	 * ---------------------------------------------------------------------- */

	/**
	 * @return void
	 */
	public function search_teachers() {
		$this->verify_request();
		if ( ! is_user_logged_in() ) {
			$this->deny();
		}
		$args = $this->get_search_args();
		// Non-admins only receive public teacher fields (no email/phone/user_id).
		if ( ! current_user_can( 'manage_options' ) ) {
			$args['public'] = true;
			if ( empty( $args['status'] ) ) {
				$args['status'] = 'active';
			}
		}
		$this->send_search_result( gmm_search_teachers( $args ), __( 'Teachers loaded.', 'gospel-music-mastery' ) );
	}

	/**
	 * Public teacher search (active only).
	 *
	 * @return void
	 */
	public function search_teachers_public() {
		$this->verify_request();
		$args           = $this->get_search_args();
		$args['status'] = 'active';
		$args['public'] = true;
		$this->send_search_result( gmm_search_teachers( $args ), __( 'Teachers loaded.', 'gospel-music-mastery' ) );
	}

	/**
	 * @return void
	 */
	public function search_students() {
		$this->verify_request();
		$this->require_admin();
		$result = gmm_admin_filter_students( $this->get_search_args() );
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 403 );
		}
		$this->send_search_result( $result, __( 'Students loaded.', 'gospel-music-mastery' ) );
	}

	/**
	 * @return void
	 */
	public function search_classes() {
		$this->verify_request();
		if ( ! is_user_logged_in() ) {
			$this->deny();
		}
		$args = $this->get_search_args();
		if ( ! current_user_can( 'manage_options' ) ) {
			$args['public']   = true;
			$args['statuses'] = function_exists( 'gmm_public_class_statuses' )
				? gmm_public_class_statuses()
				: array( 'approved', 'published' );
			unset( $args['status'] );
		}
		$this->send_search_result( gmm_search_classes( $args ), __( 'Classes loaded.', 'gospel-music-mastery' ) );
	}

	/**
	 * @return void
	 */
	public function search_classes_public() {
		$this->verify_request();
		$args             = $this->get_search_args();
		$args['statuses'] = function_exists( 'gmm_public_class_statuses' )
			? gmm_public_class_statuses()
			: array( 'approved', 'published' );
		unset( $args['status'] );
		$args['public'] = true;
		$this->send_search_result( gmm_search_classes( $args ), __( 'Classes loaded.', 'gospel-music-mastery' ) );
	}

	/**
	 * @return void
	 */
	public function search_programs() {
		$this->verify_request();
		$args = $this->get_search_args();
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			if ( empty( $args['status'] ) ) {
				$args['status'] = 'published';
			}
			$args['public'] = true;
		}
		$this->send_search_result( gmm_search_programs( $args ), __( 'Programs loaded.', 'gospel-music-mastery' ) );
	}

	/**
	 * @return void
	 */
	public function search_blogs() {
		$this->verify_request();
		$args = $this->get_search_args();
		if ( ! is_user_logged_in() || ! current_user_can( 'manage_options' ) ) {
			$args['public'] = true;
			if ( empty( $args['status'] ) ) {
				$args['status'] = 'published';
			}
		}
		$this->send_search_result( GMM_Search::search_blogs( $args ), __( 'Blog posts loaded.', 'gospel-music-mastery' ) );
	}

	/* -------------------------------------------------------------------------
	 * Filters (admin)
	 * ---------------------------------------------------------------------- */

	/**
	 * @return void
	 */
	public function filter_teachers() {
		$this->verify_request();
		$this->require_admin();

		$args = $this->get_filter_args();

		// Prefer admin teachers controller (UI status + email search + class counts).
		if ( class_exists( 'GMM_Admin_Teachers' ) ) {
			$list_args = array(
				'search'    => isset( $args['search'] ) ? $args['search'] : '',
				'status'    => ! empty( $args['status'] ) ? $args['status'] : 'all',
				'specialty' => ! empty( $args['category'] ) ? $args['category'] : ( ! empty( $args['specialization'] ) ? $args['specialization'] : 'all' ),
				'page'      => isset( $args['page'] ) ? absint( $args['page'] ) : 1,
				'per_page'  => isset( $args['per_page'] ) ? absint( $args['per_page'] ) : GMM_Admin_Teachers::PER_PAGE,
			);
			if ( ! empty( $args['specialty'] ) ) {
				$list_args['specialty'] = sanitize_key( (string) $args['specialty'] );
			}
			$result = GMM_Admin_Teachers::list_teachers( $list_args );
			$this->send_admin_filter_result( $result );
			return;
		}

		$result = gmm_admin_filter_teachers( $args );
		$this->send_admin_filter_result( $result );
	}

	/**
	 * @return void
	 */
	public function filter_students() {
		$this->verify_request();
		$this->require_admin();

		$args = $this->get_filter_args();

		if ( class_exists( 'GMM_Admin_Students' ) ) {
			$list_args = array(
				'search'   => isset( $args['search'] ) ? $args['search'] : '',
				'status'   => ! empty( $args['status'] ) ? $args['status'] : 'all',
				'level'    => ! empty( $args['level'] ) ? $args['level'] : ( ! empty( $args['difficulty'] ) ? $args['difficulty'] : 'all' ),
				'period'   => ! empty( $args['period'] ) ? $args['period'] : 'all',
				'page'     => isset( $args['page'] ) ? absint( $args['page'] ) : 1,
				'per_page' => isset( $args['per_page'] ) ? absint( $args['per_page'] ) : GMM_Admin_Students::PER_PAGE,
			);
			$result = GMM_Admin_Students::list_students( $list_args );
			$this->send_admin_filter_result( $result );
			return;
		}

		$result = gmm_admin_filter_students( $args );
		$this->send_admin_filter_result( $result );
	}

	/**
	 * @return void
	 */
	public function filter_classes() {
		$this->verify_request();
		$this->require_admin();

		$args = $this->get_filter_args();
		if ( class_exists( 'GMM_Admin_Classes' ) ) {
			$list_args = array(
				'search'     => isset( $args['search'] ) ? $args['search'] : '',
				'status'     => ! empty( $args['status'] ) ? $args['status'] : 'all',
				'category'   => ! empty( $args['category'] ) ? $args['category'] : 'all',
				'difficulty' => ! empty( $args['difficulty'] ) ? $args['difficulty'] : 'all',
				'page'       => isset( $args['page'] ) ? absint( $args['page'] ) : 1,
				'per_page'   => isset( $args['per_page'] ) ? absint( $args['per_page'] ) : GMM_Admin_Classes::PER_PAGE,
			);
			$result = GMM_Admin_Classes::list_classes( $list_args );
			$this->send_admin_filter_result( $result );
			return;
		}

		$result = gmm_admin_filter_classes( $args );
		$this->send_admin_filter_result( $result );
	}

	/**
	 * @return void
	 */
	public function filter_bookings() {
		$this->verify_request();
		$this->require_admin();
		$result = gmm_admin_filter_bookings( $this->get_filter_args() );
		$this->send_admin_filter_result( $result );
	}

	/**
	 * @return void
	 */
	public function filter_payments() {
		$this->verify_request();
		$this->require_admin();
		$result = gmm_admin_filter_payments( $this->get_filter_args() );
		$this->send_admin_filter_result( $result );
	}

	/**
	 * @return void
	 */
	public function filter_blogs() {
		$this->verify_request();
		$this->require_admin();
		$result = gmm_admin_filter_blogs( $this->get_filter_args() );
		$this->send_admin_filter_result( $result );
	}

	/* -------------------------------------------------------------------------
	 * Teacher admin actions
	 * ---------------------------------------------------------------------- */

	/**
	 * @return void
	 */
	public function approve_teacher() {
		$this->verify_request();
		$this->require_admin();
		$result = class_exists( 'GMM_Admin_Teachers' )
			? GMM_Admin_Teachers::approve( absint( $this->post( 'teacher_id' ) ) )
			: $this->set_teacher_status( absint( $this->post( 'teacher_id' ) ), 'active' );
		$this->send_result( $result, __( 'Teacher approved.', 'gospel-music-mastery' ) );
	}

	/**
	 * @return void
	 */
	public function reject_teacher() {
		$this->verify_request();
		$this->require_admin();
		$reason = sanitize_textarea_field( (string) $this->post( 'reason' ) );
		$result = class_exists( 'GMM_Admin_Teachers' )
			? GMM_Admin_Teachers::reject( absint( $this->post( 'teacher_id' ) ), $reason )
			: $this->set_teacher_status( absint( $this->post( 'teacher_id' ) ), 'rejected' );
		$this->send_result( $result, __( 'Teacher rejected.', 'gospel-music-mastery' ) );
	}

	/**
	 * @return void
	 */
	public function suspend_teacher() {
		$this->verify_request();
		$this->require_admin();
		$result = class_exists( 'GMM_Admin_Teachers' )
			? GMM_Admin_Teachers::suspend( absint( $this->post( 'teacher_id' ) ) )
			: $this->set_teacher_status( absint( $this->post( 'teacher_id' ) ), 'suspended' );
		$this->send_result( $result, __( 'Teacher suspended.', 'gospel-music-mastery' ) );
	}

	/**
	 * Soft-delete teacher (requires confirm=1). Does not delete WP user by default.
	 *
	 * @return void
	 */
	public function delete_teacher() {
		$this->verify_request();
		$this->require_admin();
		if ( '1' !== (string) $this->post( 'confirm' ) ) {
			wp_send_json_error( array( 'message' => __( 'Confirmation required.', 'gospel-music-mastery' ) ), 400 );
		}
		$result = class_exists( 'GMM_Admin_Teachers' )
			? GMM_Admin_Teachers::delete_teacher( absint( $this->post( 'teacher_id' ) ) )
			: new WP_Error( 'gmm_missing', __( 'Teacher management unavailable.', 'gospel-music-mastery' ) );
		$this->send_result( $result, __( 'Teacher deleted.', 'gospel-music-mastery' ) );
	}

	/**
	 * Bulk approve / reject / suspend.
	 *
	 * @return void
	 */
	public function bulk_teacher_action() {
		$this->verify_request();
		$this->require_admin();

		$action = sanitize_key( (string) $this->post( 'bulk_action' ) );
		$reason = sanitize_textarea_field( (string) $this->post( 'reason' ) );
		$ids    = $this->post( 'teacher_ids' );
		if ( ! is_array( $ids ) ) {
			$ids = array_filter( array_map( 'absint', explode( ',', (string) $ids ) ) );
		}

		$result = class_exists( 'GMM_Admin_Teachers' )
			? GMM_Admin_Teachers::bulk_action( $ids, $action, $reason )
			: new WP_Error( 'gmm_missing', __( 'Teacher management unavailable.', 'gospel-music-mastery' ) );

		$this->send_result( $result, __( 'Bulk action completed.', 'gospel-music-mastery' ) );
	}

	/**
	 * Admin teacher profile detail.
	 *
	 * @return void
	 */
	public function get_teacher_profile() {
		$this->verify_request();
		$this->require_admin();

		$profile = class_exists( 'GMM_Admin_Teachers' )
			? GMM_Admin_Teachers::get_profile( absint( $this->post( 'teacher_id' ) ) )
			: new WP_Error( 'gmm_missing', __( 'Teacher management unavailable.', 'gospel-music-mastery' ) );

		if ( is_wp_error( $profile ) ) {
			wp_send_json_error( array( 'message' => $profile->get_error_message() ), 400 );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Teacher profile loaded.', 'gospel-music-mastery' ),
				'profile' => $profile,
			)
		);
	}

	/**
	 * @return void
	 */
	public function update_teacher_status() {
		$this->verify_request();
		$this->require_admin();
		$status = sanitize_key( (string) $this->post( 'status' ) );

		// Map UI labels to DB values.
		if ( 'approved' === $status ) {
			$status = 'active';
		}

		$allowed = array( 'pending', 'active', 'approved', 'inactive', 'rejected', 'suspended' );
		if ( ! in_array( $status, $allowed, true ) ) {
			wp_send_json_error( array( 'message' => __( 'Invalid status.', 'gospel-music-mastery' ) ), 400 );
		}

		$result = class_exists( 'GMM_Admin_Teachers' )
			? GMM_Admin_Teachers::set_status( absint( $this->post( 'teacher_id' ) ), $status )
			: $this->set_teacher_status( absint( $this->post( 'teacher_id' ) ), $status );
		$this->send_result( $result, __( 'Teacher status updated.', 'gospel-music-mastery' ) );
	}

	/* -------------------------------------------------------------------------
	 * Student actions
	 * ---------------------------------------------------------------------- */

	/**
	 * @return void
	 */
	public function update_student_profile() {
		$this->verify_request();
		if ( ! is_user_logged_in() || ( ! gmm_is_student() && ! current_user_can( 'manage_options' ) ) ) {
			$this->deny();
		}

		$user_id = get_current_user_id();
		$data    = array(
			'first_name'            => $this->post( 'first_name' ),
			'last_name'             => $this->post( 'last_name' ),
			'email'                 => $this->post( 'email' ),
			'phone'                 => $this->post( 'phone' ),
			'learning_level'        => $this->post( 'learning_level' ),
			'preferred_instruments' => $this->post( 'preferred_instruments' ),
			'bio'                   => $this->post( 'bio' ),
		);

		$result = function_exists( 'gmm_update_student_profile' )
			? gmm_update_student_profile( $user_id, $data )
			: new WP_Error( 'gmm_missing', __( 'Student system unavailable.', 'gospel-music-mastery' ) );

		$this->send_result( $result, __( 'Profile updated successfully.', 'gospel-music-mastery' ) );
	}

	/**
	 * Admin: update student status (activate / deactivate / suspend).
	 *
	 * @return void
	 */
	public function update_student_status() {
		$this->verify_request();
		$this->require_admin();
		$status = sanitize_key( (string) $this->post( 'status' ) );
		$result = class_exists( 'GMM_Admin_Students' )
			? GMM_Admin_Students::set_status( absint( $this->post( 'student_id' ) ), $status )
			: new WP_Error( 'gmm_missing', __( 'Student management unavailable.', 'gospel-music-mastery' ) );
		$this->send_result( $result, __( 'Student status updated.', 'gospel-music-mastery' ) );
	}

	/**
	 * Admin: edit student fields + WP user.
	 *
	 * @return void
	 */
	public function admin_edit_student() {
		$this->verify_request();
		$this->require_admin();

		$data = array(
			'first_name'     => $this->post( 'first_name' ),
			'last_name'      => $this->post( 'last_name' ),
			'email'          => $this->post( 'email' ),
			'phone'          => $this->post( 'phone' ),
			'learning_level' => $this->post( 'learning_level' ),
			'learning_goals' => $this->post( 'learning_goals' ),
			'status'         => $this->post( 'status' ),
		);

		$result = class_exists( 'GMM_Admin_Students' )
			? GMM_Admin_Students::edit_student( absint( $this->post( 'student_id' ) ), $data )
			: new WP_Error( 'gmm_missing', __( 'Student management unavailable.', 'gospel-music-mastery' ) );

		$this->send_result( $result, __( 'Student updated.', 'gospel-music-mastery' ) );
	}

	/**
	 * Soft-delete student (confirm=1 required).
	 *
	 * @return void
	 */
	public function delete_student() {
		$this->verify_request();
		$this->require_admin();
		if ( '1' !== (string) $this->post( 'confirm' ) ) {
			wp_send_json_error( array( 'message' => __( 'Confirmation required.', 'gospel-music-mastery' ) ), 400 );
		}
		$result = class_exists( 'GMM_Admin_Students' )
			? GMM_Admin_Students::delete_student( absint( $this->post( 'student_id' ) ) )
			: new WP_Error( 'gmm_missing', __( 'Student management unavailable.', 'gospel-music-mastery' ) );
		$this->send_result( $result, __( 'Student deleted.', 'gospel-music-mastery' ) );
	}

	/**
	 * Bulk activate / suspend / delete.
	 *
	 * @return void
	 */
	public function bulk_student_action() {
		$this->verify_request();
		$this->require_admin();

		$action = sanitize_key( (string) $this->post( 'bulk_action' ) );
		$ids    = $this->post( 'student_ids' );
		if ( ! is_array( $ids ) ) {
			$ids = array_filter( array_map( 'absint', explode( ',', (string) $ids ) ) );
		}

		$result = class_exists( 'GMM_Admin_Students' )
			? GMM_Admin_Students::bulk_action( $ids, $action )
			: new WP_Error( 'gmm_missing', __( 'Student management unavailable.', 'gospel-music-mastery' ) );

		$this->send_result( $result, __( 'Bulk action completed.', 'gospel-music-mastery' ) );
	}

	/**
	 * Admin student profile detail.
	 *
	 * @return void
	 */
	public function get_student_profile() {
		$this->verify_request();
		$this->require_admin();

		$profile = class_exists( 'GMM_Admin_Students' )
			? GMM_Admin_Students::get_profile( absint( $this->post( 'student_id' ) ) )
			: new WP_Error( 'gmm_missing', __( 'Student management unavailable.', 'gospel-music-mastery' ) );

		if ( is_wp_error( $profile ) ) {
			wp_send_json_error( array( 'message' => $profile->get_error_message() ), 400 );
		}

		wp_send_json_success(
			array(
				'message' => __( 'Student profile loaded.', 'gospel-music-mastery' ),
				'profile' => $profile,
			)
		);
	}

	/**
	 * @return void
	 */
	public function toggle_favourite() {
		$this->verify_request();
		if ( ! is_user_logged_in() || ( ! gmm_is_student() && ! current_user_can( 'manage_options' ) ) ) {
			$this->deny();
		}

		$teacher_id = absint( $this->post( 'teacher_id' ) );
		$remove     = (bool) $this->post( 'remove' );

		if ( ! class_exists( 'GMM_Favourites' ) ) {
			wp_send_json_error( array( 'message' => __( 'Favourites unavailable.', 'gospel-music-mastery' ) ), 500 );
		}

		$result = $remove
			? GMM_Favourites::remove_favourite( $teacher_id )
			: GMM_Favourites::add_favourite( $teacher_id );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		wp_send_json_success(
			array(
				'message'     => $remove
					? __( 'Removed from favourites.', 'gospel-music-mastery' )
					: __( 'Added to favourites.', 'gospel-music-mastery' ),
				'favourite'   => ! $remove,
				'teacher_id'  => $teacher_id,
				'result_id'   => is_numeric( $result ) ? (int) $result : 0,
			)
		);
	}

	/**
	 * @return void
	 */
	public function student_cancel_booking() {
		$this->verify_request();
		if ( ! is_user_logged_in() ) {
			$this->deny();
		}

		$booking_id = absint( $this->post( 'booking_id' ) );
		$result     = function_exists( 'gmm_student_cancel_booking' )
			? gmm_student_cancel_booking( $booking_id, get_current_user_id(), '' )
			: new WP_Error( 'gmm_missing', __( 'Booking system unavailable.', 'gospel-music-mastery' ) );

		$this->send_result( $result, __( 'Booking cancelled.', 'gospel-music-mastery' ) );
	}

	/* -------------------------------------------------------------------------
	 * Booking actions
	 * ---------------------------------------------------------------------- */

	/**
	 * @return void
	 */
	public function create_booking() {
		$this->verify_request();
		if ( ! is_user_logged_in() || ( ! gmm_is_student() && ! current_user_can( 'manage_options' ) ) ) {
			$this->deny();
		}

		$data = array(
			'teacher_id'   => absint( $this->post( 'teacher_id' ) ),
			'class_id'     => absint( $this->post( 'class_id' ) ),
			'booking_date' => $this->post( 'booking_date' ),
			'booking_time' => $this->post( 'booking_time' ),
			'duration'     => absint( $this->post( 'duration' ) ),
			'amount'       => (float) $this->post( 'amount' ),
			'notes'        => $this->post( 'notes' ),
		);

		$result = function_exists( 'gmm_create_booking' )
			? gmm_create_booking( $data, '' )
			: new WP_Error( 'gmm_missing', __( 'Booking engine unavailable.', 'gospel-music-mastery' ) );

		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}

		wp_send_json_success(
			array(
				'message'    => __( 'Booking created successfully.', 'gospel-music-mastery' ),
				'booking_id' => (int) $result,
			)
		);
	}

	/**
	 * @return void
	 */
	public function confirm_booking() {
		$this->verify_request();
		if ( ! is_user_logged_in() ) {
			$this->deny();
		}

		$booking_id = absint( $this->post( 'booking_id' ) );
		$result     = function_exists( 'gmm_teacher_confirm_booking' )
			? gmm_teacher_confirm_booking( $booking_id, get_current_user_id(), '' )
			: new WP_Error( 'gmm_missing', __( 'Booking engine unavailable.', 'gospel-music-mastery' ) );

		$this->send_result( $result, __( 'Booking confirmed.', 'gospel-music-mastery' ) );
	}

	/**
	 * Cancel booking (student or teacher ownership enforced in engines).
	 *
	 * @return void
	 */
	public function cancel_booking() {
		$this->verify_request();
		if ( ! is_user_logged_in() ) {
			$this->deny();
		}

		$booking_id = absint( $this->post( 'booking_id' ) );
		$user_id    = get_current_user_id();

		if ( current_user_can( 'manage_options' ) || gmm_is_teacher( $user_id ) ) {
			$result = function_exists( 'gmm_teacher_cancel_booking' )
				? gmm_teacher_cancel_booking( $booking_id, $user_id, '' )
				: new WP_Error( 'gmm_missing', __( 'Booking engine unavailable.', 'gospel-music-mastery' ) );
		} else {
			$result = function_exists( 'gmm_student_cancel_booking' )
				? gmm_student_cancel_booking( $booking_id, $user_id, '' )
				: new WP_Error( 'gmm_missing', __( 'Booking engine unavailable.', 'gospel-music-mastery' ) );
		}

		$this->send_result( $result, __( 'Booking cancelled.', 'gospel-music-mastery' ) );
	}

	/**
	 * @return void
	 */
	public function complete_booking() {
		$this->verify_request();
		if ( ! is_user_logged_in() ) {
			$this->deny();
		}

		$booking_id = absint( $this->post( 'booking_id' ) );
		$result     = function_exists( 'gmm_teacher_complete_booking' )
			? gmm_teacher_complete_booking( $booking_id, get_current_user_id(), '' )
			: new WP_Error( 'gmm_missing', __( 'Booking engine unavailable.', 'gospel-music-mastery' ) );

		$this->send_result( $result, __( 'Booking completed.', 'gospel-music-mastery' ) );
	}

	/* -------------------------------------------------------------------------
	 * Admin class / content / payments
	 * ---------------------------------------------------------------------- */

	/**
	 * @return void
	 */
	public function approve_class() {
		$this->verify_request();
		$this->require_admin();
		$result = class_exists( 'GMM_Admin_Classes' )
			? GMM_Admin_Classes::approve( absint( $this->post( 'class_id' ) ) )
			: $this->set_class_status( absint( $this->post( 'class_id' ) ), 'approved' );
		$this->send_result( $result, __( 'Class approved.', 'gospel-music-mastery' ) );
	}

	/**
	 * @return void
	 */
	public function reject_class() {
		$this->verify_request();
		$this->require_admin();
		$reason = sanitize_textarea_field( (string) $this->post( 'reason' ) );
		$result = class_exists( 'GMM_Admin_Classes' )
			? GMM_Admin_Classes::reject( absint( $this->post( 'class_id' ) ), $reason )
			: $this->set_class_status( absint( $this->post( 'class_id' ) ), 'rejected' );
		$this->send_result( $result, __( 'Class rejected.', 'gospel-music-mastery' ) );
	}

	/**
	 * @return void
	 */
	public function admin_edit_class() {
		$this->verify_request();
		$this->require_admin();
		$data = array(
			'title'       => $this->post( 'title' ),
			'description' => $this->post( 'description' ),
			'category'    => $this->post( 'category' ),
			'difficulty'  => $this->post( 'difficulty' ),
			'duration'    => $this->post( 'duration' ),
			'price'       => $this->post( 'price' ),
			'image'       => $this->post( 'image' ),
			'status'      => $this->post( 'status' ),
		);
		$result = class_exists( 'GMM_Admin_Classes' )
			? GMM_Admin_Classes::edit_class( absint( $this->post( 'class_id' ) ), $data )
			: new WP_Error( 'gmm_missing', __( 'Class management unavailable.', 'gospel-music-mastery' ) );
		$this->send_result( $result, __( 'Class updated.', 'gospel-music-mastery' ) );
	}

	/**
	 * @return void
	 */
	public function toggle_class_featured() {
		$this->verify_request();
		$this->require_admin();
		$featured = (bool) absint( $this->post( 'featured' ) );
		$result   = class_exists( 'GMM_Admin_Classes' )
			? GMM_Admin_Classes::set_featured( absint( $this->post( 'class_id' ) ), $featured )
			: new WP_Error( 'gmm_missing', __( 'Class management unavailable.', 'gospel-music-mastery' ) );
		$this->send_result( $result, __( 'Featured status updated.', 'gospel-music-mastery' ) );
	}

	/**
	 * Soft-delete class (confirm=1).
	 *
	 * @return void
	 */
	public function delete_class() {
		$this->verify_request();
		$this->require_admin();
		if ( '1' !== (string) $this->post( 'confirm' ) ) {
			wp_send_json_error( array( 'message' => __( 'Confirmation required.', 'gospel-music-mastery' ) ), 400 );
		}
		$result = class_exists( 'GMM_Admin_Classes' )
			? GMM_Admin_Classes::delete_class( absint( $this->post( 'class_id' ) ) )
			: new WP_Error( 'gmm_missing', __( 'Class management unavailable.', 'gospel-music-mastery' ) );
		$this->send_result( $result, __( 'Class deleted.', 'gospel-music-mastery' ) );
	}

	/**
	 * @return void
	 */
	public function bulk_class_action() {
		$this->verify_request();
		$this->require_admin();
		$action = sanitize_key( (string) $this->post( 'bulk_action' ) );
		$ids    = $this->post( 'class_ids' );
		if ( ! is_array( $ids ) ) {
			$ids = array_filter( array_map( 'absint', explode( ',', (string) $ids ) ) );
		}
		$result = class_exists( 'GMM_Admin_Classes' )
			? GMM_Admin_Classes::bulk_action( $ids, $action )
			: new WP_Error( 'gmm_missing', __( 'Class management unavailable.', 'gospel-music-mastery' ) );
		$this->send_result( $result, __( 'Bulk action completed.', 'gospel-music-mastery' ) );
	}

	/**
	 * @return void
	 */
	public function get_class_profile() {
		$this->verify_request();
		$this->require_admin();
		$profile = class_exists( 'GMM_Admin_Classes' )
			? GMM_Admin_Classes::get_profile( absint( $this->post( 'class_id' ) ) )
			: new WP_Error( 'gmm_missing', __( 'Class management unavailable.', 'gospel-music-mastery' ) );
		if ( is_wp_error( $profile ) ) {
			wp_send_json_error( array( 'message' => $profile->get_error_message() ), 400 );
		}
		wp_send_json_success(
			array(
				'message' => __( 'Class profile loaded.', 'gospel-music-mastery' ),
				'profile' => $profile,
			)
		);
	}

	/**
	 * Soft delete preparation for allow-listed content types.
	 *
	 * @return void
	 */
	public function delete_content() {
		$this->verify_request();
		$this->require_admin();

		$type = sanitize_key( (string) $this->post( 'content_type' ) );
		$id   = absint( $this->post( 'content_id' ) );

		$map = array(
			'class'   => 'classes',
			'program' => 'programs',
			'blog'    => 'blog_posts',
		);

		if ( ! isset( $map[ $type ] ) || ! $id ) {
			wp_send_json_error( array( 'message' => __( 'Invalid content.', 'gospel-music-mastery' ) ), 400 );
		}

		global $wpdb;
		$table  = GMM_Database::table( $map[ $type ] );
		$status = class_exists( 'GMM_Security' ) ? GMM_Security::soft_delete_status() : 'trash';

		// Soft-delete only — never hard-delete rows via AJAX (data recovery / audit safety).
		$updated = $wpdb->update(
			$table,
			array( 'status' => $status ),
			array( 'id' => $id ),
			array( '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			wp_send_json_error( array( 'message' => __( 'Could not delete content.', 'gospel-music-mastery' ) ), 500 );
		}

		/**
		 * Fires after admin soft-deletes allow-listed content.
		 *
		 * @since 1.0.0
		 * @param string $type Content type key.
		 * @param int    $id   Row ID.
		 */
		do_action( 'gmm_content_soft_deleted', $type, $id );

		wp_send_json_success( array( 'message' => __( 'Content deleted.', 'gospel-music-mastery' ) ) );
	}

	/**
	 * @return void
	 */
	public function update_payment_status() {
		$this->verify_request();
		$this->require_admin();

		$payment_id = absint( $this->post( 'payment_id' ) );
		$status     = sanitize_key( (string) $this->post( 'status' ) );

		if ( ! class_exists( 'GMM_Payment' ) ) {
			wp_send_json_error( array( 'message' => __( 'Payment system unavailable.', 'gospel-music-mastery' ) ), 500 );
		}

		$result = GMM_Payment::update_status( $payment_id, $status, '' );
		$this->send_result( $result, __( 'Payment status updated.', 'gospel-music-mastery' ) );
	}

	/* -------------------------------------------------------------------------
	 * Helpers
	 * ---------------------------------------------------------------------- */

	/**
	 * @return void
	 */
	private function verify_request() {
		check_ajax_referer( self::NONCE_ACTION, 'nonce' );
	}

	/**
	 * @return void
	 */
	private function require_admin() {
		if ( ! current_user_can( 'manage_options' ) ) {
			$this->deny();
		}
	}

	/**
	 * @return void
	 */
	private function deny() {
		wp_send_json_error(
			array(
				'message' => __( 'Permission denied.', 'gospel-music-mastery' ),
			),
			403
		);
	}

	/**
	 * @param string $key POST key.
	 * @return mixed
	 */
	private function post( $key ) {
		// phpcs:ignore WordPress.Security.NonceVerification.Missing -- Verified via check_ajax_referer.
		if ( ! isset( $_POST[ $key ] ) ) {
			return '';
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
		return wp_unslash( $_POST[ $key ] );
	}

	/**
	 * @return array<string, mixed>
	 */
	private function get_search_args() {
		$per_page = absint( $this->post( 'per_page' ) ? $this->post( 'per_page' ) : $this->post( 'limit' ) );
		if ( $per_page < 1 ) {
			$per_page = 20;
		}

		return array(
			'search'         => sanitize_text_field( (string) $this->post( 'search' ) ),
			'name'           => sanitize_text_field( (string) $this->post( 'name' ) ),
			'specialization' => sanitize_text_field( (string) $this->post( 'specialization' ) ),
			'instrument'     => sanitize_text_field( (string) $this->post( 'instrument' ) ),
			'experience'     => sanitize_text_field( (string) $this->post( 'experience' ) ),
			'status'         => sanitize_key( (string) $this->post( 'status' ) ),
			'category'       => sanitize_text_field( (string) $this->post( 'category' ) ),
			'difficulty'     => sanitize_text_field( (string) $this->post( 'difficulty' ) ),
			'rating'         => sanitize_text_field( (string) $this->post( 'rating' ) ),
			'sort'           => sanitize_key( (string) $this->post( 'sort' ) ),
			'featured'       => $this->post( 'featured' ),
			'price_min'      => $this->post( 'price_min' ),
			'price_max'      => $this->post( 'price_max' ),
			'page'           => max( 1, absint( $this->post( 'page' ) ? $this->post( 'page' ) : 1 ) ),
			'per_page'       => min( $per_page, 100 ),
			'limit'          => min( $per_page, 100 ),
		);
	}

	/**
	 * @return array<string, mixed>
	 */
	private function get_filter_args() {
		$args              = $this->get_search_args();
		$args['date']      = sanitize_text_field( (string) $this->post( 'date' ) );
		$args['date_from'] = sanitize_text_field( (string) $this->post( 'date_from' ) );
		$args['date_to']   = sanitize_text_field( (string) $this->post( 'date_to' ) );
		$args['specialty'] = sanitize_key( (string) $this->post( 'specialty' ) );
		if ( ! $args['specialty'] ) {
			$args['specialty'] = sanitize_key( (string) $this->post( 'at_specialty' ) );
		}
		$args['level']  = sanitize_key( (string) $this->post( 'level' ) );
		$args['period'] = sanitize_key( (string) $this->post( 'period' ) );
		if ( ! $args['level'] ) {
			$args['level'] = sanitize_key( (string) $this->post( 'as_level' ) );
		}
		if ( ! $args['period'] ) {
			$args['period'] = sanitize_key( (string) $this->post( 'as_period' ) );
		}
		return $args;
	}

	/**
	 * Send paginated search JSON.
	 *
	 * @param array<string, mixed> $result  Search result.
	 * @param string               $message Message.
	 * @return void
	 */
	private function send_search_result( $result, $message ) {
		$result = is_array( $result ) ? $result : array();
		wp_send_json_success(
			array(
				'message'     => $message,
				'items'       => isset( $result['items'] ) ? $result['items'] : array(),
				'total'       => isset( $result['total'] ) ? absint( $result['total'] ) : 0,
				'page'        => isset( $result['page'] ) ? absint( $result['page'] ) : 1,
				'per_page'    => isset( $result['per_page'] ) ? absint( $result['per_page'] ) : 20,
				'total_pages' => isset( $result['total_pages'] ) ? absint( $result['total_pages'] ) : 0,
				'has_prev'    => ! empty( $result['has_prev'] ),
				'has_next'    => ! empty( $result['has_next'] ),
				'prev_page'   => isset( $result['prev_page'] ) ? $result['prev_page'] : null,
				'next_page'   => isset( $result['next_page'] ) ? $result['next_page'] : null,
			)
		);
	}

	/**
	 * Send admin filter JSON or error.
	 *
	 * @param array<string, mixed>|WP_Error $result Result.
	 * @return void
	 */
	private function send_admin_filter_result( $result ) {
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 403 );
		}
		$this->send_search_result( $result, __( 'Filter applied.', 'gospel-music-mastery' ) );
	}

	/**
	 * @param true|WP_Error|int $result  Result.
	 * @param string            $message Success message.
	 * @return void
	 */
	private function send_result( $result, $message ) {
		if ( is_wp_error( $result ) ) {
			wp_send_json_error( array( 'message' => $result->get_error_message() ), 400 );
		}
		if ( class_exists( 'GMM_Admin_Dashboard' ) ) {
			GMM_Admin_Dashboard::flush_cache();
		}
		if ( class_exists( 'GMM_Admin_Teachers' ) ) {
			GMM_Admin_Teachers::flush_cache();
		}
		if ( class_exists( 'GMM_Admin_Students' ) ) {
			GMM_Admin_Students::flush_cache();
		}
		if ( class_exists( 'GMM_Admin_Classes' ) ) {
			GMM_Admin_Classes::flush_cache();
		}
		wp_send_json_success( array( 'message' => $message, 'data' => $result ) );
	}

	/**
	 * @param int    $teacher_id Teacher row ID.
	 * @param string $status     Status.
	 * @return true|WP_Error
	 */
	private function set_teacher_status( $teacher_id, $status ) {
		if ( ! $teacher_id ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid teacher.', 'gospel-music-mastery' ) );
		}
		global $wpdb;
		$table  = GMM_Database::table( 'teachers' );
		$status = sanitize_key( $status );

		$row = $wpdb->get_row(
			$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $teacher_id ),
			ARRAY_A
		);

		$updated = $wpdb->update(
			$table,
			array(
				'status'     => $status,
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $teacher_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db', __( 'Could not update teacher.', 'gospel-music-mastery' ) );
		}

		$row = is_array( $row ) ? $row : array();
		$row['status'] = $status;

		if ( in_array( $status, array( 'active', 'approved' ), true ) ) {
			/**
			 * Fires when a teacher application is approved.
			 *
			 * @since 1.0.0
			 * @param int                  $teacher_id Teacher row ID.
			 * @param array<string, mixed> $row        Teacher row.
			 */
			do_action( 'gmm_teacher_approved', $teacher_id, $row );
		} elseif ( in_array( $status, array( 'inactive', 'rejected' ), true ) ) {
			/**
			 * Fires when a teacher application is rejected / deactivated.
			 *
			 * @since 1.0.0
			 * @param int                  $teacher_id Teacher row ID.
			 * @param array<string, mixed> $row        Teacher row.
			 */
			do_action( 'gmm_teacher_rejected', $teacher_id, $row );
		}

		return true;
	}

	/**
	 * @param int    $class_id Class ID.
	 * @param string $status   Status.
	 * @return true|WP_Error
	 */
	private function set_class_status( $class_id, $status ) {
		if ( ! $class_id ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid class.', 'gospel-music-mastery' ) );
		}
		global $wpdb;
		$table   = GMM_Database::table( 'classes' );
		$updated = $wpdb->update(
			$table,
			array(
				'status'     => sanitize_key( $status ),
				'updated_at' => current_time( 'mysql' ),
			),
			array( 'id' => $class_id ),
			array( '%s', '%s' ),
			array( '%d' )
		);
		return ( false === $updated )
			? new WP_Error( 'gmm_db', __( 'Could not update class.', 'gospel-music-mastery' ) )
			: true;
	}
}
