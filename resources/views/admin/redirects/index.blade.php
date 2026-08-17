@extends('layouts.admin')

@section('title', 'Redirects')
@section('breadcrumb', 'Redirects')

@section('content')
    <h1 class="mb-5 text-xl font-semibold">Redirect Manager</h1>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="mb-4 text-sm font-semibold">Add a redirect</h3>
            <form method="POST" action="{{ route('admin.redirects.store') }}" class="space-y-3">
                @csrf
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">From (old path)</label>
                    <input type="text" name="from_url" placeholder="/old-post-slug" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                    @error('from_url')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">To (new URL or path)</label>
                    <input type="text" name="to_url" placeholder="/blog/new-post-slug" required
                           class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-slate-500">Type</label>
                    <select name="status_code" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800">
                        <option value="301">301 — Permanent</option>
                        <option value="302">302 — Temporary</option>
                        <option value="307">307 — Temporary (preserve method)</option>
                        <option value="308">308 — Permanent (preserve method)</option>
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" checked> Active</label>
                <button type="submit" class="w-full rounded-lg bg-brand-600 px-4 py-2 text-sm font-semibold text-white hover:bg-brand-700">Add redirect</button>
            </form>
            <p class="mt-3 text-xs text-slate-400">Renaming a post/category/tag/author's slug automatically creates a redirect here too — see <code>HasSlug</code>.</p>
        </div>

        <div class="lg:col-span-2">
            <form method="GET" class="mb-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search redirects…"
                       class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
            </form>

            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white dark:border-slate-800 dark:bg-slate-900">
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-slate-200 bg-slate-50 text-xs uppercase text-slate-500 dark:border-slate-800 dark:bg-slate-800/50">
                        <tr>
                            <th class="px-4 py-3">From</th>
                            <th class="px-4 py-3">To</th>
                            <th class="px-4 py-3">Type</th>
                            <th class="px-4 py-3">Hits</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                        @forelse ($redirects as $redirect)
                            <tr class="hover:bg-slate-50 dark:hover:bg-slate-800/40">
                                <td class="px-4 py-3 font-mono text-xs">{{ $redirect->from_url }}</td>
                                <td class="px-4 py-3 font-mono text-xs text-slate-500">{{ $redirect->to_url }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $redirect->status_code }}</td>
                                <td class="px-4 py-3 text-slate-500">{{ $redirect->hits }}</td>
                                <td class="px-4 py-3">
                                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $redirect->is_active ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400' : 'bg-slate-100 text-slate-500 dark:bg-slate-700' }}">
                                        {{ $redirect->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right">
                                    <form method="POST" action="{{ route('admin.redirects.destroy', $redirect) }}" class="inline" onsubmit="return confirm('Delete this redirect?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:underline">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">No redirects yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-4">{{ $redirects->links() }}</div>
        </div>
    </div>
@endsection
