<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\DirectPricingResource\Pages;

use App\Filament\SuperAdmin\Resources\DirectPricingResource;
use Filament\Resources\Pages\EditRecord;

class EditDirectPricing extends EditRecord
{
    protected static string $resource = DirectPricingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
