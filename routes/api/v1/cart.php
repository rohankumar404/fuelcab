<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Cart\Http\Controllers\CartController;

/*
|--------------------------------------------------------------------------
| Cart Routes — API v1
|--------------------------------------------------------------------------
| All routes require authentication via Sanctum.
*/

Route::middleware('auth:sanctum')->prefix('cart')->group(function (): void {
    Route::get('/',                    [CartController::class, 'index']);
    Route::post('items',               [CartController::class, 'addItem']);
    Route::patch('items/{itemId}',     [CartController::class, 'updateItem']);
    Route::delete('items/{itemId}',    [CartController::class, 'removeItem']);
    Route::delete('/',                 [CartController::class, 'clear']);
    Route::post('merge',               [CartController::class, 'merge']);
});
