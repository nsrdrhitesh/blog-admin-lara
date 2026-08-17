<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\TagResource;
use App\Models\Tag;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class TagController extends Controller
{
    /**
     * GET /api/tags
     */
    public function index(): JsonResponse
    {
        $tags = Cache::remember('api:tags', 300, function () {
            return Tag::active()->withCount('blogs')->orderBy('name')->get();
        });

        return TagResource::collection($tags)->response();
    }
}
