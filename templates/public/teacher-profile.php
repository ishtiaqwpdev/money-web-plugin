<?php
/**
 * Template: public/teacher-profile
 *
 * Converted from frozen student-teacher-profile.html. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! empty( $teacher_missing ) || empty( $teacher ) || ! is_array( $teacher ) ) {
	echo '<div class="gmm-wrapper gmm-frontend"><div class="teacher-public-area py-120"><div class="container"><div class="td-card"><div class="td-card-head"><h3>' . esc_html__( 'This teacher profile is not available.', 'gospel-music-mastery' ) . '</h3><p>' . esc_html__( 'Only approved teachers can be viewed publicly.', 'gospel-music-mastery' ) . '</p>';
	if ( ! empty( $teachers_url ) ) {
		echo '<p><a class="theme-btn" href="' . esc_url( $teachers_url ) . '">' . esc_html__( 'Browse Teachers', 'gospel-music-mastery' ) . '</a></p>';
	}
	echo '</div></div></div></div></div>';
	return;
}

$t = wp_parse_args(
	$teacher,
	array(
		'id'             => 0,
		'name'           => '',
		'specialization' => '',
		'experience'     => '',
		'bio'            => '',
		'rating'         => 0,
		'rating_stars'   => '☆☆☆☆☆',
		'rating_display' => '0.0',
		'students'       => 0,
		'classes'        => 0,
		'image_url'      => '',
		'video_url'      => '',
		'video_poster'   => '',
		'skills'         => array(),
		'booking_url'    => '',
	)
);

$classes            = ( isset( $classes ) && is_array( $classes ) ) ? $classes : array();
$reviews            = ( isset( $reviews ) && is_array( $reviews ) ) ? $reviews : array();
$slots              = ( isset( $slots ) && is_array( $slots ) ) ? $slots : array();
$related            = ( isset( $related ) && is_array( $related ) ) ? $related : array();
$reviewable_classes = ( isset( $reviewable_classes ) && is_array( $reviewable_classes ) ) ? $reviewable_classes : array();
$is_favourite       = ! empty( $is_favourite );
$can_favourite      = ! empty( $can_favourite );
$can_review         = ! empty( $can_review );
$booking_url        = ! empty( $booking_url ) ? $booking_url : $t['booking_url'];
$teachers_url       = ! empty( $teachers_url ) ? $teachers_url : home_url( '/' );
$teacher_id         = ! empty( $teacher_id ) ? absint( $teacher_id ) : absint( $t['id'] );
$rating_summary     = ( isset( $rating_summary ) && is_array( $rating_summary ) ) ? $rating_summary : array( 'average' => $t['rating'], 'total' => 0 );

$login_url = function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'student_login' ) : wp_login_url( get_permalink() );
?>
<div class="gmm-wrapper gmm-frontend" data-teacher-id="<?php echo esc_attr( (string) $teacher_id ); ?>">

        <!-- student teacher profile -->
        <div class="teacher-public-area py-120">
            <div class="container">

                <!-- hero -->
                <section class="tp-hero-card">
                    <div class="tp-hero-media">
                        <img src="<?php echo esc_url( $t['image_url'] ); ?>" alt="<?php echo esc_attr( $t['name'] ); ?>">
                    </div>
                    <div class="tp-hero-content">
                        <span class="login-portal-badge">Gospel Music Instructor</span>
                        <h1><?php echo esc_html( $t['name'] ); ?></h1>
                        <p class="tp-hero-role"><?php echo esc_html( $t['specialization'] ); ?></p>
                        <div class="tp-hero-meta">
                            <span class="td-rating"><?php echo esc_html( $t['rating_stars'] ); ?> <strong><?php echo esc_html( $t['rating_display'] ); ?></strong></span>
                            <span><i class="far fa-users"></i> <?php echo esc_html( (string) (int) $t['students'] ); ?> Students</span>
                            <span><i class="far fa-chalkboard"></i> <?php echo esc_html( (string) (int) $t['classes'] ); ?> Classes</span>
                            <span><i class="far fa-briefcase"></i> <?php echo esc_html( $t['experience'] ); ?></span>
                        </div>
                        <div class="tp-hero-actions">
                            <a href="<?php echo esc_url( $booking_url ? $booking_url : '#book-lesson' ); ?>" class="theme-btn" id="tp-book-lesson-btn"><i class="far fa-calendar-check"></i> Book A Lesson</a>
							<?php if ( $can_favourite ) : ?>
                            <button type="button" class="theme-btn theme-btn-outline" id="tp-favourite-btn" data-favourite="<?php echo $is_favourite ? '1' : '0'; ?>">
                                <i class="<?php echo $is_favourite ? 'fas' : 'far'; ?> fa-heart"></i>
                                <span><?php echo $is_favourite ? esc_html__( 'Remove Favourite', 'gospel-music-mastery' ) : esc_html__( 'Add Favourite', 'gospel-music-mastery' ); ?></span>
                            </button>
							<?php elseif ( ! is_user_logged_in() ) : ?>
                            <a href="<?php echo esc_url( $login_url ); ?>" class="theme-btn theme-btn-outline"><i class="far fa-heart"></i> <?php esc_html_e( 'Add Favourite', 'gospel-music-mastery' ); ?></a>
							<?php endif; ?>
                            <a href="#book-lesson" class="theme-btn theme-btn-outline" data-gmm-message="student" data-teacher-name="<?php echo esc_attr( $t['name'] ); ?>" data-teacher-id="<?php echo esc_attr( (string) $teacher_id ); ?>"><i class="far fa-envelope"></i> Send Message</a>
                        </div>
                    </div>
                </section>

                <div class="tp-layout">
                    <div class="tp-main-col">

                        <!-- about -->
                        <section class="td-card">
                            <div class="td-card-head">
                                <div>
                                    <h3><?php echo esc_html( sprintf( /* translators: %s name */ __( 'About %s', 'gospel-music-mastery' ), $t['name'] ) ); ?></h3>
                                </div>
                            </div>
                            <p class="tp-about-text">
								<?php echo $t['bio'] ? esc_html( $t['bio'] ) : esc_html__( 'This instructor has not added a biography yet.', 'gospel-music-mastery' ); ?>
                            </p>
                            <div class="tp-info-grid">
                                <div class="tp-info-card">
                                    <span class="tp-info-label">Teaching Experience</span>
                                    <strong><?php echo esc_html( $t['experience'] ); ?></strong>
                                </div>
                                <div class="tp-info-card">
                                    <span class="tp-info-label">Specialization</span>
                                    <strong><?php echo esc_html( $t['specialization'] ); ?></strong>
                                </div>
                                <div class="tp-info-card">
                                    <span class="tp-info-label">Lesson Format</span>
                                    <strong><?php esc_html_e( 'Online Live Lessons', 'gospel-music-mastery' ); ?></strong>
                                </div>
                            </div>
                        </section>

                        <!-- skills -->
                        <section class="td-card">
                            <div class="td-card-head">
                                <div>
                                    <h3><?php esc_html_e( 'Skills & Focus Areas', 'gospel-music-mastery' ); ?></h3>
                                </div>
                            </div>
                            <div class="tp-skill-badges">
								<?php if ( ! empty( $t['skills'] ) ) : ?>
									<?php foreach ( $t['skills'] as $skill ) : ?>
                                <span class="tp-skill-badge"><?php echo esc_html( $skill ); ?></span>
									<?php endforeach; ?>
								<?php else : ?>
                                <span class="tp-skill-badge"><?php echo esc_html( $t['specialization'] ); ?></span>
								<?php endif; ?>
                            </div>
                        </section>

						<?php if ( ! empty( $t['video_url'] ) || ! empty( $t['video_poster'] ) ) : ?>
                        <section class="td-card">
                            <div class="td-card-head">
                                <div>
                                    <h3><?php esc_html_e( 'Intro Video', 'gospel-music-mastery' ); ?></h3>
                                </div>
                            </div>
                            <button type="button" class="tp-video-card" id="stp-video-trigger" aria-label="<?php esc_attr_e( 'Play intro video', 'gospel-music-mastery' ); ?>" data-video-url="<?php echo esc_url( $t['video_url'] ); ?>">
                                <img src="<?php echo esc_url( $t['video_poster'] ? $t['video_poster'] : $t['image_url'] ); ?>" alt="">
                                <span class="tp-video-play"><i class="fas fa-play"></i></span>
                            </button>
                        </section>
						<?php endif; ?>

                        <!-- classes -->
                        <section class="td-card">
                            <div class="td-card-head">
                                <div>
                                    <h3><?php esc_html_e( 'Available Classes', 'gospel-music-mastery' ); ?></h3>
                                    <p><?php esc_html_e( 'Choose a class and continue to booking.', 'gospel-music-mastery' ); ?></p>
                                </div>
                            </div>
                            <div class="tp-class-grid">
								<?php if ( empty( $classes ) ) : ?>
                                <p><?php esc_html_e( 'No approved classes are listed yet.', 'gospel-music-mastery' ); ?></p>
								<?php else : ?>
									<?php foreach ( $classes as $class ) : ?>
                                <article class="tp-class-card" data-class="<?php echo esc_attr( $class['title'] ); ?>" data-class-id="<?php echo esc_attr( (string) $class['id'] ); ?>" data-price="<?php echo esc_attr( (string) $class['price'] ); ?>" data-duration="<?php echo esc_attr( $class['duration_label'] ); ?>">
                                    <div class="tp-class-media">
                                        <img src="<?php echo esc_url( $class['image_url'] ); ?>" alt="<?php echo esc_attr( $class['title'] ); ?>">
                                    </div>
                                    <div class="tp-class-body">
                                        <span class="class-manage-category"><?php echo esc_html( $class['category'] ? $class['category'] : $class['difficulty'] ); ?></span>
                                        <h4><?php echo esc_html( $class['title'] ); ?></h4>
                                        <ul class="class-manage-meta">
											<?php if ( $class['duration_label'] ) : ?>
                                            <li><i class="far fa-clock"></i> <?php echo esc_html( $class['duration_label'] ); ?></li>
											<?php endif; ?>
                                            <li><i class="far fa-dollar-sign"></i> <?php echo esc_html( $class['price_display'] ); ?></li>
											<?php if ( $class['difficulty'] ) : ?>
                                            <li><i class="far fa-signal"></i> <?php echo esc_html( $class['difficulty'] ); ?></li>
											<?php endif; ?>
                                        </ul>
                                        <div class="class-manage-footer">
                                            <span class="td-rating"><?php echo esc_html( $class['rating_stars'] ); ?></span>
                                            <a href="<?php echo esc_url( $class['booking_url'] ); ?>" class="theme-btn theme-btn-outline td-action-btn"><?php esc_html_e( 'Book Class', 'gospel-music-mastery' ); ?></a>
                                        </div>
                                    </div>
                                </article>
									<?php endforeach; ?>
								<?php endif; ?>
                            </div>
                        </section>

                        <!-- schedule -->
                        <section class="td-card">
                            <div class="td-card-head">
                                <div>
                                    <h3><?php esc_html_e( 'Upcoming Availability', 'gospel-music-mastery' ); ?></h3>
                                    <p><?php esc_html_e( 'Preview upcoming openings and book a preferred time.', 'gospel-music-mastery' ); ?></p>
                                </div>
                            </div>
                            <div class="stp-schedule-list" role="list">
								<?php if ( empty( $slots ) ) : ?>
                                <p><?php esc_html_e( 'No open time slots are published right now.', 'gospel-music-mastery' ); ?></p>
								<?php else : ?>
									<?php foreach ( $slots as $slot ) : ?>
                                <article class="stp-schedule-item" role="listitem">
                                    <div class="stp-schedule-info">
                                        <strong><?php echo esc_html( $slot['date_label'] ); ?></strong>
                                        <span><i class="far fa-clock"></i> <?php echo esc_html( $slot['time_label'] ); ?></span>
                                    </div>
                                    <a href="<?php echo esc_url( $slot['booking_url'] ); ?>" class="theme-btn theme-btn-outline td-action-btn stp-book-slot"
                                        data-date="<?php echo esc_attr( $slot['date_label'] ); ?>"
                                        data-time="<?php echo esc_attr( $slot['time_label'] ); ?>">
                                        <?php esc_html_e( 'Book This Time', 'gospel-music-mastery' ); ?>
                                    </a>
                                </article>
									<?php endforeach; ?>
								<?php endif; ?>
                            </div>
                        </section>

                        <!-- reviews -->
                        <section class="td-card" id="tp-reviews">
                            <div class="td-card-head">
                                <div>
                                    <h3><?php esc_html_e( 'Reviews From Students', 'gospel-music-mastery' ); ?></h3>
                                    <p><?php echo esc_html( sprintf( /* translators: 1: name 2: count */ __( 'What students say about learning with %1$s (%2$d reviews).', 'gospel-music-mastery' ), $t['name'], (int) $rating_summary['total'] ) ); ?></p>
                                </div>
                            </div>
                            <div class="owl-carousel tp-review-carousel" id="tp-review-list">
								<?php if ( empty( $reviews ) ) : ?>
                                <article class="tp-review-card">
                                    <p><?php esc_html_e( 'No approved reviews yet.', 'gospel-music-mastery' ); ?></p>
                                </article>
								<?php else : ?>
									<?php foreach ( $reviews as $review ) : ?>
										<?php echo class_exists( 'GMM_Teacher_Profile_Public' ) ? GMM_Teacher_Profile_Public::render_review_card( $review ) : ''; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
									<?php endforeach; ?>
								<?php endif; ?>
                            </div>

							<?php if ( $can_review ) : ?>
                            <div class="tp-review-form-wrap mt-4">
                                <h4><?php esc_html_e( 'Leave a Review', 'gospel-music-mastery' ); ?></h4>
                                <form id="tp-review-form" class="tp-review-form" novalidate>
                                    <div class="form-group">
                                        <label for="tp-review-class"><?php esc_html_e( 'Completed Class', 'gospel-music-mastery' ); ?></label>
                                        <select class="form-control form-select" id="tp-review-class" name="class_id" required>
                                            <option value=""><?php esc_html_e( 'Choose a class', 'gospel-music-mastery' ); ?></option>
											<?php foreach ( $reviewable_classes as $rc ) : ?>
                                            <option value="<?php echo esc_attr( (string) $rc['class_id'] ); ?>" data-booking="<?php echo esc_attr( (string) $rc['booking_id'] ); ?>"><?php echo esc_html( $rc['title'] ); ?></option>
											<?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="tp-review-rating"><?php esc_html_e( 'Rating', 'gospel-music-mastery' ); ?></label>
                                        <select class="form-control form-select" id="tp-review-rating" name="rating" required>
                                            <option value="5">★★★★★</option>
                                            <option value="4">★★★★☆</option>
                                            <option value="3">★★★☆☆</option>
                                            <option value="2">★★☆☆☆</option>
                                            <option value="1">★☆☆☆☆</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="tp-review-comment"><?php esc_html_e( 'Your Review', 'gospel-music-mastery' ); ?></label>
                                        <textarea class="form-control" id="tp-review-comment" name="comment" rows="4" required maxlength="1000" placeholder="<?php esc_attr_e( 'Share your experience…', 'gospel-music-mastery' ); ?>"></textarea>
                                    </div>
                                    <div class="gospel-alert gospel-alert-success" id="tp-review-success" hidden>
                                        <i class="far fa-circle-check"></i>
                                        <span><?php esc_html_e( 'Thank you! Your review was submitted for approval.', 'gospel-music-mastery' ); ?></span>
                                    </div>
                                    <div class="gospel-alert gospel-alert-error" id="tp-review-error" hidden>
                                        <i class="far fa-circle-exclamation"></i>
                                        <span id="tp-review-error-text"><?php esc_html_e( 'Could not submit review.', 'gospel-music-mastery' ); ?></span>
                                    </div>
                                    <button type="submit" class="theme-btn"><i class="far fa-star"></i> <?php esc_html_e( 'Submit Review', 'gospel-music-mastery' ); ?></button>
                                </form>
                            </div>
							<?php endif; ?>
                        </section>

                        <!-- related teachers -->
                        <section class="td-card">
                            <div class="td-card-head">
                                <div>
                                    <h3><?php esc_html_e( 'Other Recommended Teachers', 'gospel-music-mastery' ); ?></h3>
                                    <p><?php esc_html_e( 'Explore more gospel music instructors you may like.', 'gospel-music-mastery' ); ?></p>
                                </div>
                                <a href="<?php echo esc_url( $teachers_url ); ?>" class="td-link"><?php esc_html_e( 'Browse All', 'gospel-music-mastery' ); ?></a>
                            </div>
                            <div class="stp-related-grid">
								<?php foreach ( $related as $rel ) : ?>
                                <article class="tm-card stp-related-card">
                                    <div class="tm-card-media">
                                        <img src="<?php echo esc_url( $rel['image_url'] ); ?>" alt="<?php echo esc_attr( $rel['name'] ); ?>">
                                    </div>
                                    <div class="tm-card-body">
                                        <h3><?php echo esc_html( $rel['name'] ); ?></h3>
                                        <p class="tm-specialty"><?php echo esc_html( $rel['specialization'] ); ?></p>
                                        <div class="tm-card-meta">
                                            <span class="tm-rating"><?php echo esc_html( $rel['rating_stars'] ); ?> <strong><?php echo esc_html( $rel['rating_display'] ); ?></strong></span>
                                        </div>
                                        <div class="tm-card-footer">
											<?php if ( ! empty( $rel['price_display'] ) ) : ?>
                                            <strong class="tm-price"><?php echo esc_html( $rel['price_display'] ); ?> <small>/ Lesson</small></strong>
											<?php endif; ?>
                                            <div class="tm-card-actions">
                                                <a href="<?php echo esc_url( $rel['profile_url'] ); ?>" class="theme-btn theme-btn-outline"><?php esc_html_e( 'View Profile', 'gospel-music-mastery' ); ?></a>
                                            </div>
                                        </div>
                                    </div>
                                </article>
								<?php endforeach; ?>
                            </div>
                        </section>

                    </div>

                    <!-- sticky booking card -->
                    <aside class="tp-side-col" id="book-lesson">
                        <div class="td-card tp-booking-card">
                            <div class="td-card-head">
                                <div>
                                    <h3><?php esc_html_e( 'Book Your Lesson', 'gospel-music-mastery' ); ?></h3>
                                    <p><?php esc_html_e( 'Select a class and preferred schedule.', 'gospel-music-mastery' ); ?></p>
                                </div>
                            </div>

                            <div class="gospel-alert gospel-alert-success" id="stp-booking-success" hidden>
                                <i class="far fa-circle-check"></i>
                                <span><?php esc_html_e( 'Opening booking…', 'gospel-music-mastery' ); ?></span>
                            </div>
                            <div class="gospel-alert gospel-alert-error" id="stp-booking-error" hidden>
                                <i class="far fa-circle-exclamation"></i>
                                <span id="stp-booking-error-text"><?php esc_html_e( 'Please complete all booking fields.', 'gospel-music-mastery' ); ?></span>
                            </div>

                            <form action="<?php echo esc_url( $booking_url ); ?>" method="get" id="stp-booking-form" novalidate>
                                <input type="hidden" name="teacher_id" value="<?php echo esc_attr( (string) $teacher_id ); ?>">
                                <div class="form-group">
                                    <label for="stp-select-class"><?php esc_html_e( 'Select Class', 'gospel-music-mastery' ); ?></label>
                                    <select class="form-control form-select" id="stp-select-class" name="class_id" required>
                                        <option value=""><?php esc_html_e( 'Choose a class', 'gospel-music-mastery' ); ?></option>
										<?php foreach ( $classes as $i => $class ) : ?>
                                        <option value="<?php echo esc_attr( (string) $class['id'] ); ?>" data-price="<?php echo esc_attr( (string) (int) $class['price'] ); ?>" <?php selected( 0 === $i ); ?>><?php echo esc_html( $class['title'] ); ?></option>
										<?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="stp-select-date"><?php esc_html_e( 'Select Date', 'gospel-music-mastery' ); ?></label>
                                    <select class="form-control form-select" id="stp-select-date" name="date">
                                        <option value=""><?php esc_html_e( 'Choose a date', 'gospel-music-mastery' ); ?></option>
										<?php
										$dates = array();
										foreach ( $slots as $slot ) {
											if ( ! empty( $slot['date_label'] ) ) {
												$dates[ $slot['date_label'] ] = $slot['date'];
											}
										}
										foreach ( $dates as $label => $raw ) :
											?>
                                        <option value="<?php echo esc_attr( $label ); ?>" data-raw="<?php echo esc_attr( $raw ); ?>"><?php echo esc_html( $label ); ?></option>
										<?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="stp-select-time"><?php esc_html_e( 'Select Time', 'gospel-music-mastery' ); ?></label>
                                    <select class="form-control form-select" id="stp-select-time" name="time">
                                        <option value=""><?php esc_html_e( 'Choose a time', 'gospel-music-mastery' ); ?></option>
										<?php
										$times = array();
										foreach ( $slots as $slot ) {
											if ( ! empty( $slot['time_label'] ) ) {
												$times[ $slot['time_label'] ] = true;
											}
										}
										foreach ( array_keys( $times ) as $time_label ) :
											?>
                                        <option value="<?php echo esc_attr( $time_label ); ?>"><?php echo esc_html( $time_label ); ?></option>
										<?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="tp-booking-price">
                                    <span><?php esc_html_e( 'Price', 'gospel-music-mastery' ); ?></span>
                                    <strong id="stp-booking-price"><?php echo ! empty( $classes[0]['price_display'] ) ? esc_html( $classes[0]['price_display'] ) : '$0'; ?></strong>
                                </div>
                                <button type="submit" class="theme-btn w-100" id="stp-continue-btn">
                                    <i class="far fa-arrow-right"></i> <?php esc_html_e( 'Continue Booking', 'gospel-music-mastery' ); ?>
                                </button>
                            </form>
                        </div>
                    </aside>
                </div>

            </div>
        </div>
        <!-- student teacher profile end -->

    <!-- video modal -->
    <div class="modal fade gospel-demo-modal" id="stp-video-modal" tabindex="-1" aria-labelledby="stp-video-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="stp-video-title"><?php esc_html_e( 'Meet Your Teacher', 'gospel-music-mastery' ); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="tp-video-frame">
                        <video id="stp-video-player" controls playsinline poster="<?php echo esc_url( $t['video_poster'] ? $t['video_poster'] : $t['image_url'] ); ?>">
							<?php if ( $t['video_url'] ) : ?>
                            <source src="<?php echo esc_url( $t['video_url'] ); ?>">
							<?php endif; ?>
                            <?php esc_html_e( 'Your browser does not support the video tag.', 'gospel-music-mastery' ); ?>
                        </video>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div><!-- .gmm-wrapper -->
