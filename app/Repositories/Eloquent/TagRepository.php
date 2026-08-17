<?php

namespace App\Repositories\Eloquent;

use App\Models\Tag;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;

class TagRepository extends BaseRepository implements TagRepositoryInterface
{
    public function __construct(Tag $model)
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

    /**
     * Used by the blog editor's tag picker — accepts free-typed tag names
     * and creates any that don't exist yet, returning their IDs.
     */
    public function findOrCreateByNames(array $names): array
    {
        return collect($names)
            ->filter()
            ->map(function (string $name) {
                return $this->model->firstOrCreate(
                    ['slug' => Str::slug($name)],
                    ['name' => trim($name)]
                )->id;
            })
            ->all();
    }
}
