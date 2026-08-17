<?php

namespace App\Jobs;

use App\Models\Media;
use App\Services\ImageProcessingService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessMediaImage implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 10;

    public function __construct(public readonly int $mediaId) {}

    public function handle(ImageProcessingService $processor): void
    {
        $media = Media::find($this->mediaId);

        if (! $media) {
            return; // deleted before the job ran
        }

        $directory = dirname($media->path);
        $basename = pathinfo($media->path, PATHINFO_FILENAME);

        $variants = $processor->generateVariants($media->path, $directory, $basename);

        $media->update($variants);
    }
}
