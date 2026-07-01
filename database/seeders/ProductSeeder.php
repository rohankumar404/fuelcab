<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        // ── Step 1: Upsert FuelCab Platform Company ───────────────────────
        $companyId = $this->upsertCompany();

        // ── Step 2: Upsert FuelCab Platform Vendor ───────────────────────
        $vendorId = $this->upsertVendor($companyId);

        // ── Step 3: Upsert fuel categories ───────────────────────────────
        $fuelCategoryId = $this->upsertCategory('Fuel', 'fuel', 'All liquid and gas fuel products');
        $lubeCategoryId = $this->upsertCategory('Lubricants', 'lubricants', 'Engine oils, greases and transmission fluids');

        // ── Step 4: Upsert 5 core fuel products ──────────────────────────
        $products = [
            [
                'sku'             => 'FC-DSL-001',
                'category_id'     => $fuelCategoryId,
                'vendor_id'       => $vendorId,
                'name'            => 'Diesel (HSD)',
                'slug'            => 'diesel-hsd',
                'description'     => 'High-speed ultra-low sulfur diesel for heavy machinery, generators, and transport fleets. Minimum order: 100 liters.',
                'price_per_unit'  => 88.50,
                'unit_of_measure' => 'liter',
                'status'          => 'active',
                'is_active'       => true,
            ],
            [
                'sku'             => 'FC-PET-001',
                'category_id'     => $fuelCategoryId,
                'vendor_id'       => $vendorId,
                'name'            => 'Petrol (MS)',
                'slug'            => 'petrol-ms',
                'description'     => 'High-octane motor spirit for commercial passenger fleets and lightweight generators.',
                'price_per_unit'  => 94.72,
                'unit_of_measure' => 'liter',
                'status'          => 'soon',
                'is_active'       => false,
            ],
            [
                'sku'             => 'FC-CNG-001',
                'category_id'     => $fuelCategoryId,
                'vendor_id'       => $vendorId,
                'name'            => 'CNG',
                'slug'            => 'cng',
                'description'     => 'Compressed Natural Gas for eco-friendly public transport and green logistics.',
                'price_per_unit'  => 76.00,
                'unit_of_measure' => 'kg',
                'status'          => 'soon',
                'is_active'       => false,
            ],
            [
                'sku'             => 'FC-LPG-001',
                'category_id'     => $fuelCategoryId,
                'vendor_id'       => $vendorId,
                'name'            => 'LPG',
                'slug'            => 'lpg',
                'description'     => 'Liquefied Petroleum Gas for industrial heating and commercial kitchen systems.',
                'price_per_unit'  => 860.00,
                'unit_of_measure' => 'cylinder',
                'status'          => 'soon',
                'is_active'       => false,
            ],
            [
                'sku'             => 'FC-LUB-001',
                'category_id'     => $lubeCategoryId,
                'vendor_id'       => $vendorId,
                'name'            => 'Lubricants',
                'slug'            => 'lubricants',
                'description'     => 'High-grade engine oils, transmission fluids, and greases for heavy industrial assets.',
                'price_per_unit'  => 450.00,
                'unit_of_measure' => 'liter',
                'status'          => 'soon',
                'is_active'       => false,
            ],
        ];

        foreach ($products as $product) {
            $existing = DB::table('products')
                ->where('sku', $product['sku'])
                ->where('vendor_id', $vendorId)
                ->first();

            if ($existing) {
                DB::table('products')
                    ->where('id', $existing->id)
                    ->update(array_merge($product, ['updated_at' => now()]));
            } else {
                DB::table('products')->insert(array_merge($product, [
                    'id'         => Str::uuid()->toString(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        $this->command->info('✅ ProductSeeder: 5 fuel products seeded successfully.');
        $this->command->info('   — Diesel (HSD)  → ACTIVE');
        $this->command->info('   — Petrol (MS)   → COMING SOON');
        $this->command->info('   — CNG           → COMING SOON');
        $this->command->info('   — LPG           → COMING SOON');
        $this->command->info('   — Lubricants    → COMING SOON');
    }

    private function upsertCompany(): string
    {
        $existing = DB::table('companies')->where('name', 'FuelCab Platform')->first();
        if ($existing) {
            return $existing->id;
        }

        $id = Str::uuid()->toString();
        DB::table('companies')->insert([
            'id'         => $id,
            'name'       => 'FuelCab Platform',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function upsertVendor(string $companyId): string
    {
        $existing = DB::table('vendors')->where('company_id', $companyId)->first();
        if ($existing) {
            return $existing->id;
        }

        $id = Str::uuid()->toString();
        DB::table('vendors')->insert([
            'id'                     => $id,
            'company_id'             => $companyId,
            'brand_name'             => 'FuelCab Direct',
            'status'                 => 'approved',
            'commission_rate'        => 0.00,
            'service_radius_meters'  => 999999,
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        return $id;
    }

    private function upsertCategory(string $name, string $slug, string $description): string
    {
        $existing = DB::table('categories')->where('slug', $slug)->first();
        if ($existing) {
            return $existing->id;
        }

        $id = Str::uuid()->toString();
        DB::table('categories')->insert([
            'id'          => $id,
            'name'        => $name,
            'slug'        => $slug,
            'description' => $description,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);

        return $id;
    }
}
