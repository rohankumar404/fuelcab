<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\VendorDocumentResource\Pages;

use App\Filament\SuperAdmin\Resources\VendorDocumentResource;
use Filament\Resources\Pages\EditRecord;

class EditVendorDocument extends EditRecord
{
    protected static string $resource = VendorDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
