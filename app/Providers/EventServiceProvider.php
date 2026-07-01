<?php

declare(strict_types=1);

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

// Order Events
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Events\OrderAssigned;
use App\Modules\Order\Events\OrderCompleted;
use App\Modules\Order\Events\OrderCancelled;

// Order Listeners
use App\Modules\Order\Listeners\NotifyNearbyDrivers;
use App\Modules\Order\Listeners\SendOrderConfirmationToCustomer;
use App\Modules\Order\Listeners\LogOrderActivity;
use App\Modules\Order\Listeners\NotifyDriverOfNewOrder;
use App\Modules\Order\Listeners\NotifyCustomerOfDriverAssignment;
use App\Modules\Order\Listeners\UpdateDriverEarnings;
use App\Modules\Order\Listeners\DeductFuelInventory;
use App\Modules\Order\Listeners\TriggerPaymentSettlement;
use App\Modules\Order\Listeners\GenerateInvoice;
use App\Modules\Order\Listeners\RefundPaymentIfApplicable;
use App\Modules\Order\Listeners\ReleaseDriver;

// Payment Events
use App\Modules\Payment\Events\PaymentVerified;
use App\Modules\Payment\Events\PaymentInitiated;
use App\Modules\Payment\Events\PaymentFailed;

// Payment Listeners
use App\Modules\Payment\Listeners\UpdateWalletBalance;
use App\Modules\Payment\Listeners\SendPaymentReceipt;

// Driver Events
use App\Modules\Driver\Events\DriverLocationUpdated;
use App\Modules\Driver\Events\DriverApproved;

// Driver Listeners
use App\Modules\Driver\Listeners\BroadcastLocationToCustomer;
use App\Modules\Driver\Listeners\UpdateRedisDriverCache;
use App\Modules\Driver\Listeners\SendDriverApprovalNotification;

// Vendor Events
use App\Modules\Vendor\Events\VendorApproved;
use App\Modules\Vendor\Events\VendorSuspended;

// Vendor Listeners
use App\Modules\Vendor\Listeners\SendVendorApprovalNotification;

// Auth Events
use App\Modules\Auth\Events\UserRegistered;
use App\Modules\Auth\Events\OtpRequested;

// Auth Listeners
use App\Modules\Auth\Listeners\SendWelcomeNotification;
use App\Modules\Auth\Listeners\SendOtpViaSms;

// Wallet Events
use App\Modules\Wallet\Events\WalletToppedUp;

// Fuel / Product Events
use App\Modules\Fuel\Events\ProductStatusChanged;
use App\Modules\Fuel\Events\InventorySynced;

// Fuel / Product Listeners
use App\Modules\Fuel\Listeners\LogInventoryChange;
use App\Modules\Fuel\Listeners\NotifyLowStock;

// Cart Events
use App\Modules\Cart\Events\CartItemAdded;
use App\Modules\Cart\Events\CartItemRemoved;
use App\Modules\Cart\Events\CartCleared;
use App\Modules\Cart\Events\GuestCartMerged;

// Cart Listeners
use App\Modules\Cart\Listeners\RevalidateCartPrices;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event-to-listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        // ─── Order ───────────────────────────────────────────────────────
        OrderCreated::class => [
            NotifyNearbyDrivers::class,
            SendOrderConfirmationToCustomer::class,
            LogOrderActivity::class,
        ],
        OrderAssigned::class => [
            NotifyDriverOfNewOrder::class,
            NotifyCustomerOfDriverAssignment::class,
        ],
        OrderCompleted::class => [
            UpdateDriverEarnings::class,
            DeductFuelInventory::class,
            TriggerPaymentSettlement::class,
            GenerateInvoice::class,
        ],
        OrderCancelled::class => [
            RefundPaymentIfApplicable::class,
            ReleaseDriver::class,
        ],

        // ─── Payment ─────────────────────────────────────────────────────
        PaymentVerified::class => [
            UpdateWalletBalance::class,
            SendPaymentReceipt::class,
        ],
        PaymentInitiated::class => [],
        PaymentFailed::class    => [],

        // ─── Driver ──────────────────────────────────────────────────────
        DriverLocationUpdated::class => [
            BroadcastLocationToCustomer::class,
            UpdateRedisDriverCache::class,
        ],
        DriverApproved::class => [
            SendDriverApprovalNotification::class,
        ],

        // ─── Vendor ──────────────────────────────────────────────────────
        VendorApproved::class => [
            SendVendorApprovalNotification::class,
        ],
        VendorSuspended::class => [],

        // ─── Auth ────────────────────────────────────────────────────────
        UserRegistered::class => [
            SendWelcomeNotification::class,
        ],
        OtpRequested::class => [
            SendOtpViaSms::class,
        ],

        // ─── Wallet ──────────────────────────────────────────────────────
        WalletToppedUp::class => [],

        // ─── Fuel / Product ──────────────────────────────────────────────
        ProductStatusChanged::class => [],
        InventorySynced::class => [
            LogInventoryChange::class,
            NotifyLowStock::class,
        ],

        // ─── Cart ─────────────────────────────────────────────────────────
        CartItemAdded::class => [
            RevalidateCartPrices::class,
        ],
        CartItemRemoved::class => [],
        CartCleared::class     => [],
        GuestCartMerged::class => [],
    ];
}
