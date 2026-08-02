<?php
/**
 * Email and in-app notification system for Gospel Music Mastery.
 *
 * Uses WordPress wp_mail() only. No SMS, push, chat, or external mail APIs.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

/**
 * Class GMM_Notifications
 *
 * Manages email notifications, system notification storage, and event hooks.
 */
class GMM_Notifications {

	const STATUS_UNREAD = 'unread';
	const STATUS_READ   = 'read';

	/**
	 * Allowed notification statuses.
	 *
	 * @var array<int, string>
	 */
	const STATUSES = array( self::STATUS_UNREAD, self::STATUS_READ );

	/**
	 * Register event listeners.
	 *
	 * @param GMM_Loader $loader Hook loader.
	 * @return void
	 */
	public static function register_hooks( $loader ) {
		$instance = new self();

		$loader->add_action( 'gmm_user_registered', $instance, 'on_user_registered', 10, 3 );
		$loader->add_action( 'gmm_teacher_approved', $instance, 'on_teacher_approved', 10, 2 );
		$loader->add_action( 'gmm_teacher_rejected', $instance, 'on_teacher_rejected', 10, 2 );
		$loader->add_action( 'gmm_booking_created', $instance, 'on_booking_created', 10, 2 );
		$loader->add_action( 'gmm_booking_confirmed', $instance, 'on_booking_confirmed', 10, 2 );
		$loader->add_action( 'gmm_booking_cancelled', $instance, 'on_booking_cancelled', 10, 2 );
		$loader->add_action( 'gmm_booking_completed', $instance, 'on_booking_completed', 10, 2 );
		$loader->add_action( 'gmm_payment_completed', $instance, 'on_payment_completed', 10, 2 );
		$loader->add_action( 'gmm_payment_refunded', $instance, 'on_payment_refunded', 10, 2 );
		$loader->add_action( 'gmm_withdrawal_requested', $instance, 'on_withdrawal_requested', 10, 3 );
		$loader->add_action( 'gmm_withdrawal_approved', $instance, 'on_withdrawal_approved', 10, 2 );
		$loader->add_action( 'gmm_withdrawal_rejected', $instance, 'on_withdrawal_rejected', 10, 2 );
		$loader->add_action( 'gmm_withdrawal_paid', $instance, 'on_withdrawal_paid', 10, 2 );
	}

	/**
	 * Store an in-app notification.
	 *
	 * @param int    $user_id WP user ID.
	 * @param string $type    Notification type key.
	 * @param string $title   Short title.
	 * @param string $message Message body.
	 * @return int|WP_Error Notification ID.
	 */
	public static function add_notification( $user_id, $type, $title, $message ) {
		$user_id = absint( $user_id );
		$type    = sanitize_key( (string) $type );
		$title   = sanitize_text_field( (string) $title );
		$message = sanitize_textarea_field( (string) $message );

		if ( ! $user_id || ! $type || '' === $title ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid notification data.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table = GMM_Database::table( 'notifications' );

		$inserted = $wpdb->insert(
			$table,
			array(
				'user_id'    => $user_id,
				'type'       => $type,
				'title'      => $title,
				'message'    => $message,
				'status'     => self::STATUS_UNREAD,
				'created_at' => current_time( 'mysql' ),
			),
			array( '%d', '%s', '%s', '%s', '%s', '%s' )
		);

		if ( ! $inserted ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not save notification.', 'gospel-music-mastery' ) );
		}

		$notification_id = (int) $wpdb->insert_id;

		/**
		 * Fires after an in-app notification is stored.
		 *
		 * @since 1.0.0
		 * @param int    $notification_id Notification ID.
		 * @param int    $user_id         User ID.
		 * @param string $type            Type key.
		 */
		do_action( 'gmm_notification_added', $notification_id, $user_id, $type );

		return $notification_id;
	}

	/**
	 * Get notifications for a user (owner or admin only).
	 *
	 * @param int                  $user_id Target WP user ID.
	 * @param array<string, mixed> $args    Optional filters: status, type, limit, offset.
	 * @return array<int, array<string, mixed>>|WP_Error
	 */
	public static function get_notifications( $user_id = 0, $args = array() ) {
		$user_id = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();
		$args    = is_array( $args ) ? $args : array();

		if ( ! $user_id ) {
			return new WP_Error( 'gmm_not_logged_in', __( 'You must be logged in.', 'gospel-music-mastery' ) );
		}

		if ( ! self::can_view_user_notifications( $user_id ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot view these notifications.', 'gospel-music-mastery' ) );
		}

		$status = isset( $args['status'] ) ? sanitize_key( (string) $args['status'] ) : '';
		$type   = isset( $args['type'] ) ? sanitize_key( (string) $args['type'] ) : '';
		$limit  = isset( $args['limit'] ) ? absint( $args['limit'] ) : 50;
		$offset = isset( $args['offset'] ) ? absint( $args['offset'] ) : 0;

		if ( $limit < 1 ) {
			$limit = 50;
		}
		if ( $limit > 100 ) {
			$limit = 100;
		}

		global $wpdb;
		$table  = GMM_Database::table( 'notifications' );
		$where  = array( 'user_id = %d' );
		$params = array( $user_id );

		if ( $status && in_array( $status, self::STATUSES, true ) ) {
			$where[]  = 'status = %s';
			$params[] = $status;
		}

		if ( $type ) {
			$where[]  = 'type = %s';
			$params[] = $type;
		}

		$sql = 'SELECT id, user_id, type, title, message, status, created_at FROM ' . $table
			. ' WHERE ' . implode( ' AND ', $where )
			. ' ORDER BY created_at DESC, id DESC'
			. ' LIMIT %d OFFSET %d';

		$params[] = $limit;
		$params[] = $offset;

		// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
		$rows = $wpdb->get_results( $wpdb->prepare( $sql, $params ), ARRAY_A );

		return is_array( $rows ) ? $rows : array();
	}

	/**
	 * Mark a notification as read (owner or admin only).
	 *
	 * @param int $notification_id Notification ID.
	 * @param int $user_id         Optional requester (defaults to current user).
	 * @return true|WP_Error
	 */
	public static function mark_notification_read( $notification_id, $user_id = 0 ) {
		$notification_id = absint( $notification_id );
		$user_id         = absint( $user_id ) ? absint( $user_id ) : get_current_user_id();

		if ( ! $notification_id || ! $user_id ) {
			return new WP_Error( 'gmm_invalid', __( 'Invalid notification.', 'gospel-music-mastery' ) );
		}

		global $wpdb;
		$table = GMM_Database::table( 'notifications' );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT id, user_id, status FROM {$table} WHERE id = %d LIMIT 1",
				$notification_id
			),
			ARRAY_A
		);

		if ( ! $row ) {
			return new WP_Error( 'gmm_not_found', __( 'Notification not found.', 'gospel-music-mastery' ) );
		}

		$owner_id = absint( $row['user_id'] );
		if ( (int) $user_id !== $owner_id && ! current_user_can( 'manage_options' ) ) {
			return new WP_Error( 'gmm_forbidden', __( 'You cannot update this notification.', 'gospel-music-mastery' ) );
		}

		if ( self::STATUS_READ === $row['status'] ) {
			return true;
		}

		$updated = $wpdb->update(
			$table,
			array( 'status' => self::STATUS_READ ),
			array( 'id' => $notification_id ),
			array( '%s' ),
			array( '%d' )
		);

		if ( false === $updated ) {
			return new WP_Error( 'gmm_db_error', __( 'Could not update notification.', 'gospel-music-mastery' ) );
		}

		return true;
	}

