@extends('layouts.admin')

@php $editing = $category->exists; @endphp
@section('title', $editing ? 'Edit Category' : 'New Category')
@section('breadcrumb')
    <a href="{{ route('admin.categories.index') }}" class="hover:underline">Categories</a> / {{ $editing ? 'Edit' : 'New' }}
@endsection

@section('content')
<form method="POST" action="{{ $editing ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data" class="max-w-2xl space-y-6">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium">Name</label>
            <input type="text" name="name" value="{{ old('name', $category->name) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            @error('name')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium">Slug <span class="font-normal text-slate-400">(leave blank to auto-generate)</span></label>
            <input type="text" name="slug" value="{{ old('slug', $category->slug) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            @error('slug')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium">Parent category</label>
            <select name="parent_id" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                <option value="">— None (top-level) —</option>
                @foreach ($tree as $parent)
                    @if (! $editing || $parent->id !== $category->id)
                        <option value="{{ $parent->id }}" @selected(old('parent_id', $category->parent_id) == $parent->id)>{{ $parent->name }}</option>
                    @endif
                @endforeach
            </select>
            @error('parent_id')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium">Description</label>
            <textarea name="description" rows="3" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('description', $category->description) }}</textarea>
        </div>

        <div class="mb-4 grid grid-cols-2 gap-4">
            <div>
                <label class="mb-1 block text-sm font-medium">Icon <span class="font-normal text-slate-400">(icon class/name)</span></label>
                <input type="text" name="icon" value="{{ old('icon', $category->icon) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Sort order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $category->sort_order ?? 0) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
            </div>
        </div>

        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium">Image</label>
            @if ($category->image)
                <img src="{{ media_url($category->image) }}" alt="" class="mb-2 h-24 w-24 rounded-lg object-cover">
            @endif
            <input type="file" name="image" accept="image/*" class="w-full text-sm">
        </div>

        <label class="flex items-center gap-2 text-sm">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true))> Active
        </label>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <h3 class="mb-4 text-sm font-semibold">SEO basics</h3>
        <div class="mb-4">
            <label class="mb-1 block text-sm font-medium">Meta title</label>
            <input type="text" name="meta_title" value="{{ old('meta_title', $category->meta_title) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
        </div>
        <div>
            <label class="mb-1 block text-sm font-medium">Meta description</label>
            <textarea name="meta_description" rows="2" maxlength="320" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('meta_description', $category->meta_description) }}</textarea>
        </div>
        <p class="mt-2 text-xs text-slate-400">Full SEO/GEO panel (Open Graph, schema, focus keyword) lands with the dedicated SEO module.</p>
    </div>

    <button type="submit" class="rounded-lg bg-brand-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
        {{ $editing ? 'Save changes' : 'Create category' }}
    </button>
</form>
@endsection
