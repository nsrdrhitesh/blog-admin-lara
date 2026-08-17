<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UploadMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Media::class);
    }

    public function rules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1'],
            'files.*' => [
                'required', 'image',
                'max:'.config('media.max_upload_kb', 8192),
                'mimes:'.implode(',', config('media.allowed_mimes')),
            ],
            'folder_id' => ['nullable', 'exists:media_folders,id'],
        ];
    }
}
