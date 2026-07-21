<?php

declare(strict_types=1);

namespace App\Filament\Operations\Resources;

use App\Filament\Operations\Resources\OrderResource\Pages;
use App\Modules\Order\Enums\OrderStatus;
use App\Modules\Order\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'DIRECT COMMERCE';

    protected static ?string $navigationLabel = 'Direct Orders';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order Overview')->schema([
                Forms\Components\TextInput::make('order_number')
                    ->label('Order Number')
                    ->disabled(),
                Forms\Components\Select::make('status')
                    ->label('Order Status')
                    ->options(OrderStatus::class)
                    ->required(),
                Forms\Components\TextInput::make('channel')
                    ->label('Sales Channel')
                    ->disabled(),
                Forms\Components\TextInput::make('total_amount')
                    ->label('Total Amount (₹)')
                    ->numeric()
                    ->prefix('₹')
                    ->disabled(),
            ])->columns(2),

            Forms\Components\Section::make('Customer Support & Fulfillment Info')->schema([
                Forms\Components\Placeholder::make('customer_name')
                    ->label('Customer Name')
                    ->content(fn (?Order $record): string => $record?->customer?->name ?? 'N/A'),
                Forms\Components\Placeholder::make('customer_phone')
                    ->label('Customer Contact Phone')
                    ->content(fn (?Order $record): string => $record?->customer?->phone ?? 'N/A'),
                Forms\Components\Placeholder::make('customer_email')
                    ->label('Customer Email')
                    ->content(fn (?Order $record): string => $record?->customer?->email ?? 'N/A'),
                Forms\Components\Placeholder::make('delivery_address')
                    ->label('Delivery Address Line 1')
                    ->content(fn (?Order $record): string => $record?->deliveryAddress?->address_line_1 ?? 'N/A'),
                Forms\Components\Placeholder::make('delivery_city')
                    ->label('City')
                    ->content(fn (?Order $record): string => $record?->deliveryAddress?->city ?? 'N/A'),
                Forms\Components\Placeholder::make('delivery_state')
                    ->label('State')
                    ->content(fn (?Order $record): string => $record?->deliveryAddress?->state ?? 'N/A'),
                Forms\Components\Textarea::make('delivery_notes')
                    ->label('Special Delivery Notes')
                    ->columnSpanFull()
                    ->disabled(),
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
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('channel')
                    ->label('Channel')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total (₹)')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Ordered At')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Manage Fulfillment'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit'   => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
