<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\VendorListingResource\Pages;

use App\Filament\SuperAdmin\Resources\VendorListingResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewVendorListing extends ViewRecord
{
    protected static string $resource = VendorListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
