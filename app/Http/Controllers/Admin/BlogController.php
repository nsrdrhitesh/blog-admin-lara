<?php

namespace App\Http\Controllers\Admin;

use App\DTOs\BlogData;
use App\Enums\BlogStatus;
use App\Exports\BlogsExport;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BulkBlogActionRequest;
use App\Http\Requests\Admin\ImportBlogsRequest;
use App\Http\Requests\Admin\StoreBlogRequest;
use App\Http\Requests\Admin\UpdateBlogRequest;
use App\Imports\BlogsImport;
use App\Models\Blog;
use App\Repositories\Interfaces\BlogRepositoryInterface;
use App\Services\AuthorService;
use App\Services\BlogService;
use App\Services\CategoryService;
use App\Services\TagService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

class BlogController extends Controller
{
    public function __construct(
        protected BlogService $blogs,
        protected CategoryService $categories,
        protected TagService $tags,
        protected AuthorService $authors,
    ) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Blog::class);

        $blogs = $this->blogs->list(
            $request->only(['search', 'status', 'category_id', 'author_id', 'is_featured', 'is_trending']),
            (int) $request->input('per_page', 20)
        );

        return view('admin.blogs.index', [
            'blogs' => $blogs,
            'categories' => $this->categories->tree(),
            'statuses' => BlogStatus::cases(),
            'filters' => $request->only(['search', 'status', 'category_id', 'author_id']),
        ]);
    }

    public function create(): View
    {
        $this->authorize('create', Blog::class);

        return view('admin.blogs.form', [
            'blog' => new Blog,
            'categories' => $this->categories->tree(),
            'authors' => $this->authors->list([], 200),
            'tags' => $this->tags->list([], 200),
            'statuses' => BlogStatus::cases(),
        ]);
    }

    public function store(StoreBlogRequest $request): RedirectResponse
    {
        $data = $this->buildBlogData($request);

        $blog = $this->blogs->create($data);

        if ($request->hasFile('featured_image')) {
            $blog->update(['featured_image' => $request->file('featured_image')->store('blogs/'.date('Y/m'), 'public')]);
        }

        return redirect()->route('admin.blogs.edit', $blog)->with('status', 'blog-created');
    }

    public function edit(Blog $blog): View
    {
        $this->authorize('update', $blog);

        return view('admin.blogs.form', [
            'blog' => $blog->load(['tags', 'images', 'seo', 'geoMeta', 'faqs']),
            'categories' => $this->categories->tree(),
            'authors' => $this->authors->list([], 200),
            'tags' => $this->tags->list([], 200),
            'statuses' => BlogStatus::cases(),
        ]);
    }

    public function update(UpdateBlogRequest $request, Blog $blog): RedirectResponse
    {
        $data = $this->buildBlogData($request);

        $blog = $this->blogs->update($blog, $data);

        if ($request->hasFile('featured_image')) {
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }
            $blog->update(['featured_image' => $request->file('featured_image')->store('blogs/'.date('Y/m'), 'public')]);
        }

        return redirect()->route('admin.blogs.edit', $blog)->with('status', 'blog-updated');
    }

    public function destroy(Blog $blog): RedirectResponse
    {
        $this->authorize('delete', $blog);

        if ($blog->featured_image) {
            Storage::disk('public')->delete($blog->featured_image);
        }

        $this->blogs->delete($blog);

        return redirect()->route('admin.blogs.index')->with('status', 'blog-deleted');
    }

    public function bulk(BulkBlogActionRequest $request): RedirectResponse
    {
        $ids = $request->validated('ids');
        $action = $request->validated('action');

        // Authorize against every targeted blog individually so an Author
        // can't bulk-publish or bulk-delete someone else's posts.
        $blogs = Blog::whereIn('id', $ids)->get();
        foreach ($blogs as $blog) {
            $this->authorize($action === 'delete' ? 'delete' : 'update', $blog);
        }

        match ($action) {
            'publish' => $this->blogs->bulkUpdateStatus($ids, BlogStatus::Published),
            'draft' => $this->blogs->bulkUpdateStatus($ids, BlogStatus::Draft),
            'archive' => $this->blogs->bulkUpdateStatus($ids, BlogStatus::Archived),
            'delete' => $this->blogs->bulkDelete($ids),
        };

        return back()->with('status', 'bulk-'.$action);
    }

    /**
     * GET /admin/blogs/export/{format} — exports the currently filtered
     * list (same query params as index()), not paginated.
     */
    public function export(Request $request, string $format): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $this->authorize('viewAny', Blog::class);

        abort_unless(in_array($format, ['csv', 'xlsx'], true), 404);

        $filters = $request->only(['search', 'status', 'category_id', 'author_id', 'is_featured', 'is_trending']);
        $export = new BlogsExport(app(BlogRepositoryInterface::class), $filters);

        $filename = 'blogs-'.now()->format('Y-m-d').'.'.$format;

        return Excel::download($export, $filename, $format === 'csv' ? \Maatwebsite\Excel\Excel::CSV : \Maatwebsite\Excel\Excel::XLSX);
    }

    /**
     * GET /admin/blogs/import-template — a blank CSV with the exact
     * headers BlogsImport expects, so "download template, fill it in,
     * upload it back" actually works without guessing column names.
     */
    public function importTemplate(): \Symfony\Component\HttpFoundation\Response
    {
        $this->authorize('create', Blog::class);

        $headers = "title,content,short_description,category,author,status\n";

        return response($headers, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="blog-import-template.csv"',
        ]);
    }

    public function import(ImportBlogsRequest $request): RedirectResponse
    {
        $import = new BlogsImport;
        Excel::import($import, $request->file('file'));

        $failureCount = count($import->failures());

        if ($failureCount > 0) {
            $messages = collect($import->failures())
                ->map(fn ($f) => "Row {$f->row()}: ".implode(', ', $f->errors()))
                ->take(10)
                ->implode(' | ');

            return back()->with('status', "import-partial")->withErrors([
                'file' => "Imported {$import->imported}, skipped {$failureCount}. {$messages}",
            ]);
        }

        return back()->with('status', 'import-success')->with('imported_count', $import->imported);
    }

    protected function buildBlogData(Request $request): BlogData
    {
        $payload = $request->validated();

        $tagIds = $payload['tag_ids'] ?? [];

        if (! empty($payload['new_tags'])) {
            $names = array_map('trim', explode(',', $payload['new_tags']));
            $tagIds = array_merge(
                $tagIds,
                app(\App\Repositories\Interfaces\TagRepositoryInterface::class)->findOrCreateByNames($names)
            );
        }

        return BlogData::fromArray([...$payload, 'tag_ids' => array_unique($tagIds)]);
    }
}
