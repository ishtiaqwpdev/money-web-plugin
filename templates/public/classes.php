<?php
/**
 * Template: public/classes
 *
 * Class search listing shell (public shortcode). Uses existing layout utilities.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

$classes = ( isset( $classes ) && is_array( $classes ) ) ? $classes : array();
$total   = isset( $total_classes ) ? (int) $total_classes : count( $classes );
$teachers_url = function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teachers' ) : '#';
?>
<div class="gmm-wrapper gmm-frontend">
	<div class="course-area py-120">
		<div class="container">
			<div class="site-heading text-center">
				<span class="site-title-tagline"><?php esc_html_e( 'Classes', 'gospel-music-mastery' ); ?></span>
				<h2 class="site-title"><?php esc_html_e( 'Find Gospel Music Classes', 'gospel-music-mastery' ); ?></h2>
				<p><?php esc_html_e( 'Browse available lessons from approved teachers.', 'gospel-music-mastery' ); ?></p>
			</div>

			<?php if ( empty( $classes ) ) : ?>
				<div class="sl-empty text-center">
					<i class="far fa-book-open"></i>
					<h3><?php esc_html_e( 'No classes listed yet.', 'gospel-music-mastery' ); ?></h3>
					<p><?php esc_html_e( 'Explore teachers to discover upcoming lessons.', 'gospel-music-mastery' ); ?></p>
					<a href="<?php echo esc_url( $teachers_url ); ?>" class="theme-btn">
						<i class="far fa-users"></i> <?php esc_html_e( 'Browse Teachers', 'gospel-music-mastery' ); ?>
					</a>
				</div>
			<?php else : ?>
				<p class="mb-4"><?php echo esc_html( sprintf( /* translators: %d: count */ _n( '%d class available', '%d classes available', $total, 'gospel-music-mastery' ), $total ) ); ?></p>
				<div class="row">
					<?php foreach ( $classes as $class ) :
						$title = isset( $class['title'] ) ? (string) $class['title'] : '';
						$teacher = isset( $class['teacher_name'] ) ? (string) $class['teacher_name'] : '';
						$price = isset( $class['price_display'] ) ? (string) $class['price_display'] : '';
						$url = isset( $class['url'] ) ? (string) $class['url'] : $teachers_url;
						?>
						<div class="col-md-6 col-lg-4 mb-4">
							<div class="td-card">
								<div class="td-card-head">
									<div>
										<h3><?php echo esc_html( $title ); ?></h3>
										<p><?php echo esc_html( $teacher ); ?></p>
									</div>
									<?php if ( $price ) : ?>
										<strong><?php echo esc_html( $price ); ?></strong>
									<?php endif; ?>
								</div>
								<a href="<?php echo esc_url( $url ); ?>" class="theme-btn theme-btn-outline">
									<?php esc_html_e( 'View Details', 'gospel-music-mastery' ); ?>
								</a>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
