<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AuthorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'photo' => media_url($this->photo),
            'bio' => $this->bio,
            'designation' => $this->designation,
            'experience_years' => $this->experience_years,
            'website' => $this->website,
            'social_links' => $this->social_links,
            'skills' => $this->skills,
            'blogs_count' => $this->whenCounted('blogs'),
        ];
    }
}
