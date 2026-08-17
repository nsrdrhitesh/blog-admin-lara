<?php

namespace App\Services;

use App\Models\Author;
use App\Repositories\Interfaces\AuthorRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Storage;

class AuthorService
{
    public function __construct(
        protected AuthorRepositoryInterface $authors,
    ) {}

    public function list(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return $this->authors->paginate($perPage, $filters);
    }

    public function find(int $id): Author
    {
        return $this->authors->findOrFail($id);
    }

    public function create(array $attributes): Author
    {
        return $this->authors->create($attributes);
    }

    public function update(Author $author, array $attributes): Author
    {
        if (! empty($attributes['photo']) && $author->photo && $attributes['photo'] !== $author->photo) {
            Storage::disk('public')->delete($author->photo);
        }

        return $this->authors->update($author, $attributes);
    }

    public function delete(Author $author): bool
    {
        if ($author->photo) {
            Storage::disk('public')->delete($author->photo);
        }

        return $this->authors->delete($author);
    }
}
