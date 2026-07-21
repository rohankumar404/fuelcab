<?php

declare(strict_types=1);

namespace App\Filament\Operations\Resources;

use App\Filament\Operations\Resources\DirectPricingResource\Pages;
use App\Modules\Fuel\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DirectPricingResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-rupee';

    protected static ?string $navigationGroup = 'DIRECT COMMERCE';

    protected static ?string $navigationLabel = 'Direct Pricing';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'direct-pricing';

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('vendor', function ($q) {
                $q->where('is_first_party', true);
            });
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Price & Ordering Controls')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Product')
                    ->disabled(),
                Forms\Components\TextInput::make('sku')
                    ->label('SKU')
                    ->disabled(),
                Forms\Components\TextInput::make('price_per_unit')
                    ->label('Price Per Unit')
                    ->numeric()
                    ->prefix('₹')
                    ->required(),
                Forms\Components\TextInput::make('min_order_quantity')
                    ->label('Minimum Order Qty')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('max_order_quantity')
                    ->label('Maximum Order Qty')
                    ->numeric()
                    ->nullable(),
                Forms\Components\Toggle::make('is_active')
                    ->label('Active')
                    ->default(true),
                Forms\Components\Toggle::make('ordering_enabled')
                    ->label('Ordering Enabled')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price_per_unit')
                    ->label('Price / Unit')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_of_measure')
                    ->label('Unit')
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_order_quantity')
                    ->label('Min Order Qty')
                    ->sortable(),
                Tables\Columns\IconColumn::make('ordering_enabled')
                    ->boolean()
                    ->label('Ordering Enabled'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('ordering_enabled')
                    ->label('Ordering Enabled Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Update Price')
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('enable_ordering')
                    ->label('Enable Ordering')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->action(function ($records): void {
                        $records->each->update(['ordering_enabled' => true]);
                        Notification::make()->title('Ordering enabled for selected products.')->success()->send();
                    }),
                Tables\Actions\BulkAction::make('disable_ordering')
                    ->label('Disable Ordering')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->action(function ($records): void {
                        $records->each->update(['ordering_enabled' => false]);
                        Notification::make()->title('Ordering disabled for selected products.')->warning()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListDirectPricing::route('/'),
            'edit'  => Pages\EditDirectPricing::route('/{record}/edit'),
        ];
    }
}
