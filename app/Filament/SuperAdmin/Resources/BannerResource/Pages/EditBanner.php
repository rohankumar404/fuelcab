<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\BannerResource\Pages;

use App\Filament\SuperAdmin\Resources\BannerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditBanner extends EditRecord
{
    protected static string $resource = BannerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->requiresConfirmation(),
        ];
    }
}
