<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Image variant sizes
    |--------------------------------------------------------------------------
    | Widths (px) for each generated variant. Height is auto-scaled to
    | preserve aspect ratio (no upscaling — see ImageProcessingService).
    */
    'sizes' => [
        'thumbnail' => 300,
        'medium' => 800,
        'large' => 1600,
    ],

    'quality' => [
        'webp' => (int) env('IMAGE_WEBP_QUALITY', 82),
        'avif' => (int) env('IMAGE_AVIF_QUALITY', 60),
        'jpg' => (int) env('IMAGE_JPG_QUALITY', 85),
    ],

    // AVIF support depends on the server's GD build (libavif). When
    // unavailable, the pipeline logs a notice and simply skips that variant
    // rather than failing the whole upload.
    'generate_avif' => env('IMAGE_GENERATE_AVIF', true),

    'max_upload_kb' => (int) env('MEDIA_MAX_UPLOAD_KB', 8192),

    'allowed_mimes' => ['jpg', 'jpeg', 'png', 'webp', 'gif'],
];
