<?php

use App\Http\Controllers\Api\AdvertisementController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\DistrictController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    Route::prefix('auth')->group(function (): void {
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('/email-login', [AuthController::class, 'emailLogin']);
        Route::middleware('api.user')->get('/me', [AuthController::class, 'me']);
    });

    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/cities', [CityController::class, 'index']);
    Route::get('/districts', [DistrictController::class, 'index']);

    Route::get('/advertisements', [AdvertisementController::class, 'index']);
    Route::get('/advertisements/{advertisement}', [AdvertisementController::class, 'show']);
    Route::post('/advertisements/{advertisement}/like', [AdvertisementController::class, 'like']);
    Route::middleware('api.user')->post('/advertisements', [AdvertisementController::class, 'store']);
});
