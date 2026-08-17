# Phase 6 — SEO + GEO Modules

## What's included
- **SEO panel** on the blog edit screen: meta title/description with live
  character counters and a live Google SERP preview, focus keyword +
  secondary keywords, canonical URL, robots directive, Open Graph and
  Twitter Card fields (with image upload), and a **0–100 SEO score with
  actionable suggestions** (`SeoAnalyzerService`) recalculated on every save.
- **GEO panel**: AI summary (long + short), key takeaways, highlights,
  pros/cons, entities (people/companies/products/locations/events/topics),
  citation URLs, E-E-A-T signals (experience/expertise/authority/trust),
  reviewer + reviewed date, and a Speakable schema toggle with CSS selectors.
- **FAQ manager**: add/edit/delete question-answer pairs per post, feeding
  straight into FAQPage JSON-LD.
- **`SchemaGeneratorService`**: builds and persists Article, Breadcrumb, FAQ,
  and Speakable JSON-LD into the `schemas` table automatically — every time
  `BlogService::create()`/`update()` runs (so it's always current even if
  you never open the SEO/GEO panels), and again whenever the SEO panel, GEO
  panel, or FAQs change.
- **Sitemap, image sitemap, RSS feed, dynamic robots.txt** — all public
  routes (`/sitemap.xml`, `/sitemap-images.xml`, `/rss.xml`, `/robots.txt`),
  cached for an hour since crawlers don't need millisecond freshness.
- **Redirect Manager** (`/admin/redirects`) plus a `CheckRedirects`
  middleware that catches any 404 and checks the `redirects` table before
  giving up — this is also what makes `HasSlug`'s automatic redirect
  creation (Phase 1) actually take effect when a post is renamed.

## A real bug fixed while building this phase
The blog edit form (Phases 3–5) had `<form>` tags nested inside the outer
post-edit `<form>` — the Gallery upload/delete forms and the Delete-post
button were all inside it. **Nested forms are invalid HTML**; browsers
handle this by implicitly closing the outer form early, which meant fields
placed after the first nested form (anything in the sidebar — category, tags,
status, publish date, featured image) could silently fail to submit,
depending on the browser's exact parsing behavior. This wasn't
theoretical — it's the kind of bug that "works in my testing" and then
intermittently drops fields in production.

Fixed by giving the real post form an id (`#blog-form`) and having every
sidebar input reference it via the `form="blog-form"` attribute instead of
relying on DOM nesting — the same pattern already used for the Tags table's
inline edit forms in Phase 3. The Gallery/Delete/SEO/GEO/FAQ forms are now
genuine sibling `<form>` elements, not descendants of the main form.

## Design decisions worth knowing about
- **`RedirectService` skips the Repository interface pattern** used
  everywhere else. Redirects are one flat table, no relationships, three
  simple queries — an interface+repository pair would be boilerplate with
  no benefit. Every other module (Blog, Category, Tag, Author, Media) keeps
  the pattern; this is a deliberate, narrow exception, not a shortcut taken
  under time pressure.
- **`SettingService` was created ahead of schedule.** Phase 1's `helpers.php`
  already referenced `App\Services\SettingService::get()` in the `setting()`
  helper, but that class was never actually written — a latent bug that
  would have thrown "class not found" the first time anything called
  `setting()`. This phase's `robots.txt` route needed it, so it's built now
  (simple cached key-value reader/writer over the `settings` table). The
  full Settings *admin UI* (Website/SMTP/Analytics/Social/Ads/Contact
  screens from the spec) is still a later phase — this is just the
  service layer it'll sit on top of.
- **Schema coverage is a deliberate subset.** Article, Breadcrumb, FAQ, and
  Speakable are fully derivable from data that exists today. Organization
  and Person schema need site-wide identity data (company name, logo, social
  profiles) that belongs in Settings, not on a per-post basis — building
  them now would mean hardcoding placeholder data. SearchAction, Video, and
  HowTo schema need authoring UI (a defined search endpoint; per-post video
  metadata; step-by-step HowTo fields) that doesn't exist yet. All four are
  straightforward additions to `SchemaGeneratorService` once their data
  sources exist — not a redesign.
- **The SEO score is a heuristic, not a certification.** It checks title/
  description length, focus keyword placement, content length, image alt
  text, and OG image presence — the same checks any SEO plugin does. It's
  useful for catching obvious gaps, not a guarantee of ranking.
- **Only Blog gets the SEO/GEO/FAQ panel UI this phase**, even though
  `HasSeo`/`HasGeoMeta`/`HasFaqs`/`HasSchemas` are on `Category` and `Page`
  too (from Phase 1) and `SeoService`/`GeoMetaService` work against any
  model using those traits unchanged. Wiring the same three Blade partials
  into the Category/Page forms is a follow-up, not new backend work.
- **No broken-link checker.** The spec lists this; it needs to crawl every
  internal link in every post's content and verify it resolves, which is
  its own background job + UI, not a small addition to this phase. Flagging
  as not built rather than quietly skipping it.

## What's NOT yet wired up
- Public blog pages (`/blog/{slug}`) don't exist yet — the sitemap, RSS
  feed, and JSON-LD all generate correct URLs for a public site, but there's
  nothing at those URLs yet. That's consistent with the original phase
  plan: a public frontend was never its own phase — Phase 8 (REST API) is
  what a headless frontend would consume instead.
- No broken link checker (see above).
- SEO/GEO panels aren't on Category or Page forms yet (see above).

## To pick up from here
Same as before — `composer install`, `.env`, `migrate --seed`,
`storage:link`, `serve`. Check `/robots.txt` and `/sitemap.xml` once you
have a published post or two.
