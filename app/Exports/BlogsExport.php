<?php

namespace App\Exports;

use App\Repositories\Interfaces\BlogRepositoryInterface;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * Exports the (filtered) blog list — same filters as the admin Blogs index
 * screen, so "export what I'm currently looking at" does what it says.
 * Not paginated: an export is expected to return everything matching the
 * filter, not just the current page.
 */
class BlogsExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(
        protected BlogRepositoryInterface $blogs,
        protected array $filters = [],
    ) {}

    public function collection(): Collection
    {
        // paginate() with a very high per-page acts as "get everything
        // matching these filters" without adding a separate all() method
        // that would duplicate paginate()'s filter logic.
        return $this->blogs->paginate(100000, $this->filters)->getCollection();
    }

    public function headings(): array
    {
        return [
            'ID', 'Title', 'Slug', 'Status', 'Category', 'Author',
            'Published At', 'Views', 'Likes', 'Comments', 'Created At',
        ];
    }

    public function map($blog): array
    {
        return [
            $blog->id,
            $blog->title,
            $blog->slug,
            $blog->status->value,
            $blog->category?->name,
            $blog->author?->name,
            $blog->published_at?->format('Y-m-d H:i'),
            $blog->view_count,
            $blog->like_count,
            $blog->comments_count ?? $blog->allComments()->count(),
            $blog->created_at->format('Y-m-d H:i'),
        ];
    }
}