	/**
	 * Send an HTML email via wp_mail using a template file.
	 *
	 * @param string               $to       Recipient email.
	 * @param string               $subject  Subject line.
	 * @param string               $template Template basename (without .php).
	 * @param array<string, mixed> $vars     Template variables.
	 * @return bool
	 */
	public static function send_email( $to, $subject, $template, $vars = array() ) {
		if ( ! self::emails_enabled() ) {
			return false;
		}

		$to      = sanitize_email( (string) $to );
		$subject = sanitize_text_field( (string) $subject );
		$template = sanitize_file_name( (string) $template );

		if ( ! is_email( $to ) || '' === $subject || '' === $template ) {
			return false;
		}

		$body = self::render_email_template( $template, $vars );
		if ( '' === $body ) {
			return false;
		}

		$headers   = array( 'Content-Type: text/html; charset=UTF-8' );
		$settings  = self::get_email_settings();
		$from_email = isset( $settings['from_email'] ) ? sanitize_email( $settings['from_email'] ) : '';
		$from_name  = isset( $settings['from_name'] ) ? sanitize_text_field( $settings['from_name'] ) : '';

		if ( is_email( $from_email ) ) {
			$name = $from_name ? $from_name : 'Gospel Music Mastery';
			$headers[] = 'From: ' . $name . ' <' . $from_email . '>';
		}

		/**
		 * Filter outgoing GMM email arguments before wp_mail.
		 *
		 * @since 1.0.0
		 * @param array<string, mixed> $mail Mail args.
		 */
		$mail = apply_filters(
			'gmm_pre_wp_mail',
			array(
				'to'      => $to,
				'subject' => $subject,
				'message' => $body,
				'headers' => $headers,
			)
		);

		return (bool) wp_mail(
			$mail['to'],
			$mail['subject'],
			$mail['message'],
			isset( $mail['headers'] ) ? $mail['headers'] : $headers
		);
	}

	/**
	 * Render an email template with escaped dynamic values available as variables.
	 *
	 * @param string               $template Template basename.
	 * @param array<string, mixed> $vars     Variables.
	 * @return string
	 */
	public static function render_email_template( $template, $vars = array() ) {
		$template = sanitize_file_name( (string) $template );
		$path     = GMM_PATH . 'templates/emails/' . $template . '.php';

		if ( ! $template || ! file_exists( $path ) ) {
			return '';
		}

		$safe = self::prepare_template_vars( is_array( $vars ) ? $vars : array() );

		// phpcs:ignore WordPress.PHP.DontExtract.extract_extract -- Controlled keys only.
		extract( $safe, EXTR_SKIP );

		ob_start();
		include $path;
		return (string) ob_get_clean();
	}

	/**
	 * Email settings option (prepared; UI not required yet).
	 *
	 * @return array<string, mixed>
	 */
	public static function get_email_settings() {
		if ( class_exists( 'GMM_Settings' ) ) {
			return GMM_Settings::get_group( GMM_Settings::OPTION_EMAIL );
		}

		$defaults = class_exists( 'GMM_Admin_Settings' )
			? GMM_Admin_Settings::default_email()
			: array(
				'from_email'      => '',
				'from_name'       => 'Gospel Music Mastery',
				'emails_enabled'  => 'yes',
				'notify_students' => 'yes',
				'notify_teachers' => 'yes',
				'notify_admins'   => 'yes',
				'preferences'     => array(),
			);

		$stored = get_option( 'gmm_email_settings', array() );
		$stored = is_array( $stored ) ? $stored : array();

		return array_merge( $defaults, $stored );
	}

	/**
	 * Whether global email sending is enabled.
	 *
	 * @return bool
	 */
	public static function emails_enabled() {
		$settings = self::get_email_settings();
		return ! empty( $settings['emails_enabled'] ) && 'yes' === $settings['emails_enabled'];
	}

