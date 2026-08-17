<?php

namespace App\Services;

use App\Models\Page;
use Illuminate\Pagination\LengthAwarePaginator;

class PageService
{
    public function list(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return Page::query()
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->orderByDesc('updated_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function find(int $id): Page
    {
        return Page::findOrFail($id);
    }

    public function create(array $attributes): Page
    {
        $attributes['user_id'] = auth()->id();

        return Page::create($attributes);
    }

    public function update(Page $page, array $attributes): Page
    {
        $page->update($attributes);

        return $page->fresh();
    }

    public function delete(Page $page): bool
    {
        return (bool) $page->delete();
    }
}
