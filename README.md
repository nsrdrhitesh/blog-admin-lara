# Blog CMS

A production-ready blogging CMS and admin panel built on **Laravel 12 +
PHP 8.2 + MySQL**, designed to run on ordinary shared hosting — no Node.js
required on the server, ever (see DEPLOYMENT.md for exactly what that
means in practice).

## Start here

- **New to this project?** Read `INSTALLATION.md` first (local dev setup).
- **Putting this on a live server?** Read `DEPLOYMENT.md` (shared hosting,
  cPanel, cron-based queue/scheduler workarounds).
- **Want to know what's built and why**, phase by phase, including every
  deliberate trade-off and every bug caught along the way: `PHASE-1-NOTES.md`
  through `PHASE-9-NOTES.md` are the real build log, not marketing copy —
  each one says plainly what's done, what's simplified, and what's
  genuinely not built yet at that point in the process.

## What's in here

| Area | Covered |
|---|---|
| Auth & RBAC | Login/forgot/reset password, 4 roles (Super Admin/Admin/Editor/Author), granular permissions |
| Blog content | Full CRUD, categories (nested), tags, authors, bulk actions, CSV/Excel export & import |
| Media | Media Library with folders/search/dedup-by-hash, per-post image gallery, queued WebP/AVIF/thumbnail generation |
| Content editor | TinyMCE (self-hosted, no API key) — tables, embeds, callouts, markdown-paste, image upload |
| SEO | Meta tags, Open Graph/Twitter cards, canonical URLs, focus-keyword scoring, sitemap.xml, RSS, robots.txt, 301 redirect manager |
| GEO | AI summaries, key takeaways, entities, E-E-A-T trust signals, FAQ schema, Speakable schema |
| Comments | Moderation queue, nested replies, approve/reject/spam |
| Settings | Site identity, SMTP, analytics, social, ads, contact, footer/header |
| Menus & Pages | Nested navigation menus, static pages |
| REST API | `/api/blogs`, `/api/categories`, `/api/tags`, `/api/authors`, `/api/search`, `/api/latest`, `/api/trending`, `/api/featured`, `/api/related/{slug}` |
| Admin UX | Dark mode, sticky/collapsible sidebar, global search, quick actions, toast notifications, dashboard widgets |
| Users | Role-based user management |

## Tech stack

Laravel 12, PHP 8.2+, MySQL, Blade + Tailwind CSS (compiled via the
standalone Tailwind CLI — no Node needed, see `PHASE-9-NOTES.md`),
Alpine.js for interactivity, TinyMCE for content editing, Intervention
Image + Spatie Image Optimizer for the media pipeline, Maatwebsite Excel
for import/export.

## Default login

After running `php artisan migrate --seed`:
```
admin@example.com / ChangeMe123!
```
**Change this immediately** from Profile in the admin sidebar.

## A note on how this project was actually built

This was built incrementally across 9 phases in conversation with a
person, not generated in one shot. Several phases involved discovering and
fixing real issues from earlier work — a missing form-request class that
would have caused a fatal error, a filter relation that hid pending
comments from moderators, a CSS rule that silently broke every modal's
open animation, cache keys that couldn't be cleanly invalidated. Every one
of those is documented, not hidden, in the relevant `PHASE-N-NOTES.md`
file. If you're extending this project, those files are worth reading
before you assume something works a certain way — they're the most
accurate description of the actual state of the code, more so than this
README's summary table above.
