<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\FaqResource\Pages;

use App\Filament\SuperAdmin\Resources\FaqResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFaqs extends ListRecords
{
    protected static string $resource = FaqResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
