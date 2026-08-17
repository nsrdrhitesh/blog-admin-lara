<?php

namespace App\Repositories\Eloquent;

use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class AuthorRepository extends BaseRepository implements AuthorRepositoryInterface
{
    public function __construct(Author $model)
    {
        $this->model = $model;
    }

    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        return $this->model->query()
            ->withCount('blogs')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->paginate($perPage)
            ->withQueryString();
    }
}
