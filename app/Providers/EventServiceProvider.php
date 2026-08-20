<?php

declare(strict_types=1);

namespace App\Providers;

use App\Modules\Auth\Events\OtpRequested;
// Order Events
use App\Modules\Auth\Events\UserRegistered;
use App\Modules\Auth\Listeners\SendOtpViaSms;
use App\Modules\Auth\Listeners\SendWelcomeNotification;
use App\Modules\Cart\Events\CartCleared;
use App\Modules\Cart\Events\CartItemAdded;
use App\Modules\Cart\Events\CartItemRemoved;
// Order Listeners
use App\Modules\Cart\Events\GuestCartMerged;
use App\Modules\Cart\Listeners\RevalidateCartPrices;
use App\Modules\Driver\Events\DriverApproved;
use App\Modules\Driver\Events\DriverLocationUpdated;
use App\Modules\Driver\Listeners\BroadcastLocationToCustomer;
use App\Modules\Driver\Listeners\SendDriverApprovalNotification;
use App\Modules\Driver\Listeners\UpdateRedisDriverCache;
use App\Modules\Fuel\Events\InventorySynced;
use App\Modules\Fuel\Events\ProductStatusChanged;
use App\Modules\Fuel\Listeners\LogInventoryChange;
use App\Modules\Fuel\Listeners\NotifyLowStock;
use App\Modules\Order\Events\OrderAccepted;
use App\Modules\Order\Events\OrderAssigned;
use App\Modules\Order\Events\OrderCancelled;
use App\Modules\Order\Events\OrderCompleted;
// Payment Events
use App\Modules\Order\Events\OrderCreated;
use App\Modules\Order\Events\OrderDispatched;
use App\Modules\Order\Listeners\DeductFuelInventory;
// Payment Listeners
use App\Modules\Order\Listeners\GenerateInvoice;
use App\Modules\Order\Listeners\LogOrderActivity;
// Driver Events
use App\Modules\Order\Listeners\LogOrderStatusChange;
use App\Modules\Order\Listeners\NotifyCustomerOfDriverAssignment;
// Driver Listeners
use App\Modules\Order\Listeners\NotifyCustomerOfOrderAcceptance;
use App\Modules\Order\Listeners\NotifyDriverOfNewOrder;
use App\Modules\Order\Listeners\NotifyNearbyDrivers;
// Vendor Events
use App\Modules\Order\Listeners\RefundPaymentIfApplicable;
use App\Modules\Order\Listeners\ReleaseDriver;
use App\Modules\Order\Listeners\SendDeliveryCompletedNotification;
// Vendor Listeners
use App\Modules\Order\Listeners\SendOrderCancellationNotification;
use App\Modules\Order\Listeners\SendOrderConfirmationToCustomer;
// Auth Events
use App\Modules\Order\Listeners\TriggerPaymentSettlement;
use App\Modules\Order\Listeners\UpdateDriverEarnings;
// Auth Listeners
use App\Modules\Payment\Events\PaymentFailed;
use App\Modules\Payment\Events\PaymentInitiated;
// Wallet Events
use App\Modules\Payment\Events\PaymentVerified;
// Fuel / Product Events
use App\Modules\Payment\Listeners\SendPaymentReceipt;
use App\Modules\Payment\Listeners\UpdateWalletBalance;
// Fuel / Product Listeners
use App\Modules\Vendor\Events\VendorApproved;
use App\Modules\Vendor\Events\VendorRejected;
// Cart Events
use App\Modules\Vendor\Events\VendorSuspended;
use App\Modules\Vendor\Listeners\SendVendorApprovalNotification;
use App\Modules\Vendor\Listeners\SendVendorRejectionNotification;
use App\Modules\Wallet\Events\WalletToppedUp;
// Cart Listeners
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

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
            LogOrderStatusChange::class,
        ],
        OrderAccepted::class => [
            NotifyCustomerOfOrderAcceptance::class,
            LogOrderStatusChange::class,
        ],
        OrderAssigned::class => [
            NotifyDriverOfNewOrder::class,
            NotifyCustomerOfDriverAssignment::class,
            LogOrderStatusChange::class,
        ],
        OrderDispatched::class => [
            DeductFuelInventory::class,
            LogOrderStatusChange::class,
        ],
        OrderCompleted::class => [
            UpdateDriverEarnings::class,
            TriggerPaymentSettlement::class,
            GenerateInvoice::class,
            SendDeliveryCompletedNotification::class,
            LogOrderStatusChange::class,
        ],
        OrderCancelled::class => [
            RefundPaymentIfApplicable::class,
            SendOrderCancellationNotification::class,
            ReleaseDriver::class,
            LogOrderStatusChange::class,
        ],

        // ─── Payment ─────────────────────────────────────────────────────
        PaymentVerified::class => [
            UpdateWalletBalance::class,
            SendPaymentReceipt::class,
        ],
        PaymentInitiated::class => [],
        PaymentFailed::class => [],

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
        VendorRejected::class => [
            SendVendorRejectionNotification::class,
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
        CartCleared::class => [],
        GuestCartMerged::class => [],
    ];
}
