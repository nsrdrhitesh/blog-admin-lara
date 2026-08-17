<?php

namespace App\Services;

use App\DTOs\BlogData;
use App\Enums\BlogStatus;
use App\Events\BlogPublished;
use App\Models\Blog;
use App\Repositories\Interfaces\BlogRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BlogService
{
    public function __construct(
        protected BlogRepositoryInterface $blogs,
        protected SchemaGeneratorService $schemas,
    ) {}

    public function list(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->blogs->paginate($perPage, $filters);
    }

    public function find(int $id): Blog
    {
        return $this->blogs->findOrFail($id);
    }

    public function create(BlogData $data): Blog
    {
        return DB::transaction(function () use ($data) {
            $blog = $this->blogs->create([
                ...$data->toModelAttributes(),
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $blog->tags()->sync($data->tagIds);

            $this->dispatchPublishedEventIfNeeded($blog);

            // Article/Breadcrumb schema is derivable from core fields alone
            // (title, slug, category), so it's kept current even before
            // anyone touches the dedicated SEO/GEO panels.
            $this->schemas->generateForBlog($blog);

            return $blog->fresh(['category', 'author', 'tags']);
        });
    }

    public function update(Blog $blog, BlogData $data): Blog
    {
        return DB::transaction(function () use ($blog, $data) {
            $wasPublished = $blog->status === BlogStatus::Published;

            $blog = $this->blogs->update($blog, [
                ...$data->toModelAttributes(),
                'updated_by' => auth()->id(),
            ]);

            $blog->tags()->sync($data->tagIds);

            if (! $wasPublished) {
                $this->dispatchPublishedEventIfNeeded($blog);
            }

            $this->schemas->generateForBlog($blog);

            return $blog->fresh(['category', 'author', 'tags']);
        });
    }

    public function delete(Blog $blog): bool
    {
        return $this->blogs->delete($blog);
    }

    public function bulkUpdateStatus(array $ids, BlogStatus $status): int
    {
        return $this->blogs->bulkUpdateStatus($ids, $status->value);
    }

    public function bulkDelete(array $ids): int
    {
        return $this->blogs->bulkDelete($ids);
    }

    /**
     * Records a view (blog_views row, for later analytics) and bumps the
     * denormalized view_count counter used for the "trending" ordering.
     * Called from the public API's show() endpoint — deliberately simple,
     * no bot filtering or per-IP dedupe; a real analytics pipeline is out
     * of scope here (the spec's "View Count" field just needs to exist and
     * increment, which this does).
     */
    public function recordView(Blog $blog, ?int $userId, string $ipAddress, ?string $userAgent, ?string $referrer): void
    {
        $blog->views()->create([
            'user_id' => $userId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'referrer' => $referrer,
            'viewed_at' => now(),
        ]);

        $blog->increment('view_count');
    }

    protected function dispatchPublishedEventIfNeeded(Blog $blog): void
    {
        if ($blog->status === BlogStatus::Published) {
            event(new BlogPublished($blog));
        }
    }
}
