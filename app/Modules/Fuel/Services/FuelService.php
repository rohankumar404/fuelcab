<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Services;

use App\Modules\Fuel\Events\InventorySynced;
use App\Modules\Fuel\Models\FuelInventory;
use App\Modules\Fuel\Models\InventoryLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class FuelService
{
    /**
     * Deduct fuel inventory for an order's items.
     *
     * @throws RuntimeException if stock is insufficient.
     */
    public function deductForOrder(string $orderId, string $vendorId, string $productId, float $quantity): void
    {
        if ($quantity <= 0) {
            return;
        }

        DB::transaction(function () use ($orderId, $vendorId, $productId, $quantity): void {
            /** @var FuelInventory|null $inventory */
            $inventory = FuelInventory::where('vendor_id', $vendorId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->first();

            if (! $inventory) {
                // If direct product inventory doesn't exist, check by product_id only
                $inventory = FuelInventory::where('product_id', $productId)
                    ->lockForUpdate()
                    ->first();
            }

            if (! $inventory) {
                throw new RuntimeException("Inventory not found for product [{$productId}] and vendor [{$vendorId}].");
            }

            $before = (float) $inventory->quantity_available;

            if ($before < $quantity) {
                throw new RuntimeException("Insufficient stock for product [{$productId}]. Available: {$before}, Requested: {$quantity}.");
            }

            $after = $before - $quantity;
            $inventory->update(['quantity_available' => $after]);

            // Log change
            InventoryLog::create([
                'inventory_id' => $inventory->id,
                'product_id' => $productId,
                'vendor_id' => $vendorId,
                'type' => 'deduction',
                'quantity_before' => $before,
                'quantity_changed' => -$quantity,
                'quantity_after' => $after,
                'reference_type' => 'order',
                'reference_id' => $orderId,
                'notes' => "Inventory deducted for order #{$orderId}",
            ]);

            Log::info('[FuelService] Inventory deducted.', [
                'order_id' => $orderId,
                'product_id' => $productId,
                'deduction' => $quantity,
                'new_stock' => $after,
            ]);

            // Fire event for low stock checks, etc.
            event(new InventorySynced($inventory));
        });
    }

    /**
     * Restock fuel inventory.
     */
    public function restock(string $vendorId, string $productId, float $quantity, ?string $userId = null): FuelInventory
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Quantity must be greater than zero.');
        }

        return DB::transaction(function () use ($vendorId, $productId, $quantity, $userId): FuelInventory {
            $inventory = FuelInventory::lockForUpdate()->firstOrCreate(
                ['vendor_id' => $vendorId, 'product_id' => $productId],
                ['quantity_available' => 0.00, 'low_stock_threshold' => 100.00]
            );

            $before = (float) $inventory->quantity_available;
            $after = $before + $quantity;

            $inventory->update([
                'quantity_available' => $after,
                'last_restocked_at' => now(),
            ]);

            InventoryLog::create([
                'inventory_id' => $inventory->id,
                'product_id' => $productId,
                'vendor_id' => $vendorId,
                'type' => 'restock',
                'quantity_before' => $before,
                'quantity_changed' => $quantity,
                'quantity_after' => $after,
                'reference_type' => 'manual',
                'created_by' => $userId,
                'notes' => 'Manual restock.',
            ]);

            Log::info('[FuelService] Inventory restocked.', [
                'vendor_id' => $vendorId,
                'product_id' => $productId,
                'amount' => $quantity,
                'new_stock' => $after,
            ]);

            event(new InventorySynced($inventory));

            return $inventory;
        });
    }
}
