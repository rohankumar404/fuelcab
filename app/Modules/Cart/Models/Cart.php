<?php

declare(strict_types=1);

namespace App\Modules\Cart\Models;

use App\Enums\SalesChannel;
use Illuminate\Database\Eloquent\Collection;
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

    /**
     * Group cart items by (sales_channel + vendor_id) for multi-order checkout.
     *
     * Returns an array of arrays, each representing one fulfillment group:
     * [
     *   ['sales_channel' => 'direct',      'vendor_id' => 'uuid-A', 'items' => Collection],
     *   ['sales_channel' => 'marketplace', 'vendor_id' => 'uuid-B', 'items' => Collection],
     *   ['sales_channel' => 'marketplace', 'vendor_id' => 'uuid-C', 'items' => Collection],
     * ]
     *
     * @return array<int, array{sales_channel: string, vendor_id: string|null, items: Collection}>
     */
    public function groupByFulfillment(): array
    {
        $groups = [];

        foreach ($this->items as $item) {
            $channel  = $item->sales_channel instanceof SalesChannel
                ? $item->sales_channel->value
                : ($item->sales_channel ?? SalesChannel::Direct->value);
            $vendorId = $item->vendor_id;
            $key      = $channel . '|' . ($vendorId ?? 'null');

            if (! isset($groups[$key])) {
                $groups[$key] = [
                    'sales_channel' => $channel,
                    'vendor_id'     => $vendorId,
                    'items'         => new Collection(),
                ];
            }

            $groups[$key]['items']->push($item);
        }

        return array_values($groups);
    }

    /**
     * Whether this cart contains items from more than one fulfillment context.
     */
    public function hasMultipleVendors(): bool
    {
        return count($this->groupByFulfillment()) > 1;
    }
}
