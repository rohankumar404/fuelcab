<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Actions;

use App\Modules\Fuel\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class UpdateFuelPriceAction
{
    /**
     * Update the price of a fuel product.
     *
     * @param  float  $basePrice  New price per unit (before tax).
     * @param  float  $taxRate  Tax rate as a percentage (e.g. 18.0 for 18%).
     * @param  string  $currency  ISO currency code (default: INR).
     */
    public function execute(
        string $productId,
        float $basePrice,
        float $taxRate = 0.0,
        string $currency = 'INR',
    ): Product {
        if ($basePrice <= 0.0) {
            throw new InvalidArgumentException('Base price must be greater than zero.');
        }
        if ($taxRate < 0.0 || $taxRate > 100.0) {
            throw new InvalidArgumentException('Tax rate must be between 0 and 100.');
        }

        $taxAmount = round($basePrice * $taxRate / 100, 4);
        $effectivePrice = round($basePrice + $taxAmount, 4);

        return DB::transaction(function () use ($productId, $basePrice, $effectivePrice, $taxRate, $currency): Product {
            $product = Product::findOrFail($productId);

            $oldPrice = $product->price_per_unit;

            $product->update([
                'price_per_unit' => $effectivePrice,
            ]);

            Log::info('[UpdateFuelPriceAction] Fuel price updated.', [
                'product_id' => $productId,
                'old_price' => $oldPrice,
                'new_base_price' => $basePrice,
                'tax_rate' => $taxRate,
                'effective_price' => $effectivePrice,
                'currency' => $currency,
            ]);

            return $product->fresh();
        });
    }
}
