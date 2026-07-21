<?php

declare(strict_types=1);

namespace App\Modules\Cart\Models;

use App\Enums\SalesChannel;
use App\Enums\UnitOfMeasure;
use App\Modules\Fuel\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorListing;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartItem extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id',
        'product_id',
        'vendor_listing_id',
        'quantity',
        'price_snapshot',
        'unit_of_measure',
        // ─── Channel & seller context ─────────────────────────────────────
        'sales_channel',
        'vendor_id',
        'product_name_snapshot',
        'product_sku_snapshot',
        'unit_snapshot',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity'       => 'float',
            'price_snapshot' => 'float',
            'sales_channel'  => SalesChannel::class,
        ];
    }

    // ─── Relationships ────────────────────────────────────────────────────

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function vendorListing(): BelongsTo
    {
        return $this->belongsTo(VendorListing::class, 'vendor_listing_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    // ─── Business Logic ───────────────────────────────────────────────────

    /**
     * Line total using snapshotted price.
     */
    public function getLineTotal(): float
    {
        return round($this->quantity * $this->price_snapshot, 2);
    }

    /**
     * Display name of the item seller.
     */
    public function getSellerName(): string
    {
        if ($this->isDirectChannel() || ! $this->vendor_id) {
            return 'FuelCab Direct';
        }

        return $this->vendor?->brand_name ?? 'Verified Vendor';
    }

    /**
     * Whether the live price differs from snapshot.
     */
    public function isPriceStale(): bool
    {
        if ($this->vendorListing) {
            return (float) $this->vendorListing->base_price !== (float) $this->price_snapshot;
        }

        if ($this->product) {
            return (float) $this->product->price_per_unit !== (float) $this->price_snapshot;
        }

        return false;
    }

    /**
     * Whether this item belongs to the FuelCab Direct channel.
     */
    public function isDirectChannel(): bool
    {
        return $this->sales_channel === SalesChannel::Direct
            || (is_string($this->sales_channel) && $this->sales_channel === 'direct');
    }

    /**
     * Whether this item belongs to the Marketplace channel.
     */
    public function isMarketplaceChannel(): bool
    {
        return $this->sales_channel === SalesChannel::Marketplace
            || (is_string($this->sales_channel) && $this->sales_channel === 'marketplace');
    }
}
