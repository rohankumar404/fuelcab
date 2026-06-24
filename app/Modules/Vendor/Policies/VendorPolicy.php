<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Policies;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Auth\Access\HandlesAuthorization;

class VendorPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any vendors.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view_vendors');
    }

    /**
     * Determine whether the user can view a vendor.
     */
    public function view(User $user, Vendor $vendor): bool
    {
        if (!$user->can('view_vendors')) {
            return false;
        }

        // Vendor admin/staff scoping
        if ($user->hasRole(['vendor_admin', 'vendor_staff'])) {
            return $user->vendor_id === $vendor->id;
        }

        return true;
    }

    /**
     * Determine whether the user can update the vendor settings.
     */
    public function update(User $user, Vendor $vendor): bool
    {
        if (!$user->can('update_vendors') && !$user->can('manage_vendor_settings')) {
            return false;
        }

        // Vendor admin can update their own vendor
        if ($user->hasRole('vendor_admin')) {
            return $user->vendor_id === $vendor->id;
        }

        // Platform staff (Operations / SuperAdmin)
        return $user->can('update_vendors');
    }

    /**
     * Determine whether the user can approve a vendor registration.
     */
    public function approve(User $user): bool
    {
        return $user->can('approve_vendors');
    }
}
