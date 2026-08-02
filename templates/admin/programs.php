<?php
/**
 * Template: admin-programs
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
<div class="gmm-wrapper gmm-dashboard gmm-admin">

        <!-- admin programs -->
        <div class="admin-dashboard-area py-120">
            <div class="container">

                <!-- admin top header -->
                <header class="sd-profile-header ad-topbar">
                    <div class="sd-profile-main ad-topbar-main">
                        <div class="sd-profile-avatar ad-topbar-avatar">
                            <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="<?php echo esc_attr( $user_name ); ?>">
                        </div>
                        <div class="sd-profile-meta">
                            <h2><?php echo esc_html( $user_name ); ?></h2>
                            <span class="sd-role">Platform Admin</span>
                            <div class="sd-profile-stats">
                                <span class="sd-stat-item"><i class="far fa-shield-check"></i> Full Access</span>
                                <span class="sd-stat-item"><i class="far fa-clock"></i> Last login: Today, 09:12 AM</span>
                            </div>
                        </div>
                    </div>

                    <div class="ad-topbar-actions">
                        <div class="dropdown ad-icon-dropdown">
                            <button class="ad-icon-btn" type="button" id="ad-notifications" data-bs-toggle="dropdown"
                                aria-expanded="false" aria-label="Notifications">
                                <i class="far fa-bell"></i>
                                <span class="ad-icon-badge">4</span>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end ad-dropdown" aria-labelledby="ad-notifications">
                                <h6 class="ad-dropdown-title">Notifications</h6>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_teachers' ) ); ?>"><i class="far fa-user-plus"></i> <span><strong>John Smith</strong> applied as a teacher</span></a>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_classes' ) ); ?>"><i class="far fa-chalkboard"></i> <span>New class submitted for review</span></a>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_payments' ) ); ?>"><i class="far fa-credit-card"></i> <span>Payment of <strong>$40</strong> received</span></a>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_teachers' ) ); ?>"><i class="far fa-triangle-exclamation"></i> <span>15 approvals are pending</span></a>
                            </div>
                        </div>

                        <div class="dropdown ad-profile-dropdown">
                            <button class="ad-profile-btn" type="button" id="ad-profile-menu" data-bs-toggle="dropdown" aria-expanded="false">
                                <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/team/01.jpg' ) ); ?>" alt="<?php echo esc_attr( $user_name ); ?>">
                                <span class="ad-profile-btn-text">
                                    <strong>Administrator</strong>
                                    <small>Platform Admin</small>
                                </span>
                                <i class="far fa-angle-down"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-end ad-dropdown" aria-labelledby="ad-profile-menu">
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_settings' ) ); ?>"><i class="far fa-user"></i> <span>My Profile</span></a>
                                <a class="dropdown-item ad-dropdown-item" href="<?php echo esc_url( gmm_get_page_link( 'admin_settings' ) ); ?>"><i class="far fa-gear"></i> <span>Settings</span></a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item ad-dropdown-item is-logout" href="admin-login.html"><i class="far fa-right-from-bracket"></i> <span>Logout</span></a>
                            </div>
                        </div>
                    </div>
                </header>

                <div class="sd-shell ad-shell">
                    <button type="button" class="sd-sidebar-toggle theme-btn theme-btn-outline" id="sd-sidebar-toggle"
                        aria-expanded="false" aria-controls="sd-sidebar">
                        <i class="far fa-bars"></i> Menu
                    </button>

                    <aside class="sd-sidebar ad-sidebar" id="sd-sidebar" aria-label="Admin navigation">
                        <nav class="sd-nav ad-nav">
                            <ul>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_dashboard' ) ); ?>" class="sd-nav-link" data-nav="dashboard"><i class="far fa-grid-2"></i> Dashboard</a></li>
                                <li class="ad-nav-group is-open" id="ad-users-group">
                                    <button type="button" class="sd-nav-link ad-nav-parent" id="ad-users-toggle"
                                        aria-expanded="true" aria-controls="ad-users-submenu">
                                        <i class="far fa-users"></i> Users
                                        <i class="far fa-angle-down ad-nav-caret"></i>
                                    </button>
                                    <ul class="ad-submenu" id="ad-users-submenu">
                                        <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_teachers' ) ); ?>" class="sd-nav-link ad-sub-link" data-nav="teachers"><i class="far fa-chalkboard-user"></i> Teachers</a></li>
                                        <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_students' ) ); ?>" class="sd-nav-link ad-sub-link" data-nav="students"><i class="far fa-graduation-cap"></i> Students</a></li>
                                    </ul>
                                </li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_classes' ) ); ?>" class="sd-nav-link" data-nav="classes"><i class="far fa-chalkboard"></i> Classes</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_bookings' ) ); ?>" class="sd-nav-link" data-nav="bookings"><i class="far fa-calendar-check"></i> Bookings</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_payments' ) ); ?>" class="sd-nav-link" data-nav="payments"><i class="far fa-credit-card"></i> Payments</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_programs' ) ); ?>" class="sd-nav-link active" data-nav="programs"><i class="far fa-music"></i> Music Programs</a></li>
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_settings' ) ); ?>" class="sd-nav-link" data-nav="settings"><i class="far fa-gear"></i> Settings</a></li>
                                <li><a href="admin-login.html" class="sd-nav-link sd-nav-logout" data-nav="logout"><i class="far fa-right-from-bracket"></i> Logout</a></li>
                            </ul>
                        </nav>
                    </aside>
                    <div class="sd-sidebar-backdrop" id="sd-sidebar-backdrop" hidden></div>

                    <div class="sd-main ad-main">

                        <section class="sd-card sd-welcome-card">
                            <div class="sd-card-head">
                                <div>
                                    <span class="login-portal-badge">Content Management</span>
                                    <h3>Manage Music Programs</h3>
                                    <p>Create, update, and manage music programs available for students and teachers.</p>
                                </div>
                                <button type="button" class="theme-btn" id="apr-add-btn">
                                    <i class="far fa-plus"></i> Add New Program
                                </button>
                            </div>
                        </section>

                        <!-- summary cards -->
                        <section class="sd-stats-grid ad-stats-grid at-stats-grid">
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-music"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="8" data-format="number">0</span>
                                    <span class="sd-stat-title">Total Programs</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="6" data-format="number">0</span>
                                    <span class="sd-stat-title">Active Programs</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card is-pending">
                                <div class="sd-stat-icon"><i class="far fa-file-pen"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="2" data-format="number">0</span>
                                    <span class="sd-stat-title">Draft Programs</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-users"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="1250" data-format="number">0</span>
                                    <span class="sd-stat-title">Total Enrollments</span>
                                </div>
                            </div>
                        </section>

                        <!-- analytics charts -->
                        <div class="gmm-chart-grid">
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Program Enrollment Chart</h3>
                                        <p>Student enrollments by program category.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-apr-enrollment" aria-label="Program enrollment bar chart"></canvas>
                                </div>
                            </section>
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Program Category</h3>
                                        <p>Programs offered by category.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap is-doughnut">
                                    <canvas id="gmm-apr-category" aria-label="Program category doughnut chart"></canvas>
                                </div>
                            </section>
                        </div>

                        <!-- programs list -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>All Programs</h3>
                                    <p>Search and manage learning programs offered on the platform.</p>
                                </div>
                                <span class="sf-count-pill" id="apr-result-count"><i class="far fa-music"></i> <strong>8</strong> Shown</span>
                            </div>

                            <form class="at-filter-bar" id="apr-filter-form" action="#" method="get">
                                <div class="at-search-field">
                                    <i class="far fa-search" aria-hidden="true"></i>
                                    <input type="search" class="form-control" id="apr-search"
                                        placeholder="Search programs..." autocomplete="off">
                                </div>
                                <div class="at-filter-selects">
                                    <div class="form-group mb-0">
                                        <label for="apr-category" class="visually-hidden">Category</label>
                                        <select class="form-control form-select" id="apr-category">
                                            <option value="all">All Categories</option>
                                            <option value="piano">Piano</option>
                                            <option value="vocals">Vocals</option>
                                            <option value="guitar">Guitar</option>
                                            <option value="drums">Drums</option>
                                            <option value="theory">Theory</option>
                                            <option value="worship">Worship</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="apr-status" class="visually-hidden">Status</label>
                                        <select class="form-control form-select" id="apr-status">
                                            <option value="all">All Status</option>
                                            <option value="active">Active</option>
                                            <option value="draft">Draft</option>
                                            <option value="inactive">Inactive</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="theme-btn"><i class="far fa-filter"></i> Filter</button>
                                </div>
                            </form>

                            <div class="apr-grid" id="apr-grid">

                                <article class="apr-card"
                                    data-id="PRG-01"
                                    data-name="Gospel Piano Mastery"
                                    data-category="piano"
                                    data-category-label="Piano"
                                    data-description="Learn gospel piano fundamentals and worship techniques."
                                    data-duration="12 Weeks"
                                    data-difficulty="Beginner"
                                    data-enrolled="250"
                                    data-status="active"
                                    data-featured="true"
                                    data-image="assets/img/course/01.jpg">
                                    <div class="apr-card-media">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/01.jpg' ) ); ?>" alt="Gospel Piano Mastery">
                                        <span class="sb-badge is-confirmed apr-status">Active</span>
                                    </div>
                                    <div class="apr-card-body">
                                        <span class="apr-category">Piano</span>
                                        <h4>Gospel Piano Mastery</h4>
                                        <p>Learn gospel piano fundamentals and worship techniques.</p>
                                        <div class="apr-card-meta">
                                            <span><i class="far fa-users"></i> 250 Enrolled</span>
                                            <span><i class="far fa-clock"></i> 12 Weeks</span>
                                        </div>
                                        <div class="apr-card-actions">
                                            <button type="button" class="theme-btn theme-btn-outline apr-view-btn"><i class="far fa-eye"></i> View</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-edit-btn"><i class="far fa-pen"></i> Edit</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-delete-btn"><i class="far fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </article>

                                <article class="apr-card"
                                    data-id="PRG-02"
                                    data-name="Worship Vocal Training"
                                    data-category="vocals"
                                    data-category-label="Vocals"
                                    data-description="Develop vocal control, harmony, and worship leading skills."
                                    data-duration="10 Weeks"
                                    data-difficulty="Intermediate"
                                    data-enrolled="320"
                                    data-status="active"
                                    data-featured="true"
                                    data-image="assets/img/course/02.jpg">
                                    <div class="apr-card-media">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/02.jpg' ) ); ?>" alt="Worship Vocal Training">
                                        <span class="sb-badge is-confirmed apr-status">Active</span>
                                    </div>
                                    <div class="apr-card-body">
                                        <span class="apr-category">Vocals</span>
                                        <h4>Worship Vocal Training</h4>
                                        <p>Develop vocal control, harmony, and worship leading skills.</p>
                                        <div class="apr-card-meta">
                                            <span><i class="far fa-users"></i> 320 Enrolled</span>
                                            <span><i class="far fa-clock"></i> 10 Weeks</span>
                                        </div>
                                        <div class="apr-card-actions">
                                            <button type="button" class="theme-btn theme-btn-outline apr-view-btn"><i class="far fa-eye"></i> View</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-edit-btn"><i class="far fa-pen"></i> Edit</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-delete-btn"><i class="far fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </article>

                                <article class="apr-card"
                                    data-id="PRG-03"
                                    data-name="Church Guitar Basics"
                                    data-category="guitar"
                                    data-category-label="Guitar"
                                    data-description="Master chord progressions and rhythm for church worship teams."
                                    data-duration="8 Weeks"
                                    data-difficulty="Beginner"
                                    data-enrolled="180"
                                    data-status="active"
                                    data-featured="true"
                                    data-image="assets/img/course/03.jpg">
                                    <div class="apr-card-media">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/03.jpg' ) ); ?>" alt="Church Guitar Basics">
                                        <span class="sb-badge is-confirmed apr-status">Active</span>
                                    </div>
                                    <div class="apr-card-body">
                                        <span class="apr-category">Guitar</span>
                                        <h4>Church Guitar Basics</h4>
                                        <p>Master chord progressions and rhythm for church worship teams.</p>
                                        <div class="apr-card-meta">
                                            <span><i class="far fa-users"></i> 180 Enrolled</span>
                                            <span><i class="far fa-clock"></i> 8 Weeks</span>
                                        </div>
                                        <div class="apr-card-actions">
                                            <button type="button" class="theme-btn theme-btn-outline apr-view-btn"><i class="far fa-eye"></i> View</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-edit-btn"><i class="far fa-pen"></i> Edit</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-delete-btn"><i class="far fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </article>

                                <article class="apr-card"
                                    data-id="PRG-04"
                                    data-name="Music Theory Fundamentals"
                                    data-category="theory"
                                    data-category-label="Theory"
                                    data-description="Build a strong foundation in scales, chords, and gospel harmony."
                                    data-duration="6 Weeks"
                                    data-difficulty="Beginner"
                                    data-enrolled="210"
                                    data-status="active"
                                    data-featured="false"
                                    data-image="assets/img/course/05.jpg">
                                    <div class="apr-card-media">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/05.jpg' ) ); ?>" alt="Music Theory Fundamentals">
                                        <span class="sb-badge is-confirmed apr-status">Active</span>
                                    </div>
                                    <div class="apr-card-body">
                                        <span class="apr-category">Theory</span>
                                        <h4>Music Theory Fundamentals</h4>
                                        <p>Build a strong foundation in scales, chords, and gospel harmony.</p>
                                        <div class="apr-card-meta">
                                            <span><i class="far fa-users"></i> 210 Enrolled</span>
                                            <span><i class="far fa-clock"></i> 6 Weeks</span>
                                        </div>
                                        <div class="apr-card-actions">
                                            <button type="button" class="theme-btn theme-btn-outline apr-view-btn"><i class="far fa-eye"></i> View</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-edit-btn"><i class="far fa-pen"></i> Edit</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-delete-btn"><i class="far fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </article>

                                <article class="apr-card"
                                    data-id="PRG-05"
                                    data-name="Gospel Drum Grooves"
                                    data-category="drums"
                                    data-category-label="Drums"
                                    data-description="Learn church-ready drum grooves and pocket timing."
                                    data-duration="9 Weeks"
                                    data-difficulty="Intermediate"
                                    data-enrolled="145"
                                    data-status="active"
                                    data-featured="false"
                                    data-image="assets/img/course/04.jpg">
                                    <div class="apr-card-media">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/04.jpg' ) ); ?>" alt="Gospel Drum Grooves">
                                        <span class="sb-badge is-confirmed apr-status">Active</span>
                                    </div>
                                    <div class="apr-card-body">
                                        <span class="apr-category">Drums</span>
                                        <h4>Gospel Drum Grooves</h4>
                                        <p>Learn church-ready drum grooves and pocket timing.</p>
                                        <div class="apr-card-meta">
                                            <span><i class="far fa-users"></i> 145 Enrolled</span>
                                            <span><i class="far fa-clock"></i> 9 Weeks</span>
                                        </div>
                                        <div class="apr-card-actions">
                                            <button type="button" class="theme-btn theme-btn-outline apr-view-btn"><i class="far fa-eye"></i> View</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-edit-btn"><i class="far fa-pen"></i> Edit</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-delete-btn"><i class="far fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </article>

                                <article class="apr-card"
                                    data-id="PRG-06"
                                    data-name="Worship Band Leadership"
                                    data-category="worship"
                                    data-category-label="Worship"
                                    data-description="Lead rehearsals, setlists, and live worship teams with confidence."
                                    data-duration="14 Weeks"
                                    data-difficulty="Advanced"
                                    data-enrolled="95"
                                    data-status="active"
                                    data-featured="false"
                                    data-image="assets/img/course/06.jpg">
                                    <div class="apr-card-media">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/06.jpg' ) ); ?>" alt="Worship Band Leadership">
                                        <span class="sb-badge is-confirmed apr-status">Active</span>
                                    </div>
                                    <div class="apr-card-body">
                                        <span class="apr-category">Worship</span>
                                        <h4>Worship Band Leadership</h4>
                                        <p>Lead rehearsals, setlists, and live worship teams with confidence.</p>
                                        <div class="apr-card-meta">
                                            <span><i class="far fa-users"></i> 95 Enrolled</span>
                                            <span><i class="far fa-clock"></i> 14 Weeks</span>
                                        </div>
                                        <div class="apr-card-actions">
                                            <button type="button" class="theme-btn theme-btn-outline apr-view-btn"><i class="far fa-eye"></i> View</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-edit-btn"><i class="far fa-pen"></i> Edit</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-delete-btn"><i class="far fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </article>

                                <article class="apr-card"
                                    data-id="PRG-07"
                                    data-name="Advanced Worship Piano"
                                    data-category="piano"
                                    data-category-label="Piano"
                                    data-description="Advanced voicings, fills, and spontaneous worship playing."
                                    data-duration="11 Weeks"
                                    data-difficulty="Advanced"
                                    data-enrolled="0"
                                    data-status="draft"
                                    data-featured="false"
                                    data-image="assets/img/course/01.jpg">
                                    <div class="apr-card-media">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/01.jpg' ) ); ?>" alt="Advanced Worship Piano">
                                        <span class="sb-badge is-pending apr-status">Draft</span>
                                    </div>
                                    <div class="apr-card-body">
                                        <span class="apr-category">Piano</span>
                                        <h4>Advanced Worship Piano</h4>
                                        <p>Advanced voicings, fills, and spontaneous worship playing.</p>
                                        <div class="apr-card-meta">
                                            <span><i class="far fa-users"></i> 0 Enrolled</span>
                                            <span><i class="far fa-clock"></i> 11 Weeks</span>
                                        </div>
                                        <div class="apr-card-actions">
                                            <button type="button" class="theme-btn theme-btn-outline apr-view-btn"><i class="far fa-eye"></i> View</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-edit-btn"><i class="far fa-pen"></i> Edit</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-delete-btn"><i class="far fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </article>

                                <article class="apr-card"
                                    data-id="PRG-08"
                                    data-name="Choir Harmony Coaching"
                                    data-category="vocals"
                                    data-category-label="Vocals"
                                    data-description="Stack gospel harmonies and coach choir section blending."
                                    data-duration="7 Weeks"
                                    data-difficulty="Intermediate"
                                    data-enrolled="50"
                                    data-status="draft"
                                    data-featured="false"
                                    data-image="assets/img/course/02.jpg">
                                    <div class="apr-card-media">
                                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/02.jpg' ) ); ?>" alt="Choir Harmony Coaching">
                                        <span class="sb-badge is-pending apr-status">Draft</span>
                                    </div>
                                    <div class="apr-card-body">
                                        <span class="apr-category">Vocals</span>
                                        <h4>Choir Harmony Coaching</h4>
                                        <p>Stack gospel harmonies and coach choir section blending.</p>
                                        <div class="apr-card-meta">
                                            <span><i class="far fa-users"></i> 50 Enrolled</span>
                                            <span><i class="far fa-clock"></i> 7 Weeks</span>
                                        </div>
                                        <div class="apr-card-actions">
                                            <button type="button" class="theme-btn theme-btn-outline apr-view-btn"><i class="far fa-eye"></i> View</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-edit-btn"><i class="far fa-pen"></i> Edit</button>
                                            <button type="button" class="theme-btn theme-btn-outline apr-delete-btn"><i class="far fa-trash"></i> Delete</button>
                                        </div>
                                    </div>
                                </article>

                            </div>

                            <div class="sl-empty" id="apr-empty" hidden>
                                <i class="far fa-music"></i>
                                <h3>No programs available.</h3>
                                <p>Try adjusting your search or filter options, or add a new program.</p>
                            </div>
                        </section>

                        <!-- featured programs -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Featured Programs</h3>
                                    <p>Toggle which programs appear as featured on the platform.</p>
                                </div>
                            </div>
                            <div class="apr-featured-list" id="apr-featured-list">
                                <!-- filled by JS from program cards -->
                            </div>
                            <div class="sl-empty" id="apr-featured-empty" hidden>
                                <i class="far fa-star"></i>
                                <h3>No featured programs.</h3>
                                <p>Use the toggles below each program listing to feature content.</p>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- admin programs end -->

    

<!-- add / edit program modal -->
    <div class="modal fade gospel-demo-modal" id="apr-form-modal" tabindex="-1" aria-labelledby="apr-form-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="apr-form-title">Add New Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="apr-program-form" action="#" method="post">
                    <div class="modal-body at-modal-body">
                        <input type="hidden" id="apr-form-id" value="">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="apr-form-image">Program Image Upload</label>
                                    <input type="file" class="form-control" id="apr-form-image" accept="image/*">
                                    <small class="form-text text-muted">Frontend demo only — image is not uploaded.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="apr-form-name">Program Name</label>
                                    <input type="text" class="form-control" id="apr-form-name" placeholder="e.g. Gospel Piano Mastery" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="apr-form-category">Category</label>
                                    <select class="form-control form-select" id="apr-form-category" required>
                                        <option value="piano">Piano</option>
                                        <option value="vocals">Vocals</option>
                                        <option value="guitar">Guitar</option>
                                        <option value="drums">Drums</option>
                                        <option value="theory">Theory</option>
                                        <option value="worship">Worship</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="apr-form-description">Description</label>
                                    <textarea class="form-control" id="apr-form-description" rows="3"
                                        placeholder="Describe the program..." required></textarea>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="apr-form-duration">Duration</label>
                                    <input type="text" class="form-control" id="apr-form-duration" placeholder="e.g. 12 Weeks" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="apr-form-difficulty">Difficulty</label>
                                    <select class="form-control form-select" id="apr-form-difficulty" required>
                                        <option value="Beginner">Beginner</option>
                                        <option value="Intermediate">Intermediate</option>
                                        <option value="Advanced">Advanced</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="apr-form-status">Status</label>
                                    <select class="form-control form-select" id="apr-form-status" required>
                                        <option value="active">Active</option>
                                        <option value="draft">Draft</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="theme-btn"><i class="far fa-floppy-disk"></i> Save Program</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- program detail modal -->
    <div class="modal fade gospel-demo-modal" id="apr-detail-modal" tabindex="-1" aria-labelledby="apr-detail-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="apr-detail-title">Program Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body at-modal-body">
                    <div class="ac-modal-hero apr-detail-hero">
                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/course/01.jpg' ) ); ?>" alt="Program" id="apr-detail-image">
                        <div>
                            <span class="apr-category" id="apr-detail-category">Piano</span>
                            <h4 id="apr-detail-name">Gospel Piano Mastery</h4>
                            <p id="apr-detail-description">Learn gospel piano fundamentals and worship techniques.</p>
                            <span class="sb-badge is-confirmed" id="apr-detail-status">Active</span>
                        </div>
                    </div>
                    <ul class="booking-modal-list at-modal-list">
                        <li><span>Duration</span><strong id="apr-detail-duration">12 Weeks</strong></li>
                        <li><span>Difficulty</span><strong id="apr-detail-difficulty">Beginner</strong></li>
                        <li><span>Enrolled Students</span><strong id="apr-detail-enrolled">250</strong></li>
                        <li><span>Program ID</span><strong id="apr-detail-id">PRG-01</strong></li>
                    </ul>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="theme-btn" id="apr-detail-edit-btn"><i class="far fa-pen"></i> Edit Program</button>
                </div>
            </div>
        </div>
    </div>


    <!-- delete confirm modal -->
    <div class="modal fade gospel-demo-modal" id="apr-delete-modal" tabindex="-1" aria-labelledby="apr-delete-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="apr-delete-title">Delete Program</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete <strong id="apr-delete-name">this program</strong>? This is a frontend demo only.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="theme-btn" id="apr-delete-confirm"><i class="far fa-trash"></i> Delete</button>
                </div>
            </div>
        </div>
    </div>

    <div class="gospel-alert gospel-alert-success sl-toast" id="apr-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="apr-toast-text">Action completed (demo).</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

