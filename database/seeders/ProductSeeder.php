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
                'sku'                => 'FC-DSL-001',
                'category_id'        => $fuelCategoryId,
                'vendor_id'          => $vendorId,
                'name'               => 'Diesel (HSD)',
                'slug'               => 'diesel-hsd',
                'description'        => 'High-speed ultra-low sulfur diesel for heavy machinery, generators, and transport fleets. Minimum order: 100 liters.',
                'short_description'  => 'High-speed ultra-low sulfur diesel for heavy machinery, generators, and transport fleets.',
                'full_description'   => 'Diesel HSD is a premium quality hydrocarbon fuel suitable for high-speed compression ignition engines. Optimized for industrial applications, power generators, agriculture, logistics fleets, and heavy equipment.',
                'price_per_unit'     => 88.50,
                'unit_of_measure'    => 'litres',
                'status'             => 'active',
                'is_active'          => true,
                'ordering_enabled'   => true,
                'is_coming_soon'     => false,
                'is_featured'        => true,
                'icon'               => 'droplet',
                'min_order_quantity' => 100.0000,
                'max_order_quantity' => 50000.0000,
                'seo_title'          => 'Buy Bulk High-Speed Diesel (HSD) Online - FuelCab',
                'seo_description'    => 'Order high-speed ultra-low sulfur diesel (HSD) for commercial and industrial generators, construction sites, and logistics fleets.',
                'display_order'      => 1,
            ],
            [
                'sku'                => 'FC-CNG-001',
                'category_id'        => $fuelCategoryId,
                'vendor_id'          => $vendorId,
                'name'               => 'CNG',
                'slug'               => 'cng',
                'description'        => 'Compressed Natural Gas for eco-friendly public transport and green logistics.',
                'short_description'  => 'Compressed Natural Gas for eco-friendly public transport and green logistics.',
                'full_description'   => 'High quality compressed natural gas (CNG) for commercial gas-powered fleets and clean energy industrial burners.',
                'price_per_unit'     => 76.00,
                'unit_of_measure'    => 'kilograms',
                'status'             => 'soon',
                'is_active'          => false,
                'ordering_enabled'   => false,
                'is_coming_soon'     => true,
                'is_featured'        => false,
                'icon'               => 'wind',
                'min_order_quantity' => 50.0000,
                'max_order_quantity' => 5000.0000,
                'seo_title'          => 'Compressed Natural Gas (CNG) B2B Supply - FuelCab',
                'seo_description'    => 'Get eco-friendly compressed natural gas (CNG) delivered to your gas-powered vehicle hubs and logistics yards.',
                'display_order'      => 2,
            ],
            [
                'sku'                => 'FC-LPG-001',
                'category_id'        => $fuelCategoryId,
                'vendor_id'          => $vendorId,
                'name'               => 'LPG',
                'slug'               => 'lpg',
                'description'        => 'Liquefied Petroleum Gas for industrial heating and commercial kitchen systems.',
                'short_description'  => 'Liquefied Petroleum Gas for industrial heating and commercial kitchen systems.',
                'full_description'   => 'Liquefied Petroleum Gas (LPG) sourced for commercial heating, high-scale kitchens, and specific manufacturing processes.',
                'price_per_unit'     => 860.00,
                'unit_of_measure'    => 'metric_tonnes',
                'status'             => 'soon',
                'is_active'          => false,
                'ordering_enabled'   => false,
                'is_coming_soon'     => true,
                'is_featured'        => false,
                'icon'               => 'flame',
                'min_order_quantity' => 1.0000,
                'max_order_quantity' => 100.0000,
                'seo_title'          => 'Commercial Liquefied Petroleum Gas (LPG) - FuelCab',
                'seo_description'    => 'Bulk commercial LPG logistics and supply services for heavy industrial cooking, heating and manufacturing.',
                'display_order'      => 3,
            ],
            [
                'sku'                => 'FC-DEF-001',
                'category_id'        => $defCategoryId,
                'vendor_id'          => $vendorId,
                'name'               => 'DEF (AdBlue)',
                'slug'               => 'def-adblue',
                'description'     => 'Premium Diesel Exhaust Fluid maintaining strict emission compliance in modern SCR diesel engines.',
                'short_description'  => 'Premium Diesel Exhaust Fluid maintaining strict emission compliance in modern SCR engines.',
                'full_description'   => 'Highly pure 32.5% urea solution required by SCR exhaust systems in modern diesel engines to reduce nitrogen oxides.',
                'price_per_unit'     => 45.00,
                'unit_of_measure'    => 'litres',
                'status'             => 'soon',
                'is_active'          => false,
                'ordering_enabled'   => false,
                'is_coming_soon'     => true,
                'is_featured'        => false,
                'icon'               => 'shield-check',
                'min_order_quantity' => 20.0000,
                'max_order_quantity' => 5000.0000,
                'seo_title'          => 'DEF AdBlue Bulk Supply - FuelCab',
                'seo_description'    => 'Top grade Diesel Exhaust Fluid (AdBlue) to ensure emissions compliance across your industrial diesel transport fleets.',
                'display_order'      => 4,
            ],
            [
                'sku'                => 'FC-LUB-001',
                'category_id'        => $lubeCategoryId,
                'vendor_id'          => $vendorId,
                'name'               => 'Lubricants',
                'slug'               => 'lubricants',
                'description'        => 'High-grade engine oils, transmission fluids, and greases for heavy industrial assets.',
                'short_description'  => 'High-grade engine oils, transmission fluids, and greases for heavy industrial assets.',
                'full_description'   => 'Specially formulated industrial and heavy vehicle lubricants, offering maximum thermal stability and wear protection.',
                'price_per_unit'     => 450.00,
                'unit_of_measure'    => 'litres',
                'status'             => 'soon',
                'is_active'          => false,
                'ordering_enabled'   => false,
                'is_coming_soon'     => true,
                'is_featured'        => false,
                'icon'               => 'cog',
                'min_order_quantity' => 20.0000,
                'max_order_quantity' => 2000.0000,
                'seo_title'          => 'Industrial Lubricants & Oils - FuelCab',
                'seo_description'    => 'Secure high-grade engine oils, transmission fluids, and greases to maintain heavy transport and manufacturing machinery.',
                'display_order'      => 5,
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
