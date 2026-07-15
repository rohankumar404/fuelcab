<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Policies;

use App\Enums\ListingStatus;
use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorListing;
use App\Modules\Vendor\Enums\VendorStatus;

class VendorListingPolicy
{
    /**
     * Super admin / ops / vendor roles can call index endpoints.
     * Query scoping ensures vendors only see their own listings.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'operations_team', 'vendor_admin', 'vendor_staff']);
    }

    /**
     * Admin can view any listing. Vendors can only view their own.
     */
    public function view(User $user, VendorListing $listing): bool
    {
        if ($user->hasAnyRole(['super_admin', 'operations_team'])) {
            return true;
        }

        return $user->hasAnyRole(['vendor_admin', 'vendor_staff'])
            && $listing->vendor_id === $user->vendor_id;
    }

    /**
     * Only APPROVED vendors can create listings.
     */
    public function create(User $user): bool
    {
        if (! $user->hasRole('vendor_admin')) {
            return false;
        }

        $vendor = Vendor::find($user->vendor_id);

        return $vendor && $vendor->status === VendorStatus::Approved;
    }

    /**
     * Vendor can update only their own DRAFT or REJECTED listings.
     * Admin can always update.
     */
    public function update(User $user, VendorListing $listing): bool
    {
        if ($user->hasAnyRole(['super_admin', 'operations_team'])) {
            return true;
        }

        return $user->hasAnyRole(['vendor_admin', 'vendor_staff'])
            && $listing->vendor_id === $user->vendor_id
            && $listing->approval_status->isEditable();
    }

    /**
     * Vendor can submit their own DRAFT or REJECTED listing for approval.
     */
    public function submit(User $user, VendorListing $listing): bool
    {
        return $user->hasRole('vendor_admin')
            && $listing->vendor_id === $user->vendor_id
            && $listing->approval_status->isSubmittable();
    }

    /**
     * Only super admin can approve a PENDING_APPROVAL listing.
     */
    public function approve(User $user, VendorListing $listing): bool
    {
        return $user->hasRole('super_admin')
            && $listing->approval_status === ListingStatus::PendingApproval;
    }

    /**
     * Only super admin can reject a listing.
     */
    public function reject(User $user, VendorListing $listing): bool
    {
        return $user->hasRole('super_admin')
            && $listing->approval_status === ListingStatus::PendingApproval;
    }

    /**
     * Only super admin can suspend an approved listing.
     */
    public function suspend(User $user, VendorListing $listing): bool
    {
        return $user->hasRole('super_admin')
            && $listing->approval_status === ListingStatus::Approved;
    }

    /**
     * Only super admin can toggle featured status.
     */
    public function feature(User $user): bool
    {
        return $user->hasRole('super_admin');
    }

    /**
     * Vendor can update available_quantity on any of their own listings.
     */
    public function updateInventory(User $user, VendorListing $listing): bool
    {
        if ($user->hasAnyRole(['super_admin', 'operations_team'])) {
            return true;
        }

        return $user->hasAnyRole(['vendor_admin', 'vendor_staff'])
            && $listing->vendor_id === $user->vendor_id;
    }

    /**
     * Vendor can update price on their own listings.
     */
    public function updatePrice(User $user, VendorListing $listing): bool
    {
        if ($user->hasAnyRole(['super_admin', 'operations_team'])) {
            return true;
        }

        return $user->hasAnyRole(['vendor_admin', 'vendor_staff'])
            && $listing->vendor_id === $user->vendor_id;
    }

    /**
     * Only super admin can soft-delete a listing.
     */
    public function delete(User $user, VendorListing $listing): bool
    {
        return $user->hasRole('super_admin');
    }
}
