<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\UnitOfMeasure;
use App\Models\Category;
use App\Modules\Fuel\Models\MarketplaceProduct;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class MarketplaceProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // ─── SOLID FUELS ────────────────────────────────────────────────
            [
                'category_slug'         => 'biomass-briquettes-bio-coal',
                'name'                  => 'Biomass Briquettes / Bio Coal',
                'slug'                  => 'biomass-briquettes-bio-coal-master',
                'description'           => 'Approved standard for clean biomass briquettes and compressed bio coal.',
                'unit_of_measure'       => UnitOfMeasure::MetricTonnes,
                'specifications_schema' => [
                    'calorific_value_kcal_kg' => 'Min 3800 - 4500',
                    'ash_content_percentage'  => 'Max 8%',
                    'moisture_percentage'     => 'Max 10%',
                ],
                'is_active'             => true,
                'is_coming_soon'        => false,
                'ordering_enabled'      => true,
                'display_order'         => 1,
            ],
            [
                'category_slug'         => 'rice-husk',
                'name'                  => 'Rice Husk Biofuel',
                'slug'                  => 'rice-husk-biofuel-master',
                'description'           => 'Approved standard raw rice husk for industrial boiler combustion.',
                'unit_of_measure'       => UnitOfMeasure::MetricTonnes,
                'specifications_schema' => [
                    'calorific_value_kcal_kg' => 'Min 3000',
                    'moisture_percentage'     => 'Max 12%',
                ],
                'is_active'             => true,
                'is_coming_soon'        => false,
                'ordering_enabled'      => true,
                'display_order'         => 2,
            ],
            [
                'category_slug'         => 'animal-tallow',
                'name'                  => 'Industrial Animal Tallow',
                'slug'                  => 'industrial-animal-tallow-master',
                'description'           => 'Approved standard industrial animal tallow fat for chemical or fuel manufacturing.',
                'unit_of_measure'       => UnitOfMeasure::MetricTonnes,
                'specifications_schema' => [
                    'free_fatty_acids_percentage' => 'Max 5%',
                    'moisture_and_impurities'     => 'Max 1%',
                ],
                'is_active'             => true,
                'is_coming_soon'        => false,
                'ordering_enabled'      => true,
                'display_order'         => 3,
            ],

            // ─── LIQUID FUELS ───────────────────────────────────────────────
            [
                'category_slug'         => 'marketplace-high-speed-diesel',
                'name'                  => 'High Speed Diesel (HSD) Vendor Supply',
                'slug'                  => 'hsd-vendor-supply-master',
                'description'           => 'Approved standard for marketplace high speed diesel delivery from licensed partners.',
                'unit_of_measure'       => UnitOfMeasure::Litres,
                'specifications_schema' => [
                    'density_at_15_c_kg_m3'     => '820-860',
                    'flash_point_c'             => 'Min 35',
                    'sulfur_content_percentage' => 'Max 0.005%',
                ],
                'is_active'             => true,
                'is_coming_soon'        => false,
                'ordering_enabled'      => true,
                'display_order'         => 1,
            ],
            [
                'category_slug'         => 'bio-diesel-b100',
                'name'                  => 'Bio-Diesel B100 Pure',
                'slug'                  => 'bio-diesel-b100-pure-master',
                'description'           => 'Approved pure biodiesels conforming strictly to IS 15607 standards.',
                'unit_of_measure'       => UnitOfMeasure::Litres,
                'specifications_schema' => [
                    'density_at_15_c_kg_m3' => '860-900',
                    'flash_point_c'         => 'Min 120',
                ],
                'is_active'             => true,
                'is_coming_soon'        => false,
                'ordering_enabled'      => true,
                'display_order'         => 2,
            ],
            [
                'category_slug'         => 'furnace-oil',
                'name'                  => 'Furnace Oil Standard',
                'slug'                  => 'furnace-oil-standard-master',
                'description'           => 'Standard heating furnace oils for heavy industrial burners.',
                'unit_of_measure'       => UnitOfMeasure::MetricTonnes,
                'specifications_schema' => [
                    'viscosity_cst_at_50_c' => 'Max 180',
                    'flash_point_c'         => 'Min 66',
                ],
                'is_active'             => true,
                'is_coming_soon'        => false,
                'ordering_enabled'      => true,
                'display_order'         => 3,
            ],

            // ─── GAS FUELS ──────────────────────────────────────────────────
            [
                'category_slug'         => 'marketplace-cng',
                'name'                  => 'Compressed Natural Gas (CNG)',
                'slug'                  => 'cng-master',
                'description'           => 'Standard clean energy Compressed Natural Gas (CNG).',
                'unit_of_measure'       => UnitOfMeasure::Kilograms,
                'specifications_schema' => [
                    'methane_content_percentage' => 'Min 90%',
                    'calorific_value_mj_kg'      => 'Min 48',
                ],
                'is_active'             => true,
                'is_coming_soon'        => false,
                'ordering_enabled'      => true,
                'display_order'         => 1,
            ],
            [
                'category_slug'         => 'green-hydrogen',
                'name'                  => 'Green Hydrogen Fuel',
                'slug'                  => 'green-hydrogen-fuel-master',
                'description'           => 'Ultra-pure green hydrogen gas for advanced industrial energy applications.',
                'unit_of_measure'       => UnitOfMeasure::Kilograms,
                'specifications_schema' => [
                    'purity_percentage' => '99.99%',
                ],
                'is_active'             => true,
                'is_coming_soon'        => true,
                'ordering_enabled'      => false,
                'display_order'         => 2,
            ],

            // ─── EV ─────────────────────────────────────────────────────────
            [
                'category_slug'         => 'ev',
                'name'                  => 'EV Charging Services',
                'slug'                  => 'ev-charging-services-master',
                'description'           => 'Approved standard charging infrastructure catalog.',
                'unit_of_measure'       => UnitOfMeasure::Units,
                'specifications_schema' => [
                    'charger_types_supported' => 'CCS2, CHAdeMO',
                ],
                'is_active'             => true,
                'is_coming_soon'        => true,
                'ordering_enabled'      => false,
                'display_order'         => 1,
            ],
        ];

        foreach ($products as $prod) {
            $category = Category::where('slug', $prod['category_slug'])->first();
            if (! $category) {
                $this->command->warn("Category slug '{$prod['category_slug']}' not found, skipping.");
                continue;
            }

            // Unset category_slug as it is not a column
            $categorySlug = $prod['category_slug'];
            unset($prod['category_slug']);

            MarketplaceProduct::updateOrCreate(
                [
                    'category_id' => $category->id,
                    'name'        => $prod['name'],
                ],
                array_merge($prod, [
                    'seo_title'       => $prod['name'] . ' Catalog Master - FuelCab',
                    'seo_description' => $prod['description'],
                    'product_image'   => "https://images.fuelcab.com/products/{$prod['slug']}.jpg",
                ])
            );
        }

        $this->command->info('✅ MarketplaceProductSeeder: Idempotent marketplace master products seeded successfully.');
    }
}
