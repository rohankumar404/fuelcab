<?php

namespace App\Filament\Operations\Resources\VendorDocumentResource\Pages;

use App\Filament\Operations\Resources\VendorDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendorDocuments extends ListRecords
{
    protected static string $resource = VendorDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
