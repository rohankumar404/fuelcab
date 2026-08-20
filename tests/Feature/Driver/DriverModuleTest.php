<?php

declare(strict_types=1);

namespace Tests\Feature\Driver;

use App\Enums\SalesChannel;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\User;
use App\Modules\Driver\Models\Driver;
use App\Modules\Driver\Models\Vehicle;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Events\OrderCompleted;
use App\Modules\Order\Models\Order;
use App\Modules\Vendor\Models\Vendor;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DriverModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $driverUser;

    private Driver $driver;

    private Vehicle $vehicle;

    private User $customer;

    private Vendor $vendor;

    private Address $address;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        // 1. Create Customer
        $this->customer = User::create([
            'name' => 'Driver Customer',
            'email' => 'drivercust@test.com',
            'phone' => '+918888877777',
            'password' => bcrypt('password123'),
            'role_type' => UserRole::Customer,
        ]);

        // 2. Create Driver User
        $this->driverUser = User::create([
            'name' => 'Delivery Driver',
            'email' => 'driveruser@test.com',
            'phone' => '+918888899999',
            'password' => bcrypt('password123'),
            'role_type' => UserRole::Driver,
        ]);

        // 3. Create Vendor
        $companyId = Str::uuid()->toString();
        DB::table('companies')->insert([
            'id' => $companyId,
            'name' => 'Driver Corp',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->vendor = Vendor::create([
            'company_id' => $companyId,
            'brand_name' => 'Driver Fuels',
            'status' => 'approved',
            'commission_rate' => 3.50,
        ]);

        // 4. Create Driver profile record
        $this->driver = Driver::create([
            'user_id' => $this->driverUser->id,
            'license_number' => 'DL-9988776655',
            'license_expiry' => '2030-05-15',
            'status' => 'offline',
            'is_approved' => true,
        ]);

        // 5. Create Vehicle
        $this->vehicle = Vehicle::create([
            'vendor_id' => $this->vendor->id,
            'registration_number' => 'KA-01-MJ-9999',
            'make' => 'Tata',
            'model' => 'LPT 1613 Tanker',
            'year' => 2022,
            'capacity_liters' => 12000.00,
            'fuel_type' => 'diesel',
            'status' => 'active',
        ]);

        // Associate Vehicle to Driver (Active)
        $this->driver->vehicles()->attach($this->vehicle->id, [
            'id' => Str::uuid()->toString(),
            'is_active' => true,
            'assigned_at' => now(),
        ]);

        // 6. Create Address
        $this->address = Address::create([
            'user_id' => $this->customer->id,
            'addressable_type' => 'App\Models\User',
            'address_line_1' => 'Green Glen Layout',
            'city' => 'Bengaluru',
            'state' => 'Karnataka',
            'postal_code' => '560103',
            'latitude' => 12.9189,
            'longitude' => 77.6703,
        ]);

        // 7. Create assigned order
        $this->order = Order::create([
            'customer_id' => $this->customer->id,
            'driver_id' => $this->driverUser->id,
            'vendor_id' => $this->vendor->id,
            'delivery_address_id' => $this->address->id,
            'subtotal_amount' => 1000.00,
            'delivery_fee' => 50.00,
            'tax_amount' => 180.00,
            'total_amount' => 1230.00,
            'status' => OrderStatus::Assigned,
            'channel' => SalesChannel::Direct,
            'delivery_otp' => '123456',
        ]);
    }

    /**
     * Test retrieving driver profile and active vehicle.
     */
    public function test_driver_can_retrieve_profile_with_active_vehicle(): void
    {
        Sanctum::actingAs($this->driverUser, ['driver:*']);

        $response = $this->getJson(route('api.v1.drivers.profile'));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Delivery Driver')
            ->assertJsonPath('data.license_number', 'DL-9988776655')
            ->assertJsonPath('data.active_vehicle.registration_number', 'KA-01-MJ-9999');
    }

    /**
     * Test toggling availability action and endpoint.
     */
    public function test_driver_can_toggle_availability(): void
    {
        Sanctum::actingAs($this->driverUser, ['driver:*']);

        $response = $this->postJson(route('api.v1.drivers.availability.toggle'), [
            'status' => 'available',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.status', 'available');

        $this->assertEquals('available', $this->driver->fresh()->status);
    }

    /**
     * Test fetching assigned orders.
     */
    public function test_driver_can_retrieve_assigned_orders(): void
    {
        Sanctum::actingAs($this->driverUser, ['driver:*']);

        $response = $this->getJson(route('api.v1.drivers.orders.assigned'));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $this->order->id)
            ->assertJsonPath('data.0.delivery_otp', '123456');
    }

    /**
     * Test OTP delivery verification codes.
     */
    public function test_driver_can_verify_delivery_otp_code(): void
    {
        Sanctum::actingAs($this->driverUser, ['driver:*']);

        // Test with incorrect OTP
        $responseBad = $this->postJson(route('api.v1.drivers.orders.verify_otp', ['orderId' => $this->order->id]), [
            'otp' => '999999',
        ]);
        $responseBad->assertStatus(422);

        // Test with correct OTP
        $responseGood = $this->postJson(route('api.v1.drivers.orders.verify_otp', ['orderId' => $this->order->id]), [
            'otp' => '123456',
        ]);
        $responseGood->assertStatus(200)
            ->assertJsonPath('data.otp_verified', true);

        $this->assertNotNull($this->order->fresh()->otp_verified_at);
    }

    /**
     * Test blocking delivery completion without OTP verification.
     */
    public function test_driver_cannot_complete_order_without_otp_verification(): void
    {
        Sanctum::actingAs($this->driverUser, ['driver:*']);

        $response = $this->postJson(route('api.v1.drivers.orders.complete', ['orderId' => $this->order->id]), [
            'photo' => 'photo_path_url_here.jpg',
            'signature' => 'signature_base64_data_here',
        ]);

        // Expect 422 validation/rejection failure
        $response->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    /**
     * Test order completion with photo and signature upload proofs.
     */
    public function test_driver_can_complete_order_delivery_with_proofs(): void
    {
        Event::fake([OrderCompleted::class]);

        Sanctum::actingAs($this->driverUser, ['driver:*']);

        // Verify OTP first
        $this->order->update([
            'otp_verified_at' => now(),
        ]);

        // Toggle status of driver to on_trip
        $this->driver->update(['status' => 'on_trip']);

        $response = $this->postJson(route('api.v1.drivers.orders.complete', ['orderId' => $this->order->id]), [
            'photo' => 'storage/proofs/photo_123.jpg',
            'signature' => 'signature_base64_data_123',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true);

        $this->assertDatabaseHas('orders', [
            'id' => $this->order->id,
            'status' => OrderStatus::Delivered->value,
            'delivery_proof_photo' => 'storage/proofs/photo_123.jpg',
            'delivery_proof_signature' => 'signature_base64_data_123',
        ]);

        // Driver availability should be set back to available
        $this->assertEquals('available', $this->driver->fresh()->status);

        Event::assertDispatched(OrderCompleted::class);
    }

    /**
     * Test trip history retrieves completed/cancelled jobs.
     */
    public function test_driver_can_retrieve_trip_history(): void
    {
        // Change order status to delivered
        $this->order->update(['status' => OrderStatus::Delivered]);

        Sanctum::actingAs($this->driverUser, ['driver:*']);

        $response = $this->getJson(route('api.v1.drivers.orders.trips'));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonStructure([
                'data' => ['data' => [['id', 'status', 'total_amount']]],
            ]);
    }
}
