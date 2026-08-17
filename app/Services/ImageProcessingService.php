<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Spatie\ImageOptimizer\OptimizerChainFactory;

/**
 * Wraps Intervention Image (GD driver — no Imagick required, matches a
 * typical shared-hosting / XAMPP PHP build) to turn one uploaded file into
 * a full set of variants: original, thumbnail/medium/large resizes, and
 * WebP/AVIF re-encodes. Every variant is compressed with Spatie's image
 * optimizer chain before being written to disk.
 *
 * Split into two steps on purpose:
 *   storeOriginal()   — fast, runs synchronously in the request so the user
 *                       gets an immediate preview + duplicate-hash check.
 *   generateVariants() — slow (multiple resizes/re-encodes), runs inside
 *                       ProcessMediaImageJob on the queue (per the spec's
 *                       "Queue Image Processing" requirement).
 */
class ImageProcessingService
{
    protected ImageManager $manager;

    public function __construct()
    {
        $this->manager = new ImageManager(new Driver);
    }

    /**
     * @return array{path: string, width: int, height: int, file_size: int, mime_type: string, hash: string, basename: string}
     */
    public function storeOriginal(UploadedFile $file, string $directory): array
    {
        $hash = hash_file('sha256', $file->getRealPath());
        $basename = Str::uuid()->toString();
        $extension = strtolower($file->getClientOriginalExtension() ?: 'jpg');
        $path = "{$directory}/{$basename}.{$extension}";

        Storage::disk('public')->put($path, file_get_contents($file->getRealPath()));

        [$width, $height] = @getimagesize($file->getRealPath()) ?: [null, null];

        return [
            'path' => $path,
            'basename' => $basename,
            'directory' => $directory,
            'width' => $width,
            'height' => $height,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getMimeType(),
            'hash' => $hash,
        ];
    }

    /**
     * Read the already-stored original back off disk and generate every
     * derived variant. Called from ProcessMediaImageJob, not the request.
     *
     * @return array{webp_path: ?string, avif_path: ?string, thumbnail_path: ?string, medium_path: ?string, large_path: ?string}
     */
    public function generateVariants(string $originalPath, string $directory, string $basename): array
    {
        $fullPath = Storage::disk('public')->path($originalPath);

        $image = $this->manager->read($fullPath);

        return [
            'webp_path' => $this->encodeVariant($image, $directory, $basename, 'webp'),
            'avif_path' => config('media.generate_avif') ? $this->encodeVariant($image, $directory, $basename, 'avif') : null,
            'thumbnail_path' => $this->resizedVariant($fullPath, $directory, $basename, 'thumbnail'),
            'medium_path' => $this->resizedVariant($fullPath, $directory, $basename, 'medium'),
            'large_path' => $this->resizedVariant($fullPath, $directory, $basename, 'large'),
        ];
    }

    /**
     * Re-encode the full-size image into a given format (webp/avif).
     * Returns null (and logs) if the server's GD build can't encode it —
     * AVIF support in particular varies by hosting environment.
     */
    protected function encodeVariant(\Intervention\Image\Interfaces\ImageInterface $image, string $directory, string $basename, string $format): ?string
    {
        try {
            $quality = config("media.quality.{$format}", 80);
            $encoded = match ($format) {
                'webp' => $image->toWebp($quality),
                'avif' => $image->toAvif($quality),
                default => null,
            };

            if (! $encoded) {
                return null;
            }

            $path = "{$directory}/{$basename}.{$format}";
            Storage::disk('public')->put($path, (string) $encoded);
            $this->optimize($path);

            return $path;
        } catch (\Throwable $e) {
            Log::warning("Image variant encode failed ({$format}): ".$e->getMessage());

            return null;
        }
    }

    /**
     * Resize (never upscale) and save as WebP under the given preset name
     * from config/media.php sizes (thumbnail/medium/large).
     */
    protected function resizedVariant(string $fullPath, string $directory, string $basename, string $preset): ?string
    {
        try {
            $targetWidth = config("media.sizes.{$preset}");
            $resized = $this->manager->read($fullPath)->scaleDown(width: $targetWidth);

            $path = "{$directory}/{$basename}-{$preset}.webp";
            Storage::disk('public')->put($path, (string) $resized->toWebp(config('media.quality.webp', 82)));
            $this->optimize($path);

            return $path;
        } catch (\Throwable $e) {
            Log::warning("Image variant resize failed ({$preset}): ".$e->getMessage());

            return null;
        }
    }

    protected function optimize(string $relativePath): void
    {
        try {
            $fullPath = Storage::disk('public')->path($relativePath);
            OptimizerChainFactory::create()->optimize($fullPath);
        } catch (\Throwable $e) {
            // Optimization is best-effort (binaries like jpegoptim/cwebp may
            // not be installed on shared hosting) — never block the upload.
            Log::info('Image optimizer skipped: '.$e->getMessage());
        }
    }

    public function deleteAllVariants(array $paths): void
    {
        $disk = Storage::disk('public');

        foreach (array_filter($paths) as $path) {
            $disk->delete($path);
        }
    }
}
