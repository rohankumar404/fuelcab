<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\VendorListingResource\Pages;

use App\Enums\ListingStatus;
use App\Filament\SuperAdmin\Resources\VendorListingResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListVendorListings extends ListRecords
{
    protected static string $resource = VendorListingResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Listings'),
            'approvals' => Tab::make('Listing Approvals')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_status', ListingStatus::PendingApproval->value))
                ->badge(fn () => static::getResource()::getModel()::where('approval_status', ListingStatus::PendingApproval->value)->count())
                ->badgeColor('warning'),
            'approved' => Tab::make('Approved Listings')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_status', ListingStatus::Approved->value))
                ->badge(fn () => static::getResource()::getModel()::where('approval_status', ListingStatus::Approved->value)->count())
                ->badgeColor('success'),
            'suspended' => Tab::make('Suspended Listings')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('approval_status', ListingStatus::Suspended->value))
                ->badge(fn () => static::getResource()::getModel()::where('approval_status', ListingStatus::Suspended->value)->count())
                ->badgeColor('danger'),
        ];
    }
}
