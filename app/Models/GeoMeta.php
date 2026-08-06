<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class GeoMeta extends Model
{
    protected $table = 'geo_metas';

    protected $fillable = [
        'ai_summary', 'short_ai_summary', 'key_takeaways', 'highlights',
        'references', 'citation_urls', 'entities',
        'reviewer_author_id', 'reviewed_date', 'content_updated_date',
        'evidence_links', 'experience_signal', 'expertise_signal',
        'authority_signal', 'trust_signal', 'pros', 'cons',
        'speakable_enabled', 'speakable_selectors',
    ];

    protected function casts(): array
    {
        return [
            'key_takeaways' => 'array',
            'highlights' => 'array',
            'references' => 'array',
            'citation_urls' => 'array',
            'entities' => 'array',
            'evidence_links' => 'array',
            'pros' => 'array',
            'cons' => 'array',
            'reviewed_date' => 'date',
            'content_updated_date' => 'date',
            'speakable_enabled' => 'boolean',
            'speakable_selectors' => 'array',
        ];
    }

    public function geoable(): MorphTo
    {
        return $this->morphTo();
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Author::class, 'reviewer_author_id');
    }
}