	/**
	 * Whether a preference key is enabled.
	 *
	 * @param string $key Preference key.
	 * @return bool
	 */
	public static function preference_enabled( $key ) {
		$key      = sanitize_key( (string) $key );
		$settings = self::get_email_settings();
		$prefs    = isset( $settings['preferences'] ) && is_array( $settings['preferences'] )
			? $settings['preferences']
			: array();

		if ( ! $key ) {
			return true;
		}

		if ( ! isset( $prefs[ $key ] ) ) {
			return true;
		}

		return 'yes' === $prefs[ $key ];
	}

	/**
	 * Handle user registration (student or teacher).
	 *
	 * @param int                  $user_id User ID.
	 * @param array<string, mixed> $data    Sanitized registration data.
	 * @param string               $role    gmm_student|gmm_teacher.
	 * @return void
	 */
	public function on_user_registered( $user_id, $data = array(), $role = '' ) {
		$user_id = absint( $user_id );
		$data    = is_array( $data ) ? $data : array();
		$role    = sanitize_key( (string) $role );

		if ( ! $user_id ) {
			return;
		}

		$user      = get_userdata( $user_id );
		$user_name = $user ? $user->display_name : '';
		$email     = $user && is_email( $user->user_email ) ? $user->user_email : '';

		if ( isset( $data['first_name'] ) || isset( $data['last_name'] ) ) {
			$user_name = trim(
				( isset( $data['first_name'] ) ? (string) $data['first_name'] : '' ) . ' ' .
				( isset( $data['last_name'] ) ? (string) $data['last_name'] : '' )
			);
		}
		if ( isset( $data['email'] ) && is_email( $data['email'] ) ) {
			$email = sanitize_email( $data['email'] );
		}

		$vars = array(
			'user_name' => $user_name,
			'user_email'=> $email,
			'site_name' => self::site_name(),
		);

		$settings = self::get_email_settings();

		if ( GMM_Roles::ROLE_TEACHER === $role ) {
			if ( ! empty( $settings['notify_teachers'] ) && 'yes' === $settings['notify_teachers'] && self::preference_enabled( 'teacher_registration' ) ) {
				self::add_notification(
					$user_id,
					'teacher_registration',
					__( 'Registration received', 'gospel-music-mastery' ),
					__( 'Your teacher application has been received and is pending review.', 'gospel-music-mastery' )
				);
				if ( $email ) {
					self::send_email(
						$email,
						__( 'Teacher registration received', 'gospel-music-mastery' ),
						'teacher-registration',
						$vars
					);
				}
			}

			self::notify_admins(
				'teacher_application',
				__( 'New teacher application', 'gospel-music-mastery' ),
				sprintf(
					/* translators: %s: user name */
					__( 'A new teacher application was submitted by %s.', 'gospel-music-mastery' ),
					$user_name ? $user_name : '#' . $user_id
				),
				'teacher-registration',
				array_merge(
					$vars,
					array(
						'admin_note' => __( 'A new teacher has applied and awaits approval.', 'gospel-music-mastery' ),
					)
				),
				__( 'New teacher application', 'gospel-music-mastery' ),
				'teacher_application'
			);
			return;
		}

		// Default: student registration.
		if ( ! empty( $settings['notify_students'] ) && 'yes' === $settings['notify_students'] && self::preference_enabled( 'student_registration' ) ) {
			self::add_notification(
				$user_id,
				'student_registration',
				__( 'Welcome!', 'gospel-music-mastery' ),
				__( 'Your student account was created successfully.', 'gospel-music-mastery' )
			);
			if ( $email ) {
				self::send_email(
					$email,
					__( 'Welcome to Gospel Music Mastery', 'gospel-music-mastery' ),
					'student-registration',
					$vars
				);
			}
		}

		self::notify_admins(
			'student_registration',
			__( 'New student registration', 'gospel-music-mastery' ),
			sprintf(
				/* translators: %s: user name */
				__( 'A new student registered: %s.', 'gospel-music-mastery' ),
				$user_name ? $user_name : '#' . $user_id
			),
			'student-registration',
			array_merge(
				$vars,
				array(
					'admin_note' => __( 'A new student has joined the platform.', 'gospel-music-mastery' ),
				)
			),
			__( 'New student registration', 'gospel-music-mastery' ),
			'student_registration'
		);
	}

	/**
	 * Teacher approved.
	 *
	 * @param int                  $teacher_id Teacher row ID.
	 * @param array<string, mixed> $row        Teacher row.
	 * @return void
	 */
	public function on_teacher_approved( $teacher_id, $row = array() ) {
		$ctx = self::teacher_context( $teacher_id, $row );
		if ( ! $ctx ) {
			return;
		}

		$settings = self::get_email_settings();
		if ( empty( $settings['notify_teachers'] ) || 'yes' !== $settings['notify_teachers'] || ! self::preference_enabled( 'teacher_approved' ) ) {
			return;
		}

		self::add_notification(
			$ctx['user_id'],
			'teacher_approved',
			__( 'Application approved', 'gospel-music-mastery' ),
			__( 'Your teacher account has been approved. You can now manage classes and bookings.', 'gospel-music-mastery' )
		);

		if ( $ctx['email'] ) {
			self::send_email(
				$ctx['email'],
				__( 'Your teacher account was approved', 'gospel-music-mastery' ),
				'teacher-approved',
				array(
					'user_name'    => $ctx['name'],
					'teacher_name' => $ctx['name'],
					'site_name'    => self::site_name(),
				)
			);
		}
	}

