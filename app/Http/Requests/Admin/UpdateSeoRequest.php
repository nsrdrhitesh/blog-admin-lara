<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSeoRequest extends FormRequest
{
    protected $errorBag = 'seo';

    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('blog'));
    }

    public function rules(): array
    {
        return [
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:320'],
            'meta_keywords' => ['nullable', 'string', 'max:255'],
            'canonical_url' => ['nullable', 'url', 'max:255'],
            'robots' => ['nullable', 'string', 'max:100'],
            'focus_keyword' => ['nullable', 'string', 'max:255'],
            'secondary_keywords' => ['nullable', 'string'], // comma-separated, split in SeoController
            'og_title' => ['nullable', 'string', 'max:255'],
            'og_description' => ['nullable', 'string', 'max:320'],
            'og_image' => ['nullable', 'image', 'max:4096'],
            'twitter_title' => ['nullable', 'string', 'max:255'],
            'twitter_description' => ['nullable', 'string', 'max:320'],
            'twitter_image' => ['nullable', 'image', 'max:4096'],
            'twitter_card' => ['nullable', 'string', 'in:summary,summary_large_image'],
        ];
    }
}
