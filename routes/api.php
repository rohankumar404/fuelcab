<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Route Loader
|--------------------------------------------------------------------------
| Loads versioned route files. Add new versions by duplicating the v1
| block and pointing to routes/api/v2/.
*/

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    require __DIR__.'/api/v1/auth.php';
    require __DIR__.'/api/v1/users.php';
    require __DIR__.'/api/v1/drivers.php';
    require __DIR__.'/api/v1/vehicles.php';
    require __DIR__.'/api/v1/orders.php';
    require __DIR__.'/api/v1/fuel.php';
    require __DIR__.'/api/v1/cart.php';
    require __DIR__.'/api/v1/checkout.php';
    require __DIR__.'/api/v1/payments.php';
    require __DIR__.'/api/v1/vendors.php';
    require __DIR__.'/api/v1/wallets.php';
    require __DIR__.'/api/v1/locations.php';
    require __DIR__.'/api/v1/notifications.php';
    require __DIR__.'/api/v1/admin.php';
});
