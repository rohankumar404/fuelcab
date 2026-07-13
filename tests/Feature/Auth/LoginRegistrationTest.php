<?php

declare(strict_types=1);

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Enums\UserRole;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Enums\DocumentStatus;
use App\Modules\Fuel\Models\MarketplaceProduct;
use App\Models\Category;
use App\Enums\UnitOfMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class LoginRegistrationTest extends TestCase
{
    use RefreshDatabase;

    private MarketplaceProduct $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Create a Category & Marketplace Product for Step 6 tests
        $category = Category::create([
            'name' => 'Biomass Briquettes',
            'slug' => 'biomass-briquettes-master',
        ]);

        $this->product = MarketplaceProduct::create([
            'category_id'     => $category->id,
            'name'            => 'Standard Briquettes',
            'slug'            => 'standard-briquettes-master',
            'unit_of_measure' => UnitOfMeasure::MetricTonnes,
        ]);

        Storage::fake('local');
    }

    /**
     * Test traditional customer registration.
     */
    public function test_customer_registration(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name'                  => 'John Doe',
            'email'                 => 'john@example.com',
            'phone'                 => '+919000000001',
            'password'              => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'user' => ['id', 'name', 'email', 'phone', 'role_type'],
                    'access_token',
                ]
            ]);

        $this->assertDatabaseHas('users', [
            'email'     => 'john@example.com',
            'role_type' => UserRole::Customer->value,
        ]);
    }

    /**
     * Test traditional customer login.
     */
    public function test_customer_login(): void
    {
        $user = User::create([
            'name'     => 'Jane Doe',
            'email'    => 'jane@example.com',
            'phone'    => '+919000000002',
            'password' => Hash::make('password123'),
            'role_type' => UserRole::Customer,
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email'    => 'jane@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => ['access_token']
            ]);
    }

    /**
     * Test existing user OTP login.
     */
    public function test_existing_user_otp_login(): void
    {
        $user = User::create([
            'name'      => 'Existing User',
            'email'     => 'existing@example.com',
            'phone'     => '+919000000003',
            'password'  => Hash::make('password123'),
            'role_type' => UserRole::Customer,
        ]);

        // 1. Send OTP
        $sendResponse = $this->postJson('/api/v1/auth/send-otp', [
            'phone' => '+919000000003',
        ]);
        $sendResponse->assertStatus(200);

        $cachedOtp = Cache::get('otp_+919000000003');
        $this->assertNotNull($cachedOtp);

        // 2. Verify OTP
        $verifyResponse = $this->postJson('/api/v1/auth/verify-otp', [
            'phone' => '+919000000003',
            'otp'   => $cachedOtp,
        ]);

        $verifyResponse->assertStatus(200)
            ->assertJsonPath('data.is_new_user', false)
            ->assertJsonStructure([
                'success',
                'data' => ['access_token', 'user']
            ]);
    }

    /**
     * Test new customer OTP flow.
     */
    public function test_new_customer_otp_flow(): void
    {
        // 1. Send OTP for new number
        $this->postJson('/api/v1/auth/send-otp', [
            'phone' => '+919000000004',
        ])->assertStatus(200);

        $cachedOtp = Cache::get('otp_+919000000004');
        $this->assertNotNull($cachedOtp);

        // 2. Verify OTP for new number
        $response = $this->postJson('/api/v1/auth/verify-otp', [
            'phone' => '+919000000004',
            'otp'   => $cachedOtp,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.is_new_user', true);

        $this->assertDatabaseHas('users', [
            'phone'     => '+919000000004',
            'role_type' => UserRole::Customer->value,
        ]);
    }

    /**
     * Test vendor application submission workflow (Steps 1 to 7).
     */
    public function test_vendor_application(): void
    {
        $user = User::create([
            'name'      => 'Vendor Applicant',
            'email'     => 'applicant@test.com',
            'phone'     => '+919000000005',
            'password'  => Hash::make('password123'),
            'role_type' => UserRole::Customer,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/vendor/applications', [
            // Step 1: Business Details
            'brand_name'            => 'Green Fuels Co',
            'company_type'          => 'proprietorship',
            // Step 2: Contact Details
            'contact_person'        => 'Alice',
            'mobile'                => '+919000000005',
            'email'                 => 'applicant@test.com',
            // Step 3: Address
            'registered_address'    => '100 Green Ave, Delhi',
            'operational_address'   => '100 Green Ave, Delhi',
            'city'                  => 'Delhi',
            'state'                 => 'Delhi',
            'pincode'               => '110001',
            'latitude'              => 28.6139,
            'longitude'             => 77.2090,
            // Step 4: GST and PAN
            'gst_number'            => '07AAAAA1111A1Z1',
            'pan'                   => 'ABCDE1234F',
            // Step 5: Document Upload
            'gst_certificate'       => UploadedFile::fake()->create('gst.pdf', 500),
            'pan_card'              => UploadedFile::fake()->create('pan.jpg', 500),
            'business_registration' => UploadedFile::fake()->create('reg.png', 500),
            'cancelled_cheque'      => UploadedFile::fake()->create('cheque.pdf', 500),
            // Step 6: Product Interests
            'product_ids'           => [$this->product->id],
        ]);

        $response->assertStatus(201)
            ->assertJsonPath('data.status', VendorStatus::Pending->value);

        // Assert Vendor record created and status = PENDING
        $vendorId = $response->json('data.vendor_id');
        $this->assertDatabaseHas('vendors', [
            'id'     => $vendorId,
            'status' => VendorStatus::Pending->value,
        ]);

        // Assert user linked to vendor
        $this->assertEquals($vendorId, $user->fresh()->vendor_id);

        // Assert Document records created
        $this->assertDatabaseHas('vendor_documents', [
            'vendor_id'     => $vendorId,
            'document_type' => 'gst_certificate',
            'status'        => DocumentStatus::Pending->value,
        ]);
    }

    /**
     * Test pending vendor panel denial.
     */
    public function test_pending_vendor_panel_denial(): void
    {
        $companyId = \Illuminate\Support\Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('companies')->insert([
            'id'         => $companyId,
            'name'       => 'Pending Co',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $pendingVendor = Vendor::create([
            'company_id'            => $companyId,
            'brand_name'            => 'Pending Vendor',
            'status'                => VendorStatus::Pending,
            'commission_rate'       => 5.0,
            'service_radius_meters' => 5000,
        ]);

        $user = User::create([
            'name'      => 'Pending Vendor Admin',
            'email'     => 'pending@test.com',
            'phone'     => '+919000000006',
            'password'  => Hash::make('password123'),
            'vendor_id' => $pendingVendor->id,
            'role_type' => UserRole::VendorAdmin,
        ]);
        $user->assignRole(UserRole::VendorAdmin->value);

        $panel = \Filament\Facades\Filament::getPanel('vendor');

        // canAccessPanel must return false
        $this->assertFalse($user->canAccessPanel($panel));
    }

    /**
     * Test approved vendor panel access.
     */
    public function test_approved_vendor_panel_access(): void
    {
        $companyId = \Illuminate\Support\Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('companies')->insert([
            'id'         => $companyId,
            'name'       => 'Approved Co',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $approvedVendor = Vendor::create([
            'company_id'            => $companyId,
            'brand_name'            => 'Approved Vendor',
            'status'                => VendorStatus::Approved,
            'commission_rate'       => 5.0,
            'service_radius_meters' => 5000,
        ]);

        $user = User::create([
            'name'      => 'Approved Vendor Admin',
            'email'     => 'approved@test.com',
            'phone'     => '+919000000007',
            'password'  => Hash::make('password123'),
            'vendor_id' => $approvedVendor->id,
            'role_type' => UserRole::VendorAdmin,
        ]);
        $user->assignRole(UserRole::VendorAdmin->value);

        $panel = \Filament\Facades\Filament::getPanel('vendor');

        // canAccessPanel must return true
        $this->assertTrue($user->canAccessPanel($panel));
    }

    /**
     * Test suspended vendor panel denial.
     */
    public function test_suspended_vendor_panel_denial(): void
    {
        $companyId = \Illuminate\Support\Str::uuid()->toString();
        \Illuminate\Support\Facades\DB::table('companies')->insert([
            'id'         => $companyId,
            'name'       => 'Suspended Co',
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $suspendedVendor = Vendor::create([
            'company_id'            => $companyId,
            'brand_name'            => 'Suspended Vendor',
            'status'                => VendorStatus::Suspended,
            'commission_rate'       => 5.0,
            'service_radius_meters' => 5000,
        ]);

        $user = User::create([
            'name'      => 'Suspended Vendor Admin',
            'email'     => 'suspended@test.com',
            'phone'     => '+919000000008',
            'password'  => Hash::make('password123'),
            'vendor_id' => $suspendedVendor->id,
            'role_type' => UserRole::VendorAdmin,
        ]);
        $user->assignRole(UserRole::VendorAdmin->value);

        $panel = \Filament\Facades\Filament::getPanel('vendor');

        // canAccessPanel must return false
        $this->assertFalse($user->canAccessPanel($panel));
    }
}
