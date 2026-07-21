<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\FaqResource\Pages;

use App\Filament\SuperAdmin\Resources\FaqResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFaq extends CreateRecord
{
    protected static string $resource = FaqResource::class;
}
