<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\VendorDocumentResource\Pages;

use App\Filament\Vendor\Resources\VendorDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListVendorDocuments extends ListRecords
{
    protected static string $resource = VendorDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()
                ->label('Upload Document'),
        ];
    }
}
