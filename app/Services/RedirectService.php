<?php

namespace App\Services;

use App\Models\Redirect;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Deliberately skips the Repository pattern used elsewhere — redirects are
 * a single flat table with no relationships or complex queries, so an
 * interface+repository pair here would be pure boilerplate. See
 * PHASE-6-NOTES.md for the reasoning; every other module keeps the pattern.
 */
class RedirectService
{
    public function list(array $filters, int $perPage = 25): LengthAwarePaginator
    {
        return Redirect::query()
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('from_url', 'like', "%{$search}%")
                        ->orWhere('to_url', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $attributes): Redirect
    {
        return Redirect::create($attributes);
    }

    public function update(Redirect $redirect, array $attributes): Redirect
    {
        $redirect->update($attributes);

        return $redirect->fresh();
    }

    public function delete(Redirect $redirect): bool
    {
        return (bool) $redirect->delete();
    }

    /**
     * Looked up by CheckRedirects middleware on a 404. Normalizes the
     * incoming path the same way from_url is stored (leading slash, no
     * trailing slash, no query string) so lookups are consistent regardless
     * of how the URL was originally typed into the redirect manager.
     */
    public function findActiveFor(string $path): ?Redirect
    {
        $normalized = '/'.ltrim(rtrim($path, '/'), '/');

        return Redirect::where('from_url', $normalized)->where('is_active', true)->first();
    }
}
