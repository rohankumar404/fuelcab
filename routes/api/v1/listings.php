<?php

declare(strict_types=1);

use App\Modules\Vendor\Http\Controllers\MarketplaceController;
use App\Modules\Vendor\Http\Controllers\VendorListingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Vendor Listings Routes — API v1
|--------------------------------------------------------------------------
*/

// ── Public (no auth required) ─────────────────────────────────────────────
Route::prefix('marketplace/listings')->group(function () {
    Route::get('/', [VendorListingController::class, 'publicIndex']);
    Route::get('/{slug}', [VendorListingController::class, 'publicShow'])
        ->where('slug', '[a-z0-9\-]+');
});

Route::prefix('marketplace')->group(function () {
    Route::get('categories', [MarketplaceController::class, 'categories']);
    Route::get('products', [MarketplaceController::class, 'products']);
    Route::post('compare', [MarketplaceController::class, 'compare']);
    Route::get('vendors/{vendor}', [MarketplaceController::class, 'showVendorProfile']);
});

// ── Authenticated routes ──────────────────────────────────────────────────
Route::middleware(['auth:sanctum'])->group(function () {

    Route::prefix('marketplace')->group(function () {
        Route::get('wishlist', [MarketplaceController::class, 'listWishlist']);
        Route::post('wishlist', [MarketplaceController::class, 'addWishlist']);
        Route::delete('wishlist/{listingId}', [MarketplaceController::class, 'removeWishlist']);

        Route::get('recently-viewed', [MarketplaceController::class, 'listRecentlyViewed']);
        Route::post('recently-viewed', [MarketplaceController::class, 'addRecentlyViewed']);

        Route::post('vendors/{vendor}/ratings', [MarketplaceController::class, 'rateVendor']);

        Route::post('listings/{slug}/order', [MarketplaceController::class, 'directOrder'])
            ->where('slug', '[a-z0-9\-]+');
    });

    // Vendor self-service (scoped to own vendor via service/policy)
    Route::prefix('vendor/listings')->group(function () {
        Route::get('/', [VendorListingController::class, 'index']);
        Route::post('/', [VendorListingController::class, 'store']);
        Route::get('/{listing}', [VendorListingController::class, 'show']);
        Route::put('/{listing}', [VendorListingController::class, 'update']);
        Route::post('/{listing}/submit', [VendorListingController::class, 'submit']);
        Route::patch('/{listing}/inventory', [VendorListingController::class, 'updateInventory']);
        Route::patch('/{listing}/price', [VendorListingController::class, 'updatePrice']);
        Route::delete('/{listing}', [VendorListingController::class, 'destroy']);
    });

    // Admin / Super Admin management
    Route::prefix('admin/listings')->group(function () {
        Route::get('/', [VendorListingController::class, 'adminIndex']);
        Route::get('/{listing}', [VendorListingController::class, 'adminShow']);
        Route::post('/{listing}/approve', [VendorListingController::class, 'approve']);
        Route::post('/{listing}/reject', [VendorListingController::class, 'reject']);
        Route::post('/{listing}/suspend', [VendorListingController::class, 'suspend']);
        Route::post('/{listing}/feature', [VendorListingController::class, 'feature']);
    });
});
