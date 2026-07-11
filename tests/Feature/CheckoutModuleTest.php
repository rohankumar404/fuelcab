<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Checkout\Models\Checkout;
use App\Modules\Fuel\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class CheckoutModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private Vendor $vendor;
    private Address $addressWithinRadius;
    private Address $addressOutsideRadius;
    private Product $product;
    private Cart $cart;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // 1. Create Company and Vendor
        $companyId = \Illuminate\Support\Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('companies')->insert([
            'id'         => $companyId,
            'name'       => 'Star Fueling Pvt Ltd',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->vendor = Vendor::create([
            'id'                     => \Illuminate\Support\Str::uuid()->toString(),
            'company_id'             => $companyId,
            'brand_name'             => 'Star Fuels',
            'status'                 => 'approved',
            'commission_rate'        => 6.00,
            'service_radius_meters'  => 15000, // 15 KM
        ]);

        // Create vendor office address (company address) at coordinates (12.9716, 77.5946)
        Address::create([
            'company_id'       => $companyId,
            'addressable_type' => 'App\Models\Company',
            'address_line_1'   => 'Vendor Main Depot',
            'city'             => 'Bengaluru',
            'state'            => 'Karnataka',
            'postal_code'      => '560001',
            'latitude'         => 12.9716,
            'longitude'        => 77.5946,
        ]);

        // 2. Create Customer
        $this->customer = User::create([
            'name'      => 'Alice Customer',
            'email'     => 'alice@checkouttest.com',
            'phone'     => '+919876543210',
            'password'  => bcrypt('password123'),
            'role_type' => \App\Enums\UserRole::Customer,
        ]);
        $this->customer->assignRole('customer');

        // Address 1: 5 KM away (within 15 KM radius) - Latitude: 12.9352, Longitude: 77.6244
        $this->addressWithinRadius = Address::create([
            'user_id'          => $this->customer->id,
            'addressable_type' => 'App\Models\User',
            'address_line_1'   => '5 KM Close Rd',
            'city'             => 'Bengaluru',
            'state'            => 'Karnataka',
            'postal_code'      => '560034',
            'latitude'         => 12.9352,
            'longitude'        => 77.6244,
        ]);

        // Address 2: 25 KM away (outside 15 KM radius) - Latitude: 13.1986, Longitude: 77.7066
        $this->addressOutsideRadius = Address::create([
            'user_id'          => $this->customer->id,
            'addressable_type' => 'App\Models\User',
            'address_line_1'   => '25 KM Far Way',
            'city'             => 'Bengaluru',
            'state'            => 'Karnataka',
            'postal_code'      => '562300',
            'latitude'         => 13.1986,
            'longitude'        => 77.7066,
        ]);

        // 3. Create Product Category & Product
        $category = Category::create([
            'id'          => \Illuminate\Support\Str::uuid()->toString(),
            'name'        => 'High Quality Fuel',
            'slug'        => 'high-quality-fuel',
            'description' => 'Test fuels',
        ]);

        $this->product = Product::create([
            'id'              => \Illuminate\Support\Str::uuid()->toString(),
            'category_id'     => $category->id,
            'vendor_id'       => $this->vendor->id,
            'name'            => 'Premium Diesel',
            'slug'            => 'premium-diesel',
            'sku'             => 'DSL-PRM-001',
            'price_per_unit'  => 90.00,
            'unit_of_measure' => 'litres',
            'is_active'       => true,
            'status'          => 'active',
        ]);

        // 4. Create Active Cart & Cart Items
        $this->cart = Cart::create([
            'user_id'   => $this->customer->id,
            'vendor_id' => $this->vendor->id,
        ]);

        CartItem::create([
            'cart_id'        => $this->cart->id,
            'product_id'     => $this->product->id,
            'quantity'       => 100.00, // 100 liters
            'price_snapshot' => 90.00,
        ]);
    }

    public function test_can_initialize_checkout_session(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->postJson('/api/v1/checkout/initialize', [
            'cart_id' => $this->cart->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Checkout initialized successfully.',
                'data' => [
                    'cart_id' => $this->cart->id,
                    'status'  => 'draft',
                    'pricing_summary' => [
                        'subtotal_amount' => 9000.00,
                    ],
                ],
            ]);

        $this->assertDatabaseHas('checkouts', [
            'cart_id' => $this->cart->id,
            'status'  => 'draft',
        ]);
    }

    public function test_can_select_address_within_radius(): void
    {
        Sanctum::actingAs($this->customer);

        // Initialize session
        $checkout = Checkout::create([
            'user_id'         => $this->customer->id,
            'cart_id'         => $this->cart->id,
            'vendor_id'       => $this->vendor->id,
            'subtotal_amount' => 9000.00,
            'status'          => 'draft',
        ]);

        $response = $this->postJson('/api/v1/checkout/address', [
            'checkout_id' => $checkout->id,
            'address_id'  => $this->addressWithinRadius->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id'         => $checkout->id,
                    'address_id' => $this->addressWithinRadius->id,
                ],
            ]);

        $this->assertDatabaseHas('checkouts', [
            'id'         => $checkout->id,
            'address_id' => $this->addressWithinRadius->id,
        ]);

        $this->assertGreaterThan(0.00, $checkout->fresh()->delivery_fee);
    }

    public function test_cannot_select_address_outside_radius(): void
    {
        Sanctum::actingAs($this->customer);

        $checkout = Checkout::create([
            'user_id'         => $this->customer->id,
            'cart_id'         => $this->cart->id,
            'vendor_id'       => $this->vendor->id,
            'subtotal_amount' => 9000.00,
            'status'          => 'draft',
        ]);

        $response = $this->postJson('/api/v1/checkout/address', [
            'checkout_id' => $checkout->id,
            'address_id'  => $this->addressOutsideRadius->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_can_select_valid_delivery_slot(): void
    {
        Sanctum::actingAs($this->customer);

        $checkout = Checkout::create([
            'user_id'         => $this->customer->id,
            'cart_id'         => $this->cart->id,
            'vendor_id'       => $this->vendor->id,
            'subtotal_amount' => 9000.00,
            'status'          => 'draft',
        ]);

        $scheduledAt = Carbon::tomorrow()->setHour(10)->setMinute(0); // 10:00 AM tomorrow

        $response = $this->postJson('/api/v1/checkout/schedule', [
            'checkout_id'           => $checkout->id,
            'scheduled_delivery_at' => $scheduledAt->toDateTimeString(),
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id'                    => $checkout->id,
                    'scheduled_delivery_at' => $scheduledAt->toDateTimeString(),
                ],
            ]);

        $this->assertEquals(
            $scheduledAt->toDateTimeString(),
            $checkout->fresh()->scheduled_delivery_at->toDateTimeString()
        );
    }

    public function test_can_view_checkout_pricing_summary(): void
    {
        Sanctum::actingAs($this->customer);

        $checkout = Checkout::create([
            'user_id'         => $this->customer->id,
            'cart_id'         => $this->cart->id,
            'vendor_id'       => $this->vendor->id,
            'address_id'      => $this->addressWithinRadius->id,
            'delivery_fee'    => 200.00,
            'subtotal_amount' => 9000.00,
            'status'          => 'draft',
        ]);

        $response = $this->getJson("/api/v1/checkout/{$checkout->id}/summary");

        // Tax split CGST/SGST = 18% of (9000 subtotal + 200 fee) = 1656
        $expectedTax = (9000 + 200) * 0.18;
        $expectedTotal = 9000 + 200 + $expectedTax;

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'pricing_summary' => [
                        'subtotal_amount' => 9000.00,
                        'delivery_fee'    => 200.00,
                        'tax_amount'      => $expectedTax,
                        'total_amount'    => $expectedTotal,
                    ],
                ],
            ]);
    }

    public function test_can_complete_payment_and_place_order(): void
    {
        Event::fake([\App\Modules\Order\Events\OrderCreated::class]);

        Sanctum::actingAs($this->customer);

        $scheduledAt = Carbon::tomorrow()->setHour(12)->setMinute(0);

        $checkout = Checkout::create([
            'user_id'               => $this->customer->id,
            'cart_id'               => $this->cart->id,
            'vendor_id'             => $this->vendor->id,
            'address_id'            => $this->addressWithinRadius->id,
            'scheduled_delivery_at' => $scheduledAt,
            'delivery_fee'          => 200.00,
            'subtotal_amount'       => 9000.00,
            'tax_amount'            => 1656.00,
            'total_amount'          => 10856.00,
            'status'                => 'draft',
        ]);

        $response = $this->postJson('/api/v1/checkout/pay', [
            'checkout_id'    => $checkout->id,
            'payment_method' => 'stripe',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Payment processed and order placed successfully.',
                'data' => [
                    'status' => 'pending',
                ],
            ]);

        // Assert checkout session status changed
        $this->assertEquals('completed', $checkout->fresh()->status);
        $this->assertEquals('success', $checkout->fresh()->payment_status);

        // Assert Order created
        $this->assertDatabaseHas('orders', [
            'customer_id'     => $this->customer->id,
            'vendor_id'       => $this->vendor->id,
            'total_amount'    => 10856.00,
            'delivery_fee'    => 200.00,
        ]);

        // Assert cart items are cleared
        $this->assertEquals(0, $this->cart->items()->count());

        Event::assertDispatched(\App\Modules\Order\Events\OrderCreated::class);
    }
}
