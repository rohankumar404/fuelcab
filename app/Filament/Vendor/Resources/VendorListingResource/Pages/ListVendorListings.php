<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\VendorListingResource\Pages;

use App\Filament\Vendor\Resources\VendorListingResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendorListings extends ListRecords
{
    protected static string $resource = VendorListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
