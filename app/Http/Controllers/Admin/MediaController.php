<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkMediaActionRequest;
use App\Http\Requests\Admin\UpdateMediaRequest;
use App\Http\Requests\Admin\UploadMediaRequest;
use App\Models\Media;
use App\Models\MediaFolder;
use App\Services\MediaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function __construct(protected MediaService $mediaService) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Media::class);

        return view('admin.media.index', [
            'media' => $this->mediaService->list(
                $request->only(['search', 'folder_id', 'mime_type']),
                (int) $request->input('per_page', 24)
            ),
            'folders' => MediaFolder::whereNull('parent_id')->with('children')->orderBy('name')->get(),
            'filters' => $request->only(['search', 'folder_id', 'mime_type']),
        ]);
    }

    /**
     * Drag-and-drop upload endpoint — accepts multiple files, returns JSON
     * so the frontend can append thumbnails without a full page reload.
     */
    public function store(UploadMediaRequest $request): JsonResponse
    {
        $uploaded = collect($request->file('files'))
            ->map(fn ($file) => $this->mediaService->upload($file, $request->input('folder_id')))
            ->map(fn (Media $media) => [
                'id' => $media->id,
                'url' => $media->url,
                'name' => $media->original_name,
            ]);

        return response()->json(['media' => $uploaded]);
    }

    public function update(UpdateMediaRequest $request, Media $medium): RedirectResponse
    {
        $this->mediaService->update($medium, $request->validated());

        return back()->with('status', 'media-updated');
    }

    public function replace(Request $request, Media $medium): RedirectResponse
    {
        $this->authorize('create', Media::class);

        $request->validate(['file' => ['required', 'image', 'max:'.config('media.max_upload_kb', 8192)]]);

        $this->mediaService->replace($medium, $request->file('file'));

        return back()->with('status', 'media-replaced');
    }

    public function destroy(Media $medium): RedirectResponse
    {
        $this->authorize('delete', $medium);

        $this->mediaService->delete($medium);

        return back()->with('status', 'media-deleted');
    }

    public function bulkDestroy(BulkMediaActionRequest $request): RedirectResponse
    {
        $this->mediaService->bulkDelete($request->validated('ids'));

        return back()->with('status', 'media-bulk-deleted');
    }
}
