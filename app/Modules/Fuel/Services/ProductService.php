<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Services;

use App\Modules\Fuel\Events\InventorySynced;
use App\Modules\Fuel\Events\ProductStatusChanged;
use App\Modules\Fuel\Models\FuelInventory;
use App\Modules\Fuel\Models\Product;
use App\Modules\Fuel\Repositories\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ProductService
{
    public function __construct(
        private readonly ProductRepository $repository,
    ) {}

    /**
     * Get paginated product listing with filters.
     */
    public function getProducts(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return $this->repository->paginate($filters, $perPage);
    }

    /**
     * Get a single product by ID.
     */
    public function getProduct(string $id): Product
    {
        return $this->repository->findById($id);
    }

    /**
     * Update the ordering/listing status of a product.
     */
    public function updateStatus(string $productId, string $status): Product
    {
        if (!in_array($status, ['active', 'disabled', 'soon'], true)) {
            throw new \InvalidArgumentException("Invalid product status: {$status}");
        }

        return DB::transaction(function () use ($productId, $status) {
            $product = Product::findOrFail($productId);
            $oldStatus = $product->status;

            $product->update([
                'status'    => $status,
                'is_active' => $status === 'active',
            ]);

            event(new ProductStatusChanged($product, $oldStatus, $status));

            return $product->fresh();
        });
    }

    /**
     * Sync current inventory level for a product. Fires InventorySynced event.
     */
    public function syncInventory(
        string $productId,
        float $quantityAvailable,
        string $referenceType = 'manual',
        ?string $referenceId = null,
        float $lowStockThreshold = 100.0,
    ): FuelInventory {
        return DB::transaction(function () use ($productId, $quantityAvailable, $referenceType, $referenceId, $lowStockThreshold) {
            $product = Product::findOrFail($productId);

            $existing = FuelInventory::firstOrNew(['product_id' => $productId]);
            $quantityBefore = (float) ($existing->quantity_available ?? 0);

            $inventory = FuelInventory::updateOrCreate(
                ['product_id' => $productId],
                [
                    'vendor_id'           => $product->vendor_id,
                    'quantity_available'  => $quantityAvailable,
                    'low_stock_threshold' => $lowStockThreshold,
                    'last_restocked_at'   => now(),
                ]
            );

            event(new InventorySynced($inventory, $quantityBefore, $quantityAvailable, $referenceType, $referenceId));

            return $inventory;
        });
    }
}
