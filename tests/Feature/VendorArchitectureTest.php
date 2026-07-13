<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorDocument;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Enums\DocumentStatus;
use App\Enums\UserRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * VendorArchitectureTest
 *
 * Verifies:
 *  - Vendor profile isolation (IDOR prevention)
 *  - Cross-vendor document access denied
 *  - Super admin full access
 *  - Status transition authorization
 *  - Customer auth not affected
 */
class VendorArchitectureTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $vendorAdminA;
    private User $vendorAdminB;
    private User $customer;
    private Vendor $vendorA;
    private Vendor $vendorB;
    private VendorDocument $docA;
    private VendorDocument $docB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // ── Companies ──────────────────────────────────────────────────────
        $companyAId = Str::uuid()->toString();
        $companyBId = Str::uuid()->toString();

        DB::table('companies')->insert([
            ['id' => $companyAId, 'name' => 'Vendor A Corp', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['id' => $companyBId, 'name' => 'Vendor B Corp', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // ── Vendors ────────────────────────────────────────────────────────
        $this->vendorA = Vendor::create([
            'company_id'            => $companyAId,
            'brand_name'            => 'Vendor A Fuels',
            'legal_name'            => 'Vendor A Fuels Pvt Ltd',
            'gst_number'            => '27AABCV1234A1Z5',
            'contact_person'        => 'Alice',
            'mobile'                => '+919000000001',
            'email'                 => 'a@vendora.com',
            'city'                  => 'Mumbai',
            'state'                 => 'Maharashtra',
            'status'                => VendorStatus::Approved,
            'verification_status'   => DocumentStatus::Verified,
            'commission_rate'       => 5.00,
            'service_radius_meters' => 50000,
        ]);

        $this->vendorB = Vendor::create([
            'company_id'            => $companyBId,
            'brand_name'            => 'Vendor B Biofuels',
            'legal_name'            => 'Vendor B Biofuels Ltd',
            'gst_number'            => '27XYZWV5678B1Z9',
            'contact_person'        => 'Bob',
            'mobile'                => '+919000000002',
            'email'                 => 'b@vendorb.com',
            'city'                  => 'Pune',
            'state'                 => 'Maharashtra',
            'status'                => VendorStatus::Approved,
            'verification_status'   => DocumentStatus::Verified,
            'commission_rate'       => 7.00,
            'service_radius_meters' => 50000,
        ]);

        // ── Users ──────────────────────────────────────────────────────────
        $this->superAdmin = User::create([
            'name'      => 'Super Admin',
            'email'     => 'admin@fuelcab.com',
            'phone'     => '+919100000000',
            'password'  => bcrypt('password'),
            'role_type' => UserRole::OperationsTeam,
        ]);
        $this->superAdmin->assignRole('super_admin');

        $this->vendorAdminA = User::create([
            'name'      => 'Vendor A Admin',
            'email'     => 'admin@vendora.com',
            'phone'     => '+919200000001',
            'password'  => bcrypt('password'),
            'vendor_id' => $this->vendorA->id,
            'role_type' => UserRole::VendorAdmin,
        ]);
        $this->vendorAdminA->assignRole('vendor_admin');

        $this->vendorAdminB = User::create([
            'name'      => 'Vendor B Admin',
            'email'     => 'admin@vendorb.com',
            'phone'     => '+919200000002',
            'password'  => bcrypt('password'),
            'vendor_id' => $this->vendorB->id,
            'role_type' => UserRole::VendorAdmin,
        ]);
        $this->vendorAdminB->assignRole('vendor_admin');

        $this->customer = User::create([
            'name'      => 'Test Customer',
            'email'     => 'customer@test.com',
            'phone'     => '+919300000000',
            'password'  => bcrypt('password'),
            'role_type' => UserRole::Customer,
        ]);
        $this->customer->assignRole('customer');

        // ── Documents ──────────────────────────────────────────────────────
        $this->docA = VendorDocument::create([
            'vendor_id'     => $this->vendorA->id,
            'document_type' => 'gst_certificate',
            'file_path'     => 'vendor-documents/' . $this->vendorA->id . '/gst.pdf',
            'status'        => DocumentStatus::Pending,
        ]);

        $this->docB = VendorDocument::create([
            'vendor_id'     => $this->vendorB->id,
            'document_type' => 'gst_certificate',
            'file_path'     => 'vendor-documents/' . $this->vendorB->id . '/gst.pdf',
            'status'        => DocumentStatus::Pending,
        ]);
    }

    // ── 1. Vendor Profile Isolation ────────────────────────────────────────

    /**
     * Vendor admin can view their OWN profile via /vendor/profile.
     */
    public function test_vendor_admin_can_view_own_profile(): void
    {
        Sanctum::actingAs($this->vendorAdminA);

        $response = $this->getJson('/api/v1/vendor/profile');

        $response->assertOk()
            ->assertJsonPath('data.id', $this->vendorA->id)
            ->assertJsonPath('data.brand_name', 'Vendor A Fuels');
    }

    /**
     * IDOR: Vendor A admin CANNOT read Vendor B's profile by guessing ID.
     *
     * The self-service profile endpoint does NOT accept a vendor_id parameter —
     * it is always derived from the authenticated user's own vendor_id.
     * This means there is no URL for Vendor A to attempt to guess Vendor B's ID.
     * However, the admin show endpoint /api/v1/admin/vendors/{vendor} MUST be 403.
     */
    public function test_vendor_admin_cannot_view_another_vendors_profile(): void
    {
        Sanctum::actingAs($this->vendorAdminA);

        // Attempt to access Vendor B's record through admin endpoint — must be denied
        $response = $this->getJson('/api/v1/admin/vendors/' . $this->vendorB->id);

        $response->assertForbidden();
    }

    /**
     * Super Admin CAN view any vendor's profile.
     */
    public function test_super_admin_can_view_any_vendor(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->getJson('/api/v1/admin/vendors/' . $this->vendorA->id);
        $response->assertOk()->assertJsonPath('data.id', $this->vendorA->id);

        $response = $this->getJson('/api/v1/admin/vendors/' . $this->vendorB->id);
        $response->assertOk()->assertJsonPath('data.id', $this->vendorB->id);
    }

    // ── 2. Document IDOR Tests ─────────────────────────────────────────────

    /**
     * Vendor A's documents list endpoint returns ONLY Vendor A's documents.
     */
    public function test_vendor_admin_documents_are_scoped_to_own_vendor(): void
    {
        Sanctum::actingAs($this->vendorAdminA);

        $response = $this->getJson('/api/v1/vendor/documents');

        $response->assertOk();

        // All returned document IDs must belong to Vendor A
        $documentIds = collect($response->json('data'))->pluck('id');
        $this->assertTrue($documentIds->contains($this->docA->id));
        $this->assertFalse($documentIds->contains($this->docB->id));
    }

    /**
     * IDOR: Vendor A admin cannot verify Vendor B's documents.
     */
    public function test_vendor_admin_cannot_verify_another_vendors_document(): void
    {
        Sanctum::actingAs($this->vendorAdminA);

        $response = $this->postJson('/api/v1/admin/documents/' . $this->docB->id . '/verify');

        $response->assertForbidden();
    }

    /**
     * IDOR: Vendor A admin cannot reject Vendor B's documents.
     */
    public function test_vendor_admin_cannot_reject_another_vendors_document(): void
    {
        Sanctum::actingAs($this->vendorAdminA);

        $response = $this->postJson('/api/v1/admin/documents/' . $this->docB->id . '/reject', [
            'reason' => 'Malicious attempt',
        ]);

        $response->assertForbidden();
    }

    /**
     * Super Admin CAN verify documents from any vendor.
     */
    public function test_super_admin_can_verify_any_vendor_document(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->postJson('/api/v1/admin/documents/' . $this->docA->id . '/verify');

        $response->assertOk();
        $this->assertDatabaseHas('vendor_documents', [
            'id'     => $this->docA->id,
            'status' => DocumentStatus::Verified->value,
        ]);
    }

    // ── 3. Vendor Status Transition Authorization ──────────────────────────

    /**
     * Super Admin can approve a vendor.
     */
    public function test_super_admin_can_approve_vendor(): void
    {
        // Create a pending vendor to approve
        $companyId = Str::uuid()->toString();
        DB::table('companies')->insert(['id' => $companyId, 'name' => 'New Co', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);

        $pendingVendor = Vendor::create([
            'company_id'            => $companyId,
            'brand_name'            => 'New Vendor',
            'status'                => VendorStatus::Pending,
            'commission_rate'       => 5.00,
            'service_radius_meters' => 5000,
        ]);

        Sanctum::actingAs($this->superAdmin);
        $response = $this->postJson('/api/v1/admin/vendors/' . $pendingVendor->id . '/approve');

        $response->assertOk();
        $this->assertEquals(VendorStatus::Approved->value, $pendingVendor->fresh()->status->value);
    }

    /**
     * Vendor admin CANNOT approve another vendor.
     */
    public function test_vendor_admin_cannot_approve_another_vendor(): void
    {
        $companyId = Str::uuid()->toString();
        DB::table('companies')->insert(['id' => $companyId, 'name' => 'Another Co', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()]);

        $pendingVendor = Vendor::create([
            'company_id'            => $companyId,
            'brand_name'            => 'Another Vendor',
            'status'                => VendorStatus::Pending,
            'commission_rate'       => 5.00,
            'service_radius_meters' => 5000,
        ]);

        Sanctum::actingAs($this->vendorAdminA);
        $response = $this->postJson('/api/v1/admin/vendors/' . $pendingVendor->id . '/approve');

        $response->assertForbidden();
    }

    /**
     * Super Admin can suspend an approved vendor.
     */
    public function test_super_admin_can_suspend_vendor(): void
    {
        Sanctum::actingAs($this->superAdmin);

        $response = $this->postJson('/api/v1/admin/vendors/' . $this->vendorA->id . '/suspend', [
            'reason' => 'License expired',
        ]);

        $response->assertOk();
        $this->assertEquals(VendorStatus::Suspended->value, $this->vendorA->fresh()->status->value);
    }

    /**
     * Super Admin can reactivate a suspended vendor.
     */
    public function test_super_admin_can_reactivate_suspended_vendor(): void
    {
        // First suspend
        $this->vendorA->update(['status' => VendorStatus::Suspended]);

        Sanctum::actingAs($this->superAdmin);
        $response = $this->postJson('/api/v1/admin/vendors/' . $this->vendorA->id . '/reactivate');

        $response->assertOk();
        $this->assertEquals(VendorStatus::Approved->value, $this->vendorA->fresh()->status->value);
    }

    // ── 4. Customer Auth Not Affected ─────────────────────────────────────

    /**
     * Customer cannot access vendor admin endpoints.
     */
    public function test_customer_cannot_access_vendor_admin_endpoints(): void
    {
        Sanctum::actingAs($this->customer);

        $this->getJson('/api/v1/admin/vendors')->assertForbidden();
        $this->postJson('/api/v1/admin/vendors/' . $this->vendorA->id . '/approve')->assertForbidden();
    }

    /**
     * Customer can authenticate with Sanctum and call any protected endpoints.
     * Verifies customer authentication flow is unbroken by vendor architecture changes.
     */
    public function test_customer_auth_is_unaffected(): void
    {
        // Customer can generate a Sanctum token (simulating successful Google OAuth login)
        $token = $this->customer->createToken('test_token')->plainTextToken;

        $this->assertNotEmpty($token);

        // Customer can call their own protected route (e.g. cart)
        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/cart');

        // Authenticated customer gets a valid JSON response (not 401 Unauthorized)
        // Cart returns 200 or 201 depending on whether it already exists
        $response->assertSuccessful();
    }

    // ── 5. Vendor Profile Update Scoped to Own Vendor ─────────────────────

    /**
     * Vendor admin can update their own profile fields.
     */
    public function test_vendor_admin_can_update_own_profile(): void
    {
        Sanctum::actingAs($this->vendorAdminA);

        $response = $this->putJson('/api/v1/vendor/profile', [
            'contact_person' => 'Alice Updated',
            'city'           => 'Thane',
        ]);

        $response->assertOk();
        $this->assertEquals('Alice Updated', $this->vendorA->fresh()->contact_person);
        $this->assertEquals('Thane', $this->vendorA->fresh()->city);
    }

    /**
     * Vendor admin cannot change their own vendor status via profile update.
     */
    public function test_vendor_admin_cannot_change_own_status(): void
    {
        Sanctum::actingAs($this->vendorAdminA);

        // status field is not in the validated fields list — will be silently ignored
        $response = $this->putJson('/api/v1/vendor/profile', [
            'status' => VendorStatus::Suspended->value,
        ]);

        $response->assertOk();

        // Status must remain Approved — not changed
        $this->assertEquals(VendorStatus::Approved->value, $this->vendorA->fresh()->status->value);
    }
}
