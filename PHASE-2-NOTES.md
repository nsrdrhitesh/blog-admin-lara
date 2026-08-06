# Phase 2 — Auth & RBAC

## What's included
- **Login** (throttled — 5 attempts per email+IP, tracks `last_login_at`/`last_login_ip`,
  blocks deactivated accounts), **Forgot Password**, **Reset Password** — all using
  Laravel's built-in `Password` broker against the `password_reset_tokens` table
  from Phase 1.
- **Profile** page: update name/email/avatar, and a separate change-password form
  (each with its own named error bag so validation errors don't bleed between
  the two forms on the same page).
- **8 Policies** (Blog, Category, Tag, Author, Media, BlogComment, User, Setting),
  registered in `AppServiceProvider` and wired to the permission slugs seeded in
  Phase 1 (`$user->hasPermissionTo('blogs.edit')`, etc). `BlogPolicy` and
  `AuthorPolicy` add an extra rule: an **Author** role can only edit/delete their
  own posts, even though they hold the `blogs.edit`/`blogs.delete` permission —
  Editors/Admins/Super Admins aren't restricted this way.
  `UserPolicy::delete` blocks a user from deleting their own account, even as
  Super Admin.
- Admin layout shell (sidebar, topbar, dark mode toggle via Alpine, profile
  dropdown) that the Blog/Category/Media modules will hang their nav links off
  of in later phases.
- **Filled in `config/`, which was completely empty after Phase 1** — see below.

## Important fix: config/ directory
Phase 1 shipped models and migrations but never actually wrote `config/*.php`.
That's a real gap, not a style choice — without `config/auth.php` the password
broker has nothing to read, without `config/database.php` there's no MySQL
connection, without `config/filesystems.php` the `public` disk (where every
blog/media image path resolves against) doesn't exist. Added all 9 core files:
`app.php`, `auth.php`, `cache.php`, `database.php`, `filesystems.php`,
`session.php`, `queue.php`, `mail.php`, `logging.php`, `services.php` — standard
Laravel 12 defaults, reading from the `.env.example` keys already in place.

## Tailwind note
Auth and admin layouts currently load Tailwind via the Play CDN (`cdn.tailwindcss.com`)
so there's something to look at immediately without a Node build step. This is
fine for development but **not for production** — no purge, ships the whole
framework to the browser on every request. Before going live, compile a real
`public/build/app.css` with the Tailwind CLI (standalone binary, no Node/npm
needed) locally and swap the `<script>` tag for a `<link>` — this'll be handled
as part of the final "Admin UI polish" phase.

## What's NOT yet wired up
- No Blog/Category/Tag/Media/Comment controllers yet — the policies exist and
  are ready, but nothing calls them until those modules ship.
- Sidebar only has a Dashboard link; other nav items are added as each module
  lands.
- Email verification is not enforced (not in the original spec's Authentication
  list) — only email/password login, forgot/reset, and RBAC were requested.

## To pick up from here
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```
Log in at `/login` with `admin@example.com` / `ChangeMe123!`, then change the
password immediately from `/admin/profile`.
