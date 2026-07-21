<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\MarketplaceOrderResource\Pages;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class MarketplaceOrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'MARKETPLACE';

    protected static ?string $navigationLabel = 'Marketplace Orders';

    protected static ?int $navigationSort = 5;

    protected static ?string $slug = 'marketplace-orders';

    /**
     * Always scope to marketplace channel only.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('channel', 'marketplace');
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order Details')->schema([
                Forms\Components\Placeholder::make('order_number')
                    ->label('Order Number')
                    ->content(fn ($record) => $record?->order_number ?? '—'),
                Forms\Components\Placeholder::make('customer')
                    ->content(fn ($record) => $record?->customer?->name ?? '—'),
                Forms\Components\Placeholder::make('vendor')
                    ->content(fn ($record) => $record?->vendor?->brand_name ?? '—'),
                Forms\Components\Select::make('status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucwords(str_replace('_', ' ', $s->value))]))
                    ->required(),
                Forms\Components\Textarea::make('delivery_notes')
                    ->nullable()
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->copyable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor.brand_name')
                    ->label('Vendor')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray'    => 'pending',
                        'warning' => 'accepted',
                        'info'    => 'assigned',
                        'primary' => 'out_for_delivery',
                        'success' => 'delivered',
                        'danger'  => 'cancelled',
                    ])
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', (string) ($state instanceof OrderStatus ? $state->value : $state)))),
                Tables\Columns\TextColumn::make('total_amount')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commission')
                    ->money('INR')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucwords(str_replace('_', ' ', $s->value))])),
                Tables\Filters\Filter::make('created_today')
                    ->label('Today')
                    ->query(fn ($query) => $query->whereDate('created_at', today())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // No delete — marketplace orders are financial records
            ])
            ->bulkActions([
                // No bulk delete
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketplaceOrders::route('/'),
            'edit'  => Pages\EditMarketplaceOrder::route('/{record}/edit'),
        ];
    }
}
