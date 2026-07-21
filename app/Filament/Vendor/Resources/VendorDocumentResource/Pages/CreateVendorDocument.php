<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\VendorDocumentResource\Pages;

use App\Filament\Vendor\Resources\VendorDocumentResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVendorDocument extends CreateRecord
{
    protected static string $resource = VendorDocumentResource::class;

    /**
     * SECURITY: Force vendor_id from authenticated user — never from the form payload.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['vendor_id'] = auth()->user()->vendor_id;
        $data['status']    = 'pending';
        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
