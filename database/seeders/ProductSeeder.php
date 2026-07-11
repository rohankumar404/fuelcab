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

        // ── Step 3: Upsert FuelCab Direct fuel categories ─────────────────
        $fuelCategoryId = $this->upsertCategory('Fuel', 'fuel', 'All liquid and gas fuel products');
        $lubeCategoryId = $this->upsertCategory('Lubricants', 'lubricants', 'Engine oils, greases and transmission fluids');
        $defCategoryId = $this->upsertCategory('DEF', 'def', 'Diesel Exhaust Fluid / AdBlue');

        // ── Step 4: Upsert 5 core direct fuel products (NO PETROL) ────────
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
                'sku'             => 'FC-DEF-001',
                'category_id'     => $defCategoryId,
                'vendor_id'       => $vendorId,
                'name'            => 'DEF (AdBlue)',
                'slug'            => 'def-adblue',
                'description'     => 'Premium Diesel Exhaust Fluid maintaining strict emission compliance in modern SCR diesel engines.',
                'price_per_unit'  => 45.00,
                'unit_of_measure' => 'liter',
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

        // Clean up any old Petrol product if left in database (to ensure no petrol reference survives)
        DB::table('products')->where('slug', 'petrol-ms')->delete();

        $this->command->info('✅ ProductSeeder: Direct fuel products seeded successfully (Petrol removed).');

        // ── Step 5: Seed Marketplace Categories & Subcategories ───────────
        $this->seedMarketplaceCategories();
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
            // Update to ensure is_first_party is true
            DB::table('vendors')->where('company_id', $companyId)->update([
                'is_first_party' => true,
                'brand_name' => 'FuelCab Direct',
            ]);
            return $existing->id;
        }

        $id = Str::uuid()->toString();
        DB::table('vendors')->insert([
            'id'                     => $id,
            'company_id'             => $companyId,
            'brand_name'             => 'FuelCab Direct',
            'is_first_party'         => true,
            'status'                 => 'approved',
            'commission_rate'        => 0.00,
            'service_radius_meters'  => 999999,
            'created_at'             => now(),
            'updated_at'             => now(),
        ]);

        return $id;
    }

    private function upsertCategory(string $name, string $slug, string $description, ?string $parentId = null, string $type = 'liquid', bool $isComingSoon = false): string
    {
        // Query by name or slug to respect name and slug uniqueness
        $existing = DB::table('categories')
            ->where('name', $name)
            ->orWhere('slug', $slug)
            ->first();

        if ($existing) {
            DB::table('categories')->where('id', $existing->id)->update([
                'name' => $name,
                'slug' => $slug,
                'description' => $description,
                'parent_id' => $parentId,
                'type' => $type,
                'is_coming_soon' => $isComingSoon,
                'updated_at' => now(),
            ]);
            return $existing->id;
        }

        $id = Str::uuid()->toString();
        DB::table('categories')->insert([
            'id'             => $id,
            'name'           => $name,
            'slug'           => $slug,
            'description'    => $description,
            'parent_id'      => $parentId,
            'type'           => $type,
            'is_coming_soon' => $isComingSoon,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        return $id;
    }

    private function seedMarketplaceCategories(): void
    {
        // 1. SOLID FUELS
        $solidId = $this->upsertCategory('Solid Fuels', 'solid-fuels', 'Industrial solid fuels and biomass options', null, 'solid');
        
        $solidSubcategories = [
            'Animal Tallow' => 'animal-tallow',
            'Chicken Tallow' => 'chicken-tallow',
            'Palm Stearin' => 'palm-stearin',
            'PP/HDPE Waste' => 'pp-hdpe-waste',
            'LDP/MHW' => 'ldp-mhw',
            'Tyre Waste' => 'tyre-waste',
            'Saw Dust' => 'saw-dust',
            'Wood Chips' => 'wood-chips',
            'Rice Husk' => 'rice-husk',
            'Coffee Husk' => 'coffee-husk',
            'Ground Nut Cell' => 'ground-nut-cell',
            'Soya Husk' => 'soya-husk',
            'Carbon Black' => 'carbon-black',
            'Bio-Mass Pellets' => 'bio-mass-pellets',
            'Starch Based Raw Materials' => 'starch-based-raw-materials',
            'RDF (Refuse Derived Fuel)' => 'rdf',
            'Biomass Briquettes / Bio Coal' => 'biomass-briquettes-bio-coal',
            'Other Bio Mass' => 'other-bio-mass',
        ];

        foreach ($solidSubcategories as $name => $slug) {
            $this->upsertCategory($name, $slug, "Industrial Solid Fuel: {$name}", $solidId, 'solid');
        }

        // 2. LIQUID FUELS
        $liquidId = $this->upsertCategory('Liquid Fuels', 'liquid-fuels', 'Industrial liquid fuels, additives and bio-liquids', null, 'liquid');
        
        $liquidSubcategories = [
            'High Speed Diesel' => 'marketplace-high-speed-diesel',
            'Bio Diesel (B-100)' => 'bio-diesel-b100',
            'LDO' => 'ldo',
            'Bio-LDO' => 'bio-ldo',
            'Furnace Oil' => 'furnace-oil',
            'Base Oil' => 'base-oil',
            'Bitumen' => 'bitumen',
            'UCO' => 'uco',
            'MTO' => 'mto',
            'MTO Cut' => 'mto-cut',
            'MHO' => 'mho',
            'Bio-Ethanol' => 'bio-ethanol',
            'Bio-Furnace Oil' => 'bio-furnace-oil',
            'Bio-Fuel Additives' => 'bio-fuel-additives',
            'Acid Oil' => 'acid-oil',
            'Other Vegetable Oil' => 'other-vegetable-oil',
            'Bio-Lubricants' => 'bio-lubricants',
            'Industrial Lubricants' => 'marketplace-lubricants',
            'New Bitumen' => 'new-bitumen',
        ];

        foreach ($liquidSubcategories as $name => $slug) {
            $this->upsertCategory($name, $slug, "Industrial Liquid Fuel: {$name}", $liquidId, 'liquid');
        }

        // 3. GAS FUELS
        $gasId = $this->upsertCategory('Gas Fuels', 'gas-fuels', 'Alternative and industrial gas fuels', null, 'gas');
        
        $gasSubcategories = [
            'Bio-CNG / CBG' => 'bio-cng-cbg',
            'CNG' => 'marketplace-cng',
            'LNG' => 'lng',
            'Green Hydrogen' => 'green-hydrogen',
        ];

        foreach ($gasSubcategories as $name => $slug) {
            $this->upsertCategory($name, $slug, "Industrial Gas Fuel: {$name}", $gasId, 'gas');
        }

        // 4. EV
        $this->upsertCategory('EV', 'ev', 'Electric Vehicle Charging network services', null, 'ev', true);

        $this->command->info('✅ ProductSeeder: Marketplace subcategories seeded successfully.');
    }
}
