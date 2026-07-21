<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources\QuoteRequestResource\Pages;

use App\Filament\SuperAdmin\Resources\QuoteRequestResource;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListQuoteRequests extends ListRecords
{
    protected static string $resource = QuoteRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make('All Requests'),

            'pending' => Tab::make('Pending')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'pending'))
                ->badge(fn () => static::getResource()::getModel()::where('status', 'pending')->count())
                ->badgeColor('warning'),

            'responded' => Tab::make('Responded')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'responded'))
                ->badge(fn () => static::getResource()::getModel()::where('status', 'responded')->count())
                ->badgeColor('success'),

            'closed' => Tab::make('Closed')
                ->modifyQueryUsing(fn (Builder $q) => $q->where('status', 'closed'))
                ->badgeColor('gray'),
        ];
    }
}
