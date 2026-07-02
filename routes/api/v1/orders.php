<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Order\Http\Controllers\OrderController;
use App\Modules\Order\Http\Controllers\OrderTrackingController;

/*
|--------------------------------------------------------------------------
| Orders Routes — API v1
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('orders')->group(function (): void {
    // Status Transitions
    Route::patch('{id}/accept',         [OrderController::class, 'accept']);
    Route::patch('{id}/assign-driver',  [OrderController::class, 'assignDriver']);
    Route::patch('{id}/status',         [OrderController::class, 'updateStatus']);

    // Tracking endpoints
    Route::post('{id}/tracking',        [OrderTrackingController::class, 'store']);
    Route::get('{id}/tracking',         [OrderTrackingController::class, 'track']);
});
