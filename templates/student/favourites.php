<?php
/**
 * Template: student-favourites
 *
 * Converted from frozen HTML design. Markup/classes preserved.
 *
 * @package GMM
 */

defined( 'ABSPATH' ) || exit;

if ( ! isset( $user_name ) ) {
	$user_name = 'Guest';
}
if ( ! isset( $user_first_name ) ) {
	$user_first_name = $user_name;
}
?>
<div class="gmm-wrapper gmm-dashboard">

        <!-- student favourites -->
        <div class="student-dashboard-area py-120">
            <div class="container">

                <!-- dashboard profile header -->
                <div class="sd-profile-header">
                    <div class="sd-profile-main">
                        <div class="sd-profile-avatar">
                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="<?php echo esc_attr( $user_name ); ?>">
                        </div>
                        <div class="sd-profile-meta">
                            <h2><?php echo esc_html( $user_name ); ?></h2>
                            <span class="sd-role">Music Student</span>
                            <div class="sd-profile-stats">
                                <span class="sd-stat-item"><i class="far fa-signal"></i> Learning Level: Intermediate</span>
                                <span class="sd-stat-item"><i class="far fa-music"></i> Gospel Piano</span>
                            </div>
                        </div>
                    </div>
                    <div class="sd-profile-actions">
                        <a href="teachers.html" class="theme-btn"><i class="far fa-search"></i> Find Teachers</a>
                    </div>
                </div>

                <div class="sd-shell">
                    <button type="button" class="sd-sidebar-toggle theme-btn theme-btn-outline" id="sd-sidebar-toggle"
                        aria-expanded="false" aria-controls="sd-sidebar">
                        <i class="far fa-bars"></i> Menu
                    </button>

                    <!-- sidebar -->
                    <aside class="sd-sidebar" id="sd-sidebar" aria-label="Student navigation">
                        <nav class="sd-nav">
                            <ul>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_dashboard' ) ); ?>" class="sd-nav-link" data-nav="dashboard"><i class="far fa-grid-2"></i> Dashboard</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_profile' ) ); ?>" class="sd-nav-link" data-nav="profile"><i class="far fa-user"></i> My Profile</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_lessons' ) ); ?>" class="sd-nav-link" data-nav="lessons"><i class="far fa-book-open"></i> My Lessons</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_bookings' ) ); ?>" class="sd-nav-link" data-nav="bookings"><i class="far fa-calendar-check"></i> My Bookings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_dashboard' ) ); ?>" class="sd-nav-link" data-nav="messages"><i class="far fa-comments"></i> Messages</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_favourites' ) ); ?>" class="sd-nav-link active" data-nav="favourites"><i class="far fa-heart"></i> Favourite Teachers</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_payments' ) ); ?>" class="sd-nav-link" data-nav="payments"><i class="far fa-credit-card"></i> Payments</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_settings' ) ); ?>" class="sd-nav-link" data-nav="settings"><i class="far fa-gear"></i> Settings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'student_login' ) ); ?>" class="sd-nav-link sd-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
                            </ul>
                        </nav>
                    </aside>
                    <div class="sd-sidebar-backdrop" id="sd-sidebar-backdrop" hidden></div>

                    <!-- main content -->
                    <div class="sd-main">

                        <!-- page header -->
                        <section class="sd-card sd-welcome-card">
                            <div class="sd-card-head">
                                <div>
                                    <span class="login-portal-badge">My Favorites</span>
                                    <h3>Favourite Teachers</h3>
                                    <p>View the instructors you have saved and continue your learning journey with your preferred teachers.</p>
                                </div>
                                <div class="sf-count-pill">
                                    <i class="fas fa-heart"></i>
                                    <span>Saved Teachers: <strong id="sf-count">8</strong></span>
                                </div>
                            </div>
                        </section>

                        <!-- empty state -->
                        <div class="sd-card sl-empty" id="sf-empty" hidden>
                            <i class="far fa-heart"></i>
                            <h3>No Favourite Teachers Yet</h3>
                            <p>Save your favourite instructors to quickly access their profiles and lessons.</p>
                            <a href="teachers.html" class="theme-btn"><i class="far fa-search"></i> Find Teachers</a>
                        </div>

                        <!-- favourites grid -->
                        <section class="sf-grid" id="sf-grid">

                            <article class="tm-card sf-card" data-name="John Smith">
                                <button type="button" class="sf-heart-btn is-active" aria-label="Remove John Smith from favourites" title="Remove favourite">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <div class="tm-card-media">
                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="John Smith">
                                </div>
                                <div class="tm-card-body">
                                    <h3>John Smith</h3>
                                    <p class="tm-specialty">Gospel Piano Instructor</p>
                                    <div class="tm-card-meta">
                                        <span class="tm-rating">★★★★★ <strong>4.9</strong></span>
                                        <span><i class="far fa-users"></i> 25 Students</span>
                                        <span><i class="far fa-briefcase"></i> 10+ Years</span>
                                    </div>
                                    <div class="tm-card-footer">
                                        <strong class="tm-price">$40 <small>/ Lesson</small></strong>
                                        <div class="tm-card-actions">
                                            <a href="student-teacher-profile.html" class="theme-btn theme-btn-outline">View Profile</a>
                                            <a href="booking.html" class="theme-btn">Book Lesson</a>
                                        </div>
                                    </div>
                                </div>
                            </article>

                            <article class="tm-card sf-card" data-name="Emily Davis">
                                <button type="button" class="sf-heart-btn is-active" aria-label="Remove Emily Davis from favourites" title="Remove favourite">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <div class="tm-card-media">
                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/02.jpg' ) ); ?>" alt="Emily Davis">
                                </div>
                                <div class="tm-card-body">
                                    <h3>Emily Davis</h3>
                                    <p class="tm-specialty">Vocal Coach</p>
                                    <div class="tm-card-meta">
                                        <span class="tm-rating">★★★★★ <strong>5.0</strong></span>
                                        <span><i class="far fa-users"></i> 32 Students</span>
                                        <span><i class="far fa-briefcase"></i> 8+ Years</span>
                                    </div>
                                    <div class="tm-card-footer">
                                        <strong class="tm-price">$45 <small>/ Lesson</small></strong>
                                        <div class="tm-card-actions">
                                            <a href="student-teacher-profile.html" class="theme-btn theme-btn-outline">View Profile</a>
                                            <a href="booking.html" class="theme-btn">Book Lesson</a>
                                        </div>
                                    </div>
                                </div>
                            </article>

                            <article class="tm-card sf-card" data-name="Michael Brown">
                                <button type="button" class="sf-heart-btn is-active" aria-label="Remove Michael Brown from favourites" title="Remove favourite">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <div class="tm-card-media">
                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/03.jpg' ) ); ?>" alt="Michael Brown">
                                </div>
                                <div class="tm-card-body">
                                    <h3>Michael Brown</h3>
                                    <p class="tm-specialty">Guitar Instructor</p>
                                    <div class="tm-card-meta">
                                        <span class="tm-rating">★★★★☆ <strong>4.7</strong></span>
                                        <span><i class="far fa-users"></i> 18 Students</span>
                                        <span><i class="far fa-briefcase"></i> 7+ Years</span>
                                    </div>
                                    <div class="tm-card-footer">
                                        <strong class="tm-price">$40 <small>/ Lesson</small></strong>
                                        <div class="tm-card-actions">
                                            <a href="student-teacher-profile.html" class="theme-btn theme-btn-outline">View Profile</a>
                                            <a href="booking.html" class="theme-btn">Book Lesson</a>
                                        </div>
                                    </div>
                                </div>
                            </article>

                            <article class="tm-card sf-card" data-name="David Wilson">
                                <button type="button" class="sf-heart-btn is-active" aria-label="Remove David Wilson from favourites" title="Remove favourite">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <div class="tm-card-media">
                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/04.jpg' ) ); ?>" alt="David Wilson">
                                </div>
                                <div class="tm-card-body">
                                    <h3>David Wilson</h3>
                                    <p class="tm-specialty">Worship Leader</p>
                                    <div class="tm-card-meta">
                                        <span class="tm-rating">★★★★★ <strong>4.8</strong></span>
                                        <span><i class="far fa-users"></i> 40 Students</span>
                                        <span><i class="far fa-briefcase"></i> 12+ Years</span>
                                    </div>
                                    <div class="tm-card-footer">
                                        <strong class="tm-price">$55 <small>/ Lesson</small></strong>
                                        <div class="tm-card-actions">
                                            <a href="student-teacher-profile.html" class="theme-btn theme-btn-outline">View Profile</a>
                                            <a href="booking.html" class="theme-btn">Book Lesson</a>
                                        </div>
                                    </div>
                                </div>
                            </article>

                            <article class="tm-card sf-card" data-name="Sophia Martinez">
                                <button type="button" class="sf-heart-btn is-active" aria-label="Remove Sophia Martinez from favourites" title="Remove favourite">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <div class="tm-card-media">
                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/05.jpg' ) ); ?>" alt="Sophia Martinez">
                                </div>
                                <div class="tm-card-body">
                                    <h3>Sophia Martinez</h3>
                                    <p class="tm-specialty">Drums Instructor</p>
                                    <div class="tm-card-meta">
                                        <span class="tm-rating">★★★★☆ <strong>4.6</strong></span>
                                        <span><i class="far fa-users"></i> 14 Students</span>
                                        <span><i class="far fa-briefcase"></i> 6+ Years</span>
                                    </div>
                                    <div class="tm-card-footer">
                                        <strong class="tm-price">$42 <small>/ Lesson</small></strong>
                                        <div class="tm-card-actions">
                                            <a href="student-teacher-profile.html" class="theme-btn theme-btn-outline">View Profile</a>
                                            <a href="booking.html" class="theme-btn">Book Lesson</a>
                                        </div>
                                    </div>
                                </div>
                            </article>

                            <article class="tm-card sf-card" data-name="James Lee">
                                <button type="button" class="sf-heart-btn is-active" aria-label="Remove James Lee from favourites" title="Remove favourite">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <div class="tm-card-media">
                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/06.jpg' ) ); ?>" alt="James Lee">
                                </div>
                                <div class="tm-card-body">
                                    <h3>James Lee</h3>
                                    <p class="tm-specialty">Hammond Organ Teacher</p>
                                    <div class="tm-card-meta">
                                        <span class="tm-rating">★★★★★ <strong>4.9</strong></span>
                                        <span><i class="far fa-users"></i> 21 Students</span>
                                        <span><i class="far fa-briefcase"></i> 15+ Years</span>
                                    </div>
                                    <div class="tm-card-footer">
                                        <strong class="tm-price">$50 <small>/ Lesson</small></strong>
                                        <div class="tm-card-actions">
                                            <a href="student-teacher-profile.html" class="theme-btn theme-btn-outline">View Profile</a>
                                            <a href="booking.html" class="theme-btn">Book Lesson</a>
                                        </div>
                                    </div>
                                </div>
                            </article>

                            <article class="tm-card sf-card" data-name="Olivia Harris">
                                <button type="button" class="sf-heart-btn is-active" aria-label="Remove Olivia Harris from favourites" title="Remove favourite">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <div class="tm-card-media">
                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/07.jpg' ) ); ?>" alt="Olivia Harris">
                                </div>
                                <div class="tm-card-body">
                                    <h3>Olivia Harris</h3>
                                    <p class="tm-specialty">Music Theory Tutor</p>
                                    <div class="tm-card-meta">
                                        <span class="tm-rating">★★★★★ <strong>4.8</strong></span>
                                        <span><i class="far fa-users"></i> 27 Students</span>
                                        <span><i class="far fa-briefcase"></i> 9+ Years</span>
                                    </div>
                                    <div class="tm-card-footer">
                                        <strong class="tm-price">$38 <small>/ Lesson</small></strong>
                                        <div class="tm-card-actions">
                                            <a href="student-teacher-profile.html" class="theme-btn theme-btn-outline">View Profile</a>
                                            <a href="booking.html" class="theme-btn">Book Lesson</a>
                                        </div>
                                    </div>
                                </div>
                            </article>

                            <article class="tm-card sf-card" data-name="Daniel Clark">
                                <button type="button" class="sf-heart-btn is-active" aria-label="Remove Daniel Clark from favourites" title="Remove favourite">
                                    <i class="fas fa-heart"></i>
                                </button>
                                <div class="tm-card-media">
                                    <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/08.jpg' ) ); ?>" alt="Daniel Clark">
                                </div>
                                <div class="tm-card-body">
                                    <h3>Daniel Clark</h3>
                                    <p class="tm-specialty">Gospel Guitar Coach</p>
                                    <div class="tm-card-meta">
                                        <span class="tm-rating">★★★★☆ <strong>4.5</strong></span>
                                        <span><i class="far fa-users"></i> 11 Students</span>
                                        <span><i class="far fa-briefcase"></i> 4+ Years</span>
                                    </div>
                                    <div class="tm-card-footer">
                                        <strong class="tm-price">$35 <small>/ Lesson</small></strong>
                                        <div class="tm-card-actions">
                                            <a href="student-teacher-profile.html" class="theme-btn theme-btn-outline">View Profile</a>
                                            <a href="booking.html" class="theme-btn">Book Lesson</a>
                                        </div>
                                    </div>
                                </div>
                            </article>

                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- student favourites end -->

    
</div><!-- .gmm-wrapper -->

