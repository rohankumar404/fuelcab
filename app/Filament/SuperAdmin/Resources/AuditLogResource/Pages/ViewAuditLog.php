<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\AuditLogResource\Pages;

use App\Filament\SuperAdmin\Resources\AuditLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewAuditLog extends ViewRecord
{
    protected static string $resource = AuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return []; // Read-only — no edit or delete
    }
}
