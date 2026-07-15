<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Vendor\Http\Resources\VendorListingCollection;
use App\Modules\Vendor\Http\Resources\VendorListingResource;
use App\Modules\Vendor\Models\VendorListing;
use App\Modules\Vendor\Services\VendorListingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VendorListingController extends Controller
{
    public function __construct(
        private readonly VendorListingService $service,
    ) {}

    // ── Public (unauthenticated) ─────────────────────────────────────────────

    /**
     * GET /api/v1/marketplace/listings
     * Public: returns only APPROVED + active listings.
     */
    public function publicIndex(Request $request): VendorListingCollection
    {
        $listings = $this->service->getPublicListings(
            filters: $request->only(['marketplace_product_id', 'vendor_id', 'dispatch_location', 'featured', 'search']),
            perPage: (int) $request->get('per_page', 20),
        );

        return new VendorListingCollection($listings);
    }

    /**
     * GET /api/v1/marketplace/listings/{listing}
     * Public: show a single approved listing.
     */
    public function publicShow(string $slug): JsonResponse
    {
        $listing = VendorListing::with(['vendor', 'marketplaceProduct.category'])
            ->public()
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data'    => new VendorListingResource($listing),
        ]);
    }

    // ── Vendor self-service ──────────────────────────────────────────────────

    /**
     * GET /api/v1/vendor/listings
     * Vendor: list own listings (all statuses).
     */
    public function index(Request $request): VendorListingCollection
    {
        $this->authorize('viewAny', VendorListing::class);

        $listings = $this->service->getVendorListings(
            vendorId: $request->user()->vendor_id,
            filters:  $request->only(['approval_status']),
            perPage:  (int) $request->get('per_page', 20),
        );

        return new VendorListingCollection($listings);
    }

    /**
     * POST /api/v1/vendor/listings
     * Vendor: create a new DRAFT listing.
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', VendorListing::class);

        $validated = $request->validate([
            'marketplace_product_id'   => 'required|uuid|exists:marketplace_products,id',
            'listing_title'            => 'required|string|max:255',
            'slug'                     => 'nullable|string|max:255',
            'sku'                      => 'nullable|string|max:100',
            'short_description'        => 'nullable|string|max:500',
            'full_description'         => 'nullable|string',
            'product_images'           => 'nullable|array',
            'product_images.*'         => 'url',
            'min_order_quantity'       => 'required|numeric|min:0.0001',
            'max_order_quantity'       => 'nullable|numeric|gt:min_order_quantity',
            'unit'                     => 'required|string|in:litres,kilograms,metric_tonnes,units',
            'available_quantity'       => 'required|numeric|min:0',
            'base_price'               => 'required|numeric|min:0.01',
            'tax_rate'                 => 'nullable|numeric|min:0|max:100',
            'tax_inclusive'            => 'nullable|boolean',
            'dispatch_location'        => 'nullable|string|max:255',
            'serviceable_locations'    => 'nullable|array',
            'serviceable_locations.*'  => 'string',
            'estimated_dispatch_hours' => 'nullable|integer|min:1',
            'quality_specifications'   => 'nullable|array',
            'certificate_documents'    => 'nullable|array',
            'certificate_documents.*'  => 'url',
            'is_active'                => 'nullable|boolean',
        ]);

        $listing = $this->service->create($validated, $request->user()->vendor_id);

        return response()->json([
            'success' => true,
            'message' => 'Listing draft created successfully.',
            'data'    => new VendorListingResource($listing->load(['vendor', 'marketplaceProduct'])),
        ], 201);
    }

    /**
     * GET /api/v1/vendor/listings/{listing}
     * Vendor: show own listing by ID.
     */
    public function show(VendorListing $listing): JsonResponse
    {
        $this->authorize('view', $listing);

        return response()->json([
            'success' => true,
            'data'    => new VendorListingResource($listing->load(['vendor', 'marketplaceProduct.category'])),
        ]);
    }

    /**
     * PUT /api/v1/vendor/listings/{listing}
     * Vendor: update a DRAFT or REJECTED listing.
     */
    public function update(Request $request, VendorListing $listing): JsonResponse
    {
        $this->authorize('update', $listing);

        $validated = $request->validate([
            'listing_title'            => 'sometimes|string|max:255',
            'slug'                     => 'nullable|string|max:255',
            'sku'                      => 'nullable|string|max:100',
            'short_description'        => 'nullable|string|max:500',
            'full_description'         => 'nullable|string',
            'product_images'           => 'nullable|array',
            'product_images.*'         => 'url',
            'min_order_quantity'       => 'sometimes|numeric|min:0.0001',
            'max_order_quantity'       => 'nullable|numeric',
            'unit'                     => 'sometimes|string|in:litres,kilograms,metric_tonnes,units',
            'available_quantity'       => 'sometimes|numeric|min:0',
            'base_price'               => 'sometimes|numeric|min:0.01',
            'tax_rate'                 => 'nullable|numeric|min:0|max:100',
            'tax_inclusive'            => 'nullable|boolean',
            'dispatch_location'        => 'nullable|string|max:255',
            'serviceable_locations'    => 'nullable|array',
            'serviceable_locations.*'  => 'string',
            'estimated_dispatch_hours' => 'nullable|integer|min:1',
            'quality_specifications'   => 'nullable|array',
            'certificate_documents'    => 'nullable|array',
            'certificate_documents.*'  => 'url',
            'is_active'                => 'nullable|boolean',
        ]);

        $updated = $this->service->update($listing, $validated);

        return response()->json([
            'success' => true,
            'message' => 'Listing updated successfully.',
            'data'    => new VendorListingResource($updated->load(['vendor', 'marketplaceProduct'])),
        ]);
    }

    /**
     * POST /api/v1/vendor/listings/{listing}/submit
     * Vendor: submit DRAFT/REJECTED listing for admin review.
     */
    public function submit(VendorListing $listing): JsonResponse
    {
        $this->authorize('submit', $listing);

        $submitted = $this->service->submit($listing);

        return response()->json([
            'success' => true,
            'message' => 'Listing submitted for approval.',
            'data'    => new VendorListingResource($submitted),
        ]);
    }

    /**
     * PATCH /api/v1/vendor/listings/{listing}/inventory
     * Vendor: update available stock quantity.
     */
    public function updateInventory(Request $request, VendorListing $listing): JsonResponse
    {
        $this->authorize('updateInventory', $listing);

        $validated = $request->validate([
            'available_quantity' => 'required|numeric|min:0',
        ]);

        $updated = $this->service->updateInventory($listing, (float) $validated['available_quantity']);

        return response()->json([
            'success'            => true,
            'message'            => 'Inventory updated successfully.',
            'available_quantity' => (float) $updated->available_quantity,
        ]);
    }

    /**
     * PATCH /api/v1/vendor/listings/{listing}/price
     * Vendor: update base price.
     */
    public function updatePrice(Request $request, VendorListing $listing): JsonResponse
    {
        $this->authorize('updatePrice', $listing);

        $validated = $request->validate([
            'base_price' => 'required|numeric|min:0.01',
        ]);

        $updated = $this->service->updatePrice($listing, (float) $validated['base_price']);

        return response()->json([
            'success'    => true,
            'message'    => 'Price updated successfully.',
            'base_price' => (float) $updated->base_price,
        ]);
    }

    /**
     * DELETE /api/v1/vendor/listings/{listing}
     * Soft delete (super admin only).
     */
    public function destroy(VendorListing $listing): JsonResponse
    {
        $this->authorize('delete', $listing);

        $listing->delete();

        return response()->json([
            'success' => true,
            'message' => 'Listing deleted.',
        ]);
    }

    // ── Admin endpoints ──────────────────────────────────────────────────────

    /**
     * GET /api/v1/admin/listings
     * Admin: all listings with filters.
     */
    public function adminIndex(Request $request): VendorListingCollection
    {
        $this->authorize('viewAny', VendorListing::class);

        $listings = $this->service->getAdminListings(
            filters: $request->only(['approval_status', 'vendor_id', 'marketplace_product_id']),
            perPage: (int) $request->get('per_page', 20),
        );

        return new VendorListingCollection($listings);
    }

    /**
     * GET /api/v1/admin/listings/{listing}
     */
    public function adminShow(VendorListing $listing): JsonResponse
    {
        $this->authorize('view', $listing);

        return response()->json([
            'success' => true,
            'data'    => new VendorListingResource($listing->load(['vendor', 'marketplaceProduct.category', 'reviewer'])),
        ]);
    }

    /**
     * POST /api/v1/admin/listings/{listing}/approve
     */
    public function approve(VendorListing $listing): JsonResponse
    {
        $this->authorize('approve', $listing);

        $approved = $this->service->approve($listing, auth()->user());

        return response()->json([
            'success' => true,
            'message' => "Listing '{$approved->listing_title}' approved.",
            'data'    => new VendorListingResource($approved),
        ]);
    }

    /**
     * POST /api/v1/admin/listings/{listing}/reject
     */
    public function reject(Request $request, VendorListing $listing): JsonResponse
    {
        $this->authorize('reject', $listing);

        $request->validate([
            'reason' => 'required|string|max:1000',
        ]);

        $rejected = $this->service->reject($listing, auth()->user(), $request->reason);

        return response()->json([
            'success' => true,
            'message' => "Listing '{$rejected->listing_title}' rejected.",
            'data'    => new VendorListingResource($rejected),
        ]);
    }

    /**
     * POST /api/v1/admin/listings/{listing}/suspend
     */
    public function suspend(VendorListing $listing): JsonResponse
    {
        $this->authorize('suspend', $listing);

        $suspended = $this->service->suspend($listing);

        return response()->json([
            'success' => true,
            'message' => "Listing '{$suspended->listing_title}' suspended.",
            'data'    => new VendorListingResource($suspended),
        ]);
    }

    /**
     * POST /api/v1/admin/listings/{listing}/feature
     */
    public function feature(VendorListing $listing): JsonResponse
    {
        $this->authorize('feature', $listing);

        $updated = $this->service->toggleFeatured($listing);
        $state   = $updated->is_featured ? 'featured' : 'unfeatured';

        return response()->json([
            'success'     => true,
            'message'     => "Listing '{$updated->listing_title}' {$state}.",
            'is_featured' => $updated->is_featured,
        ]);
    }
}
