<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\PaymentResource\Pages;
use App\Modules\Payment\Models\Payment;
use App\Modules\Order\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;
    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'FINANCE';
    protected static ?string $navigationLabel = 'Payments';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Payment Details')
                ->schema([
                    Forms\Components\Select::make('order_id')
                        ->label('Order')
                        ->options(Order::pluck('order_number', 'id'))
                        ->searchable()
                        ->required(),
                    Forms\Components\TextInput::make('payment_gateway')
                        ->required()
                        ->maxLength(100),
                    Forms\Components\TextInput::make('gateway_transaction_id')
                        ->label('Transaction ID')
                        ->maxLength(255)
                        ->nullable(),
                    Forms\Components\TextInput::make('amount')
                        ->numeric()
                        ->prefix('₹')
                        ->required(),
                    Forms\Components\TextInput::make('currency')
                        ->default('INR')
                        ->required(),
                    Forms\Components\Select::make('status')
                        ->options([
                            'pending' => 'Pending',
                            'success' => 'Success',
                            'failed' => 'Failed',
                            'refunded' => 'Refunded'
                        ])
                        ->default('pending')
                        ->required(),
                    Forms\Components\DateTimePicker::make('paid_at')
                        ->nullable(),
                    Forms\Components\Textarea::make('error_message')
                        ->columnSpanFull()
                        ->nullable(),
                ])->columns(2)
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('gateway_transaction_id')
                    ->label('Transaction ID')
                    ->searchable()
                    ->copyable(),
                Tables\Columns\TextColumn::make('order.order_number')
                    ->label('Order')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('amount')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('payment_gateway')
                    ->label('Gateway')
                    ->badge(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'success',
                        'danger' => 'failed',
                        'gray' => 'refunded',
                    ])
                    ->sortable(),
                Tables\Columns\TextColumn::make('paid_at')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'success' => 'Success',
                        'failed' => 'Failed',
                        'refunded' => 'Refunded',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                // No DeleteAction — payments are financial records
            ])
            ->bulkActions([
                // No DeleteBulkAction — payments are financial records
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
