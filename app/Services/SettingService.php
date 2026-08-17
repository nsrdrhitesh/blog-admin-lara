<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/**
 * Thin cached key-value accessor over the settings table (Phase 1). Backs
 * the global setting() helper. Full Settings admin UI (grouped by
 * general/mail/seo/social/ads/contact) ships in its own phase — this is
 * just the read/write layer other services (schema builder, sitemap,
 * robots.txt) depend on today.
 */
class SettingService
{
    protected const CACHE_KEY = 'settings:all';

    public static function get(string $key, mixed $default = null): mixed
    {
        return static::all()->get($key, $default);
    }

    public static function set(string $key, mixed $value, string $group = 'general', string $type = 'text'): Setting
    {
        $setting = Setting::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group, 'type' => $type]);

        Cache::forget(static::CACHE_KEY);

        return $setting;
    }

    public static function all(): \Illuminate\Support\Collection
    {
        return Cache::rememberForever(static::CACHE_KEY, fn () => Setting::pluck('value', 'key'));
    }

    public static function forgetCache(): void
    {
        Cache::forget(static::CACHE_KEY);
    }
}
