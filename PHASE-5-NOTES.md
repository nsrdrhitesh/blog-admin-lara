# Phase 5 — Content Editor

## What's included
TinyMCE 7, wired directly into the blog form's `content` field, self-hosted via
jsdelivr (`cdn.jsdelivr.net/npm/tinymce@7`) rather than TinyMCE's own
`cdn.tiny.cloud` — that's the difference between "needs an API key" and
"doesn't." `license_key: 'gpl'` in the init config is the documented way to
run it under the open-source license without the evaluation nag banner. No
Node build step anywhere in this — it's a `<script>` tag.

Covers every item from the spec's Content Editor list:
- **Images** — inserting, dragging, or pasting an image uploads it through
  the exact same `BlogImageController`/`BlogImageService` pipeline from
  Phase 4 (`image_type: inline`), so it gets the same WebP/AVIF/thumbnail
  variants as gallery images, queued the same way.
- **Tables, headings, lists, links, code** (`codesample` for syntax-highlighted
  blocks, `code` for raw HTML source view — the spec's "Custom HTML").
- **Quotes** — native `blockquote` formatting.
- **Callouts** — a custom toolbar button wrapping the selection in
  `<div class="callout">`, styled inline via `content_style`.
- **Embeds** — a custom dialog that parses a pasted YouTube/Vimeo URL into
  a responsive `<iframe>`.
- **Image gallery** — a custom toolbar button (only shown when the post has
  gallery images already) that opens a picker of this post's own gallery
  images from Phase 4, so you're not re-uploading something you already
  attached.
- **Image alt/caption/alignment/resize/drag** — TinyMCE's built-in `image`
  plugin dialog handles alt text, alignment, and resize by dragging the
  corner handles once an image is in the editor.
- **Markdown paste** — a small regex-based converter (headers, bold/italic,
  links, unordered lists, inline code) runs on paste when the pasted text
  looks like markdown. This is **not** a full CommonMark implementation —
  tables, nested lists, and ordered lists in pasted markdown won't convert.
  Good enough for "pasted from a notes app," not a markdown authoring tool.
- **Automatic slug** — a live JS preview under the Slug field as you type the
  title (cosmetic only; the actual slug is still generated server-side by
  `HasSlug` from Phase 1 if the field is left blank — that's the source of
  truth, not the JS).
- **Word count / character count / reading time** — TinyMCE's `wordcount`
  plugin feeds a small stats line above the editor (`312 words · ~2 min
  read`), recalculated on every keystroke.
- **Auto save** — client-side only: the editor content is written to
  `localStorage` every 15 seconds while dirty, and on load, if a newer local
  draft exists than what's in the field, you're prompted to restore it.
  **This is a crash-recovery net, not a real autosave-as-draft feature** —
  it never touches the server or creates revisions. See below for why.

## Known limitation, stated plainly: no inline images on a brand-new post
Inline image upload needs a `blog_id` to attach the `BlogImage` row to.
A **New Post** screen doesn't have one yet — the row doesn't exist until you
save. Rather than build a WordPress-style "auto-draft" (silently creating a
draft blog the instant you open the New Post screen, before you've typed
anything, and cleaning up orphaned auto-drafts later), the image button is
simply **disabled with a visible message** on the create form: "Save this
post once to enable inline image uploads." After the first save you're on
the edit screen with a real ID and the toolbar's image button, drag-drop,
and paste-upload all work normally.

This is a real, deliberate scope line — not a bug. Building the auto-draft
flow properly (orphan cleanup, race conditions if the user never finishes
the draft, etc.) is more surface area than this phase's budget covers well;
flagging it here so it's a known follow-up rather than a silent gap.

## What's NOT yet wired up
- No real server-side autosave/draft history/revisions (see above).
- No image cropping tool inside the editor (same gap noted in Phase 4).
- Markdown paste is best-effort, not spec-complete (see above).

## To pick up from here
Same as Phase 4 — nothing new needed beyond `composer install` /
`php artisan serve`. Open any existing blog post's edit screen to see the
editor; TinyMCE loads from jsdelivr so the admin machine needs internet
access to view the page (this is normal — admin panels loading a CDN-hosted
JS library is standard practice and doesn't require anything on the shared
hosting server itself).
