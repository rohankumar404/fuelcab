<?php

declare(strict_types=1);

use App\Modules\Wallet\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Wallets Routes — API v1
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->prefix('wallets')->group(function (): void {
    Route::get('/', [WalletController::class, 'show'])->name('wallets.show');
    Route::post('top-up', [WalletController::class, 'topUp'])->name('wallets.top_up');
    Route::post('deduct', [WalletController::class, 'deduct'])->name('wallets.deduct');
});
