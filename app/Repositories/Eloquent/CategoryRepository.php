<?php

namespace App\Repositories\Eloquent;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
    public function __construct(Category $model)
    {
        $this->model = $model;
    }

    public function paginate(int $perPage = 20, array $filters = []): LengthAwarePaginator
    {
        return $this->model->query()
            ->with('parent')
            ->withCount('blogs')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function tree(): Collection
    {
        return $this->model->query()
            ->whereNull('parent_id')
            ->with('children')
            ->orderBy('sort_order')
            ->get();
    }
}
