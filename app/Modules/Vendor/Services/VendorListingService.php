<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Services;

use App\Enums\ListingStatus;
use App\Models\User;
use App\Modules\Vendor\Models\VendorListing;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class VendorListingService
{
    /**
     * Create a new DRAFT listing for the given vendor.
     * vendor_id is injected from service — never from user payload.
     */
    public function create(array $data, string $vendorId): VendorListing
    {
        return DB::transaction(function () use ($data, $vendorId) {
            $data['vendor_id']       = $vendorId;
            $data['approval_status'] = ListingStatus::Draft->value;
            $data['slug']            = $this->uniqueSlug($data['listing_title'], $data['slug'] ?? null);

            return VendorListing::create($data);
        });
    }

    /**
     * Update a DRAFT or REJECTED listing.
     */
    public function update(VendorListing $listing, array $data): VendorListing
    {
        return DB::transaction(function () use ($listing, $data) {
            // Prevent vendor_id tampering — always stripped before reaching here
            unset($data['vendor_id'], $data['approval_status'], $data['reviewed_by'], $data['reviewed_at'], $data['approved_at']);

            if (isset($data['listing_title']) || isset($data['slug'])) {
                $title             = $data['listing_title'] ?? $listing->listing_title;
                $slug              = $data['slug'] ?? null;
                $data['slug']      = $this->uniqueSlug($title, $slug, $listing->id);
            }

            $listing->update($data);

            return $listing->fresh();
        });
    }

    /**
     * Transition DRAFT or REJECTED → PENDING_APPROVAL.
     */
    public function submit(VendorListing $listing): VendorListing
    {
        $listing->update([
            'approval_status' => ListingStatus::PendingApproval->value,
            'rejection_reason' => null,
        ]);

        return $listing->fresh();
    }

    /**
     * Super Admin approves a PENDING_APPROVAL listing.
     */
    public function approve(VendorListing $listing, User $reviewer): VendorListing
    {
        $listing->update([
            'approval_status' => ListingStatus::Approved->value,
            'reviewed_by'     => $reviewer->id,
            'reviewed_at'     => now(),
            'approved_at'     => now(),
            'rejection_reason'=> null,
        ]);

        return $listing->fresh();
    }

    /**
     * Super Admin rejects a PENDING_APPROVAL listing with a reason.
     */
    public function reject(VendorListing $listing, User $reviewer, string $reason): VendorListing
    {
        $listing->update([
            'approval_status'  => ListingStatus::Rejected->value,
            'reviewed_by'      => $reviewer->id,
            'reviewed_at'      => now(),
            'rejection_reason' => $reason,
        ]);

        return $listing->fresh();
    }

    /**
     * Super Admin suspends an APPROVED listing.
     */
    public function suspend(VendorListing $listing): VendorListing
    {
        $listing->update([
            'approval_status' => ListingStatus::Suspended->value,
        ]);

        return $listing->fresh();
    }

    /**
     * Vendor updates available stock quantity.
     */
    public function updateInventory(VendorListing $listing, float $quantity): VendorListing
    {
        $listing->update(['available_quantity' => $quantity]);
        return $listing->fresh();
    }

    /**
     * Vendor updates base price.
     */
    public function updatePrice(VendorListing $listing, float $price): VendorListing
    {
        $listing->update(['base_price' => $price]);
        return $listing->fresh();
    }

    /**
     * Toggle is_featured for a listing (super admin only).
     */
    public function toggleFeatured(VendorListing $listing): VendorListing
    {
        $listing->update(['is_featured' => ! $listing->is_featured]);
        return $listing->fresh();
    }

    /**
     * Public marketplace — only APPROVED + is_active listings.
     */
    public function getPublicListings(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = VendorListing::with(['vendor', 'marketplaceProduct.category'])
            ->public();

        if (! empty($filters['marketplace_product_id'])) {
            $query->where('marketplace_product_id', $filters['marketplace_product_id']);
        }

        if (! empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (! empty($filters['dispatch_location'])) {
            $query->where('dispatch_location', 'ilike', '%' . $filters['dispatch_location'] . '%');
        }

        if (! empty($filters['featured'])) {
            $query->where('is_featured', true);
        }

        if (! empty($filters['search'])) {
            $query->where('listing_title', 'ilike', '%' . $filters['search'] . '%');
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Vendor's own listings — all statuses.
     */
    public function getVendorListings(string $vendorId, array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = VendorListing::with(['marketplaceProduct'])
            ->forVendor($vendorId);

        if (! empty($filters['approval_status'])) {
            $query->where('approval_status', $filters['approval_status']);
        }

        return $query->latest()->paginate($perPage);
    }

    /**
     * Admin view — all listings with optional filters.
     */
    public function getAdminListings(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        $query = VendorListing::with(['vendor', 'marketplaceProduct']);

        if (! empty($filters['approval_status'])) {
            $query->where('approval_status', $filters['approval_status']);
        }

        if (! empty($filters['vendor_id'])) {
            $query->where('vendor_id', $filters['vendor_id']);
        }

        if (! empty($filters['marketplace_product_id'])) {
            $query->where('marketplace_product_id', $filters['marketplace_product_id']);
        }

        return $query->latest()->paginate($perPage);
    }

    // ── Private helpers ──────────────────────────────────────────────────────

    private function uniqueSlug(string $title, ?string $proposed, ?string $excludeId = null): string
    {
        $base = Str::slug($proposed ?? $title);
        $slug = $base;
        $i    = 1;

        while (
            VendorListing::where('slug', $slug)
                ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId))
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }
}
