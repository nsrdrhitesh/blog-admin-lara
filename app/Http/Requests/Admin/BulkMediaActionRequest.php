<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BulkMediaActionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Bulk delete has no single Media instance to authorize against —
        // check the underlying permission directly rather than passing a
        // class-string into a policy method typed for a model instance.
        return $this->user()->hasPermissionTo('media.delete');
    }

    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'exists:media,id'],
        ];
    }
}
