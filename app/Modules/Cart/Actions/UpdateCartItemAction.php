<?php

declare(strict_types=1);

namespace App\Modules\Cart\Actions;

use App\Enums\ListingStatus;
use App\Enums\UnitOfMeasure;
use App\Modules\Cart\DTOs\UpdateCartItemDTO;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Vendor\Enums\VendorStatus;
use Illuminate\Support\Facades\DB;

class UpdateCartItemAction
{
    public function execute(CartItem $item, UpdateCartItemDTO $dto): CartItem
    {
        return DB::transaction(function () use ($item, $dto) {
            $newQuantity = $dto->quantity;

            // ── A. Marketplace Listing Cart Item ──────────────────────────────
            if ($item->vendorListing) {
                $listing = $item->vendorListing;

                // 1. Vendor status check
                $vendor = $listing->vendor;
                if (! $vendor || $vendor->status !== VendorStatus::Approved) {
                    $vendorName = $vendor?->brand_name ?? 'Vendor';
                    throw new \DomainException("Vendor '{$vendorName}' is not currently active to accept orders.");
                }

                // 2. Approval status check
                if ($listing->approval_status !== ListingStatus::Approved) {
                    throw new \DomainException("Listing '{$listing->listing_title}' is not currently approved.");
                }

                // 3. Active status check
                if (! $listing->is_active) {
                    throw new \DomainException("Listing '{$listing->listing_title}' is currently unavailable.");
                }

                $unitStr = $listing->unit instanceof UnitOfMeasure ? $listing->unit->value : (string) $listing->unit;

                // 4. MOQ validation
                $minQty = (float) ($listing->min_order_quantity ?? 1.0);
                if ($newQuantity < $minQty) {
                    throw new \DomainException("Minimum order quantity for '{$listing->listing_title}' is {$minQty} {$unitStr}.");
                }

                // 5. Max quantity validation
                if ($listing->max_order_quantity !== null) {
                    $maxQty = (float) $listing->max_order_quantity;
                    if ($newQuantity > $maxQty) {
                        throw new \DomainException("Maximum allowed order quantity for '{$listing->listing_title}' is {$maxQty} {$unitStr}.");
                    }
                }

                // 6. Inventory check
                $availableStock = (float) $listing->available_quantity;
                if ($newQuantity > $availableStock) {
                    throw new \DomainException("Insufficient stock available for '{$listing->listing_title}'. Available stock: {$availableStock} {$unitStr}.");
                }

                $item->update([
                    'quantity'       => $newQuantity,
                    'price_snapshot' => (float) $listing->base_price,
                ]);

                return $item->fresh();
            }

            // ── B. Direct Product Cart Item ───────────────────────────────────
            if ($item->product) {
                $product = $item->product;

                if (! $product->isOrderingEnabled()) {
                    throw new \DomainException("Product '{$product->name}' is not available for ordering.");
                }

                $unitStr = $product->unit_of_measure instanceof UnitOfMeasure
                    ? $product->unit_of_measure->value
                    : ($product->unit_of_measure ?? 'units');

                // 1. MOQ validation
                $minQty = (float) ($product->min_order_quantity ?? 1.0);
                if ($newQuantity < $minQty) {
                    throw new \DomainException("Minimum order quantity for '{$product->name}' is {$minQty} {$unitStr}.");
                }

                // 2. Max quantity validation
                if ($product->max_order_quantity !== null) {
                    $maxQty = (float) $product->max_order_quantity;
                    if ($newQuantity > $maxQty) {
                        throw new \DomainException("Maximum allowed order quantity for '{$product->name}' is {$maxQty} {$unitStr}.");
                    }
                }

                // 3. Inventory check
                if (isset($product->current_stock) && $product->current_stock !== null) {
                    $availableStock = (float) $product->current_stock;
                    if ($newQuantity > $availableStock) {
                        throw new \DomainException("Insufficient inventory stock available for '{$product->name}'. Available: {$availableStock} {$unitStr}.");
                    }
                }

                $item->update([
                    'quantity'       => $newQuantity,
                    'price_snapshot' => (float) $product->price_per_unit,
                ]);

                return $item->fresh();
            }

            // Fallback simple update if target product/listing no longer exists
            $item->update(['quantity' => $newQuantity]);
            return $item->fresh();
        });
    }
}
