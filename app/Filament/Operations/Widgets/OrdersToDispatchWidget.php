<?php

declare(strict_types=1);

namespace App\Filament\Operations\Widgets;

use App\Modules\Order\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class OrdersToDispatchWidget extends BaseWidget
{
    protected static ?string $heading = 'Orders to Dispatch';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Order::query()
                    ->where('status', 'confirmed')
                    ->whereNull('driver_id')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable(),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer'),
                Tables\Columns\TextColumn::make('vendor.business_name')->label('Vendor'),
                Tables\Columns\TextColumn::make('total_amount')->money('INR'),
                Tables\Columns\TextColumn::make('created_at')->since()->label('Waiting'),
            ])
            ->actions([
                Tables\Actions\Action::make('assign_driver')
                    ->label('Assign Driver')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->url(fn ($record) => route('filament.operations.resources.orders.edit', $record)),
            ]);
    }
}
