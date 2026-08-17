<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Page;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

/**
 * Public XML sitemaps, image sitemap, RSS feed, and a dynamic robots.txt.
 * Cached for an hour since these are crawler-facing, not user-facing —
 * a new post doesn't need to appear within milliseconds.
 */
class SitemapController extends Controller
{
    public function index(): Response
    {
        $urls = Cache::remember('sitemap.urls', 3600, function () {
            $blogs = Blog::published()->select('slug', 'updated_at')->get()
                ->map(fn ($blog) => ['loc' => url('/blog/'.$blog->slug), 'lastmod' => $blog->updated_at]);

            $categories = Category::where('is_active', true)->select('slug', 'updated_at')->get()
                ->map(fn ($cat) => ['loc' => url('/category/'.$cat->slug), 'lastmod' => $cat->updated_at]);

            $pages = Page::where('status', 'published')->select('slug', 'updated_at')->get()
                ->map(fn ($page) => ['loc' => url('/page/'.$page->slug), 'lastmod' => $page->updated_at]);

            return $blogs->concat($categories)->concat($pages);
        });

        return response()
            ->view('sitemaps.urlset', ['urls' => $urls])
            ->header('Content-Type', 'text/xml');
    }

    /**
     * Image sitemap — one <url> per post that has a featured image, each
     * listing that post's inline/gallery images too (spec: "Image Sitemap").
     */
    public function images(): Response
    {
        $entries = Cache::remember('sitemap.images', 3600, function () {
            return Blog::published()
                ->with('images')
                ->whereNotNull('featured_image')
                ->orWhereHas('images')
                ->get()
                ->map(function (Blog $blog) {
                    $images = $blog->images->map(fn ($img) => [
                        'loc' => $img->url,
                        'caption' => $img->caption,
                        'title' => $img->title,
                    ])->values();

                    if ($blog->featured_image) {
                        $images->prepend(['loc' => media_url($blog->featured_image), 'caption' => $blog->title, 'title' => $blog->title]);
                    }

                    return ['loc' => url('/blog/'.$blog->slug), 'images' => $images];
                })
                ->filter(fn ($entry) => $entry['images']->isNotEmpty());
        });

        return response()
            ->view('sitemaps.images', ['entries' => $entries])
            ->header('Content-Type', 'text/xml');
    }

    public function rss(): Response
    {
        $blogs = Cache::remember('rss.feed', 900, function () {
            return Blog::published()->with(['author', 'category'])->latest('published_at')->limit(50)->get();
        });

        return response()
            ->view('sitemaps.rss', ['blogs' => $blogs])
            ->header('Content-Type', 'application/rss+xml; charset=UTF-8');
    }

    public function robots(): Response
    {
        $disallow = setting('robots_disallow');

        $lines = [
            'User-agent: *',
            $disallow ? 'Disallow: '.$disallow : 'Disallow:',
            '',
            'Sitemap: '.url('/sitemap.xml'),
        ];

        return response(implode("\n", $lines))->header('Content-Type', 'text/plain');
    }
}
