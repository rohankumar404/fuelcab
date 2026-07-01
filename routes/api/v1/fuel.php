<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Fuel\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| Fuel Routes — API v1
|--------------------------------------------------------------------------
*/

Route::prefix('products')->group(function (): void {
    // Public — list & view
    Route::get('/', [ProductController::class, 'index']);
    Route::get('{id}', [ProductController::class, 'show']);

    // Protected — admin/vendor actions
    Route::middleware('auth:sanctum')->group(function (): void {
        Route::patch('{id}/status', [ProductController::class, 'updateStatus']);
        Route::post('{id}/sync-inventory', [ProductController::class, 'syncInventory']);
        Route::post('bulk-sync', [ProductController::class, 'bulkSync']);
    });
});
