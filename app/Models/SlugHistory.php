<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SlugHistory extends Model
{
    protected $fillable = ['sluggable_type', 'sluggable_id', 'old_slug'];

    public function sluggable(): MorphTo
    {
        return $this->morphTo();
    }
}
