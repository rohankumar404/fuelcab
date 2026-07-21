<?php

declare(strict_types=1);

namespace App\Modules\Cart\Models;

use App\Enums\SalesChannel;
use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
     * Total price using snapshotted prices.
     */
    public function getTotal(): float
    {
        return (float) round($this->items->sum(fn ($item) => $item->quantity * $item->price_snapshot), 2);
    }

    /**
     * Total item count.
     */
    public function getItemCount(): int
    {
        return $this->items->count();
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
     * @return array<int, array{sales_channel: string, vendor_id: string|null, seller_name: string, is_first_party: bool, items: Collection, subtotal: float}>
     */
    public function groupByFulfillment(): array
    {
        $groups = [];

        foreach ($this->items as $item) {
            $channel  = $item->sales_channel instanceof SalesChannel
                ? $item->sales_channel->value
                : ($item->sales_channel ?? SalesChannel::Direct->value);

            $vendorId = $item->vendor_id;
            $key      = $channel . '|' . ($vendorId ?? 'direct');

            if (! isset($groups[$key])) {
                $sellerName   = $item->getSellerName();
                $isFirstParty = $channel === 'direct' || ($item->vendor && $item->vendor->is_first_party);

                $groups[$key] = [
                    'sales_channel'  => $channel,
                    'vendor_id'      => $vendorId,
                    'seller_name'    => $sellerName,
                    'is_first_party' => $isFirstParty,
                    'items'          => new Collection(),
                    'subtotal'       => 0.0,
                ];
            }

            $groups[$key]['items']->push($item);
            $groups[$key]['subtotal'] = round($groups[$key]['subtotal'] + $item->getLineTotal(), 2);
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
