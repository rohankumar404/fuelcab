<?php

namespace App\Filament\Operations\Resources\PaymentResource\Pages;

use App\Filament\Operations\Resources\PaymentResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePayment extends CreateRecord
{
    protected static string $resource = PaymentResource::class;
}
