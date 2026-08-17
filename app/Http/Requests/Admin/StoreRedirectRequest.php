<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRedirectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('settings.edit');
    }

    public function rules(): array
    {
        return [
            'from_url' => ['required', 'string', 'max:255', 'starts_with:/', Rule::unique('redirects', 'from_url')],
            'to_url' => ['required', 'string', 'max:255'],
            'status_code' => ['required', 'integer', Rule::in([301, 302, 307, 308])],
            'is_active' => ['boolean'],
        ];
    }
}
