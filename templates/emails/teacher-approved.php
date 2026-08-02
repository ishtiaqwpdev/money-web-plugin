<?php
/**
 * Teacher approved email template.
 *
 * Available vars: $user_name, $teacher_name, $site_name
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;
?>
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8" />
	<title><?php echo esc_html( $site_name ); ?></title>
</head>
<body style="margin:0;padding:24px;font-family:Arial,Helvetica,sans-serif;color:#222;background:#f7f7f7;">
	<table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #e5e5e5;">
		<tr>
			<td style="padding:24px;">
				<h1 style="margin:0 0 16px;font-size:22px;"><?php echo esc_html( $site_name ); ?></h1>
				<p style="margin:0 0 12px;"><?php printf( /* translators: %s: user name */ esc_html__( 'Hello %s,', 'gospel-music-mastery' ), esc_html( $user_name ) ); ?></p>
				<p style="margin:0 0 12px;"><?php esc_html_e( 'Congratulations! Your teacher account has been approved. You can now create classes, set availability, and accept bookings.', 'gospel-music-mastery' ); ?></p>
				<p style="margin:24px 0 0;font-size:12px;color:#666;"><?php echo esc_html( $site_name ); ?></p>
			</td>
		</tr>
	</table>
</body>
</html>
