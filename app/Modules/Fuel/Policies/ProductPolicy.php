<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Policies;

use App\Models\User;
use App\Modules\Fuel\Models\Product;

class ProductPolicy
{
    /**
     * Only admins can view full product list (vendors see their own).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'operations_team', 'vendor_admin', 'vendor_staff']);
    }

    public function view(User $user, Product $product): bool
    {
        if ($user->hasAnyRole(['super_admin', 'operations_team'])) {
            return true;
        }

        // Vendors can only view their own products
        return $user->hasAnyRole(['vendor_admin', 'vendor_staff']) && $product->vendor_id === $user->vendor_id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'operations_team', 'vendor_admin', 'vendor_staff']);
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->hasAnyRole(['super_admin', 'operations_team'])) {
            return true;
        }

        return $user->hasAnyRole(['vendor_admin', 'vendor_staff']) && $product->vendor_id === $user->vendor_id;
    }

    /**
     * Only super-admin or operations can update product ordering status.
     */
    public function updateStatus(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'operations_team']);
    }

    /**
     * Vendors can sync their own inventory; admins can sync any.
     */
    public function syncInventory(User $user, Product $product): bool
    {
        if ($user->hasAnyRole(['super_admin', 'operations_team'])) {
            return true;
        }

        return $user->hasAnyRole(['vendor_admin', 'vendor_staff']) && $product->vendor_id === $user->vendor_id;
    }

    public function delete(User $user): bool
    {
        return $user->hasAnyRole(['super_admin']);
    }
}
