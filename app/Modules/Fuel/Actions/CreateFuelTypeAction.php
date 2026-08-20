<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Actions;

use App\Modules\Fuel\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use InvalidArgumentException;

class CreateFuelTypeAction
{
    /**
     * Create a new fuel product record.
     *
     * @param  array{
     *   name: string,
     *   category_id: string,
     *   description: string|null,
     *   status: string|null,
     *   price_per_unit: float|null,
     *   unit_of_measure: string|null,
     *   vendor_id: string|null,
     *   created_by: string|null,
     * } $data
     */
    public function execute(array $data): Product
    {
        $name = trim($data['name'] ?? '');
        if (empty($name)) {
            throw new InvalidArgumentException('Fuel type name is required.');
        }

        $product = Product::create([
            'name' => $name,
            'slug' => Str::slug($name).'-'.Str::random(4),
            'sku' => 'FUEL-'.strtoupper(Str::random(8)),
            'category_id' => $data['category_id'],
            'vendor_id' => $data['vendor_id'] ?? null,
            'description' => $data['description'] ?? null,
            'short_description' => $data['description'] ?? null,
            'price_per_unit' => $data['price_per_unit'] ?? 0.0,
            'unit_of_measure' => $data['unit_of_measure'] ?? 'liter',
            'status' => $data['status'] ?? 'active',
            'is_active' => true,
            'created_by' => $data['created_by'] ?? null,
        ]);

        Log::info('[CreateFuelTypeAction] Fuel product created.', [
            'product_id' => $product->id,
            'name' => $product->name,
            'category_id' => $product->category_id,
        ]);

        return $product;
    }
}
