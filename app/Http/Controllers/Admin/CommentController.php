<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogComment;
use App\Services\CommentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CommentController extends Controller
{
    public function __construct(protected CommentService $comments) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', BlogComment::class);

        return view('admin.comments.index', [
            'comments' => $this->comments->list($request->only(['search', 'status', 'blog_id']), 20),
            'filters' => $request->only(['search', 'status', 'blog_id']),
        ]);
    }

    public function approve(BlogComment $comment): RedirectResponse
    {
        $this->authorize('approve', $comment);

        $this->comments->approve($comment);

        return back()->with('status', 'comment-approved');
    }

    public function reject(BlogComment $comment): RedirectResponse
    {
        $this->authorize('approve', $comment);

        $this->comments->reject($comment);

        return back()->with('status', 'comment-rejected');
    }

    public function spam(BlogComment $comment): RedirectResponse
    {
        $this->authorize('approve', $comment);

        $this->comments->markSpam($comment);

        return back()->with('status', 'comment-marked-spam');
    }

    public function reply(Request $request, BlogComment $comment): RedirectResponse
    {
        $this->authorize('approve', $comment);

        $request->validate(['content' => ['required', 'string', 'max:2000']]);

        $this->comments->reply($comment, $request->input('content'));

        return back()->with('status', 'comment-replied');
    }

    public function destroy(BlogComment $comment): RedirectResponse
    {
        $this->authorize('delete', $comment);

        $this->comments->delete($comment);

        return back()->with('status', 'comment-deleted');
    }
}
