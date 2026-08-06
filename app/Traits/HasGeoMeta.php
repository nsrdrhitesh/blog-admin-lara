<?php

namespace App\Traits;

use App\Models\GeoMeta;

/**
 * Generative Engine Optimization metadata relation (AI summaries, entities,
 * FAQs source data, E-E-A-T trust signals) — polymorphic one-to-one.
 */
trait HasGeoMeta
{
    public function geoMeta()
    {
        return $this->morphOne(GeoMeta::class, 'geoable');
    }

    public function geoMetaOrNew(): GeoMeta
    {
        return $this->geoMeta ?: $this->geoMeta()->make();
    }
}
