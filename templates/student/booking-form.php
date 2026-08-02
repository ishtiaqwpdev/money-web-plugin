<?php
/**
 * Legacy path — booking form now lives at templates/public/booking-form.php.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

$file = defined( 'GMM_PATH' ) ? GMM_PATH . 'templates/public/booking-form.php' : '';
if ( $file && is_readable( $file ) ) {
	include $file;
}
