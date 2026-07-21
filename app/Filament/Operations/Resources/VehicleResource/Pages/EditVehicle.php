<?php

declare(strict_types=1);

namespace App\Filament\Operations\Resources\VehicleResource\Pages;

use App\Filament\Operations\Resources\VehicleResource;
use Filament\Resources\Pages\EditRecord;

class EditVehicle extends EditRecord
{
    protected static string $resource = VehicleResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
