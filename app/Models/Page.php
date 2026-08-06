<?php

namespace App\Models;

use App\Traits\HasFaqs;
use App\Traits\HasGeoMeta;
use App\Traits\HasSchemas;
use App\Traits\HasSeo;
use App\Traits\HasSlug;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Page extends Model
{
    use HasFaqs, HasGeoMeta, HasSchemas, HasSeo, HasSlug, SoftDeletes;

    protected $fillable = ['title', 'slug', 'content', 'status', 'user_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function publicUrlPrefix(): string
    {
        return '/page';
    }
}
