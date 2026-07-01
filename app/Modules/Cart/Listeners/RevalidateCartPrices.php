<?php

declare(strict_types=1);

namespace App\Modules\Cart\Listeners;

use App\Modules\Cart\Events\CartItemAdded;
use Illuminate\Support\Facades\Log;

class RevalidateCartPrices
{
    public function handle(CartItemAdded $event): void
    {
        $item = $event->item;

        if (! $item->product) {
            return;
        }

        $livePrice     = (float) $item->product->price_per_unit;
        $snapshotPrice = $item->price_snapshot;

        if ($livePrice !== $snapshotPrice) {
            // Update snapshot to live price
            $item->update(['price_snapshot' => $livePrice]);

            Log::info('CartService: Price snapshot updated on add', [
                'cart_item_id'   => $item->id,
                'product_id'     => $item->product_id,
                'snapshot_was'   => $snapshotPrice,
                'snapshot_now'   => $livePrice,
            ]);
        }
    }
}
