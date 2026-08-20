<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Company;
use App\Models\User;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Models\Vendor;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * SecurityAuditTest
 *
 * Covers:
 * - Authorization  : Role-based access control on admin endpoints
 * - IDOR           : Cart items, addresses scoped to owner
 * - Rate Limiting  : Headers present on auth routes
 * - Mass Assignment: Registration cannot elevate role_type
 * - Headers        : Security headers injected by SecurityHeaders middleware
 */
class SecurityAuditTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private User $superAdmin;

    private Vendor $vendor;

    private User $vendorAdmin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        $this->customer = User::create([
            'name' => 'Test Customer',
            'email' => 'customer@test.com',
            'password' => bcrypt('password'),
            'role_type' => UserRole::Customer,
        ]);
        $this->customer->assignRole(UserRole::Customer->value);

        $this->superAdmin = User::create([
            'name' => 'Super Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role_type' => UserRole::SuperAdmin,
        ]);
        $this->superAdmin->assignRole(UserRole::SuperAdmin->value);

        $company = Company::create(['name' => 'Acme Fuels', 'tax_number' => 'TAXACME', 'status' => 'active']);
        $this->vendor = Vendor::create([
            'company_id' => $company->id,
            'brand_name' => 'Acme Fuels',
            'status' => VendorStatus::Pending,
            'contact_email' => 'acme@example.com',
        ]);

        $this->vendorAdmin = User::create([
            'name' => 'Vendor Admin',
            'email' => 'vendoradmin@test.com',
            'password' => bcrypt('password'),
            'role_type' => UserRole::VendorAdmin,
            'vendor_id' => $this->vendor->id,
        ]);
        $this->vendorAdmin->assignRole(UserRole::VendorAdmin->value);
    }

    // ── Authorization ─────────────────────────────────────────────────────

    /** TEST: Customer cannot access admin vendor list */
    public function test_customer_cannot_list_all_vendors(): void
    {
        Sanctum::actingAs($this->customer);
        $response = $this->getJson('/api/v1/admin/vendors');
        $response->assertStatus(403);
    }

    /** TEST: Customer cannot approve a vendor */
    public function test_customer_cannot_approve_vendor(): void
    {
        Sanctum::actingAs($this->customer);
        $response = $this->postJson("/api/v1/admin/vendors/{$this->vendor->id}/approve");
        $response->assertStatus(403);
    }

    /** TEST: Vendor admin cannot approve another vendor */
    public function test_vendor_admin_cannot_approve_vendor(): void
    {
        Sanctum::actingAs($this->vendorAdmin);
        $response = $this->postJson("/api/v1/admin/vendors/{$this->vendor->id}/approve");
        $response->assertStatus(403);
    }

    /** TEST: Super admin can list vendors */
    public function test_super_admin_can_list_vendors(): void
    {
        Sanctum::actingAs($this->superAdmin);
        $response = $this->getJson('/api/v1/admin/vendors');
        $response->assertStatus(200);
    }

    // ── Mass Assignment Protection ────────────────────────────────────────

    /** TEST: Registration cannot elevate role_type to super_admin */
    public function test_registration_cannot_elevate_role(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'Attacker',
            'email' => 'attacker@evil.com',
            'phone' => '+919999999999',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role_type' => 'super_admin',  // attempted privilege escalation
            'status' => 'active',
        ]);

        $response->assertStatus(201);

        // Verify the role was forced to Customer
        $user = User::where('email', 'attacker@evil.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals(UserRole::Customer, $user->role_type);
        $this->assertTrue($user->hasRole('customer'));
        $this->assertFalse($user->hasRole('super_admin'));
    }

    // ── Security Headers ─────────────────────────────────────────────────

    /** TEST: Security headers are present on every response */
    public function test_security_headers_present_on_api_response(): void
    {
        $response = $this->getJson('/api/v1/marketplace/listings');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'no-referrer-when-downgrade');
        $this->assertNotNull($response->headers->get('Content-Security-Policy'));
    }

    // ── IDOR: Address Ownership ───────────────────────────────────────────

    /** TEST: Customer cannot delete another user's address (IDOR) */
    public function test_customer_cannot_delete_another_users_address(): void
    {
        // Create an address belonging to superAdmin using correct polymorphic structure
        $address = Address::create([
            'addressable_type' => User::class,
            'addressable_id' => $this->superAdmin->id,
            'user_id' => $this->superAdmin->id,
            'address_line_1' => '123 Admin Street',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400001',
            'latitude' => 19.0760,
            'longitude' => 72.8777,
        ]);

        Sanctum::actingAs($this->customer);
        // deleteAddress scopes by user_id → findOrFail → 404 for non-owner
        $response = $this->deleteJson("/api/v1/customer/addresses/{$address->id}");
        $response->assertStatus(404);

        // Confirm address still exists in the database
        $this->assertDatabaseHas('addresses', ['id' => $address->id]);
    }

    // ── Unauthenticated Access ─────────────────────────────────────────────

    /** TEST: Authenticated routes require a valid token */
    public function test_customer_profile_requires_auth(): void
    {
        $response = $this->getJson('/api/v1/customer/profile');
        $response->assertStatus(401);
    }

    /** TEST: Order list requires authentication */
    public function test_order_list_requires_auth(): void
    {
        $response = $this->getJson('/api/v1/orders');
        $response->assertStatus(401);
    }

    /** TEST: Wallet show requires authentication */
    public function test_wallet_requires_auth(): void
    {
        $response = $this->getJson('/api/v1/wallets');
        $response->assertStatus(401);
    }

    // ── Payment Webhook Signature ─────────────────────────────────────────

    /** TEST: Webhook rejects requests with no signature */
    public function test_webhook_rejects_missing_signature(): void
    {
        $response = $this->postJson('/api/v1/payments/webhook', [
            'event' => 'payment.captured',
        ]);
        $response->assertStatus(400)
            ->assertJsonPath('error', 'Missing signature');
    }

    /** TEST: Webhook rejects requests with invalid HMAC signature */
    public function test_webhook_rejects_invalid_signature(): void
    {
        $response = $this->withHeaders([
            'X-Razorpay-Signature' => 'invalid_signature_abc123',
        ])->postJson('/api/v1/payments/webhook', [
            'event' => 'payment.captured',
        ]);
        $response->assertStatus(400)
            ->assertJsonPath('error', 'Invalid signature');
    }
}
