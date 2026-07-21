<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\VendorDocumentResource\Pages;

use App\Filament\Vendor\Resources\VendorDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditVendorDocument extends EditRecord
{
    protected static string $resource = VendorDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->label('Remove Document'),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
