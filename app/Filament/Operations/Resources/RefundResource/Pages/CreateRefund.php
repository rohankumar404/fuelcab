<?php

namespace App\Filament\Operations\Resources\RefundResource\Pages;

use App\Filament\Operations\Resources\RefundResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateRefund extends CreateRecord
{
    protected static string $resource = RefundResource::class;
}
