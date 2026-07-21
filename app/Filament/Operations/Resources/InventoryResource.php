<?php

declare(strict_types=1);

namespace App\Filament\Operations\Resources;

use App\Filament\Operations\Resources\InventoryResource\Pages;
use App\Modules\Fuel\Models\FuelInventory;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class InventoryResource extends Resource
{
    protected static ?string $model = FuelInventory::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'DIRECT COMMERCE';

    protected static ?string $navigationLabel = 'Direct Inventory';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Stock Management')->schema([
                Forms\Components\Select::make('product_id')
                    ->label('Product')
                    ->relationship('product', 'name')
                    ->required(),
                Forms\Components\TextInput::make('quantity_available')
                    ->label('Quantity Available')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('quantity_reserved')
                    ->label('Quantity Reserved')
                    ->numeric()
                    ->default(0),
                Forms\Components\TextInput::make('reorder_threshold')
                    ->label('Minimum Reorder Threshold')
                    ->numeric()
                    ->default(100),
                Forms\Components\TextInput::make('location_name')
                    ->label('Depot / Terminal Location')
                    ->nullable(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_available')
                    ->label('Available Stock')
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity_reserved')
                    ->label('Reserved')
                    ->sortable(),
                Tables\Columns\TextColumn::make('product.unit_of_measure')
                    ->label('Unit'),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Update Stock')
                    ->icon('heroicon-o-pencil-square'),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListInventories::route('/'),
            'create' => Pages\CreateInventory::route('/create'),
            'edit'   => Pages\EditInventory::route('/{record}/edit'),
        ];
    }
}
