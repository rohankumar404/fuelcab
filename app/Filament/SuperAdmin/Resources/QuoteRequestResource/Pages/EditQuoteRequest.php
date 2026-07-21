<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\QuoteRequestResource\Pages;

use App\Filament\SuperAdmin\Resources\QuoteRequestResource;
use Filament\Resources\Pages\EditRecord;

class EditQuoteRequest extends EditRecord
{
    protected static string $resource = QuoteRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
