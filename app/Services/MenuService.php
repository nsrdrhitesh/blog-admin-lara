<?php

namespace App\Services;

use App\Models\Menu;
use App\Models\MenuItem;
use Illuminate\Support\Str;

class MenuService
{
    public function list()
    {
        return Menu::withCount('items')->orderBy('name')->get();
    }

    public function create(string $name): Menu
    {
        return Menu::create(['name' => $name, 'slug' => Str::slug($name)]);
    }

    public function delete(Menu $menu): bool
    {
        return (bool) $menu->delete(); // items cascade via FK
    }

    public function addItem(Menu $menu, array $attributes): MenuItem
    {
        $attributes['sort_order'] ??= $menu->items()->max('sort_order') + 1;

        return $menu->items()->create($attributes);
    }

    public function updateItem(MenuItem $item, array $attributes): MenuItem
    {
        $item->update($attributes);

        return $item->fresh();
    }

    public function deleteItem(MenuItem $item): bool
    {
        return (bool) $item->delete(); // children cascade via FK
    }
}
