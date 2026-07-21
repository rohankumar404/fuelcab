<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\ListingStatus;
use App\Enums\UnitOfMeasure;
use App\Enums\UserRole;
use App\Filament\Vendor\Resources\InventoryResource;
use App\Filament\Vendor\Resources\OrderResource;
use App\Filament\Vendor\Resources\QuoteRequestResource;
use App\Filament\Vendor\Resources\SettlementResource;
use App\Filament\Vendor\Resources\VendorDocumentResource;
use App\Filament\Vendor\Resources\VendorListingResource;
use App\Models\Address;
use App\Models\BulkInquiry;
use App\Models\Category;
use App\Models\Company;
use App\Models\Settlement;
use App\Models\User;
use App\Modules\Fuel\Models\MarketplaceProduct;
use App\Modules\Fuel\Models\Product;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use App\Modules\Vendor\Enums\DocumentStatus;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorDocument;
use App\Modules\Vendor\Models\VendorListing;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class VendorPanelSecurityTest extends TestCase
{
    use RefreshDatabase;

    private Vendor $vendorA;
    private User $userA;

    private Vendor $vendorB;
    private User $userB;

    private Category $category;
    private MarketplaceProduct $marketplaceProduct;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(\Database\Seeders\RolesAndPermissionsSeeder::class);

        // Setup Vendor A & User A
        $companyA = Company::create(['name' => 'Company A', 'tax_number' => 'TAXA123', 'status' => 'active']);
        $this->vendorA = Vendor::create([
            'company_id'    => $companyA->id,
            'brand_name'    => 'Vendor A Fuels',
            'status'        => VendorStatus::Approved,
            'contact_email' => 'vendora@example.com',
        ]);
        $this->userA = User::create([
            'name'      => 'Vendor A Admin',
            'email'     => 'admina@example.com',
            'password'  => bcrypt('password'),
            'role_type' => UserRole::VendorAdmin,
            'vendor_id' => $this->vendorA->id,
        ]);
        $this->userA->assignRole(UserRole::VendorAdmin->value);

        // Setup Vendor B & User B
        $companyB = Company::create(['name' => 'Company B', 'tax_number' => 'TAXB456', 'status' => 'active']);
        $this->vendorB = Vendor::create([
            'company_id'    => $companyB->id,
            'brand_name'    => 'Vendor B Fuels',
            'status'        => VendorStatus::Approved,
            'contact_email' => 'vendorb@example.com',
        ]);
        $this->userB = User::create([
            'name'      => 'Vendor B Admin',
            'email'     => 'adminb@example.com',
            'password'  => bcrypt('password'),
            'role_type' => UserRole::VendorAdmin,
            'vendor_id' => $this->vendorB->id,
        ]);
        $this->userB->assignRole(UserRole::VendorAdmin->value);

        $this->category = Category::create(['name' => 'Biomass', 'slug' => 'biomass']);
        $this->marketplaceProduct = MarketplaceProduct::create([
            'category_id' => $this->category->id,
            'name'        => 'Biomass Pellets',
            'slug'        => 'biomass-pellets',
            'unit'        => UnitOfMeasure::MetricTonnes,
            'is_active'   => true,
        ]);
    }

    /**
     * TEST 1: Vendor A attempting to access Vendor B's Listing
     */
    public function test_vendor_a_cannot_access_vendor_b_listing(): void
    {
        $listingB = VendorListing::create([
            'vendor_id'              => $this->vendorB->id,
            'marketplace_product_id' => $this->marketplaceProduct->id,
            'listing_title'          => 'Vendor B Pellet Offer',
            'slug'                   => 'vendor-b-pellet-offer',
            'unit'                   => UnitOfMeasure::MetricTonnes,
            'base_price'             => 8500,
            'available_quantity'     => 500,
            'approval_status'        => ListingStatus::Approved,
            'is_active'              => true,
        ]);

        // Policy view check for User A on Listing B
        $this->assertFalse(Gate::forUser($this->userA)->allows('view', $listingB));
        $this->assertFalse(Gate::forUser($this->userA)->allows('update', $listingB));
        $this->assertFalse(Gate::forUser($this->userA)->allows('submit', $listingB));

        // Filament Resource Query Scope Check
        $this->actingAs($this->userA);
        $queryResults = VendorListingResource::getEloquentQuery()->pluck('id')->toArray();
        $this->assertNotContains($listingB->id, $queryResults);
    }

    /**
     * TEST 2: Vendor A attempting to access Vendor B's Order
     */
    public function test_vendor_a_cannot_access_vendor_b_order(): void
    {
        $customer = User::create([
            'name'      => 'Customer User',
            'email'     => 'customer@example.com',
            'password'  => bcrypt('password'),
            'role_type' => UserRole::Customer,
        ]);
        $customer->assignRole(UserRole::Customer->value);

        $address = Address::create([
            'addressable_type' => User::class,
            'addressable_id'   => $customer->id,
            'address_line_1'   => '123 Main St',
            'city'             => 'Mumbai',
            'state'            => 'Maharashtra',
            'pincode'          => '400001',
            'postal_code'      => '400001',
            'latitude'         => 19.0760,
            'longitude'        => 72.8777,
        ]);

        $orderB = Order::create([
            'vendor_id'           => $this->vendorB->id,
            'customer_id'         => $customer->id,
            'delivery_address_id' => $address->id,
            'order_number'        => 'ORD-VEND-B-001',
            'status'              => OrderStatus::Pending,
            'channel'             => 'marketplace',
            'subtotal_amount'     => 25000.00,
            'tax_amount'          => 0.00,
            'delivery_fee'        => 0.00,
            'total_amount'        => 25000.00,
        ]);

        // Policy view & accept check for User A on Order B
        $this->assertFalse(Gate::forUser($this->userA)->allows('view', $orderB));
        $this->assertFalse(Gate::forUser($this->userA)->allows('accept', $orderB));
        $this->assertFalse(Gate::forUser($this->userA)->allows('updateStatus', $orderB));

        // Filament Resource Query Scope Check
        $this->actingAs($this->userA);
        $queryResults = OrderResource::getEloquentQuery()->pluck('id')->toArray();
        $this->assertNotContains($orderB->id, $queryResults);
    }

    /**
     * TEST 3: Vendor A attempting to access Vendor B's Quote Request
     */
    public function test_vendor_a_cannot_access_vendor_b_quote_request(): void
    {
        $product = Product::create([
            'category_id'    => $this->category->id,
            'name'           => 'Direct Diesel',
            'slug'           => 'direct-diesel',
            'sku'            => 'DIESEL-01',
            'price'          => 90.00,
            'price_per_unit' => 90.00,
            'unit'           => UnitOfMeasure::Litres,
            'is_active'      => true,
            'vendor_id'      => $this->vendorB->id,
        ]);

        $customer = User::create([
            'name'      => 'Customer Quote User',
            'email'     => 'custquote@example.com',
            'password'  => bcrypt('password'),
            'role_type' => UserRole::Customer,
        ]);

        $inquiryB = BulkInquiry::create([
            'user_id'                 => $customer->id,
            'product_id'              => $product->id,
            'vendor_id'               => $this->vendorB->id,
            'quantity'                => 1000,
            'preferred_delivery_date' => now()->addDays(5),
            'status'                  => 'pending',
            'message'                 => 'Bulk inquiry for Vendor B',
        ]);

        // Policy view & respond check for User A on Inquiry B
        $this->assertFalse(Gate::forUser($this->userA)->allows('view', $inquiryB));
        $this->assertFalse(Gate::forUser($this->userA)->allows('respond', $inquiryB));

        // Filament Resource Query Scope Check
        $this->actingAs($this->userA);
        $queryResults = QuoteRequestResource::getEloquentQuery()->pluck('id')->toArray();
        $this->assertNotContains($inquiryB->id, $queryResults);
    }

    /**
     * TEST 4: Vendor A attempting to access Vendor B's Settlement
     */
    public function test_vendor_a_cannot_access_vendor_b_settlement(): void
    {
        $settlementB = Settlement::create([
            'vendor_id'         => $this->vendorB->id,
            'gross_amount'      => 100000.00,
            'commission_amount' => 5000.00,
            'adjustments'       => 0.00,
            'net_payable'       => 95000.00,
            'status'            => 'pending',
        ]);

        // Policy view check for User A on Settlement B
        $this->assertFalse(Gate::forUser($this->userA)->allows('view', $settlementB));

        // Filament Resource Query Scope Check
        $this->actingAs($this->userA);
        $queryResults = SettlementResource::getEloquentQuery()->pluck('id')->toArray();
        $this->assertNotContains($settlementB->id, $queryResults);
    }

    /**
     * TEST 5: Vendor A attempting to access Vendor B's Document
     */
    public function test_vendor_a_cannot_access_vendor_b_document(): void
    {
        $docB = VendorDocument::create([
            'vendor_id'     => $this->vendorB->id,
            'document_type' => 'gst_certificate',
            'file_path'     => 'https://example.com/vendor_b_gst.pdf',
            'status'        => DocumentStatus::Pending,
        ]);

        // Policy view, update & delete check for User A on Document B
        $this->assertFalse(Gate::forUser($this->userA)->allows('view', $docB));
        $this->assertFalse(Gate::forUser($this->userA)->allows('update', $docB));
        $this->assertFalse(Gate::forUser($this->userA)->allows('delete', $docB));

        // Filament Resource Query Scope Check
        $this->actingAs($this->userA);
        $queryResults = VendorDocumentResource::getEloquentQuery()->pluck('id')->toArray();
        $this->assertNotContains($docB->id, $queryResults);
    }

    /**
     * TEST 6: Vendor A attempting to access Vendor B's Inventory
     */
    public function test_vendor_a_cannot_access_vendor_b_inventory(): void
    {
        $listingB = VendorListing::create([
            'vendor_id'              => $this->vendorB->id,
            'marketplace_product_id' => $this->marketplaceProduct->id,
            'listing_title'          => 'Vendor B Stock Listing',
            'slug'                   => 'vendor-b-stock-listing',
            'unit'                   => UnitOfMeasure::MetricTonnes,
            'base_price'             => 9000,
            'available_quantity'     => 150,
            'min_order_quantity'     => 10,
            'approval_status'        => ListingStatus::Approved,
            'is_active'              => true,
        ]);

        // Filament Inventory Resource Query Scope Check as User A
        $this->actingAs($this->userA);
        $inventoryResults = InventoryResource::getEloquentQuery()->pluck('id')->toArray();
        $this->assertNotContains($listingB->id, $inventoryResults);
    }
}
