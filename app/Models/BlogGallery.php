<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BlogGallery extends Model
{
    protected $table = 'blog_gallery';

    protected $fillable = ['blog_id', 'media_id', 'path', 'caption', 'sort_order'];

    public function blog(): BelongsTo
    {
        return $this->belongsTo(Blog::class);
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(Media::class);
    }
}
