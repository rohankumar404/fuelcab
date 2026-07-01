<?php

declare(strict_types=1);

namespace App\Modules\Cart\Actions;

use App\Modules\Cart\DTOs\AddCartItemDTO;
use App\Modules\Cart\Events\CartItemAdded;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Fuel\Models\Product;
use Illuminate\Support\Facades\DB;

class AddItemToCartAction
{
    private const MIN_QUANTITY = 100.0;

    public function execute(Cart $cart, AddCartItemDTO $dto): CartItem
    {
        return DB::transaction(function () use ($cart, $dto) {
            $product = Product::findOrFail($dto->productId);

            // Validate product is orderable
            if (! $product->isOrderingEnabled()) {
                throw new \DomainException("Product '{$product->name}' is not available for ordering.");
            }

            // Enforce minimum quantity
            if ($dto->quantity < self::MIN_QUANTITY) {
                throw new \DomainException("Minimum order quantity is " . self::MIN_QUANTITY . " litres.");
            }

            // Enforce single-vendor cart constraint
            if ($cart->vendor_id && $cart->vendor_id !== $product->vendor_id) {
                throw new \DomainException(
                    "Cart is locked to a different vendor. Clear the cart before adding products from a new vendor."
                );
            }

            // Lock cart to this vendor
            if (! $cart->vendor_id) {
                $cart->update(['vendor_id' => $product->vendor_id]);
            }

            // Add or increment item
            $existing = CartItem::where('cart_id', $cart->id)
                ->where('product_id', $dto->productId)
                ->whereNull('deleted_at')
                ->first();

            if ($existing) {
                $existing->update(['quantity' => $existing->quantity + $dto->quantity]);
                $item = $existing->fresh();
            } else {
                $item = CartItem::create([
                    'cart_id'        => $cart->id,
                    'product_id'     => $dto->productId,
                    'quantity'       => $dto->quantity,
                    'price_snapshot' => $product->price_per_unit,
                    'unit_of_measure'=> $product->unit_of_measure,
                ]);
            }

            event(new CartItemAdded($cart, $item));

            return $item;
        });
    }
}
