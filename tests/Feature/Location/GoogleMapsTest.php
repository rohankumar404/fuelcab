<?php

declare(strict_types=1);

namespace Tests\Feature\Location;

use App\Enums\SalesChannel;
use App\Enums\UserRole;
use App\Exceptions\ApiException;
use App\Models\Address;
use App\Models\User;
use App\Modules\Driver\Events\DriverLocationUpdated;
use App\Modules\Driver\Models\DriverLocation;
use App\Modules\Location\Services\GoogleMapsService;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Models\OrderTracking;
use App\Modules\Vendor\Models\Vendor;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class GoogleMapsTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $driverUser;

    private Vendor $vendor;

    private Address $address;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        // Configure mock API key
        config(['fuelcab.maps.api_key' => 'mock_maps_api_key_123']);

        // Create a customer
        $this->customer = User::create([
            'name' => 'Location Customer',
            'email' => 'loccust@test.com',
            'phone' => '+919999911111',
            'password' => bcrypt('password123'),
            'role_type' => UserRole::Customer,
        ]);

        // Create a driver user
        $this->driverUser = User::create([
            'name' => 'Location Driver',
            'email' => 'locdriver@test.com',
            'phone' => '+919999922222',
            'password' => bcrypt('password123'),
            'role_type' => UserRole::Driver,
        ]);

        // Create driver record in drivers table
        DB::table('drivers')->insert([
            'id' => Str::uuid()->toString(),
            'user_id' => $this->driverUser->id,
            'license_number' => 'DL-'.uniqid(),
            'license_expiry' => '2030-12-31',
            'status' => 'offline',
            'is_approved' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create vendor
        $companyId = Str::uuid()->toString();
        DB::table('companies')->insert([
            'id' => $companyId,
            'name' => 'Maps Corp',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->vendor = Vendor::create([
            'company_id' => $companyId,
            'brand_name' => 'Maps Fuels',
            'status' => 'approved',
            'commission_rate' => 4.00,
        ]);

        // Create Address without coordinates initially
        $this->address = Address::create([
            'user_id' => $this->customer->id,
            'addressable_type' => 'App\Models\User',
            'address_line_1' => 'Gateway Towers, Sector 62',
            'city' => 'Noida',
            'state' => 'Uttar Pradesh',
            'postal_code' => '201301',
            'latitude' => 0.0,
            'longitude' => 0.0,
        ]);

        // Create Order
        $this->order = Order::create([
            'customer_id' => $this->customer->id,
            'driver_id' => $this->driverUser->id, // assign driver
            'vendor_id' => $this->vendor->id,
            'delivery_address_id' => $this->address->id,
            'subtotal_amount' => 500.00,
            'delivery_fee' => 30.00,
            'tax_amount' => 90.00,
            'total_amount' => 620.00,
            'status' => OrderStatus::Pending,
            'channel' => SalesChannel::Direct,
        ]);
    }

    /**
     * Test address autocomplete suggestions endpoint.
     */
    public function test_autocomplete_returns_suggestions(): void
    {
        Http::fake([
            'https://maps.googleapis.com/maps/api/place/autocomplete/json*' => Http::response([
                'status' => 'OK',
                'predictions' => [
                    [
                        'description' => 'Whitefield, Bengaluru, Karnataka, India',
                        'place_id' => 'ChIJc862u-oRrjsR3a3Sg1m7KGA',
                    ],
                ],
            ], 200),
        ]);

        Sanctum::actingAs($this->customer, ['customer:*']);

        $response = $this->postJson(route('api.v1.locations.autocomplete'), [
            'q' => 'Whitefield',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.0.description', 'Whitefield, Bengaluru, Karnataka, India')
            ->assertJsonPath('data.0.place_id', 'ChIJc862u-oRrjsR3a3Sg1m7KGA');
    }

    /**
     * Test geocode endpoint returns coordinates and stores them on the Address model.
     */
    public function test_geocode_calculates_and_stores_coordinates(): void
    {
        Http::fake([
            'https://maps.googleapis.com/maps/api/geocode/json*' => Http::response([
                'status' => 'OK',
                'results' => [
                    [
                        'formatted_address' => 'Gateway Towers, Noida, UP, India',
                        'place_id' => 'ChIJywe-999',
                        'geometry' => [
                            'location' => [
                                'lat' => 28.6282,
                                'lng' => 77.3789,
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Sanctum::actingAs($this->customer, ['customer:*']);

        $response = $this->postJson(route('api.v1.locations.geocode'), [
            'address' => 'Gateway Towers, Sector 62',
            'address_id' => $this->address->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.lat', 28.6282)
            ->assertJsonPath('data.lng', 77.3789);

        // Coordinates should be updated on the DB address record
        $this->assertEquals(28.6282, (float) $this->address->fresh()->latitude);
        $this->assertEquals(77.3789, (float) $this->address->fresh()->longitude);
    }

    /**
     * Test ETA endpoint using Google Distance Matrix.
     */
    public function test_eta_endpoint_returns_minutes_and_km(): void
    {
        Http::fake([
            'https://maps.googleapis.com/maps/api/distancematrix/json*' => Http::response([
                'status' => 'OK',
                'rows' => [
                    [
                        'elements' => [
                            [
                                'status' => 'OK',
                                'distance' => [
                                    'text' => '12.4 km',
                                    'value' => 12400,
                                ],
                                'duration' => [
                                    'text' => '24 mins',
                                    'value' => 1440,
                                ],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Sanctum::actingAs($this->customer, ['customer:*']);

        $response = $this->getJson(route('api.v1.locations.eta', [
            'origin_lat' => 12.9716,
            'origin_lng' => 77.5946,
            'destination_lat' => 12.9279,
            'destination_lng' => 77.6271,
        ]));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.eta_minutes', 24)
            ->assertJsonPath('data.distance_km', 12.4);
    }

    /**
     * Test route optimization endpoint.
     */
    public function test_route_optimization_endpoint(): void
    {
        Http::fake([
            'https://maps.googleapis.com/maps/api/directions/json*' => Http::response([
                'status' => 'OK',
                'routes' => [
                    [
                        'waypoint_order' => [1, 0],
                        'overview_polyline' => [
                            'points' => '_p~iF~ps|U_ulLnnqC',
                        ],
                        'legs' => [
                            [
                                'start_address' => 'A',
                                'end_address' => 'C',
                                'distance' => ['value' => 5000, 'text' => '5.0 km'],
                                'duration' => ['value' => 600, 'text' => '10 mins'],
                            ],
                            [
                                'start_address' => 'C',
                                'end_address' => 'B',
                                'distance' => ['value' => 3000, 'text' => '3.0 km'],
                                'duration' => ['value' => 360, 'text' => '6 mins'],
                            ],
                        ],
                    ],
                ],
            ], 200),
        ]);

        Sanctum::actingAs($this->customer, ['customer:*']);

        $response = $this->postJson(route('api.v1.locations.route.optimize'), [
            'waypoints' => [
                ['lat' => 12.9716, 'lng' => 77.5946, 'label' => 'Start'],
                ['lat' => 12.9279, 'lng' => 77.6271, 'label' => 'Waypoint 1'],
                ['lat' => 12.9562, 'lng' => 77.7011, 'label' => 'Waypoint 2'],
                ['lat' => 12.9716, 'lng' => 77.5946, 'label' => 'End'],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.optimized_order', [1, 0])
            ->assertJsonPath('data.total_distance_km', 8)
            ->assertJsonPath('data.total_duration_min', 16);
    }

    /**
     * Test driver location update endpoint.
     */
    public function test_driver_location_update_upserts_and_fires_event(): void
    {
        Event::fake([DriverLocationUpdated::class]);

        Sanctum::actingAs($this->driverUser, ['driver:*']);

        $response = $this->postJson(route('api.v1.locations.driver.update'), [
            'latitude' => 13.0827,
            'longitude' => 80.2707,
            'speed' => 45.5,
            'heading' => 180.0,
            'order_id' => $this->order->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.latitude', 13.0827)
            ->assertJsonPath('data.longitude', 80.2707);

        // Verify driver location row upserted
        $driverId = DB::table('drivers')
            ->where('user_id', $this->driverUser->id)
            ->value('id');

        $this->assertDatabaseHas('driver_locations', [
            'driver_id' => $driverId,
            'latitude' => 13.0827,
            'longitude' => 80.2707,
            'heading' => 180.0,
            'speed_kmh' => 45.5,
        ]);

        // Verify order tracking breadcrumb created
        $this->assertDatabaseHas('order_tracking', [
            'order_id' => $this->order->id,
            'driver_id' => $driverId,
            'latitude' => 13.0827,
            'longitude' => 80.2707,
        ]);

        Event::assertDispatched(DriverLocationUpdated::class);
    }

    /**
     * Test customer polling live driver location coordinates.
     */
    public function test_customer_can_retrieve_live_driver_location(): void
    {
        $driverId = DB::table('drivers')
            ->where('user_id', $this->driverUser->id)
            ->value('id');

        // Seed driver location
        DriverLocation::create([
            'driver_id' => $driverId,
            'latitude' => 13.0827,
            'longitude' => 80.2707,
            'heading' => 90.0,
            'speed_kmh' => 30.0,
            'recorded_at' => now(),
        ]);

        Sanctum::actingAs($this->customer, ['customer:*']);

        $response = $this->getJson(route('api.v1.locations.driver.live', ['orderId' => $this->order->id]));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.latitude', 13.0827)
            ->assertJsonPath('data.longitude', 80.2707)
            ->assertJsonPath('data.heading', 90)
            ->assertJsonPath('data.speed_kmh', 30);
    }

    /**
     * Test order delivery tracking history returns chronological path.
     */
    public function test_customer_can_retrieve_delivery_tracking_history(): void
    {
        $driverId = DB::table('drivers')
            ->where('user_id', $this->driverUser->id)
            ->value('id');

        // Create tracking breadcrumbs
        OrderTracking::create([
            'order_id' => $this->order->id,
            'driver_id' => $driverId,
            'latitude' => 12.9716,
            'longitude' => 77.5946,
            'status' => 'pending',
            'recorded_at' => now()->subMinutes(10),
        ]);

        OrderTracking::create([
            'order_id' => $this->order->id,
            'driver_id' => $driverId,
            'latitude' => 12.9800,
            'longitude' => 77.6000,
            'status' => 'processing',
            'recorded_at' => now(),
        ]);

        Sanctum::actingAs($this->customer, ['customer:*']);

        $response = $this->getJson(route('api.v1.locations.tracking.history', ['orderId' => $this->order->id]));

        $response->assertStatus(200)
            ->assertJsonPath('success', true)
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.latitude', 12.9716)
            ->assertJsonPath('data.1.latitude', 12.9800);
    }

    /**
     * Test GoogleMapsService throws exception on REQUEST_DENIED.
     */
    public function test_google_maps_service_throws_exception_on_denied_request(): void
    {
        Http::fake([
            'https://maps.googleapis.com/*' => Http::response([
                'status' => 'REQUEST_DENIED',
                'error_message' => 'The provided API key is invalid.',
            ], 200),
        ]);

        $this->expectException(ApiException::class);
        $this->expectExceptionCode(403);

        $service = new GoogleMapsService;
        $service->geocode('Any Address');
    }
}
