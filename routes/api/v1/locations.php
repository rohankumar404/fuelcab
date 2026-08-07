<?php

declare(strict_types=1);

use App\Modules\Location\Http\Controllers\LocationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| ulocations Routes — API v1
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum'])->group(function () {
    // Address Search & Coordinates
    Route::post('locations/autocomplete', [LocationController::class, 'autocomplete'])->name('locations.autocomplete');
    Route::post('locations/geocode', [LocationController::class, 'geocode'])->name('locations.geocode');
    
    // Matrix Calculations
    Route::get('locations/eta', [LocationController::class, 'eta'])->name('locations.eta');
    Route::get('locations/distance', [LocationController::class, 'distance'])->name('locations.distance');
    
    // Route Optimization
    Route::post('locations/route/optimize', [LocationController::class, 'optimizeRoute'])->name('locations.route.optimize');

    // Live Tracking & History
    Route::get('locations/driver/{orderId}', [LocationController::class, 'getLiveDriverPosition'])->name('locations.driver.live');
    Route::get('locations/tracking/{orderId}', [LocationController::class, 'getDeliveryTrackingHistory'])->name('locations.tracking.history');
    
    // GPS updates (Driver only as authorized in Request class)
    Route::post('locations/driver/update', [LocationController::class, 'updateDriverLocation'])->name('locations.driver.update');
});
