<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\OperationsUserResource\Pages;

use App\Filament\SuperAdmin\Resources\OperationsUserResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOperationsUsers extends ListRecords
{
    protected static string $resource = OperationsUserResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make()->label('Add Staff User'),
        ];
    }
}
