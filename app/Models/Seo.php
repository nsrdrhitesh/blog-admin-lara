<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Seo extends Model
{
    protected $table = 'seos';

    protected $fillable = [
        'meta_title', 'meta_description', 'meta_keywords', 'canonical_url', 'robots',
        'focus_keyword', 'secondary_keywords',
        'og_title', 'og_description', 'og_image',
        'twitter_title', 'twitter_description', 'twitter_image', 'twitter_card',
        'seo_score', 'seo_suggestions',
    ];

    protected function casts(): array
    {
        return [
            'secondary_keywords' => 'array',
            'seo_suggestions' => 'array',
        ];
    }

    public function seoable(): MorphTo
    {
        return $this->morphTo();
    }
}
