<?php

use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BlogController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SearchController;
use App\Http\Controllers\Api\TagController;
use Illuminate\Support\Facades\Route;

// Public REST API. bootstrap/app.php's withRouting(api: ...) automatically
// applies the "api" middleware group (stateless, JSON error responses) and
// a 60-requests/minute throttle — no extra middleware needed here.
//
// Every response goes through an API Resource (app/Http/Resources), never
// a raw model — see BlogResource vs BlogDetailResource for why list and
// detail endpoints use different shapes.

Route::get('/health', fn () => response()->json(['status' => 'ok']));

Route::get('/blogs', [BlogController::class, 'index']);
Route::get('/blogs/{slug}', [BlogController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/tags', [TagController::class, 'index']);
Route::get('/authors', [AuthorController::class, 'index']);
Route::get('/search', [SearchController::class, 'index']);
Route::get('/latest', [BlogController::class, 'latest']);
Route::get('/trending', [BlogController::class, 'trending']);
Route::get('/featured', [BlogController::class, 'featured']);
Route::get('/related/{slug}', [BlogController::class, 'related']);
