<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Fuel\Models\Product;
use App\Modules\Fuel\Models\FuelInventory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    protected Category $category;
    protected Vendor $vendor;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->category = Category::create([
            'name' => 'Diesel',
            'slug' => 'diesel',
            'description' => 'Premium high speed diesel',
        ]);

        $companyId = \Illuminate\Support\Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('companies')->insert([
            'id'         => $companyId,
            'name'       => 'Apex Logistics LLC',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->vendor = Vendor::create([
            'company_id'            => $companyId,
            'brand_name'            => 'Apex Fuel Station',
            'status'                => 'approved',
            'commission_rate'       => 0.00,
            'service_radius_meters' => 5000,
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'vendor_id' => $this->vendor->id,
            'name' => 'Ultra Diesel',
            'slug' => 'ultra-diesel',
            'sku' => 'DSL-ULTRA-01',
            'price_per_unit' => 88.50,
            'unit_of_measure' => 'litres',
            'is_active' => true,
            'status' => 'active',
        ]);

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);
        $user = User::create([
            'name'      => 'Admin User',
            'email'     => 'admin@fuelcab.com',
            'password'  => bcrypt('password123'),
            'role_type' => \App\Enums\UserRole::SuperAdmin,
        ]);
        $user->assignRole('super_admin');
        \Laravel\Sanctum\Sanctum::actingAs($user);
    }

    /**
     * Test updating product status to disabled.
     */
    public function test_can_update_product_status(): void
    {
        $response = $this->patchJson("/api/v1/products/{$this->product->id}/status", [
            'status' => 'disabled',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $this->product->id,
                    'status' => 'disabled',
                    'is_active' => false,
                ],
            ]);

        $this->assertDatabaseHas('products', [
            'id' => $this->product->id,
            'status' => 'disabled',
            'is_active' => false,
        ]);
    }

    /**
     * Test synchronizing product inventory levels.
     */
    public function test_can_sync_product_inventory(): void
    {
        $response = $this->postJson("/api/v1/products/{$this->product->id}/sync-inventory", [
            'quantity_available' => 15000.50,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'product_id' => $this->product->id,
                    'quantity_available' => 15000.50,
                ],
            ]);

        $this->assertDatabaseHas('inventories', [
            'product_id' => $this->product->id,
            'quantity_available' => 15000.50,
        ]);
    }

    /**
     * Test direct product catalog scopes, enum casting, and API responses.
     */
    public function test_direct_product_catalog_properties(): void
    {
        // Set first-party vendor flag
        $this->vendor->update(['is_first_party' => true]);

        // Update product catalog details
        $this->product->update([
            'short_description' => 'Test short description',
            'full_description' => 'Test full description',
            'is_featured' => true,
            'ordering_enabled' => true,
            'display_order' => 1,
            'unit_of_measure' => \App\Enums\UnitOfMeasure::Litres,
        ]);

        // Check enum casting
        $this->assertEquals(\App\Enums\UnitOfMeasure::Litres, $this->product->fresh()->unit_of_measure);

        // Check query scopes
        $this->assertTrue(Product::direct()->where('id', $this->product->id)->exists());
        $this->assertTrue(Product::featured()->where('id', $this->product->id)->exists());

        // Check API response fields
        $response = $this->getJson("/api/v1/products/{$this->product->id}");
        $response->assertStatus(200)
            ->assertJson([
                'data' => [
                    'id' => $this->product->id,
                    'short_description' => 'Test short description',
                    'full_description' => 'Test full description',
                    'is_featured' => true,
                    'ordering_enabled' => true,
                    'unit_of_measure' => 'litres',
                    'display_order' => 1,
                ]
            ]);
    }
}
