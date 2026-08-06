<?php

use Illuminate\Support\Str;

if (! function_exists('reading_time')) {
    /**
     * Estimate reading time in minutes from plain-text content.
     */
    function reading_time(string $content, int $wordsPerMinute = 200): int
    {
        $wordCount = str_word_count(strip_tags($content));

        return max(1, (int) ceil($wordCount / $wordsPerMinute));
    }
}

if (! function_exists('format_bytes')) {
    function format_bytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1024 ** $pow);

        return round($bytes, $precision).' '.$units[$pow];
    }
}

if (! function_exists('excerpt_from_html')) {
    function excerpt_from_html(string $html, int $length = 160): string
    {
        $text = trim(preg_replace('/\s+/', ' ', strip_tags($html)));

        return Str::limit($text, $length);
    }
}

if (! function_exists('setting')) {
    /**
     * Read a cached site setting value by key.
     */
    function setting(string $key, mixed $default = null): mixed
    {
        return \App\Services\SettingService::get($key, $default);
    }
}

if (! function_exists('media_url')) {
    /**
     * Resolve a stored relative path (e.g. "blogs/2026/08/x.webp") to a public URL.
     */
    function media_url(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        return Str::startsWith($path, ['http://', 'https://'])
            ? $path
            : \Illuminate\Support\Facades\Storage::disk('public')->url($path);
    }
}
