<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Address;
use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderStatusLog;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Notifications\OrderPlacedNotification;
use App\Modules\Order\Notifications\OrderAcceptedNotification;
use App\Modules\Order\Notifications\DriverAssignedNotification;
use App\Modules\Order\Notifications\OrderOutForDeliveryNotification;
use App\Modules\Order\Notifications\OrderDeliveredNotification;
use App\Modules\Order\Notifications\OrderCancelledNotification;
use App\Modules\Order\Notifications\NewOrderAssignedToDriverNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $driver;
    private User $vendorAdmin;
    private User $vendorStaff;
    private User $unauthorizedVendorAdmin;
    private User $superAdmin;
    private Vendor $vendor;
    private Vendor $otherVendor;
    private Address $address;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // 1. Create Companies
        $companyId = \Illuminate\Support\Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('companies')->insert([
            'id'         => $companyId,
            'name'       => 'Apex Logistics LLC',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $otherCompanyId = \Illuminate\Support\Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('companies')->insert([
            'id'         => $otherCompanyId,
            'name'       => 'Other Logistics LLC',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // 2. Create Vendors
        $this->vendor = Vendor::create([
            'id'                     => \Illuminate\Support\Str::uuid()->toString(),
            'company_id'             => $companyId,
            'brand_name'             => 'Apex Fuels',
            'status'                 => 'approved',
            'commission_rate'        => 5.00,
            'service_radius_meters'  => 10000,
        ]);

        $this->otherVendor = Vendor::create([
            'id'                     => \Illuminate\Support\Str::uuid()->toString(),
            'company_id'             => $otherCompanyId,
            'brand_name'             => 'Other Fuels',
            'status'                 => 'approved',
            'commission_rate'        => 4.50,
            'service_radius_meters'  => 8000,
        ]);

        // 3. Create Users
        $this->customer = User::create([
            'name'      => 'John Customer',
            'email'     => 'customer@fuelcab.com',
            'phone'     => '+919999999999',
            'password'  => bcrypt('password123'),
            'role_type' => \App\Enums\UserRole::Customer,
        ]);
        $this->customer->assignRole('customer');

        $this->driver = User::create([
            'name'      => 'Bob Driver',
            'email'     => 'driver@fuelcab.com',
            'phone'     => '+918888888888',
            'password'  => bcrypt('password123'),
            'role_type' => \App\Enums\UserRole::Driver,
        ]);
        $this->driver->assignRole('driver');

        // Create driver record in drivers table
        \Illuminate\Support\Facades\DB::table('drivers')->insert([
            'id'             => \Illuminate\Support\Str::uuid()->toString(),
            'user_id'        => $this->driver->id,
            'license_number' => 'DL-999999',
            'license_expiry' => now()->addYears(5)->toDateString(),
            'status'         => 'available',
            'is_approved'    => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $this->vendorAdmin = User::create([
            'name'      => 'Alice VendorAdmin',
            'email'     => 'vendoradmin@fuelcab.com',
            'phone'     => '+917777777777',
            'password'  => bcrypt('password123'),
            'role_type' => \App\Enums\UserRole::VendorAdmin,
            'vendor_id' => $this->vendor->id,
        ]);
        $this->vendorAdmin->assignRole('vendor_admin');

        $this->vendorStaff = User::create([
            'name'      => 'Charlie Staff',
            'email'     => 'staff@fuelcab.com',
            'phone'     => '+916666666666',
            'password'  => bcrypt('password123'),
            'role_type' => \App\Enums\UserRole::VendorStaff,
            'vendor_id' => $this->vendor->id,
        ]);
        $this->vendorStaff->assignRole('vendor_staff');

        $this->unauthorizedVendorAdmin = User::create([
            'name'      => 'Mallory BadAdmin',
            'email'     => 'badadmin@fuelcab.com',
            'phone'     => '+915555555555',
            'password'  => bcrypt('password123'),
            'role_type' => \App\Enums\UserRole::VendorAdmin,
            'vendor_id' => $this->otherVendor->id,
        ]);
        $this->unauthorizedVendorAdmin->assignRole('vendor_admin');

        $this->superAdmin = User::create([
            'name'      => 'Super Admin',
            'email'     => 'superadmin@fuelcab.com',
            'phone'     => '+914444444444',
            'password'  => bcrypt('password123'),
            'role_type' => \App\Enums\UserRole::SuperAdmin,
        ]);
        $this->superAdmin->assignRole('super_admin');

        // 4. Create Address
        $this->address = Address::create([
            'user_id'          => $this->customer->id,
            'addressable_type' => 'App\Models\User',
            'address_line_1'   => '123 Business Rd',
            'city'             => 'Bengaluru',
            'state'            => 'Karnataka',
            'postal_code'      => '560001',
            'latitude'         => 12.9716,
            'longitude'        => 77.5946,
        ]);

        // 5. Create Order (Default status: pending)
        $this->order = Order::create([
            'customer_id'         => $this->customer->id,
            'vendor_id'           => $this->vendor->id,
            'delivery_address_id' => $this->address->id,
            'status'              => OrderStatus::Pending,
            'subtotal_amount'     => 8850.00,
            'delivery_fee'        => 150.00,
            'tax_amount'          => 1620.00,
            'total_amount'        => 10620.00,
        ]);
    }

    public function test_vendor_admin_can_accept_pending_order(): void
    {
        Notification::fake();

        Sanctum::actingAs($this->vendorAdmin);

        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/accept");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Order accepted successfully.',
                'data' => [
                    'id'     => $this->order->id,
                    'status' => 'accepted',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'id'     => $this->order->id,
            'status' => 'accepted',
        ]);

        $this->assertDatabaseHas('order_status_logs', [
            'order_id'    => $this->order->id,
            'from_status' => 'pending',
            'to_status'   => 'accepted',
        ]);

        Notification::assertSentTo(
            $this->customer,
            OrderAcceptedNotification::class
        );
    }

    public function test_customer_cannot_accept_order(): void
    {
        Sanctum::actingAs($this->customer);

        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/accept");

        $response->assertStatus(403);
    }

    public function test_unauthorized_vendor_admin_cannot_accept_order(): void
    {
        Sanctum::actingAs($this->unauthorizedVendorAdmin);

        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/accept");

        $response->assertStatus(404);
    }

    public function test_vendor_admin_can_assign_driver_to_accepted_order(): void
    {
        Notification::fake();

        Sanctum::actingAs($this->vendorAdmin);

        // Pre-transition to accepted
        $this->order->update(['status' => OrderStatus::Accepted]);
        OrderStatusLog::create([
            'order_id'    => $this->order->id,
            'from_status' => OrderStatus::Pending,
            'to_status'   => OrderStatus::Accepted,
        ]);

        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/assign-driver", [
            'driver_id' => $this->driver->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Driver assigned successfully.',
                'data' => [
                    'id'        => $this->order->id,
                    'driver_id' => $this->driver->id,
                    'status'    => 'assigned',
                ],
            ]);

        $this->assertDatabaseHas('orders', [
            'id'        => $this->order->id,
            'driver_id' => $this->driver->id,
            'status'    => 'assigned',
        ]);

        Notification::assertSentTo(
            $this->driver,
            NewOrderAssignedToDriverNotification::class
        );

        Notification::assertSentTo(
            $this->customer,
            DriverAssignedNotification::class
        );
    }

    public function test_cannot_assign_non_driver_user(): void
    {
        Sanctum::actingAs($this->vendorAdmin);
        $this->order->update(['status' => OrderStatus::Accepted]);

        // Attempting to assign customer as driver
        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/assign-driver", [
            'driver_id' => $this->customer->id,
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'User is not a driver.',
            ]);
    }

    public function test_driver_can_transition_lifecycle_after_assignment(): void
    {
        Notification::fake();

        // Setup: Order is assigned to driver
        $this->order->update([
            'status'    => OrderStatus::Assigned,
            'driver_id' => $this->driver->id,
        ]);

        Sanctum::actingAs($this->driver);

        // 1. Transition to out_for_delivery
        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'out_for_delivery',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'out_for_delivery');

        Notification::assertSentTo(
            $this->customer,
            OrderOutForDeliveryNotification::class
        );

        // 2. Transition to delivered
        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'delivered',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'delivered');

        $this->assertNotNull($this->order->fresh()->delivered_at);

        Notification::assertSentTo(
            $this->customer,
            OrderDeliveredNotification::class
        );
    }

    public function test_invalid_transitions_are_rejected(): void
    {
        Sanctum::actingAs($this->vendorAdmin);

        // Attempting pending directly to delivered
        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'delivered',
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
            ]);
    }

    public function test_can_record_and_get_tracking_locations(): void
    {
        // Setup: driver is active and order is out_for_delivery
        $this->order->update([
            'status'    => OrderStatus::OutForDelivery,
            'driver_id' => $this->driver->id,
        ]);

        Sanctum::actingAs($this->driver);

        // Post tracking point
        $response = $this->postJson("/api/v1/orders/{$this->order->id}/tracking", [
            'latitude'  => 12.9810,
            'longitude' => 77.6010,
        ]);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'data' => [
                    'latitude'  => 12.9810,
                    'longitude' => 77.6010,
                    'status'    => 'out_for_delivery',
                ],
            ]);

        // Customer can view the tracking trail
        Sanctum::actingAs($this->customer);

        $response = $this->getJson("/api/v1/orders/{$this->order->id}/tracking");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'order_id',
                    'status',
                    'latest_location' => ['latitude', 'longitude'],
                    'coordinate_trail' => [
                        ['latitude', 'longitude', 'recorded_at']
                    ],
                ],
            ]);
    }

    public function test_role_scoped_orders_list(): void
    {
        // Create an order for another vendor and customer
        $otherCustomer = User::create([
            'name'      => 'Other Customer',
            'email'     => 'othercust@fuelcab.com',
            'phone'     => '+913333333333',
            'password'  => bcrypt('password123'),
            'role_type' => \App\Enums\UserRole::Customer,
        ]);

        $otherOrder = Order::create([
            'customer_id'         => $otherCustomer->id,
            'vendor_id'           => $this->otherVendor->id,
            'delivery_address_id' => $this->address->id,
            'status'              => OrderStatus::Pending,
            'subtotal_amount'     => 5000.00,
            'delivery_fee'        => 100.00,
            'tax_amount'          => 900.00,
            'total_amount'        => 6000.00,
        ]);

        // 1. Customer should only see their own order (1 order)
        Sanctum::actingAs($this->customer);
        $response = $this->getJson('/api/v1/orders');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->order->id, $response->json('data.0.id'));

        // 2. Vendor Admin should only see orders for their vendor (1 order)
        Sanctum::actingAs($this->vendorAdmin);
        $response = $this->getJson('/api/v1/orders');
        $response->assertStatus(200);
        $this->assertCount(1, $response->json('data'));
        $this->assertEquals($this->order->id, $response->json('data.0.id'));

        // 3. Super Admin should see all orders (2 orders)
        Sanctum::actingAs($this->superAdmin);
        $response = $this->getJson('/api/v1/orders');
        $response->assertStatus(200);
        $this->assertCount(2, $response->json('data'));
    }

    public function test_single_order_details_view_authorization(): void
    {
        // 1. Customer can view their own order
        Sanctum::actingAs($this->customer);
        $response = $this->getJson("/api/v1/orders/{$this->order->id}");
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $this->order->id);

        // 2. Unauthorized vendor admin cannot view it (returns 404 due to tenant scope)
        Sanctum::actingAs($this->unauthorizedVendorAdmin);
        $response = $this->getJson("/api/v1/orders/{$this->order->id}");
        $response->assertStatus(404);
    }
}
