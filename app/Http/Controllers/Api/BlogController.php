<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogDetailResource;
use App\Http\Resources\BlogResource;
use App\Repositories\Interfaces\BlogRepositoryInterface;
use App\Services\BlogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class BlogController extends Controller
{
    public function __construct(
        protected BlogRepositoryInterface $blogs,
        protected BlogService $blogService,
    ) {}

    /**
     * GET /api/blogs — filters: category, tag, author (all by slug), search, per_page.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) $request->input('per_page', 15), 50);

        $blogs = $this->blogs->publishedPaginate($perPage, $request->only(['category', 'tag', 'author', 'search']));

        return BlogResource::collection($blogs)->response();
    }

    /**
     * GET /api/blogs/{slug} — full detail: content, images, SEO, JSON-LD
     * schema, FAQs, GEO summary. Also records a view.
     */
    public function show(Request $request, string $slug): JsonResponse
    {
        $blog = $this->blogs->findPublishedBySlug($slug);

        if (! $blog) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        $blog->load(['category', 'subCategory', 'author', 'tags', 'images', 'seo', 'schemas', 'faqs', 'geoMeta']);

        $this->blogService->recordView(
            $blog,
            $request->user()?->id,
            $request->ip(),
            $request->userAgent(),
            $request->header('referer')
        );

        return (new BlogDetailResource($blog))->response();
    }

    /**
     * GET /api/latest — most recently published posts.
     */
    public function latest(Request $request): JsonResponse
    {
        $limit = min((int) $request->input('limit', 10), 30);

        // Cached under one fixed-size key (not per requested limit — a
        // cache key per distinct ?limit= value is unbounded and can't be
        // cleanly invalidated) and sliced down to what was actually asked for.
        $blogs = Cache::remember('api:latest', 300, fn () => $this->blogs->latest(30))->take($limit);

        return BlogResource::collection($blogs)->response();
    }

    /**
     * GET /api/trending — flagged trending, ordered by view count.
     */
    public function trending(Request $request): JsonResponse
    {
        $limit = min((int) $request->input('limit', 10), 30);

        $blogs = Cache::remember('api:trending', 300, fn () => $this->blogs->trending(30))->take($limit);

        return BlogResource::collection($blogs)->response();
    }

    /**
     * GET /api/featured
     */
    public function featured(Request $request): JsonResponse
    {
        $limit = min((int) $request->input('limit', 10), 30);

        $blogs = Cache::remember('api:featured', 300, fn () => $this->blogs->featured(30))->take($limit);

        return BlogResource::collection($blogs)->response();
    }

    /**
     * GET /api/related/{slug} — same category, excludes the post itself.
     */
    public function related(Request $request, string $slug): JsonResponse
    {
        $blog = $this->blogs->findPublishedBySlug($slug);

        if (! $blog) {
            return response()->json(['message' => 'Post not found.'], 404);
        }

        $limit = min((int) $request->input('limit', 4), 12);

        return BlogResource::collection($this->blogs->related($blog, $limit))->response();
    }
}
