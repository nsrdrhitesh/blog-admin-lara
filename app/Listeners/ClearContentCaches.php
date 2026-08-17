<?php

namespace App\Listeners;

use App\Events\BlogPublished;
use Illuminate\Support\Facades\Cache;

/**
 * Busts every cache keyed to "the current set of published posts" when a
 * post is published. Written once and reused for the sitemap/RSS caches
 * too (Phase 6 introduced those with a TTL but no invalidation listener —
 * same gap, fixed here rather than duplicated a second time for the API).
 *
 * Only fires on the publish transition (see BlogService::create/update),
 * not on every edit — an already-published post being tweaked doesn't
 * change these listings' membership, only its own content, which each
 * cached list already re-reads fresh relations for anyway once the TTL
 * expires. Good enough for a 5–15 minute staleness window; a full
 * cache-tag-based invalidation on every save is more machinery than this
 * scale of caching benefit justifies.
 */
class ClearContentCaches
{
    public function handle(BlogPublished $event): void
    {
        Cache::forget('api:latest');
        Cache::forget('api:trending');
        Cache::forget('api:featured');
        Cache::forget('sitemap.urls');
        Cache::forget('sitemap.images');
        Cache::forget('rss.feed');
        Cache::forget('dashboard:stats');
        Cache::forget('dashboard:recent:5');
        Cache::forget('dashboard:top:5');
    }
}
