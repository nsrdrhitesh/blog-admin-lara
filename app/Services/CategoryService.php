<?php

namespace App\Services;

use App\Models\Category;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function __construct(
        protected CategoryRepositoryInterface $categories,
    ) {}

    public function list(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->categories->paginate($perPage, $filters);
    }

    public function tree(): Collection
    {
        return $this->categories->tree();
    }

    public function find(int $id): Category
    {
        return $this->categories->findOrFail($id);
    }

    public function create(array $attributes): Category
    {
        return $this->categories->create($attributes);
    }

    public function update(Category $category, array $attributes): Category
    {
        return $this->categories->update($category, $attributes);
    }

    public function delete(Category $category): bool
    {
        return $this->categories->delete($category);
    }
}
