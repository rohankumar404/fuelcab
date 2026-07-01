<?php

declare(strict_types=1);

namespace App\Modules\Cart\Actions;

use App\Modules\Cart\Events\CartItemRemoved;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use Illuminate\Support\Facades\DB;

class RemoveCartItemAction
{
    public function execute(Cart $cart, CartItem $item): void
    {
        DB::transaction(function () use ($cart, $item) {
            $item->delete(); // soft delete

            // If cart is now empty, unlock vendor
            $cart->refresh();
            if ($cart->items()->count() === 0) {
                $cart->update(['vendor_id' => null]);
            }

            event(new CartItemRemoved($cart, $item));
        });
    }
}
