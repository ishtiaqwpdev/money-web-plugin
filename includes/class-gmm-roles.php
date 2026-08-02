<?php
/**
 * Custom roles and capabilities for Gospel Music Mastery.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Roles
 *
 * Registers and maintains plugin roles. Does not remove roles on deactivation.
 */
class GMM_Roles {

	/**
	 * Teacher role key.
	 *
	 * @var string
	 */
	const ROLE_TEACHER = 'gmm_teacher';

	/**
	 * Student role key.
	 *
	 * @var string
	 */
	const ROLE_STUDENT = 'gmm_student';

	/**
	 * Register custom roles and sync capabilities.
	 *
	 * Safe to call multiple times — updates caps without duplicating roles.
	 *
	 * @return void
	 */
	public static function register() {
		self::add_teacher_role();
		self::add_student_role();
		self::grant_admin_gmm_capabilities();

		/**
		 * Fires after GMM roles and capabilities are registered.
		 *
		 * @since 1.0.0
		 */
		do_action( 'gmm_roles_registered' );
	}

	/**
	 * Create or refresh the Teacher role.
	 *
	 * @return void
	 */
	private static function add_teacher_role() {
		$caps = self::get_teacher_capabilities();

		if ( get_role( self::ROLE_TEACHER ) ) {
			self::sync_role_capabilities( self::ROLE_TEACHER, $caps );
			return;
		}

		add_role(
			self::ROLE_TEACHER,
			__( 'Teacher', 'gospel-music-mastery' ),
			$caps
		);
	}

	/**
	 * Create or refresh the Student role.
	 *
	 * @return void
	 */
	private static function add_student_role() {
		$caps = self::get_student_capabilities();

		if ( get_role( self::ROLE_STUDENT ) ) {
			self::sync_role_capabilities( self::ROLE_STUDENT, $caps );
			return;
		}

		add_role(
			self::ROLE_STUDENT,
			__( 'Student', 'gospel-music-mastery' ),
			$caps
		);
	}

	/**
	 * Ensure WordPress administrators receive GMM custom capabilities.
	 *
	 * Does not create a new admin role.
	 *
	 * @return void
	 */
	private static function grant_admin_gmm_capabilities() {
		$admin = get_role( 'administrator' );
		if ( ! $admin ) {
			return;
		}

		foreach ( self::get_all_gmm_capabilities() as $cap ) {
			if ( ! $admin->has_cap( $cap ) ) {
				$admin->add_cap( $cap );
			}
		}
	}

	/**
	 * Align an existing role's capabilities with the desired map.
	 *
	 * Adds missing GMM-related caps; removes only GMM custom caps that
	 * should no longer apply. Never strips core WordPress caps from roles.
	 *
	 * @param string               $role_key Role key.
	 * @param array<string, bool>  $desired  Capability map.
	 * @return void
	 */
	private static function sync_role_capabilities( $role_key, $desired ) {
		$role = get_role( $role_key );
		if ( ! $role ) {
			return;
		}

		foreach ( $desired as $cap => $grant ) {
			if ( $grant ) {
				$role->add_cap( $cap );
			} else {
				$role->remove_cap( $cap );
			}
		}

		// Remove GMM custom caps that are no longer part of this role.
		foreach ( self::get_all_gmm_capabilities() as $gmm_cap ) {
			if ( empty( $desired[ $gmm_cap ] ) && $role->has_cap( $gmm_cap ) ) {
				$role->remove_cap( $gmm_cap );
			}
		}
	}

	/**
	 * Capability map for teachers.
	 *
	 * Explicitly excludes privileged WP caps (manage_options, edit_users, etc.).
	 *
	 * @return array<string, bool>
	 */
	public static function get_teacher_capabilities() {
		return array(
			'read'                     => true,
			'upload_files'             => true,
			'edit_posts'               => true,
			'publish_posts'            => true,
			'delete_posts'             => false,
			'edit_others_posts'        => false,
			'delete_others_posts'      => false,
			'manage_options'           => false,
			'edit_users'               => false,
			'delete_users'             => false,
			'create_users'             => false,
			'list_users'               => false,
			'install_plugins'          => false,
			'activate_plugins'         => false,
			'manage_gmm_profile'       => true,
			'manage_gmm_classes'       => true,
			'manage_gmm_bookings'      => true,
			'manage_gmm_availability'  => true,
			'manage_gmm_reviews'       => false,
		);
	}

	/**
	 * Capability map for students.
	 *
	 * @return array<string, bool>
	 */
	public static function get_student_capabilities() {
		return array(
			'read'                 => true,
			'upload_files'         => true,
			'edit_posts'           => false,
			'publish_posts'        => false,
			'manage_options'       => false,
			'edit_users'           => false,
			'delete_users'         => false,
			'manage_plugins'       => false,
			'install_plugins'      => false,
			'activate_plugins'     => false,
			'manage_gmm_profile'   => true,
			'manage_gmm_bookings'  => true,
			'manage_gmm_reviews'   => true,
			'manage_gmm_classes'   => false,
			'manage_gmm_availability' => false,
		);
	}

	/**
	 * All custom GMM capability names.
	 *
	 * @return array<int, string>
	 */
	public static function get_all_gmm_capabilities() {
		return array(
			'manage_gmm_profile',
			'manage_gmm_classes',
			'manage_gmm_bookings',
			'manage_gmm_availability',
			'manage_gmm_reviews',
		);
	}

	/**
	 * Whether a user has the teacher role (or admin override via manage_options).
	 *
	 * Prefer capability checks for actions; this helper is for portal routing.
	 *
	 * @param int $user_id Optional user ID. Defaults to current user.
	 * @return bool
	 */
	public static function is_teacher( $user_id = 0 ) {
		$user = self::resolve_user( $user_id );
		if ( ! $user ) {
			return false;
		}

		if ( user_can( $user, 'manage_options' ) ) {
			return false; // Admins are admins, not teachers for portal helpers.
		}

		return in_array( self::ROLE_TEACHER, (array) $user->roles, true )
			|| user_can( $user, 'manage_gmm_classes' );
	}

	/**
	 * Whether a user has the student role.
	 *
	 * @param int $user_id Optional user ID. Defaults to current user.
	 * @return bool
	 */
	public static function is_student( $user_id = 0 ) {
		$user = self::resolve_user( $user_id );
		if ( ! $user ) {
			return false;
		}

		if ( user_can( $user, 'manage_options' ) ) {
			return false;
		}

		return in_array( self::ROLE_STUDENT, (array) $user->roles, true )
			|| ( user_can( $user, 'manage_gmm_reviews' ) && ! user_can( $user, 'manage_gmm_classes' ) );
	}

	/**
	 * Whether a user is a WordPress administrator.
	 *
	 * Uses capability check — never role name alone for security decisions.
	 *
	 * @param int $user_id Optional user ID. Defaults to current user.
	 * @return bool
	 */
	public static function is_admin( $user_id = 0 ) {
		$user = self::resolve_user( $user_id );
		if ( ! $user ) {
			return false;
		}

		return user_can( $user, 'manage_options' );
	}

	/**
	 * Resolve a WP_User from an ID or the current user.
	 *
	 * @param int $user_id User ID.
	 * @return WP_User|false
	 */
	private static function resolve_user( $user_id = 0 ) {
		$user_id = absint( $user_id );

		if ( $user_id > 0 ) {
			$user = get_userdata( $user_id );
			return $user ? $user : false;
		}

		if ( ! is_user_logged_in() ) {
			return false;
		}

		$user = wp_get_current_user();
		return ( $user && $user->exists() ) ? $user : false;
	}
}
