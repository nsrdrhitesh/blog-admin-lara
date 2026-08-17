<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreMediaFolderRequest;
use App\Models\MediaFolder;
use App\Services\MediaService;
use Illuminate\Http\RedirectResponse;

class MediaFolderController extends Controller
{
    public function __construct(protected MediaService $mediaService) {}

    public function store(StoreMediaFolderRequest $request): RedirectResponse
    {
        $this->mediaService->createFolder($request->validated('parent_id'), $request->validated('name'));

        return back()->with('status', 'folder-created');
    }

    public function destroy(MediaFolder $folder): RedirectResponse
    {
        // No single Media instance to authorize against here either —
        // same reasoning as BulkMediaActionRequest.
        if (! auth()->user()->hasPermissionTo('media.delete')) {
            abort(403);
        }

        $this->mediaService->deleteFolder($folder);

        return back()->with('status', 'folder-deleted');
    }
}
