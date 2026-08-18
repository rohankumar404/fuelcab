<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use App\Modules\User\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| User & Customer Portal Routes — API v1
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->group(function (): void {
    // Dashboard
    Route::get('customer/dashboard', [UserController::class, 'dashboard']);

    // Profile
    Route::get('customer/profile',    [UserController::class, 'profile']);
    Route::put('customer/profile',    [UserController::class, 'updateProfile']);

    // Addresses
    Route::get('customer/addresses',       [UserController::class, 'listAddresses']);
    Route::post('customer/addresses',      [UserController::class, 'createAddress']);
    Route::put('customer/addresses/{id}',  [UserController::class, 'updateAddress']);
    Route::delete('customer/addresses/{id}', [UserController::class, 'deleteAddress']);

    // Favorites
    Route::get('customer/favorites',             [UserController::class, 'listFavorites']);
    Route::post('customer/favorites',            [UserController::class, 'addFavorite']);
    Route::delete('customer/favorites/{listingId}', [UserController::class, 'deleteFavorite']);

    // Support Tickets
    Route::get('customer/support/tickets',  [UserController::class, 'listTickets']);
    Route::post('customer/support/tickets', [UserController::class, 'createTicket']);
});
