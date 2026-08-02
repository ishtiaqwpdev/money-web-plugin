<?php
/**
 * Database schema installer for Gospel Music Mastery.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Database
 *
 * Creates and upgrades plugin custom tables via dbDelta.
 * Does not drop tables or delete data on activation.
 */
class GMM_Database {

	/**
	 * Current database schema version.
	 *
	 * @var string
	 */
	const DB_VERSION = '1.4.0';

	/**
	 * Option key storing installed DB version.
	 *
	 * @var string
	 */
	const DB_VERSION_OPTION = 'gmm_db_version';

	/**
	 * Install or upgrade all plugin tables.
	 *
	 * Safe to run on every activation — dbDelta only creates/alters as needed.
	 *
	 * @return void
	 */
	public static function install() {
		require_once ABSPATH . 'wp-admin/includes/upgrade.php';

		$schema = self::get_schema();

		foreach ( $schema as $sql ) {
			dbDelta( $sql );
		}

		update_option( self::DB_VERSION_OPTION, defined( 'GMM_DB_VERSION' ) ? GMM_DB_VERSION : self::DB_VERSION );
	}

	/**
	 * Whether the installed DB version matches the plugin schema version.
	 *
	 * @return bool
	 */
	public static function needs_upgrade() {
		$installed = get_option( self::DB_VERSION_OPTION, '' );
		$current   = defined( 'GMM_DB_VERSION' ) ? GMM_DB_VERSION : self::DB_VERSION;
		return version_compare( (string) $installed, (string) $current, '<' );
	}

	/**
	 * Maybe upgrade schema when plugin loads (future-safe).
	 *
	 * @return void
	 */
	public static function maybe_upgrade() {
		if ( self::needs_upgrade() ) {
			self::install();
		}
	}

	/**
	 * Build full table name with WordPress + gmm_ prefix.
	 *
	 * @param string $name Table key without prefix (e.g. teachers).
	 * @return string
	 */
	public static function table( $name ) {
		global $wpdb;
		return $wpdb->prefix . 'gmm_' . sanitize_key( $name );
	}

	/**
	 * Return CREATE TABLE SQL statements for all GMM tables.
	 *
	 * @return array<int, string>
	 */
	public static function get_schema() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();

		$teachers      = self::table( 'teachers' );
		$students      = self::table( 'students' );
		$classes       = self::table( 'classes' );
		$bookings      = self::table( 'bookings' );
		$payments      = self::table( 'payments' );
		$reviews       = self::table( 'reviews' );
		$availability  = self::table( 'availability' );
		$programs      = self::table( 'programs' );
		$blog_posts    = self::table( 'blog_posts' );
		$favourites    = self::table( 'favourites' );
		$notifications = self::table( 'notifications' );

		$sql = array();

