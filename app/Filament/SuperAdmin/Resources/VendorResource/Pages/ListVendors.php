<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\VendorResource\Pages;

use App\Filament\SuperAdmin\Resources\VendorResource;
use App\Modules\Vendor\Enums\VendorStatus;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListVendors extends ListRecords
{
    protected static string $resource = VendorResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Vendors'),
            'applications' => Tab::make('Vendor Applications')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', VendorStatus::Pending->value))
                ->badge(fn () => static::getResource()::getModel()::where('status', VendorStatus::Pending->value)->count())
                ->badgeColor('warning'),
            'approved' => Tab::make('Approved Vendors')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', VendorStatus::Approved->value))
                ->badge(fn () => static::getResource()::getModel()::where('status', VendorStatus::Approved->value)->count())
                ->badgeColor('success'),
            'suspended' => Tab::make('Suspended Vendors')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', VendorStatus::Suspended->value))
                ->badge(fn () => static::getResource()::getModel()::where('status', VendorStatus::Suspended->value)->count())
                ->badgeColor('danger'),
        ];
    }
}
