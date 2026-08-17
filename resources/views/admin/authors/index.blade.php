@extends('layouts.admin')

@section('title', 'Authors')
@section('breadcrumb', 'Authors')

@section('content')
    <div class="mb-5 flex items-center justify-between">
        <h1 class="text-xl font-semibold">Authors</h1>
        @can('create', \App\Models\Author::class)
            <a href="{{ route('admin.authors.create') }}" class="rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">
                + New Author
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

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @forelse ($authors as $author)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="flex items-center gap-3">
                    <img src="{{ $author->photo ? media_url($author->photo) : 'https://ui-avatars.com/api/?name='.urlencode($author->name) }}"
                         alt="" class="h-12 w-12 rounded-full object-cover">
                    <div>
                        <p class="font-medium">{{ $author->name }}</p>
                        <p class="text-xs text-slate-500">{{ $author->designation ?? '—' }}</p>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-500">{{ $author->blogs_count }} posts</p>
                <div class="mt-4 flex gap-3 text-sm">
                    <a href="{{ route('admin.authors.edit', $author) }}" class="text-brand-600 hover:underline">Edit</a>
                    @can('delete', $author)
                        <form method="POST" action="{{ route('admin.authors.destroy', $author) }}" onsubmit="return confirm('Delete this author?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-red-600 hover:underline">Delete</button>
                        </form>
                    @endcan
                </div>
            </div>
        @empty
            <p class="col-span-full py-10 text-center text-slate-400">No authors yet.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $authors->links() }}</div>
@endsection
