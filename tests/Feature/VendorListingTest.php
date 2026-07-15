<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Enums\UnitOfMeasure;
use App\Enums\UserRole;
use App\Models\Category;
use App\Models\User;
use App\Modules\Fuel\Models\MarketplaceProduct;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Tests\TestCase;

class VendorListingTest extends TestCase
{
    use RefreshDatabase;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        $this->category = Category::create([
            'name' => 'Test Biofuels',
            'slug' => 'test-biofuels',
        ]);
    }

    // ── Test helpers ─────────────────────────────────────────────────────────

    private function makeCompany(string $name = 'Test Company'): string
    {
        $id = Str::uuid()->toString();
        DB::table('companies')->insert([
            'id'         => $id,
            'name'       => $name,
            'status'     => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        return $id;
    }

    private function createApprovedVendor(string $suffix = ''): Vendor
    {
        $rand      = Str::random(6);
        $companyId = $this->makeCompany('Test Vendor Company ' . $rand . $suffix);

        return Vendor::create([
            'company_id'          => $companyId,
            'brand_name'          => 'Test Fuel Supplier ' . $rand,
            'legal_name'          => 'Test Fuel Supplier ' . $rand . ' Pvt Ltd',
            'vendor_code'         => 'VND-' . strtoupper($rand),
            'gst_number'          => '27ABCDE' . $rand . '1Z5',
            'pan'                 => 'ABCDE' . $rand,
            'company_type'        => 'private_limited',
            'contact_person'      => 'Test Admin ' . $rand,
            'mobile'              => '9' . substr(preg_replace('/[^0-9]/', '', $rand . '000000000'), 0, 9),
            'email'               => 'vendor' . strtolower($rand) . '@test.com',
            'registered_address'  => '123 Test Street',
            'operational_address' => '123 Test Street',
            'city'                => 'Surat',
            'state'               => 'Gujarat',
            'pincode'             => '395001',
            'status'              => VendorStatus::Approved,
        ]);
    }

    private function createVendorAdmin(Vendor $vendor): User
    {
        $rand = Str::random(6);
        $user = User::create([
            'name'      => 'Vendor Admin ' . $rand,
            'mobile'    => '8' . substr(preg_replace('/[^0-9]/', '', $rand . '000000000'), 0, 9),
            'email'     => 'vendor_admin_' . strtolower($rand) . '@test.com',
            'password'  => bcrypt('password'),
            'role_type' => UserRole::VendorAdmin,
            'vendor_id' => $vendor->id,
        ]);
        $user->syncRoles([UserRole::VendorAdmin->value]);
        return $user;
    }

    private function createSuperAdmin(): User
    {
        $rand = Str::random(6);
        $user = User::create([
            'name'      => 'Super Admin ' . $rand,
            'mobile'    => '7' . substr(preg_replace('/[^0-9]/', '', $rand . '000000000'), 0, 9),
            'email'     => 'super_' . strtolower($rand) . '@test.com',
            'password'  => bcrypt('password'),
            'role_type' => UserRole::SuperAdmin,
        ]);
        $user->syncRoles([UserRole::SuperAdmin->value]);
        return $user;
    }

    private function createMarketplaceProduct(): MarketplaceProduct
    {
        return MarketplaceProduct::create([
            'category_id'      => $this->category->id,
            'name'             => 'RDF (Refuse Derived Fuel)',
            'slug'             => 'rdf-refuse-derived-fuel',
            'unit_of_measure'  => UnitOfMeasure::MetricTonnes,
            'is_active'        => true,
            'ordering_enabled' => true,
            'display_order'    => 1,
        ]);
    }

    private function draftListingData(MarketplaceProduct $product): array
    {
        return [
            'marketplace_product_id' => $product->id,
            'listing_title'          => 'Premium Industrial RDF - 3500+ GCV',
            'unit'                   => 'metric_tonnes',
            'available_quantity'     => 500,
            'base_price'             => 4500,
            'min_order_quantity'     => 10,
            'tax_rate'               => 18,
            'is_active'              => true,
        ];
    }

    // ── Tests ─────────────────────────────────────────────────────────────────

    public function test_approved_vendor_can_create_draft_listing(): void
    {
        $vendor = $this->createApprovedVendor();
        $user   = $this->createVendorAdmin($vendor);
        $product = $this->createMarketplaceProduct();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/vendor/listings', $this->draftListingData($product));

        $response->assertStatus(201)
            ->assertJsonPath('data.approval_status', 'DRAFT')
            ->assertJsonPath('data.listing_title', 'Premium Industrial RDF - 3500+ GCV');
    }

    public function test_vendor_cannot_inject_vendor_id_from_payload(): void
    {
        $vendor  = $this->createApprovedVendor();
        $vendor2 = $this->createApprovedVendor();
        $vendor2->update(['mobile' => '9000000002', 'email' => 'other@vendor.com', 'vendor_code' => 'VND-TEST002']);

        $user    = $this->createVendorAdmin($vendor);
        $product = $this->createMarketplaceProduct();

        $data = $this->draftListingData($product);
        $data['vendor_id'] = $vendor2->id; // Attempt IDOR

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/vendor/listings', $data);

        $response->assertStatus(201);

        // Listing must be scoped to the authenticated vendor — not the injected one
        $this->assertDatabaseHas('vendor_listings', [
            'listing_title' => 'Premium Industrial RDF - 3500+ GCV',
            'vendor_id'     => $vendor->id,  // ← must be auth vendor
        ]);
        $this->assertDatabaseMissing('vendor_listings', [
            'listing_title' => 'Premium Industrial RDF - 3500+ GCV',
            'vendor_id'     => $vendor2->id,  // ← never the injected one
        ]);
    }

    public function test_pending_vendor_cannot_create_listing(): void
    {
        $vendor = $this->createApprovedVendor();
        $vendor->update(['status' => VendorStatus::Pending]);
        $user    = $this->createVendorAdmin($vendor);
        $product = $this->createMarketplaceProduct();

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/vendor/listings', $this->draftListingData($product));

        $response->assertStatus(403);
    }

    public function test_vendor_can_edit_draft_listing(): void
    {
        $vendor  = $this->createApprovedVendor();
        $user    = $this->createVendorAdmin($vendor);
        $product = $this->createMarketplaceProduct();

        $listing = VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor->id,
            'slug'            => 'premium-rdf-3500-gcv',
            'approval_status' => ListingStatus::Draft->value,
        ]));

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/vendor/listings/{$listing->id}", [
                'base_price' => 4800,
            ]);

        $response->assertOk()
            ->assertJsonPath('data.base_price', 4800);
    }

    public function test_vendor_cannot_edit_approved_listing(): void
    {
        $vendor  = $this->createApprovedVendor();
        $user    = $this->createVendorAdmin($vendor);
        $product = $this->createMarketplaceProduct();

        $listing = VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor->id,
            'slug'            => 'premium-rdf-approved',
            'approval_status' => ListingStatus::Approved->value,
        ]));

        $response = $this->actingAs($user, 'sanctum')
            ->putJson("/api/v1/vendor/listings/{$listing->id}", [
                'base_price' => 9999,
            ]);

        $response->assertStatus(403);
    }

    public function test_vendor_can_submit_draft_for_approval(): void
    {
        $vendor  = $this->createApprovedVendor();
        $user    = $this->createVendorAdmin($vendor);
        $product = $this->createMarketplaceProduct();

        $listing = VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor->id,
            'slug'            => 'rdf-submit-test',
            'approval_status' => ListingStatus::Draft->value,
        ]));

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/vendor/listings/{$listing->id}/submit");

        $response->assertOk()
            ->assertJsonPath('data.approval_status', 'PENDING_APPROVAL');
    }

    public function test_vendor_cannot_submit_already_pending_listing(): void
    {
        $vendor  = $this->createApprovedVendor();
        $user    = $this->createVendorAdmin($vendor);
        $product = $this->createMarketplaceProduct();

        $listing = VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor->id,
            'slug'            => 'rdf-pending-test',
            'approval_status' => ListingStatus::PendingApproval->value,
        ]));

        $response = $this->actingAs($user, 'sanctum')
            ->postJson("/api/v1/vendor/listings/{$listing->id}/submit");

        $response->assertStatus(403);
    }

    public function test_public_api_returns_only_approved_active_listings(): void
    {
        $vendor  = $this->createApprovedVendor();
        $product = $this->createMarketplaceProduct();

        // Approved + active (should appear)
        VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor->id,
            'slug'            => 'approved-listing',
            'approval_status' => ListingStatus::Approved->value,
            'is_active'       => true,
        ]));

        // Draft (must not appear)
        VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor->id,
            'slug'            => 'draft-listing',
            'approval_status' => ListingStatus::Draft->value,
            'is_active'       => true,
        ]));

        // Rejected (must not appear)
        VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor->id,
            'slug'            => 'rejected-listing',
            'approval_status' => ListingStatus::Rejected->value,
            'is_active'       => true,
        ]));

        $response = $this->getJson('/api/v1/marketplace/listings');
        $response->assertOk();

        $titles = collect($response->json('data'))->pluck('listing_title')->all();
        $this->assertCount(1, $titles);
        $this->assertContains('Premium Industrial RDF - 3500+ GCV', $titles);
    }

    public function test_public_api_single_listing_by_slug(): void
    {
        $vendor  = $this->createApprovedVendor();
        $product = $this->createMarketplaceProduct();

        VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor->id,
            'slug'            => 'rdf-3500-gcv-surat',
            'approval_status' => ListingStatus::Approved->value,
            'is_active'       => true,
        ]));

        $response = $this->getJson('/api/v1/marketplace/listings/rdf-3500-gcv-surat');
        $response->assertOk()
            ->assertJsonPath('data.slug', 'rdf-3500-gcv-surat');
    }

    public function test_public_api_does_not_expose_draft_by_slug(): void
    {
        $vendor  = $this->createApprovedVendor();
        $product = $this->createMarketplaceProduct();

        VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor->id,
            'slug'            => 'secret-draft-slug',
            'approval_status' => ListingStatus::Draft->value,
            'is_active'       => true,
        ]));

        $response = $this->getJson('/api/v1/marketplace/listings/secret-draft-slug');
        $response->assertNotFound();
    }

    public function test_super_admin_can_approve_listing(): void
    {
        $admin   = $this->createSuperAdmin();
        $vendor  = $this->createApprovedVendor();
        $product = $this->createMarketplaceProduct();

        $listing = VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor->id,
            'slug'            => 'pending-approval-slug',
            'approval_status' => ListingStatus::PendingApproval->value,
        ]));

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/listings/{$listing->id}/approve");

        $response->assertOk()
            ->assertJsonPath('data.approval_status', 'APPROVED');

        $this->assertNotNull($listing->fresh()->approved_at);
    }

    public function test_super_admin_can_reject_listing_with_reason(): void
    {
        $admin   = $this->createSuperAdmin();
        $vendor  = $this->createApprovedVendor();
        $product = $this->createMarketplaceProduct();

        $listing = VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor->id,
            'slug'            => 'reject-test-slug',
            'approval_status' => ListingStatus::PendingApproval->value,
        ]));

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/listings/{$listing->id}/reject", [
                'reason' => 'Quality specifications are insufficient. Please add GCV value.',
            ]);

        $response->assertOk()
            ->assertJsonPath('data.approval_status', 'REJECTED');

        $this->assertDatabaseHas('vendor_listings', [
            'id'               => $listing->id,
            'rejection_reason' => 'Quality specifications are insufficient. Please add GCV value.',
        ]);
    }

    public function test_vendor_can_read_rejection_reason_on_own_listing(): void
    {
        $vendor  = $this->createApprovedVendor();
        $user    = $this->createVendorAdmin($vendor);
        $product = $this->createMarketplaceProduct();

        $listing = VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'        => $vendor->id,
            'slug'             => 'rejection-readable',
            'approval_status'  => ListingStatus::Rejected->value,
            'rejection_reason' => 'Please provide GCV value.',
        ]));

        $response = $this->actingAs($user, 'sanctum')
            ->getJson("/api/v1/vendor/listings/{$listing->id}");

        $response->assertOk()
            ->assertJsonPath('data.rejection_reason', 'Please provide GCV value.');
    }

    public function test_rejection_reason_not_exposed_in_public_api(): void
    {
        $vendor  = $this->createApprovedVendor();
        $product = $this->createMarketplaceProduct();

        // Even if somehow approval_status is APPROVED but we want to verify rejection_reason hidden
        VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'        => $vendor->id,
            'slug'             => 'public-listing-no-rejection',
            'approval_status'  => ListingStatus::Approved->value,
            'is_active'        => true,
            'rejection_reason' => 'Internal note — should not appear.',
        ]));

        $response = $this->getJson('/api/v1/marketplace/listings/public-listing-no-rejection');
        $response->assertOk();

        // rejection_reason must not appear for unauthenticated public request
        $this->assertNull($response->json('data.rejection_reason'));
    }

    public function test_super_admin_can_suspend_approved_listing(): void
    {
        $admin   = $this->createSuperAdmin();
        $vendor  = $this->createApprovedVendor();
        $product = $this->createMarketplaceProduct();

        $listing = VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor->id,
            'slug'            => 'approved-to-suspend',
            'approval_status' => ListingStatus::Approved->value,
        ]));

        $response = $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/listings/{$listing->id}/suspend");

        $response->assertOk()
            ->assertJsonPath('data.approval_status', 'SUSPENDED');
    }

    public function test_super_admin_can_toggle_featured(): void
    {
        $admin   = $this->createSuperAdmin();
        $vendor  = $this->createApprovedVendor();
        $product = $this->createMarketplaceProduct();

        $listing = VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor->id,
            'slug'            => 'feature-toggle-test',
            'approval_status' => ListingStatus::Approved->value,
            'is_featured'     => false,
        ]));

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/listings/{$listing->id}/feature")
            ->assertOk()
            ->assertJsonPath('is_featured', true);

        // Toggle off
        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/admin/listings/{$listing->id}/feature")
            ->assertOk()
            ->assertJsonPath('is_featured', false);
    }

    public function test_vendor_can_update_inventory(): void
    {
        $vendor  = $this->createApprovedVendor();
        $user    = $this->createVendorAdmin($vendor);
        $product = $this->createMarketplaceProduct();

        $listing = VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor->id,
            'slug'            => 'inventory-update-test',
            'approval_status' => ListingStatus::Approved->value,
        ]));

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/vendor/listings/{$listing->id}/inventory", [
                'available_quantity' => 1000,
            ]);

        $response->assertOk()
            ->assertJsonPath('available_quantity', 1000);
    }

    public function test_vendor_can_update_price(): void
    {
        $vendor  = $this->createApprovedVendor();
        $user    = $this->createVendorAdmin($vendor);
        $product = $this->createMarketplaceProduct();

        $listing = VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor->id,
            'slug'            => 'price-update-test',
            'approval_status' => ListingStatus::Approved->value,
        ]));

        $response = $this->actingAs($user, 'sanctum')
            ->patchJson("/api/v1/vendor/listings/{$listing->id}/price", [
                'base_price' => 5200,
            ]);

        $response->assertOk()
            ->assertJsonPath('base_price', 5200);
    }

    public function test_customer_cannot_access_vendor_listing_endpoints(): void
    {
        $customer = User::create([
            'name'     => 'Customer',
            'mobile'   => '9111111111',
            'email'    => 'customer@test.com',
            'password' => bcrypt('password'),
            'role_type'=> UserRole::Customer,
        ]);
        $customer->syncRoles(['customer']);

        $response = $this->actingAs($customer, 'sanctum')
            ->getJson('/api/v1/vendor/listings');

        $response->assertStatus(403);
    }

    public function test_vendor_cannot_access_another_vendors_listing(): void
    {
        $vendor1  = $this->createApprovedVendor();
        $vendor2  = $this->createApprovedVendor('other');

        $user1   = $this->createVendorAdmin($vendor1);
        $product = $this->createMarketplaceProduct();

        $listing = VendorListing::create(array_merge($this->draftListingData($product), [
            'vendor_id'       => $vendor2->id, // belongs to vendor2
            'slug'            => 'vendor2-listing',
            'approval_status' => ListingStatus::Draft->value,
        ]));

        // vendor1 tries to view vendor2's listing
        $response = $this->actingAs($user1, 'sanctum')
            ->getJson("/api/v1/vendor/listings/{$listing->id}");

        $response->assertStatus(403);
    }

    public function test_quality_specifications_stored_as_jsonb(): void
    {
        $vendor  = $this->createApprovedVendor();
        $user    = $this->createVendorAdmin($vendor);
        $product = $this->createMarketplaceProduct();

        $specs = [
            'GCV'           => '3500 kcal/kg minimum',
            'Moisture'       => 'Max 15%',
            'Ash Content'    => 'Max 20%',
            'Sulphur'        => 'Max 1%',
            'Density'        => '650 kg/m3',
        ];

        $data = array_merge($this->draftListingData($product), [
            'quality_specifications' => $specs,
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->postJson('/api/v1/vendor/listings', $data);

        $response->assertStatus(201);

        $listing = VendorListing::first();
        $this->assertEquals($specs, $listing->quality_specifications);
    }
}
