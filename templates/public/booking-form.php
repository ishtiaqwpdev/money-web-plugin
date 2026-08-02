<?php
/**
 * Template: booking-form (public student flow)
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 * Data bound for teacher/class/slots; interactions via gmm-booking-flow.js.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

$booking_teacher     = ( isset( $booking_teacher ) && is_array( $booking_teacher ) ) ? $booking_teacher : array();
$booking_classes     = ( isset( $booking_classes ) && is_array( $booking_classes ) ) ? $booking_classes : array();
$selected_teacher_id = isset( $selected_teacher_id ) ? absint( $selected_teacher_id ) : 0;
$selected_class_id   = isset( $selected_class_id ) ? absint( $selected_class_id ) : 0;
$selected_class      = ( isset( $selected_class ) && is_array( $selected_class ) ) ? $selected_class : array();
$confirmed_booking   = ( isset( $confirmed_booking ) && is_array( $confirmed_booking ) ) ? $confirmed_booking : null;
$teacher_profile_url = isset( $teacher_profile_url ) ? (string) $teacher_profile_url : '';
$teachers_url        = isset( $teachers_url ) ? (string) $teachers_url : ( function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teachers' ) : '#' );

$teacher_name  = ! empty( $booking_teacher['name'] ) ? (string) $booking_teacher['name'] : __( 'Select a teacher', 'gospel-music-mastery' );
$teacher_role  = ! empty( $booking_teacher['role'] ) ? (string) $booking_teacher['role'] : __( 'Gospel Music Instructor', 'gospel-music-mastery' );
$teacher_image = ! empty( $booking_teacher['image_url'] ) ? (string) $booking_teacher['image_url'] : gmm_design_asset_url( 'assets/img/team/01.jpg' );
if ( ! $teacher_profile_url && ! empty( $booking_teacher['profile_url'] ) ) {
	$teacher_profile_url = (string) $booking_teacher['profile_url'];
}
if ( ! $teacher_profile_url ) {
	$teacher_profile_url = $teachers_url;
}

$duration_label = ! empty( $selected_class['duration_label'] )
	? (string) $selected_class['duration_label']
	: ( ! empty( $selected_class['duration'] ) ? sprintf( '%d Minutes', (int) $selected_class['duration'] ) : '60 Minutes' );
$level_label = ! empty( $selected_class['difficulty'] ) ? (string) $selected_class['difficulty'] : 'Beginner';
$price_label = ! empty( $selected_class['price_display'] )
	? (string) $selected_class['price_display']
	: ( isset( $selected_class['price'] ) ? '$' . number_format_i18n( (float) $selected_class['price'], 0 ) : '$0' );
$class_short = ! empty( $selected_class['short'] )
	? (string) $selected_class['short']
	: ( ! empty( $selected_class['title'] ) ? (string) $selected_class['title'] : __( 'Select a class', 'gospel-music-mastery' ) );

$show_success = is_array( $confirmed_booking ) && ! empty( $confirmed_booking['id'] );
?>
<div class="gmm-wrapper gmm-frontend" id="gmm-booking-flow"
	data-teacher-id="<?php echo esc_attr( (string) $selected_teacher_id ); ?>"
	data-class-id="<?php echo esc_attr( (string) $selected_class_id ); ?>">
        <div class="booking-page-area py-120">
            <div class="container">

                <!-- progress stepper -->
                <ol class="bk-stepper" id="bk-stepper" aria-label="Booking progress">
                    <li class="bk-step is-complete" data-step="1">
                        <span class="bk-step-num">1</span>
                        <span class="bk-step-label">Select Lesson</span>
                    </li>
                    <li class="bk-step is-active" data-step="2">
                        <span class="bk-step-num">2</span>
                        <span class="bk-step-label">Choose Schedule</span>
                    </li>
                    <li class="bk-step" data-step="3">
                        <span class="bk-step-num">3</span>
                        <span class="bk-step-label">Confirm Booking</span>
                    </li>
                </ol>

                <div class="gospel-alert gospel-alert-error" id="bk-error" hidden>
                    <i class="far fa-circle-exclamation"></i>
                    <span id="bk-error-text">Please select a class, date, and time.</span>
                </div>
                <div class="gospel-alert gospel-alert-success" id="bk-success" <?php echo $show_success ? '' : 'hidden'; ?>>
                    <i class="far fa-circle-check"></i>
                    <span id="bk-success-text">
						<?php if ( $show_success ) : ?>
							<?php
							echo esc_html(
								sprintf(
									/* translators: 1: booking id, 2: teacher, 3: class, 4: date, 5: time, 6: status */
									__( 'Booking #%1$s created — %2$s · %3$s · %4$s at %5$s · Status: %6$s (payment pending).', 'gospel-music-mastery' ),
									(string) $confirmed_booking['id'],
									(string) $confirmed_booking['teacher'],
									(string) $confirmed_booking['class'],
									(string) $confirmed_booking['date_label'],
									(string) $confirmed_booking['time_label'],
									(string) $confirmed_booking['status']
								)
							);
							?>
						<?php else : ?>
							Booking created. Payment is pending…
						<?php endif; ?>
					</span>
                </div>

                <form action="#" method="post" id="bk-booking-form" novalidate>
					<input type="hidden" name="teacher_id" id="bk-teacher-id" value="<?php echo esc_attr( (string) $selected_teacher_id ); ?>">
					<input type="hidden" name="class_id" id="bk-class-id" value="<?php echo esc_attr( (string) $selected_class_id ); ?>">
					<input type="hidden" name="duration" id="bk-duration" value="<?php echo esc_attr( (string) ( isset( $selected_class['duration'] ) ? (int) $selected_class['duration'] : 60 ) ); ?>">
                    <div class="bk-layout">

                        <!-- left column -->
                        <div class="bk-main-col">

                            <!-- lesson selection -->
                            <section class="td-card bk-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Choose Your Lesson</h3>
                                        <p>Select the class you want to book with your instructor.</p>
                                    </div>
                                </div>

                                <div class="bk-teacher-row">
                                    <img src="<?php echo esc_url( $teacher_image ); ?>" alt="<?php echo esc_attr( $teacher_name ); ?>" class="bk-teacher-avatar" id="bk-teacher-avatar">
                                    <div>
                                        <h4 id="bk-teacher-name"><?php echo esc_html( $teacher_name ); ?></h4>
                                        <span class="bk-teacher-role" id="bk-teacher-role"><?php echo esc_html( $teacher_role ); ?></span>
                                        <a href="<?php echo esc_url( $teacher_profile_url ); ?>" class="td-link" id="bk-teacher-profile-link">View Profile</a>
                                    </div>
                                </div>

								<?php if ( empty( $selected_teacher_id ) ) : ?>
								<div class="form-group">
									<label for="bk-select-teacher">Select Teacher</label>
									<select class="form-control form-select" id="bk-select-teacher">
										<option value="">Choose a teacher</option>
										<?php
										$booking_teachers = ( isset( $booking_teachers ) && is_array( $booking_teachers ) ) ? $booking_teachers : array();
										foreach ( $booking_teachers as $t ) :
											if ( empty( $t['id'] ) ) {
												continue;
											}
											?>
											<option value="<?php echo esc_attr( (string) $t['id'] ); ?>">
												<?php echo esc_html( isset( $t['name'] ) ? (string) $t['name'] : '' ); ?>
											</option>
										<?php endforeach; ?>
									</select>
								</div>
								<?php endif; ?>

                                <div class="form-group">
                                    <label for="bk-select-class">Select Class</label>
                                    <select class="form-control form-select" id="bk-select-class" required>
                                        <option value="">Choose a class</option>
										<?php foreach ( $booking_classes as $class ) :
											if ( empty( $class['id'] ) ) {
												continue;
											}
											$cid   = (int) $class['id'];
											$title = isset( $class['title'] ) ? (string) $class['title'] : '';
											$short = ! empty( $class['short'] ) ? (string) $class['short'] : $title;
											$dur   = isset( $class['duration'] ) ? (int) $class['duration'] : 60;
											$dur_l = ! empty( $class['duration_label'] ) ? (string) $class['duration_label'] : sprintf( '%d Minutes', $dur );
											$level = ! empty( $class['difficulty'] ) ? (string) $class['difficulty'] : '';
											$price = isset( $class['price'] ) ? (float) $class['price'] : 0.0;
											?>
											<option
												value="<?php echo esc_attr( (string) $cid ); ?>"
												data-short="<?php echo esc_attr( $short ); ?>"
												data-duration="<?php echo esc_attr( $dur_l ); ?>"
												data-duration-mins="<?php echo esc_attr( (string) $dur ); ?>"
												data-level="<?php echo esc_attr( $level ); ?>"
												data-price="<?php echo esc_attr( (string) $price ); ?>"
												<?php selected( $selected_class_id, $cid ); ?>>
												<?php echo esc_html( $title ); ?>
											</option>
										<?php endforeach; ?>
                                    </select>
                                </div>

                                <div class="bk-lesson-details" id="bk-lesson-details">
                                    <div class="bk-detail-item">
                                        <span>Duration</span>
                                        <strong id="bk-detail-duration"><?php echo esc_html( $duration_label ); ?></strong>
                                    </div>
                                    <div class="bk-detail-item">
                                        <span>Level</span>
                                        <strong id="bk-detail-level"><?php echo esc_html( $level_label ); ?></strong>
                                    </div>
                                    <div class="bk-detail-item">
                                        <span>Price</span>
                                        <strong id="bk-detail-price"><?php echo esc_html( $price_label ); ?></strong>
                                    </div>
                                </div>
                            </section>

                            <!-- schedule -->
                            <section class="td-card bk-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Choose Date &amp; Time</h3>
                                        <p>Pick an available date, then select a time slot.</p>
                                    </div>
                                </div>

                                <div class="bk-calendar-wrap">
                                    <div class="bk-calendar-header">
                                        <button type="button" class="bk-cal-nav" id="bk-cal-prev" aria-label="Previous month">
                                            <i class="far fa-angle-left"></i>
                                        </button>
                                        <strong id="bk-cal-month"></strong>
                                        <button type="button" class="bk-cal-nav" id="bk-cal-next" aria-label="Next month">
                                            <i class="far fa-angle-right"></i>
                                        </button>
                                    </div>
                                    <div class="bk-cal-weekdays" aria-hidden="true">
                                        <span>Su</span><span>Mo</span><span>Tu</span><span>We</span><span>Th</span><span>Fr</span><span>Sa</span>
                                    </div>
                                    <div class="bk-cal-grid" id="bk-cal-grid" role="grid" aria-label="Select an available date"></div>
                                    <p class="bk-cal-hint"><i class="far fa-circle-info"></i> Orange dates are available for booking.</p>
                                </div>

                                <div class="bk-slots-block">
                                    <h4>Available Time Slots</h4>
                                    <div class="bk-time-slots" id="bk-time-slots" role="group" aria-label="Available time slots">
                                        <p class="bk-cal-hint" id="bk-slots-hint">Select an available date to view time slots.</p>
                                    </div>
                                    <input type="hidden" id="bk-selected-date" name="booking_date" value="">
                                    <input type="hidden" id="bk-selected-time" name="booking_time" value="">
                                    <input type="hidden" id="bk-selected-date-iso" name="booking_date_iso" value="">
                                    <input type="hidden" id="bk-selected-time-iso" name="booking_time_iso" value="">
                                </div>
                            </section>

                            <!-- notes -->
                            <section class="td-card bk-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Special Notes For Teacher</h3>
                                        <p>Optional message for your instructor before the lesson.</p>
                                    </div>
                                </div>
                                <div class="form-group mb-0">
                                    <label for="bk-notes" class="visually-hidden">Special Notes For Teacher</label>
                                    <textarea class="form-control" id="bk-notes" name="notes" rows="4"
                                        placeholder="Tell your instructor about your learning goals or requirements."></textarea>
                                </div>
                            </section>

                        </div>

                        <!-- right column summary -->
                        <aside class="bk-side-col">
                            <div class="td-card bk-summary-card">
                                <div class="td-card-head">
                                    <div>
                                        <h3>Booking Summary</h3>
                                        <p>Review your lesson details before payment.</p>
                                    </div>
                                </div>

                                <ul class="bk-summary-list">
                                    <li>
                                        <span>Teacher</span>
                                        <strong id="bk-sum-teacher"><?php echo esc_html( $teacher_name ); ?></strong>
                                    </li>
                                    <li>
                                        <span>Class</span>
                                        <strong id="bk-sum-class"><?php echo esc_html( $class_short ); ?></strong>
                                    </li>
                                    <li>
                                        <span>Date</span>
                                        <strong id="bk-sum-date">Select a date</strong>
                                    </li>
                                    <li>
                                        <span>Time</span>
                                        <strong id="bk-sum-time">Select a time</strong>
                                    </li>
                                    <li>
                                        <span>Duration</span>
                                        <strong id="bk-sum-duration"><?php echo esc_html( $duration_label ); ?></strong>
                                    </li>
                                    <li class="bk-summary-total">
                                        <span>Total</span>
                                        <strong id="bk-sum-total"><?php echo esc_html( $price_label ); ?></strong>
                                    </li>
                                </ul>

                                <button type="submit" class="theme-btn w-100" id="bk-proceed-btn" disabled>
                                    <i class="far fa-credit-card"></i> Proceed To Payment
                                </button>
                                <a href="<?php echo esc_url( $teacher_profile_url ); ?>" class="bk-back-link" id="bk-back-link">
                                    <i class="far fa-arrow-left"></i> Back to Teacher Profile
                                </a>
                            </div>
                        </aside>

                    </div>
                </form>

            </div>
        </div>
        <!-- booking area end -->
</div><!-- .gmm-wrapper -->
