<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fuel\Http\Requests\SyncInventoryRequest;
use App\Modules\Fuel\Http\Requests\UpdateProductStatusRequest;
use App\Modules\Fuel\Http\Resources\ProductCollection;
use App\Modules\Fuel\Http\Resources\ProductResource;
use App\Modules\Fuel\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {}

    /**
     * GET /api/v1/products
     * List products with optional filters: status, vendor_id, category_id, search.
     */
    public function index(Request $request): ProductCollection
    {
        $products = $this->productService->getProducts(
            filters: $request->only(['status', 'vendor_id', 'category_id', 'search']),
            perPage: (int) $request->get('per_page', 15),
        );

        return new ProductCollection($products);
    }

    /**
     * GET /api/v1/products/{id}
     * Show a single product with inventory & vendor details.
     */
    public function show(string $id): ProductResource
    {
        $product = $this->productService->getProduct($id);

        return new ProductResource($product);
    }

    /**
     * PATCH /api/v1/products/{id}/status
     * Enable / Disable / mark Coming Soon.
     */
    public function updateStatus(UpdateProductStatusRequest $request, string $id): JsonResponse
    {
        try {
            $product = $this->productService->updateStatus($id, $request->validated('status'));

            return response()->json([
                'success' => true,
                'message' => "Product status updated to '{$product->status}'.",
                'data'    => new ProductResource($product),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * POST /api/v1/products/{id}/sync-inventory
     * Sync inventory level for a product (vendor or admin).
     */
    public function syncInventory(SyncInventoryRequest $request, string $id): JsonResponse
    {
        try {
            $validated = $request->validated();
            $inventory = $this->productService->syncInventory(
                productId:         $id,
                quantityAvailable: (float) $validated['quantity_available'],
                lowStockThreshold: (float) ($validated['low_stock_threshold'] ?? 100.0),
            );

            return response()->json([
                'success' => true,
                'message' => 'Inventory synchronized successfully.',
                'data'    => [
                    'product_id'         => $inventory->product_id,
                    'quantity_available' => $inventory->quantity_available,
                    'low_stock_threshold'=> $inventory->low_stock_threshold,
                    'last_restocked_at'  => $inventory->last_restocked_at,
                    'is_low_stock'       => $inventory->quantity_available <= $inventory->low_stock_threshold,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * POST /api/v1/products/bulk-sync
     * Dispatch a queued bulk inventory sync job from vendor API payload.
     */
    public function bulkSync(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'items'                       => 'required|array|min:1',
            'items.*.product_id'          => 'required|uuid|exists:products,id',
            'items.*.quantity_available'  => 'required|numeric|min:0',
        ]);

        \App\Modules\Fuel\Jobs\SyncInventoryJob::dispatch(
            $validated['items'],
            'api_sync',
        );

        return response()->json([
            'success' => true,
            'message' => 'Bulk inventory sync dispatched to queue.',
            'count'   => count($validated['items']),
        ]);
    }
}
