<?php

use Illuminate\Support\Facades\Route;

// Public REST API (GET /api/blogs, /api/categories, etc.) is added in the
// API phase once BlogController/CategoryController and their Resources exist.

Route::get('/health', fn () => response()->json(['status' => 'ok']));
