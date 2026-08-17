<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class AuthorController extends Controller
{
    /**
     * GET /api/authors
     */
    public function index(): JsonResponse
    {
        $authors = Cache::remember('api:authors', 300, function () {
            return Author::active()->withCount('blogs')->orderBy('name')->get();
        });

        return AuthorResource::collection($authors)->response();
    }
}
