<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Policies;

use App\Models\Settlement;
use App\Models\User;

/**
 * SettlementPolicy
 *
 * Security: vendor_id resolved from authenticated user.
 * Vendors can ONLY view their own settlements.
 */
class SettlementPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'operations_team', 'vendor_admin', 'vendor_staff']);
    }

    public function view(User $user, Settlement $settlement): bool
    {
        if ($user->hasAnyRole(['super_admin', 'operations_team'])) {
            return true;
        }

        return $user->hasAnyRole(['vendor_admin', 'vendor_staff'])
            && $settlement->vendor_id === $user->vendor_id;
    }

    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function update(User $user, Settlement $settlement): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user, Settlement $settlement): bool
    {
        return $user->hasRole('super_admin');
    }
}
