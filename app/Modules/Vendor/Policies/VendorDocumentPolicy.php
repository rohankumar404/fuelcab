<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Policies;

use App\Models\User;
use App\Modules\Vendor\Models\VendorDocument;
use App\Modules\Vendor\Enums\DocumentStatus;

class VendorDocumentPolicy
{
    /**
     * Vendor users can view their own documents; admins can view all.
     */
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'operations_team', 'vendor_admin', 'vendor_staff']);
    }

    public function view(User $user, VendorDocument $document): bool
    {
        if ($user->hasAnyRole(['super_admin', 'operations_team'])) {
            return true;
        }

        // Vendor users can only view their own vendor's documents — IDOR prevention
        return $user->hasAnyRole(['vendor_admin', 'vendor_staff'])
            && (string) $user->vendor_id === (string) $document->vendor_id;
    }

    /**
     * Vendor admin can upload their own documents.
     */
    public function create(User $user): bool
    {
        return $user->hasAnyRole(['super_admin', 'vendor_admin']);
    }

    /**
     * Vendors can update their own documents; admin can update any.
     */
    public function update(User $user, VendorDocument $document): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        return $user->hasRole('vendor_admin')
            && (string) $user->vendor_id === (string) $document->vendor_id;
    }

    /**
     * Only super admin can verify documents.
     */
    public function verify(User $user, VendorDocument $document): bool
    {
        return $user->hasRole('super_admin')
            && $document->status !== DocumentStatus::Verified;
    }

    /**
     * Only super admin can reject documents.
     */
    public function reject(User $user, VendorDocument $document): bool
    {
        return $user->hasRole('super_admin')
            && $document->status !== DocumentStatus::Rejected;
    }

    public function delete(User $user, VendorDocument $document): bool
    {
        if ($user->hasRole('super_admin')) {
            return true;
        }

        // Only pending documents can be deleted by vendor_admin
        return $user->hasRole('vendor_admin')
            && (string) $user->vendor_id === (string) $document->vendor_id
            && $document->status === DocumentStatus::Pending;
    }
}
