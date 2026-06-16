<?php

declare(strict_types=1);

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\QrCodeController;
use App\Http\Controllers\Api\RedirectController;
use App\Http\Controllers\Api\ShortenController;
use App\Http\Controllers\Api\UrlManagementController;
use App\Http\Controllers\Api\UrlStatisticsController;
use Illuminate\Support\Facades\Route;

Route::middleware('throttle:10,1')->group(function () {

    // Auth
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::post('/auth/refresh', [AuthController::class, 'refresh']);
        Route::get('/auth/me', [AuthController::class, 'me']);
    });

    // Encurtador
   Route::post('/shorten', ShortenController::class)
    ->middleware('auth.optional');
    Route::get('/{code}', RedirectController::class)->where('code', '[A-Za-z0-9]{6,8}');
    Route::get('/{code}/stats', UrlStatisticsController::class)->where('code', '[A-Za-z0-9]{6,8}');
    Route::get('/{code}/qrcode', QrCodeController::class)
        ->where('code', '[A-Za-z0-9]{6,8}')
        ->name('urls.qrcode');

    Route::middleware('auth:api')->group(function () {
        Route::get('/me/urls', [UrlManagementController::class, 'index']);
        Route::patch('/{code}', [UrlManagementController::class, 'update']);
        Route::delete('/{code}', [UrlManagementController::class, 'destroy']);
    });
});