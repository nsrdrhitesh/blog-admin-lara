@extends('layouts.admin')

@section('title', $menu->name)
@section('breadcrumb')
    <a href="{{ route('admin.menus.index') }}" class="hover:underline">Menus</a> / {{ $menu->name }}
@endsection

@section('content')
    <h1 class="mb-5 text-xl font-semibold">{{ $menu->name }} items</h1>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="mb-4 text-sm font-semibold">Add item</h3>
            <form method="POST" action="{{ route('admin.menus.items.store', $menu) }}" class="space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Label</label>
                    <input type="text" name="label" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">URL</label>
                    <input type="text" name="url" placeholder="/blog or https://…" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Parent item</label>
                    <select name="parent_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <option value="">— Top level —</option>
                        @foreach ($menu->items as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Sort order</label>
                    <input type="number" name="sort_order" value="0" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="open_new_tab" value="1"> Open in new tab</label>
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Add item</button>
            </form>
        </div>

        <div class="lg:col-span-2 space-y-3">
            @forelse ($menu->items as $item)
                <div class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="font-medium">{{ $item->label }}</p>
                            <p class="text-xs text-slate-400">{{ $item->url ?? '—' }} · order {{ $item->sort_order }}</p>
                        </div>
                        <form method="POST" action="{{ route('admin.menus.items.destroy', [$menu, $item]) }}" onsubmit="return confirm('Delete this item?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:underline">Delete</button>
                        </form>
                    </div>
                    @if ($item->children->isNotEmpty())
                        <div class="mt-3 space-y-2 border-l-2 border-slate-100 pl-4 dark:border-slate-800">
                            @foreach ($item->children as $child)
                                <div class="flex items-center justify-between">
                                    <p class="text-sm">{{ $child->label }} <span class="text-xs text-slate-400">{{ $child->url }}</span></p>
                                    <form method="POST" action="{{ route('admin.menus.items.destroy', [$menu, $child]) }}" onsubmit="return confirm('Delete this item?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-xs text-red-600 hover:underline">Delete</button>
                                    </form>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            @empty
                <p class="py-10 text-center text-slate-400">No items yet — add one from the form.</p>
            @endforelse
        </div>
    </div>
@endsection
