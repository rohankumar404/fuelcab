<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Actions;

use App\Models\Address;
use App\Modules\Checkout\Models\Checkout;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Support\Facades\DB;

class CalculateCheckoutSummaryAction
{
    public function execute(string $userId, string $checkoutId): Checkout
    {
        return DB::transaction(function () use ($userId, $checkoutId) {
            $checkout = Checkout::with(['cart.items', 'address'])->where('user_id', $userId)->where('status', 'draft')->findOrFail($checkoutId);

            $subtotal = $checkout->cart->getTotal();
            $deliveryFee = $checkout->delivery_fee;

            // Compute tax (Indian GST: 18%)
            $gstRate = 0.18;
            $taxableAmount = $subtotal + $deliveryFee;
            $taxAmount = $taxableAmount * $gstRate;

            $totalAmount = $subtotal + $deliveryFee + $taxAmount;

            $checkout->update([
                'subtotal_amount' => round($subtotal, 2),
                'tax_amount'      => round($taxAmount, 2),
                'total_amount'    => round($totalAmount, 2),
            ]);

            return $checkout;
        });
    }
}
