<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\Public\SitemapController;
use Illuminate\Support\Facades\Route;

// Public-facing blog listing/detail, categories, tags, and page routes still
// don't exist — this is an admin-only build so far. Sitemap/RSS/robots are
// the one public-facing surface that landed this phase, since search
// engines need them regardless of whether a public theme exists yet.

Route::get('/', function () {
    return view('welcome');
});

Route::get('sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('sitemap-images.xml', [SitemapController::class, 'images'])->name('sitemap.images');
Route::get('rss.xml', [SitemapController::class, 'rss'])->name('rss');
Route::get('robots.txt', [SitemapController::class, 'robots'])->name('robots');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');

    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
});
