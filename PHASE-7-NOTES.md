# Phase 7 — Comments, Settings, Menus, Pages, Users

## A note on how this phase started
Before writing anything, I inventoried the project rather than assuming a
blank slate — Phase 6 had already turned up unexpected prior work, so I
checked first this time. That surfaced a real, pre-existing gap worth
fixing as part of this phase: `UserController` (User CRUD, RBAC role
assignment) already existed with working `store`/`update`/`destroy` logic,
but **its views (`admin/users/index|create|edit.blade.php`) didn't exist,
and it wasn't wired into `routes/admin.php` at all** — visiting it would
have 500'd immediately. Built the views and wired the routes as part of
this phase rather than leaving it broken.

## What's included
- **Comments moderation** (`/admin/comments`): approve/reject/mark-spam/
  delete/reply, with nested replies shown **at every status** (not just
  approved) so a moderator can actually see and act on a pending or spam
  reply — the public-facing `replies()` relation on `BlogComment` filters
  to approved-only by design, so a new `allReplies()` relation was added
  specifically for this admin view rather than silently reusing the
  filtered one and hiding replies that need moderation.
- **Settings** (`/admin/settings`): a tabbed screen (General, SMTP,
  Analytics, Social, Ads, Contact, Footer, Header, SEO) over the flat
  `settings` key-value table from Phase 1, built on `SettingService` from
  Phase 6. Every field is **allowlisted** in `SettingController::FIELDS` —
  a request can't write an arbitrary settings key just by including it in
  the POST body.
- **Menus** (`/admin/menus`): create/delete menus, add/delete items with
  one level of nesting (parent/child) and a numeric sort order — no
  drag-and-drop, consistent with how Categories handle ordering since
  Phase 3 (same trade-off, not a new shortcut).
- **Pages** (`/admin/pages`): full CRUD. `Page` already had `HasSeo`/
  `HasGeoMeta`/`HasFaqs`/`HasSchemas` since Phase 1, but the SEO/GEO/FAQ
  Blade panels built in Phase 6 are blog-specific (routes and forms both
  assume a `Blog` route parameter) — wiring them onto Pages too is real,
  known remaining work, not silently dropped (see below).
- **Users wired up** (`/admin/users`) — see note above.
- Added a `pages` and `menus` permission group to the RBAC seeder
  (`view/create/edit/delete` and `view/edit` respectively) — **existing
  installs need to re-run `php artisan db:seed --class=RolePermissionSeeder`**
  to pick these up; it's safe to re-run (every permission/role is
  `firstOrCreate`, nothing gets duplicated or reset).
- Sidebar nav now includes all of the above; Users/Settings links are
  hidden from anyone without `users.view`/`settings.view` (Author and
  Editor roles won't see them cluttering the sidebar).

## Design decisions worth knowing about
- **Comments, Settings, Menus, Pages, Users all skip the Repository
  interface pattern.** This continues the precedent `RedirectService` set
  in Phase 6: Comments/Settings/Menus operate on simple, mostly-flat data
  with no complex querying that would benefit from an interface layer, and
  Pages/Users are simple enough CRUD that a repository would just be a
  pass-through. Blog/Category/Tag/Author/Media — the modules with actual
  query complexity (filters, eager loading, bulk ops) — keep the pattern.
- **SMTP settings are stored but not yet applied.** The Settings screen
  saves SMTP host/port/credentials into the `settings` table, but outgoing
  mail still reads from `.env`'s `MAIL_*` values (`config/mail.php`,
  Phase 2). Making the DB values actually override the mail config means a
  boot-time config loader that runs early enough to affect the mail driver
  but late enough that the database connection exists — a real piece of
  work, not a one-line fix. The Settings UI says as much directly on the
  SMTP tab rather than implying it already works.
- **Pages don't have the SEO/GEO/FAQ panel UI yet**, despite the backend
  fully supporting it. Generalizing `SeoController`/`GeoMetaController`/
  `FaqController` (and the three Blade partials) from "takes a `Blog`
  route parameter" to "takes any seoable/geoable/faqable model" is a
  contained, well-scoped follow-up — flagged rather than left as a silent
  gap.

## What's NOT yet wired up
- Public-facing comment submission form (no public blog pages exist yet —
  consistent with every prior phase's notes on this).
- SMTP settings don't actually change how mail sends yet (see above).
- SEO/GEO/FAQ panels on Pages (see above).
- Menu items aren't rendered anywhere publicly yet (no public frontend).

## To pick up from here
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```
If you're updating an existing install rather than starting fresh, also run:
```bash
php artisan db:seed --class=RolePermissionSeeder
```
to pick up the new `pages`/`menus` permissions.
