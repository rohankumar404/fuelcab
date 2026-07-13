<?php

declare(strict_types=1);

namespace App\Modules\Checkout\DTOs;

use App\Modules\Cart\Models\CartItem;
use Illuminate\Database\Eloquent\Collection;

/**
 * Represents one fulfillment group produced by grouping cart items
 * by (sales_channel + vendor_id).
 *
 * Each group maps to exactly ONE Order at checkout.
 */
final class FulfillmentGroupDTO
{
    /**
     * @param string          $salesChannel  e.g. 'direct' | 'marketplace'
     * @param string|null     $vendorId      UUID of the fulfilling vendor (null = FuelCab Direct)
     * @param Collection      $items         CartItem collection for this group
     * @param float           $subtotal      Sum of line totals for this group
     */
    public function __construct(
        public readonly string     $salesChannel,
        public readonly ?string    $vendorId,
        public readonly Collection $items,
        public readonly float      $subtotal,
    ) {}

    public static function fromGroup(array $group): self
    {
        /** @var Collection<CartItem> $items */
        $items    = $group['items'];
        $subtotal = (float) $items->sum(fn (CartItem $i) => $i->quantity * $i->price_snapshot);

        return new self(
            salesChannel: $group['sales_channel'],
            vendorId:     $group['vendor_id'],
            items:        $items,
            subtotal:     round($subtotal, 2),
        );
    }

    public function isDirectChannel(): bool
    {
        return $this->salesChannel === 'direct';
    }

    public function isMarketplaceChannel(): bool
    {
        return $this->salesChannel === 'marketplace';
    }
}
