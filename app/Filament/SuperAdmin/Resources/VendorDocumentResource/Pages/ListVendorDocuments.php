<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\VendorDocumentResource\Pages;

use App\Filament\SuperAdmin\Resources\VendorDocumentResource;
use App\Modules\Vendor\Enums\DocumentStatus;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListVendorDocuments extends ListRecords
{
    protected static string $resource = VendorDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $model = static::getResource()::getModel();

        return [
            'all' => Tab::make('All Documents'),

            'pending' => Tab::make('Pending Verification')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', DocumentStatus::Pending->value))
                ->badge($model::where('status', DocumentStatus::Pending->value)->count())
                ->badgeColor('warning'),

            'verified' => Tab::make('Verified')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', DocumentStatus::Verified->value))
                ->badge($model::where('status', DocumentStatus::Verified->value)->count())
                ->badgeColor('success'),

            'rejected' => Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', DocumentStatus::Rejected->value))
                ->badge($model::where('status', DocumentStatus::Rejected->value)->count())
                ->badgeColor('danger'),
        ];
    }
}
