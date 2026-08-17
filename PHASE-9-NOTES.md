# Phase 9 — Admin UI Polish

## What this phase actually was
Unlike most phases, this one was mostly **inventory and repair**, not
greenfield build. Checking the project state first (established habit
since Phase 6) turned up that almost the entire Phase 9 scope already
existed in complete, well-written form: the Tailwind CLI build setup
(`package.json`, `tailwind.config.js`, `resources/css/app.css`) with a CDN
fallback, a fully wired `DashboardService`/`DashboardController` with real
cached stats, a sticky collapsible sidebar, toast notifications, a
"Search Everywhere" global search bar with a grouped-results dropdown, a
"+ New" Quick Actions menu, and complete `BlogsExport`/`BlogsImport`
classes for CSV/Excel export and import.

What was genuinely missing were a few specific gaps — some of them real
bugs that would have broken things the moment a user hit them:

### 1. `ImportBlogsRequest` didn't exist — a real fatal error waiting to happen
`BlogController` already imported and type-hinted `App\Http\Requests\Admin\ImportBlogsRequest`
in its `import()` method, but the class itself was never created. The
`import` route wasn't registered either, so this was invisible until both
gaps were closed — but closing only one would have left the other as a
500. Created the request class (validates `file`: csv/xlsx/xls, max 5MB)
and both fixes together make the feature actually reachable.

### 2. Export/import routes never registered, and no UI to trigger them
`BlogController::export()`, `importTemplate()`, and `import()` were fully
implemented and correct — just never wired into `routes/admin.php`, and
the Blogs index page had no Export/Import buttons. Added:
- `GET blogs/export/{format}` (csv/xlsx, respects the current list filters)
- `GET blogs/import-template` (blank CSV with the exact expected headers)
- `POST blogs/import`
- An Export dropdown and an Import modal on the Blogs index page, with a
  dedicated success banner showing the imported count, plus row-level
  failure detail surfaced through the existing form-error channel.

### 3. `x-cloak` was used everywhere but silently did nothing
Alpine's `x-cloak` attribute (used on the search dropdown, Quick Actions
menu, and every modal built since Phase 3–4) only works if a
`[x-cloak] { display: none !important; }` CSS rule exists somewhere —
Alpine sets the attribute, but hiding it is CSS's job, not Alpine's. That
rule was never defined, in the compiled `app.css` **or** anywhere else, so
every cloaked element has been flashing visible for a frame before Alpine
initializes since it was first used. Fixed in both places it needs to
exist: `resources/css/app.css` (compiled path) and an inline `<style>` in
the layout's `<head>` (CDN-fallback path, for when `app.css` hasn't been
built yet).

### 4. The `shimmer` CSS class was defined but never used anywhere
A complete shimmer-loading CSS effect existed in `app.css` but grepping
the entire `resources/views` tree found zero uses of it. Wired it into the
Media Library grid: each thumbnail now shows the shimmer placeholder via a
small per-image Alpine `loaded` flag until the `<img>`'s `load` event
fires, using native `loading="lazy"` alongside it.

### 5. Sidebar defaulted open on mobile
`sidebarOpen` was hardcoded `true` regardless of viewport — meaning a
phone visitor got a full-width sidebar covering the screen on first load,
directly working against the spec's "Mobile Friendly" line. Changed the
initial state to check `window.innerWidth >= 1024` (Tailwind's `lg`
breakpoint) and auto-collapse on resize below it.

## What's genuinely NOT done, stated plainly
- **The global search bar is fully hidden below the `lg` breakpoint** —
  mobile users currently have no "Search Everywhere" access at all, not
  even a collapsed icon-triggered version. Building a proper mobile search
  overlay is real, scoped work — flagged rather than silently shipped as
  "mobile friendly."
- **No actual compiled `public/build/app.css` ships in this ZIP.** This
  sandbox has no internet access, so it can't download the Tailwind
  standalone CLI binary (the whole point of using it is that it needs
  *no* Node — a single executable) to run the build. The layout's
  conditional fallback (Play CDN until that file exists) means the panel
  still renders correctly out of the box; running `npm install && npm run
  build:css` once locally (or using the standalone `tailwindcss` binary
  directly, no Node required either way) produces the real production
  file. This was true since Phase 2/5 first introduced the CDN link and
  remains the one piece of this project that requires a manual local step
  no amount of code generation here can substitute for.
- No CSV/Excel export for Categories/Tags/Authors/Comments — only Blogs,
  matching what was already built rather than expanding scope further.
- No drag-and-drop reordering anywhere (Categories, Menu items, Blog
  gallery images all still use a numeric sort-order field) — consistent
  with the trade-off already established and documented in Phases 3 and 7.

## To pick up from here
```bash
composer install
npm install && npm run build:css   # produces public/build/app.css — optional,
                                    # panel works via CDN fallback without it
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan serve
```
