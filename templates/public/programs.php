<?php
/**
 * Template: public/programs
 *
 * Program search listing shell (public shortcode). Uses existing layout utilities.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

$programs = ( isset( $programs ) && is_array( $programs ) ) ? $programs : array();
$total    = isset( $total_programs ) ? (int) $total_programs : count( $programs );
$teachers_url = function_exists( 'gmm_get_page_link' ) ? gmm_get_page_link( 'teachers' ) : '#';
?>
<div class="gmm-wrapper gmm-frontend">
	<div class="course-area py-120">
		<div class="container">
			<div class="site-heading text-center">
				<span class="site-title-tagline"><?php esc_html_e( 'Programs', 'gospel-music-mastery' ); ?></span>
				<h2 class="site-title"><?php esc_html_e( 'Gospel Music Programs', 'gospel-music-mastery' ); ?></h2>
				<p><?php esc_html_e( 'Explore structured learning programs for worship musicians.', 'gospel-music-mastery' ); ?></p>
			</div>

			<?php if ( empty( $programs ) ) : ?>
				<div class="sl-empty text-center">
					<i class="far fa-layer-group"></i>
					<h3><?php esc_html_e( 'No programs available yet.', 'gospel-music-mastery' ); ?></h3>
					<p><?php esc_html_e( 'Check back soon or browse teachers for individual lessons.', 'gospel-music-mastery' ); ?></p>
					<a href="<?php echo esc_url( $teachers_url ); ?>" class="theme-btn">
						<i class="far fa-users"></i> <?php esc_html_e( 'Browse Teachers', 'gospel-music-mastery' ); ?>
					</a>
				</div>
			<?php else : ?>
				<p class="mb-4"><?php echo esc_html( sprintf( /* translators: %d: count */ _n( '%d program available', '%d programs available', $total, 'gospel-music-mastery' ), $total ) ); ?></p>
				<div class="row">
					<?php foreach ( $programs as $program ) :
						$title = isset( $program['title'] ) ? (string) $program['title'] : '';
						$cat   = isset( $program['category'] ) ? (string) $program['category'] : '';
						$url   = isset( $program['url'] ) ? (string) $program['url'] : '#';
						?>
						<div class="col-md-6 col-lg-4 mb-4">
							<div class="td-card">
								<div class="td-card-head">
									<div>
										<span class="login-portal-badge"><?php echo esc_html( $cat ? $cat : __( 'Program', 'gospel-music-mastery' ) ); ?></span>
										<h3><?php echo esc_html( $title ); ?></h3>
									</div>
								</div>
								<a href="<?php echo esc_url( $url ); ?>" class="theme-btn theme-btn-outline">
									<?php esc_html_e( 'View Program', 'gospel-music-mastery' ); ?>
								</a>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
</div>
