<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\MarketplaceOrderResource\Pages;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

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

            Forms\Components\Section::make('Pricing Summary')->schema([
                Forms\Components\Placeholder::make('subtotal_amount')
                    ->content(fn ($record) => $record?->subtotal_amount ? '₹'.number_format((float) $record->subtotal_amount, 2) : '—'),
                Forms\Components\Placeholder::make('delivery_fee')
                    ->content(fn ($record) => $record?->delivery_fee ? '₹'.number_format((float) $record->delivery_fee, 2) : '—'),
                Forms\Components\Placeholder::make('tax_amount')
                    ->content(fn ($record) => $record?->tax_amount ? '₹'.number_format((float) $record->tax_amount, 2) : '—'),
                Forms\Components\Placeholder::make('total_amount')
                    ->content(fn ($record) => $record?->total_amount ? '₹'.number_format((float) $record->total_amount, 2) : '—'),
            ])->columns(2),

            Forms\Components\Section::make('Order Items')->schema([
                Forms\Components\Repeater::make('items')
                    ->relationship('items')
                    ->schema([
                        Forms\Components\TextInput::make('product_name_snapshot')
                            ->label('Product Name')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('quantity')
                            ->numeric()
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('price_per_unit')
                            ->label('Price per Unit')
                            ->numeric()
                            ->prefix('₹')
                            ->disabled()
                            ->dehydrated(false),
                        Forms\Components\TextInput::make('total_price')
                            ->label('Total Price')
                            ->numeric()
                            ->prefix('₹')
                            ->disabled()
                            ->dehydrated(false),
                    ])
                    ->columns(4)
                    ->addable(false)
                    ->deletable(false)
                    ->reorderable(false),
            ]),
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
                Tables\Columns\TextColumn::make('status')
                    ->badge()->color(fn ($state) => match ($state instanceof \BackedEnum ? $state->value : $state) {
                        'pending' => 'gray',
                        'accepted' => 'warning',
                        'assigned' => 'info',
                        'out_for_delivery' => 'primary',
                        'delivered' => 'success',
                        'cancelled' => 'danger',
                        default => 'gray',
                    })
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
                Tables\Actions\BulkAction::make('updateStatus')
                    ->label('Update Status')
                    ->icon('heroicon-o-check-circle')
                    ->requiresConfirmation()
                    ->form([
                        Forms\Components\Select::make('status')
                            ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucwords(str_replace('_', ' ', $s->value))]))
                            ->required(),
                    ])
                    ->action(function (Collection $records, array $data): void {
                        foreach ($records as $record) {
                            $record->update(['status' => $data['status']]);
                        }
                        Notification::make()
                            ->title('Orders status updated successfully.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMarketplaceOrders::route('/'),
            'edit' => Pages\EditMarketplaceOrder::route('/{record}/edit'),
        ];
    }
}
