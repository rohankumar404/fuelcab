<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources\QuoteRequestResource\Pages;

use App\Filament\Vendor\Resources\QuoteRequestResource;
use Filament\Resources\Pages\ViewRecord;

class ViewQuoteRequest extends ViewRecord
{
    protected static string $resource = QuoteRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
