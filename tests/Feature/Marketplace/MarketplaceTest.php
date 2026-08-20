<?php

declare(strict_types=1);

namespace Tests\Feature\Marketplace;

use App\Enums\UserRole;
use App\Models\Address;
use App\Models\Category;
use App\Models\User;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorListing;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MarketplaceTest extends TestCase
{
    use RefreshDatabase;

    private User $customer;

    private Address $address;

    private Vendor $vendor;

    private VendorListing $listing1;

    private VendorListing $listing2;

    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RolesAndPermissionsSeeder::class);

        // Create customer
        $this->customer = User::create([
            'name' => 'Marketplace Customer',
            'email' => 'cust@marketplace.com',
            'mobile' => '9988776655',
            'password' => bcrypt('password'),
            'role_type' => UserRole::Customer,
        ]);
        $this->customer->assignRole(UserRole::Customer->value);

        // Create address
        $this->address = Address::create([
            'user_id' => $this->customer->id,
            'addressable_type' => User::class,
            'address_line_1' => '123 Test St',
            'city' => 'Mumbai',
            'state' => 'Maharashtra',
            'postal_code' => '400001',
            'latitude' => 19.076,
            'longitude' => 72.8777,
        ]);

        // Create category
        $this->category = Category::create([
            'name' => 'Marketplace Fuel',
            'slug' => 'marketplace-fuel',
        ]);

        // Create Company and Vendor
        $companyId = Str::uuid()->toString();
        DB::table('companies')->insert([
            'id' => $companyId,
            'name' => 'Super Vendor Corp',
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $this->vendor = Vendor::create([
            'company_id' => $companyId,
            'brand_name' => 'Super Fuel Vendor',
            'status' => 'approved',
        ]);

        // Create products
        $productId1 = Str::uuid()->toString();
        DB::table('marketplace_products')->insert([
            'id' => $productId1,
            'category_id' => $this->category->id,
            'name' => 'Bio Diesel',
            'slug' => 'bio-diesel',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $productId2 = Str::uuid()->toString();
        DB::table('marketplace_products')->insert([
            'id' => $productId2,
            'category_id' => $this->category->id,
            'name' => 'Ethanol Blend',
            'slug' => 'ethanol-blend',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create listings
        $this->listing1 = VendorListing::create([
            'vendor_id' => $this->vendor->id,
            'marketplace_product_id' => $productId1,
            'listing_title' => 'Premium Bio Diesel',
            'slug' => 'premium-bio-diesel',
            'sku' => 'LST-BIO-01',
            'base_price' => 95.00,
            'tax_rate' => 18.00,
            'unit' => 'litres',
            'approval_status' => 'APPROVED',
            'is_active' => true,
            'seo_title' => 'Bio Diesel SEO',
            'seo_description' => 'Bio Diesel SEO Desc',
        ]);

        $this->listing2 = VendorListing::create([
            'vendor_id' => $this->vendor->id,
            'marketplace_product_id' => $productId2,
            'listing_title' => 'Eco Ethanol Blend',
            'slug' => 'eco-ethanol-blend',
            'sku' => 'LST-ETH-02',
            'base_price' => 80.00,
            'tax_rate' => 18.00,
            'unit' => 'litres',
            'approval_status' => 'APPROVED',
            'is_active' => true,
        ]);
    }

    /** @test */
    public function public_can_view_categories_and_products(): void
    {
        $this->getJson('/api/v1/marketplace/categories')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->getJson('/api/v1/marketplace/products')
            ->assertOk()
            ->assertJsonPath('data.total', 2);
    }

    /** @test */
    public function customer_can_manage_wishlist(): void
    {
        Sanctum::actingAs($this->customer);

        // Add
        $this->postJson('/api/v1/marketplace/wishlist', [
            'vendor_listing_id' => $this->listing1->id,
        ])->assertStatus(201);

        // List
        $this->getJson('/api/v1/marketplace/wishlist')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        // Delete
        $this->deleteJson("/api/v1/marketplace/wishlist/{$this->listing1->id}")
            ->assertOk();
    }

    /** @test */
    public function public_can_compare_listings(): void
    {
        $this->postJson('/api/v1/marketplace/compare', [
            'listing_ids' => [$this->listing1->id, $this->listing2->id],
        ])->assertOk()
            ->assertJsonCount(2, 'data');
    }

    /** @test */
    public function customer_can_track_recently_viewed(): void
    {
        Sanctum::actingAs($this->customer);

        // Add
        $this->postJson('/api/v1/marketplace/recently-viewed', [
            'vendor_listing_id' => $this->listing1->id,
        ])->assertStatus(201);

        // List
        $this->getJson('/api/v1/marketplace/recently-viewed')
            ->assertOk()
            ->assertJsonCount(1, 'data');
    }

    /** @test */
    public function customer_can_rate_vendor_and_view_profile(): void
    {
        Sanctum::actingAs($this->customer);

        // Rate
        $this->postJson("/api/v1/marketplace/vendors/{$this->vendor->id}/ratings", [
            'rating' => 5,
            'review' => 'Excellent bulk delivery service.',
        ])->assertStatus(201);

        // View Profile
        $this->getJson("/api/v1/marketplace/vendors/{$this->vendor->id}")
            ->assertOk()
            ->assertJsonPath('data.vendor.rating_avg', 5)
            ->assertJsonPath('data.vendor.ratings_count', 1)
            ->assertJsonCount(2, 'data.listings');
    }

    /** @test */
    public function customer_can_place_direct_order_from_listing(): void
    {
        Sanctum::actingAs($this->customer);

        $this->postJson("/api/v1/marketplace/listings/{$this->listing1->slug}/order", [
            'quantity' => 10,
            'delivery_address_id' => $this->address->id,
            'notes' => 'Deliver by tomorrow morning',
        ])->assertStatus(201)
            ->assertJsonPath('data.listing_slug', $this->listing1->slug)
            ->assertJsonPath('data.quantity', 10)
            ->assertJsonPath('data.total_amount', 1121); // 95 * 1.18 * 10 = 1121
    }

    /** @test */
    public function public_listings_can_be_filtered_and_sorted(): void
    {
        // 1. Filter by price_max
        $this->getJson('/api/v1/marketplace/listings?price_max=85')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $this->listing2->slug);

        // 2. Filter by price_min
        $this->getJson('/api/v1/marketplace/listings?price_min=90')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $this->listing1->slug);

        // 3. Filter by category
        $this->getJson("/api/v1/marketplace/listings?category_id={$this->category->id}")
            ->assertOk()
            ->assertJsonCount(2, 'data');

        // 4. Sort by price_asc
        $this->getJson('/api/v1/marketplace/listings?sort_by=price_asc')
            ->assertOk()
            ->assertJsonPath('data.0.slug', $this->listing2->slug)
            ->assertJsonPath('data.1.slug', $this->listing1->slug);

        // 5. Sort by price_desc
        $this->getJson('/api/v1/marketplace/listings?sort_by=price_desc')
            ->assertOk()
            ->assertJsonPath('data.0.slug', $this->listing1->slug)
            ->assertJsonPath('data.1.slug', $this->listing2->slug);
    }
}
