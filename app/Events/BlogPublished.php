<?php

namespace App\Events;

use App\Models\Blog;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

/**
 * Dispatched when a blog's status transitions into "published" — later
 * phases hook listeners here for sitemap regeneration, cache busting,
 * and social/webhook notifications.
 */
class BlogPublished
{
    use Dispatchable, SerializesModels;

    public function __construct(public readonly Blog $blog) {}
}
