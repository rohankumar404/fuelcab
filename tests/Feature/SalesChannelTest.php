<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Cart\Actions\AddItemToCartAction;
use App\Modules\Cart\DTOs\AddCartItemDTO;
use App\Modules\Checkout\Models\Checkout;
use App\Modules\Checkout\Services\CheckoutService;
use App\Modules\Fuel\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use App\Enums\SalesChannel;
use App\Enums\UnitOfMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class SalesChannelTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Vendor $directVendor;
    private Vendor $vendorA;
    private Vendor $vendorB;
    private Product $diesel;
    private Product $biomass;
    private Product $rdf;
    private Address $address;
    private Cart $cart;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // ── 1. Create customer first
        $this->customer = User::create([
            'id'        => \Illuminate\Support\Str::uuid()->toString(),
            'name'      => 'Test Customer',
            'email'     => 'customer@fuelcab.com',
            'phone'     => '+919999999999',
            'password'  => bcrypt('password123'),
            'role_type' => \App\Enums\UserRole::Customer,
        ]);

        // ── 2. Create customer address
        $this->address = Address::create([
            'user_id'          => $this->customer->id,
            'addressable_type' => 'App\Models\User',
            'name'             => 'Headquarters',
            'address_line_1'   => '100 MG Road',
            'city'             => 'Bengaluru',
            'state'            => 'Karnataka',
            'postal_code'      => '560001',
            'country'          => 'India',
            'latitude'         => 12.9716,
            'longitude'        => 77.5946,
        ]);

        // ── 3. Create Companies and Vendors
        // Direct (FuelCab-owned)
        $companyDirectId = \Illuminate\Support\Str::uuid()->toString();
        $this->createCompanyRecord($companyDirectId, 'FuelCab Direct Ltd');
        $this->directVendor = Vendor::create([
            'id'                     => \Illuminate\Support\Str::uuid()->toString(),
            'company_id'             => $companyDirectId,
            'brand_name'             => 'FuelCab Direct',
            'status'                 => 'approved',
            'commission_rate'        => 0.00,
            'is_first_party'         => true,
            'service_radius_meters'  => 50000,
        ]);
        Address::create([
            'company_id'       => $companyDirectId,
            'addressable_type' => 'App\Models\Company',
            'address_line_1'   => 'Direct Depot',
            'city'             => 'Bengaluru',
            'state'            => 'Karnataka',
            'postal_code'      => '560001',
            'country'          => 'India',
            'latitude'         => 12.9716,
            'longitude'        => 77.5946,
        ]);

        // Vendor A
        $companyAId = \Illuminate\Support\Str::uuid()->toString();
        $this->createCompanyRecord($companyAId, 'Vendor A Corp');
        $this->vendorA = Vendor::create([
            'id'                     => \Illuminate\Support\Str::uuid()->toString(),
            'company_id'             => $companyAId,
            'brand_name'             => 'Vendor A Fuels',
            'status'                 => 'approved',
            'commission_rate'        => 5.00,
            'is_first_party'         => false,
            'service_radius_meters'  => 50000,
        ]);
        Address::create([
            'company_id'       => $companyAId,
            'addressable_type' => 'App\Models\Company',
            'address_line_1'   => 'Depot A',
            'city'             => 'Bengaluru',
            'state'            => 'Karnataka',
            'postal_code'      => '560001',
            'country'          => 'India',
            'latitude'         => 12.9716,
            'longitude'        => 77.5946,
        ]);

        // Vendor B
        $companyBId = \Illuminate\Support\Str::uuid()->toString();
        $this->createCompanyRecord($companyBId, 'Vendor B Corp');
        $this->vendorB = Vendor::create([
            'id'                     => \Illuminate\Support\Str::uuid()->toString(),
            'company_id'             => $companyBId,
            'brand_name'             => 'Vendor B Biofuels',
            'status'                 => 'approved',
            'commission_rate'        => 8.00,
            'is_first_party'         => false,
            'service_radius_meters'  => 50000,
        ]);
        Address::create([
            'company_id'       => $companyBId,
            'addressable_type' => 'App\Models\Company',
            'address_line_1'   => 'Depot B',
            'city'             => 'Bengaluru',
            'state'            => 'Karnataka',
            'postal_code'      => '560001',
            'country'          => 'India',
            'latitude'         => 12.9716,
            'longitude'        => 77.5946,
        ]);

        // ── 4. Create Category
        $category = Category::create([
            'name' => 'Fuels',
            'slug' => 'fuels',
        ]);

        // ── 5. Create Products
        $this->diesel = Product::create([
            'category_id'        => $category->id,
            'vendor_id'          => $this->directVendor->id,
            'name'               => 'Direct Diesel',
            'slug'               => 'direct-diesel',
            'sku'                => 'DSL-DIR-100',
            'price_per_unit'     => 90.00,
            'unit_of_measure'    => UnitOfMeasure::Litres,
            'is_active'          => true,
            'ordering_enabled'   => true,
            'min_order_quantity' => 100.0,
        ]);

        $this->biomass = Product::create([
            'category_id'        => $category->id,
            'vendor_id'          => $this->vendorA->id,
            'name'               => 'Biomass Briquettes',
            'slug'               => 'biomass-briquettes',
            'sku'                => 'BIO-MP-200',
            'price_per_unit'     => 12000.00,
            'unit_of_measure'    => UnitOfMeasure::MetricTonnes,
            'is_active'          => true,
            'ordering_enabled'   => true,
            'min_order_quantity' => 1.0,
        ]);

        $this->rdf = Product::create([
            'category_id'        => $category->id,
            'vendor_id'          => $this->vendorB->id,
            'name'               => 'RDF Solid Waste',
            'slug'               => 'rdf-solid-waste',
            'sku'                => 'RDF-MP-300',
            'price_per_unit'     => 8000.00,
            'unit_of_measure'    => UnitOfMeasure::MetricTonnes,
            'is_active'          => true,
            'ordering_enabled'   => true,
            'min_order_quantity' => 1.0,
        ]);

        // ── 6. Get Cart
        $this->cart = Cart::create([
            'user_id' => $this->customer->id,
        ]);
    }

    private function createCompanyRecord(string $id, string $name): void
    {
        \Illuminate\Support\Facades\DB::table('companies')->insert([
            'id'         => $id,
            'name'       => $name,
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Test checkout containing only Direct products.
     */
    public function test_direct_only_cart_creates_single_order(): void
    {
        Sanctum::actingAs($this->customer);

        // Add 500L Diesel to cart
        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO($this->diesel->id, 500.0)
        );

        $checkoutService = app(CheckoutService::class);
        $checkout = $checkoutService->initialize($this->customer->id, $this->cart->id);
        $checkoutService->selectAddress($this->customer->id, $checkout->id, $this->address->id);
        $checkoutService->selectSchedule($this->customer->id, $checkout->id, Carbon::tomorrow()->setHour(12)->toDateTimeString());

        $result = $checkoutService->pay($this->customer->id, $checkout->id, 'wallet');

        $this->assertEquals(1, $result->orderCount());
        $order = $result->primaryOrder();

        $this->assertEquals(SalesChannel::Direct, $order->channel);
        $this->assertEquals($this->directVendor->id, $order->vendor_id);
        $this->assertEquals(45000.00, $order->subtotal_amount);

        // Assert OrderItem snapshot fields
        $item = $order->items->first();
        $this->assertEquals('Direct Diesel', $item->product_name_snapshot);
        $this->assertEquals(SalesChannel::Direct, $item->sales_channel);
        $this->assertEquals(UnitOfMeasure::Litres->value, $item->unit_snapshot);
    }

    /**
     * Test checkout containing only Marketplace products from a single vendor.
     */
    public function test_marketplace_only_cart_creates_single_order(): void
    {
        Sanctum::actingAs($this->customer);

        // Add 2 MT Biomass from Vendor A to cart
        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO($this->biomass->id, 2.0)
        );

        $checkoutService = app(CheckoutService::class);
        $checkout = $checkoutService->initialize($this->customer->id, $this->cart->id);
        $checkoutService->selectAddress($this->customer->id, $checkout->id, $this->address->id);
        $checkoutService->selectSchedule($this->customer->id, $checkout->id, Carbon::tomorrow()->setHour(12)->toDateTimeString());

        $result = $checkoutService->pay($this->customer->id, $checkout->id, 'wallet');

        $this->assertEquals(1, $result->orderCount());
        $order = $result->primaryOrder();

        $this->assertEquals(SalesChannel::Marketplace, $order->channel);
        $this->assertEquals($this->vendorA->id, $order->vendor_id);
        $this->assertEquals(24000.00, $order->subtotal_amount);

        $item = $order->items->first();
        $this->assertEquals('Biomass Briquettes', $item->product_name_snapshot);
        $this->assertEquals(SalesChannel::Marketplace, $item->sales_channel);
        $this->assertEquals($this->vendorA->id, $item->vendor_id);
        $this->assertEquals(UnitOfMeasure::MetricTonnes->value, $item->unit_snapshot);
    }

    /**
     * Test mixed checkout: Direct and Marketplace items.
     * Expects 2 separate orders.
     */
    public function test_mixed_cart_creates_multiple_orders(): void
    {
        Sanctum::actingAs($this->customer);

        // Add 500L Direct Diesel
        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO($this->diesel->id, 500.0)
        );

        // Add 2 MT Marketplace Biomass (Vendor A)
        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO($this->biomass->id, 2.0)
        );

        $checkoutService = app(CheckoutService::class);
        $checkout = $checkoutService->initialize($this->customer->id, $this->cart->id);
        $checkoutService->selectAddress($this->customer->id, $checkout->id, $this->address->id);
        $checkoutService->selectSchedule($this->customer->id, $checkout->id, Carbon::tomorrow()->setHour(12)->toDateTimeString());

        $result = $checkoutService->pay($this->customer->id, $checkout->id, 'wallet');

        // Verify 2 orders generated
        $this->assertEquals(2, $result->orderCount());

        $directOrder = $result->orders->first(fn ($o) => $o->channel === SalesChannel::Direct);
        $marketOrder = $result->orders->first(fn ($o) => $o->channel === SalesChannel::Marketplace);

        $this->assertNotNull($directOrder);
        $this->assertNotNull($marketOrder);

        $this->assertEquals($this->directVendor->id, $directOrder->vendor_id);
        $this->assertEquals(45000.00, $directOrder->subtotal_amount);

        $this->assertEquals($this->vendorA->id, $marketOrder->vendor_id);
        $this->assertEquals(24000.00, $marketOrder->subtotal_amount);
    }

    /**
     * Test checkout containing items from multiple distinct marketplace vendors.
     * Expects separate orders for each vendor.
     */
    public function test_multiple_marketplace_vendors_cart_groups_correctly(): void
    {
        Sanctum::actingAs($this->customer);

        // Add 2 MT Marketplace Biomass (Vendor A)
        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO($this->biomass->id, 2.0)
        );

        // Add 3 MT Marketplace RDF (Vendor B)
        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO($this->rdf->id, 3.0)
        );

        $checkoutService = app(CheckoutService::class);
        $checkout = $checkoutService->initialize($this->customer->id, $this->cart->id);
        $checkoutService->selectAddress($this->customer->id, $checkout->id, $this->address->id);
        $checkoutService->selectSchedule($this->customer->id, $checkout->id, Carbon::tomorrow()->setHour(12)->toDateTimeString());

        $result = $checkoutService->pay($this->customer->id, $checkout->id, 'wallet');

        // Verify 2 separate Marketplace orders generated
        $this->assertEquals(2, $result->orderCount());

        $orderA = $result->orders->first(fn ($o) => $o->vendor_id === $this->vendorA->id);
        $orderB = $result->orders->first(fn ($o) => $o->vendor_id === $this->vendorB->id);

        $this->assertNotNull($orderA);
        $this->assertNotNull($orderB);

        $this->assertEquals(SalesChannel::Marketplace, $orderA->channel);
        $this->assertEquals(24000.00, $orderA->subtotal_amount);

        $this->assertEquals(SalesChannel::Marketplace, $orderB->channel);
        $this->assertEquals(24000.00, $orderB->subtotal_amount); // 3 * 8000
    }

    /**
     * Test that order items preserve price, name, and unit snapshots historically,
     * even if product data changes later.
     */
    public function test_order_items_carry_historical_snapshots(): void
    {
        Sanctum::actingAs($this->customer);

        app(AddItemToCartAction::class)->execute(
            $this->cart,
            new AddCartItemDTO($this->diesel->id, 500.0)
        );

        $checkoutService = app(CheckoutService::class);
        $checkout = $checkoutService->initialize($this->customer->id, $this->cart->id);
        $checkoutService->selectAddress($this->customer->id, $checkout->id, $this->address->id);
        $checkoutService->selectSchedule($this->customer->id, $checkout->id, Carbon::tomorrow()->setHour(12)->toDateTimeString());

        $result = $checkoutService->pay($this->customer->id, $checkout->id, 'wallet');
        $orderItem = $result->primaryOrder()->items->first();

        // Validate initial snapshots
        $this->assertEquals('Direct Diesel', $orderItem->product_name_snapshot);
        $this->assertEquals(90.00, $orderItem->price_per_unit);
        $this->assertEquals('litres', $orderItem->unit_snapshot);

        // Edit live product information
        $this->diesel->update([
            'name'            => 'Premium Super Diesel',
            'price_per_unit'  => 140.00,
            'unit_of_measure' => UnitOfMeasure::Units,
        ]);

        // Refresh and check that snapshots are completely unaffected
        $refreshedItem = $orderItem->fresh();
        $this->assertEquals('Direct Diesel', $refreshedItem->product_name_snapshot);
        $this->assertEquals(90.00, $refreshedItem->price_per_unit);
        $this->assertEquals('litres', $refreshedItem->unit_snapshot);
    }
}
