<?php

use App\Http\Controllers\Admin\ProfileController;
use Illuminate\Support\Facades\Route;

// Admin panel routes, auto-loaded with the "admin." name prefix and "/admin"
// URI prefix by bootstrap/app.php. All routes here require an authenticated,
// active user — module-specific permission checks are added as each
// controller lands (see App\Http\Middleware\EnsureUserHasPermission).

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('admin.dashboard');
    })->name('dashboard');

    Route::get('profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
});
