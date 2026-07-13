<?php

declare(strict_types=1);

namespace App\Modules\Cart\Models;

use App\Enums\SalesChannel;
use App\Enums\UnitOfMeasure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;
use App\Modules\Fuel\Models\Product;
use App\Modules\Vendor\Models\Vendor;

class CartItem extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'cart_items';

    protected $fillable = [
        'cart_id',
        'product_id',
        'quantity',
        'price_snapshot',
        'unit_of_measure',
        // ─── Channel context ───────────────────────────────────────────────
        'sales_channel',
        'vendor_id',
        'product_name_snapshot',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity'        => 'float',
            'price_snapshot'  => 'float',
            'sales_channel'   => SalesChannel::class,
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
     * Whether the live product price differs from snapshot.
     */
    public function isPriceStale(): bool
    {
        return $this->product && (float) $this->product->price_per_unit !== $this->price_snapshot;
    }

    /**
     * Whether this item belongs to the FuelCab Direct channel.
     */
    public function isDirectChannel(): bool
    {
        return $this->sales_channel === SalesChannel::Direct;
    }

    /**
     * Whether this item belongs to the Marketplace channel.
     */
    public function isMarketplaceChannel(): bool
    {
        return $this->sales_channel === SalesChannel::Marketplace;
    }
}
