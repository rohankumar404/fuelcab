<?php

declare(strict_types=1);

namespace App\Modules\Cart\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;
use App\Models\User;
use App\Modules\Vendor\Models\Vendor;

class Cart extends Model
{
    use HasUuid, SoftDeletes;

    protected $table = 'carts';

    protected $fillable = [
        'user_id',
        'vendor_id',
        'guest_token',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [];
    }

    // ─── Relationships ────────────────────────────────────────────────────

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CartItem::class)->whereNull('deleted_at');
    }

    // ─── Business Logic ───────────────────────────────────────────────────

    /**
     * Whether the cart is locked to a specific vendor.
     */
    public function isLockedToVendor(): bool
    {
        return $this->vendor_id !== null;
    }

    /**
     * Total price using snapshotted prices.
     */
    public function getTotal(): float
    {
        return (float) $this->items->sum(fn ($item) => $item->quantity * $item->price_snapshot);
    }

    /**
     * Whether the cart has any items.
     */
    public function isEmpty(): bool
    {
        return $this->items->isEmpty();
    }
}
