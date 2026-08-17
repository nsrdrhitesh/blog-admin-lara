<?php

namespace App\Repositories\Interfaces;

use App\Models\Blog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

interface BlogRepositoryInterface extends BaseRepositoryInterface
{
    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator;

    public function findBySlug(string $slug): ?Blog;

    public function related(Blog $blog, int $limit = 4): Collection;

    public function bulkUpdateStatus(array $ids, string $status): int;

    public function bulkDelete(array $ids): int;

    /**
     * Public-API-facing variants — scoped to published posts only, unlike
     * paginate() above which the admin panel uses to list every status.
     */
    public function publishedPaginate(int $perPage = 15, array $filters = []): LengthAwarePaginator;

    public function findPublishedBySlug(string $slug): ?Blog;

    public function latest(int $limit = 10): Collection;

    public function trending(int $limit = 10): Collection;

    public function featured(int $limit = 10): Collection;

    public function search(string $query, int $limit = 20): Collection;
}