	/**
	 * Teacher rejected.
	 *
	 * @param int                  $teacher_id Teacher row ID.
	 * @param array<string, mixed> $row        Teacher row.
	 * @return void
	 */
	public function on_teacher_rejected( $teacher_id, $row = array() ) {
		$ctx = self::teacher_context( $teacher_id, $row );
		if ( ! $ctx ) {
			return;
		}

		$settings = self::get_email_settings();
		if ( empty( $settings['notify_teachers'] ) || 'yes' !== $settings['notify_teachers'] || ! self::preference_enabled( 'teacher_rejected' ) ) {
			return;
		}

		self::add_notification(
			$ctx['user_id'],
			'teacher_rejected',
			__( 'Application update', 'gospel-music-mastery' ),
			__( 'Your teacher application was not approved at this time.', 'gospel-music-mastery' )
		);

		if ( $ctx['email'] ) {
			self::send_email(
				$ctx['email'],
				__( 'Teacher application update', 'gospel-music-mastery' ),
				'teacher-rejected',
				array(
					'user_name'    => $ctx['name'],
					'teacher_name' => $ctx['name'],
					'site_name'    => self::site_name(),
				)
			);
		}
	}

	/**
	 * Booking created.
	 *
	 * @param int                  $booking_id Booking ID.
	 * @param array<string, mixed> $row        Booking row (may be status string from transitions — ignored).
	 * @return void
	 */
	public function on_booking_created( $booking_id, $row = array() ) {
		$ctx = self::booking_context( $booking_id );
		if ( ! $ctx ) {
			return;
		}

		$settings = self::get_email_settings();
		$vars     = self::booking_email_vars( $ctx );

		if ( ! empty( $settings['notify_students'] ) && 'yes' === $settings['notify_students'] && self::preference_enabled( 'booking_created' ) && $ctx['student_user_id'] ) {
			self::add_notification(
				$ctx['student_user_id'],
				'booking_created',
				__( 'Booking created', 'gospel-music-mastery' ),
				sprintf(
					/* translators: %d: booking ID */
					__( 'Your booking #%d was created and is pending confirmation.', 'gospel-music-mastery' ),
					$ctx['booking_id']
				)
			);
			if ( $ctx['student_email'] ) {
				self::send_email(
					$ctx['student_email'],
					__( 'Booking created', 'gospel-music-mastery' ),
					'booking-created',
					array_merge( $vars, array( 'user_name' => $ctx['student_name'] ) )
				);
			}
		}

		if ( ! empty( $settings['notify_teachers'] ) && 'yes' === $settings['notify_teachers'] && self::preference_enabled( 'booking_created' ) && $ctx['teacher_user_id'] ) {
			self::add_notification(
				$ctx['teacher_user_id'],
				'booking_created',
				__( 'New booking', 'gospel-music-mastery' ),
				sprintf(
					/* translators: %d: booking ID */
					__( 'You have a new booking request #%d.', 'gospel-music-mastery' ),
					$ctx['booking_id']
				)
			);
			if ( $ctx['teacher_email'] ) {
				self::send_email(
					$ctx['teacher_email'],
					__( 'New booking request', 'gospel-music-mastery' ),
					'booking-created',
					array_merge( $vars, array( 'user_name' => $ctx['teacher_name'] ) )
				);
			}
		}

		self::notify_admins(
			'booking_created',
			__( 'New booking', 'gospel-music-mastery' ),
			sprintf(
				/* translators: %d: booking ID */
				__( 'A new booking #%d was created.', 'gospel-music-mastery' ),
				$ctx['booking_id']
			),
			'booking-created',
			$vars,
			__( 'New booking created', 'gospel-music-mastery' ),
			'booking_created'
		);
	}

	/**
	 * Booking confirmed.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $status     Status (from transition hook).
	 * @return void
	 */
	public function on_booking_confirmed( $booking_id, $status = '' ) {
		$ctx = self::booking_context( $booking_id );
		if ( ! $ctx ) {
			return;
		}

		$settings = self::get_email_settings();
		$vars     = self::booking_email_vars( $ctx );

		if ( ! empty( $settings['notify_students'] ) && 'yes' === $settings['notify_students'] && self::preference_enabled( 'booking_confirmed' ) && $ctx['student_user_id'] ) {
			self::add_notification(
				$ctx['student_user_id'],
				'booking_confirmed',
				__( 'Booking confirmed', 'gospel-music-mastery' ),
				sprintf(
					/* translators: %d: booking ID */
					__( 'Your booking #%d has been confirmed.', 'gospel-music-mastery' ),
					$ctx['booking_id']
				)
			);
			if ( $ctx['student_email'] ) {
				self::send_email(
					$ctx['student_email'],
					__( 'Booking confirmed', 'gospel-music-mastery' ),
					'booking-confirmed',
					array_merge( $vars, array( 'user_name' => $ctx['student_name'] ) )
				);
			}
		}
	}

	/**
	 * Booking cancelled.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $status     Status.
	 * @return void
	 */
	public function on_booking_cancelled( $booking_id, $status = '' ) {
		$ctx = self::booking_context( $booking_id );
		if ( ! $ctx ) {
			return;
		}

		$settings = self::get_email_settings();
		$vars     = self::booking_email_vars( $ctx );

		if ( ! empty( $settings['notify_students'] ) && 'yes' === $settings['notify_students'] && self::preference_enabled( 'booking_cancelled' ) && $ctx['student_user_id'] ) {
			self::add_notification(
				$ctx['student_user_id'],
				'booking_cancelled',
				__( 'Booking cancelled', 'gospel-music-mastery' ),
				sprintf(
					/* translators: %d: booking ID */
					__( 'Booking #%d was cancelled.', 'gospel-music-mastery' ),
					$ctx['booking_id']
				)
			);
			if ( $ctx['student_email'] ) {
				self::send_email(
					$ctx['student_email'],
					__( 'Booking cancelled', 'gospel-music-mastery' ),
					'booking-cancelled',
					array_merge( $vars, array( 'user_name' => $ctx['student_name'] ) )
				);
			}
		}

		if ( ! empty( $settings['notify_teachers'] ) && 'yes' === $settings['notify_teachers'] && self::preference_enabled( 'booking_cancelled' ) && $ctx['teacher_user_id'] ) {
			self::add_notification(
				$ctx['teacher_user_id'],
				'booking_cancelled',
				__( 'Booking cancelled', 'gospel-music-mastery' ),
				sprintf(
					/* translators: %d: booking ID */
					__( 'Booking #%d was cancelled.', 'gospel-music-mastery' ),
					$ctx['booking_id']
				)
			);
			if ( $ctx['teacher_email'] ) {
				self::send_email(
					$ctx['teacher_email'],
					__( 'Booking cancelled', 'gospel-music-mastery' ),
					'booking-cancelled',
					array_merge( $vars, array( 'user_name' => $ctx['teacher_name'] ) )
				);
			}
		}
	}

