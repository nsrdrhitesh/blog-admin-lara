@extends('layouts.admin')

@section('title', 'Tags')
@section('breadcrumb', 'Tags')

@section('content')
    <h1 class="mb-5 text-xl font-semibold">Tags</h1>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Quick add --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="mb-4 text-sm font-semibold">Add a tag</h3>
            <form method="POST" action="{{ route('admin.tags.store') }}" class="space-y-3">
                @csrf
                <input type="text" name="name" placeholder="Tag name" required
                       class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                @error('name')<p class="text-xs text-red-500">{{ $message }}</p>@enderror
                <input type="color" name="color" value="#4f46e5" class="h-9 w-16 rounded border border-slate-300 dark:border-slate-700">
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Add tag</button>
            </form>
        </div>

        {{-- List --}}
        <div class="lg:col-span-2">
            <div class="mb-4">
                <form method="GET">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search tags…"
                           class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                </form>
            </div>

            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3">Name</th>
                            <th class="px-4 py-3">Posts</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($tags as $tag)
                            @php $formId = 'tag-form-'.$tag->id; @endphp
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-2">
                                    <input type="text" name="name" form="{{ $formId }}" value="{{ $tag->name }}"
                                           class="w-full rounded border-0 bg-transparent px-1 py-1 text-sm focus:bg-white focus:ring-1 focus:ring-brand-600 dark:focus:bg-slate-800">
                                </td>
                                <td class="px-4 py-2 text-slate-500">{{ $tag->blogs_count }}</td>
                                <td class="px-4 py-2">
                                    <label class="inline-flex items-center gap-1.5 text-xs">
                                        <input type="checkbox" name="is_active" form="{{ $formId }}" value="1" @checked($tag->is_active)> Active
                                    </label>
                                </td>
                                <td class="px-4 py-2 text-right">
                                    <button type="submit" form="{{ $formId }}" class="text-brand-600 hover:underline">Save</button>
                                    @can('delete', $tag)
                                        <form method="POST" action="{{ route('admin.tags.destroy', $tag) }}" class="inline" onsubmit="return confirm('Delete this tag?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="ml-3 text-red-600 hover:underline">Delete</button>
                                        </form>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="px-4 py-10 text-center text-slate-400">No tags yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- One lightweight form per row, referenced by the inputs above via
                 the form="" attribute — keeps the table itself valid HTML. --}}
            @foreach ($tags as $tag)
                <form id="tag-form-{{ $tag->id }}" method="POST" action="{{ route('admin.tags.update', $tag) }}" class="hidden">
                    @csrf @method('PUT')
                </form>
            @endforeach

            <div class="mt-4">{{ $tags->links() }}</div>
        </div>
    </div>
@endsection
