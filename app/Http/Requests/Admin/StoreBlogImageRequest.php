<?php

namespace App\Http\Requests\Admin;

use App\Enums\ImageType;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class StoreBlogImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('blog'));
    }

    public function rules(): array
    {
        return [
            'image' => ['required', 'image', 'max:'.config('media.max_upload_kb', 8192)],
            'image_type' => ['required', new Enum(ImageType::class)],
            'alt_text' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'credit' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:255'],
        ];
    }
}
