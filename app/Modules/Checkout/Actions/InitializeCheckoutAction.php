<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Actions;

use App\Modules\Cart\Models\Cart;
use App\Modules\Checkout\Models\Checkout;
use Illuminate\Support\Facades\DB;

class InitializeCheckoutAction
{
    public function execute(string $userId, string $cartId): Checkout
    {
        return DB::transaction(function () use ($userId, $cartId) {
            $cart = Cart::with(['items'])->where('user_id', $userId)->findOrFail($cartId);

            if ($cart->isEmpty()) {
                throw new \DomainException("Cannot initialize checkout with an empty cart.");
            }

            // Find or create a draft checkout session for this cart
            $checkout = Checkout::updateOrCreate(
                [
                    'user_id' => $userId,
                    'cart_id' => $cartId,
                    'status'  => 'draft',
                ],
                [
                    'vendor_id'       => $cart->vendor_id,
                    'subtotal_amount' => $cart->getTotal(),
                    'total_amount'    => $cart->getTotal(),
                ]
            );

            return $checkout;
        });
    }
}
