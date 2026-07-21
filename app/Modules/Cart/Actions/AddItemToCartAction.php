<?php

declare(strict_types=1);

namespace App\Modules\Cart\Actions;

use App\Enums\ListingStatus;
use App\Enums\SalesChannel;
use App\Enums\UnitOfMeasure;
use App\Modules\Cart\DTOs\AddCartItemDTO;
use App\Modules\Cart\Events\CartItemAdded;
use App\Modules\Cart\Models\Cart;
use App\Modules\Cart\Models\CartItem;
use App\Modules\Fuel\Models\Product;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Models\VendorListing;
use Illuminate\Support\Facades\DB;

class AddItemToCartAction
{
    public function execute(Cart $cart, AddCartItemDTO $dto): CartItem
    {
        return DB::transaction(function () use ($cart, $dto) {
            // ── A. Marketplace Listing Flow (explicit listing_id takes precedence) ──
            if ($dto->vendorListingId) {
                return $this->addMarketplaceListing($cart, $dto);
            }

            // ── B. Direct Product or Marketplace Product (by product_id) ─────────
            if ($dto->productId) {
                return $this->addDirectProduct($cart, $dto);
            }

            throw new \DomainException('Invalid cart payload: product_id or vendor_listing_id required.');
        });
    }

    private function addMarketplaceListing(Cart $cart, AddCartItemDTO $dto): CartItem
    {
        $listing = VendorListing::with(['vendor', 'marketplaceProduct'])
            ->findOrFail($dto->vendorListingId);

        // 1. Vendor status validation
        $vendor = $listing->vendor;
        if (! $vendor || $vendor->status !== VendorStatus::Approved) {
            $vendorName = $vendor?->brand_name ?? 'Vendor';
            throw new \DomainException("Vendor '{$vendorName}' is not currently active to accept orders.");
        }

        // 2. Listing approval validation
        if ($listing->approval_status !== ListingStatus::Approved) {
            throw new \DomainException("Listing '{$listing->listing_title}' is not currently approved for ordering.");
        }

        // 3. Ordering enabled / Active validation
        if (! $listing->is_active) {
            throw new \DomainException("Listing '{$listing->listing_title}' is currently unavailable for ordering.");
        }

        $unitStr = $listing->unit instanceof UnitOfMeasure ? $listing->unit->value : (string) $listing->unit;

        // Check if item already exists in cart to validate total combined quantity
        $existing = CartItem::where('cart_id', $cart->id)
            ->where('vendor_listing_id', $listing->id)
            ->whereNull('deleted_at')
            ->first();

        $targetQuantity = $existing ? ($existing->quantity + $dto->quantity) : $dto->quantity;

        // 4. MOQ validation
        $minQty = (float) ($listing->min_order_quantity ?? 1.0);
        if ($targetQuantity < $minQty) {
            throw new \DomainException("Minimum order quantity for '{$listing->listing_title}' is {$minQty} {$unitStr}.");
        }

        // 5. Maximum quantity validation
        if ($listing->max_order_quantity !== null) {
            $maxQty = (float) $listing->max_order_quantity;
            if ($targetQuantity > $maxQty) {
                throw new \DomainException("Maximum allowed order quantity for '{$listing->listing_title}' is {$maxQty} {$unitStr}.");
            }
        }

        // 6. Inventory validation
        $availableStock = (float) $listing->available_quantity;
        if ($targetQuantity > $availableStock) {
            throw new \DomainException("Insufficient stock available for '{$listing->listing_title}'. Available stock: {$availableStock} {$unitStr}.");
        }

        // Add or update line item
        if ($existing) {
            $existing->update([
                'quantity'              => $targetQuantity,
                'price_snapshot'        => (float) $listing->base_price,
                'product_name_snapshot' => $listing->listing_title,
                'product_sku_snapshot'  => $listing->sku,
                'unit_snapshot'         => $unitStr,
            ]);
            $item = $existing->fresh();
        } else {
            $item = CartItem::create([
                'cart_id'               => $cart->id,
                'product_id'            => null,   // marketplace items reference via vendor_listing_id
                'vendor_listing_id'     => $listing->id,
                'quantity'              => $dto->quantity,
                'price_snapshot'        => (float) $listing->base_price,
                'unit_of_measure'       => $unitStr,
                'sales_channel'         => SalesChannel::Marketplace->value,
                'vendor_id'             => $listing->vendor_id,
                'product_name_snapshot' => $listing->listing_title,
                'product_sku_snapshot'  => $listing->sku,
                'unit_snapshot'         => $unitStr,
            ]);
        }

        event(new CartItemAdded($cart, $item));

        return $item;
    }

    private function addDirectProduct(Cart $cart, AddCartItemDTO $dto): CartItem
    {
        $product = Product::with('vendor')->findOrFail($dto->productId);

        // 1. Ordering enabled validation
        if (! $product->isOrderingEnabled()) {
            throw new \DomainException("Product '{$product->name}' is not available for ordering.");
        }

        $unitStr = $product->unit_of_measure instanceof UnitOfMeasure
            ? $product->unit_of_measure->value
            : ($product->unit_of_measure ?? 'units');

        $existing = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $product->id)
            ->whereNull('vendor_listing_id')
            ->whereNull('deleted_at')
            ->first();

        $targetQuantity = $existing ? ($existing->quantity + $dto->quantity) : $dto->quantity;

        // 2. MOQ validation
        $minQty = (float) ($product->min_order_quantity ?? 1.0);
        if ($targetQuantity < $minQty) {
            throw new \DomainException("Minimum order quantity for '{$product->name}' is {$minQty} {$unitStr}.");
        }

        // 3. Maximum quantity validation
        if ($product->max_order_quantity !== null) {
            $maxQty = (float) $product->max_order_quantity;
            if ($targetQuantity > $maxQty) {
                throw new \DomainException("Maximum allowed order quantity for '{$product->name}' is {$maxQty} {$unitStr}.");
            }
        }

        // 4. Inventory validation
        if (isset($product->current_stock) && $product->current_stock !== null) {
            $availableStock = (float) $product->current_stock;
            if ($targetQuantity > $availableStock) {
                throw new \DomainException("Insufficient inventory stock available for '{$product->name}'. Available: {$availableStock} {$unitStr}.");
            }
        }

        // Resolve sales channel & vendor context
        // IMPORTANT: always store vendor_id — even for first-party Direct products.
        // The checkout address action uses it to validate delivery radius.
        $isFirstParty = $product->vendor ? $product->vendor->is_first_party : true;
        $channel      = $isFirstParty ? SalesChannel::Direct : SalesChannel::Marketplace;
        $vendorId     = $product->vendor_id;  // always stored

        if ($existing) {
            $existing->update([
                'quantity'              => $targetQuantity,
                'price_snapshot'        => (float) $product->price_per_unit,
                'product_name_snapshot' => $product->name,
                'product_sku_snapshot'  => $product->sku,
                'unit_snapshot'         => $unitStr,
            ]);
            $item = $existing->fresh();
        } else {
            $item = CartItem::create([
                'cart_id'               => $cart->id,
                'product_id'            => $product->id,
                'quantity'              => $dto->quantity,
                'price_snapshot'        => (float) $product->price_per_unit,
                'unit_of_measure'       => $unitStr,
                'sales_channel'         => $channel->value,
                'vendor_id'             => $vendorId,
                'product_name_snapshot' => $product->name,
                'product_sku_snapshot'  => $product->sku,
                'unit_snapshot'         => $unitStr,
            ]);
        }

        event(new CartItemAdded($cart, $item));

        return $item;
    }
}
