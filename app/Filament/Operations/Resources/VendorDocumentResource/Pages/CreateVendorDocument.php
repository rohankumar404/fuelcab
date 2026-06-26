<?php

namespace App\Filament\Operations\Resources\VendorDocumentResource\Pages;

use App\Filament\Operations\Resources\VendorDocumentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateVendorDocument extends CreateRecord
{
    protected static string $resource = VendorDocumentResource::class;
}
