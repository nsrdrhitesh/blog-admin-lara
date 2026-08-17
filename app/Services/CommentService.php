<?php

namespace App\Services;

use App\Enums\CommentStatus;
use App\Models\BlogComment;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CommentService
{
    /**
     * Filters: search, status, blog_id. Only top-level comments are listed
     * (replies show nested underneath) — matches the pattern used for the
     * public-facing thread view once one exists.
     */
    public function list(array $filters, int $perPage = 20): LengthAwarePaginator
    {
        return BlogComment::query()
            ->with(['blog:id,title,slug', 'user:id,name', 'allReplies.user:id,name'])
            ->whereNull('parent_id')
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('content', 'like', "%{$search}%"))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['blog_id'] ?? null, fn ($q, $id) => $q->where('blog_id', $id))
            ->orderByDesc('created_at')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function approve(BlogComment $comment): BlogComment
    {
        $comment->update(['status' => CommentStatus::Approved]);

        return $comment;
    }

    public function reject(BlogComment $comment): BlogComment
    {
        $comment->update(['status' => CommentStatus::Rejected]);

        return $comment;
    }

    public function markSpam(BlogComment $comment): BlogComment
    {
        $comment->update(['status' => CommentStatus::Spam]);

        return $comment;
    }

    public function delete(BlogComment $comment): bool
    {
        // Replies cascade via the FK's cascadeOnDelete (Phase 1 migration).
        return (bool) $comment->delete();
    }

    /**
     * Admin/editor reply — auto-approved since it's staff-authored, unlike
     * a visitor comment which starts pending.
     */
    public function reply(BlogComment $parent, string $content): BlogComment
    {
        return BlogComment::create([
            'blog_id' => $parent->blog_id,
            'parent_id' => $parent->id,
            'user_id' => Auth::id(),
            'content' => $content,
            'status' => CommentStatus::Approved,
        ]);
    }
}
