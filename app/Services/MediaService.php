<?php

namespace App\Services;

use App\Jobs\ProcessMediaImage;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Repositories\Interfaces\MediaRepositoryInterface;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MediaService
{
    public function __construct(
        protected MediaRepositoryInterface $media,
        protected ImageProcessingService $processor,
    ) {}

    public function list(array $filters, int $perPage = 24): LengthAwarePaginator
    {
        return $this->media->paginate($perPage, $filters);
    }

    public function find(int $id): Media
    {
        return $this->media->findOrFail($id);
    }

    /**
     * Store an upload, deduplicating by content hash. If an identical file
     * has already been uploaded, the existing Media row is reused and its
     * reuse_count incremented instead of storing the file twice — per the
     * spec's "duplicate detection" / "image reuse" requirements.
     */
    public function upload(UploadedFile $file, ?int $folderId = null): Media
    {
        $hash = hash_file('sha256', $file->getRealPath());
        $existing = $this->media->findByHash($hash);

        if ($existing) {
            $existing->increment('reuse_count');

            return $existing;
        }

        return DB::transaction(function () use ($file, $folderId) {
            $stored = $this->processor->storeOriginal($file, 'media/'.date('Y/m'));

            $media = $this->media->create([
                'folder_id' => $folderId,
                'uploaded_by' => Auth::id(),
                'disk' => 'public',
                'path' => $stored['path'],
                'original_name' => $file->getClientOriginalName(),
                'mime_type' => $stored['mime_type'],
                'file_size' => $stored['file_size'],
                'width' => $stored['width'],
                'height' => $stored['height'],
                'hash' => $stored['hash'],
            ]);

            ProcessMediaImage::dispatch($media->id);

            return $media;
        });
    }

    /**
     * Replace the underlying file for an existing Media row (same DB id,
     * so every place it's referenced keeps pointing at the same record) —
     * per the spec's "image replacement" requirement.
     */
    public function replace(Media $media, UploadedFile $file): Media
    {
        $this->processor->deleteAllVariants([
            $media->path, $media->webp_path, $media->avif_path, $media->thumbnail_path,
        ]);

        $stored = $this->processor->storeOriginal($file, 'media/'.date('Y/m'));

        $media = $this->media->update($media, [
            'path' => $stored['path'],
            'webp_path' => null,
            'avif_path' => null,
            'thumbnail_path' => null,
            'original_name' => $file->getClientOriginalName(),
            'mime_type' => $stored['mime_type'],
            'file_size' => $stored['file_size'],
            'width' => $stored['width'],
            'height' => $stored['height'],
            'hash' => $stored['hash'],
        ]);

        ProcessMediaImage::dispatch($media->id);

        return $media;
    }

    public function update(Media $media, array $attributes): Media
    {
        return $this->media->update($media, $attributes);
    }

    public function delete(Media $media): bool
    {
        $this->processor->deleteAllVariants([
            $media->path, $media->webp_path, $media->avif_path, $media->thumbnail_path,
        ]);

        return $this->media->delete($media);
    }

    public function bulkDelete(array $ids): int
    {
        $items = Media::whereIn('id', $ids)->get();

        foreach ($items as $media) {
            $this->processor->deleteAllVariants([
                $media->path, $media->webp_path, $media->avif_path, $media->thumbnail_path,
            ]);
        }

        return $this->media->bulkDelete($ids);
    }

    public function createFolder(?int $parentId, string $name): MediaFolder
    {
        return MediaFolder::create([
            'parent_id' => $parentId,
            'name' => $name,
            'slug' => \Illuminate\Support\Str::slug($name),
        ]);
    }

    public function deleteFolder(MediaFolder $folder): bool
    {
        // Files inside are NOT deleted — they're just detached so nothing
        // in the media library silently disappears from under a blog post.
        $folder->media()->update(['folder_id' => null]);

        return $folder->delete();
    }
}