	/**
	 * Lesson / booking completed.
	 *
	 * @param int    $booking_id Booking ID.
	 * @param string $status     Status.
	 * @return void
	 */
	public function on_booking_completed( $booking_id, $status = '' ) {
		$ctx = self::booking_context( $booking_id );
		if ( ! $ctx ) {
			return;
		}

		$settings = self::get_email_settings();

		if ( ! empty( $settings['notify_students'] ) && 'yes' === $settings['notify_students'] && self::preference_enabled( 'lesson_completed' ) && $ctx['student_user_id'] ) {
			self::add_notification(
				$ctx['student_user_id'],
				'lesson_completed',
				__( 'Lesson completed', 'gospel-music-mastery' ),
				sprintf(
					/* translators: %d: booking ID */
					__( 'Your lesson for booking #%d is marked complete.', 'gospel-music-mastery' ),
					$ctx['booking_id']
				)
			);
		}
	}

	/**
	 * Payment completed.
	 *
	 * @param int                  $payment_id Payment ID.
	 * @param array<string, mixed> $row        Payment row.
	 * @return void
	 */
	public function on_payment_completed( $payment_id, $row = array() ) {
		$ctx = self::payment_context( $payment_id, $row );
		if ( ! $ctx ) {
			return;
		}

		$settings = self::get_email_settings();
		$vars     = array(
			'user_name'    => $ctx['student_name'],
			'teacher_name' => $ctx['teacher_name'],
			'class_name'   => $ctx['class_name'],
			'booking_id'   => $ctx['booking_id'],
			'amount'       => $ctx['amount_formatted'],
			'site_name'    => self::site_name(),
			'payment_id'   => $ctx['payment_id'],
		);

		if ( ! empty( $settings['notify_students'] ) && 'yes' === $settings['notify_students'] && self::preference_enabled( 'payment_completed' ) && $ctx['student_user_id'] ) {
			self::add_notification(
				$ctx['student_user_id'],
				'payment_completed',
				__( 'Payment completed', 'gospel-music-mastery' ),
				sprintf(
					/* translators: %s: amount */
					__( 'Your payment of %s was completed.', 'gospel-music-mastery' ),
					$ctx['amount_formatted']
				)
			);
			if ( $ctx['student_email'] ) {
				self::send_email(
					$ctx['student_email'],
					__( 'Payment completed', 'gospel-music-mastery' ),
					'payment-completed',
					$vars
				);
			}
		}

		if ( ! empty( $settings['notify_teachers'] ) && 'yes' === $settings['notify_teachers'] && self::preference_enabled( 'payment_completed' ) && $ctx['teacher_user_id'] ) {
			self::add_notification(
				$ctx['teacher_user_id'],
				'payment_received',
				__( 'Payment received', 'gospel-music-mastery' ),
				sprintf(
					/* translators: %s: amount */
					__( 'A payment of %s was received for your lesson.', 'gospel-music-mastery' ),
					$ctx['amount_formatted']
				)
			);
			if ( $ctx['teacher_email'] ) {
				self::send_email(
					$ctx['teacher_email'],
					__( 'Payment received', 'gospel-music-mastery' ),
					'payment-completed',
					array_merge( $vars, array( 'user_name' => $ctx['teacher_name'] ) )
				);
			}
		}

		self::notify_admins(
			'payment_activity',
			__( 'Payment activity', 'gospel-music-mastery' ),
			sprintf(
				/* translators: 1: payment ID, 2: amount */
				__( 'Payment #%1$d completed for %2$s.', 'gospel-music-mastery' ),
				$ctx['payment_id'],
				$ctx['amount_formatted']
			),
			'payment-completed',
			$vars,
			__( 'Payment completed', 'gospel-music-mastery' ),
			'payment_activity'
		);
	}

	/**
	 * Payment refunded / refund request notification.
	 *
	 * @param int                  $payment_id Payment ID.
	 * @param array<string, mixed> $row        Payment row.
	 * @return void
	 */
	public function on_payment_refunded( $payment_id, $row = array() ) {
		$ctx = self::payment_context( $payment_id, $row );
		if ( ! $ctx ) {
			return;
		}

		$settings = self::get_email_settings();
		$vars     = array(
			'user_name'    => $ctx['student_name'],
			'teacher_name' => $ctx['teacher_name'],
			'class_name'   => $ctx['class_name'],
			'booking_id'   => $ctx['booking_id'],
			'amount'       => $ctx['amount_formatted'],
			'site_name'    => self::site_name(),
			'payment_id'   => $ctx['payment_id'],
		);

		if ( ! empty( $settings['notify_students'] ) && 'yes' === $settings['notify_students'] && self::preference_enabled( 'payment_refunded' ) && $ctx['student_user_id'] ) {
			self::add_notification(
				$ctx['student_user_id'],
				'payment_refunded',
				__( 'Payment refunded', 'gospel-music-mastery' ),
				sprintf(
					/* translators: %s: amount */
					__( 'A refund of %s has been processed.', 'gospel-music-mastery' ),
					$ctx['amount_formatted']
				)
			);
			if ( $ctx['student_email'] ) {
				self::send_email(
					$ctx['student_email'],
					__( 'Payment refunded', 'gospel-music-mastery' ),
					'payment-refunded',
					$vars
				);
			}
		}

		self::notify_admins(
			'refund_request',
			__( 'Refund activity', 'gospel-music-mastery' ),
			sprintf(
				/* translators: 1: payment ID, 2: amount */
				__( 'Refund recorded for payment #%1$d (%2$s).', 'gospel-music-mastery' ),
				$ctx['payment_id'],
				$ctx['amount_formatted']
			),
			'payment-refunded',
			$vars,
			__( 'Payment refunded', 'gospel-music-mastery' ),
			'refund_request'
		);
	}

