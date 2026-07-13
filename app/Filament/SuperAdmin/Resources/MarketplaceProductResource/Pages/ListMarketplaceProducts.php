<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\MarketplaceProductResource\Pages;

use App\Filament\SuperAdmin\Resources\MarketplaceProductResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMarketplaceProducts extends ListRecords
{
    protected static string $resource = MarketplaceProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
