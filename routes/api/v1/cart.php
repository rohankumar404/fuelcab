<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\Cart\Http\Controllers\CartController;

/*
|--------------------------------------------------------------------------
| Cart Routes — API v1
|--------------------------------------------------------------------------
|
| Authenticated routes require Sanctum. The cart endpoint also supports
| guest access via the X-Guest-Token header (for Flutter + Next.js guests).
|
| Guest carts are server-side sessions keyed by a UUID token generated
| client-side and passed as X-Guest-Token header. On login, the guest
| cart is merged into the user's persistent cart via POST /cart/merge.
|
*/

// Guest-accessible cart routes (authenticated OR guest token)
Route::middleware('auth:sanctum')->prefix('cart')->group(function (): void {
    Route::get('/',                 [CartController::class, 'index']);
    Route::post('items',            [CartController::class, 'addItem']);
    Route::patch('items/{itemId}',  [CartController::class, 'updateItem']);
    Route::delete('items/{itemId}', [CartController::class, 'removeItem']);
    Route::delete('/',              [CartController::class, 'clear']);
    Route::post('merge',            [CartController::class, 'merge']);
});

// Public guest-accessible cart routes (no auth required — use X-Guest-Token header)
Route::prefix('cart')->group(function (): void {
    Route::get('/guest',                    [CartController::class, 'index']);
    Route::post('/guest/items',             [CartController::class, 'addItem']);
    Route::patch('/guest/items/{itemId}',   [CartController::class, 'updateItem']);
    Route::delete('/guest/items/{itemId}',  [CartController::class, 'removeItem']);
    Route::delete('/guest',                 [CartController::class, 'clear']);
});
