<?php

use App\Http\Controllers\Api\Admin\AdController as AdminAdController;
use App\Http\Controllers\Api\Admin\AdminUserController as AdminAdminUserController;
use App\Http\Controllers\Api\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Api\Admin\CityController as AdminCityController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\DistrictController as AdminDistrictController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;
use App\Http\Controllers\Api\AdvertisementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\UploadController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('/email-login', [AuthController::class, 'emailLogin']);
        Route::middleware('api.user')->group(function (): void {
            Route::get('/me', [AuthController::class, 'me']);
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::patch('/profile', [AuthController::class, 'updateProfile']);
            Route::post('/change-password', [AuthController::class, 'changePassword']);
        });
    });

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/cities', [CityController::class, 'index']);
    Route::get('/districts', [DistrictController::class, 'index']);

    Route::get('/advertisements', [AdvertisementController::class, 'index']);
    Route::get('/advertisements/{advertisement}', [AdvertisementController::class, 'show']);
    Route::post('/advertisements/{advertisement}/like', [AdvertisementController::class, 'like']);
    Route::middleware('api.user')->group(function (): void {
        Route::get('/advertisements/mine', [AdvertisementController::class, 'mine']);
        Route::post('/advertisements', [AdvertisementController::class, 'store']);
        Route::patch('/advertisements/{advertisement}', [AdvertisementController::class, 'update']);
        Route::delete('/advertisements/{advertisement}', [AdvertisementController::class, 'destroy']);
    });

    Route::middleware('api.user')->group(function (): void {
        Route::post('/upload/image', [UploadController::class, 'image']);
        Route::post('/upload/images', [UploadController::class, 'images']);
    });
});

Route::prefix('admin/v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('/login', [AdminAuthController::class, 'login']);
        Route::middleware('api.admin')->group(function (): void {
            Route::get('/me', [AdminAuthController::class, 'me']);
            Route::post('/logout', [AdminAuthController::class, 'logout']);
            Route::post('/change-password', [AdminAuthController::class, 'changePassword']);
        });
    });

    Route::middleware(['api.admin', 'api.admin.role:super_admin,admin'])->group(function (): void {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);

        Route::get('/advertisements', [AdminAdController::class, 'index']);
        Route::get('/advertisements/{advertisement}', [AdminAdController::class, 'show']);
        Route::delete('/advertisements/{advertisement}', [AdminAdController::class, 'destroy']);

        Route::get('/users', [AdminUserController::class, 'index']);
        Route::post('/users', [AdminUserController::class, 'store']);
        Route::get('/users/{user}', [AdminUserController::class, 'show']);
        Route::patch('/users/{user}/role', [AdminUserController::class, 'updateRole']);
        Route::patch('/users/{user}/toggle-active', [AdminUserController::class, 'toggleActive']);
        Route::delete('/users/{user}', [AdminUserController::class, 'destroy']);

        Route::get('/categories', [AdminCategoryController::class, 'index']);
        Route::post('/categories', [AdminCategoryController::class, 'store']);
        Route::patch('/categories/{category}', [AdminCategoryController::class, 'update']);
        Route::patch('/categories/{category}/toggle-active', [AdminCategoryController::class, 'toggleActive']);
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy']);

        Route::get('/cities', [AdminCityController::class, 'index']);
        Route::post('/cities', [AdminCityController::class, 'store']);
        Route::patch('/cities/{city}', [AdminCityController::class, 'update']);
        Route::delete('/cities/{city}', [AdminCityController::class, 'destroy']);

        Route::get('/districts', [AdminDistrictController::class, 'index']);
        Route::post('/districts', [AdminDistrictController::class, 'store']);
        Route::patch('/districts/{district}', [AdminDistrictController::class, 'update']);
        Route::delete('/districts/{district}', [AdminDistrictController::class, 'destroy']);
    });

    Route::middleware(['api.admin', 'api.admin.role:super_admin,admin,ads_agent'])->group(function (): void {
        Route::patch('/advertisements/{advertisement}/approve', [AdminAdController::class, 'approve']);
        Route::patch('/advertisements/{advertisement}/reject', [AdminAdController::class, 'reject']);
        Route::patch('/advertisements/{advertisement}/toggle-active', [AdminAdController::class, 'toggleActive']);
        Route::patch('/advertisements/{advertisement}/toggle-pinned', [AdminAdController::class, 'togglePinned']);
    });

    Route::middleware(['api.admin', 'api.admin.role:super_admin'])->group(function (): void {
        Route::get('/admin-users', [AdminAdminUserController::class, 'index']);
        Route::post('/admin-users', [AdminAdminUserController::class, 'store']);
        Route::patch('/admin-users/{admin}/role', [AdminAdminUserController::class, 'updateRole']);
        Route::patch('/admin-users/{admin}/password', [AdminAdminUserController::class, 'updatePassword']);
        Route::patch('/admin-users/{admin}/toggle-active', [AdminAdminUserController::class, 'toggleActive']);
        Route::delete('/admin-users/{admin}', [AdminAdminUserController::class, 'destroy']);
    });
});
