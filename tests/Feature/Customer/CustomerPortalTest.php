<?php

declare(strict_types=1);

namespace Tests\Feature\Customer;

use App\Enums\SalesChannel;
use App\Enums\UnitOfMeasure;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Category;
use App\Models\Company;
use App\Models\User;
use App\Modules\Fuel\Models\Product;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderItem;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorListing;
use App\Modules\Wallet\Models\Wallet;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Vendor $vendor;

    private VendorListing $listing;

    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        // 1. Create customer user
        $this->customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'mobile' => '9876543210',
            'password' => bcrypt('password'),
            'role_type' => UserRole::Customer,
        ]);
        $this->customer->assignRole(UserRole::Customer->value);

        // 2. Create Company & Vendor
        $companyId = Str::uuid()->toString();
        DB::table('companies')->insert([
            'id' => $companyId,
            'name' => 'Vendor Corp',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->vendor = Vendor::create([
            'company_id' => $companyId,
            'brand_name' => 'Vendor brand',
            'status' => 'approved',
        ]);

        // 3. Create category & listing
        $categoryId = Str::uuid()->toString();
        DB::table('categories')->insert([
            'id' => $categoryId,
            'name' => 'Fuel Category',
            'slug' => 'fuel-category',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId = Str::uuid()->toString();
        DB::table('marketplace_products')->insert([
            'id' => $productId,
            'category_id' => $categoryId,
            'name' => 'Direct Product',
            'slug' => 'direct-product',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->listing = VendorListing::create([
            'vendor_id' => $this->vendor->id,
            'marketplace_product_id' => $productId,
            'listing_title' => 'Direct Listing Title',
            'slug' => 'direct-listing-title-'.Str::random(4),
            'sku' => 'LST-DIR-99',
            'base_price' => 100.00,
            'tax_rate' => 18.00,
            'unit' => 'litres',
            'approval_status' => 'APPROVED',
        ]);

        // 4. Create address
        $this->address = Address::create([
            'user_id' => $this->customer->id,
            'addressable_type' => User::class,
            'address_line_1' => '123 Test St',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400001',
            'latitude' => 19.076,
            'longitude' => 72.8777,
        ]);
    }

    // ── Profile Tests ────────────────────────────────────────────────────────

    /** @test */
    public function customer_can_get_and_update_profile(): void
    {
        Sanctum::actingAs($this->customer);

        $this->getJson('/api/v1/customer/profile')
            ->assertOk()
            ->assertJsonPath('data.name', 'Customer User');

        $this->putJson('/api/v1/customer/profile', [
            'name' => 'Updated Customer User',
        ])->assertOk()
            ->assertJsonPath('data.name', 'Updated Customer User');
    }

    // ── Address CRUD Tests ───────────────────────────────────────────────────

    /** @test */
    public function customer_can_manage_addresses(): void
    {
        Sanctum::actingAs($this->customer);

        // List
        $this->getJson('/api/v1/customer/addresses')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        // Create
        $response = $this->postJson('/api/v1/customer/addresses', [
            'address_line_1' => '456 New St',
            'city' => 'Pune',
            'state' => 'Maharashtra',
            'postal_code' => '411001',
            'latitude' => 18.5204,
            'longitude' => 73.8567,
        ])->assertStatus(201);

        $addressId = $response->json('data.id');

        // Update
        $this->putJson("/api/v1/customer/addresses/{$addressId}", [
            'city' => 'Thane',
        ])->assertOk()
            ->assertJsonPath('data.city', 'Thane');

        // Delete
        $this->deleteJson("/api/v1/customer/addresses/{$addressId}")
            ->assertOk();
    }

    // ── Favorites Tests ──────────────────────────────────────────────────────

    /** @test */
    public function customer_can_manage_favorites(): void
    {
        Sanctum::actingAs($this->customer);

        // Create favorite
        $this->postJson('/api/v1/customer/favorites', [
            'vendor_listing_id' => $this->listing->id,
        ])->assertStatus(201);

        // List
        $this->getJson('/api/v1/customer/favorites')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        // Delete
        $this->deleteJson("/api/v1/customer/favorites/{$this->listing->id}")
            ->assertOk();
    }

    // ── Notifications Tests ──────────────────────────────────────────────────

    /** @test */
    public function customer_can_manage_notifications(): void
    {
        Sanctum::actingAs($this->customer);

        DB::table('notifications')->insert([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\OrderPlaced',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->customer->id,
            'data' => json_encode(['title' => 'Sample Notice']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // List
        $this->getJson('/api/v1/notifications')
            ->assertOk();
    }

    // ── Subscriptions Tests ──────────────────────────────────────────────────

    /** @test */
    public function customer_can_manage_subscriptions(): void
    {
        Sanctum::actingAs($this->customer);

        // Create subscription
        $response = $this->postJson('/api/v1/orders/subscriptions', [
            'vendor_listing_id' => $this->listing->id,
            'quantity' => 50.00,
            'frequency' => 'weekly',
        ])->assertStatus(201);

        $subscriptionId = $response->json('data.id');

        // List
        $this->getJson('/api/v1/orders/subscriptions')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        // Update
        $this->patchJson("/api/v1/orders/subscriptions/{$subscriptionId}", [
            'status' => 'paused',
        ])->assertOk()
            ->assertJsonPath('data.status', 'paused');

        // Cancel
        $this->deleteJson("/api/v1/orders/subscriptions/{$subscriptionId}")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');
    }

    // ── Emergency Orders Tests ───────────────────────────────────────────────

    /** @test */
    public function customer_can_place_emergency_orders(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/v1/orders/emergency', [
            'vendor_listing_id' => $this->listing->id,
            'delivery_address_id' => $this->address->id,
            'quantity' => 10.00,
        ])->assertStatus(201);

        $this->assertDatabaseHas('orders', [
            'id' => $response->json('data.id'),
            'is_emergency' => true,
            'total_amount' => 1430.00, // 1000 + 250 fee + 180 tax
        ]);
    }

    // ── Wallet Tests ─────────────────────────────────────────────────────────

    /** @test */
    public function customer_can_use_wallet(): void
    {
        Sanctum::actingAs($this->customer);

        // Show (creates wallet automatically)
        $this->getJson('/api/v1/wallets')
            ->assertOk()
            ->assertJsonPath('data.balance', 0);

        // Top up
        $this->postJson('/api/v1/wallets/top-up', [
            'amount' => 500.00,
            'description' => 'Test Top Up',
        ])->assertOk()
            ->assertJsonPath('data.balance', 500);

        // Deduct
        $this->postJson('/api/v1/wallets/deduct', [
            'amount' => 200.00,
            'description' => 'Test Deduct',
        ])->assertOk()
            ->assertJsonPath('data.balance', 300);
    }

    // ── Support Tickets Tests ────────────────────────────────────────────────

    /** @test */
    public function customer_can_manage_support_tickets(): void
    {
        Sanctum::actingAs($this->customer);

        // Submit ticket
        $this->postJson('/api/v1/customer/support/tickets', [
            'subject' => 'Issue with delivery',
            'message' => 'The driver was late.',
        ])->assertStatus(201);

        // List tickets
        $this->getJson('/api/v1/customer/support/tickets')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    // ── Invoices Tests ───────────────────────────────────────────────────────

    /** @test */
    public function customer_can_download_invoice(): void
    {
        Sanctum::actingAs($this->customer);

        // Seed products table to satisfy order_items foreign key constraint
        $product = Product::create([
            'category_id' => Category::first()->id,
            'vendor_id' => $this->vendor->id,
            'name' => 'Test Product for Invoice',
            'slug' => 'test-product-for-invoice',
            'sku' => 'PRD-INV-99',
            'price_per_unit' => 100.00,
            'unit_of_measure' => UnitOfMeasure::Litres,
            'is_active' => true,
            'ordering_enabled' => true,
            'min_order_quantity' => 1.0,
        ]);

        // Create an order
        $order = Order::create([
            'customer_id' => $this->customer->id,
            'vendor_id' => $this->vendor->id,
            'delivery_address_id' => $this->address->id,
            'status' => OrderStatus::Delivered,
            'subtotal_amount' => 1000.00,
            'tax_amount' => 180.00,
            'delivery_fee' => 50.00,
            'total_amount' => 1230.00,
            'channel' => SalesChannel::Direct,
            'order_number' => 'ORD-INV-1001',
        ]);

        OrderItem::create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 10.00,
            'price_per_unit' => 100.00,
            'total_price' => 1000.00,
            'sales_channel' => SalesChannel::Direct,
            'product_name_snapshot' => $product->name,
            'product_sku_snapshot' => $product->sku,
            'unit_snapshot' => 'Litres',
        ]);

        $this->getJson("/api/v1/orders/{$order->id}/invoice")
            ->assertOk();
    }
}
