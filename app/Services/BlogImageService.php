<?php

namespace App\Services;

use App\Enums\ImageType;
use App\Jobs\ProcessBlogImage;
use App\Models\Blog;
use App\Models\BlogImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Handles the featured/banner/inline/gallery images attached to a single
 * Blog post (the blog_images table) — separate from the general Media
 * Library (MediaService) because these carry per-post metadata (alt,
 * caption, credit, source, sort order within the post) that a shared
 * library item doesn't have.
 */
class BlogImageService
{
    public function __construct(
        protected ImageProcessingService $processor,
    ) {}

    public function upload(Blog $blog, UploadedFile $file, ImageType $type, array $meta = []): BlogImage
    {
        return DB::transaction(function () use ($blog, $file, $type, $meta) {
            $stored = $this->processor->storeOriginal($file, 'blogs/'.date('Y/m'));

            $nextOrder = $blog->images()->where('image_type', $type->value)->max('sort_order') + 1;

            $blogImage = $blog->images()->create([
                'path' => $stored['path'],
                'original_path' => $stored['path'],
                'alt_text' => $meta['alt_text'] ?? null,
                'title' => $meta['title'] ?? null,
                'caption' => $meta['caption'] ?? null,
                'description' => $meta['description'] ?? null,
                'credit' => $meta['credit'] ?? null,
                'source_url' => $meta['source_url'] ?? null,
                'width' => $stored['width'],
                'height' => $stored['height'],
                'file_size' => $stored['file_size'],
                'mime_type' => $stored['mime_type'],
                'hash' => $stored['hash'],
                'image_type' => $type,
                'sort_order' => $nextOrder,
                'lazy_load' => $meta['lazy_load'] ?? true,
                'version' => 1,
            ]);

            ProcessBlogImage::dispatch($blogImage->id);

            return $blogImage;
        });
    }

    public function updateMeta(BlogImage $blogImage, array $meta): BlogImage
    {
        $blogImage->update([
            'alt_text' => $meta['alt_text'] ?? $blogImage->alt_text,
            'title' => $meta['title'] ?? $blogImage->title,
            'caption' => $meta['caption'] ?? $blogImage->caption,
            'description' => $meta['description'] ?? $blogImage->description,
            'credit' => $meta['credit'] ?? $blogImage->credit,
            'source_url' => $meta['source_url'] ?? $blogImage->source_url,
            'lazy_load' => array_key_exists('lazy_load', $meta) ? (bool) $meta['lazy_load'] : $blogImage->lazy_load,
        ]);

        return $blogImage->fresh();
    }

    /**
     * Swap the underlying file but keep the same BlogImage row (and bump
     * `version`) so anything referencing this image's ID stays valid —
     * the spec's "image versioning" / "image replacement" requirement.
     */
    public function replace(BlogImage $blogImage, UploadedFile $file): BlogImage
    {
        $this->processor->deleteAllVariants([
            $blogImage->path, $blogImage->webp_path, $blogImage->avif_path,
            $blogImage->thumbnail_path, $blogImage->medium_path, $blogImage->large_path,
        ]);

        $stored = $this->processor->storeOriginal($file, 'blogs/'.date('Y/m'));

        $blogImage->update([
            'path' => $stored['path'],
            'original_path' => $stored['path'],
            'webp_path' => null,
            'avif_path' => null,
            'thumbnail_path' => null,
            'medium_path' => null,
            'large_path' => null,
            'width' => $stored['width'],
            'height' => $stored['height'],
            'file_size' => $stored['file_size'],
            'mime_type' => $stored['mime_type'],
            'hash' => $stored['hash'],
            'version' => $blogImage->version + 1,
        ]);

        ProcessBlogImage::dispatch($blogImage->id);

        return $blogImage->fresh();
    }

    public function reorder(Blog $blog, array $orderedIds): void
    {
        foreach ($orderedIds as $index => $id) {
            BlogImage::where('id', $id)->where('blog_id', $blog->id)->update(['sort_order' => $index]);
        }
    }

    public function delete(BlogImage $blogImage): bool
    {
        $this->processor->deleteAllVariants([
            $blogImage->path, $blogImage->webp_path, $blogImage->avif_path,
            $blogImage->thumbnail_path, $blogImage->medium_path, $blogImage->large_path,
        ]);

        return (bool) $blogImage->delete();
    }
}
