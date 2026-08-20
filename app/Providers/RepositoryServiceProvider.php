<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Analytics\Interfaces\AnalyticsServiceInterface;
// Auth
use App\Modules\Analytics\Services\AnalyticsService;
use App\Modules\Auth\Interfaces\AuthServiceInterface;
// User
use App\Modules\Auth\Services\AuthService;
use App\Modules\Driver\Interfaces\DriverRepositoryInterface;
use App\Modules\Driver\Interfaces\DriverServiceInterface;
use App\Modules\Driver\Repositories\DriverRepository;
// Driver
use App\Modules\Driver\Services\DriverService;
use App\Modules\Fuel\Interfaces\FuelRepositoryInterface;
use App\Modules\Fuel\Interfaces\FuelServiceInterface;
use App\Modules\Fuel\Repositories\FuelRepository;
// Vehicle
use App\Modules\Fuel\Services\FuelService;
use App\Modules\Location\Interfaces\LocationServiceInterface;
use App\Modules\Location\Services\LocationService;
use App\Modules\Notification\Interfaces\NotificationServiceInterface;
// Order
use App\Modules\Notification\Services\NotificationService;
use App\Modules\Order\Interfaces\OrderRepositoryInterface;
use App\Modules\Order\Interfaces\OrderServiceInterface;
use App\Modules\Order\Repositories\OrderRepository;
// Fuel
use App\Modules\Order\Services\OrderService;
use App\Modules\Payment\Interfaces\PaymentRepositoryInterface;
use App\Modules\Payment\Interfaces\PaymentServiceInterface;
use App\Modules\Payment\Repositories\PaymentRepository;
// Payment
use App\Modules\Payment\Services\PaymentService;
use App\Modules\User\Interfaces\UserRepositoryInterface;
use App\Modules\User\Interfaces\UserServiceInterface;
use App\Modules\User\Repositories\UserRepository;
// Vendor
use App\Modules\User\Services\UserService;
use App\Modules\Vehicle\Interfaces\VehicleRepositoryInterface;
use App\Modules\Vehicle\Interfaces\VehicleServiceInterface;
use App\Modules\Vehicle\Repositories\VehicleRepository;
// Wallet
use App\Modules\Vehicle\Services\VehicleService;
use App\Modules\Vendor\Interfaces\VendorRepositoryInterface;
use App\Modules\Vendor\Interfaces\VendorServiceInterface;
use App\Modules\Vendor\Repositories\VendorRepository;
// Location
use App\Modules\Vendor\Services\VendorService;
use App\Modules\Wallet\Interfaces\WalletRepositoryInterface;
// Notification
use App\Modules\Wallet\Interfaces\WalletServiceInterface;
use App\Modules\Wallet\Repositories\WalletRepository;
// Analytics
use App\Modules\Wallet\Services\WalletService;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All interface → concrete bindings.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        // Auth
        AuthServiceInterface::class => AuthService::class,

        // User
        UserRepositoryInterface::class => UserRepository::class,
        UserServiceInterface::class => UserService::class,

        // Driver
        DriverRepositoryInterface::class => DriverRepository::class,
        DriverServiceInterface::class => DriverService::class,

        // Vehicle
        VehicleRepositoryInterface::class => VehicleRepository::class,
        VehicleServiceInterface::class => VehicleService::class,

        // Order
        OrderRepositoryInterface::class => OrderRepository::class,
        OrderServiceInterface::class => OrderService::class,

        // Fuel
        FuelRepositoryInterface::class => FuelRepository::class,
        FuelServiceInterface::class => FuelService::class,

        // Payment
        PaymentRepositoryInterface::class => PaymentRepository::class,
        PaymentServiceInterface::class => PaymentService::class,

        // Vendor
        VendorRepositoryInterface::class => VendorRepository::class,
        VendorServiceInterface::class => VendorService::class,

        // Wallet
        WalletRepositoryInterface::class => WalletRepository::class,
        WalletServiceInterface::class => WalletService::class,

        // Location
        LocationServiceInterface::class => LocationService::class,

        // Notification
        NotificationServiceInterface::class => NotificationService::class,

        // Analytics
        AnalyticsServiceInterface::class => AnalyticsService::class,
    ];

    public function register(): void
    {
        foreach ($this->bindings as $interface => $concrete) {
            $this->app->bind($interface, $concrete);
        }
    }
}
