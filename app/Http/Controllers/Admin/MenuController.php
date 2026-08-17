<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMenuItemRequest;
use App\Http\Requests\Admin\StoreMenuRequest;
use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\MenuService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MenuController extends Controller
{
    public function __construct(protected MenuService $menus) {}

    public function index(Request $request): View
    {
        if (! $request->user()->hasPermissionTo('menus.view')) {
            abort(403);
        }

        return view('admin.menus.index', ['menus' => $this->menus->list()]);
    }

    public function store(StoreMenuRequest $request): RedirectResponse
    {
        $this->menus->create($request->validated('name'));

        return back()->with('status', 'menu-created');
    }

    public function destroy(Request $request, Menu $menu): RedirectResponse
    {
        if (! $request->user()->hasPermissionTo('menus.edit')) {
            abort(403);
        }

        $this->menus->delete($menu);

        return redirect()->route('admin.menus.index')->with('status', 'menu-deleted');
    }

    public function edit(Request $request, Menu $menu): View
    {
        if (! $request->user()->hasPermissionTo('menus.view')) {
            abort(403);
        }

        return view('admin.menus.edit', [
            'menu' => $menu->load('items.children'),
        ]);
    }

    public function storeItem(StoreMenuItemRequest $request, Menu $menu): RedirectResponse
    {
        $this->menus->addItem($menu, $request->validated());

        return back()->with('status', 'menu-item-created');
    }

    public function updateItem(StoreMenuItemRequest $request, Menu $menu, MenuItem $item): RedirectResponse
    {
        $this->menus->updateItem($item, $request->validated());

        return back()->with('status', 'menu-item-updated');
    }

    public function destroyItem(Request $request, Menu $menu, MenuItem $item): RedirectResponse
    {
        if (! $request->user()->hasPermissionTo('menus.edit')) {
            abort(403);
        }

        $this->menus->deleteItem($item);

        return back()->with('status', 'menu-item-deleted');
    }
}
