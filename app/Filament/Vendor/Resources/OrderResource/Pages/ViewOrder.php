<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\OrderResource\Pages;

use App\Filament\Vendor\Resources\OrderResource;
use Filament\Resources\Pages\ViewRecord;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
