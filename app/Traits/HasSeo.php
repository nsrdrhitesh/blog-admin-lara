<?php

namespace App\Traits;

use App\Models\Seo;

/**
 * Adds a polymorphic one-to-one SEO relation and a convenience accessor
 * that always returns an Seo instance (persisted or a fresh default one)
 * so Blade/API code never has to null-check.
 */
trait HasSeo
{
    public function seo()
    {
        return $this->morphOne(Seo::class, 'seoable');
    }

    public function seoOrNew(): Seo
    {
        return $this->seo ?: $this->seo()->make();
    }
}
