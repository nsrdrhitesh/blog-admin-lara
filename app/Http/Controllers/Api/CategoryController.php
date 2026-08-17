<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class CategoryController extends Controller
{
    /**
     * GET /api/categories — active categories, top-level with children nested
     * one level (matches the admin tree used for the blog form's category picker).
     */
    public function index(): JsonResponse
    {
        $categories = Cache::remember('api:categories', 300, function () {
            return Category::active()
                ->whereNull('parent_id')
                ->withCount('blogs')
                ->with(['children' => fn ($q) => $q->active()->withCount('blogs')])
                ->orderBy('sort_order')
                ->get();
        });

        return CategoryResource::collection($categories)->additional([
            'meta' => [],
        ])->response();
    }
}
