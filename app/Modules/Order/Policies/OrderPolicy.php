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
     * Determine whether the user can view any orders.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_orders');
    }

    /**
     * Determine whether the user can view the order.
     */
    public function view(User $user, Order $order): bool
    {
        // 1. Must have general permission
        if (!$user->can('view_orders')) {
            return false;
        }

        // 2. SaaS Tenant/Vendor scoping rule: Vendor admins/staff can only see their vendor's orders
        if ($user->hasRole(['vendor_admin', 'vendor_staff'])) {
            return $user->vendor_id === $order->vendor_id;
        }

        // 3. Driver scoping rule: Drivers can only view orders assigned to them
        if ($user->hasRole('driver')) {
            return $user->id === $order->driver_id;
        }

        // 4. Customer scoping rule: Customers can only view their own orders
        if ($user->hasRole('customer')) {
            return $user->id === $order->customer_id;
        }

        // SuperAdmin / Operations team gets general pass (handled implicitly or through check)
        return true;
    }

    /**
     * Determine whether the user can create orders.
     */
    public function create(User $user): bool
    {
        return $user->can('create_orders');
    }

    /**
     * Determine whether the user can update the order.
     */
    public function update(User $user, Order $order): bool
    {
        if (!$user->can('update_orders')) {
            return false;
        }

        // Tenant Scoping boundary
        if ($user->hasRole(['vendor_admin', 'vendor_staff'])) {
            return $user->vendor_id === $order->vendor_id;
        }

        // Driver context: Driver can only update the status of their assigned order
        if ($user->hasRole('driver')) {
            return $user->id === $order->driver_id;
        }

        return true;
    }

    /**
     * Determine whether the user can cancel the order.
     */
    public function cancel(User $user, Order $order): bool
    {
        if (!$user->can('cancel_orders')) {
            return false;
        }

        if ($user->hasRole('customer')) {
            // Customer can only cancel their own order and only if not already dispatched/completed
            return $user->id === $order->customer_id && in_array($order->status, ['pending', 'confirmed']);
        }

        if ($user->hasRole('vendor_admin')) {
            return $user->vendor_id === $order->vendor_id;
        }

        return true;
    }

    /**
     * Determine whether the user can dispatch the order.
     */
    public function dispatch(User $user, Order $order): bool
    {
        if (!$user->can('dispatch_orders')) {
            return false;
        }

        if ($user->hasRole(['vendor_admin', 'vendor_staff'])) {
            return $user->vendor_id === $order->vendor_id;
        }

        return true;
    }
}