	/**
	 * Teacher requested a withdrawal.
	 *
	 * @param int                  $withdrawal_id Withdrawal ID.
	 * @param array<string, mixed> $row           Withdrawal row.
	 * @param int                  $user_id       Teacher WP user ID.
	 * @return void
	 */
	public function on_withdrawal_requested( $withdrawal_id, $row = array(), $user_id = 0 ) {
		$ctx = self::withdrawal_context( $withdrawal_id, $row, $user_id );
		if ( ! $ctx ) {
			return;
		}

		if ( $ctx['teacher_user_id'] ) {
			self::add_notification(
				$ctx['teacher_user_id'],
				'withdrawal_requested',
				__( 'Withdrawal requested', 'gospel-music-mastery' ),
				sprintf(
					/* translators: %s: amount */
					__( 'Your withdrawal request for %s is pending review.', 'gospel-music-mastery' ),
					$ctx['amount_formatted']
				)
			);
		}

		self::notify_admins(
			'withdrawal_requested',
			__( 'Withdrawal requested', 'gospel-music-mastery' ),
			sprintf(
				/* translators: 1: withdrawal ID, 2: amount */
				__( 'Withdrawal #%1$d requested for %2$s.', 'gospel-music-mastery' ),
				$ctx['withdrawal_id'],
				$ctx['amount_formatted']
			),
			'payment-completed',
			array(
				'user_name'  => $ctx['teacher_name'],
				'amount'     => $ctx['amount_formatted'],
				'site_name'  => self::site_name(),
				'payment_id' => $ctx['withdrawal_id'],
			),
			__( 'Withdrawal requested', 'gospel-music-mastery' ),
			'payment_activity'
		);
	}

	/**
	 * Withdrawal approved.
	 *
	 * @param int                  $withdrawal_id ID.
	 * @param array<string, mixed> $row           Row.
	 * @return void
	 */
	public function on_withdrawal_approved( $withdrawal_id, $row = array() ) {
		$ctx = self::withdrawal_context( $withdrawal_id, $row );
		if ( ! $ctx || ! $ctx['teacher_user_id'] ) {
			return;
		}
		self::add_notification(
			$ctx['teacher_user_id'],
			'withdrawal_approved',
			__( 'Withdrawal approved', 'gospel-music-mastery' ),
			sprintf(
				/* translators: %s: amount */
				__( 'Your withdrawal of %s was approved.', 'gospel-music-mastery' ),
				$ctx['amount_formatted']
			)
		);
	}

	/**
	 * Withdrawal rejected.
	 *
	 * @param int                  $withdrawal_id ID.
	 * @param array<string, mixed> $row           Row.
	 * @return void
	 */
	public function on_withdrawal_rejected( $withdrawal_id, $row = array() ) {
		$ctx = self::withdrawal_context( $withdrawal_id, $row );
		if ( ! $ctx || ! $ctx['teacher_user_id'] ) {
			return;
		}
		$note = ! empty( $row['admin_note'] ) ? ' ' . sanitize_text_field( (string) $row['admin_note'] ) : '';
		self::add_notification(
			$ctx['teacher_user_id'],
			'withdrawal_rejected',
			__( 'Withdrawal rejected', 'gospel-music-mastery' ),
			sprintf(
				/* translators: %s: amount */
				__( 'Your withdrawal of %s was rejected.', 'gospel-music-mastery' ),
				$ctx['amount_formatted']
			) . $note
		);
	}

	/**
	 * Withdrawal marked paid.
	 *
	 * @param int                  $withdrawal_id ID.
	 * @param array<string, mixed> $row           Row.
	 * @return void
	 */
	public function on_withdrawal_paid( $withdrawal_id, $row = array() ) {
		$ctx = self::withdrawal_context( $withdrawal_id, $row );
		if ( ! $ctx || ! $ctx['teacher_user_id'] ) {
			return;
		}
		self::add_notification(
			$ctx['teacher_user_id'],
			'withdrawal_paid',
			__( 'Withdrawal paid', 'gospel-music-mastery' ),
			sprintf(
				/* translators: %s: amount */
				__( 'Your withdrawal of %s has been paid.', 'gospel-music-mastery' ),
				$ctx['amount_formatted']
			)
		);
	}

