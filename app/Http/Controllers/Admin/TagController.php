<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreTagRequest;
use App\Http\Requests\Admin\UpdateTagRequest;
use App\Models\Tag;
use App\Services\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TagController extends Controller
{
    public function __construct(protected TagService $tags) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Tag::class);

        return view('admin.tags.index', [
            'tags' => $this->tags->list($request->only('search'), (int) $request->input('per_page', 30)),
        ]);
    }

    public function store(StoreTagRequest $request): RedirectResponse
    {
        $this->tags->create($request->validated());

        return back()->with('status', 'tag-created');
    }

    public function update(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $this->tags->update($tag, $request->validated());

        return back()->with('status', 'tag-updated');
    }

    public function destroy(Tag $tag): RedirectResponse
    {
        $this->authorize('delete', $tag);

        $this->tags->delete($tag);

        return back()->with('status', 'tag-deleted');
    }
}
