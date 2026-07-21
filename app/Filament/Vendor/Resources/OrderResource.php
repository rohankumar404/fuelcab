<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\OrderResource\Pages;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'Orders';

    protected static ?string $navigationLabel = 'Orders';

    protected static ?int $navigationSort = 4;

    /**
     * SECURITY: Always scope to the authenticated vendor's orders.
     * vendor_id is resolved server-side — never trusted from frontend.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(auth()->check() && auth()->user()->vendor_id, function ($query) {
                $query->where('vendor_id', auth()->user()->vendor_id);
            })
            ->with(['customer', 'deliveryAddress', 'items']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order Details')->schema([
                Forms\Components\TextInput::make('order_number')
                    ->label('Order Number')
                    ->disabled(),

                Forms\Components\Select::make('status')
                    ->label('Fulfillment Status')
                    ->options([
                        OrderStatus::Accepted->value      => 'Accepted',
                        OrderStatus::Assigned->value      => 'Assigned / Processing',
                        OrderStatus::OutForDelivery->value => 'Out for Delivery',
                        OrderStatus::Delivered->value     => 'Delivered',
                        OrderStatus::Cancelled->value     => 'Cancelled',
                    ])
                    ->required(),

                Forms\Components\TextInput::make('total_amount')
                    ->label('Total Amount (₹)')
                    ->prefix('₹')
                    ->disabled(),

                Forms\Components\TextInput::make('channel')
                    ->label('Channel')
                    ->disabled(),
            ])->columns(2),

            Forms\Components\Section::make('Customer Information')->schema([
                Forms\Components\Placeholder::make('customer_name')
                    ->label('Customer')
                    ->content(fn (?Order $record): string => $record?->customer?->name ?? '—'),

                Forms\Components\Placeholder::make('customer_phone')
                    ->label('Contact Phone')
                    ->content(fn (?Order $record): string => $record?->customer?->phone ?? '—'),

                Forms\Components\Placeholder::make('delivery_address')
                    ->label('Delivery Address')
                    ->content(fn (?Order $record): string => implode(', ', array_filter([
                        $record?->deliveryAddress?->address_line_1,
                        $record?->deliveryAddress?->city,
                        $record?->deliveryAddress?->state,
                    ])) ?: '—'),

                Forms\Components\Textarea::make('delivery_notes')
                    ->label('Delivery Notes')
                    ->disabled()
                    ->columnSpanFull(),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Order #')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state?->value ?? $state) {
                        'pending'         => 'warning',
                        'accepted'        => 'info',
                        'assigned'        => 'primary',
                        'out_for_delivery' => 'primary',
                        'delivered'       => 'success',
                        'cancelled'       => 'danger',
                        default           => 'gray',
                    })
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total (₹)')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('channel')
                    ->label('Channel')
                    ->badge()
                    ->color('gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Placed At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'          => 'Pending',
                        'accepted'         => 'Accepted',
                        'assigned'         => 'Assigned',
                        'out_for_delivery' => 'Out for Delivery',
                        'delivered'        => 'Delivered',
                        'cancelled'        => 'Cancelled',
                    ]),
            ])
            ->actions([
                // Accept: only for PENDING orders
                Tables\Actions\Action::make('accept')
                    ->label('Accept')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::Pending)
                    ->action(function (Order $record): void {
                        // SECURITY: Re-verify vendor ownership
                        if ($record->vendor_id !== auth()->user()->vendor_id) {
                            Notification::make()->title('Unauthorized.')->danger()->send();
                            return;
                        }

                        if (! $record->status->canTransitionTo(OrderStatus::Accepted)) {
                            Notification::make()->title('Cannot accept this order.')->warning()->send();
                            return;
                        }

                        $record->update(['status' => OrderStatus::Accepted]);

                        Notification::make()
                            ->title('Order accepted.')
                            ->success()
                            ->send();
                    }),

                // Process: move from accepted → assigned
                Tables\Actions\Action::make('process')
                    ->label('Mark Processing')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->color('info')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::Accepted)
                    ->action(function (Order $record): void {
                        if ($record->vendor_id !== auth()->user()->vendor_id) {
                            Notification::make()->title('Unauthorized.')->danger()->send();
                            return;
                        }

                        $record->update(['status' => OrderStatus::Assigned]);

                        Notification::make()
                            ->title('Order marked as processing.')
                            ->success()
                            ->send();
                    }),

                // Out for delivery
                Tables\Actions\Action::make('dispatch')
                    ->label('Mark Dispatched')
                    ->icon('heroicon-o-truck')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::Assigned)
                    ->action(function (Order $record): void {
                        if ($record->vendor_id !== auth()->user()->vendor_id) {
                            Notification::make()->title('Unauthorized.')->danger()->send();
                            return;
                        }

                        $record->update(['status' => OrderStatus::OutForDelivery]);

                        Notification::make()
                            ->title('Order marked as out for delivery.')
                            ->success()
                            ->send();
                    }),

                // Delivered
                Tables\Actions\Action::make('delivered')
                    ->label('Mark Delivered')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Order $record): bool => $record->status === OrderStatus::OutForDelivery)
                    ->action(function (Order $record): void {
                        if ($record->vendor_id !== auth()->user()->vendor_id) {
                            Notification::make()->title('Unauthorized.')->danger()->send();
                            return;
                        }

                        $record->update(['status' => OrderStatus::Delivered]);

                        Notification::make()
                            ->title('Order marked as delivered.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make()
                    ->label('View'),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'view'   => Pages\ViewOrder::route('/{record}'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
