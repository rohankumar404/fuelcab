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
        return $user->hasAnyRole(['super-admin', 'operations', 'vendor']);
    }

    public function view(User $user, Product $product): bool
    {
        if ($user->hasAnyRole(['super-admin', 'operations'])) {
            return true;
        }

        // Vendors can only view their own products
        return $user->hasRole('vendor') && $product->vendor_id === $user->vendor?->id;
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'operations', 'vendor']);
    }

    public function update(User $user, Product $product): bool
    {
        if ($user->hasAnyRole(['super-admin', 'operations'])) {
            return true;
        }

        return $user->hasRole('vendor') && $product->vendor_id === $user->vendor?->id;
    }

    /**
     * Only super-admin can update product ordering status.
     */
    public function updateStatus(User $user): bool
    {
        return $user->hasAnyRole(['super-admin', 'operations']);
    }

    /**
     * Vendors can sync their own inventory; admins can sync any.
     */
    public function syncInventory(User $user, Product $product): bool
    {
        if ($user->hasAnyRole(['super-admin', 'operations'])) {
            return true;
        }

        return $user->hasRole('vendor') && $product->vendor_id === $user->vendor?->id;
    }

    public function delete(User $user): bool
    {
        return $user->hasAnyRole(['super-admin']);
    }
}
