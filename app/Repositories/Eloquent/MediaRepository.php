<?php

namespace App\Repositories\Eloquent;

use App\Models\Media;
use App\Repositories\Interfaces\MediaRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class MediaRepository extends BaseRepository implements MediaRepositoryInterface
{
    public function __construct(Media $model)
    {
        $this->model = $model;
    }

    /**
     * Filters: search (by original_name/title/alt), folder_id, mime_type.
     */
    public function paginate(int $perPage = 24, array $filters = []): LengthAwarePaginator
    {
        return $this->model->query()
            ->with('folder')
            ->when($filters['search'] ?? null, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('original_name', 'like', "%{$search}%")
                        ->orWhere('title', 'like', "%{$search}%")
                        ->orWhere('alt_text', 'like', "%{$search}%");
                });
            })
            ->when(array_key_exists('folder_id', $filters), fn ($query) => $query->where('folder_id', $filters['folder_id']))
            ->when($filters['mime_type'] ?? null, fn ($query, $mime) => $query->where('mime_type', 'like', "{$mime}%"))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findByHash(string $hash): ?Media
    {
        return $this->model->where('hash', $hash)->first();
    }

    public function bulkDelete(array $ids): int
    {
        return $this->model->whereIn('id', $ids)->delete();
    }
}
