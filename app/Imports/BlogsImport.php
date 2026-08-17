<?php

namespace App\Imports;

use App\Enums\BlogStatus;
use App\Models\Author;
use App\Models\Blog;
use App\Models\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Validators\Failure;

/**
 * Bulk blog import from CSV/XLSX. Expects columns: title, content,
 * short_description (optional), category (matched by name, optional),
 * author (matched by name, optional), status (draft/published, defaults
 * to draft). Every imported row lands as a draft unless status is
 * explicitly "published" — never silently publishes content that hasn't
 * been reviewed. Row-level failures are collected rather than aborting
 * the whole file on the first bad row.
 */
class BlogsImport implements SkipsOnFailure, ToCollection, WithHeadingRow, WithValidation
{
    use Importable;

    public int $imported = 0;

    protected array $failures = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $row) {
            if (empty($row['title']) || empty($row['content'])) {
                continue; // caught by WithValidation before this runs, but stay defensive
            }

            $category = ! empty($row['category'])
                ? Category::where('name', $row['category'])->first()
                : null;

            $author = ! empty($row['author'])
                ? Author::where('name', $row['author'])->first()
                : null;

            $status = in_array($row['status'] ?? null, ['published', 'draft', 'archived'], true)
                ? $row['status']
                : BlogStatus::Draft->value;

            Blog::create([
                'title' => $row['title'],
                'content' => $row['content'],
                'short_description' => $row['short_description'] ?? null,
                'category_id' => $category?->id,
                'author_id' => $author?->id,
                'status' => $status,
                'published_at' => $status === BlogStatus::Published->value ? now() : null,
                'created_by' => auth()->id(),
                'updated_by' => auth()->id(),
            ]);

            $this->imported++;
        }
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string'],
            'status' => ['nullable', 'in:draft,published,archived'],
        ];
    }

    public function onFailure(Failure ...$failures): void
    {
        $this->failures = array_merge($this->failures, $failures);
    }

    public function failures(): array
    {
        return $this->failures;
    }
}
