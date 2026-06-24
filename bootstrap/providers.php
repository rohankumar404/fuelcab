<?php

use App\Providers\AppServiceProvider;
use App\Providers\RepositoryServiceProvider;
use App\Providers\EventServiceProvider;

// Module Service Providers
use App\Modules\Auth\Providers\AuthServiceProvider;
use App\Modules\User\Providers\UserServiceProvider;
use App\Modules\Driver\Providers\DriverServiceProvider;
use App\Modules\Vehicle\Providers\VehicleServiceProvider;
use App\Modules\Order\Providers\OrderServiceProvider;
use App\Modules\Fuel\Providers\FuelServiceProvider;
use App\Modules\Payment\Providers\PaymentServiceProvider;
use App\Modules\Vendor\Providers\VendorServiceProvider;
use App\Modules\Wallet\Providers\WalletServiceProvider;
use App\Modules\Notification\Providers\NotificationServiceProvider;
use App\Modules\Location\Providers\LocationServiceProvider;
use App\Modules\Analytics\Providers\AnalyticsServiceProvider;
use App\Modules\Admin\Providers\AdminServiceProvider;

return [
    // ─── Core ────────────────────────────────────────────────────────
    AppServiceProvider::class,
    RepositoryServiceProvider::class,
    EventServiceProvider::class,
    \Spatie\Permission\PermissionServiceProvider::class,

    // ─── Modules ─────────────────────────────────────────────────────
    AuthServiceProvider::class,
    UserServiceProvider::class,
    DriverServiceProvider::class,
    VehicleServiceProvider::class,
    OrderServiceProvider::class,
    FuelServiceProvider::class,
    PaymentServiceProvider::class,
    VendorServiceProvider::class,
    WalletServiceProvider::class,
    NotificationServiceProvider::class,
    LocationServiceProvider::class,
    AnalyticsServiceProvider::class,
    AdminServiceProvider::class,
];
