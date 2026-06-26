<?php

namespace App\Filament\Operations\Resources\DriverDocumentResource\Pages;

use App\Filament\Operations\Resources\DriverDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDriverDocument extends EditRecord
{
    protected static string $resource = DriverDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
