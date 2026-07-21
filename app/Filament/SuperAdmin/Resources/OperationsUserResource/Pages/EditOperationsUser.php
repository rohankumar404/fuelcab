<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\OperationsUserResource\Pages;

use App\Filament\SuperAdmin\Resources\OperationsUserResource;
use Filament\Resources\Pages\EditRecord;

class EditOperationsUser extends EditRecord
{
    protected static string $resource = OperationsUserResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
