<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\MarketplaceOrderResource\Pages;

use App\Filament\SuperAdmin\Resources\MarketplaceOrderResource;
use Filament\Resources\Pages\EditRecord;

class EditMarketplaceOrder extends EditRecord
{
    protected static string $resource = MarketplaceOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
