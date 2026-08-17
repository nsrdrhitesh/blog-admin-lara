<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ImageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreBlogImageRequest;
use App\Models\Blog;
use App\Models\BlogImage;
use App\Services\BlogImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * Manages the images that belong to one specific Blog post (featured,
 * banner, inline, gallery) — distinct from MediaController, which manages
 * the general-purpose Media Library. See BlogImageService for why.
 */
class BlogImageController extends Controller
{
    public function __construct(protected BlogImageService $images) {}

    public function store(StoreBlogImageRequest $request, Blog $blog): RedirectResponse|JsonResponse
    {
        $blogImage = $this->images->upload(
            $blog,
            $request->file('image'),
            ImageType::from($request->validated('image_type')),
            $request->only(['alt_text', 'title', 'caption', 'description', 'credit', 'source_url'])
        );

        if ($request->wantsJson()) {
            return response()->json(['image' => [
                'id' => $blogImage->id,
                'url' => $blogImage->url,
                'image_type' => $blogImage->image_type->value,
            ]]);
        }

        return back()->with('status', 'image-uploaded');
    }

    public function update(Request $request, Blog $blog, BlogImage $image): RedirectResponse
    {
        $this->authorize('update', $blog);

        $request->validate([
            'alt_text' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'caption' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'credit' => ['nullable', 'string', 'max:255'],
            'source_url' => ['nullable', 'url', 'max:255'],
            'lazy_load' => ['nullable', 'boolean'],
        ]);

        $this->images->updateMeta($image, $request->all());

        return back()->with('status', 'image-updated');
    }

    public function replace(Request $request, Blog $blog, BlogImage $image): RedirectResponse
    {
        $this->authorize('update', $blog);

        $request->validate(['image' => ['required', 'image', 'max:'.config('media.max_upload_kb', 8192)]]);

        $this->images->replace($image, $request->file('image'));

        return back()->with('status', 'image-replaced');
    }

    public function reorder(Request $request, Blog $blog): JsonResponse
    {
        $this->authorize('update', $blog);

        $request->validate(['ids' => ['required', 'array'], 'ids.*' => ['integer']]);

        $this->images->reorder($blog, $request->input('ids'));

        return response()->json(['status' => 'ok']);
    }

    public function destroy(Blog $blog, BlogImage $image): RedirectResponse
    {
        $this->authorize('update', $blog);

        $this->images->delete($image);

        return back()->with('status', 'image-deleted');
    }
}
