<?php
/**
 * Template: public/teachers
 *
 * Converted from frozen teachers.html. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

$filters   = ( isset( $filters ) && is_array( $filters ) ) ? $filters : array();
$teachers  = ( isset( $teachers ) && is_array( $teachers ) ) ? $teachers : array();
$total     = isset( $total_teachers ) ? (int) $total_teachers : count( $teachers );
$pagination = ( isset( $pagination ) && is_array( $pagination ) ) ? $pagination : array();

$search_q = isset( $filters['search'] ) ? (string) $filters['search'] : '';
$sort     = isset( $filters['sort'] ) ? (string) $filters['sort'] : 'newest';
$rating   = isset( $filters['rating'] ) ? (string) $filters['rating'] : '';
$experience = isset( $filters['experience'] ) ? (string) $filters['experience'] : '';
$price_max  = isset( $filters['price_max'] ) && null !== $filters['price_max'] ? (float) $filters['price_max'] : 100;
$instruments = isset( $filters['instruments'] ) && is_array( $filters['instruments'] ) ? $filters['instruments'] : array();
$inst_lower  = array_map( 'strtolower', $instruments );
$has_inst_filter = ! empty( $instruments );

$sort_ui = $sort;
if ( 'most_students' === $sort ) {
	$sort_ui = 'recommended';
} elseif ( 'highest_rated' === $sort ) {
	$sort_ui = 'rating';
} elseif ( 'price_low' === $sort ) {
	$sort_ui = 'price-low';
} elseif ( 'price_high' === $sort ) {
	$sort_ui = 'price-high';
} elseif ( 'most_classes' === $sort ) {
	$sort_ui = 'most_classes';
} elseif ( 'newest' === $sort ) {
	$sort_ui = 'newest';
}

$level_ui = '';
if ( 'beginner' === $experience ) {
	$level_ui = 'beginner';
} elseif ( 'experienced' === $experience ) {
	$level_ui = 'experienced';
}
?>
<div class="gmm-wrapper gmm-frontend">

        <!-- teachers marketplace -->
        <div class="teachers-market-area py-120">
            <div class="container">

                <!-- hero + search -->
                <section class="tm-hero-card">
                    <div class="tm-hero-copy">
                        <h1>Learn From Experienced Gospel Music Instructors</h1>
                        <p>Connect with skilled teachers who can help you improve your piano, vocals, guitar, and worship music skills.</p>
                    </div>
                    <form class="tm-search-form" id="tm-search-form" action="#" method="get" role="search">
                        <div class="tm-search-field">
                            <i class="far fa-search" aria-hidden="true"></i>
                            <input type="search" class="form-control" id="tm-search-input" name="search"
                                placeholder="Search teachers, instruments, or skills..." autocomplete="off" value="<?php echo esc_attr( $search_q ); ?>">
                        </div>
                        <button type="submit" class="theme-btn"><i class="far fa-search"></i> Search</button>
                    </form>
                </section>

                <div class="tm-layout">
                    <button type="button" class="theme-btn theme-btn-outline tm-filter-toggle" id="tm-filter-toggle"
                        aria-expanded="false" aria-controls="tm-filters">
                        <i class="far fa-sliders"></i> Filters
                    </button>

                    <!-- filters -->
                    <aside class="tm-filters" id="tm-filters" aria-label="Teacher filters">
                        <div class="tm-filters-head">
                            <h3>Filters</h3>
                            <button type="button" class="tm-clear-filters" id="tm-clear-filters">Clear All</button>
                        </div>

                        <div class="tm-filter-group">
                            <h4>Instrument</h4>
                            <div class="tm-check-list">
								<?php
								$inst_labels = array(
									'piano'  => 'Piano',
									'guitar' => 'Guitar',
									'vocals' => 'Vocals',
									'drums'  => 'Drums',
									'organ'  => 'Organ',
									'theory' => 'Music Theory',
								);
								foreach ( $inst_labels as $val => $label ) :
									$checked = ! $has_inst_filter || in_array( $val, $inst_lower, true );
									?>
                                <label class="tm-check"><input type="checkbox" name="instrument" value="<?php echo esc_attr( $val ); ?>" <?php checked( $checked ); ?>> <?php echo esc_html( $label ); ?></label>
								<?php endforeach; ?>
                            </div>
                        </div>

                        <div class="tm-filter-group">
                            <h4>Experience</h4>
                            <div class="tm-radio-list">
                                <label class="tm-check"><input type="radio" name="level" value="" <?php checked( '' === $level_ui ); ?>> Any Experience</label>
                                <label class="tm-check"><input type="radio" name="level" value="beginner" <?php checked( 'beginner' === $level_ui ); ?>> Beginner Teacher</label>
                                <label class="tm-check"><input type="radio" name="level" value="experienced" <?php checked( 'experienced' === $level_ui ); ?>> Experienced Teacher</label>
                            </div>
                        </div>

                        <div class="tm-filter-group">
                            <h4>Price Range</h4>
                            <div class="tm-price-slider">
                                <input type="range" id="tm-price-range" min="20" max="100" value="<?php echo esc_attr( (string) max( 20, min( 100, (int) $price_max ) ) ); ?>" step="5"
                                    aria-label="Maximum price per lesson">
                                <div class="tm-price-labels">
                                    <span>$20</span>
                                    <strong id="tm-price-value">Up to $<?php echo esc_html( (string) max( 20, min( 100, (int) $price_max ) ) ); ?></strong>
                                    <span>$100</span>
                                </div>
                            </div>
                        </div>

                        <div class="tm-filter-group">
                            <h4>Rating</h4>
                            <div class="tm-radio-list">
                                <label class="tm-check"><input type="radio" name="rating" value="" <?php checked( '' === $rating ); ?>> Any Rating</label>
                                <label class="tm-check"><input type="radio" name="rating" value="5" <?php checked( '5' === $rating ); ?>> <span class="tm-stars">★★★★★</span> 5 Stars</label>
                                <label class="tm-check"><input type="radio" name="rating" value="4" <?php checked( '4' === $rating ); ?>> <span class="tm-stars">★★★★☆</span> 4+ Stars</label>
                                <label class="tm-check"><input type="radio" name="rating" value="3" <?php checked( '3' === $rating ); ?>> <span class="tm-stars">★★★☆☆</span> 3+ Stars</label>
                                <label class="tm-check"><input type="radio" name="rating" value="2" <?php checked( '2' === $rating ); ?>> <span class="tm-stars">★★☆☆☆</span> 2+ Stars</label>
                                <label class="tm-check"><input type="radio" name="rating" value="1" <?php checked( '1' === $rating ); ?>> <span class="tm-stars">★☆☆☆☆</span> 1+ Stars</label>
                            </div>
                        </div>

                        <div class="tm-filter-group">
                            <h4>Availability</h4>
                            <div class="tm-check-list">
                                <label class="tm-check"><input type="checkbox" name="availability" value="today"> Available Today</label>
                                <label class="tm-check"><input type="checkbox" name="availability" value="week"> Available This Week</label>
                                <label class="tm-check"><input type="checkbox" name="availability" value="weekend"> Weekend Availability</label>
                            </div>
                        </div>

                        <button type="button" class="theme-btn w-100" id="tm-apply-filters">
                            <i class="far fa-filter"></i> Apply Filters
                        </button>
                    </aside>
                    <div class="tm-filters-backdrop" id="tm-filters-backdrop" hidden></div>

                    <!-- listings -->
                    <div class="tm-listings">
                        <div class="tm-results-bar">
                            <p><strong id="tm-results-count"><?php echo esc_html( (string) $total ); ?></strong> teachers found</p>
                            <div class="tm-sort">
                                <label for="tm-sort-select">Sort by</label>
                                <select class="form-control form-select" id="tm-sort-select">
                                    <option value="newest" <?php selected( $sort_ui, 'newest' ); ?>>Newest Teachers</option>
                                    <option value="recommended" <?php selected( $sort_ui, 'recommended' ); ?>>Most Students</option>
                                    <option value="most_classes" <?php selected( $sort_ui, 'most_classes' ); ?>>Most Classes</option>
                                    <option value="rating" <?php selected( $sort_ui, 'rating' ); ?>>Highest Rated</option>
                                    <option value="price-low" <?php selected( $sort_ui, 'price-low' ); ?>>Price: Low to High</option>
                                    <option value="price-high" <?php selected( $sort_ui, 'price-high' ); ?>>Price: High to Low</option>
                                </select>
                            </div>
                        </div>

                        <div class="tm-empty" id="tm-empty" <?php echo empty( $teachers ) ? '' : 'hidden'; ?>>
                            <i class="far fa-search"></i>
                            <h3>No teachers found.</h3>
                            <p>Try adjusting your filters.</p>
                            <button type="button" class="theme-btn theme-btn-outline" id="tm-empty-reset">
                                <i class="far fa-rotate-left"></i> Reset Filters
                            </button>
                        </div>

                        <div class="tm-grid" id="tm-grid">
							<?php
							if ( ! empty( $teachers ) && class_exists( 'GMM_Teacher_Search' ) ) {
								foreach ( $teachers as $teacher ) {
									echo GMM_Teacher_Search::render_card_html( $teacher ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped inside helper.
								}
							}
							?>
                        </div>

						<?php
						if ( class_exists( 'GMM_Teacher_Search' ) ) {
							echo GMM_Teacher_Search::render_pagination_html( $pagination ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						}
						?>
                    </div>
                </div>
            </div>
        </div>
        <!-- teachers marketplace end -->

</div><!-- .gmm-wrapper -->
