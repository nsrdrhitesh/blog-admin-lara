<?php

namespace App\Traits;

use Illuminate\Support\Str;

/**
 * Generates a unique slug from a source attribute (default: title/name)
 * and records the previous slug in slug_histories so old URLs can 301 redirect.
 */
trait HasSlug
{
    public static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueSlug();
            }
        });

        static::updating(function ($model) {
            $sourceField = $model->slugSourceField();

            if ($model->isDirty('slug') && $model->getOriginal('slug')) {
                $model->recordSlugHistory($model->getOriginal('slug'));
            } elseif ($model->isDirty($sourceField) && ! $model->isDirty('slug')) {
                $model->slug = $model->generateUniqueSlug();
                $model->recordSlugHistory($model->getOriginal('slug'));
            }
        });
    }

    protected function slugSourceField(): string
    {
        return property_exists($this, 'slugSource') ? $this->slugSource : 'title';
    }

    protected function generateUniqueSlug(): string
    {
        $source = $this->{$this->slugSourceField()} ?? 'item';
        $base = Str::slug($source);
        $slug = $base;
        $i = 1;

        while (
            static::withTrashed()
                ->where('slug', $slug)
                ->when($this->exists, fn ($q) => $q->where('id', '!=', $this->id))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }

    protected function recordSlugHistory(string $oldSlug): void
    {
        if (! class_exists(\App\Models\SlugHistory::class)) {
            return;
        }

        \App\Models\SlugHistory::create([
            'sluggable_type' => static::class,
            'sluggable_id' => $this->id,
            'old_slug' => $oldSlug,
        ]);

        // Also register a redirect from the old public URL, if the model knows one.
        if (method_exists($this, 'publicUrlPrefix')) {
            \App\Models\Redirect::firstOrCreate(
                ['from_url' => $this->publicUrlPrefix().'/'.$oldSlug],
                ['to_url' => $this->publicUrlPrefix().'/'.$this->slug, 'status_code' => 301]
            );
        }
    }
}
