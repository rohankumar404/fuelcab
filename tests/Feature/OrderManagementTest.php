<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Address;
use App\Models\Category;
use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderStatusLog;
use App\Modules\Order\Models\OrderTracking;
use App\Modules\Order\Enums\OrderStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;
    private User $driver;
    private Vendor $vendor;
    private Address $address;
    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // 1. Create Customer
        $this->customer = User::create([
            'name'      => 'John Customer',
            'email'     => 'customer@fuelcab.com',
            'password'  => bcrypt('password123'),
            'role_type' => \App\Enums\UserRole::Customer,
        ]);
        $this->customer->assignRole('customer');

        // 2. Create Driver
        $this->driver = User::create([
            'name'      => 'Bob Driver',
            'email'     => 'driver@fuelcab.com',
            'password'  => bcrypt('password123'),
            'role_type' => \App\Enums\UserRole::Driver,
        ]);
        $this->driver->assignRole('driver');

        // Create driver record in drivers table
        \Illuminate\Support\Facades\DB::table('drivers')->insert([
            'id'             => \Illuminate\Support\Str::uuid()->toString(),
            'user_id'        => $this->driver->id,
            'license_number' => 'DL-' . rand(100000, 999999),
            'license_expiry' => now()->addYears(5)->toDateString(),
            'status'         => 'available',
            'is_approved'    => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        // 3. Create Company and Vendor
        $companyId = \Illuminate\Support\Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('companies')->insert([
            'id'         => $companyId,
            'name'       => 'Apex Logistics LLC',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->vendor = Vendor::create([
            'id'                     => \Illuminate\Support\Str::uuid()->toString(),
            'company_id'             => $companyId,
            'brand_name'             => 'Apex Fuels',
            'status'                 => 'approved',
            'commission_rate'        => 5.00,
            'service_radius_meters'  => 10000,
        ]);

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

        event(new \App\Modules\Order\Events\OrderCreated($this->order));
    }

    public function test_can_accept_pending_order(): void
    {
        Sanctum::actingAs($this->customer);

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
    }

    public function test_can_assign_driver_to_accepted_order(): void
    {
        Sanctum::actingAs($this->customer);

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

        $this->assertDatabaseHas('order_status_logs', [
            'order_id'    => $this->order->id,
            'from_status' => 'accepted',
            'to_status'   => 'assigned',
        ]);
    }

    public function test_cannot_assign_non_driver_user(): void
    {
        Sanctum::actingAs($this->customer);
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

    public function test_can_transition_through_lifecycle(): void
    {
        Sanctum::actingAs($this->customer);

        // Transition from assigned to out_for_delivery
        $this->order->update([
            'status'    => OrderStatus::Assigned,
            'driver_id' => $this->driver->id,
        ]);

        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'out_for_delivery',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'out_for_delivery');

        // Transition from out_for_delivery to delivered
        $response = $this->patchJson("/api/v1/orders/{$this->order->id}/status", [
            'status' => 'delivered',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.status', 'delivered');

        $this->assertNotNull($this->order->fresh()->delivered_at);
    }

    public function test_invalid_transitions_are_rejected(): void
    {
        Sanctum::actingAs($this->customer);

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
        Sanctum::actingAs($this->customer);

        $this->order->update([
            'status'    => OrderStatus::OutForDelivery,
            'driver_id' => $this->driver->id,
        ]);

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

        // Get tracking trail
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
}
