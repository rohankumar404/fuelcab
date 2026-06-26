<?php

return [
    App\Modules\Admin\Providers\AdminServiceProvider::class,
    App\Modules\Analytics\Providers\AnalyticsServiceProvider::class,
    App\Modules\Auth\Providers\AuthServiceProvider::class,
    App\Modules\Driver\Providers\DriverServiceProvider::class,
    App\Modules\Fuel\Providers\FuelServiceProvider::class,
    App\Modules\Location\Providers\LocationServiceProvider::class,
    App\Modules\Notification\Providers\NotificationServiceProvider::class,
    App\Modules\Order\Providers\OrderServiceProvider::class,
    App\Modules\Payment\Providers\PaymentServiceProvider::class,
    App\Modules\User\Providers\UserServiceProvider::class,
    App\Modules\Vehicle\Providers\VehicleServiceProvider::class,
    App\Modules\Vendor\Providers\VendorServiceProvider::class,
    App\Modules\Wallet\Providers\WalletServiceProvider::class,
    App\Providers\AppServiceProvider::class,
    App\Providers\EventServiceProvider::class,
    App\Providers\Filament\SuperAdminPanelProvider::class,
    App\Providers\Filament\OperationsPanelProvider::class,
    App\Providers\Filament\VendorPanelProvider::class,
    App\Providers\RepositoryServiceProvider::class,
    Spatie\Permission\PermissionServiceProvider::class,
];

