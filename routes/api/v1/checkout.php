<?php

declare(strict_types=1);

use App\Modules\Checkout\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Checkout Routes — API v1
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('checkout')->group(function (): void {
    Route::post('initialize', [CheckoutController::class, 'initialize']);
    Route::post('address', [CheckoutController::class, 'selectAddress']);
    Route::post('schedule', [CheckoutController::class, 'selectSchedule']);
    Route::get('{checkoutId}/summary', [CheckoutController::class, 'summary']);
    Route::post('pay', [CheckoutController::class, 'pay']);
});
