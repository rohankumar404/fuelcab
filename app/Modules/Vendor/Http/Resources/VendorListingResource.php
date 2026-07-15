<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VendorListingResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                       => $this->id,
            'listing_title'            => $this->listing_title,
            'slug'                     => $this->slug,
            'sku'                      => $this->sku,
            'short_description'        => $this->short_description,
            'full_description'         => $this->full_description,
            'product_images'           => $this->product_images ?? [],
            'min_order_quantity'       => (float) $this->min_order_quantity,
            'max_order_quantity'       => $this->max_order_quantity ? (float) $this->max_order_quantity : null,
            'unit'                     => $this->unit instanceof \BackedEnum ? $this->unit->value : $this->unit,
            'available_quantity'       => (float) $this->available_quantity,
            'base_price'               => (float) $this->base_price,
            'price_with_tax'           => $this->priceWithTax(),
            'tax_rate'                 => (float) $this->tax_rate,
            'tax_inclusive'            => (bool) $this->tax_inclusive,
            'dispatch_location'        => $this->dispatch_location,
            'serviceable_locations'    => $this->serviceable_locations ?? [],
            'estimated_dispatch_hours' => $this->estimated_dispatch_hours,
            'quality_specifications'   => $this->quality_specifications,
            'certificate_documents'    => $this->certificate_documents ?? [],
            'is_active'                => (bool) $this->is_active,
            'is_featured'              => (bool) $this->is_featured,
            'approval_status'          => $this->approval_status instanceof \BackedEnum
                ? $this->approval_status->value
                : $this->approval_status,
            'approval_status_label'    => $this->approval_status instanceof \App\Enums\ListingStatus
                ? $this->approval_status->label()
                : null,
            // rejection_reason visible only to vendor and admin — not public
            'rejection_reason'         => $this->when(
                $request->user()?->hasAnyRole(['super_admin', 'operations_team', 'vendor_admin', 'vendor_staff']),
                $this->rejection_reason
            ),
            'approved_at'              => $this->approved_at,
            'marketplace_product'      => $this->whenLoaded('marketplaceProduct', fn () => [
                'id'       => $this->marketplaceProduct->id,
                'name'     => $this->marketplaceProduct->name,
                'slug'     => $this->marketplaceProduct->slug,
                'category' => $this->marketplaceProduct->relationLoaded('category') ? [
                    'id'   => $this->marketplaceProduct->category?->id,
                    'name' => $this->marketplaceProduct->category?->name,
                ] : null,
            ]),
            'vendor'                   => $this->whenLoaded('vendor', fn () => [
                'id'         => $this->vendor->id,
                'brand_name' => $this->vendor->brand_name,
                'city'       => $this->vendor->city,
                'state'      => $this->vendor->state,
            ]),
            'created_at'               => $this->created_at,
            'updated_at'               => $this->updated_at,
        ];
    }
}
