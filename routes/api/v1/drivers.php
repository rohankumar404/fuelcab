<?php

declare(strict_types=1);

use App\Modules\Driver\Http\Controllers\DriverController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| udrivers Routes — API v1
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('drivers')->name('drivers.')->group(function () {
        Route::get('profile', [DriverController::class, 'profile'])->name('profile');
        Route::post('availability', [DriverController::class, 'toggleAvailability'])->name('availability.toggle');
        
        // Orders & Trips
        Route::get('orders/assigned', [DriverController::class, 'assignedOrders'])->name('orders.assigned');
        Route::get('orders/trips', [DriverController::class, 'tripHistory'])->name('orders.trips');
        
        // Verification & Completion
        Route::post('orders/{orderId}/verify-otp', [DriverController::class, 'verifyOtp'])->name('orders.verify_otp');
        Route::post('orders/{orderId}/complete', [DriverController::class, 'completeOrder'])->name('orders.complete');
    });
});
