<?php

declare(strict_types=1);

use App\Modules\Auth\Http\Controllers\AuthController;
use App\Modules\Auth\Http\Controllers\GoogleAuthController;
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

    // Strict throttle for registration and login attempts
    Route::middleware('throttle:auth')->group(function (): void {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('reset-password', [AuthController::class, 'resetPassword']);
    });

    // Special rate limits for OTP actions to prevent SMS flooding
    Route::middleware('throttle:otp')->group(function (): void {
        Route::post('send-otp', [AuthController::class, 'sendOtp']);
        Route::post('resend-otp', [AuthController::class, 'resendOtp']);
        Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
        Route::post('verify-reset-otp', [AuthController::class, 'verifyResetOtp']);
    });
});
