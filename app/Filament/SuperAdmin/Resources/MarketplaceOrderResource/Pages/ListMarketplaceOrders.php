<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\MarketplaceOrderResource\Pages;

use App\Filament\SuperAdmin\Resources\MarketplaceOrderResource;
use App\Modules\Order\Enums\OrderStatus;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListMarketplaceOrders extends ListRecords
{
    protected static string $resource = MarketplaceOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        $model = static::getResource()::getModel();
        $base  = $model::where('channel', 'marketplace');

        return [
            'all' => Tab::make('All')
                ->badge($base->clone()->count()),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', OrderStatus::Pending->value))
                ->badge($base->clone()->where('status', OrderStatus::Pending->value)->count())
                ->badgeColor('warning'),

            'active' => Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $q) => $q->whereIn('status', [
                    OrderStatus::Accepted->value,
                    OrderStatus::Assigned->value,
                    OrderStatus::OutForDelivery->value,
                ]))
                ->badgeColor('info'),

            'delivered' => Tab::make('Delivered')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', OrderStatus::Delivered->value))
                ->badgeColor('success'),

            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', OrderStatus::Cancelled->value))
                ->badgeColor('danger'),
        ];
    }
}
