<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Order\Http\Controllers\OrderController;
use App\Modules\Order\Http\Controllers\OrderTrackingController;
use App\Modules\Order\Http\Controllers\CustomerOrderController;

/*
|--------------------------------------------------------------------------
| Orders Routes — API v1
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('orders')->group(function (): void {
    // Customer Portal fixed-segment routes — MUST come before {id} wildcards
    Route::post('emergency',            [CustomerOrderController::class, 'createEmergencyOrder']);
    Route::get('subscriptions',         [CustomerOrderController::class, 'listSubscriptions']);
    Route::post('subscriptions',        [CustomerOrderController::class, 'createSubscription']);
    Route::patch('subscriptions/{id}',  [CustomerOrderController::class, 'updateSubscription']);
    Route::delete('subscriptions/{id}', [CustomerOrderController::class, 'cancelSubscription']);

    // CRUD Endpoints
    Route::get('/',          [OrderController::class, 'index']);
    Route::get('{id}',       [OrderController::class, 'show']);

    // Status Transitions
    Route::patch('{id}/accept',        [OrderController::class, 'accept']);
    Route::patch('{id}/assign-driver', [OrderController::class, 'assignDriver']);
    Route::patch('{id}/status',        [OrderController::class, 'updateStatus']);

    // Tracking
    Route::post('{id}/tracking', [OrderTrackingController::class, 'store']);
    Route::get('{id}/tracking',  [OrderTrackingController::class, 'track']);

    // Invoice download
    Route::get('{id}/invoice',   [CustomerOrderController::class, 'downloadInvoice']);
});
