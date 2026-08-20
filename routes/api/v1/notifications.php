<?php

declare(strict_types=1);

use App\Modules\Notification\Http\Controllers\NotificationController;
use App\Modules\Notification\Http\Controllers\PushTokenController;
use App\Modules\Notification\Http\Controllers\QuoteController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Notifications Routes — API v1
|--------------------------------------------------------------------------
*/

// ── Public (no auth required) ─────────────────────────────────────────────
Route::post('marketplace/rfq', [QuoteController::class, 'store'])->name('marketplace.rfq.store');

// ── Authenticated routes ──────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->group(function () {
    Route::post('notifications/push-token', [PushTokenController::class, 'store'])->name('notifications.push_token.store');

    // Database notifications CRUD
    Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark_all_read');
    Route::patch('notifications/{id}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::delete('notifications/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
});
