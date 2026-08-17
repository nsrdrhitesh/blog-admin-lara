<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Full single-post representation — GET /api/blogs/{slug} only. Adds
 * content, gallery/inline images, the SEO block, generated JSON-LD schema
 * (from Phase 6's SchemaGeneratorService, read straight off the persisted
 * `schemas` rows rather than regenerated per-request), FAQs, and the GEO
 * AI-summary fields. Every "Include X" line from the API spec lives here.
 */
class BlogDetailResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'featured_image' => media_url($this->featured_image),
            'banner_image' => media_url($this->banner_image),
            'reading_time_minutes' => $this->reading_time_minutes,
            'word_count' => $this->word_count,
            'language' => $this->language,
            'published_at' => $this->published_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'is_featured' => $this->is_featured,
            'is_trending' => $this->is_trending,
            'is_sticky' => $this->is_sticky,
            'allow_comments' => $this->allow_comments,
            'view_count' => $this->view_count,
            'like_count' => $this->like_count,
            'share_count' => $this->share_count,

            'category' => new CategoryResource($this->whenLoaded('category')),
            'sub_category' => new CategoryResource($this->whenLoaded('subCategory')),
            'author' => new AuthorResource($this->whenLoaded('author')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),

            'images' => BlogImageResource::collection($this->whenLoaded('images')),

            'seo' => $this->whenLoaded('seo', fn () => $this->seo ? [
                'meta_title' => $this->seo->meta_title,
                'meta_description' => $this->seo->meta_description,
                'meta_keywords' => $this->seo->meta_keywords,
                'canonical_url' => $this->seo->canonical_url,
                'robots' => $this->seo->robots,
                'focus_keyword' => $this->seo->focus_keyword,
                'secondary_keywords' => $this->seo->secondary_keywords,
                'og_title' => $this->seo->og_title,
                'og_description' => $this->seo->og_description,
                'og_image' => media_url($this->seo->og_image),
                'twitter_title' => $this->seo->twitter_title,
                'twitter_description' => $this->seo->twitter_description,
                'twitter_image' => media_url($this->seo->twitter_image),
                'twitter_card' => $this->seo->twitter_card,
            ] : null),

            // Keyed by schema type, e.g. {"article": {...}, "breadcrumb": {...}}
            'schema' => $this->whenLoaded('schemas', fn () => $this->schemas
                ->where('is_active', true)
                ->mapWithKeys(fn ($schema) => [$schema->type->value => $schema->data])
            ),

            'faqs' => FaqResource::collection($this->whenLoaded('faqs')),

            'ai_summary' => $this->whenLoaded('geoMeta', fn () => $this->geoMeta ? [
                'summary' => $this->geoMeta->ai_summary,
                'short_summary' => $this->geoMeta->short_ai_summary,
                'key_takeaways' => $this->geoMeta->key_takeaways,
                'highlights' => $this->geoMeta->highlights,
                'pros' => $this->geoMeta->pros,
                'cons' => $this->geoMeta->cons,
            ] : null),
        ];
    }
}
