<?php

declare(strict_types=1);

namespace App\Modules\Cart\Actions;

use App\Enums\SalesChannel;
use App\Modules\Cart\DTOs\AddCartItemDTO;
use App\Modules\Cart\Events\CartItemAdded;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Fuel\Models\Product;
use Illuminate\Support\Facades\DB;

class AddItemToCartAction
{
    public function execute(Cart $cart, AddCartItemDTO $dto): CartItem
    {
        return DB::transaction(function () use ($cart, $dto) {
            $product = Product::with('vendor')->findOrFail($dto->productId);

            // ── Guard: product must be orderable ──────────────────────────
            if (! $product->isOrderingEnabled()) {
                throw new \DomainException("Product '{$product->name}' is not available for ordering.");
            }

            // ── Resolve unit snapshot ─────────────────────────────────────
            $unitSnapshot = $product->unit_of_measure instanceof \App\Enums\UnitOfMeasure
                ? $product->unit_of_measure->value
                : ($product->unit_of_measure ?? 'units');

            // ── Guard: min order quantity ─────────────────────────────────
            $minQty = $product->min_order_quantity ?? 1.0;
            if ($dto->quantity < $minQty) {
                throw new \DomainException(
                    "Minimum order quantity for '{$product->name}' is {$minQty} {$unitSnapshot}."
                );
            }

            // ── Resolve sales channel from vendor first-party flag ─────────
            // Direct: FuelCab-owned vendor (is_first_party = true)
            // Marketplace: third-party approved vendor
            $channel = ($product->vendor && $product->vendor->is_first_party)
                ? SalesChannel::Direct
                : SalesChannel::Marketplace;

            // ── Add or increment item ─────────────────────────────────────
            // NOTE: Cart is no longer single-vendor locked.
            // Multi-vendor carts are valid; grouping happens at checkout time.
            $existing = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $dto->productId)
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                $existing->update(['quantity' => $existing->quantity + $dto->quantity]);
                $item = $existing->fresh();
            } else {
                $item = CartItem::create([
                    'cart_id'               => $cart->id,
                    'product_id'            => $dto->productId,
                    'quantity'              => $dto->quantity,
                    'price_snapshot'        => (float) $product->price_per_unit,
                    'unit_of_measure'       => $unitSnapshot,
                    // ─── Channel context ──────────────────────────────────
                    'sales_channel'         => $channel->value,
                    'vendor_id'             => $product->vendor_id,
                    'product_name_snapshot' => $product->name,
                ]);
            }

            event(new CartItemAdded($cart, $item));

            return $item;
        });
    }
}
