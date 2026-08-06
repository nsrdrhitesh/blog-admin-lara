<?php

namespace App\Models;

use App\Enums\ImageType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogImage extends Model
{
    protected $fillable = [
        'blog_id', 'path', 'thumbnail_path', 'medium_path', 'large_path',
        'original_path', 'webp_path', 'avif_path',
        'alt_text', 'title', 'caption', 'description', 'credit', 'source_url',
        'width', 'height', 'file_size', 'mime_type', 'hash',
        'image_type', 'sort_order', 'lazy_load', 'version',
    ];

    protected function casts(): array
    {
        return [
            'image_type' => ImageType::class,
            'lazy_load' => 'boolean',
        ];
    }

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    public function getUrlAttribute(): ?string
    {
        return media_url($this->webp_path ?? $this->path);
    }
}
