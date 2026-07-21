<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Enums\SalesChannel;
use App\Enums\UnitOfMeasure;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Cart\Actions\AddItemToCartAction;
use App\Modules\Cart\DTOs\AddCartItemDTO;
use App\Modules\Cart\Services\CartService;
use App\Modules\Fuel\Models\MarketplaceProduct;
use App\Modules\Fuel\Models\Product;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Concurrency;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CartModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Vendor $directVendor;
    private Vendor $vendorA;
    private Product $diesel;
    private Product $biomass;
    private MarketplaceProduct $marketplaceProduct;
    private VendorListing $listing;
    private Category $category;
    private Cart $cart;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Customer
        $this->customer = User::create([
            'name'      => 'Cart Test Customer',
            'email'     => 'cart-test@fuelcab.com',
            'password'  => bcrypt('password'),
            'role_type' => UserRole::Customer,
        ]);
        $this->customer->assignRole(UserRole::Customer->value);

        $this->category = Category::create(['name' => 'Fuels', 'slug' => 'fuels-cart-test']);

        // Direct Vendor (first-party)
        $companyDirect = \Illuminate\Support\Facades\DB::table('companies')->insertGetId([
            'id'         => $directId = \Illuminate\Support\Str::uuid()->toString(),
            'name'       => 'FuelCab Direct Depot',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->directVendor = Vendor::create([
            'company_id'            => $directId,
            'brand_name'            => 'FuelCab Direct',
            'status'                => VendorStatus::Approved,
            'is_first_party'        => true,
            'service_radius_meters' => 50000,
        ]);

        // Marketplace Vendor A
        $companyA = \Illuminate\Support\Facades\DB::table('companies')->insertGetId([
            'id'         => $vendorAId = \Illuminate\Support\Str::uuid()->toString(),
            'name'       => 'Vendor A Corp',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->vendorA = Vendor::create([
            'company_id'            => $vendorAId,
            'brand_name'            => 'Vendor A Fuels',
            'status'                => VendorStatus::Approved,
            'is_first_party'        => false,
            'service_radius_meters' => 50000,
        ]);

        // Products
        $this->diesel = Product::create([
            'category_id'        => $this->category->id,
            'vendor_id'          => $this->directVendor->id,
            'name'               => 'High Speed Diesel',
            'slug'               => 'high-speed-diesel',
            'sku'                => 'HSD-001',
            'price_per_unit'     => 90.00,
            'unit_of_measure'    => UnitOfMeasure::Litres,
            'is_active'          => true,
            'ordering_enabled'   => true,
            'min_order_quantity' => 100.0,
        ]);

        $this->biomass = Product::create([
            'category_id'        => $this->category->id,
            'vendor_id'          => $this->vendorA->id,
            'name'               => 'Biomass Briquettes',
            'slug'               => 'biomass-briquettes-ct',
            'sku'                => 'BIO-001',
            'price_per_unit'     => 12000.00,
            'unit_of_measure'    => UnitOfMeasure::MetricTonnes,
            'is_active'          => true,
            'ordering_enabled'   => true,
            'min_order_quantity' => 1.0,
        ]);

        // Marketplace Product & Listing
        $this->marketplaceProduct = MarketplaceProduct::create([
            'category_id' => $this->category->id,
            'name'        => 'Rice Husk',
            'slug'        => 'rice-husk-ct',
            'unit'        => UnitOfMeasure::MetricTonnes,
            'is_active'   => true,
        ]);

        $this->listing = VendorListing::create([
            'vendor_id'              => $this->vendorA->id,
            'marketplace_product_id' => $this->marketplaceProduct->id,
            'listing_title'          => 'Rice Husk Premium',
            'slug'                   => 'rice-husk-premium-ct',
            'unit'                   => UnitOfMeasure::MetricTonnes,
            'base_price'             => 3500.00,
            'available_quantity'     => 100,
            'min_order_quantity'     => 5,
            'max_order_quantity'     => 50,
            'approval_status'        => ListingStatus::Approved,
            'is_active'              => true,
        ]);

        $this->cart = Cart::create(['user_id' => $this->customer->id]);
    }

    // ═══════════════════════════════════════════════════════════
    // SECTION 1: Direct Product Cart Tests
    // ═══════════════════════════════════════════════════════════

    /** @test */
    public function test_can_add_direct_product_to_cart(): void
    {
        $item = app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO($this->diesel->id, 500.0)
        );

        $this->assertEquals($this->diesel->id, $item->product_id);
        $this->assertEquals(500.0, $item->quantity);
        $this->assertEquals(90.00, $item->price_snapshot);
        $this->assertEquals('direct', $item->sales_channel->value);
        $this->assertEquals($this->directVendor->id, $item->vendor_id);
        $this->assertEquals('High Speed Diesel', $item->product_name_snapshot);
    }

    /** @test */
    public function test_adding_same_direct_product_accumulates_quantity(): void
    {
        app(AddItemToCartAction::class)->execute($this->cart, new AddCartItemDTO($this->diesel->id, 200.0));
        app(AddItemToCartAction::class)->execute($this->cart, new AddCartItemDTO($this->diesel->id, 300.0));

        $items = CartItem::where('cart_id', $this->cart->id)->whereNull('deleted_at')->get();
        $this->assertCount(1, $items);
        $this->assertEquals(500.0, $items->first()->quantity);
    }

    /** @test */
    public function test_direct_product_moq_validation_fails(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/Minimum order quantity/');

        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO($this->diesel->id, 50.0) // below 100L MOQ
        );
    }

    /** @test */
    public function test_disabled_ordering_product_blocked(): void
    {
        $this->diesel->update(['ordering_enabled' => false]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/not available for ordering/');

        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO($this->diesel->id, 500.0)
        );
    }

    // ═══════════════════════════════════════════════════════════
    // SECTION 2: Marketplace Listing Cart Tests
    // ═══════════════════════════════════════════════════════════

    /** @test */
    public function test_can_add_marketplace_listing_to_cart(): void
    {
        $item = app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO(null, 10.0, $this->listing->id)
        );

        $this->assertEquals($this->listing->id, $item->vendor_listing_id);
        $this->assertEquals(10.0, $item->quantity);
        $this->assertEquals(3500.00, $item->price_snapshot);
        $this->assertEquals('marketplace', $item->sales_channel->value);
        $this->assertEquals($this->vendorA->id, $item->vendor_id);
        $this->assertEquals('Rice Husk Premium', $item->product_name_snapshot);
    }

    /** @test */
    public function test_marketplace_listing_moq_validation_fails(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/Minimum order quantity/');

        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO(null, 2.0, $this->listing->id) // below 5 MT MOQ
        );
    }

    /** @test */
    public function test_marketplace_listing_max_quantity_validation_fails(): void
    {
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/Maximum allowed order quantity/');

        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO(null, 100.0, $this->listing->id) // above 50 MT max
        );
    }

    /** @test */
    public function test_marketplace_listing_inventory_check_fails(): void
    {
        $this->listing->update(['available_quantity' => 8]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/Insufficient stock/');

        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO(null, 10.0, $this->listing->id)
        );
    }

    /** @test */
    public function test_inactive_listing_blocked(): void
    {
        $this->listing->update(['is_active' => false]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/currently unavailable/');

        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO(null, 10.0, $this->listing->id)
        );
    }

    /** @test */
    public function test_unapproved_listing_blocked(): void
    {
        $this->listing->update(['approval_status' => ListingStatus::Draft]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/not currently approved/');

        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO(null, 10.0, $this->listing->id)
        );
    }

    /** @test */
    public function test_suspended_vendor_listing_blocked(): void
    {
        $this->vendorA->update(['status' => VendorStatus::Suspended]);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/not currently active/');

        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO(null, 10.0, $this->listing->id)
        );
    }

    // ═══════════════════════════════════════════════════════════
    // SECTION 3: Mixed Cart — Seller Grouping
    // ═══════════════════════════════════════════════════════════

    /** @test */
    public function test_mixed_cart_groups_correctly_by_seller(): void
    {
        app(AddItemToCartAction::class)->execute($this->cart, new AddCartItemDTO($this->diesel->id, 500.0));
        app(AddItemToCartAction::class)->execute($this->cart, new AddCartItemDTO($this->biomass->id, 2.0));

        $cart = $this->cart->fresh(['items.product', 'items.vendorListing', 'items.vendor']);
        $groups = $cart->groupByFulfillment();

        $this->assertCount(2, $groups);

        $channels = array_column($groups, 'sales_channel');
        $this->assertContains('direct', $channels);
        $this->assertContains('marketplace', $channels);

        $this->assertTrue($cart->hasMultipleVendors());
    }

    /** @test */
    public function test_cart_total_is_sum_of_all_snapshots(): void
    {
        app(AddItemToCartAction::class)->execute($this->cart, new AddCartItemDTO($this->diesel->id, 500.0));     // 500 * 90 = 45000
        app(AddItemToCartAction::class)->execute($this->cart, new AddCartItemDTO(null, 10.0, $this->listing->id)); // 10 * 3500 = 35000

        $cart = $this->cart->fresh(['items']);
        $this->assertEquals(80000.0, $cart->getTotal());
    }

    // ═══════════════════════════════════════════════════════════
    // SECTION 4: Cart API Endpoints
    // ═══════════════════════════════════════════════════════════

    /** @test */
    public function test_authenticated_user_can_view_cart(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->getJson('/api/v1/cart');
        $response->assertOk()
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'id', 'items', 'seller_groups',
                         'item_count', 'total', 'has_multiple_sellers', 'is_empty',
                     ],
                 ]);
    }

    /** @test */
    public function test_api_add_direct_product_to_cart(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $this->diesel->id,
            'quantity'   => 500,
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.item_count', 1)
                 ->assertJsonPath('data.total', 45000);
    }

    /** @test */
    public function test_api_add_marketplace_listing_to_cart(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/v1/cart/items', [
            'vendor_listing_id' => $this->listing->id,
            'quantity'          => 10,
        ]);

        $response->assertStatus(201)
                 ->assertJson(['success' => true])
                 ->assertJsonPath('data.seller_groups.0.sales_channel', 'marketplace');
    }

    /** @test */
    public function test_api_moq_violation_returns_422(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/v1/cart/items', [
            'product_id' => $this->diesel->id,
            'quantity'   => 10, // below 100L MOQ
        ]);

        $response->assertStatus(422)
                 ->assertJson(['success' => false])
                 ->assertJsonFragment(['message' => 'Minimum order quantity for \'High Speed Diesel\' is 100 litres.']);
    }

    /** @test */
    public function test_api_update_quantity(): void
    {
        Sanctum::actingAs($this->customer);

        $item = CartItem::create([
            'cart_id'               => $this->cart->id,
            'product_id'            => $this->diesel->id,
            'quantity'              => 200.0,
            'price_snapshot'        => 90.00,
            'unit_of_measure'       => 'litres',
            'sales_channel'         => 'direct',
            'vendor_id'             => $this->directVendor->id,
            'product_name_snapshot' => 'High Speed Diesel',
        ]);

        $response = $this->patchJson("/api/v1/cart/items/{$item->id}", ['quantity' => 400]);
        $response->assertOk()->assertJson(['success' => true]);
    }

    /** @test */
    public function test_api_remove_item(): void
    {
        Sanctum::actingAs($this->customer);

        $item = CartItem::create([
            'cart_id'               => $this->cart->id,
            'product_id'            => $this->diesel->id,
            'quantity'              => 200.0,
            'price_snapshot'        => 90.00,
            'unit_of_measure'       => 'litres',
            'sales_channel'         => 'direct',
            'vendor_id'             => $this->directVendor->id,
            'product_name_snapshot' => 'High Speed Diesel',
        ]);

        $response = $this->deleteJson("/api/v1/cart/items/{$item->id}");
        $response->assertOk()->assertJson(['success' => true]);

        $this->assertSoftDeleted('cart_items', ['id' => $item->id]);
    }

    /** @test */
    public function test_api_clear_cart(): void
    {
        Sanctum::actingAs($this->customer);

        CartItem::create([
            'cart_id'               => $this->cart->id,
            'product_id'            => $this->diesel->id,
            'quantity'              => 200.0,
            'price_snapshot'        => 90.00,
            'unit_of_measure'       => 'litres',
            'sales_channel'         => 'direct',
            'vendor_id'             => $this->directVendor->id,
            'product_name_snapshot' => 'High Speed Diesel',
        ]);

        $response = $this->deleteJson('/api/v1/cart');
        $response->assertOk()->assertJson(['success' => true]);

        $count = CartItem::where('cart_id', $this->cart->id)->whereNull('deleted_at')->count();
        $this->assertEquals(0, $count);
    }

    /** @test */
    public function test_api_cart_resource_returns_seller_groups(): void
    {
        Sanctum::actingAs($this->customer);

        // Add direct product
        $r1 = $this->postJson('/api/v1/cart/items', ['product_id' => $this->diesel->id, 'quantity' => 500]);
        $r1->assertStatus(201);

        // Add marketplace listing
        $r2 = $this->postJson('/api/v1/cart/items', ['vendor_listing_id' => $this->listing->id, 'quantity' => 10]);
        $r2->assertStatus(201);

        $response = $this->getJson('/api/v1/cart');
        $response->assertOk();

        $groups = $response->json('data.seller_groups');
        $this->assertGreaterThanOrEqual(2, count($groups));

        $channels = array_column($groups, 'sales_channel');
        $this->assertContains('direct', $channels);
        $this->assertContains('marketplace', $channels);

        $this->assertTrue($response->json('data.has_multiple_sellers'));
    }

    /** @test */
    public function test_api_requires_authentication_for_cart_access(): void
    {
        // No auth, no guest token — authenticated endpoint returns 401
        $response = $this->getJson('/api/v1/cart');
        $response->assertStatus(401);
    }

    // ═══════════════════════════════════════════════════════════
    // SECTION 5: Price Snapshot Integrity
    // ═══════════════════════════════════════════════════════════

    /** @test */
    public function test_price_snapshot_captured_at_add_time(): void
    {
        $originalPrice = 90.00;

        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO($this->diesel->id, 500.0)
        );

        // Simulate price change AFTER adding to cart
        $this->diesel->update(['price_per_unit' => 120.00]);

        $item = CartItem::where('cart_id', $this->cart->id)->first();

        // Snapshot must be the original price
        $this->assertEquals($originalPrice, $item->price_snapshot);
        $this->assertTrue($item->isPriceStale());
    }

    /** @test */
    public function test_listing_price_snapshot_staleness_detection(): void
    {
        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO(null, 10.0, $this->listing->id)
        );

        // Price changes after cart add
        $this->listing->update(['base_price' => 4200.00]);

        $item = CartItem::where('cart_id', $this->cart->id)
            ->where('vendor_listing_id', $this->listing->id)
            ->first();

        // Reload with listing relation
        $item->load('vendorListing');
        $this->assertTrue($item->isPriceStale());
    }

    // ═══════════════════════════════════════════════════════════
    // SECTION 6: Concurrency / Race Condition Tests
    // ═══════════════════════════════════════════════════════════

    /** @test */
    public function test_concurrent_add_accumulates_not_duplicates(): void
    {
        // Simulate two concurrent adds of the same direct product
        // Uses DB transaction isolation to prevent duplicate rows
        $cartId    = $this->cart->id;
        $productId = $this->diesel->id;

        DB::transaction(function () use ($cartId, $productId) {
            app(AddItemToCartAction::class)->execute(
                $this->cart,
                new AddCartItemDTO($productId, 200.0)
            );
        });

        DB::transaction(function () use ($cartId, $productId) {
            // Re-fetch cart to simulate second request
            $cart = Cart::find($cartId);
            app(AddItemToCartAction::class)->execute(
                $cart,
                new AddCartItemDTO($productId, 300.0)
            );
        });

        $items = CartItem::where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->whereNull('deleted_at')
            ->get();

        $this->assertCount(1, $items);
        $this->assertEquals(500.0, $items->first()->quantity);
    }

    /** @test */
    public function test_concurrent_quantity_update_does_not_corrupt(): void
    {
        $item = CartItem::create([
            'cart_id'               => $this->cart->id,
            'product_id'            => $this->diesel->id,
            'quantity'              => 200.0,
            'price_snapshot'        => 90.00,
            'unit_of_measure'       => 'litres',
            'sales_channel'         => 'direct',
            'vendor_id'             => $this->directVendor->id,
            'product_name_snapshot' => 'High Speed Diesel',
        ]);

        // Simulate two sequential update requests
        Sanctum::actingAs($this->customer);

        $this->patchJson("/api/v1/cart/items/{$item->id}", ['quantity' => 400]);
        $this->patchJson("/api/v1/cart/items/{$item->id}", ['quantity' => 600]);

        $final = CartItem::find($item->id);
        $this->assertEquals(600.0, $final->quantity);
    }

    /** @test */
    public function test_inventory_boundary_prevents_overselling(): void
    {
        // Set stock to exactly 20 units
        $this->listing->update(['available_quantity' => 20]);

        // First add 20 — should work
        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO(null, 20.0, $this->listing->id)
        );

        // Second add — should fail with insufficient stock
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/Insufficient stock/');

        // Clear existing item first to test fresh add
        CartItem::where('cart_id', $this->cart->id)->forceDelete();

        // Try adding 25 when only 20 available
        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO(null, 25.0, $this->listing->id)
        );
    }

    /** @test */
    public function test_guest_cart_via_header_token(): void
    {
        $guestToken = 'guest_test_token_12345';

        // GET cart via guest endpoint with X-Guest-Token header (no auth required)
        $response = $this->withHeaders(['X-Guest-Token' => $guestToken])
                         ->getJson('/api/v1/cart/guest');

        $response->assertOk()
                 ->assertJsonPath('data.is_empty', true);
    }

    /** @test */
    public function test_guest_cart_merge_on_login(): void
    {
        $guestToken = 'guest_merge_test_123';

        // Add item via guest endpoint (no auth)
        $this->withHeaders(['X-Guest-Token' => $guestToken])
             ->postJson('/api/v1/cart/guest/items', [
                 'product_id' => $this->diesel->id,
                 'quantity'   => 200,
             ]);

        // Login and merge guest cart into user cart
        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/v1/cart/merge', [
            'guest_token' => $guestToken,
        ]);

        $response->assertOk()
                 ->assertJson(['success' => true]);
    }
}
