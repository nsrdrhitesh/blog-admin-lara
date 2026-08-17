<?php

namespace App\Repositories\Interfaces;

use App\Models\Media;
use Illuminate\Pagination\LengthAwarePaginator;

interface MediaRepositoryInterface extends BaseRepositoryInterface
{
    public function paginate(int $perPage = 24, array $filters = []): LengthAwarePaginator;

    public function findByHash(string $hash): ?Media;

    public function bulkDelete(array $ids): int;
}
