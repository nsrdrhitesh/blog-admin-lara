<?php

namespace App\DTOs;

/**
 * Immutable transfer object between StoreBlogRequest/UpdateBlogRequest and
 * BlogService — keeps the service's method signature stable even as the
 * form grows new fields (SEO/GEO panels land on top of this in later phases).
 */
final class BlogData
{
    public function __construct(
        public readonly string $title,
        public readonly ?string $slug,
        public readonly ?string $shortDescription,
        public readonly ?string $excerpt,
        public readonly string $content,
        public readonly ?int $categoryId,
        public readonly ?int $subCategoryId,
        public readonly ?int $authorId,
        public readonly string $status,
        public readonly ?string $publishedAt,
        public readonly ?string $scheduledAt,
        public readonly bool $isFeatured,
        public readonly bool $isTrending,
        public readonly bool $isSticky,
        public readonly bool $allowComments,
        public readonly string $language,
        public readonly ?string $country,
        public readonly ?string $region,
        public readonly ?string $city,
        public readonly array $tagIds,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            title: $data['title'],
            slug: $data['slug'] ?? null,
            shortDescription: $data['short_description'] ?? null,
            excerpt: $data['excerpt'] ?? null,
            content: $data['content'],
            categoryId: $data['category_id'] ?? null,
            subCategoryId: $data['sub_category_id'] ?? null,
            authorId: $data['author_id'] ?? null,
            status: $data['status'],
            publishedAt: $data['published_at'] ?? null,
            scheduledAt: $data['scheduled_at'] ?? null,
            isFeatured: (bool) ($data['is_featured'] ?? false),
            isTrending: (bool) ($data['is_trending'] ?? false),
            isSticky: (bool) ($data['is_sticky'] ?? false),
            allowComments: (bool) ($data['allow_comments'] ?? true),
            language: $data['language'] ?? 'en',
            country: $data['country'] ?? null,
            region: $data['region'] ?? null,
            city: $data['city'] ?? null,
            tagIds: $data['tag_ids'] ?? [],
        );
    }

    /**
     * Attributes mapped 1:1 onto the blogs table (everything except tags,
     * which is a separate pivot sync step).
     */
    public function toModelAttributes(): array
    {
        return [
            'title' => $this->title,
            'slug' => $this->slug,
            'short_description' => $this->shortDescription,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'category_id' => $this->categoryId,
            'sub_category_id' => $this->subCategoryId,
            'author_id' => $this->authorId,
            'status' => $this->status,
            'published_at' => $this->publishedAt,
            'scheduled_at' => $this->scheduledAt,
            'is_featured' => $this->isFeatured,
            'is_trending' => $this->isTrending,
            'is_sticky' => $this->isSticky,
            'allow_comments' => $this->allowComments,
            'language' => $this->language,
            'country' => $this->country,
            'region' => $this->region,
            'city' => $this->city,
        ];
    }
}
