<?php

declare(strict_types=1);

namespace App\Filament\Operations\Resources\InventoryResource\Pages;

use App\Filament\Operations\Resources\InventoryResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInventory extends CreateRecord
{
    protected static string $resource = InventoryResource::class;
}
