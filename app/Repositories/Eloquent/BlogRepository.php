<?php

namespace App\Repositories\Eloquent;

use App\Enums\BlogStatus;
use App\Models\Blog;
use App\Repositories\Interfaces\BlogRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class BlogRepository extends BaseRepository implements BlogRepositoryInterface
{
    public function __construct(Blog $model)
    {
        $this->model = $model;
    }

    /**
     * Filters: search, status, category_id, author_id, is_featured, is_trending.
     */
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        return $this->model->query()
            ->with(['category', 'author.user'])
            ->withCount(['allComments as comments_count'])
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when($filters['category_id'] ?? null, fn ($query, $id) => $query->where('category_id', $id))
            ->when($filters['author_id'] ?? null, fn ($query, $id) => $query->where('author_id', $id))
            ->when(isset($filters['is_featured']), fn ($query) => $query->where('is_featured', (bool) $filters['is_featured']))
            ->when(isset($filters['is_trending']), fn ($query) => $query->where('is_trending', (bool) $filters['is_trending']))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findBySlug(string $slug): ?Blog
    {
        return $this->model->where('slug', $slug)->first();
    }

    public function related(Blog $blog, int $limit = 4): Collection
    {
        return $this->model->query()
            ->published()
            ->where('id', '!=', $blog->id)
            ->where('category_id', $blog->category_id)
            ->latest('published_at')
            ->limit($limit)
            ->get();
    }

    public function bulkUpdateStatus(array $ids, string $status): int
    {
        $attributes = ['status' => $status];

        if ($status === BlogStatus::Published->value) {
            $attributes['published_at'] = now();
        }

        return $this->model->whereIn('id', $ids)->update($attributes);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    /**
     * Filters: category (slug), tag (slug), author (slug), search.
     * Every public API list endpoint routes through this — scoped to
     * published posts only, unlike paginate() above (admin, all statuses).
     */
    public function publishedPaginate(int $perPage = 15, array $filters = []): LengthAwarePaginator
    {
        return $this->baseApiQuery()
            ->when($filters['category'] ?? null, fn ($q, $slug) => $q->whereHas('category', fn ($c) => $c->where('slug', $slug)))
            ->when($filters['tag'] ?? null, fn ($q, $slug) => $q->whereHas('tags', fn ($t) => $t->where('slug', $slug)))
            ->when($filters['author'] ?? null, fn ($q, $slug) => $q->whereHas('author', fn ($a) => $a->where('slug', $slug)))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($inner) use ($search) {
                    $inner->where('title', 'like', "%{$search}%")
                        ->orWhere('short_description', 'like', "%{$search}%")
                        ->orWhere('content', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('published_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findPublishedBySlug(string $slug): ?Blog
    {
        return $this->baseApiQuery()->where('slug', $slug)->first();
    }

    public function latest(int $limit = 10): Collection
    {
        return $this->baseApiQuery()->orderByDesc('published_at')->limit($limit)->get();
    }

    public function trending(int $limit = 10): Collection
    {
        return $this->baseApiQuery()->where('is_trending', true)
            ->orderByDesc('view_count')->limit($limit)->get();
    }

    public function featured(int $limit = 10): Collection
    {
        return $this->baseApiQuery()->where('is_featured', true)
            ->orderByDesc('published_at')->limit($limit)->get();
    }

    public function search(string $query, int $limit = 20): Collection
    {
        return $this->baseApiQuery()
            ->where(function ($q) use ($query) {
                $q->where('title', 'like', "%{$query}%")
                    ->orWhere('short_description', 'like', "%{$query}%")
                    ->orWhere('content', 'like', "%{$query}%");
            })
            ->orderByDesc('published_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Shared base for every public API query — published-only, with the
     * relations every BlogResource needs eagerly loaded so list endpoints
     * never N+1.
     */
    protected function baseApiQuery(): \Illuminate\Database\Eloquent\Builder
    {
        return $this->model->query()
            ->published()
            ->with(['category', 'author', 'tags']);
    }
}
