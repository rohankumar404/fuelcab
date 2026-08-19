<?php

declare(strict_types=1);

use App\Modules\Payment\Http\Controllers\PaymentController;
use App\Modules\Payment\Http\Controllers\PaymentWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| upayments Routes — API v1
|--------------------------------------------------------------------------
*/

// ── Public Webhook route ──────────────────────────────────────────────────
Route::post('payments/webhook', [PaymentWebhookController::class, 'handle'])
    ->middleware('throttle:webhooks')
    ->name('payments.webhook');

// ── Authenticated Routes ──────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('payments/initiate', [PaymentController::class, 'initiate'])->name('payments.initiate');
    Route::post('payments/verify', [PaymentController::class, 'verify'])->name('payments.verify');
    Route::get('payments/history', [PaymentController::class, 'history'])->name('payments.history');
});
