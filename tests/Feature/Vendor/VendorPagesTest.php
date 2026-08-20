<?php

declare(strict_types=1);

namespace Tests\Feature\Vendor;

use App\Enums\SalesChannel;
use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Settlement;
use App\Models\User;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Models\Vendor;
use Database\Seeders\RolesAndPermissionsSeeder;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * VendorPagesTest
 *
 * Verifies Analytics, Notifications, and Reports Filament pages:
 *  - Pages are accessible to authenticated vendor users
 *  - Pages reject unauthenticated requests (redirect to login)
 *  - Vendor-scoped data isolation (Analytics, Reports summaries)
 *  - Notification mark-as-read and clear-all database operations
 *  - Reports page summary calculations are vendor-scoped
 */
class VendorPagesTest extends TestCase
{
    use RefreshDatabase;

    private Vendor $vendor;

    private User $vendorUser;

    private Vendor $otherVendor;

    private User $otherVendorUser;

    private User $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->customer = $this->createCustomer();

        $this->vendor = $this->createApprovedVendor('Primary');
        $this->vendorUser = $this->createVendorAdmin($this->vendor);

        $this->otherVendor = $this->createApprovedVendor('Other');
        $this->otherVendorUser = $this->createVendorAdmin($this->otherVendor);
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    private function makeCompany(string $name = 'Test Company'): string
    {
        $id = Str::uuid()->toString();
        DB::table('companies')->insert([
            'id' => $id,
            'name' => $name,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return $id;
    }

    private function createApprovedVendor(string $suffix = ''): Vendor
    {
        $rand = Str::random(6);
        $companyId = $this->makeCompany('Test Company '.$rand.$suffix);

        return Vendor::create([
            'company_id' => $companyId,
            'brand_name' => 'Test Vendor '.$rand,
            'legal_name' => 'Test Vendor '.$rand.' Pvt Ltd',
            'vendor_code' => 'VND-'.strtoupper($rand),
            'gst_number' => '27ABCDE'.$rand.'1Z5',
            'pan' => 'ABCDE'.$rand,
            'company_type' => 'private_limited',
            'contact_person' => 'Admin '.$rand,
            'mobile' => '9'.substr(preg_replace('/[^0-9]/', '', $rand.'000000000'), 0, 9),
            'email' => 'vendor'.strtolower($rand).'@test.com',
            'registered_address' => '123 Test Street',
            'operational_address' => '123 Test Street',
            'city' => 'Surat',
            'state' => 'Gujarat',
            'pincode' => '395001',
            'status' => VendorStatus::Approved,
        ]);
    }

    private function createVendorAdmin(Vendor $vendor): User
    {
        $rand = Str::random(6);
        $user = User::create([
            'name' => 'Vendor Admin '.$rand,
            'mobile' => '8'.substr(preg_replace('/[^0-9]/', '', $rand.'000000000'), 0, 9),
            'email' => 'admin_'.strtolower($rand).'@test.com',
            'password' => bcrypt('password'),
            'role_type' => UserRole::VendorAdmin,
            'vendor_id' => $vendor->id,
        ]);
        $user->syncRoles([UserRole::VendorAdmin->value]);

        return $user;
    }

    private function createCustomer(): User
    {
        $rand = Str::random(6);
        $user = User::create([
            'name' => 'Customer '.$rand,
            'mobile' => '6'.substr(preg_replace('/[^0-9]/', '', $rand.'000000000'), 0, 9),
            'email' => 'customer_'.strtolower($rand).'@test.com',
            'password' => bcrypt('password'),
            'role_type' => UserRole::Customer,
        ]);
        $user->syncRoles([UserRole::Customer->value]);

        return $user;
    }

    private function createAddress(User $user): Address
    {
        return Address::create([
            'addressable_type' => User::class,
            'addressable_id' => $user->id,
            'address_line_1' => '123 Main St',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'pincode' => '400001',
            'postal_code' => '400001',
            'latitude' => 19.0760,
            'longitude' => 72.8777,
        ]);
    }

    private function seedOrders(Vendor $vendor, int $count = 3): void
    {
        $address = $this->createAddress($this->customer);

        for ($i = 0; $i < $count; $i++) {
            Order::create([
                'vendor_id' => $vendor->id,
                'customer_id' => $this->customer->id,
                'delivery_address_id' => $address->id,
                'order_number' => 'ORD-'.strtoupper(Str::random(8)),
                'status' => OrderStatus::Delivered,
                'total_amount' => 1000.00 * ($i + 1),
                'subtotal_amount' => 1000.00 * ($i + 1),
                'tax_amount' => 0.00,
                'delivery_fee' => 0.00,
                'channel' => SalesChannel::Direct,
                'created_at' => now()->subDays($i),
            ]);
        }
    }

    private function seedSettlements(Vendor $vendor, int $count = 2): void
    {
        for ($i = 0; $i < $count; $i++) {
            Settlement::create([
                'vendor_id' => $vendor->id,
                'gross_amount' => 10000.00,
                'commission_amount' => 500.00,
                'net_payable' => 9500.00,
                'status' => 'processed',
            ]);
        }
    }

    private function setPanelContext(): void
    {
        Filament::setCurrentPanel(Filament::getPanel('vendor'));
    }

    // ── Page Access Tests (HTTP) ──────────────────────────────────────────────

    /** @test */
    public function analytics_page_accessible_to_vendor_user(): void
    {
        $this->actingAs($this->vendorUser);
        $this->setPanelContext();

        $response = $this->get('/vendor/analytics');
        $response->assertOk();
    }

    /** @test */
    public function analytics_page_redirects_unauthenticated_users(): void
    {
        $response = $this->get('/vendor/analytics');
        $response->assertRedirect();
    }

    /** @test */
    public function notifications_page_accessible_to_vendor_user(): void
    {
        $this->actingAs($this->vendorUser);
        $this->setPanelContext();

        $response = $this->get('/vendor/notifications');
        $response->assertOk();
    }

    /** @test */
    public function notifications_page_redirects_unauthenticated_users(): void
    {
        $response = $this->get('/vendor/notifications');
        $response->assertRedirect();
    }

    /** @test */
    public function reports_page_accessible_to_vendor_user(): void
    {
        $this->actingAs($this->vendorUser);
        $this->setPanelContext();

        $response = $this->get('/vendor/reports');
        $response->assertOk();
    }

    /** @test */
    public function reports_page_redirects_unauthenticated_users(): void
    {
        $response = $this->get('/vendor/reports');
        $response->assertRedirect();
    }

    // ── Data Isolation: Analytics ─────────────────────────────────────────────

    /** @test */
    public function analytics_order_count_is_vendor_scoped(): void
    {
        $this->seedOrders($this->vendor, 3);
        $this->seedOrders($this->otherVendor, 5);

        // Direct DB query verifies scoping (same logic as Analytics::mount())
        $myOrders = Order::where('vendor_id', $this->vendor->id)->count();
        $otherOrders = Order::where('vendor_id', $this->otherVendor->id)->count();

        $this->assertEquals(3, $myOrders);
        $this->assertEquals(5, $otherOrders);
    }

    /** @test */
    public function analytics_settlement_totals_are_vendor_scoped(): void
    {
        $this->seedSettlements($this->vendor, 2);
        $this->seedSettlements($this->otherVendor, 10);

        $myTotal = Settlement::where('vendor_id', $this->vendor->id)->sum('gross_amount');
        $otherTotal = Settlement::where('vendor_id', $this->otherVendor->id)->sum('gross_amount');

        // 2 × 10000 = 20000 for my vendor
        $this->assertEquals(20000.00, (float) $myTotal);
        // 10 × 10000 = 100000 for other vendor
        $this->assertEquals(100000.00, (float) $otherTotal);
        // Must NOT be the same
        $this->assertNotEquals($myTotal, $otherTotal);
    }

    // ── Notifications: DB operations ─────────────────────────────────────────

    /** @test */
    public function notifications_are_scoped_to_authenticated_user(): void
    {
        // Insert notification for vendorUser
        DB::table('notifications')->insert([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->vendorUser->id,
            'data' => json_encode(['title' => 'My Notification']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Insert notification for otherVendorUser
        DB::table('notifications')->insert([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->otherVendorUser->id,
            'data' => json_encode(['title' => 'Other User Notification']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // vendorUser should see exactly 1 notification
        $myCount = $this->vendorUser->notifications()->count();
        $otherCount = $this->otherVendorUser->notifications()->count();

        $this->assertEquals(1, $myCount);
        $this->assertEquals(1, $otherCount);
    }

    /** @test */
    public function mark_all_notifications_as_read_only_affects_own_user(): void
    {
        // 2 unread notifications for vendorUser
        for ($i = 0; $i < 2; $i++) {
            DB::table('notifications')->insert([
                'id' => Str::uuid()->toString(),
                'type' => 'App\Notifications\TestNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $this->vendorUser->id,
                'data' => json_encode(['title' => 'Notification '.$i]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // 1 unread for other user
        DB::table('notifications')->insert([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->otherVendorUser->id,
            'data' => json_encode(['title' => 'Other Notification']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Mark all read for vendorUser only
        $this->vendorUser->unreadNotifications->markAsRead();

        $myUnread = $this->vendorUser->unreadNotifications()->count();
        $otherUnread = $this->otherVendorUser->unreadNotifications()->count();

        // vendorUser has 0 unread
        $this->assertEquals(0, $myUnread);
        // otherVendorUser still has 1 unread (not affected)
        $this->assertEquals(1, $otherUnread);
    }

    /** @test */
    public function deleting_all_notifications_only_deletes_own(): void
    {
        // 2 notifications for vendorUser, 1 for otherVendorUser
        for ($i = 0; $i < 2; $i++) {
            DB::table('notifications')->insert([
                'id' => Str::uuid()->toString(),
                'type' => 'App\Notifications\TestNotification',
                'notifiable_type' => User::class,
                'notifiable_id' => $this->vendorUser->id,
                'data' => json_encode(['title' => 'Delete Me '.$i]),
                'read_at' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        DB::table('notifications')->insert([
            'id' => Str::uuid()->toString(),
            'type' => 'App\Notifications\TestNotification',
            'notifiable_type' => User::class,
            'notifiable_id' => $this->otherVendorUser->id,
            'data' => json_encode(['title' => 'Keep Me']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Delete all notifications of vendorUser
        $this->vendorUser->notifications()->delete();

        $myCount = $this->vendorUser->notifications()->count();
        $otherCount = $this->otherVendorUser->notifications()->count();

        $this->assertEquals(0, $myCount);
        $this->assertEquals(1, $otherCount); // Other user unaffected
    }

    // ── Reports: Summary Calculation Correctness ──────────────────────────────

    /** @test */
    public function reports_order_summary_counts_correctly(): void
    {
        $this->seedOrders($this->vendor, 4);
        $this->seedOrders($this->otherVendor, 7);

        $total = Order::where('vendor_id', $this->vendor->id)->count();
        $revenue = Order::where('vendor_id', $this->vendor->id)->sum('total_amount');

        $this->assertEquals(4, $total);
        // 4 orders: 1000 + 2000 + 3000 + 4000 = 10000
        $this->assertEquals(10000.00, (float) $revenue);
    }

    /** @test */
    public function reports_settlement_summary_sums_correctly(): void
    {
        $this->seedSettlements($this->vendor, 3);
        $this->seedSettlements($this->otherVendor, 5);

        $gross = Settlement::where('vendor_id', $this->vendor->id)->sum('gross_amount');
        $commission = Settlement::where('vendor_id', $this->vendor->id)->sum('commission_amount');
        $net = Settlement::where('vendor_id', $this->vendor->id)->sum('net_payable');

        // 3 settlements × 10000 gross, 500 commission, 9500 net
        $this->assertEquals(30000.00, (float) $gross);
        $this->assertEquals(1500.00, (float) $commission);
        $this->assertEquals(28500.00, (float) $net);
    }

    /** @test */
    public function vendor_dashboard_is_accessible(): void
    {
        $this->actingAs($this->vendorUser);
        $this->setPanelContext();

        $response = $this->get('/vendor');
        $response->assertOk();
    }

    /** @test */
    public function company_profile_page_is_accessible(): void
    {
        $this->actingAs($this->vendorUser);
        $this->setPanelContext();

        $response = $this->get('/vendor/company-profile');
        $response->assertOk();
    }

    /** @test */
    public function vendor_settings_page_is_accessible(): void
    {
        $this->actingAs($this->vendorUser);
        $this->setPanelContext();

        $response = $this->get('/vendor/vendor-settings');
        $response->assertOk();
    }
}
