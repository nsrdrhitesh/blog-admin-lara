<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreMenuItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->hasPermissionTo('menus.edit');
    }

    public function rules(): array
    {
        return [
            'parent_id' => ['nullable', 'exists:menu_items,id'],
            'label' => ['required', 'string', 'max:255'],
            'url' => ['nullable', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100'],
            'open_new_tab' => ['boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
        ];
    }
}
