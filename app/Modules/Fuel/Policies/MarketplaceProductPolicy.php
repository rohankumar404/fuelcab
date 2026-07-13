<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Policies;

use App\Models\User;
use App\Modules\Fuel\Models\MarketplaceProduct;

class MarketplaceProductPolicy
{
    /**
     * Anyone authenticated with appropriate staff roles can view.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'operations_team', 'vendor_admin', 'vendor_staff']);
    }

    public function view(User $user, MarketplaceProduct $product): bool
    {
        return $user->hasAnyRole(['super_admin', 'operations_team', 'vendor_admin', 'vendor_staff']);
    }

    /**
     * Only super_admin can create, update, delete master products.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, MarketplaceProduct $product): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, MarketplaceProduct $product): bool
    {
        return $user->hasRole('super_admin');
    }
}
