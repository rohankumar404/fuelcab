<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Policies;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Enums\VendorStatus;

class VendorPolicy
{
    /**
     * Super admin and operations can list all vendors.
     * Vendor users can only see their own vendor (enforced in controller).
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'operations_team', 'vendor_admin', 'vendor_staff']);
    }

    /**
     * Super admin / ops can view any. Vendor users only their own.
     */
    public function view(User $user, Vendor $vendor): bool
    {
        if ($user->hasAnyRole(['super_admin', 'operations_team'])) {
            return true;
        }

        return $user->hasAnyRole(['vendor_admin', 'vendor_staff'])
            && (string) $user->vendor_id === (string) $vendor->id;
    }

    /**
     * Only super admin can create new vendor records.
     */
    public function create(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Super admin can update any vendor.
     * Vendor admin can update their own profile fields (not status).
     */
    public function update(User $user, Vendor $vendor): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasRole('vendor_admin')
            && (string) $user->vendor_id === (string) $vendor->id;
    }

    /**
     * Only super admin can approve vendors.
     */
    public function approve(User $user, Vendor $vendor): bool
    {
        return $user->hasRole('super_admin')
            && $vendor->status !== VendorStatus::Approved;
    }

    /**
     * Only super admin can reject vendors.
     */
    public function reject(User $user, Vendor $vendor): bool
    {
        return $user->hasRole('super_admin')
            && $vendor->status !== VendorStatus::Rejected;
    }

    /**
     * Only super admin can suspend vendors.
     */
    public function suspend(User $user, Vendor $vendor): bool
    {
        return $user->hasRole('super_admin')
            && $vendor->status === VendorStatus::Approved;
    }

    /**
     * Only super admin can reactivate suspended vendors.
     */
    public function reactivate(User $user, Vendor $vendor): bool
    {
        return $user->hasRole('super_admin')
            && $vendor->status === VendorStatus::Suspended;
    }

    /**
     * Only super admin can add internal notes.
     */
    public function addNotes(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    public function delete(User $user): bool
    {
        return $user->hasRole('super_admin');
    }
}
