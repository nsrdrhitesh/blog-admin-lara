@extends('layouts.admin')

@section('title', 'Dashboard')
@section('breadcrumb', 'Dashboard')

@section('content')
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        @foreach ([
            ['label' => 'Total Posts', 'value' => number_format($stats['total_posts']), 'href' => route('admin.blogs.index')],
            ['label' => 'Published', 'value' => number_format($stats['published']), 'href' => route('admin.blogs.index', ['status' => 'published'])],
            ['label' => 'Drafts', 'value' => number_format($stats['drafts']), 'href' => route('admin.blogs.index', ['status' => 'draft'])],
            ['label' => 'Comments Pending', 'value' => number_format($stats['comments_pending']), 'href' => route('admin.comments.index', ['status' => 'pending'])],
        ] as $stat)
            <a href="{{ $stat['href'] }}" class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-brand-600 dark:border-slate-800 dark:bg-slate-900">
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ $stat['label'] }}</p>
                <p class="mt-1 text-2xl font-semibold">{{ $stat['value'] }}</p>
            </a>
        @endforeach
    </div>

    <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        {{-- Recent posts --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900 lg:col-span-2">
            <h3 class="mb-4 text-sm font-semibold">Recent posts</h3>
            <div class="space-y-3">
                @forelse ($recentPosts as $post)
                    <a href="{{ route('admin.blogs.edit', $post) }}" class="flex items-center justify-between rounded-lg px-2 py-2 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <div>
                            <p class="text-sm font-medium">{{ $post->title }}</p>
                            <p class="text-xs text-slate-400">{{ $post->author?->name ?? 'Unassigned' }} · {{ $post->category?->name ?? 'Uncategorized' }} · {{ $post->created_at->diffForHumans() }}</p>
                        </div>
                        @php
                            $badgeClasses = [
                                'published' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                                'draft' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                                'scheduled' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                                'archived' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                            ];
                        @endphp
                        <span class="shrink-0 rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeClasses[$post->status->value] }}">{{ $post->status->label() }}</span>
                    </a>
                @empty
                    <p class="py-6 text-center text-sm text-slate-400">No posts yet — <a href="{{ route('admin.blogs.create') }}" class="text-brand-600 hover:underline">create your first one</a>.</p>
                @endforelse
            </div>
        </div>

        {{-- Top posts by views --}}
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
            <h3 class="mb-4 text-sm font-semibold">Top posts</h3>
            <div class="space-y-3">
                @forelse ($topPosts as $post)
                    <a href="{{ route('admin.blogs.edit', $post) }}" class="flex items-center justify-between rounded-lg px-2 py-2 hover:bg-slate-50 dark:hover:bg-slate-800/50">
                        <p class="truncate text-sm font-medium">{{ $post->title }}</p>
                        <span class="shrink-0 text-xs text-slate-400">{{ number_format($post->view_count) }} views</span>
                    </a>
                @empty
                    <p class="py-6 text-center text-sm text-slate-400">No published posts yet.</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Recent comments --}}
    <div class="mt-6 rounded-xl border border-slate-200 bg-white p-6 shadow-sm dark:border-slate-800 dark:bg-slate-900">
        <div class="mb-4 flex items-center justify-between">
            <h3 class="text-sm font-semibold">Recent comments</h3>
            <a href="{{ route('admin.comments.index') }}" class="text-xs text-brand-600 hover:underline">View all</a>
        </div>
        <div class="space-y-3">
            @forelse ($recentComments as $comment)
                <div class="flex items-start justify-between gap-4 rounded-lg px-2 py-2">
                    <div>
                        <p class="text-sm">
                            <span class="font-medium">{{ $comment->authorName() }}</span>
                            <span class="text-slate-400"> on {{ $comment->blog?->title ?? 'a deleted post' }}</span>
                        </p>
                        <p class="mt-0.5 truncate text-xs text-slate-500 dark:text-slate-400">{{ Str::limit($comment->content, 100) }}</p>
                    </div>
                    <span class="shrink-0 text-xs text-slate-400">{{ $comment->created_at->diffForHumans() }}</span>
                </div>
            @empty
                <p class="py-6 text-center text-sm text-slate-400">No comments yet.</p>
            @endforelse
        </div>
    </div>
@endsection
