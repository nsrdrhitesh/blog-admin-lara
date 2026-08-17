<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use App\Models\Blog;
use App\Models\Category;
use App\Models\Page;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Backs the "Search Everywhere" box in the admin topbar — a quick jump-to
 * tool across every content type, not a content-discovery feature for
 * visitors (that's Api\SearchController, blogs-only, public-facing).
 * Each result group is skipped entirely if the user lacks that type's
 * view permission, rather than showing results they can't open.
 */
class SearchController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = trim((string) $request->input('q'));

        if (mb_strlen($query) < 2) {
            return response()->json(['results' => []]);
        }

        $user = $request->user();
        $results = [];

        if ($user->hasPermissionTo('blogs.view')) {
            $results['Blogs'] = Blog::where('title', 'like', "%{$query}%")
                ->limit(5)->get()->map(fn ($b) => [
                    'label' => $b->title,
                    'url' => route('admin.blogs.edit', $b),
                ]);
        }

        if ($user->hasPermissionTo('pages.view')) {
            $results['Pages'] = Page::where('title', 'like', "%{$query}%")
                ->limit(5)->get()->map(fn ($p) => [
                    'label' => $p->title,
                    'url' => route('admin.pages.edit', $p),
                ]);
        }

        if ($user->hasPermissionTo('categories.view')) {
            $results['Categories'] = Category::where('name', 'like', "%{$query}%")
                ->limit(5)->get()->map(fn ($c) => [
                    'label' => $c->name,
                    'url' => route('admin.categories.edit', $c),
                ]);
        }

        if ($user->hasPermissionTo('authors.view')) {
            $results['Authors'] = Author::where('name', 'like', "%{$query}%")
                ->limit(5)->get()->map(fn ($a) => [
                    'label' => $a->name,
                    'url' => route('admin.authors.edit', $a),
                ]);
        }

        if ($user->hasPermissionTo('tags.view')) {
            $results['Tags'] = Tag::where('name', 'like', "%{$query}%")
                ->limit(5)->get()->map(fn ($t) => [
                    'label' => $t->name,
                    'url' => route('admin.tags.index'),
                ]);
        }

        // Drop empty groups so the frontend doesn't render empty section headers.
        $results = array_filter($results, fn ($group) => $group->isNotEmpty());

        return response()->json(['results' => $results]);
    }
}