		$sql[] = "CREATE TABLE {$teachers} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			first_name varchar(100) NOT NULL DEFAULT '',
			last_name varchar(100) NOT NULL DEFAULT '',
			email varchar(191) NOT NULL DEFAULT '',
			phone varchar(50) NOT NULL DEFAULT '',
			profile_image varchar(255) NOT NULL DEFAULT '',
			bio longtext NULL,
			specialization varchar(255) NOT NULL DEFAULT '',
			experience varchar(100) NOT NULL DEFAULT '',
			rating decimal(3,2) NOT NULL DEFAULT 0.00,
			intro_video varchar(255) NOT NULL DEFAULT '',
			status varchar(20) NOT NULL DEFAULT 'pending',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY email (email),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$students} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			first_name varchar(100) NOT NULL DEFAULT '',
			last_name varchar(100) NOT NULL DEFAULT '',
			email varchar(191) NOT NULL DEFAULT '',
			phone varchar(50) NOT NULL DEFAULT '',
			profile_image varchar(255) NOT NULL DEFAULT '',
			learning_level varchar(50) NOT NULL DEFAULT '',
			learning_goals text NULL,
			preferred_instruments text NULL,
			bio longtext NULL,
			status varchar(20) NOT NULL DEFAULT 'active',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY email (email),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$classes} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			teacher_id bigint(20) unsigned NOT NULL DEFAULT 0,
			title varchar(255) NOT NULL DEFAULT '',
			description longtext NULL,
			category varchar(100) NOT NULL DEFAULT '',
			difficulty varchar(50) NOT NULL DEFAULT '',
			duration int(11) NOT NULL DEFAULT 0,
			price decimal(10,2) NOT NULL DEFAULT 0.00,
			rating decimal(3,2) NOT NULL DEFAULT 0.00,
			image varchar(255) NOT NULL DEFAULT '',
			video varchar(255) NOT NULL DEFAULT '',
			status varchar(20) NOT NULL DEFAULT 'draft',
			featured tinyint(1) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY teacher_id (teacher_id),
			KEY status (status),
			KEY featured (featured),
			KEY category (category)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$bookings} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			student_id bigint(20) unsigned NOT NULL DEFAULT 0,
			teacher_id bigint(20) unsigned NOT NULL DEFAULT 0,
			class_id bigint(20) unsigned NOT NULL DEFAULT 0,
			booking_date date NOT NULL DEFAULT '0000-00-00',
			booking_time time NOT NULL DEFAULT '00:00:00',
			duration int(11) NOT NULL DEFAULT 0,
			amount decimal(10,2) NOT NULL DEFAULT 0.00,
			payment_status varchar(20) NOT NULL DEFAULT 'pending',
			booking_status varchar(20) NOT NULL DEFAULT 'pending',
			notes text NULL,
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY student_id (student_id),
			KEY teacher_id (teacher_id),
			KEY class_id (class_id),
			KEY payment_status (payment_status),
			KEY booking_status (booking_status),
			KEY booking_date (booking_date)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$payments} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			booking_id bigint(20) unsigned NOT NULL DEFAULT 0,
			student_id bigint(20) unsigned NOT NULL DEFAULT 0,
			teacher_id bigint(20) unsigned NOT NULL DEFAULT 0,
			transaction_id varchar(191) NOT NULL DEFAULT '',
			amount decimal(10,2) NOT NULL DEFAULT 0.00,
			payment_method varchar(50) NOT NULL DEFAULT '',
			payment_status varchar(20) NOT NULL DEFAULT 'pending',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY booking_id (booking_id),
			KEY student_id (student_id),
			KEY teacher_id (teacher_id),
			KEY transaction_id (transaction_id),
			KEY payment_status (payment_status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$reviews} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			student_id bigint(20) unsigned NOT NULL DEFAULT 0,
			teacher_id bigint(20) unsigned NOT NULL DEFAULT 0,
			class_id bigint(20) unsigned NOT NULL DEFAULT 0,
			rating tinyint(3) unsigned NOT NULL DEFAULT 0,
			comment text NULL,
			status varchar(20) NOT NULL DEFAULT 'pending',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			UNIQUE KEY student_teacher_class (student_id, teacher_id, class_id),
			KEY student_id (student_id),
			KEY teacher_id (teacher_id),
			KEY class_id (class_id),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$availability} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			teacher_id bigint(20) unsigned NOT NULL DEFAULT 0,
			available_date date NOT NULL DEFAULT '0000-00-00',
			start_time time NOT NULL DEFAULT '00:00:00',
			end_time time NOT NULL DEFAULT '00:00:00',
			status varchar(20) NOT NULL DEFAULT 'open',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY teacher_id (teacher_id),
			KEY available_date (available_date),
			KEY status (status)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$programs} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			title varchar(255) NOT NULL DEFAULT '',
			description longtext NULL,
			category varchar(100) NOT NULL DEFAULT '',
			image varchar(255) NOT NULL DEFAULT '',
			difficulty varchar(50) NOT NULL DEFAULT '',
			status varchar(20) NOT NULL DEFAULT 'draft',
			featured tinyint(1) NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY status (status),
			KEY featured (featured),
			KEY category (category)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$blog_posts} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			author_id bigint(20) unsigned NOT NULL DEFAULT 0,
			title varchar(255) NOT NULL DEFAULT '',
			content longtext NULL,
			image varchar(255) NOT NULL DEFAULT '',
			category varchar(100) NOT NULL DEFAULT '',
			status varchar(20) NOT NULL DEFAULT 'draft',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			updated_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY author_id (author_id),
			KEY status (status),
			KEY category (category)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$favourites} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			student_id bigint(20) unsigned NOT NULL DEFAULT 0,
			teacher_id bigint(20) unsigned NOT NULL DEFAULT 0,
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			UNIQUE KEY student_teacher (student_id, teacher_id),
			KEY student_id (student_id),
			KEY teacher_id (teacher_id)
		) {$charset_collate};";

		$sql[] = "CREATE TABLE {$notifications} (
			id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
			user_id bigint(20) unsigned NOT NULL DEFAULT 0,
			type varchar(50) NOT NULL DEFAULT '',
			title varchar(255) NOT NULL DEFAULT '',
			message text NULL,
			status varchar(20) NOT NULL DEFAULT 'unread',
			created_at datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY type (type),
			KEY status (status),
			KEY created_at (created_at)
		) {$charset_collate};";

		return $sql;
	}

	/**
	 * List of logical table keys (without prefix).
	 *
	 * @return array<int, string>
	 */
	public static function get_table_keys() {
		return array(
			'teachers',
			'students',
			'classes',
			'bookings',
			'payments',
			'reviews',
			'availability',
			'programs',
			'blog_posts',
			'favourites',
			'notifications',
		);
	}
}
