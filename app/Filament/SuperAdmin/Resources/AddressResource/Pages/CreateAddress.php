<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\AddressResource\Pages;

use App\Filament\SuperAdmin\Resources\AddressResource;
use Filament\Resources\Pages\CreateRecord;

class CreateAddress extends CreateRecord
{
    protected static string $resource = AddressResource::class;
}
