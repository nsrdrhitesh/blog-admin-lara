<?php

namespace App\Jobs;

use App\Models\BlogImage;
use App\Services\ImageProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessBlogImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(public readonly int $blogImageId) {}

    public function handle(ImageProcessingService $processor): void
    {
        $blogImage = BlogImage::find($this->blogImageId);

        if (! $blogImage) {
            return;
        }

        $path = $blogImage->original_path ?? $blogImage->path;
        $directory = dirname($path);
        $basename = pathinfo($path, PATHINFO_FILENAME);

        $variants = $processor->generateVariants($path, $directory, $basename);

        $blogImage->update([
            'webp_path' => $variants['webp_path'],
            'avif_path' => $variants['avif_path'],
            'thumbnail_path' => $variants['thumbnail_path'],
            'medium_path' => $variants['medium_path'],
            'large_path' => $variants['large_path'],
        ]);
    }
}
