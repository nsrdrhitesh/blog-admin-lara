<?php

return [
    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    // Populated by the SEO/GEO settings module (Google Analytics, Search
    // Console, social meta) once that module ships.
    'google_analytics' => [
        'id' => env('GOOGLE_ANALYTICS_ID'),
    ],
];
