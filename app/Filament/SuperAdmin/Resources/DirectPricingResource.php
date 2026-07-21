<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\DirectPricingResource\Pages;
use App\Modules\Fuel\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DirectPricingResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-rupee';

    protected static ?string $navigationGroup = 'DIRECT COMMERCE';

    protected static ?string $navigationLabel = 'Direct Pricing';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    // Slug to avoid route conflict with ProductResource
    protected static ?string $slug = 'direct-pricing';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Pricing Details')->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Product Name')
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
            ->defaultSort('category.name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('vendor.brand_name')
                    ->label('Vendor')
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('price_per_unit')
                    ->label('Price / Unit')
                    ->money('INR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_of_measure')
                    ->label('Unit')
                    ->sortable(),
                Tables\Columns\TextColumn::make('min_order_quantity')
                    ->label('Min Qty')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\IconColumn::make('ordering_enabled')
                    ->boolean()
                    ->label('Ordering On'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category_id')
                    ->label('Category')
                    ->relationship('category', 'name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('vendor_id')
                    ->label('Vendor')
                    ->relationship('vendor', 'brand_name')
                    ->searchable(),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active'),
                Tables\Filters\TernaryFilter::make('ordering_enabled')
                    ->label('Ordering Enabled'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Update Price')
                    ->icon('heroicon-o-pencil-square')
                    ->successNotificationTitle('Price updated successfully.')
                    ->mutateFormDataUsing(fn (array $data) => array_intersect_key($data, array_flip([
                        'price_per_unit', 'min_order_quantity', 'max_order_quantity',
                        'is_active', 'ordering_enabled',
                    ]))),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('activate')
                    ->label('Activate Selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        $records->each->update(['is_active' => true, 'ordering_enabled' => true]);
                        Notification::make()->title('Products activated.')->success()->send();
                    }),
                Tables\Actions\BulkAction::make('deactivate')
                    ->label('Deactivate Selected')
                    ->icon('heroicon-o-x-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        $records->each->update(['is_active' => false, 'ordering_enabled' => false]);
                        Notification::make()->title('Products deactivated.')->warning()->send();
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
