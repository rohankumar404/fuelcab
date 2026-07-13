<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Modules\Fuel\Models\MarketplaceProduct;
use App\Enums\UnitOfMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MarketplaceProductTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
    }

    /**
     * Test categories table extension columns.
     */
    public function test_categories_extended_columns(): void
    {
        $category = Category::create([
            'name'            => 'Solid Fuels',
            'slug'            => 'solid-fuels',
            'image_path'      => 'https://images.fuelcab.com/categories/solid.jpg',
            'display_order'   => 10,
            'is_active'       => true,
            'seo_title'       => 'Industrial Solid Fuels - FuelCab',
            'seo_description' => 'Best industrial solid fuels.',
        ]);

        $this->assertDatabaseHas('categories', [
            'id'              => $category->id,
            'name'            => 'Solid Fuels',
            'image_path'      => 'https://images.fuelcab.com/categories/solid.jpg',
            'display_order'   => 10,
            'is_active'       => true,
            'seo_title'       => 'Industrial Solid Fuels - FuelCab',
        ]);
    }

    /**
     * Test marketplace products master creation, relationships and constraints.
     */
    public function test_marketplace_product_master_creation_and_jsonb_specs(): void
    {
        $category = Category::create([
            'name' => 'Biomass Briquettes / Bio Coal',
            'slug' => 'biomass-briquettes-bio-coal',
        ]);

        $specs = [
            'calorific_value_kcal_kg' => 'Min 3800 - 4500',
            'ash_content_percentage'  => 'Max 8%',
        ];

        $product = MarketplaceProduct::create([
            'category_id'           => $category->id,
            'name'                  => 'Biomass Briquettes Grade A',
            'slug'                  => 'biomass-briquettes-grade-a',
            'description'           => 'Premium approved Biomass Briquettes.',
            'unit_of_measure'       => UnitOfMeasure::MetricTonnes,
            'specifications_schema' => $specs,
            'is_active'             => true,
            'is_coming_soon'        => false,
            'ordering_enabled'      => true,
            'display_order'         => 1,
            'seo_title'             => 'Biomass Briquettes Grade A Master - FuelCab',
            'seo_description'       => 'Approved grade A briquettes specifications.',
        ]);

        $this->assertDatabaseHas('marketplace_products', [
            'id'         => $product->id,
            'name'       => 'Biomass Briquettes Grade A',
            'slug'       => 'biomass-briquettes-grade-a',
            'is_active'  => true,
        ]);

        $fresh = $product->fresh();
        $this->assertEquals($specs, $fresh->specifications_schema);
        $this->assertEquals(UnitOfMeasure::MetricTonnes, $fresh->unit_of_measure);
        $this->assertEquals($category->id, $fresh->category->id);
    }

    /**
     * Test unique constraint: unique category_id and name.
     */
    public function test_marketplace_product_unique_constraint(): void
    {
        $category = Category::create([
            'name' => 'Biomass Briquettes / Bio Coal',
            'slug' => 'biomass-briquettes-bio-coal',
        ]);

        MarketplaceProduct::create([
            'category_id'     => $category->id,
            'name'            => 'Biomass Briquettes Grade A',
            'slug'            => 'biomass-briquettes-grade-a',
            'unit_of_measure' => UnitOfMeasure::MetricTonnes,
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        // Attempting to create same name under same category will fail unique index
        MarketplaceProduct::create([
            'category_id'     => $category->id,
            'name'            => 'Biomass Briquettes Grade A',
            'slug'            => 'biomass-briquettes-grade-a-2',
            'unit_of_measure' => UnitOfMeasure::MetricTonnes,
        ]);
    }

    /**
     * Test idempotent seeders. Running seeders multiple times must not create duplicate categories or products.
     */
    public function test_marketplace_product_seeder_is_idempotent(): void
    {
        // 1. Run seeder first time
        $this->seed(\Database\Seeders\ProductSeeder::class);
        $this->seed(\Database\Seeders\MarketplaceProductSeeder::class);

        $initialCategoryCount = Category::count();
        $initialProductCount  = MarketplaceProduct::count();

        $this->assertGreaterThan(0, $initialCategoryCount);
        $this->assertGreaterThan(0, $initialProductCount);

        // 2. Run seeder second time
        $this->seed(\Database\Seeders\ProductSeeder::class);
        $this->seed(\Database\Seeders\MarketplaceProductSeeder::class);

        // Counts should remain identical
        $this->assertEquals($initialCategoryCount, Category::count());
        $this->assertEquals($initialProductCount, MarketplaceProduct::count());
    }
}
