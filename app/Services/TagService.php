<?php

namespace App\Services;

use App\Models\Tag;
use App\Repositories\Interfaces\TagRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class TagService
{
    public function __construct(
        protected TagRepositoryInterface $tags,
    ) {}

    public function list(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->tags->paginate($perPage, $filters);
    }

    public function find(int $id): Tag
    {
        return $this->tags->findOrFail($id);
    }

    public function create(array $attributes): Tag
    {
        return $this->tags->create($attributes);
    }

    public function update(Tag $tag, array $attributes): Tag
    {
        return $this->tags->update($tag, $attributes);
    }

    public function delete(Tag $tag): bool
    {
        return $this->tags->delete($tag);
    }
}
