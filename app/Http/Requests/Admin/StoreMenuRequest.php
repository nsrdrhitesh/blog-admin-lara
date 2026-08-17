<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('menus.edit');
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255']];
    }
}
