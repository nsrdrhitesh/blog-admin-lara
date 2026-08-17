@extends('layouts.admin')

@section('title', 'Menus')
@section('breadcrumb', 'Menus')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Menus</h1>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="mb-4 text-sm font-semibold">Create a menu</h3>
            <form method="POST" action="{{ route('admin.menus.store') }}" class="space-y-3">
                @csrf
                <input type="text" name="name" placeholder="e.g. Header, Footer" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                @error('name')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Create menu</button>
            </form>
        </div>

        <div class="lg:col-span-2">
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Items</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($menus as $menu)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 font-medium">{{ $menu->name }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $menu->items_count }}</td>
                                <td class="px-4 py-3 text-right space-x-3">
                                    <a href="{{ route('admin.menus.edit', $menu) }}" class="text-brand-600 hover:underline">Manage items</a>
                                    <form method="POST" action="{{ route('admin.menus.destroy', $menu) }}" class="inline" onsubmit="return confirm('Delete this menu and all its items?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-4 py-10 text-center text-slate-400">No menus yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
