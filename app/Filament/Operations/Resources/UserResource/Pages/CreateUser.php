<?php

namespace App\Filament\Operations\Resources\UserResource\Pages;

use App\Filament\Operations\Resources\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;
}
