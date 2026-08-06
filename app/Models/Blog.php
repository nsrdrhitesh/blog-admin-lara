<?php

namespace App\Models;

use App\Enums\BlogStatus;
use App\Traits\HasFaqs;
use App\Traits\HasGeoMeta;
use App\Traits\HasSchemas;
use App\Traits\HasSeo;
use App\Traits\HasSlug;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Blog extends Model
{
    use HasFaqs, HasGeoMeta, HasSchemas, HasSeo, HasSlug, LogsActivity, SoftDeletes;

    protected $fillable = [
        'title', 'slug', 'short_description', 'excerpt', 'content',
        'featured_image', 'banner_image',
        'category_id', 'sub_category_id', 'author_id', 'user_id',
        'status', 'published_at', 'scheduled_at',
        'is_featured', 'is_trending', 'is_sticky', 'allow_comments',
        'reading_time_minutes', 'word_count', 'language',
        'country', 'region', 'city', 'latitude', 'longitude',
        'view_count', 'like_count', 'share_count',
        'created_by', 'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => BlogStatus::class,
            'published_at' => 'datetime',
            'scheduled_at' => 'datetime',
            'is_featured' => 'boolean',
            'is_trending' => 'boolean',
            'is_sticky' => 'boolean',
            'allow_comments' => 'boolean',
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (Blog $blog) {
            if ($blog->isDirty('content')) {
                $blog->word_count = str_word_count(strip_tags($blog->content));
                $blog->reading_time_minutes = reading_time($blog->content);
            }

            if ($blog->isDirty('excerpt') === false && empty($blog->excerpt)) {
                $blog->excerpt = excerpt_from_html($blog->content, 200);
            }
        });
    }

    // Relationships

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function subCategory(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'sub_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(Author::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'blog_tag');
    }

    public function images(): HasMany
    {
        return $this->hasMany(BlogImage::class)->orderBy('sort_order');
    }

    public function gallery(): HasMany
    {
        return $this->hasMany(BlogGallery::class)->orderBy('sort_order');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BlogComment::class)->whereNull('parent_id')->orderByDesc('created_at');
    }

    public function allComments(): HasMany
    {
        return $this->hasMany(BlogComment::class);
    }

    public function views(): HasMany
    {
        return $this->hasMany(BlogView::class);
    }

    // Scopes

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', BlogStatus::Published)
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeTrending(Builder $query): Builder
    {
        return $query->where('is_trending', true);
    }

    public function publicUrlPrefix(): string
    {
        return '/blog';
    }
}
