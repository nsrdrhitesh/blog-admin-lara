@extends('layouts.admin')

@php $editing = $page->exists; @endphp
@section('title', $editing ? 'Edit Page' : 'New Page')
@section('breadcrumb')
    <a href="{{ route('admin.pages.index') }}" class="hover:underline">Pages</a> / {{ $editing ? 'Edit' : 'New' }}
@endsection

@section('content')
<form method="POST" action="{{ $editing ? route('admin.pages.update', $page) : route('admin.pages.store') }}" class="grid grid-cols-1 gap-6 lg:grid-cols-3">
    @csrf
    @if ($editing) @method('PUT') @endif

    <div class="space-y-6 lg:col-span-2">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <div class="mb-4">
                <label class="mb-1 block text-sm font-medium">Title</label>
                <input type="text" name="title" value="{{ old('title', $page->title) }}" required class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                @error('title')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div class="mb-4">
                <label class="mb-1 block text-sm font-medium">Slug <span class="font-normal text-slate-400">(leave blank to auto-generate)</span></label>
                <input type="text" name="slug" value="{{ old('slug', $page->slug) }}" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                @error('slug')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-medium">Content</label>
                <textarea name="content" id="content-editor" rows="16" required class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-sm dark:border-slate-700 dark:bg-slate-800">{{ old('content', $page->content) }}</textarea>
                @error('content')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                <p class="mt-2 text-xs text-slate-400">Plain textarea for now — the TinyMCE integration from Phase 5 is wired to Blog posts specifically (its image upload needs a blog_id); hooking the same editor to Pages without image support is a small follow-up.</p>
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="mb-4 text-sm font-semibold">Publish</h3>
            <div class="mb-4">
                <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
                <select name="status" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                    <option value="draft" @selected(old('status', $page->status ?? 'draft') === 'draft')>Draft</option>
                    <option value="published" @selected(old('status', $page->status ?? 'draft') === 'published')>Published</option>
                </select>
            </div>
            <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-brand-700">
                {{ $editing ? 'Save changes' : 'Create page' }}
            </button>
        </div>

        <p class="text-xs text-slate-400">SEO/GEO/FAQ panels (from Phase 6) aren't wired onto Pages yet — the traits and backend services already support it (`Page` uses the same `HasSeo`/`HasGeoMeta`/`HasFaqs` traits as `Blog`), it's just the three Blade partials and routes that still need generalizing from blog-specific to polymorphic. Noted as a follow-up rather than silently left out.</p>

        @if ($editing)
            <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" onsubmit="return confirm('Delete this page?')">
                @csrf @method('DELETE')
                <button type="submit" class="w-full rounded-lg border border-red-300 px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950">
                    Delete page
                </button>
            </form>
        @endif
    </div>
</form>
@endsection
