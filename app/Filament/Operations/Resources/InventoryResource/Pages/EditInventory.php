<?php

declare(strict_types=1);

namespace App\Filament\Operations\Resources\InventoryResource\Pages;

use App\Filament\Operations\Resources\InventoryResource;
use Filament\Resources\Pages\EditRecord;

class EditInventory extends EditRecord
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
