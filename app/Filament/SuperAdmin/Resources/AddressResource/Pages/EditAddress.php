<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\AddressResource\Pages;

use App\Filament\SuperAdmin\Resources\AddressResource;
use Filament\Resources\Pages\EditRecord;

class EditAddress extends EditRecord
{
    protected static string $resource = AddressResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
