<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\MarketplaceProductResource\Pages;

use App\Filament\SuperAdmin\Resources\MarketplaceProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMarketplaceProduct extends CreateRecord
{
    protected static string $resource = MarketplaceProductResource::class;
}
