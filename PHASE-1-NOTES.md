# Phase 1 — Foundation

## What's included
- Full folder structure: Controllers/Repositories/Services/Models/Policies/Requests/
  Observers/Resources/Traits/Helpers/Enums/DTOs/Events/Listeners/Jobs/Console/Middlewares
- **27 migrations** covering every table from the spec: RBAC (roles, permissions,
  permission_role), users/auth, authors, categories (nested), tags, pages, the full
  blogs table, blog_images (with thumbnail/medium/large/webp/avif paths + all metadata
  fields), blog_gallery, blog_tag pivot, blog_views, blog_comments (nested), a
  **polymorphic** seos/schemas/faqs/geo_metas set (reusable across Blog, Page, Category),
  media + media_folders, menus + menu_items, settings, activity_logs, redirects,
  slug_histories, notifications, and queue/cache tables.
- **24 Eloquent models**, fully typed relationships, casts (including PHP enums),
  and reusable traits: `HasSlug` (auto-slug + old-slug redirect history),
  `HasSeo`, `HasGeoMeta`, `HasFaqs`, `HasSchemas`, `LogsActivity`.
- 5 Enums: `BlogStatus`, `CommentStatus`, `ImageType`, `RoleSlug`, `SchemaType`.
- Global helpers: `reading_time()`, `format_bytes()`, `excerpt_from_html()`,
  `setting()`, `media_url()`.
- RBAC seeder: 4 roles (Super Admin/Admin/Editor/Author) with scoped permissions,
  and a default super-admin user (`admin@example.com` / `ChangeMe123!` — **change
  this immediately after first login**).
- Minimal bootable skeleton: `artisan`, `bootstrap/app.php`, `public/index.php`,
  `.env.example`, `composer.json` (Laravel 12, PHP 8.2 — matches XAMPP 8.2.4 —,
  Intervention Image, Spatie Image Optimizer, Maatwebsite Excel — no Node
  required), route files, and role/permission middleware.

## Design decisions worth knowing about
- **SEO, GEO metadata, JSON-LD schemas, and FAQs are polymorphic** (`seos`,
  `geo_metas`, `schemas`, `faqs` tables with `*able_type`/`*able_id`), not columns
  bolted onto `blogs`. This lets Pages and Categories reuse the exact same SEO/FAQ
  UI and logic later without duplicating tables — one `SeoService` serves everyone.
- **Images never store binary/base64.** `blog_images` and `media` store only
  relative paths (e.g. `blogs/2026/08/uuid.webp`) plus a `hash` column for
  duplicate detection, per the spec.
- `role_id` is a single FK on `users` (not a pivot) since the spec describes
  one role per admin user; `permission_role` is the many-to-many that makes
  each role's permission set configurable without code changes.

## What's NOT yet wired up (coming in later phases)
- No controllers/requests/policies/resources yet — models and DB only.
- `routes/admin.php` and `routes/api.php` are placeholders (a dashboard stub
  and a health check) so the app boots; real routes land with their controllers.
- No Tailwind/Blade admin UI yet.
- `vendor/` is intentionally not included — this sandbox has no internet access,
  so run `composer install` yourself after unzipping (standard for any Laravel
  project pulled from source control).

## To run this phase locally
```bash
composer install
cp .env.example .env
php artisan key:generate
# set DB_* in .env to your MySQL/phpMyAdmin credentials
php artisan migrate --seed
```
