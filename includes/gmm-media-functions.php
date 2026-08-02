<?php
/**
 * Media helper functions.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Upload an image via WordPress Media Library.
 *
 * @param string $file_key  $_FILES key (default gmm_file).
 * @param string $context   Upload context.
 * @param int    $object_id Related row ID.
 * @param string $nonce     Optional nonce.
 * @return array<string, mixed>|WP_Error
 */
function gmm_upload_image( $file_key = 'gmm_file', $context = '', $object_id = 0, $nonce = '' ) {
	if ( ! class_exists( 'GMM_Media' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Media system unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Media::upload_image( $file_key, $context, $object_id, $nonce );
}

/**
 * Upload a video via WordPress Media Library.
 *
 * @param string $file_key  $_FILES key.
 * @param string $context   Upload context.
 * @param int    $object_id Related row ID.
 * @param string $nonce     Optional nonce.
 * @return array<string, mixed>|WP_Error
 */
function gmm_upload_video( $file_key = 'gmm_file', $context = '', $object_id = 0, $nonce = '' ) {
	if ( ! class_exists( 'GMM_Media' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Media system unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Media::upload_video( $file_key, $context, $object_id, $nonce );
}

/**
 * Delete media reference / attachment safely.
 *
 * @param int    $attachment_id Attachment ID.
 * @param string $context       Optional context.
 * @param int    $object_id     Optional object ID.
 * @param string $nonce         Optional nonce.
 * @return array<string, mixed>|WP_Error
 */
function gmm_delete_media( $attachment_id, $context = '', $object_id = 0, $nonce = '' ) {
	if ( ! class_exists( 'GMM_Media' ) ) {
		return new WP_Error( 'gmm_missing', __( 'Media system unavailable.', 'gospel-music-mastery' ) );
	}
	return GMM_Media::delete_media( $attachment_id, $context, $object_id, $nonce );
}

/**
 * Resolve stored attachment ID or legacy URL to a URL.
 *
 * @param mixed  $stored Stored value.
 * @param string $size   Image size.
 * @return string
 */
function gmm_get_media_url( $stored, $size = 'full' ) {
	if ( ! class_exists( 'GMM_Media' ) ) {
		return is_string( $stored ) ? esc_url( $stored ) : '';
	}
	$url = GMM_Media::get_media_url( $stored, $size );
	return $url ? esc_url( $url ) : '';
}

/**
 * Attachment preview data for frontend wiring.
 *
 * @param int $attachment_id Attachment ID.
 * @return array<string, mixed>|null
 */
function gmm_get_attachment_preview( $attachment_id ) {
	if ( ! class_exists( 'GMM_Media' ) ) {
		return null;
	}
	return GMM_Media::get_attachment_preview( $attachment_id );
}

/**
 * Parse attachment ID from a stored media field.
 *
 * @param mixed $stored Stored value.
 * @return int
 */
function gmm_get_attachment_id( $stored ) {
	$stored = is_string( $stored ) || is_numeric( $stored ) ? trim( (string) $stored ) : '';
	return ( $stored && ctype_digit( $stored ) ) ? absint( $stored ) : 0;
}
