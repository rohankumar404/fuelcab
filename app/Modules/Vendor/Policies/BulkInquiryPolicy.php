<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Policies;

use App\Models\BulkInquiry;
use App\Models\User;

/**
 * BulkInquiryPolicy
 *
 * Security: vendor_id is ALWAYS resolved from the authenticated user.
 * Vendors can ONLY view/respond to inquiries for their own listings.
 * Cross-vendor access is denied at the policy level.
 */
class BulkInquiryPolicy
{
    /**
     * Super admin / ops / vendor roles can list inquiries.
     * Query scoping ensures vendors only see their own.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'operations_team', 'vendor_admin', 'vendor_staff']);
    }

    /**
     * Vendor can view inquiry ONLY if it belongs to their vendor.
     */
    public function view(User $user, BulkInquiry $inquiry): bool
    {
        if ($user->hasAnyRole(['super_admin', 'operations_team'])) {
            return true;
        }

        return $user->hasAnyRole(['vendor_admin', 'vendor_staff'])
            && $inquiry->vendor_id === $user->vendor_id;
    }

    /**
     * Customers can create inquiries.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Only the owning vendor can submit a quotation for their own inquiry.
     */
    public function respond(User $user, BulkInquiry $inquiry): bool
    {
        if ($user->hasAnyRole(['super_admin', 'operations_team'])) {
            return true;
        }

        return $user->hasAnyRole(['vendor_admin', 'vendor_staff'])
            && $inquiry->vendor_id === $user->vendor_id
            && $inquiry->status === 'pending';
    }

    /**
     * Deny update to non-owning vendors.
     */
    public function update(User $user, BulkInquiry $inquiry): bool
    {
        if ($user->hasAnyRole(['super_admin', 'operations_team'])) {
            return true;
        }

        return $user->hasAnyRole(['vendor_admin', 'vendor_staff'])
            && $inquiry->vendor_id === $user->vendor_id;
    }

    public function delete(User $user, BulkInquiry $inquiry): bool
    {
        return $user->hasRole('super_admin');
    }
}
