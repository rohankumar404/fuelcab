<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Models;

use App\Enums\ListingStatus;
use App\Enums\UnitOfMeasure;
use App\Models\User;
use App\Modules\Fuel\Models\MarketplaceProduct;
use App\Traits\Auditable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

class VendorListing extends Model
{
    use HasUuid, Auditable, SoftDeletes;

    protected $table = 'vendor_listings';

    protected $fillable = [
        'vendor_id',
        'marketplace_product_id',
        'listing_title',
        'slug',
        'sku',
        'short_description',
        'full_description',
        'product_images',
        'min_order_quantity',
        'max_order_quantity',
        'unit',
        'available_quantity',
        'base_price',
        'tax_rate',
        'tax_inclusive',
        'dispatch_location',
        'serviceable_locations',
        'estimated_dispatch_hours',
        'quality_specifications',
        'certificate_documents',
        'is_active',
        'is_featured',
        'approval_status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
        'approved_at',
    ];

    protected $casts = [
        'product_images'          => 'array',
        'serviceable_locations'   => 'array',
        'quality_specifications'  => 'array',
        'certificate_documents'   => 'array',
        'unit'                    => UnitOfMeasure::class,
        'approval_status'         => ListingStatus::class,
        'tax_inclusive'           => 'boolean',
        'is_active'               => 'boolean',
        'is_featured'             => 'boolean',
        'min_order_quantity'      => 'decimal:4',
        'max_order_quantity'      => 'decimal:4',
        'available_quantity'      => 'decimal:4',
        'base_price'              => 'decimal:4',
        'tax_rate'                => 'decimal:2',
        'reviewed_at'             => 'datetime',
        'approved_at'             => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function marketplaceProduct(): BelongsTo
    {
        return $this->belongsTo(MarketplaceProduct::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // ── Scopes ───────────────────────────────────────────────────────────────

    /**
     * Only APPROVED and active listings — used by public marketplace API.
     */
    public function scopePublic(Builder $query): Builder
    {
        return $query
            ->where('approval_status', ListingStatus::Approved->value)
            ->where('is_active', true);
    }

    /**
     * Scope listings to a specific vendor.
     */
    public function scopeForVendor(Builder $query, string $vendorId): Builder
    {
        return $query->where('vendor_id', $vendorId);
    }

    /**
     * Listings awaiting admin review.
     */
    public function scopePending(Builder $query): Builder
    {
        return $query->where('approval_status', ListingStatus::PendingApproval->value);
    }

    /**
     * Featured approved listings.
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query
            ->where('approval_status', ListingStatus::Approved->value)
            ->where('is_active', true)
            ->where('is_featured', true);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isEditable(): bool
    {
        return $this->approval_status->isEditable();
    }

    public function isApproved(): bool
    {
        return $this->approval_status === ListingStatus::Approved;
    }

    public function priceWithTax(): float
    {
        $price = (float) $this->base_price;
        if ($this->tax_inclusive) {
            return $price;
        }
        return round($price + ($price * ((float) $this->tax_rate / 100)), 4);
    }
}
