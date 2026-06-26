<?php

namespace App\Filament\SuperAdmin\Resources\CategoryResource\Pages;

use App\Filament\SuperAdmin\Resources\CategoryResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateCategory extends CreateRecord
{
    protected static string $resource = CategoryResource::class;
}
