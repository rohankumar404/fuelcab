<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\VendorListingResource\Pages;

use App\Filament\SuperAdmin\Resources\VendorListingResource;
use Filament\Resources\Pages\ListRecords;

class ListVendorListings extends ListRecords
{
    protected static string $resource = VendorListingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
