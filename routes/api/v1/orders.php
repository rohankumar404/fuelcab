<?php

declare(strict_types=1);

use App\Modules\Order\Http\Controllers\CustomerOrderController;
use App\Modules\Order\Http\Controllers\OrderController;
use App\Modules\Order\Http\Controllers\OrderTrackingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Orders Routes — API v1
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('orders')->group(function (): void {
    // ── Customer Portal — static routes BEFORE {id} wildcards ──────────────
    Route::post('emergency', [CustomerOrderController::class, 'createEmergencyOrder']);
    Route::get('subscriptions', [CustomerOrderController::class, 'listSubscriptions']);
    Route::post('subscriptions', [CustomerOrderController::class, 'createSubscription']);
    Route::patch('subscriptions/{id}', [CustomerOrderController::class, 'updateSubscription']);
    Route::delete('subscriptions/{id}', [CustomerOrderController::class, 'cancelSubscription']);

    // ── Create & List & Show ─────────────────────────────────────────────────
    Route::post('/', [OrderController::class, 'store']);   // POST /api/v1/orders
    Route::get('/', [OrderController::class, 'index']);    // GET  /api/v1/orders
    Route::get('{id}', [OrderController::class, 'show']);

    // ── Status Transitions (vendor/driver-facing) ───────────────────────────
    Route::patch('{id}/accept', [OrderController::class, 'accept']);
    Route::patch('{id}/assign-driver', [OrderController::class, 'assignDriver']);
    Route::patch('{id}/status', [OrderController::class, 'updateStatus']);

    // ── Customer Actions ─────────────────────────────────────────────────────
    Route::post('{id}/cancel', [CustomerOrderController::class, 'cancel']);
    Route::post('{id}/reorder', [CustomerOrderController::class, 'reorder']);

    // ── GPS Tracking ─────────────────────────────────────────────────────────
    Route::post('{id}/tracking', [OrderTrackingController::class, 'store']);
    Route::get('{id}/tracking', [OrderTrackingController::class, 'track']);

    // ── Delivery Confirmation (OTP proof-of-delivery) ────────────────────────
    Route::post('{id}/confirm-delivery', [OrderController::class, 'confirmDelivery']);

    // ── Invoice ──────────────────────────────────────────────────────────────
    Route::get('{id}/invoice', [CustomerOrderController::class, 'downloadInvoice']);
});
