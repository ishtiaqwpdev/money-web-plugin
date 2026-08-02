<?php
/**
 * Plugin Name:       Gospel Music Mastery
 * Plugin URI:        https://gospelmusicmastery.com/
 * Description:       Core plugin for the Gospel Music Mastery learning platform (teachers, students, bookings, and related features).
 * Version:           1.0.2
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            Gospel Music Mastery
 * Author URI:        https://gospelmusicmastery.com/
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       gospel-music-mastery
 * Domain Path:       /languages
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Plugin version.
 */
define( 'GMM_VERSION', '1.0.2' );

/**
 * Database schema version.
 */
define( 'GMM_DB_VERSION', '1.4.0' );

/**
 * Absolute filesystem path to the plugin directory (with trailing slash).
 */
define( 'GMM_PATH', plugin_dir_path( __FILE__ ) );

/**
 * URL to the plugin directory (with trailing slash).
 */
define( 'GMM_URL', plugin_dir_url( __FILE__ ) );

/**
 * Plugin basename (e.g. gospel-music-mastery/gospel-music-mastery.php).
 */
define( 'GMM_BASENAME', plugin_basename( __FILE__ ) );

/**
 * Autoload core classes used on activation / bootstrap.
 */
require_once GMM_PATH . 'includes/class-gmm-database.php';
require_once GMM_PATH . 'includes/class-gmm-roles.php';
require_once GMM_PATH . 'includes/gmm-role-functions.php';
require_once GMM_PATH . 'includes/class-gmm-settings.php';
require_once GMM_PATH . 'includes/gmm-settings-functions.php';
require_once GMM_PATH . 'includes/class-gmm-assets.php';
require_once GMM_PATH . 'includes/gmm-asset-functions.php';
require_once GMM_PATH . 'includes/class-gmm-template-loader.php';
require_once GMM_PATH . 'includes/gmm-template-functions.php';
require_once GMM_PATH . 'includes/class-gmm-shortcodes.php';
require_once GMM_PATH . 'includes/class-gmm-pages.php';
require_once GMM_PATH . 'admin/class-gmm-admin-menu.php';
require_once GMM_PATH . 'admin/class-gmm-admin-pages.php';
require_once GMM_PATH . 'admin/class-gmm-admin-settings.php';
require_once GMM_PATH . 'admin/class-gmm-admin-dashboard.php';
require_once GMM_PATH . 'admin/class-gmm-admin-teachers.php';
require_once GMM_PATH . 'admin/class-gmm-admin-students.php';
require_once GMM_PATH . 'admin/class-gmm-admin-classes.php';
require_once GMM_PATH . 'admin/class-gmm-admin-bookings.php';
require_once GMM_PATH . 'admin/class-gmm-admin-payments.php';
require_once GMM_PATH . 'admin/gmm-admin-functions.php';
require_once GMM_PATH . 'admin/gmm-admin-dashboard-functions.php';
require_once GMM_PATH . 'admin/gmm-admin-teachers-functions.php';
require_once GMM_PATH . 'admin/gmm-admin-students-functions.php';
require_once GMM_PATH . 'admin/gmm-admin-classes-functions.php';
require_once GMM_PATH . 'admin/gmm-admin-bookings-functions.php';
require_once GMM_PATH . 'admin/gmm-admin-payments-functions.php';
require_once GMM_PATH . 'teacher/class-gmm-teacher.php';
require_once GMM_PATH . 'teacher/class-gmm-teacher-classes.php';
require_once GMM_PATH . 'teacher/class-gmm-availability.php';
require_once GMM_PATH . 'teacher/gmm-teacher-functions.php';
require_once GMM_PATH . 'student/class-gmm-student.php';
require_once GMM_PATH . 'student/class-gmm-student-lessons.php';
require_once GMM_PATH . 'student/class-gmm-student-bookings.php';
require_once GMM_PATH . 'student/class-gmm-favourites.php';
require_once GMM_PATH . 'student/class-gmm-student-payments.php';
require_once GMM_PATH . 'student/gmm-student-functions.php';
require_once GMM_PATH . 'includes/class-gmm-booking.php';
require_once GMM_PATH . 'includes/gmm-booking-functions.php';
require_once GMM_PATH . 'includes/class-gmm-payment.php';
require_once GMM_PATH . 'includes/gmm-payment-functions.php';
require_once GMM_PATH . 'includes/payment-gateways/class-gmm-stripe.php';
require_once GMM_PATH . 'includes/payment-gateways/class-gmm-paypal.php';
require_once GMM_PATH . 'includes/class-gmm-auth.php';
require_once GMM_PATH . 'includes/gmm-auth-functions.php';
require_once GMM_PATH . 'includes/class-gmm-search.php';
require_once GMM_PATH . 'includes/gmm-search-functions.php';
require_once GMM_PATH . 'includes/class-gmm-ajax.php';
require_once GMM_PATH . 'includes/gmm-ajax-functions.php';
require_once GMM_PATH . 'includes/class-gmm-notifications.php';
require_once GMM_PATH . 'includes/gmm-notification-functions.php';
require_once GMM_PATH . 'includes/class-gmm-reviews.php';
require_once GMM_PATH . 'includes/gmm-review-functions.php';
require_once GMM_PATH . 'includes/class-gmm-media.php';
require_once GMM_PATH . 'includes/gmm-media-functions.php';
require_once GMM_PATH . 'includes/class-gmm-analytics.php';
require_once GMM_PATH . 'includes/gmm-analytics-functions.php';
require_once GMM_PATH . 'includes/class-gmm-security.php';
require_once GMM_PATH . 'teacher/class-gmm-teacher-earnings.php';
require_once GMM_PATH . 'includes/class-gmm-activator.php';
require_once GMM_PATH . 'includes/class-gmm-deactivator.php';
require_once GMM_PATH . 'includes/class-gmm-loader.php';
require_once GMM_PATH . 'includes/class-gmm-plugin.php';

/**
 * Runs on plugin activation.
 *
 * @return void
 */
function gmm_activate_plugin() {
	GMM_Activator::activate();
}

/**
 * Runs on plugin deactivation.
 *
 * @return void
 */
function gmm_deactivate_plugin() {
	GMM_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'gmm_activate_plugin' );
register_deactivation_hook( __FILE__, 'gmm_deactivate_plugin' );

/**
 * Begins plugin execution.
 *
 * @return void
 */
function gmm_run_plugin() {
	$plugin = new GMM_Plugin();
	$plugin->run();

	// Keep schema current after plugin updates without requiring re-activation.
	if ( class_exists( 'GMM_Database' ) ) {
		GMM_Database::maybe_upgrade();
	}
}

gmm_run_plugin();
