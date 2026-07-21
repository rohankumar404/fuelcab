<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\FaqResource\Pages;

use App\Filament\SuperAdmin\Resources\FaqResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFaq extends EditRecord
{
    protected static string $resource = FaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()->requiresConfirmation(),
        ];
    }
}
