<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Repositories\Interfaces\BlogRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    public function __construct(protected BlogRepositoryInterface $blogs) {}

    /**
     * GET /api/search?q=... — not cached (query-dependent, low individual
     * hit rate makes caching not worth the complexity here, unlike the
     * fixed latest/trending/featured lists).
     */
    public function index(Request $request): JsonResponse
    {
        $request->validate(['q' => ['required', 'string', 'min:2', 'max:255']]);

        $limit = min((int) $request->input('limit', 20), 50);

        $results = $this->blogs->search($request->input('q'), $limit);

        return BlogResource::collection($results)->additional([
            'meta' => ['query' => $request->input('q'), 'count' => $results->count()],
        ])->response();
    }
}
