<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\SettlementResource\Pages;

use App\Filament\Vendor\Resources\SettlementResource;
use Filament\Resources\Pages\ListRecords;

class ListSettlements extends ListRecords
{
    protected static string $resource = SettlementResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
