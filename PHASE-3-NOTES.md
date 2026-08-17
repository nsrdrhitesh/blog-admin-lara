# Phase 3 — Blog Core (Blogs, Categories, Tags, Authors)

## What's included
- **Repository + Service pattern**, as the spec asked for: `Repositories/Interfaces`
  define the contract, `Repositories/Eloquent` implement it, `RepositoryServiceProvider`
  binds interface → implementation (so a controller/service only ever type-hints
  `BlogRepositoryInterface`, never the Eloquent class directly).
- **Services** (`BlogService`, `CategoryService`, `TagService`, `AuthorService`) hold
  the business logic — transactional create/update, tag syncing, bulk actions,
  cleaning up old files on replace/delete — so controllers stay thin.
- **`BlogData` DTO**: form input → typed object → `BlogService`. Keeps the service's
  method signature stable as SEO/GEO/image fields get added to the form in later
  phases, instead of passing raw arrays around.
- **Full CRUD** for Blogs, Categories (nested, with parent/child guard against
  self-referencing), Tags (inline-editable table), Authors — each behind its
  Phase 2 policy (`$this->authorize(...)` in every controller action).
- **Bulk actions on the Blogs list**: publish / move to draft / archive / delete,
  each still authorized per-row server-side (so an Author can't bulk-publish
  someone else's post just by selecting checkboxes) — filters, search, and
  pagination on the same screen.
- **Free-typed tags**: the blog form has both a multi-select of existing tags and
  a comma-separated "new tags" field; `TagRepository::findOrCreateByNames()`
  creates any that don't exist yet and both feed into the same `tags()->sync()`.
- Sidebar nav now links to Blogs/Categories/Tags/Authors with an active-state
  highlight.

## Design decisions worth knowing about
- **Authors can only edit/delete their own posts** — enforced in `BlogPolicy`
  (added in Phase 2), not re-checked here; the controller just calls
  `$this->authorize()` and trusts the policy.
- Featured image upload is wired (stored under `blogs/YYYY/MM/`, old file deleted
  on replace) but **inline images, gallery, alt/caption/credit metadata, and
  WebP/AVIF generation are intentionally not here** — that's the whole Phase 4
  scope (Media & Images) and doing it half-way here would mean redoing it.
- The content field is a plain `<textarea>` for now. Wiring TinyMCE/CKEditor
  in without a Node build step is Phase 5 (Content Editor) — swapping it in
  later won't touch the DB or validation, since `content` is already a single
  `longText` field either way.
- Category/Tag/Author `slug` fields are optional in the forms — leave blank
  and `HasSlug` (from Phase 1) generates one from the name automatically, and
  records old→new mappings in `slug_histories` if you rename later.

## What's NOT yet wired up
- No image gallery, inline image manager, or Media Library UI (Phase 4).
- No rich text editor (Phase 5).
- No SEO/GEO panel on the blog form yet, even though the `seos`/`geo_metas`
  tables and `HasSeo`/`HasGeoMeta` traits already exist on `Blog` from Phase 1
  (Phase 6).
- No public-facing blog routes or REST API yet — this is 100% admin-side.

## To pick up from here
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```
Log in, then Blogs / Categories / Tags / Authors are all live in the sidebar.
