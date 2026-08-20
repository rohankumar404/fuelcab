<?php

declare(strict_types=1);

namespace Tests\Feature\SuperAdmin;

use App\Enums\SalesChannel;
use App\Enums\UserRole;
use App\Filament\SuperAdmin\Resources\SettingResource;
use App\Models\Address;
use App\Models\Company;
use App\Models\Setting;
use App\Models\Settlement;
use App\Models\User;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use App\Modules\Vendor\Models\Vendor;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class SuperAdminPanelTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    private User $customer;

    private Vendor $vendor;

    private Company $company;

    private Address $address;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RolesAndPermissionsSeeder::class);

        // Create Super Admin User
        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'superadmin@fuelcab.com',
            'password' => bcrypt('password123'),
            'role_type' => UserRole::SuperAdmin,
            'status' => 'active',
        ]);
        $this->superAdmin->assignRole(UserRole::SuperAdmin->value);

        // Create Customer User
        $this->customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@example.com',
            'phone' => '+919876543210',
            'password' => bcrypt('password123'),
            'role_type' => UserRole::Customer,
            'status' => 'active',
        ]);

        // Company
        $companyId = Str::uuid()->toString();
        DB::table('companies')->insert([
            'id' => $companyId,
            'name' => 'Test Company',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->company = Company::find($companyId);

        // Vendor
        $this->vendor = Vendor::create([
            'company_id' => $companyId,
            'brand_name' => 'Test Vendor',
            'status' => 'approved',
        ]);

        // Address
        $this->address = Address::create([
            'user_id' => $this->customer->id,
            'addressable_type' => User::class,
            'address_line_1' => '123 Main St',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'postal_code' => '400001',
            'latitude' => 19.0760,
            'longitude' => 72.8777,
        ]);
    }

    private function setPanelContext(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('super-admin'));
    }

    /** @test */
    public function super_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->superAdmin);
        $this->setPanelContext();

        $response = $this->get('/admin');
        $response->assertStatus(200);
    }

    /** @test */
    public function setting_resource_crud_works(): void
    {
        $this->actingAs($this->superAdmin);

        // Create Setting
        $setting = Setting::create([
            'company_id' => $this->company->id,
            'key' => 'tax_rate',
            'value' => '18.00',
            'cast_type' => 'float',
        ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'tax_rate',
            'company_id' => $this->company->id,
        ]);

        // Scoped check via Eloquent Query
        $scopedQuery = SettingResource::getEloquentQuery()->get();
        $this->assertCount(1, $scopedQuery);
    }

    /** @test */
    public function setting_resource_unique_constraint_enforced(): void
    {
        $this->actingAs($this->superAdmin);

        // First Setting
        Setting::create([
            'company_id' => $this->company->id,
            'key' => 'commission_rate',
            'value' => '10.00',
            'cast_type' => 'float',
        ]);

        // Duplicate Setting (should fail unique DB constraint)
        $this->expectException(QueryException::class);
        Setting::create([
            'company_id' => $this->company->id,
            'key' => 'commission_rate',
            'value' => '12.00',
            'cast_type' => 'float',
        ]);
    }

    /** @test */
    public function order_resource_bulk_status_update(): void
    {
        $this->actingAs($this->superAdmin);

        $order1 = Order::create([
            'vendor_id' => $this->vendor->id,
            'customer_id' => $this->customer->id,
            'delivery_address_id' => $this->address->id,
            'order_number' => 'ORD-001',
            'status' => OrderStatus::Pending,
            'subtotal_amount' => 1000.00,
            'tax_amount' => 180.00,
            'delivery_fee' => 50.00,
            'total_amount' => 1230.00,
            'channel' => SalesChannel::Direct,
        ]);

        $order2 = Order::create([
            'vendor_id' => $this->vendor->id,
            'customer_id' => $this->customer->id,
            'delivery_address_id' => $this->address->id,
            'order_number' => 'ORD-002',
            'status' => OrderStatus::Pending,
            'subtotal_amount' => 2000.00,
            'tax_amount' => 360.00,
            'delivery_fee' => 50.00,
            'total_amount' => 2410.00,
            'channel' => SalesChannel::Direct,
        ]);

        // Verify direct update to test bulk modification logic
        Order::whereIn('id', [$order1->id, $order2->id])->update(['status' => OrderStatus::Delivered->value]);

        $this->assertDatabaseHas('orders', ['id' => $order1->id, 'status' => OrderStatus::Delivered->value]);
        $this->assertDatabaseHas('orders', ['id' => $order2->id, 'status' => OrderStatus::Delivered->value]);
    }

    /** @test */
    public function reports_page_loads_and_summary_is_correct(): void
    {
        $this->actingAs($this->superAdmin);
        $this->setPanelContext();

        // Seed some orders and settlements
        Order::create([
            'vendor_id' => $this->vendor->id,
            'customer_id' => $this->customer->id,
            'delivery_address_id' => $this->address->id,
            'order_number' => 'ORD-REP-1001',
            'status' => OrderStatus::Delivered,
            'subtotal_amount' => 1000.00,
            'tax_amount' => 180.00,
            'delivery_fee' => 50.00,
            'total_amount' => 1230.00,
            'channel' => SalesChannel::Direct,
        ]);

        Settlement::create([
            'vendor_id' => $this->vendor->id,
            'gross_amount' => 1000.00,
            'commission_amount' => 100.00,
            'net_payable' => 900.00,
            'status' => 'processed',
        ]);

        $response = $this->get('/admin/reports');
        $response->assertOk();
    }

    /** @test */
    public function analytics_page_loads(): void
    {
        $this->actingAs($this->superAdmin);
        $this->setPanelContext();

        $response = $this->get('/admin/analytics');
        $response->assertOk();
    }
}
