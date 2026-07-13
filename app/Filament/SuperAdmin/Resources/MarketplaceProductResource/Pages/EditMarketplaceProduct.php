<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\MarketplaceProductResource\Pages;

use App\Filament\SuperAdmin\Resources\MarketplaceProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMarketplaceProduct extends EditRecord
{
    protected static string $resource = MarketplaceProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
