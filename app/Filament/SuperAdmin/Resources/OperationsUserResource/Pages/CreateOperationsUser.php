<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\OperationsUserResource\Pages;

use App\Filament\SuperAdmin\Resources\OperationsUserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateOperationsUser extends CreateRecord
{
    protected static string $resource = OperationsUserResource::class;
}
