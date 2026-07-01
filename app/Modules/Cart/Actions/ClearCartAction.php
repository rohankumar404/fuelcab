<?php

declare(strict_types=1);

namespace App\Modules\Cart\Actions;

use App\Modules\Cart\Events\CartCleared;
use App\Modules\Cart\Models\Cart;
use Illuminate\Support\Facades\DB;

class ClearCartAction
{
    public function execute(Cart $cart): void
    {
        DB::transaction(function () use ($cart) {
            // Soft-delete all items
            $cart->items()->each(fn ($item) => $item->delete());

            // Unlock vendor
            $cart->update(['vendor_id' => null]);

            event(new CartCleared($cart));
        });
    }
}
