<?php

namespace App\Services;

use App\Models\Blog;
use App\Models\GeoMeta;
use App\Services\SchemaGeneratorService;

/**
 * Persists the polymorphic GeoMeta record (AI summary, key takeaways,
 * entities, E-E-A-T trust signals, pros/cons, speakable flag) that powers
 * both the admin GEO panel and the FAQ/Speakable JSON-LD schemas.
 */
class GeoMetaService
{
    public function __construct(protected SchemaGeneratorService $schemas) {}

    public function save(Blog $blog, array $attributes): GeoMeta
    {
        // Textareas post one-per-line; split into arrays for the JSON columns.
        foreach (['key_takeaways', 'highlights', 'citation_urls', 'evidence_links'] as $field) {
            if (isset($attributes[$field]) && is_string($attributes[$field])) {
                $attributes[$field] = $this->linesToArray($attributes[$field]);
            }
        }

        foreach (['pros', 'cons'] as $field) {
            if (isset($attributes[$field]) && is_string($attributes[$field])) {
                $attributes[$field] = $this->linesToArray($attributes[$field]);
            }
        }

        if (isset($attributes['references']) && is_array($attributes['references'])) {
            $attributes['references'] = collect($attributes['references'])
                ->filter(fn ($ref) => ! empty($ref['title']) || ! empty($ref['url']))
                ->values()->all();
        }

        if (isset($attributes['entities']) && is_array($attributes['entities'])) {
            $attributes['entities'] = collect($attributes['entities'])
                ->map(fn ($value) => is_string($value) ? $this->linesToArray($value) : $value)
                ->all();
        }

        if (isset($attributes['speakable_selectors']) && is_string($attributes['speakable_selectors'])) {
            $attributes['speakable_selectors'] = $this->linesToArray($attributes['speakable_selectors']);
        }

        $geoMeta = $blog->geoMeta()->updateOrCreate([], $attributes);

        $this->schemas->generateForBlog($blog->fresh());

        return $geoMeta->fresh();
    }

    protected function linesToArray(string $text): array
    {
        return collect(explode("\n", $text))
            ->map(fn ($line) => trim($line))
            ->filter()
            ->values()
            ->all();
    }
}
