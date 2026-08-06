<?php

namespace App\Models;

use App\Traits\HasSeo;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use HasSeo, HasSlug, SoftDeletes;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'description', 'icon', 'image',
        'meta_title', 'meta_description', 'sort_order', 'is_active',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id')->orderBy('sort_order');
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class, 'category_id');
    }

    public function publicUrlPrefix(): string
    {
        return '/category';
    }
}
