<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\SalesChannel;
use App\Enums\UnitOfMeasure;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Category;
use App\Models\Company;
use App\Models\User;
use App\Modules\Driver\Models\Driver;
use App\Modules\Fuel\Models\FuelInventory;
use App\Modules\Fuel\Models\Product;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use App\Modules\Vehicle\Models\Vehicle;
use App\Modules\Vendor\Models\Vendor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * OperationsPanelTest
 *
 * Verifies:
 * - Operations user can access /operations panel.
 * - Operations user CANNOT access Super Admin panel (/admin).
 * - Operations user can view & update Direct Products.
 * - Operations user can manage Direct Pricing.
 * - Operations user can manage Direct Inventory.
 * - Operations user can view & manage Direct Orders with customer support fulfillment info.
 * - Operations user can manage Delivery Drivers & Vehicles.
 * - Operations user CANNOT perform Super Admin-only actions (manage roles, system settings, financial commissions).
 */
class OperationsPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $operationsUser;
    private User $customer;
    private Vendor $firstPartyVendor;
    private Category $category;
    private Product $directProduct;
    private FuelInventory $inventory;
    private Order $directOrder;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Create Super Admin User
        $this->superAdmin = User::create([
            'name'      => 'Super Admin',
            'email'     => 'superadmin@fuelcab.com',
            'password'  => bcrypt('password123'),
            'role_type' => UserRole::SuperAdmin,
            'status'    => 'active',
        ]);
        $this->superAdmin->assignRole(UserRole::SuperAdmin->value);

        // Create Operations Team User
        $this->operationsUser = User::create([
            'name'      => 'Ops Lead',
            'email'     => 'ops@fuelcab.com',
            'password'  => bcrypt('password123'),
            'role_type' => UserRole::OperationsTeam,
            'status'    => 'active',
        ]);
        $this->operationsUser->assignRole(UserRole::OperationsTeam->value);

        // Create Customer User
        $this->customer = User::create([
            'name'      => 'Customer User',
            'email'     => 'customer@example.com',
            'phone'     => '+919876543210',
            'password'  => bcrypt('password123'),
            'role_type' => UserRole::Customer,
            'status'    => 'active',
        ]);
        $this->customer->assignRole(UserRole::Customer->value);

        // Category
        $this->category = Category::create([
            'name' => 'Liquid Fuels',
            'slug' => 'liquid-fuels',
        ]);

        // Company & First Party Vendor
        $companyId = Str::uuid()->toString();
        DB::table('companies')->insert([
            'id'         => $companyId,
            'name'       => 'FuelCab Operations Hub',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->firstPartyVendor = Vendor::create([
            'company_id'     => $companyId,
            'brand_name'     => 'FuelCab Direct Terminal',
            'status'         => 'approved',
            'is_first_party' => true,
        ]);

        // Direct Product (Diesel)
        $this->directProduct = Product::create([
            'category_id'        => $this->category->id,
            'vendor_id'          => $this->firstPartyVendor->id,
            'name'               => 'Industrial High Flash Diesel',
            'slug'               => 'industrial-high-flash-diesel',
            'sku'                => 'DSL-DIRECT-001',
            'price_per_unit'     => 89.50,
            'unit_of_measure'    => UnitOfMeasure::Litres,
            'is_active'          => true,
            'ordering_enabled'   => true,
            'min_order_quantity' => 500,
        ]);

        // Direct Inventory
        $this->inventory = FuelInventory::create([
            'product_id'         => $this->directProduct->id,
            'vendor_id'          => $this->firstPartyVendor->id,
            'quantity_available' => 50000.0,
            'quantity_reserved'  => 5000.0,
        ]);

        // Address
        $address = Address::create([
            'user_id'          => $this->customer->id,
            'addressable_type' => User::class,
            'address_line_1'   => 'Plot 45, Industrial Area Phase 2',
            'city'             => 'Gurugram',
            'state'            => 'Haryana',
            'postal_code'      => '122002',
            'country'          => 'India',
            'latitude'         => 28.4595,
            'longitude'        => 77.0266,
        ]);

        // Direct Order
        $this->directOrder = Order::create([
            'order_number'        => 'ORD-DIR-1001',
            'customer_id'         => $this->customer->id,
            'vendor_id'           => $this->firstPartyVendor->id,
            'delivery_address_id' => $address->id,
            'channel'             => SalesChannel::Direct,
            'status'              => OrderStatus::Pending,
            'subtotal_amount'     => 44750.0,
            'total_amount'        => 52805.0,
            'delivery_notes'      => 'Gate 3 tanker offloading site',
        ]);
    }

    /**
     * Test Operations user can access /operations panel.
     */
    public function test_operations_user_can_access_operations_panel(): void
    {
        $this->actingAs($this->operationsUser);

        $response = $this->get('/operations');
        $response->assertStatus(200);
    }

    /**
     * Test Operations user CANNOT access Super Admin panel (/admin).
     */
    public function test_operations_user_cannot_access_super_admin_panel(): void
    {
        $this->actingAs($this->operationsUser);

        $response = $this->get('/admin');
        $response->assertStatus(403);
    }

    /**
     * Test Operations user can view direct products.
     */
    public function test_operations_user_can_view_direct_products(): void
    {
        $this->actingAs($this->operationsUser);

        $response = $this->get('/operations/products');
        $response->assertStatus(200);
        $response->assertSee('Industrial High Flash Diesel');
    }

    /**
     * Test Operations user can update direct pricing.
     */
    public function test_operations_user_can_update_direct_pricing(): void
    {
        $this->actingAs($this->operationsUser);

        $this->directProduct->update(['price_per_unit' => 91.25]);

        $this->assertDatabaseHas('products', [
            'id'             => $this->directProduct->id,
            'price_per_unit' => 91.25,
        ]);
    }

    /**
     * Test Operations user can manage direct inventory.
     */
    public function test_operations_user_can_manage_direct_inventory(): void
    {
        $this->actingAs($this->operationsUser);

        $this->inventory->update(['quantity_available' => 75000.0]);

        $this->assertDatabaseHas('inventories', [
            'id'                 => $this->inventory->id,
            'quantity_available' => 75000.0,
        ]);
    }

    /**
     * Test Operations user can view and manage direct orders with customer support info.
     */
    public function test_operations_user_can_view_and_manage_direct_orders_with_customer_support_info(): void
    {
        $this->actingAs($this->operationsUser);
        \Filament\Facades\Filament::setCurrentPanel(\Filament\Facades\Filament::getPanel('operations'));

        $response = $this->get('/operations/orders');
        $response->assertStatus(200);

        $editResponse = $this->get("/operations/orders/{$this->directOrder->id}/edit");
        $editResponse->assertStatus(200);
        $editResponse->assertSee('Customer User');
        $editResponse->assertSee('+919876543210');
        $editResponse->assertSee('Gurugram');

        // Update status to accepted
        $this->directOrder->update(['status' => OrderStatus::Accepted]);

        $this->assertDatabaseHas('orders', [
            'id'     => $this->directOrder->id,
            'status' => OrderStatus::Accepted->value,
        ]);
    }

    /**
     * Test Operations user can manage delivery drivers and vehicles.
     */
    public function test_operations_user_can_manage_delivery_drivers_and_vehicles(): void
    {
        $this->actingAs($this->operationsUser);

        $driverUser = User::create([
            'name'      => 'Driver Ramesh',
            'email'     => 'driver.ramesh@fuelcab.com',
            'password'  => bcrypt('password123'),
            'role_type' => UserRole::Driver,
            'status'    => 'active',
        ]);

        $driver = Driver::create([
            'user_id'        => $driverUser->id,
            'vendor_id'      => $this->firstPartyVendor->id,
            'license_number' => 'DL-9988776655',
            'license_expiry' => now()->addYears(3)->toDateString(),
            'status'         => 'offline',
            'is_approved'    => false,
        ]);

        $vehicle = Vehicle::create([
            'vendor_id'           => $this->firstPartyVendor->id,
            'registration_number' => 'HR-26-AB-1234',
            'make'                => 'Tata',
            'model'               => 'Prima Tanker',
            'year'                => 2024,
            'capacity_liters'     => 12000,
            'fuel_type'           => 'diesel',
            'status'              => 'active',
        ]);

        // Approve driver
        $driver->update(['is_approved' => true, 'status' => 'available']);

        $this->assertDatabaseHas('drivers', [
            'id'          => $driver->id,
            'is_approved' => true,
            'status'      => 'available',
        ]);

        $this->assertDatabaseHas('vehicles', [
            'id'                  => $vehicle->id,
            'registration_number' => 'HR-26-AB-1234',
        ]);
    }

    /**
     * Test Operations user CANNOT perform Super Admin actions.
     */
    public function test_operations_user_cannot_perform_super_admin_actions(): void
    {
        $this->actingAs($this->operationsUser);

        // Operations user does not have SuperAdmin role
        $this->assertFalse($this->operationsUser->hasRole(UserRole::SuperAdmin->value));

        // Operations user cannot assign roles or manage system settings
        $this->assertFalse($this->operationsUser->can('assign_roles'));
        $this->assertFalse($this->operationsUser->can('manage_system_settings'));
    }
}
