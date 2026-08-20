<?php

use App\Modules\Admin\Providers\AdminServiceProvider;
use App\Modules\Analytics\Providers\AnalyticsServiceProvider;
use App\Modules\Auth\Providers\AuthServiceProvider;
use App\Modules\Driver\Providers\DriverServiceProvider;
use App\Modules\Fuel\Providers\FuelServiceProvider;
use App\Modules\Location\Providers\LocationServiceProvider;
use App\Modules\Notification\Providers\NotificationServiceProvider;
use App\Modules\Order\Providers\OrderServiceProvider;
use App\Modules\Payment\Providers\PaymentServiceProvider;
use App\Modules\User\Providers\UserServiceProvider;
use App\Modules\Vehicle\Providers\VehicleServiceProvider;
use App\Modules\Vendor\Providers\VendorServiceProvider;
use App\Modules\Wallet\Providers\WalletServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\EventServiceProvider;
use App\Providers\Filament\OperationsPanelProvider;
use App\Providers\Filament\SuperAdminPanelProvider;
use App\Providers\Filament\VendorPanelProvider;
use App\Providers\RepositoryServiceProvider;
use Spatie\Permission\PermissionServiceProvider;

return [
    AdminServiceProvider::class,
    AnalyticsServiceProvider::class,
    AuthServiceProvider::class,
    DriverServiceProvider::class,
    FuelServiceProvider::class,
    LocationServiceProvider::class,
    NotificationServiceProvider::class,
    OrderServiceProvider::class,
    PaymentServiceProvider::class,
    UserServiceProvider::class,
    VehicleServiceProvider::class,
    VendorServiceProvider::class,
    WalletServiceProvider::class,
    AppServiceProvider::class,
    EventServiceProvider::class,
    SuperAdminPanelProvider::class,
    OperationsPanelProvider::class,
    VendorPanelProvider::class,
    RepositoryServiceProvider::class,
    PermissionServiceProvider::class,
];
