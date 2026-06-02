<?php

use App\Http\Controllers\Web\AdminAuthController;
use App\Http\Controllers\Web\AdminDashboardController;
use App\Http\Controllers\Web\PublicAdController;
use App\Http\Controllers\Web\UserPhoneAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicAdController::class, 'index'])->name('home');
Route::get('/login', [UserPhoneAuthController::class, 'showLogin'])->name('user.login.form');
Route::post('/login', [UserPhoneAuthController::class, 'login'])->name('user.login.submit');
Route::post('/logout', [UserPhoneAuthController::class, 'logout'])->name('user.logout');

Route::middleware('user.auth')->group(function (): void {
    Route::get('/ads/create', [PublicAdController::class, 'create'])->name('ads.create');
    Route::post('/ads', [PublicAdController::class, 'store'])->name('ads.store');
});

Route::get('/ads/{advertisement}', [PublicAdController::class, 'show'])->name('ads.show');
Route::post('/ads/{advertisement}/like', [PublicAdController::class, 'like'])->name('ads.like');

Route::view('/about', 'pages.about')->name('pages.about');
Route::view('/privacy-policy', 'pages.privacy')->name('pages.privacy');
Route::view('/terms-and-conditions', 'pages.terms')->name('pages.terms');

Route::prefix('admin')->name('admin.')->group(function (): void {
    Route::get('/login', [AdminAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login.submit');

    Route::middleware('admin.auth')->group(function (): void {
        Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');

        Route::middleware('admin.role:super_admin,admin')->group(function (): void {
            Route::get('/', [AdminDashboardController::class, 'dashboard'])->name('dashboard');

            Route::get('/users', [AdminDashboardController::class, 'users'])->name('users.index');
            Route::post('/users', [AdminDashboardController::class, 'storeUser'])->name('users.store');
            Route::patch('/users/{user}/role', [AdminDashboardController::class, 'updateUserRole'])->name('users.role');
            Route::patch('/users/{user}/toggle-active', [AdminDashboardController::class, 'toggleUserActive'])->name('users.toggle-active');

            Route::get('/categories', [AdminDashboardController::class, 'categories'])->name('categories.index');
            Route::post('/categories', [AdminDashboardController::class, 'storeCategory'])->name('categories.store');
            Route::patch('/categories/{category}', [AdminDashboardController::class, 'updateCategory'])->name('categories.update');
            Route::patch('/categories/{category}/toggle-active', [AdminDashboardController::class, 'toggleCategoryActive'])->name('categories.toggle-active');
            Route::delete('/categories/{category}', [AdminDashboardController::class, 'deleteCategory'])->name('categories.delete');

            Route::get('/cities', [AdminDashboardController::class, 'cities'])->name('cities.index');
            Route::post('/cities', [AdminDashboardController::class, 'storeCity'])->name('cities.store');
            Route::patch('/cities/{city}', [AdminDashboardController::class, 'updateCity'])->name('cities.update');
            Route::delete('/cities/{city}', [AdminDashboardController::class, 'deleteCity'])->name('cities.delete');

            Route::get('/districts', [AdminDashboardController::class, 'districts'])->name('districts.index');
            Route::post('/districts', [AdminDashboardController::class, 'storeDistrict'])->name('districts.store');
            Route::patch('/districts/{district}', [AdminDashboardController::class, 'updateDistrict'])->name('districts.update');
            Route::delete('/districts/{district}', [AdminDashboardController::class, 'deleteDistrict'])->name('districts.delete');
        });

        Route::middleware('admin.role:super_admin,admin,ads_agent')->group(function (): void {
            Route::get('/ads', [AdminDashboardController::class, 'ads'])->name('ads.index');
            Route::patch('/ads/{advertisement}/approve', [AdminDashboardController::class, 'approve'])->name('ads.approve');
            Route::patch('/ads/{advertisement}/reject', [AdminDashboardController::class, 'reject'])->name('ads.reject');
            Route::patch('/ads/{advertisement}/toggle-active', [AdminDashboardController::class, 'toggleActive'])->name('ads.toggle-active');
            Route::patch('/ads/{advertisement}/toggle-pinned', [AdminDashboardController::class, 'togglePinned'])->name('ads.toggle-pinned');
        });

        Route::middleware('admin.role:super_admin')->group(function (): void {
            Route::get('/admin-users', [AdminDashboardController::class, 'adminUsers'])->name('admin-users.index');
            Route::post('/admin-users', [AdminDashboardController::class, 'storeAdminUser'])->name('admin-users.store');
            Route::patch('/admin-users/{admin}/role', [AdminDashboardController::class, 'updateAdminUserRole'])->name('admin-users.role');
            Route::patch('/admin-users/{admin}/password', [AdminDashboardController::class, 'updateAdminUserPassword'])->name('admin-users.password');
            Route::patch('/admin-users/{admin}/toggle-active', [AdminDashboardController::class, 'toggleAdminUserActive'])->name('admin-users.toggle-active');
        });
    });
});
