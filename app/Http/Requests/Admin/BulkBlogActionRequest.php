<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkBlogActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // per-blog authorization happens in the controller against each id
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:blogs,id'],
            'action' => ['required', Rule::in(['publish', 'draft', 'archive', 'delete'])],
        ];
    }
}
