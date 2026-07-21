<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\InventoryResource\Pages;

use App\Filament\Vendor\Resources\InventoryResource;
use Filament\Resources\Pages\ListRecords;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
