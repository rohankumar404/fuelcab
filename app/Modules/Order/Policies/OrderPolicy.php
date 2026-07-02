<?php

declare(strict_types=1);

namespace App\Modules\Order\Policies;

use App\Models\User;
use App\Modules\Order\Models\Order;
use Illuminate\Auth\Access\HandlesAuthorization;

class OrderPolicy
{
    use HandlesAuthorization;

    /**
     * Super admins and operations team bypass all checks.
     */
    public function before(User $user): ?bool
    {
        if ($user->hasRole(['super_admin', 'operations_team'])) {
            return true;
        }

        return null;
    }

    /**
     * Customers can view their own orders.
     * Vendors can view orders placed with them.
     * Drivers can view orders assigned to them.
     */
    public function view(User $user, Order $order): bool
    {
        return match (true) {
            $user->hasRole('customer')      => $order->customer_id === $user->id,
            $user->hasRole('driver')        => $order->driver_id === $user->id,
            $user->hasRole(['vendor_admin', 'vendor_staff']) => $order->vendor_id === $user->vendor_id,
            default                         => false,
        };
    }

    /**
     * Only vendor admins and vendor staff can accept orders for their vendor.
     */
    public function accept(User $user, Order $order): bool
    {
        if (! $user->hasRole(['vendor_admin', 'vendor_staff'])) {
            return false;
        }

        return $order->vendor_id === $user->vendor_id;
    }

    /**
     * Only vendor admins and operations team can assign drivers.
     */
    public function assignDriver(User $user, Order $order): bool
    {
        if ($user->hasRole('vendor_admin')) {
            return $order->vendor_id === $user->vendor_id;
        }

        return false;
    }

    /**
     * Drivers can update status for their own orders.
     * Vendor admins/staff can update status for their vendor orders.
     */
    public function updateStatus(User $user, Order $order): bool
    {
        return match (true) {
            $user->hasRole('driver')        => $order->driver_id === $user->id,
            $user->hasRole(['vendor_admin', 'vendor_staff']) => $order->vendor_id === $user->vendor_id,
            default                         => false,
        };
    }

    /**
     * Customers can cancel their own pending orders.
     */
    public function cancel(User $user, Order $order): bool
    {
        if (! $user->hasRole('customer')) {
            return $this->updateStatus($user, $order);
        }

        return $order->customer_id === $user->id
            && $order->status?->value === 'pending';
    }
}
