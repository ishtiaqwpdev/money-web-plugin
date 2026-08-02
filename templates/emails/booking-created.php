<?php
/**
 * Booking created email template.
 *
 * Available vars: $user_name, $booking_id, $teacher_name, $student_name, $class_name, $booking_date, $booking_time, $amount, $site_name, $admin_note
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
				<p style="margin:0 0 12px;"><?php esc_html_e( 'A new booking has been created.', 'gospel-music-mastery' ); ?></p>
				<ul style="margin:0 0 12px;padding-left:18px;">
					<li><?php printf( /* translators: %s: booking id */ esc_html__( 'Booking ID: %s', 'gospel-music-mastery' ), esc_html( (string) $booking_id ) ); ?></li>
					<?php if ( ! empty( $class_name ) ) : ?>
						<li><?php printf( /* translators: %s: class name */ esc_html__( 'Class: %s', 'gospel-music-mastery' ), esc_html( $class_name ) ); ?></li>
					<?php endif; ?>
					<?php if ( ! empty( $teacher_name ) ) : ?>
						<li><?php printf( /* translators: %s: teacher name */ esc_html__( 'Teacher: %s', 'gospel-music-mastery' ), esc_html( $teacher_name ) ); ?></li>
					<?php endif; ?>
					<?php if ( ! empty( $student_name ) ) : ?>
						<li><?php printf( /* translators: %s: student name */ esc_html__( 'Student: %s', 'gospel-music-mastery' ), esc_html( $student_name ) ); ?></li>
					<?php endif; ?>
					<?php if ( ! empty( $booking_date ) ) : ?>
						<li><?php printf( /* translators: %s: date */ esc_html__( 'Date: %s', 'gospel-music-mastery' ), esc_html( $booking_date ) ); ?></li>
					<?php endif; ?>
					<?php if ( ! empty( $booking_time ) ) : ?>
						<li><?php printf( /* translators: %s: time */ esc_html__( 'Time: %s', 'gospel-music-mastery' ), esc_html( $booking_time ) ); ?></li>
					<?php endif; ?>
					<?php if ( ! empty( $amount ) ) : ?>
						<li><?php printf( /* translators: %s: amount */ esc_html__( 'Amount: %s', 'gospel-music-mastery' ), esc_html( $amount ) ); ?></li>
					<?php endif; ?>
				</ul>
				<?php if ( ! empty( $admin_note ) ) : ?>
					<p style="margin:0 0 12px;"><?php echo esc_html( $admin_note ); ?></p>
				<?php endif; ?>
				<p style="margin:24px 0 0;font-size:12px;color:#666;"><?php echo esc_html( $site_name ); ?></p>
			</td>
		</tr>
	</table>
</body>
</html>
