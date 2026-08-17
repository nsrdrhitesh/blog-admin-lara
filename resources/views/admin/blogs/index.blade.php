@extends('layouts.admin')

@section('title', 'Blogs')
@section('breadcrumb', 'Blogs')

@section('content')
<div x-data="{ selected: [], allIds: [{{ $blogs->getCollection()->pluck('id')->implode(',') }}] }">

    <div class="mb-5 flex items-center justify-between" x-data="{ importOpen: false }">
        <h1 class="text-xl font-semibold">Blogs</h1>
        <div class="flex items-center gap-2">
            @can('viewAny', \App\Models\Blog::class)
                <div class="relative" x-data="{ exportOpen: false }">
                    <button type="button" @click="exportOpen = !exportOpen"
                            class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                        Export
                    </button>
                    <div x-show="exportOpen" @click.outside="exportOpen = false" x-transition x-cloak
                         class="absolute right-0 z-10 mt-2 w-40 rounded-lg border border-slate-200 bg-white py-1 text-sm shadow-lg dark:border-slate-800 dark:bg-slate-900">
                        <a href="{{ route('admin.blogs.export', array_merge(['format' => 'csv'], $filters ?? [])) }}" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">Export CSV</a>
                        <a href="{{ route('admin.blogs.export', array_merge(['format' => 'xlsx'], $filters ?? [])) }}" class="block px-4 py-2 hover:bg-slate-100 dark:hover:bg-slate-800">Export Excel</a>
                    </div>
                </div>
            @endcan
            @can('create', \App\Models\Blog::class)
                <button type="button" @click="importOpen = true" class="rounded-lg border border-slate-300 px-3 py-2 text-sm font-medium hover:bg-slate-50 dark:border-slate-700 dark:hover:bg-slate-800">
                    Import
                </button>
                <a href="{{ route('admin.blogs.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                    + New Post
                </a>
            @endcan
        </div>

        {{-- Import modal --}}
        <div x-show="importOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4">
            <div @click.outside="importOpen = false" class="w-full max-w-md rounded-xl bg-white p-6 dark:bg-slate-900">
                <h3 class="mb-1 text-sm font-semibold">Import posts</h3>
                <p class="mb-4 text-xs text-slate-400">
                    CSV or Excel with columns: title, content, short_description, category, author, status.
                    Every imported row lands as a draft unless status is explicitly "published".
                    <a href="{{ route('admin.blogs.import-template') }}" class="text-brand-600 hover:underline">Download a blank template</a>.
                </p>
                @error('file')<p class="mb-3 text-xs text-red-500">{{ $message }}</p>@enderror
                <form method="POST" action="{{ route('admin.blogs.import') }}" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="file" name="file" accept=".csv,.xlsx,.xls" required class="w-full text-sm">
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="importOpen = false" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700">Cancel</button>
                        <button type="submit" class="rounded-lg bg-brand-600 px-3 py-1.5 text-sm font-semibold text-white hover:bg-brand-700">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (session('status') === 'import-success')
        <div class="mb-4 rounded-lg bg-emerald-50 px-4 py-3 text-sm text-emerald-700 ring-1 ring-emerald-200 dark:bg-emerald-500/10 dark:text-emerald-400 dark:ring-emerald-500/30">
            Imported {{ session('imported_count') }} post(s) as drafts.
        </div>
    @endif

    {{-- Filters --}}
    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex-1 min-w-[200px]">
            <label class="mb-1 block text-xs font-medium text-slate-500">Search</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" placeholder="Title or slug…"
                   class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                <option value="">All</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->value }}" @selected(($filters['status'] ?? '') === $status->value)>{{ $status->label() }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Category</label>
            <select name="category_id" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                <option value="">All</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat->id }}" @selected(($filters['category_id'] ?? '') == $cat->id)>{{ $cat->name }}</option>
                    @foreach ($cat->children as $child)
                        <option value="{{ $child->id }}" @selected(($filters['category_id'] ?? '') == $child->id)>— {{ $child->name }}</option>
                    @endforeach
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-1.5 text-sm font-medium text-white hover:bg-slate-900 dark:bg-slate-700">
            Filter
        </button>
        @if (array_filter($filters ?? []))
            <a href="{{ route('admin.blogs.index') }}" class="text-sm text-slate-500 hover:underline">Clear</a>
        @endif
    </form>

    {{-- Bulk actions --}}
    <form method="POST" action="{{ route('admin.blogs.bulk') }}" id="bulk-form">
        @csrf
        <div class="mb-3 flex items-center gap-2" x-show="selected.length > 0" x-cloak>
            <span class="text-sm text-slate-500" x-text="selected.length + ' selected'"></span>
            <button type="submit" name="action" value="publish" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Publish</button>
            <button type="submit" name="action" value="draft" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Move to Draft</button>
            <button type="submit" name="action" value="archive" class="rounded-lg border border-slate-300 px-3 py-1 text-xs font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800">Archive</button>
            <button type="submit" name="action" value="delete" onclick="return confirm('Delete the selected posts? This cannot be undone.')"
                    class="rounded-lg border border-red-300 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950">Delete</button>
        </div>

        <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-800/50">
                    <tr>
                        <th class="w-10 px-4 py-3">
                            <input type="checkbox" @change="selected = $event.target.checked ? [...allIds] : []">
                        </th>
                        <th class="px-4 py-3">Title</th>
                        <th class="px-4 py-3">Category</th>
                        <th class="px-4 py-3">Author</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Comments</th>
                        <th class="px-4 py-3">Views</th>
                        <th class="px-4 py-3">Published</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                    @forelse ($blogs as $blog)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                            <td class="px-4 py-3">
                                <input type="checkbox" name="ids[]" value="{{ $blog->id }}" x-model="selected">
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.blogs.edit', $blog) }}" class="font-medium hover:text-brand-600">{{ $blog->title }}</a>
                                <div class="mt-0.5 flex gap-1.5">
                                    @if ($blog->is_featured)<span class="rounded bg-amber-100 px-1.5 py-0.5 text-[10px] font-medium text-amber-700 dark:bg-amber-500/10 dark:text-amber-400">Featured</span>@endif
                                    @if ($blog->is_trending)<span class="rounded bg-rose-100 px-1.5 py-0.5 text-[10px] font-medium text-rose-700 dark:bg-rose-500/10 dark:text-rose-400">Trending</span>@endif
                                    @if ($blog->is_sticky)<span class="rounded bg-blue-100 px-1.5 py-0.5 text-[10px] font-medium text-blue-700 dark:bg-blue-500/10 dark:text-blue-400">Sticky</span>@endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $blog->category?->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $blog->author?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @php
                                    $badgeClasses = [
                                        'published' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                                        'draft' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                                        'scheduled' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                                        'archived' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                                    ];
                                @endphp
                                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeClasses[$blog->status->value] }}">
                                    {{ $blog->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-slate-500">{{ $blog->comments_count }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ number_format($blog->view_count) }}</td>
                            <td class="px-4 py-3 text-slate-500">{{ $blog->published_at?->format('M j, Y') ?? '—' }}</td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('admin.blogs.edit', $blog) }}" class="text-brand-600 hover:underline">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="px-4 py-10 text-center text-slate-400">No posts yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </form>

    <div class="mt-4">{{ $blogs->links() }}</div>
</div>
@endsection
