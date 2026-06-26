<?php

namespace App\Filament\Vendor\Resources\DriverDocumentResource\Pages;

use App\Filament\Vendor\Resources\DriverDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDriverDocuments extends ListRecords
{
    protected static string $resource = DriverDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