	/**
	 * Resolve withdrawal notification context.
	 *
	 * @param int                  $withdrawal_id ID.
	 * @param array<string, mixed> $row           Row.
	 * @param int                  $user_id       Optional WP user.
	 * @return array<string, mixed>|null
	 */
	private static function withdrawal_context( $withdrawal_id, $row = array(), $user_id = 0 ) {
		$withdrawal_id = absint( $withdrawal_id );
		$row           = is_array( $row ) ? $row : array();

		if ( ( ! $row || empty( $row['teacher_id'] ) ) && $withdrawal_id && class_exists( 'GMM_Teacher_Earnings' ) ) {
			$found = GMM_Teacher_Earnings::get_withdrawal_row( $withdrawal_id );
			if ( is_array( $found ) ) {
				$row = $found;
			}
		}

		if ( empty( $row['teacher_id'] ) && ! $user_id ) {
			return null;
		}

		$teacher_id = isset( $row['teacher_id'] ) ? absint( $row['teacher_id'] ) : 0;
		$amount     = isset( $row['amount'] ) ? (float) $row['amount'] : 0.0;
		$teacher_user_id = absint( $user_id );
		$teacher_name    = '';

		if ( ! $teacher_user_id && $teacher_id ) {
			global $wpdb;
			$teachers = GMM_Database::table( 'teachers' );
			$trow     = $wpdb->get_row(
				$wpdb->prepare( "SELECT user_id, first_name, last_name FROM {$teachers} WHERE id = %d", $teacher_id ),
				ARRAY_A
			);
			if ( is_array( $trow ) ) {
				$teacher_user_id = absint( $trow['user_id'] );
				$teacher_name    = trim( (string) $trow['first_name'] . ' ' . (string) $trow['last_name'] );
			}
		} elseif ( $teacher_user_id ) {
			$user = get_userdata( $teacher_user_id );
			if ( $user ) {
				$teacher_name = $user->display_name;
			}
		}

		return array(
			'withdrawal_id'    => $withdrawal_id ? $withdrawal_id : ( isset( $row['id'] ) ? absint( $row['id'] ) : 0 ),
			'teacher_id'       => $teacher_id,
			'teacher_user_id'  => $teacher_user_id,
			'teacher_name'     => $teacher_name ? $teacher_name : __( 'Teacher', 'gospel-music-mastery' ),
			'amount'           => $amount,
			'amount_formatted' => '$' . number_format_i18n( $amount, 2 ),
		);
	}

	/**
	 * Notify site admins (in-app + email).
	 *
	 * @param string               $type           In-app type.
	 * @param string               $title          Title.
	 * @param string               $message        Message.
	 * @param string               $email_template Email template.
	 * @param array<string, mixed> $vars           Template vars.
	 * @param string               $email_subject  Subject.
	 * @param string               $preference_key Preference key.
	 * @return void
	 */
	private static function notify_admins( $type, $title, $message, $email_template, $vars, $email_subject, $preference_key ) {
		$settings = self::get_email_settings();
		if ( empty( $settings['notify_admins'] ) || 'yes' !== $settings['notify_admins'] || ! self::preference_enabled( $preference_key ) ) {
			return;
		}

		$admin_ids = get_users(
			array(
				'role'   => 'administrator',
				'fields' => 'ID',
				'number' => 20,
			)
		);

		if ( is_array( $admin_ids ) ) {
			foreach ( $admin_ids as $admin_id ) {
				self::add_notification( absint( $admin_id ), $type, $title, $message );
			}
		}

		$admin_email = isset( $settings['from_email'] ) && is_email( $settings['from_email'] )
			? $settings['from_email']
			: get_option( 'admin_email' );

		if ( is_email( $admin_email ) ) {
			self::send_email(
				$admin_email,
				$email_subject,
				$email_template,
				array_merge(
					$vars,
					array(
						'user_name'  => __( 'Admin', 'gospel-music-mastery' ),
						'admin_note' => $message,
					)
				)
			);
		}
	}

	/**
	 * Whether current user may view a user's notifications.
	 *
	 * @param int $user_id Target user.
	 * @return bool
	 */
	private static function can_view_user_notifications( $user_id ) {
		$current = get_current_user_id();
		if ( ! $current ) {
			return false;
		}
		if ( (int) $current === (int) $user_id ) {
			return true;
		}
		return current_user_can( 'manage_options' );
	}

	/**
	 * Sanitize / stringify template variables for safe extract.
	 *
	 * @param array<string, mixed> $vars Raw vars.
	 * @return array<string, string|int>
	 */
	private static function prepare_template_vars( $vars ) {
		$allowed = array(
			'user_name',
			'user_email',
			'teacher_name',
			'student_name',
			'class_name',
			'booking_id',
			'payment_id',
			'amount',
			'site_name',
			'booking_date',
			'booking_time',
			'admin_note',
		);

		$out = array();
		foreach ( $allowed as $key ) {
			if ( ! array_key_exists( $key, $vars ) ) {
				$out[ $key ] = '';
				continue;
			}
			$value = $vars[ $key ];
			if ( is_numeric( $value ) && in_array( $key, array( 'booking_id', 'payment_id' ), true ) ) {
				$out[ $key ] = absint( $value );
			} else {
				$out[ $key ] = sanitize_text_field( (string) $value );
			}
		}

		return $out;
	}

	/**
	 * Platform display name.
	 *
	 * @return string
	 */
	private static function site_name() {
		$general = get_option( 'gmm_general_settings', array() );
		if ( is_array( $general ) && ! empty( $general['site_name'] ) ) {
			return sanitize_text_field( (string) $general['site_name'] );
		}
		return 'Gospel Music Mastery';
	}

