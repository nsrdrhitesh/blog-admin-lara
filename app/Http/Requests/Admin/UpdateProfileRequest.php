<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProfileRequest extends FormRequest
{
    /**
     * Named error bag so the profile page can show this form's errors
     * separately from the change-password form on the same page.
     */
    protected $errorBag = 'updateProfile';

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($this->user()->id)],
            'avatar' => ['nullable', 'image', 'max:2048'],
        ];
    }
}
