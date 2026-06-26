<?php

namespace App\Filament\Operations\Resources\OrderResource\Pages;

use App\Filament\Operations\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;
}
