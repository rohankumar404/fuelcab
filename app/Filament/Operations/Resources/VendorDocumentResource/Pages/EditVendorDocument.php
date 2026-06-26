<?php

namespace App\Filament\Operations\Resources\VendorDocumentResource\Pages;

use App\Filament\Operations\Resources\VendorDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVendorDocument extends EditRecord
{
    protected static string $resource = VendorDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
