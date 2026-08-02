# Gospel Music Mastery

WordPress plugin for the Gospel Music Mastery learning platform — teachers, students, classes, bookings, payments, reviews, and admin portals.

**Version:** 1.0.0  
**Database schema version:** `GMM_DB_VERSION` `1.4.0`  
**Text domain:** `gospel-music-mastery`  
**Prefix:** `GMM` / `gmm_` / `gmm-`  
**License:** GPL-2.0-or-later

This plugin is designed to run with the **Eduka** theme. The approved Gospel Music Mastery design is **frozen** — the plugin does not modify theme files, theme CSS, or theme JavaScript.

---

## Requirements

| Requirement | Minimum |
|-------------|---------|
| WordPress | 6.0+ |
| PHP | 7.4+ |
| MySQL / MariaDB | Compatible with WordPress |
| Theme | Eduka (recommended) |

---

## Installation

1. Upload the `gospel-music-mastery` folder to `wp-content/plugins/`  
   (or install via **Plugins → Add New → Upload Plugin** using `gospel-music-mastery-v1.0.0.zip`).
2. Activate **Gospel Music Mastery** under **Plugins**.
3. On activation the plugin will:
   - Create custom database tables (`{prefix}gmm_*`)
   - Register roles `gmm_teacher` and `gmm_student`
   - Create portal pages with shortcodes
   - Initialize default settings
4. Open **Gospel Music Mastery** in the WordPress admin menu to configure settings.
5. Point design assets (images used in templates) by filtering `gmm_design_assets_base` to your theme/CDN copy of Eduka assets, if needed.

### Deactivation

Deactivating the plugin **does not** delete tables, roles, pages, or user data. Reactivation is safe.

---

## Features

- Teacher and student registration / login portals
- Teacher classes, availability, bookings, withdrawals
- Student lessons, bookings, favourites, payments
- Booking engine with ownership checks
- Payment records + Stripe / PayPal gateway stubs
- Reviews and ratings
- Search / filter (teachers, classes, programs)
- Media uploads via WordPress Media Library
- Notifications and email templates
- Analytics helpers for dashboards
- WP Admin management screens and settings API
- Namespaced CSS (`.gmm-*`) and JS (`window.GMM`) to reduce conflicts

---

## Plugin structure

```
gospel-music-mastery/
├── gospel-music-mastery.php   Main plugin file
├── includes/                  Core modules (DB, AJAX, booking, payment, …)
├── admin/                     WP Admin menu, pages, settings
├── public/                    Public hooks placeholder
├── student/                   Student portal modules
├── teacher/                   Teacher portal modules
├── templates/                 Frozen design PHP templates + emails
├── assets/                    Plugin CSS / JS (gmm- prefixed)
├── languages/                 Translations
└── README.md
```

---

## Shortcodes

### Student

| Shortcode | Purpose |
|-----------|---------|
| `[gmm_student_login]` | Student login |
| `[gmm_student_register]` | Student registration |
| `[gmm_student_dashboard]` | Student dashboard |
| `[gmm_student_profile]` | Student profile |
| `[gmm_student_lessons]` | Lessons |
| `[gmm_student_bookings]` | Bookings list |
| `[gmm_booking_form]` | Book a lesson |
| `[gmm_student_favourites]` | Favourite teachers |
| `[gmm_student_payments]` | Payments |
| `[gmm_student_settings]` | Settings |

### Teacher

| Shortcode | Purpose |
|-----------|---------|
| `[gmm_teacher_login]` | Teacher login |
| `[gmm_teacher_register]` | Teacher registration |
| `[gmm_teacher_dashboard]` | Teacher dashboard |
| `[gmm_teacher_profile]` | Teacher profile |
| `[gmm_teacher_classes]` | Classes |
| `[gmm_teacher_bookings]` | Bookings |
| `[gmm_teacher_availability]` | Availability |
| `[gmm_teacher_withdrawals]` | Withdrawals / earnings |
| `[gmm_teacher_settings]` | Settings |

### Admin (capability: `manage_options`)

| Shortcode | Purpose |
|-----------|---------|
| `[gmm_admin_dashboard]` | Admin dashboard |
| `[gmm_admin_teachers]` | Teachers |
| `[gmm_admin_students]` | Students |
| `[gmm_admin_classes]` | Classes |
| `[gmm_admin_bookings]` | Bookings |
| `[gmm_admin_payments]` | Payments |
| `[gmm_admin_programs]` | Programs |
| `[gmm_admin_blog]` | Blog |
| `[gmm_admin_settings]` | Settings |

Pages for these shortcodes are created automatically on activation (duplicates are skipped).

---

## Database

Tables use `{wpdb_prefix}gmm_`:

- `gmm_teachers`, `gmm_students`, `gmm_classes`
- `gmm_bookings`, `gmm_payments`, `gmm_reviews`
- `gmm_availability`, `gmm_programs`, `gmm_blog_posts`
- `gmm_favourites`, `gmm_notifications`

Schema version is stored in option `gmm_db_version` and compared to constant `GMM_DB_VERSION`.  
`GMM_Database::maybe_upgrade()` runs on load so future schema bumps apply without re-activation.

---

## Security notes

- All PHP files guard with `defined( 'ABSPATH' ) || exit`
- AJAX uses `check_ajax_referer( 'gmm_nonce' )`
- Admin actions require `current_user_can( 'manage_options' )`
- Teachers/students are restricted to their own data
- Inputs sanitized; outputs escaped in templates
- Uploads go through WordPress Media Library with mime/size checks
- Soft-delete for admin content removal (status `trash`)
- Deactivation does not wipe production data

---

## Theme & plugin compatibility

- Does **not** edit Eduka theme files
- Plugin CSS/JS are scoped under `.gmm-wrapper` / `window.GMM`
- Compatible in principle with common caching, security, SEO, and page-builder plugins when shortcodes/pages are excluded from aggressive HTML caches as needed
- After installing a page cache plugin, purge cache once and exclude logged-in portal pages if dashboards appear stale

---

## Updates

1. Backup the database and `wp-content/plugins/gospel-music-mastery`
2. Replace the plugin folder with the new version (or upload the new ZIP)
3. Visit any admin page once so `GMM_Database::maybe_upgrade()` can apply schema changes
4. Clear any full-page caches
5. Confirm portals and shortcodes still render

Bump `GMM_VERSION` for the plugin release and `GMM_DB_VERSION` only when the schema changes.

---

## Support

- Plugin URI: https://gospelmusicmastery.com/
- Author: Gospel Music Mastery
