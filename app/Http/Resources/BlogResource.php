<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Lightweight blog representation for list endpoints (index, latest,
 * trending, featured, related, search) — no full content/SEO/schema, so a
 * listing page's JSON payload doesn't drag along every field only needed
 * for the single-post view. See BlogDetailResource for that.
 */
class BlogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->short_description,
            'excerpt' => $this->excerpt,
            'featured_image' => media_url($this->featured_image),
            'reading_time_minutes' => $this->reading_time_minutes,
            'published_at' => $this->published_at?->toIso8601String(),
            'is_featured' => $this->is_featured,
            'is_trending' => $this->is_trending,
            'is_sticky' => $this->is_sticky,
            'view_count' => $this->view_count,
            'like_count' => $this->like_count,
            'share_count' => $this->share_count,
            'category' => new CategoryResource($this->whenLoaded('category')),
            'author' => new AuthorResource($this->whenLoaded('author')),
            'tags' => TagResource::collection($this->whenLoaded('tags')),
        ];
    }
}
