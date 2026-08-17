@extends('layouts.admin')

@section('title', 'Pages')
@section('breadcrumb', 'Pages')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Pages</h1>
        @can('create', \App\Models\Page::class)
            <a href="{{ route('admin.pages.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                + New Page
            </a>
        @endcan
    </div>

    <form method="GET" class="mb-4 flex items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex-1">
            <label class="mb-1 block text-xs font-medium text-slate-500">Search</label>
            <input type="text" name="search" value="{{ request('search') }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
        </div>
        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-1.5 text-sm font-medium text-white hover:bg-slate-900 dark:bg-slate-700">Filter</button>
    </form>

    <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
        <table class="w-full text-left text-sm">
            <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-800/50">
                <tr>
                    <th class="px-4 py-3">Title</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Updated</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                @forelse ($pages as $page)
                    <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                        <td class="px-4 py-3 font-medium">{{ $page->title }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $page->status === 'published' ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-slate-100 text-slate-600 dark:bg-slate-700' }}">
                                {{ ucfirst($page->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $page->updated_at->format('M j, Y') }}</td>
                        <td class="px-4 py-3 text-right space-x-3">
                            <a href="{{ route('admin.pages.edit', $page) }}" class="text-brand-600 hover:underline">Edit</a>
                            @can('delete', $page)
                                <form method="POST" action="{{ route('admin.pages.destroy', $page) }}" class="inline" onsubmit="return confirm('Delete this page?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No pages yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $pages->links() }}</div>
@endsection
