<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMediaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', \App\Models\Media::class); // media.upload permission gates edits too
    }

    public function rules(): array
    {
        return [
            'alt_text' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            'credit' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:255'],
            'folder_id' => ['nullable', 'exists:media_folders,id'],
        ];
    }
}
