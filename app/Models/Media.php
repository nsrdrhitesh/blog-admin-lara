<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Media extends Model
{
    protected $table = 'media';

    use SoftDeletes;

    protected $fillable = [
        'folder_id', 'uploaded_by', 'disk', 'path', 'webp_path', 'avif_path', 'thumbnail_path',
        'original_name', 'mime_type', 'file_size', 'width', 'height', 'hash',
        'alt_text', 'title', 'caption', 'credit', 'source_url', 'reuse_count',
    ];

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function getUrlAttribute(): ?string
    {
        return media_url($this->webp_path ?? $this->path);
    }
}
