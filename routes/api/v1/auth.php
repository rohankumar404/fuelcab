<?php

declare(strict_types=1);

use App\Modules\Auth\Http\Controllers\GoogleAuthController;
use App\Modules\Auth\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Auth Routes — API v1
|--------------------------------------------------------------------------
*/

Route::prefix('auth')->name('auth.')->group(function (): void {
    // Google OAuth Routes
    Route::get('google', [GoogleAuthController::class, 'redirectToGoogle'])->name('google');
    Route::get('google/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');

    // Traditional & OTP Auth Routes
    Route::post('register',   [AuthController::class, 'register']);
    Route::post('login',      [AuthController::class, 'login']);
    Route::post('send-otp',   [AuthController::class, 'sendOtp']);
    Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
});
