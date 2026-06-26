<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\OrderResource\Pages;
use App\Enums\OrderStatus;
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
    protected static ?string $navigationGroup = 'Operations';
    protected static ?int $navigationSort = 1;
    protected static ?string $recordTitleAttribute = 'order_number';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Order Context')->schema([
                Forms\Components\Select::make('customer_id')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('vendor_id')
                    ->relationship('vendor', 'business_name')
                    ->searchable()
                    ->required(),
                Forms\Components\Select::make('driver_id')
                    ->relationship('driver', 'name')
                    ->searchable()
                    ->nullable(),
                Forms\Components\Select::make('delivery_address_id')
                    ->relationship('deliveryAddress', 'address_line_1')
                    ->searchable()
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Pricing & Status')->schema([
                Forms\Components\Select::make('status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)]))
                    ->required(),
                Forms\Components\TextInput::make('subtotal_amount')
                    ->numeric()
                    ->prefix('₹')
                    ->required(),
                Forms\Components\TextInput::make('delivery_fee')
                    ->numeric()
                    ->prefix('₹')
                    ->default(0.00),
                Forms\Components\TextInput::make('tax_amount')
                    ->numeric()
                    ->prefix('₹')
                    ->default(0.00),
                Forms\Components\TextInput::make('total_amount')
                    ->numeric()
                    ->prefix('₹')
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Delivery Details')->schema([
                Forms\Components\DateTimePicker::make('scheduled_delivery_at')->nullable(),
                Forms\Components\DateTimePicker::make('delivered_at')->nullable(),
                Forms\Components\Textarea::make('delivery_notes')->nullable()->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('customer.name')->label('Customer')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('vendor.business_name')->label('Vendor')->sortable(),
                Tables\Columns\TextColumn::make('driver.name')->label('Driver')->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray'    => 'pending',
                        'warning' => 'confirmed',
                        'info'    => 'en_route',
                        'primary' => 'arrived',
                        'success' => 'completed',
                        'danger'  => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('total_amount')->money('INR')->sortable(),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(OrderStatus::cases())->mapWithKeys(fn ($s) => [$s->value => ucfirst($s->value)])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit'  => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
