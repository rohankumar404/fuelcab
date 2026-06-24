<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

// Auth
use App\Modules\Auth\Interfaces\AuthServiceInterface;
use App\Modules\Auth\Services\AuthService;

// User
use App\Modules\User\Interfaces\UserRepositoryInterface;
use App\Modules\User\Interfaces\UserServiceInterface;
use App\Modules\User\Repositories\UserRepository;
use App\Modules\User\Services\UserService;

// Driver
use App\Modules\Driver\Interfaces\DriverRepositoryInterface;
use App\Modules\Driver\Interfaces\DriverServiceInterface;
use App\Modules\Driver\Repositories\DriverRepository;
use App\Modules\Driver\Services\DriverService;

// Vehicle
use App\Modules\Vehicle\Interfaces\VehicleRepositoryInterface;
use App\Modules\Vehicle\Interfaces\VehicleServiceInterface;
use App\Modules\Vehicle\Repositories\VehicleRepository;
use App\Modules\Vehicle\Services\VehicleService;

// Order
use App\Modules\Order\Interfaces\OrderRepositoryInterface;
use App\Modules\Order\Interfaces\OrderServiceInterface;
use App\Modules\Order\Repositories\OrderRepository;
use App\Modules\Order\Services\OrderService;

// Fuel
use App\Modules\Fuel\Interfaces\FuelRepositoryInterface;
use App\Modules\Fuel\Interfaces\FuelServiceInterface;
use App\Modules\Fuel\Repositories\FuelRepository;
use App\Modules\Fuel\Services\FuelService;

// Payment
use App\Modules\Payment\Interfaces\PaymentRepositoryInterface;
use App\Modules\Payment\Interfaces\PaymentServiceInterface;
use App\Modules\Payment\Repositories\PaymentRepository;
use App\Modules\Payment\Services\PaymentService;

// Vendor
use App\Modules\Vendor\Interfaces\VendorRepositoryInterface;
use App\Modules\Vendor\Interfaces\VendorServiceInterface;
use App\Modules\Vendor\Repositories\VendorRepository;
use App\Modules\Vendor\Services\VendorService;

// Wallet
use App\Modules\Wallet\Interfaces\WalletRepositoryInterface;
use App\Modules\Wallet\Interfaces\WalletServiceInterface;
use App\Modules\Wallet\Repositories\WalletRepository;
use App\Modules\Wallet\Services\WalletService;

// Location
use App\Modules\Location\Interfaces\LocationServiceInterface;
use App\Modules\Location\Services\LocationService;

// Notification
use App\Modules\Notification\Interfaces\NotificationServiceInterface;
use App\Modules\Notification\Services\NotificationService;

// Analytics
use App\Modules\Analytics\Interfaces\AnalyticsServiceInterface;
use App\Modules\Analytics\Services\AnalyticsService;

class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * All interface → concrete bindings.
     *
     * @var array<class-string, class-string>
     */
    public array $bindings = [
        // Auth
        AuthServiceInterface::class        => AuthService::class,

        // User
        UserRepositoryInterface::class     => UserRepository::class,
        UserServiceInterface::class        => UserService::class,

        // Driver
        DriverRepositoryInterface::class   => DriverRepository::class,
        DriverServiceInterface::class      => DriverService::class,

        // Vehicle
        VehicleRepositoryInterface::class  => VehicleRepository::class,
        VehicleServiceInterface::class     => VehicleService::class,

        // Order
        OrderRepositoryInterface::class    => OrderRepository::class,
        OrderServiceInterface::class       => OrderService::class,

        // Fuel
        FuelRepositoryInterface::class     => FuelRepository::class,
        FuelServiceInterface::class        => FuelService::class,

        // Payment
        PaymentRepositoryInterface::class  => PaymentRepository::class,
        PaymentServiceInterface::class     => PaymentService::class,

        // Vendor
        VendorRepositoryInterface::class   => VendorRepository::class,
        VendorServiceInterface::class      => VendorService::class,

        // Wallet
        WalletRepositoryInterface::class   => WalletRepository::class,
        WalletServiceInterface::class      => WalletService::class,

        // Location
        LocationServiceInterface::class    => LocationService::class,

        // Notification
        NotificationServiceInterface::class => NotificationService::class,

        // Analytics
        AnalyticsServiceInterface::class   => AnalyticsService::class,
    ];

    public function register(): void
    {
        foreach ($this->bindings as $interface => $concrete) {
            $this->app->bind($interface, $concrete);
        }
    }
}
