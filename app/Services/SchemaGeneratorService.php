<?php

namespace App\Services;

use App\Enums\SchemaType;
use App\Models\Blog;

/**
 * Builds JSON-LD structured data for a Blog and persists it into the
 * `schemas` polymorphic table (one row per schema type) so it can be
 * rendered on the public post page without recomputing it on every request.
 * Regenerated automatically whenever BlogService creates/updates a post —
 * see BlogService::create()/update().
 *
 * Covers Article, Breadcrumb, and FAQ schema (the three that can be fully
 * derived from data already on the Blog). Organization/Person/SearchAction
 * need site-wide Settings data that doesn't exist until the Settings module
 * ships; Video/HowTo/Speakable need per-post authoring UI that isn't built
 * yet either — see PHASE-6-NOTES.md for the honest list of what's stubbed.
 */
class SchemaGeneratorService
{
    public function generateForBlog(Blog $blog): void
    {
        $blog->loadMissing(['category', 'author', 'faqs', 'geoMeta', 'seo']);

        $this->upsert($blog, SchemaType::Article, $this->buildArticleSchema($blog));
        $this->upsert($blog, SchemaType::Breadcrumb, $this->buildBreadcrumbSchema($blog));

        if ($blog->faqs->isNotEmpty()) {
            $this->upsert($blog, SchemaType::Faq, $this->buildFaqSchema($blog));
        } else {
            $blog->schemas()->where('type', SchemaType::Faq)->delete();
        }

        if ($blog->geoMeta?->speakable_enabled) {
            $this->upsert($blog, SchemaType::Speakable, $this->buildSpeakableSchema($blog));
        } else {
            $blog->schemas()->where('type', SchemaType::Speakable)->delete();
        }
    }

    protected function buildArticleSchema(Blog $blog): array
    {
        $url = url('/blog/'.$blog->slug);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $blog->seo?->meta_title ?: $blog->title,
            'description' => $blog->seo?->meta_description ?: $blog->short_description,
            'image' => array_values(array_filter([media_url($blog->featured_image)])),
            'datePublished' => optional($blog->published_at)->toIso8601String(),
            'dateModified' => $blog->updated_at?->toIso8601String(),
            'author' => $blog->author ? [
                '@type' => 'Person',
                'name' => $blog->author->name,
                'url' => $blog->author->website,
            ] : null,
            'mainEntityOfPage' => ['@type' => 'WebPage', '@id' => $url],
            'articleSection' => $blog->category?->name,
            'wordCount' => $blog->word_count,
            'inLanguage' => $blog->language,
        ];
    }

    protected function buildBreadcrumbSchema(Blog $blog): array
    {
        $items = [
            ['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => url('/')],
        ];

        if ($blog->category) {
            $items[] = [
                '@type' => 'ListItem',
                'position' => 2,
                'name' => $blog->category->name,
                'item' => url('/category/'.$blog->category->slug),
            ];
        }

        $items[] = [
            '@type' => 'ListItem',
            'position' => count($items) + 1,
            'name' => $blog->title,
            'item' => url('/blog/'.$blog->slug),
        ];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'BreadcrumbList',
            'itemListElement' => $items,
        ];
    }

    protected function buildFaqSchema(Blog $blog): array
    {
        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => $blog->faqs->map(fn ($faq) => [
                '@type' => 'Question',
                'name' => $faq->question,
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => $faq->answer],
            ])->all(),
        ];
    }

    protected function buildSpeakableSchema(Blog $blog): array
    {
        $selectors = $blog->geoMeta?->speakable_selectors ?: ['.post-title', '.post-summary'];

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'speakable' => [
                '@type' => 'SpeakableSpecification',
                'cssSelector' => $selectors,
            ],
            'url' => url('/blog/'.$blog->slug),
        ];
    }

    protected function upsert(Blog $blog, SchemaType $type, array $data): void
    {
        // Drop null values so the stored JSON-LD doesn't include empty fields.
        $data = $this->stripNulls($data);

        $blog->schemas()->updateOrCreate(
            ['type' => $type->value],
            ['data' => $data, 'is_active' => true]
        );
    }

    protected function stripNulls(array $data): array
    {
        return collect($data)
            ->map(fn ($value) => is_array($value) ? $this->stripNulls($value) : $value)
            ->filter(fn ($value) => $value !== null && $value !== [])
            ->all();
    }
}
