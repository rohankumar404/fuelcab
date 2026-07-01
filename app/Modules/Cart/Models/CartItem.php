<?php

declare(strict_types=1);

namespace App\Modules\Cart\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;
use App\Modules\Fuel\Models\Product;

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
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity'       => 'float',
            'price_snapshot' => 'float',
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
}
