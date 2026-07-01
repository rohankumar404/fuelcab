<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Category;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Fuel\Models\Product;
use App\Modules\Fuel\Models\FuelInventory;
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

        $this->vendor = Vendor::create([
            'business_name' => 'Apex Fuel Station',
            'contact_name' => 'Alice Smith',
            'contact_email' => 'alice@apexfuel.com',
            'contact_phone' => '9876543210',
            'status' => 'approved',
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'vendor_id' => $this->vendor->id,
            'name' => 'Ultra Diesel',
            'slug' => 'ultra-diesel',
            'sku' => 'DSL-ULTRA-01',
            'price_per_unit' => 88.50,
            'unit_of_measure' => 'liter',
            'is_active' => true,
            'status' => 'active',
        ]);
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
}
