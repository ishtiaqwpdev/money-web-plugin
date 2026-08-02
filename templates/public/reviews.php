<?php
/**
 * Template: public/reviews
 *
 * Public reviews listing shell. Uses existing layout utilities.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

$reviews = ( isset( $reviews ) && is_array( $reviews ) ) ? $reviews : array();
$total   = isset( $total_reviews ) ? (int) $total_reviews : count( $reviews );
$teachers_url = function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teachers' ) : '#';
?>
<div class="gmm-wrapper gmm-frontend">
	<div class="testimonial-area py-120">
		<div class="container">
			<div class="site-heading text-center">
				<span class="site-title-tagline"><?php esc_html_e( 'Reviews', 'gospel-music-mastery' ); ?></span>
				<h2 class="site-title"><?php esc_html_e( 'Student Reviews', 'gospel-music-mastery' ); ?></h2>
				<p><?php esc_html_e( 'Recent feedback from students across Gospel Music Mastery.', 'gospel-music-mastery' ); ?></p>
			</div>

			<?php if ( empty( $reviews ) ) : ?>
				<div class="sl-empty text-center">
					<i class="far fa-star"></i>
					<h3><?php esc_html_e( 'No reviews published yet.', 'gospel-music-mastery' ); ?></h3>
					<p><?php esc_html_e( 'Approved student reviews will appear here.', 'gospel-music-mastery' ); ?></p>
					<a href="<?php echo esc_url( $teachers_url ); ?>" class="theme-btn">
						<i class="far fa-users"></i> <?php esc_html_e( 'Browse Teachers', 'gospel-music-mastery' ); ?>
					</a>
				</div>
			<?php else : ?>
				<p class="mb-4 text-center"><?php echo esc_html( sprintf( /* translators: %d: count */ _n( '%d review', '%d reviews', $total, 'gospel-music-mastery' ), $total ) ); ?></p>
				<div class="row">
					<?php foreach ( $reviews as $review ) :
						$name   = isset( $review['student_name'] ) ? (string) $review['student_name'] : __( 'Student', 'gospel-music-mastery' );
						$teacher = isset( $review['teacher_name'] ) ? (string) $review['teacher_name'] : '';
						$comment = isset( $review['comment'] ) ? (string) $review['comment'] : '';
						$rating  = isset( $review['rating'] ) ? (float) $review['rating'] : 0;
						?>
						<div class="col-md-6 col-lg-4 mb-4">
							<div class="td-card">
								<div class="td-card-head">
									<div>
										<strong><?php echo esc_html( $name ); ?></strong>
										<p><?php echo esc_html( $teacher ); ?></p>
									</div>
									<span class="sb-badge is-confirmed"><?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?> ★</span>
								</div>
								<p><?php echo esc_html( wp_trim_words( $comment, 40 ) ); ?></p>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
