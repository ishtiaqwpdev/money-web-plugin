<?php
/**
 * Notification helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add an in-app notification for a user.
 *
 * @param int    $user_id WP user ID.
 * @param string $type    Type key.
 * @param string $title   Title.
 * @param string $message Message.
 * @return int|WP_Error
 */
function gmm_add_notification( $user_id, $type, $title, $message ) {
	if ( ! class_exists( 'GMM_Notifications' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Notifications unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Notifications::add_notification( $user_id, $type, $title, $message );
}

/**
 * Get notifications for a user.
 *
 * @param int                  $user_id User ID (0 = current).
 * @param array<string, mixed> $args    Filters.
 * @return array<int, array<string, mixed>>|WP_Error
 */
function gmm_get_notifications( $user_id = 0, $args = array() ) {
	if ( ! class_exists( 'GMM_Notifications' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Notifications unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Notifications::get_notifications( $user_id, $args );
}

/**
 * Mark a notification as read.
 *
 * @param int $notification_id Notification ID.
 * @param int $user_id         Optional requester.
 * @return true|WP_Error
 */
function gmm_mark_notification_read( $notification_id, $user_id = 0 ) {
	if ( ! class_exists( 'GMM_Notifications' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Notifications unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Notifications::mark_notification_read( $notification_id, $user_id );
}

/**
 * Send a GMM email via wp_mail + template.
 *
 * @param string               $to       Recipient.
 * @param string               $subject  Subject.
 * @param string               $template Template basename.
 * @param array<string, mixed> $vars     Variables.
 * @return bool
 */
function gmm_send_email( $to, $subject, $template, $vars = array() ) {
	if ( ! class_exists( 'GMM_Notifications' ) ) {
		return false;
	}
	return GMM_Notifications::send_email( $to, $subject, $template, $vars );
}
