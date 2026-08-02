<?php
/**
 * Template: admin-blog
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

        <!-- admin blog -->
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
                                <li><a href="<?php echo esc_url( gmm_get_page_link( 'admin_programs' ) ); ?>" class="sd-nav-link" data-nav="programs"><i class="far fa-music"></i> Music Programs</a></li>
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
                                    <h3>Manage Blogs</h3>
                                    <p>Create educational articles, manage blog posts, and publish music learning content.</p>
                                </div>
                                <button type="button" class="theme-btn" id="abl-add-btn">
                                    <i class="far fa-plus"></i> Create New Blog
                                </button>
                            </div>
                        </section>

                        <!-- stats -->
                        <section class="sd-stats-grid ad-stats-grid at-stats-grid">
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-newspaper"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="120">0</span>
                                    <span class="sd-stat-title">Total Posts</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-circle-check"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="90">0</span>
                                    <span class="sd-stat-title">Published</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card">
                                <div class="sd-stat-icon"><i class="far fa-file-pen"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="20">0</span>
                                    <span class="sd-stat-title">Drafts</span>
                                </div>
                            </div>
                            <div class="sd-stat-card ad-stat-card is-pending">
                                <div class="sd-stat-icon"><i class="far fa-clock"></i></div>
                                <div class="sd-stat-body">
                                    <span class="sd-stat-value ad-counter" data-count="10">0</span>
                                    <span class="sd-stat-title">Pending Review</span>
                                </div>
                            </div>
                        </section>

                        <!-- analytics charts -->
                        <div class="gmm-chart-grid">
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Views Growth</h3>
                                        <p>Monthly article views across the blog.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap">
                                    <canvas id="gmm-abl-views" aria-label="Blog views growth line chart"></canvas>
                                </div>
                            </section>
                            <section class="sd-card ad-chart-card gmm-chart-card">
                                <div class="sd-card-head">
                                    <div>
                                        <h3>Category Distribution</h3>
                                        <p>Blog posts by content category.</p>
                                    </div>
                                </div>
                                <div class="gmm-chart-wrap is-doughnut">
                                    <canvas id="gmm-abl-category" aria-label="Blog category doughnut chart"></canvas>
                                </div>
                            </section>
                        </div>

                        <!-- blog table -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>All Blog Posts</h3>
                                    <p>Search, filter, and manage website articles.</p>
                                </div>
                                <span class="sf-count-pill" id="abl-result-count"><i class="far fa-newspaper"></i> <strong>8</strong> Shown</span>
                            </div>

                            <form class="at-filter-bar" id="abl-filter-form" action="#" method="get">
                                <div class="at-search-field">
                                    <i class="far fa-search" aria-hidden="true"></i>
                                    <input type="search" class="form-control" id="abl-search"
                                        placeholder="Search blog posts..." autocomplete="off">
                                </div>
                                <div class="at-filter-selects">
                                    <div class="form-group mb-0">
                                        <label for="abl-category" class="visually-hidden">Category</label>
                                        <select class="form-control form-select" id="abl-category">
                                            <option value="all">All Categories</option>
                                            <option value="education">Music Education</option>
                                            <option value="piano">Piano</option>
                                            <option value="vocals">Vocals</option>
                                            <option value="worship">Worship</option>
                                            <option value="tips">Teacher Tips</option>
                                        </select>
                                    </div>
                                    <div class="form-group mb-0">
                                        <label for="abl-status" class="visually-hidden">Status</label>
                                        <select class="form-control form-select" id="abl-status">
                                            <option value="all">All Status</option>
                                            <option value="published">Published</option>
                                            <option value="draft">Draft</option>
                                            <option value="pending">Pending</option>
                                        </select>
                                    </div>
                                    <button type="submit" class="theme-btn"><i class="far fa-filter"></i> Filter</button>
                                </div>
                            </form>

                            <div class="table-responsive td-table-wrap" id="abl-table-wrap">
                                <table class="table td-table sb-table at-table">
                                    <thead>
                                        <tr>
                                            <th>Featured Image</th>
                                            <th>Title</th>
                                            <th>Author</th>
                                            <th>Category</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="abl-table-body">

                                        <tr class="abl-row"
                                            data-id="BLG-01"
                                            data-title="How To Improve Gospel Piano Skills"
                                            data-author="Admin"
                                            data-category="piano"
                                            data-category-label="Piano"
                                            data-status="published"
                                            data-date="March 20, 2026"
                                            data-short="Practical tips to strengthen gospel piano technique and worship flow."
                                            data-content="Gospel piano improves through consistent practice of chord inversions, left-hand patterns, and worship-song transitions. Start each session with scales, then practice common progressions in several keys. Focus on dynamics and listening carefully to the vocalist so your accompaniment supports the message."
                                            data-image="assets/img/blog/01.jpg"
                                            data-featured="true">
                                            <td data-label="Featured Image">
                                                <img class="abl-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/blog/01.jpg' ) ); ?>" alt="How To Improve Gospel Piano Skills">
                                            </td>
                                            <td data-label="Title"><strong class="abl-title-text">How To Improve Gospel Piano Skills</strong></td>
                                            <td data-label="Author">Admin</td>
                                            <td data-label="Category"><span class="abl-cat-label">Piano</span></td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed abl-status">Published</span></td>
                                            <td data-label="Date">March 20, 2026</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-view-btn"><i class="far fa-eye"></i> <span>View Blog</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-publish-btn"><i class="far fa-upload"></i> <span>Publish</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-draft-btn"><i class="far fa-file"></i> <span>Move To Draft</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-delete-btn"><i class="far fa-trash"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="abl-row"
                                            data-id="BLG-02"
                                            data-title="Best Worship Vocal Techniques"
                                            data-author="Admin"
                                            data-category="vocals"
                                            data-category-label="Vocals"
                                            data-status="draft"
                                            data-date="March 25, 2026"
                                            data-short="Essential vocal techniques for clearer worship leading and healthier singing."
                                            data-content="Strong worship vocals come from breath support, relaxed jaw posture, and intentional phrasing. Warm up gently, avoid straining for high notes, and use microphone technique to preserve tone. Practice harmony stacking with your team so blends stay consistent during live services."
                                            data-image="assets/img/blog/02.jpg"
                                            data-featured="true">
                                            <td data-label="Featured Image">
                                                <img class="abl-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/blog/02.jpg' ) ); ?>" alt="Best Worship Vocal Techniques">
                                            </td>
                                            <td data-label="Title"><strong class="abl-title-text">Best Worship Vocal Techniques</strong></td>
                                            <td data-label="Author">Admin</td>
                                            <td data-label="Category"><span class="abl-cat-label">Vocals</span></td>
                                            <td data-label="Status"><span class="sb-badge is-inactive abl-status">Draft</span></td>
                                            <td data-label="Date">March 25, 2026</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-view-btn"><i class="far fa-eye"></i> <span>View Blog</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-publish-btn"><i class="far fa-upload"></i> <span>Publish</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-draft-btn"><i class="far fa-file"></i> <span>Move To Draft</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-delete-btn"><i class="far fa-trash"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="abl-row"
                                            data-id="BLG-03"
                                            data-title="Building a Strong Worship Team Culture"
                                            data-author="Admin"
                                            data-category="worship"
                                            data-category-label="Worship"
                                            data-status="published"
                                            data-date="March 18, 2026"
                                            data-short="How healthy rehearsal habits shape better Sunday worship."
                                            data-content="A strong worship team culture prioritizes preparation, humility, and musical excellence. Clear setlists, punctual rehearsals, and honest feedback help musicians grow together. Celebrate progress, pray as a team, and keep the focus on serving the congregation."
                                            data-image="assets/img/blog/03.jpg"
                                            data-featured="true">
                                            <td data-label="Featured Image">
                                                <img class="abl-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/blog/03.jpg' ) ); ?>" alt="Building a Strong Worship Team Culture">
                                            </td>
                                            <td data-label="Title"><strong class="abl-title-text">Building a Strong Worship Team Culture</strong></td>
                                            <td data-label="Author">Admin</td>
                                            <td data-label="Category"><span class="abl-cat-label">Worship</span></td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed abl-status">Published</span></td>
                                            <td data-label="Date">March 18, 2026</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-view-btn"><i class="far fa-eye"></i> <span>View Blog</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-publish-btn"><i class="far fa-upload"></i> <span>Publish</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-draft-btn"><i class="far fa-file"></i> <span>Move To Draft</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-delete-btn"><i class="far fa-trash"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="abl-row"
                                            data-id="BLG-04"
                                            data-title="5 Ways Music Education Builds Confidence"
                                            data-author="Emily Davis"
                                            data-category="education"
                                            data-category-label="Music Education"
                                            data-status="published"
                                            data-date="March 15, 2026"
                                            data-short="Why structured music learning helps students grow beyond the instrument."
                                            data-content="Music education builds confidence by giving students measurable progress, creative expression, and performance opportunities. Regular lessons create discipline, while group classes develop collaboration. Celebrate small wins so learners stay motivated through challenging passages."
                                            data-image="assets/img/blog/bs-1.jpg"
                                            data-featured="false">
                                            <td data-label="Featured Image">
                                                <img class="abl-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/blog/bs-1.jpg' ) ); ?>" alt="5 Ways Music Education Builds Confidence">
                                            </td>
                                            <td data-label="Title"><strong class="abl-title-text">5 Ways Music Education Builds Confidence</strong></td>
                                            <td data-label="Author">Emily Davis</td>
                                            <td data-label="Category"><span class="abl-cat-label">Music Education</span></td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed abl-status">Published</span></td>
                                            <td data-label="Date">March 15, 2026</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-view-btn"><i class="far fa-eye"></i> <span>View Blog</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-publish-btn"><i class="far fa-upload"></i> <span>Publish</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-draft-btn"><i class="far fa-file"></i> <span>Move To Draft</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-delete-btn"><i class="far fa-trash"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="abl-row"
                                            data-id="BLG-05"
                                            data-title="Teacher Tips for Online Music Lessons"
                                            data-author="John Smith"
                                            data-category="tips"
                                            data-category-label="Teacher Tips"
                                            data-status="pending"
                                            data-date="March 22, 2026"
                                            data-short="Practical guidance for clearer, more engaging virtual lesson sessions."
                                            data-content="Online lessons succeed with stable audio, clear camera framing, and structured agendas. Share materials before class, keep instructions short, and leave space for student questions. Record short demos when helpful so students can review between sessions."
                                            data-image="assets/img/blog/bs-2.jpg"
                                            data-featured="false">
                                            <td data-label="Featured Image">
                                                <img class="abl-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/blog/bs-2.jpg' ) ); ?>" alt="Teacher Tips for Online Music Lessons">
                                            </td>
                                            <td data-label="Title"><strong class="abl-title-text">Teacher Tips for Online Music Lessons</strong></td>
                                            <td data-label="Author">John Smith</td>
                                            <td data-label="Category"><span class="abl-cat-label">Teacher Tips</span></td>
                                            <td data-label="Status"><span class="sb-badge is-pending abl-status">Pending</span></td>
                                            <td data-label="Date">March 22, 2026</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-view-btn"><i class="far fa-eye"></i> <span>View Blog</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-publish-btn"><i class="far fa-upload"></i> <span>Publish</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-draft-btn"><i class="far fa-file"></i> <span>Move To Draft</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-delete-btn"><i class="far fa-trash"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="abl-row"
                                            data-id="BLG-06"
                                            data-title="Choosing the Right Piano Practice Routine"
                                            data-author="Admin"
                                            data-category="piano"
                                            data-category-label="Piano"
                                            data-status="draft"
                                            data-date="March 12, 2026"
                                            data-short="A simple weekly practice plan for busy gospel musicians."
                                            data-content="A balanced piano routine includes warm-ups, repertoire, ear training, and worship song application. Keep sessions focused and track what improved. Short daily practice often beats long inconsistent sessions."
                                            data-image="assets/img/blog/bs-3.jpg"
                                            data-featured="false">
                                            <td data-label="Featured Image">
                                                <img class="abl-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/blog/bs-3.jpg' ) ); ?>" alt="Choosing the Right Piano Practice Routine">
                                            </td>
                                            <td data-label="Title"><strong class="abl-title-text">Choosing the Right Piano Practice Routine</strong></td>
                                            <td data-label="Author">Admin</td>
                                            <td data-label="Category"><span class="abl-cat-label">Piano</span></td>
                                            <td data-label="Status"><span class="sb-badge is-inactive abl-status">Draft</span></td>
                                            <td data-label="Date">March 12, 2026</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-view-btn"><i class="far fa-eye"></i> <span>View Blog</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-publish-btn"><i class="far fa-upload"></i> <span>Publish</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-draft-btn"><i class="far fa-file"></i> <span>Move To Draft</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-delete-btn"><i class="far fa-trash"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="abl-row"
                                            data-id="BLG-07"
                                            data-title="How Harmony Shapes Gospel Choir Sound"
                                            data-author="Olivia Martin"
                                            data-category="vocals"
                                            data-category-label="Vocals"
                                            data-status="published"
                                            data-date="March 10, 2026"
                                            data-short="Understanding section blend and chord voicing in choir arrangements."
                                            data-content="Gospel choir sound thrives on balanced sections, clear vowels, and intentional chord voicing. Directors should train ears for tuning and encourage singers to listen across parts. Small adjustments in dynamics can transform a good arrangement into a powerful moment."
                                            data-image="assets/img/blog/01.jpg"
                                            data-featured="false">
                                            <td data-label="Featured Image">
                                                <img class="abl-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/blog/01.jpg' ) ); ?>" alt="How Harmony Shapes Gospel Choir Sound">
                                            </td>
                                            <td data-label="Title"><strong class="abl-title-text">How Harmony Shapes Gospel Choir Sound</strong></td>
                                            <td data-label="Author">Olivia Martin</td>
                                            <td data-label="Category"><span class="abl-cat-label">Vocals</span></td>
                                            <td data-label="Status"><span class="sb-badge is-confirmed abl-status">Published</span></td>
                                            <td data-label="Date">March 10, 2026</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-view-btn"><i class="far fa-eye"></i> <span>View Blog</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-publish-btn"><i class="far fa-upload"></i> <span>Publish</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-draft-btn"><i class="far fa-file"></i> <span>Move To Draft</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-delete-btn"><i class="far fa-trash"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                        <tr class="abl-row"
                                            data-id="BLG-08"
                                            data-title="Planning Seasonal Worship Setlists"
                                            data-author="Admin"
                                            data-category="worship"
                                            data-category-label="Worship"
                                            data-status="pending"
                                            data-date="March 8, 2026"
                                            data-short="A simple framework for Advent, Easter, and regular Sunday planning."
                                            data-content="Seasonal setlists should connect Scripture themes with singable keys and thoughtful song order. Map energy across the service, protect transitions, and leave room for congregational response. Review what worked after each season and refine next year."
                                            data-image="assets/img/blog/02.jpg"
                                            data-featured="false">
                                            <td data-label="Featured Image">
                                                <img class="abl-thumb" src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/blog/02.jpg' ) ); ?>" alt="Planning Seasonal Worship Setlists">
                                            </td>
                                            <td data-label="Title"><strong class="abl-title-text">Planning Seasonal Worship Setlists</strong></td>
                                            <td data-label="Author">Admin</td>
                                            <td data-label="Category"><span class="abl-cat-label">Worship</span></td>
                                            <td data-label="Status"><span class="sb-badge is-pending abl-status">Pending</span></td>
                                            <td data-label="Date">March 8, 2026</td>
                                            <td data-label="Action">
                                                <div class="dropdown at-action-dropdown">
                                                    <button class="at-action-btn" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Actions">
                                                        <i class="far fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end ad-dropdown at-action-menu">
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-view-btn"><i class="far fa-eye"></i> <span>View Blog</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-edit-btn"><i class="far fa-pen"></i> <span>Edit</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-publish-btn"><i class="far fa-upload"></i> <span>Publish</span></button></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-draft-btn"><i class="far fa-file"></i> <span>Move To Draft</span></button></li>
                                                        <li><hr class="dropdown-divider"></li>
                                                        <li><button type="button" class="dropdown-item ad-dropdown-item abl-delete-btn"><i class="far fa-trash"></i> <span>Delete</span></button></li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>

                                    </tbody>
                                </table>
                            </div>

                            <div class="sl-empty" id="abl-empty" hidden>
                                <i class="far fa-newspaper"></i>
                                <h3>No blog posts available.</h3>
                                <p>Try adjusting your search or filter options, or create a new blog post.</p>
                            </div>

                            <nav class="at-pagination" id="abl-pagination" aria-label="Blog pagination">
                                <ul class="pagination justify-content-center mb-0">
                                    <li class="page-item disabled" id="abl-page-prev">
                                        <a class="page-link" href="#" data-page="prev" aria-label="Previous"><i class="far fa-angle-left"></i> Previous</a>
                                    </li>
                                    <li class="page-item active"><a class="page-link" href="#" data-page="1">1</a></li>
                                    <li class="page-item"><a class="page-link" href="#" data-page="2">2</a></li>
                                    <li class="page-item"><a class="page-link" href="#" data-page="3">3</a></li>
                                    <li class="page-item" id="abl-page-next">
                                        <a class="page-link" href="#" data-page="next" aria-label="Next">Next <i class="far fa-angle-right"></i></a>
                                    </li>
                                </ul>
                            </nav>
                        </section>

                        <!-- featured articles -->
                        <section class="sd-card">
                            <div class="sd-card-head">
                                <div>
                                    <h3>Featured Articles</h3>
                                    <p>Highlighted blog posts shown on the public site.</p>
                                </div>
                            </div>
                            <div class="abl-featured-grid" id="abl-featured-grid">
                                <!-- filled by JS -->
                            </div>
                            <div class="sl-empty" id="abl-featured-empty" hidden>
                                <i class="far fa-star"></i>
                                <h3>No featured articles.</h3>
                                <p>Mark published posts as featured from the blog list.</p>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
        <!-- admin blog end -->

    

<!-- create / edit modal -->
    <div class="modal fade gospel-demo-modal" id="abl-form-modal" tabindex="-1" aria-labelledby="abl-form-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="abl-form-title">Create New Blog</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="abl-blog-form" action="#" method="post">
                    <div class="modal-body at-modal-body">
                        <input type="hidden" id="abl-form-id" value="">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="abl-form-image">Featured Image Upload</label>
                                    <input type="file" class="form-control" id="abl-form-image" accept="image/*">
                                    <small class="form-text text-muted">Frontend demo only — image is not uploaded.</small>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="abl-form-title-input">Blog Title</label>
                                    <input type="text" class="form-control" id="abl-form-title-input" placeholder="Enter blog title" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="abl-form-category">Category</label>
                                    <select class="form-control form-select" id="abl-form-category" required>
                                        <option value="education">Music Education</option>
                                        <option value="piano">Piano</option>
                                        <option value="vocals">Vocals</option>
                                        <option value="worship">Worship</option>
                                        <option value="tips">Teacher Tips</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="abl-form-author">Author Name</label>
                                    <input type="text" class="form-control" id="abl-form-author" placeholder="Admin" required>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="abl-form-short">Short Description</label>
                                    <textarea class="form-control" id="abl-form-short" rows="2"
                                        placeholder="Brief summary for listings..." required></textarea>
                                </div>
                            </div>
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label for="abl-form-content">Full Content Editor Area</label>
                                    <textarea class="form-control abl-editor" id="abl-form-content" rows="8"
                                        placeholder="Write the full article content here..." required></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-0">
                                    <label for="abl-form-status">Status</label>
                                    <select class="form-control form-select" id="abl-form-status" required>
                                        <option value="draft">Draft</option>
                                        <option value="published">Published</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="theme-btn"><i class="far fa-floppy-disk"></i> Save Blog</button>
                    </div>
                </form>
            </div>
        </div>
    </div>


    <!-- preview modal -->
    <div class="modal fade gospel-demo-modal" id="abl-preview-modal" tabindex="-1" aria-labelledby="abl-preview-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="abl-preview-title">Blog Preview</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body at-modal-body">
                    <div class="abl-preview-hero">
                        <img src="<?php echo esc_url( gmm_design_asset_url( 'assets/img/blog/01.jpg' ) ); ?>" alt="Blog" id="abl-preview-image">
                    </div>
                    <div class="abl-preview-meta">
                        <span class="apr-category" id="abl-preview-category">Piano</span>
                        <span class="sb-badge is-confirmed" id="abl-preview-status">Published</span>
                    </div>
                    <h4 id="abl-preview-heading">How To Improve Gospel Piano Skills</h4>
                    <ul class="abl-preview-info">
                        <li><i class="far fa-user"></i> <span id="abl-preview-author">Admin</span></li>
                        <li><i class="far fa-calendar"></i> <span id="abl-preview-date">March 20, 2026</span></li>
                    </ul>
                    <p class="abl-preview-short" id="abl-preview-short">Short description</p>
                    <div class="abl-preview-content" id="abl-preview-content">
                        Content preview
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="theme-btn" id="abl-open-full-preview">
                        <i class="far fa-arrow-up-right-from-square"></i> Open Full Preview
                    </button>
                </div>
            </div>
        </div>
    </div>


    <!-- delete confirm -->
    <div class="modal fade gospel-demo-modal" id="abl-delete-modal" tabindex="-1" aria-labelledby="abl-delete-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="abl-delete-title">Delete Blog Post</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">Are you sure you want to delete <strong id="abl-delete-name">this blog post</strong>? This is a frontend demo only.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="theme-btn theme-btn-outline" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="theme-btn" id="abl-delete-confirm"><i class="far fa-trash"></i> Delete</button>
                </div>
            </div>
        </div>
    </div>

    <div class="gospel-alert gospel-alert-success sl-toast" id="abl-toast" hidden>
        <i class="far fa-circle-check"></i>
        <span id="abl-toast-text">Action completed (demo).</span>
    </div>


    <!-- js -->
</div><!-- .gmm-wrapper -->

