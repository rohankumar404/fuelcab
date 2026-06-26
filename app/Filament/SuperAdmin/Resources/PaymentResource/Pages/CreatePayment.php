<?php

namespace App\Filament\SuperAdmin\Resources\PaymentResource\Pages;

use App\Filament\SuperAdmin\Resources\PaymentResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
}
