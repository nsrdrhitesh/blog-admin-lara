<?php

use App\Http\Controllers\Admin\AuthorController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BlogImageController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CommentController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\FaqController;
use App\Http\Controllers\Admin\GeoMetaController;
use App\Http\Controllers\Admin\MediaController;
use App\Http\Controllers\Admin\MediaFolderController;
use App\Http\Controllers\Admin\MenuController;
use App\Http\Controllers\Admin\PageController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\RedirectController;
use App\Http\Controllers\Admin\SearchController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TagController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

// Admin panel routes, auto-loaded with the "admin." name prefix and "/admin"
// URI prefix by bootstrap/app.php. All routes require an authenticated,
// active user; per-module authorization happens via Policies inside each
// controller (see App\Policies).

Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('search', [SearchController::class, 'index'])->name('search');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');

    Route::post('blogs/bulk', [BlogController::class, 'bulk'])->name('blogs.bulk');
    Route::get('blogs/export/{format}', [BlogController::class, 'export'])->name('blogs.export');
    Route::get('blogs/import-template', [BlogController::class, 'importTemplate'])->name('blogs.import-template');
    Route::post('blogs/import', [BlogController::class, 'import'])->name('blogs.import');
    Route::resource('blogs', BlogController::class)->except(['show']);

    // Per-post images (featured/banner/inline/gallery) — nested under the
    // owning blog since BlogImage rows carry per-post metadata.
    Route::prefix('blogs/{blog}/images')->name('blogs.images.')->group(function () {
        Route::post('/', [BlogImageController::class, 'store'])->name('store');
        Route::put('reorder', [BlogImageController::class, 'reorder'])->name('reorder');
        Route::put('{image}', [BlogImageController::class, 'update'])->name('update');
        Route::put('{image}/replace', [BlogImageController::class, 'replace'])->name('replace');
        Route::delete('{image}', [BlogImageController::class, 'destroy'])->name('destroy');
    });

    Route::resource('categories', CategoryController::class)->except(['show']);

    Route::resource('tags', TagController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('authors', AuthorController::class)->except(['show']);

    // SEO / GEO / FAQ panels — nested under the owning blog, one record each
    // (Seo and GeoMeta are polymorphic one-to-one; PUT upserts).
    Route::put('blogs/{blog}/seo', [SeoController::class, 'update'])->name('blogs.seo.update');
    Route::put('blogs/{blog}/geo', [GeoMetaController::class, 'update'])->name('blogs.geo.update');

    Route::prefix('blogs/{blog}/faqs')->name('blogs.faqs.')->group(function () {
        Route::post('/', [FaqController::class, 'store'])->name('store');
        Route::put('{faq}', [FaqController::class, 'update'])->name('update');
        Route::delete('{faq}', [FaqController::class, 'destroy'])->name('destroy');
    });

    // Redirect Manager
    Route::get('redirects', [RedirectController::class, 'index'])->name('redirects.index');
    Route::post('redirects', [RedirectController::class, 'store'])->name('redirects.store');
    Route::put('redirects/{redirect}', [RedirectController::class, 'update'])->name('redirects.update');
    Route::delete('redirects/{redirect}', [RedirectController::class, 'destroy'])->name('redirects.destroy');

    // Media Library
    Route::post('media/bulk-delete', [MediaController::class, 'bulkDestroy'])->name('media.bulk-delete');
    Route::put('media/{medium}/replace', [MediaController::class, 'replace'])->name('media.replace');
    Route::resource('media', MediaController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::post('media-folders', [MediaFolderController::class, 'store'])->name('media-folders.store');
    Route::delete('media-folders/{folder}', [MediaFolderController::class, 'destroy'])->name('media-folders.destroy');

    // Comments moderation
    Route::get('comments', [CommentController::class, 'index'])->name('comments.index');
    Route::put('comments/{comment}/approve', [CommentController::class, 'approve'])->name('comments.approve');
    Route::put('comments/{comment}/reject', [CommentController::class, 'reject'])->name('comments.reject');
    Route::put('comments/{comment}/spam', [CommentController::class, 'spam'])->name('comments.spam');
    Route::post('comments/{comment}/reply', [CommentController::class, 'reply'])->name('comments.reply');
    Route::delete('comments/{comment}', [CommentController::class, 'destroy'])->name('comments.destroy');

    // Settings (single grouped/tabbed screen backed by the flat settings table)
    Route::get('settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('settings', [SettingController::class, 'update'])->name('settings.update');

    // Menus
    Route::resource('menus', MenuController::class)->only(['index', 'store', 'destroy']);
    Route::get('menus/{menu}/edit', [MenuController::class, 'edit'])->name('menus.edit');
    Route::prefix('menus/{menu}/items')->name('menus.items.')->group(function () {
        Route::post('/', [MenuController::class, 'storeItem'])->name('store');
        Route::put('{item}', [MenuController::class, 'updateItem'])->name('update');
        Route::delete('{item}', [MenuController::class, 'destroyItem'])->name('destroy');
    });

    // Pages
    Route::resource('pages', PageController::class)->except(['show']);

    // Users
    Route::resource('users', UserController::class)->except(['show']);
});
