<?php

declare(strict_types=1);

namespace App\Filament\Operations\Resources\InventoryResource\Pages;

use App\Filament\Operations\Resources\InventoryResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListInventories extends ListRecords
{
    protected static string $resource = InventoryResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
