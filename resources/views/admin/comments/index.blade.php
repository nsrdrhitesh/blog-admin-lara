@extends('layouts.admin')

@section('title', 'Comments')
@section('breadcrumb', 'Comments')

@section('content')
    <h1 class="mb-5 text-xl font-semibold">Comments</h1>

    <form method="GET" class="mb-4 flex flex-wrap items-end gap-3 rounded-xl border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex-1 min-w-[200px]">
            <label class="mb-1 block text-xs font-medium text-slate-500">Search</label>
            <input type="text" name="search" value="{{ $filters['search'] ?? '' }}" class="w-full rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
        </div>
        <div>
            <label class="mb-1 block text-xs font-medium text-slate-500">Status</label>
            <select name="status" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm dark:border-slate-700 dark:bg-slate-800">
                <option value="">All</option>
                @foreach (['pending', 'approved', 'rejected', 'spam'] as $status)
                    <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>{{ ucfirst($status) }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-1.5 text-sm font-medium text-white hover:bg-slate-900 dark:bg-slate-700">Filter</button>
    </form>

    <div class="space-y-4">
        @forelse ($comments as $comment)
            <div class="rounded-xl border border-slate-200 bg-white p-5 shadow-sm dark:border-slate-800 dark:bg-slate-900">
                <div class="mb-2 flex items-center justify-between">
                    <div>
                        <span class="font-medium">{{ $comment->authorName() }}</span>
                        <span class="text-xs text-slate-400"> on </span>
                        <span class="text-sm text-slate-500">{{ $comment->blog?->title ?? 'Deleted post' }}</span>
                    </div>
                    @php
                        $badgeClasses = [
                            'pending' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                            'approved' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/10 dark:text-emerald-400',
                            'rejected' => 'bg-slate-100 text-slate-600 dark:bg-slate-700 dark:text-slate-300',
                            'spam' => 'bg-red-100 text-red-700 dark:bg-red-500/10 dark:text-red-400',
                        ];
                    @endphp
                    <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $badgeClasses[$comment->status->value] }}">
                        {{ ucfirst($comment->status->value) }}
                    </span>
                </div>

                <p class="mb-3 text-sm text-slate-700 dark:text-slate-300">{{ $comment->content }}</p>

                <div class="flex flex-wrap items-center gap-3 text-xs">
                    @can('approve', $comment)
                        @if ($comment->status->value !== 'approved')
                            <form method="POST" action="{{ route('admin.comments.approve', $comment) }}">@csrf @method('PUT')<button class="text-emerald-600 hover:underline">Approve</button></form>
                        @endif
                        @if ($comment->status->value !== 'rejected')
                            <form method="POST" action="{{ route('admin.comments.reject', $comment) }}">@csrf @method('PUT')<button class="text-slate-500 hover:underline">Reject</button></form>
                        @endif
                        @if ($comment->status->value !== 'spam')
                            <form method="POST" action="{{ route('admin.comments.spam', $comment) }}">@csrf @method('PUT')<button class="text-red-600 hover:underline">Mark spam</button></form>
                        @endif
                    @endcan
                    @can('delete', $comment)
                        <form method="POST" action="{{ route('admin.comments.destroy', $comment) }}" onsubmit="return confirm('Delete this comment and its replies?')">
                            @csrf @method('DELETE')<button class="text-red-600 hover:underline">Delete</button>
                        </form>
                    @endcan
                    @can('approve', $comment)
                        <button type="button" onclick="document.getElementById('reply-{{ $comment->id }}').classList.toggle('hidden')" class="text-brand-600 hover:underline">Reply</button>
                    @endcan
                </div>

                @can('approve', $comment)
                    <form id="reply-{{ $comment->id }}" method="POST" action="{{ route('admin.comments.reply', $comment) }}" class="mt-3 hidden">
                        @csrf
                        <textarea name="content" rows="2" required placeholder="Write a reply…" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm dark:border-slate-700 dark:bg-slate-800"></textarea>
                        <button type="submit" class="mt-2 rounded-lg bg-brand-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-brand-700">Post reply</button>
                    </form>
                @endcan

                {{-- Nested replies — shows every status (not just approved)
                     so moderators can act on pending/spam replies too. --}}
                @if ($comment->allReplies->isNotEmpty())
                    <div class="mt-4 space-y-3 border-l-2 border-slate-100 pl-4 dark:border-slate-800">
                        @foreach ($comment->allReplies as $reply)
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <span class="text-sm font-medium">{{ $reply->authorName() }}</span>
                                    <p class="text-sm text-slate-600 dark:text-slate-400">{{ $reply->content }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-2">
                                    <span class="rounded-full px-2 py-0.5 text-[10px] font-medium {{ $badgeClasses[$reply->status->value] }}">
                                        {{ ucfirst($reply->status->value) }}
                                    </span>
                                    @can('delete', $reply)
                                        <form method="POST" action="{{ route('admin.comments.destroy', $reply) }}" onsubmit="return confirm('Delete this reply?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="text-xs text-red-600 hover:underline">Delete</button>
                                        </form>
                                    @endcan
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        @empty
            <p class="py-16 text-center text-slate-400">No comments yet.</p>
        @endforelse
    </div>

    <div class="mt-4">{{ $comments->links() }}</div>
@endsection
