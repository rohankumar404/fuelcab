<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\UserFavorite;
use App\Models\UserRecentlyViewed;
use App\Models\VendorRating;
use App\Modules\Fuel\Models\MarketplaceProduct;
use App\Modules\Order\Models\Order;
use App\Modules\Vendor\Http\Resources\VendorListingResource;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorListing;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class MarketplaceController extends Controller
{
    use ApiResponse;

    // ── Categories ───────────────────────────────────────────────────────────

    /**
     * GET /api/v1/marketplace/categories
     * Returns root categories with nested children (public).
     */
    public function categories(): JsonResponse
    {
        $categories = Cache::remember('marketplace_categories', 3600, function () {
            return Category::with('children')->whereNull('parent_id')->get();
        });

        return $this->success($categories, 'Marketplace categories retrieved successfully.');
    }

    // ── Product Master (Catalog) ─────────────────────────────────────────────

    /**
     * GET /api/v1/marketplace/products
     * Paginated master product catalog, optionally filtered by category or search (public).
     */
    public function products(Request $request): JsonResponse
    {
        $products = MarketplaceProduct::with('category')
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->search, fn ($q, $s) => $q->where('name', 'ilike', "%{$s}%"))
            ->orderBy('display_order')
            ->paginate($request->integer('per_page', 20));

        return $this->success($products, 'Marketplace product master retrieved successfully.');
    }

    // ── Direct Orders ────────────────────────────────────────────────────────

    /**
     * POST /api/v1/marketplace/listings/{slug}/order
     * Place a direct marketplace order for a listing (no cart required, authenticated).
     */
    public function directOrder(Request $request, string $slug): JsonResponse
    {
        $listing = VendorListing::with('vendor')
            ->public()
            ->where('slug', $slug)
            ->firstOrFail();

        $validated = $request->validate([
            'quantity' => 'required|numeric|min:'.(float) $listing->min_order_quantity,
            'delivery_address_id' => 'required|uuid|exists:addresses,id',
            'notes' => 'nullable|string|max:1000',
        ]);

        $quantity = (float) $validated['quantity'];
        $basePrice = (float) $listing->base_price;
        $subtotal = round($basePrice * $quantity, 2);

        if ($listing->tax_inclusive) {
            $total = $subtotal;
            $tax = round($total - ($total / (1 + ((float) $listing->tax_rate / 100))), 2);
            $subtotalBeforeTax = round($total - $tax, 2);
        } else {
            $tax = round($subtotal * ((float) $listing->tax_rate / 100), 2);
            $total = round($subtotal + $tax, 2);
            $subtotalBeforeTax = $subtotal;
        }

        $order = DB::transaction(function () use ($request, $listing, $validated, $subtotalBeforeTax, $tax, $total) {
            return Order::create([
                'customer_id' => $request->user()->id,
                'vendor_id' => $listing->vendor_id,
                'channel' => 'marketplace',
                'status' => 'pending',
                'subtotal_amount' => $subtotalBeforeTax,
                'tax_amount' => $tax,
                'total_amount' => $total,
                'delivery_address_id' => $validated['delivery_address_id'],
                'notes' => $validated['notes'] ?? null,
            ]);
        });

        return $this->success([
            'order_id' => $order->id,
            'listing_slug' => $listing->slug,
            'quantity' => $quantity,
            'total_amount' => $total,
            'status' => $order->status,
        ], 'Direct order placed successfully.', 201);
    }

    // ── Wishlist (mapped to user_favorites) ──────────────────────────────────

    /**
     * GET /api/v1/marketplace/wishlist
     */
    public function listWishlist(Request $request): JsonResponse
    {
        $favorites = UserFavorite::with('listing.marketplaceProduct')
            ->where('user_id', $request->user()->id)
            ->get();

        return $this->success($favorites, 'Wishlist retrieved successfully.');
    }

    /**
     * POST /api/v1/marketplace/wishlist
     */
    public function addWishlist(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_listing_id' => 'required|uuid|exists:vendor_listings,id',
        ]);

        $favorite = UserFavorite::firstOrCreate([
            'user_id' => $request->user()->id,
            'vendor_listing_id' => $validated['vendor_listing_id'],
        ]);

        return $this->success($favorite, 'Listing added to wishlist.', 201);
    }

    /**
     * DELETE /api/v1/marketplace/wishlist/{listingId}
     */
    public function removeWishlist(Request $request, string $listingId): JsonResponse
    {
        $favorite = UserFavorite::where('user_id', $request->user()->id)
            ->where('vendor_listing_id', $listingId)
            ->firstOrFail();

        $favorite->delete();

        return $this->success(null, 'Listing removed from wishlist.');
    }

    // ── Compare ──────────────────────────────────────────────────────────────

    /**
     * POST /api/v1/marketplace/compare
     * Up to 4 listings — returns side-by-side specs (public).
     */
    public function compare(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'listing_ids' => 'required|array|min:1|max:4',
            'listing_ids.*' => 'required|uuid|exists:vendor_listings,id',
        ]);

        $listings = VendorListing::with(['vendor', 'marketplaceProduct.category'])
            ->whereIn('id', $validated['listing_ids'])
            ->get();

        return $this->success(VendorListingResource::collection($listings), 'Comparison data retrieved.');
    }

    // ── Recently Viewed ──────────────────────────────────────────────────────

    /**
     * GET /api/v1/marketplace/recently-viewed
     */
    public function listRecentlyViewed(Request $request): JsonResponse
    {
        $recentlyViewed = UserRecentlyViewed::with('listing.marketplaceProduct')
            ->where('user_id', $request->user()->id)
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();

        return $this->success($recentlyViewed, 'Recently viewed listings retrieved.');
    }

    /**
     * POST /api/v1/marketplace/recently-viewed
     */
    public function addRecentlyViewed(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'vendor_listing_id' => 'required|uuid|exists:vendor_listings,id',
        ]);

        $record = UserRecentlyViewed::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'vendor_listing_id' => $validated['vendor_listing_id'],
            ],
            ['updated_at' => now()]
        );

        return $this->success($record, 'Recently viewed listing recorded.', 201);
    }

    // ── Vendor Profiles ──────────────────────────────────────────────────────

    /**
     * GET /api/v1/marketplace/vendors/{vendor}
     * Public vendor profile: brand details, ratings, and active listings.
     */
    public function showVendorProfile(string $vendorId): JsonResponse
    {
        $vendor = Vendor::findOrFail($vendorId);

        $listings = VendorListing::with('marketplaceProduct')
            ->where('vendor_id', $vendorId)
            ->public()
            ->get();

        $avgRating = VendorRating::where('vendor_id', $vendorId)->avg('rating') ?? 0.0;
        $totalRatings = VendorRating::where('vendor_id', $vendorId)->count();

        return $this->success([
            'vendor' => [
                'id' => $vendor->id,
                'brand_name' => $vendor->brand_name,
                'city' => $vendor->city,
                'state' => $vendor->state,
                'rating_avg' => round((float) $avgRating, 2),
                'ratings_count' => $totalRatings,
            ],
            'listings' => VendorListingResource::collection($listings),
        ], 'Vendor profile retrieved successfully.');
    }

    // ── Vendor Ratings ───────────────────────────────────────────────────────

    /**
     * POST /api/v1/marketplace/vendors/{vendor}/ratings
     * Submit or update a 1–5 star rating + optional review for a vendor.
     */
    public function rateVendor(Request $request, string $vendorId): JsonResponse
    {
        $vendor = Vendor::findOrFail($vendorId);

        $validated = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:1000',
        ]);

        $rating = VendorRating::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'vendor_id' => $vendor->id,
            ],
            [
                'rating' => $validated['rating'],
                'review' => $validated['review'] ?? null,
            ]
        );

        return $this->success($rating, 'Vendor rated successfully.', 201);
    }
}
