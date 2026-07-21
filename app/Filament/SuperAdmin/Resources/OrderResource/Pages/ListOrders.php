<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\OrderResource\Pages;

use App\Filament\SuperAdmin\Resources\OrderResource;
use App\Modules\Order\Enums\OrderStatus;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        $model = static::getResource()::getModel();

        return [
            'all' => Tab::make('All Orders')
                ->badge($model::count()),

            'direct' => Tab::make('Direct Commerce')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('channel', 'direct'))
                ->badge($model::where('channel', 'direct')->count())
                ->badgeColor('info'),

            'marketplace' => Tab::make('Marketplace')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('channel', 'marketplace'))
                ->badge($model::where('channel', 'marketplace')->count())
                ->badgeColor('success'),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::Pending->value))
                ->badge($model::where('status', OrderStatus::Pending->value)->count())
                ->badgeColor('warning'),

            'delivered' => Tab::make('Delivered')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::Delivered->value))
                ->badgeColor('success'),

            'cancelled' => Tab::make('Cancelled')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('status', OrderStatus::Cancelled->value))
                ->badgeColor('danger'),
        ];
    }
}