	/**
	 * Resolve teacher user context.
	 *
	 * @param int                  $teacher_id Teacher row ID.
	 * @param array<string, mixed> $row        Optional row.
	 * @return array<string, mixed>|null
	 */
	private static function teacher_context( $teacher_id, $row = array() ) {
		$teacher_id = absint( $teacher_id );
		$row        = is_array( $row ) ? $row : array();

		if ( empty( $row['user_id'] ) && $teacher_id ) {
			global $wpdb;
			$table = GMM_Database::table( 'teachers' );
			$row   = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $teacher_id ),
				ARRAY_A
			);
			$row = is_array( $row ) ? $row : array();
		}

		$user_id = isset( $row['user_id'] ) ? absint( $row['user_id'] ) : 0;
		if ( ! $user_id ) {
			return null;
		}

		$name = trim(
			( isset( $row['first_name'] ) ? (string) $row['first_name'] : '' ) . ' ' .
			( isset( $row['last_name'] ) ? (string) $row['last_name'] : '' )
		);
		$email = isset( $row['email'] ) ? sanitize_email( (string) $row['email'] ) : '';
		if ( ! is_email( $email ) ) {
			$user = get_userdata( $user_id );
			$email = $user && is_email( $user->user_email ) ? $user->user_email : '';
			if ( '' === $name && $user ) {
				$name = $user->display_name;
			}
		}

		return array(
			'teacher_id' => $teacher_id,
			'user_id'    => $user_id,
			'name'       => $name,
			'email'      => $email,
		);
	}

	/**
	 * Load booking context for notifications (no capability gate).
	 *
	 * @param int $booking_id Booking ID.
	 * @return array<string, mixed>|null
	 */
	private static function booking_context( $booking_id ) {
		$booking_id = absint( $booking_id );
		if ( ! $booking_id ) {
			return null;
		}

		global $wpdb;
		$bookings = GMM_Database::table( 'bookings' );
		$students = GMM_Database::table( 'students' );
		$teachers = GMM_Database::table( 'teachers' );
		$classes  = GMM_Database::table( 'classes' );

		$row = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT b.*,
					s.user_id AS student_user_id,
					s.first_name AS student_first_name,
					s.last_name AS student_last_name,
					s.email AS student_email,
					t.user_id AS teacher_user_id,
					t.first_name AS teacher_first_name,
					t.last_name AS teacher_last_name,
					t.email AS teacher_email,
					c.title AS class_title
				FROM {$bookings} b
				LEFT JOIN {$students} s ON s.id = b.student_id
				LEFT JOIN {$teachers} t ON t.id = b.teacher_id
				LEFT JOIN {$classes} c ON c.id = b.class_id
				WHERE b.id = %d
				LIMIT 1",
				$booking_id
			),
			ARRAY_A
		);

		if ( ! is_array( $row ) ) {
			return null;
		}

		return array(
			'booking_id'       => $booking_id,
			'student_user_id'  => absint( $row['student_user_id'] ),
			'teacher_user_id'  => absint( $row['teacher_user_id'] ),
			'student_name'     => trim( $row['student_first_name'] . ' ' . $row['student_last_name'] ),
			'teacher_name'     => trim( $row['teacher_first_name'] . ' ' . $row['teacher_last_name'] ),
			'student_email'    => is_email( $row['student_email'] ) ? $row['student_email'] : '',
			'teacher_email'    => is_email( $row['teacher_email'] ) ? $row['teacher_email'] : '',
			'class_name'       => isset( $row['class_title'] ) ? (string) $row['class_title'] : '',
			'booking_date'     => isset( $row['booking_date'] ) ? (string) $row['booking_date'] : '',
			'booking_time'     => isset( $row['booking_time'] ) ? (string) $row['booking_time'] : '',
			'amount'           => isset( $row['amount'] ) ? (float) $row['amount'] : 0.0,
			'amount_formatted' => self::format_amount( isset( $row['amount'] ) ? $row['amount'] : 0 ),
		);
	}

	/**
	 * Booking vars for email templates.
	 *
	 * @param array<string, mixed> $ctx Context.
	 * @return array<string, mixed>
	 */
	private static function booking_email_vars( $ctx ) {
		return array(
			'booking_id'   => $ctx['booking_id'],
			'teacher_name' => $ctx['teacher_name'],
			'student_name' => $ctx['student_name'],
			'class_name'   => $ctx['class_name'],
			'booking_date' => $ctx['booking_date'],
			'booking_time' => $ctx['booking_time'],
			'amount'       => $ctx['amount_formatted'],
			'site_name'    => self::site_name(),
		);
	}

	/**
	 * Payment context.
	 *
	 * @param int                  $payment_id Payment ID.
	 * @param array<string, mixed> $row        Optional row.
	 * @return array<string, mixed>|null
	 */
	private static function payment_context( $payment_id, $row = array() ) {
		$payment_id = absint( $payment_id );
		$row        = is_array( $row ) ? $row : array();

		if ( empty( $row['booking_id'] ) && $payment_id ) {
			global $wpdb;
			$table = GMM_Database::table( 'payments' );
			$row   = $wpdb->get_row(
				$wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d LIMIT 1", $payment_id ),
				ARRAY_A
			);
			$row = is_array( $row ) ? $row : array();
		}

		if ( empty( $row ) ) {
			return null;
		}

		$booking_id = isset( $row['booking_id'] ) ? absint( $row['booking_id'] ) : 0;
		$booking    = $booking_id ? self::booking_context( $booking_id ) : null;

		$amount = isset( $row['amount'] ) ? (float) $row['amount'] : 0.0;

		return array(
			'payment_id'       => $payment_id ? $payment_id : ( isset( $row['id'] ) ? absint( $row['id'] ) : 0 ),
			'booking_id'       => $booking_id,
			'amount'           => $amount,
			'amount_formatted' => self::format_amount( $amount ),
			'student_user_id'  => $booking ? $booking['student_user_id'] : 0,
			'teacher_user_id'  => $booking ? $booking['teacher_user_id'] : 0,
			'student_name'     => $booking ? $booking['student_name'] : '',
			'teacher_name'     => $booking ? $booking['teacher_name'] : '',
			'student_email'    => $booking ? $booking['student_email'] : '',
			'teacher_email'    => $booking ? $booking['teacher_email'] : '',
			'class_name'       => $booking ? $booking['class_name'] : '',
		);
	}

	/**
	 * Format money for messages.
	 *
	 * @param mixed $amount Amount.
	 * @return string
	 */
	private static function format_amount( $amount ) {
		$payment = get_option( 'gmm_payment_settings', array() );
		$currency = ( is_array( $payment ) && ! empty( $payment['currency'] ) )
			? sanitize_text_field( (string) $payment['currency'] )
			: 'USD';

		return $currency . ' ' . number_format_i18n( (float) $amount, 2 );
	}
}
