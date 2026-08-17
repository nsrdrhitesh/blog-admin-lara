<?php

namespace App\Http\Requests\Admin;

use App\Enums\BlogStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

class UpdateBlogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('blog'));
    }

    public function rules(): array
    {
        $blogId = $this->route('blog')->id;

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'alpha_dash', Rule::unique('blogs', 'slug')->ignore($blogId)],
            'short_description' => ['nullable', 'string', 'max:500'],
            'excerpt' => ['nullable', 'string', 'max:1000'],
            'content' => ['required', 'string'],

            'category_id' => ['nullable', 'exists:categories,id'],
            'sub_category_id' => ['nullable', 'exists:categories,id', 'different:category_id'],
            'author_id' => ['nullable', 'exists:authors,id'],

            'status' => ['required', new Enum(BlogStatus::class)],
            'published_at' => ['nullable', 'date', 'required_if:status,'.BlogStatus::Published->value],
            'scheduled_at' => ['nullable', 'date', 'required_if:status,'.BlogStatus::Scheduled->value],

            'is_featured' => ['boolean'],
            'is_trending' => ['boolean'],
            'is_sticky' => ['boolean'],
            'allow_comments' => ['boolean'],

            'language' => ['nullable', 'string', 'max:10'],
            'country' => ['nullable', 'string', 'max:255'],
            'region' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],

            'tag_ids' => ['nullable', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
            'new_tags' => ['nullable', 'string'],

            'featured_image' => ['nullable', 'image', 'max:4096'],
        ];
    }
}
