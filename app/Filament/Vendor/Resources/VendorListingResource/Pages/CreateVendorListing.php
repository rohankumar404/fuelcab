<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\VendorListingResource\Pages;

use App\Filament\Vendor\Resources\VendorListingResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVendorListing extends CreateRecord
{
    protected static string $resource = VendorListingResource::class;

    /**
     * Always inject the authenticated vendor's vendor_id before saving.
     * This is a hard guard — vendor_id can never come from the Filament form payload.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['vendor_id']       = auth()->user()->vendor_id;
        $data['approval_status'] = \App\Enums\ListingStatus::Draft->value;

        // Flatten repeater image/doc arrays
        if (isset($data['product_images'])) {
            $data['product_images'] = collect($data['product_images'])->pluck('url')->filter()->values()->all();
        }
        if (isset($data['certificate_documents'])) {
            $data['certificate_documents'] = collect($data['certificate_documents'])->pluck('url')->filter()->values()->all();
        }

        return $data;
    }
}
