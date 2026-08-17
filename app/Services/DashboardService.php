<?php

namespace App\Services;

use App\Enums\BlogStatus;
use App\Enums\CommentStatus;
use App\Models\Blog;
use App\Models\BlogComment;
use Illuminate\Support\Facades\Cache;

/**
 * Backs the admin dashboard widgets. Cached briefly (2 minutes) since
 * every one of these is a full-table aggregate query and the dashboard is
 * the single most-visited admin screen — without a cache, every page load
 * would run 6+ COUNT/SUM queries for numbers that don't need to be
 * second-fresh.
 */
class DashboardService
{
    public function stats(): array
    {
        return Cache::remember('dashboard:stats', 120, function () {
            return [
                'total_posts' => Blog::count(),
                'published' => Blog::where('status', BlogStatus::Published)->count(),
                'drafts' => Blog::where('status', BlogStatus::Draft)->count(),
                'comments_pending' => BlogComment::where('status', CommentStatus::Pending)->count(),
                'total_views' => (int) Blog::sum('view_count'),
            ];
        });
    }

    public function recentPosts(int $limit = 5)
    {
        return Cache::remember("dashboard:recent:{$limit}", 120, function () use ($limit) {
            return Blog::with(['author', 'category'])
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        });
    }

    public function topPosts(int $limit = 5)
    {
        return Cache::remember("dashboard:top:{$limit}", 120, function () use ($limit) {
            return Blog::where('status', BlogStatus::Published)
                ->orderByDesc('view_count')
                ->limit($limit)
                ->get();
        });
    }

    public function recentComments(int $limit = 5)
    {
        return Cache::remember("dashboard:comments:{$limit}", 120, function () use ($limit) {
            return BlogComment::with(['blog:id,title', 'user:id,name'])
                ->whereNull('parent_id')
                ->orderByDesc('created_at')
                ->limit($limit)
                ->get();
        });
    }
}
