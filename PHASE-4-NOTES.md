# Phase 4 — Media & Images

## What's included
- **`ImageProcessingService`** (Intervention Image v3, GD driver — no Imagick
  needed, matches a typical XAMPP/shared-hosting PHP build): generates
  thumbnail/medium/large resizes plus WebP and AVIF re-encodes, and runs every
  variant through Spatie's image optimizer chain.
- **Split into a fast + a queued half, deliberately**: `storeOriginal()` runs
  synchronously in the request (just saves the file + reads dimensions, so
  the duplicate-hash check and an immediate preview both work), while
  `generateVariants()` — the expensive part (5 resizes/re-encodes per image)
  — runs inside `ProcessMediaImage`/`ProcessBlogImage`, both `ShouldQueue` jobs.
  This is what the spec's "Queue Image Processing" line actually means in
  practice; doing all of it inline would make every upload block for seconds.
- **Duplicate detection**: every upload is hashed (`sha256`) before storing;
  if an identical file already exists in the Media Library, the existing
  row's `reuse_count` is incremented and nothing is re-uploaded.
- **Media Library**: folders (nestable), drag-and-drop multi-upload, search,
  grid view with hover actions, an edit-metadata modal (alt/caption/credit/
  source), replace, and bulk delete.
- **Per-blog images** (`BlogImageService`/`BlogImageController`, nested under
  `blogs/{blog}/images`): featured/banner/inline/gallery images with the full
  metadata set from the spec (alt, title, caption, description, credit,
  source URL, width/height, lazy-load flag) and a `version` counter that
  increments on replace — the blog edit page now has a working gallery panel.
- AVIF generation is **best-effort**: wrapped in try/catch, logs a warning
  and simply omits that variant if the server's GD build lacks AVIF support,
  rather than failing the whole upload. Same for the Spatie optimizer step —
  its CLI binaries (jpegoptim, cwebp, etc.) usually aren't installed on shared
  hosting/XAMPP by default, so that step is skipped gracefully, not fatal.

## Design decision: two separate image systems, on purpose
Phase 1's schema has both a general `media` table (Media Library) and a
per-post `blog_images` table. Rather than picking one, this phase uses them
for what they're actually good at:
- **Media Library** (`media` table) — shared assets you'll reuse across
  multiple posts (a logo, a recurring diagram). Dedup by hash, reuse counter.
- **`blog_images`** — images that belong to *one* post and carry that post's
  own caption/credit/alt text, ordered within that post (gallery/inline),
  versioned on replace.

The old `blog_gallery` table from Phase 1's migrations is superseded by
`blog_images` with `image_type = gallery` (which has the full metadata
columns `blog_gallery` never did) — it's still in the schema for now but no
new code writes to it. Worth dropping in a later cleanup migration if you're
not using it.

## What you'll need on the server for this to fully work
- **GD extension** (already required — enabled it for you in an earlier step).
- **PHP queue worker running** (`php artisan queue:work`) — without it,
  uploads still succeed and show the original image immediately, but
  thumbnail/medium/large/WebP/AVIF variants won't generate until a worker
  processes the `jobs` table. For local dev, `QUEUE_CONNECTION=sync` in
  `.env` will run jobs immediately instead (fine for testing, not for
  production — see `queue:work` in the deployment guide, coming in a later
  phase).
- AVIF support is optional — check with `php -r "var_dump(function_exists('imageavif'));"`.
  If it's `false`, set `IMAGE_GENERATE_AVIF=false` in `.env` to skip it
  cleanly instead of logging a warning on every upload.

## What's NOT yet wired up
- No image cropping UI yet (the spec's "Support image cropping" line) —
  Intervention Image supports server-side cropping, but a crop *tool* needs
  client-side JS (e.g. Cropper.js) that hasn't been added; flagging rather
  than faking it.
- Inline images *within* blog content (as opposed to the gallery) are meant
  to be inserted from the rich text editor toolbar — that's Phase 5.
- No image sitemap / CDN integration — that's part of the SEO module.

## To pick up from here
```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
php artisan queue:work   # separate terminal, or set QUEUE_CONNECTION=sync for quick local testing
php artisan serve
```
