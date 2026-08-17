<?php

namespace App\Models;

use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Author extends Model
{
    use HasSlug, SoftDeletes;

    protected string $slugSource = 'name';

    protected $fillable = [
        'user_id', 'name', 'slug', 'photo', 'bio', 'designation',
        'experience_years', 'website', 'social_links', 'skills', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'social_links' => 'array',
            'skills' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function blogs(): HasMany
    {
        return $this->hasMany(Blog::class);
    }

    public function scopeActive(\Illuminate\Database\Eloquent\Builder $query): \Illuminate\Database\Eloquent\Builder
    {
        return $query->where('is_active', true);
    }

    public function publicUrlPrefix(): string
    {
        return '/authors';
    }
}
