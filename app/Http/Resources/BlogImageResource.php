<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogImageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'url' => $this->url,
            'thumbnail_url' => media_url($this->thumbnail_path),
            'medium_url' => media_url($this->medium_path),
            'large_url' => media_url($this->large_path),
            'alt_text' => $this->alt_text,
            'caption' => $this->caption,
            'credit' => $this->credit,
            'source_url' => $this->source_url,
            'width' => $this->width,
            'height' => $this->height,
            'image_type' => $this->image_type?->value,
        ];
    }
}
