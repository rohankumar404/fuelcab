<?php

declare(strict_types=1);

namespace App\Filament\Operations\Resources;

use App\Filament\Operations\Resources\VehicleResource\Pages;
use App\Modules\Vehicle\Models\Vehicle;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VehicleResource extends Resource
{
    protected static ?string $model = Vehicle::class;

    protected static ?string $navigationIcon = 'heroicon-o-truck';

    protected static ?string $navigationGroup = 'DELIVERY OPERATIONS';

    protected static ?string $navigationLabel = 'Vehicles';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Vehicle Fleet Details')->schema([
                Forms\Components\Select::make('vendor_id')
                    ->label('Depot / Vendor')
                    ->relationship('vendor', 'brand_name')
                    ->required(),
                Forms\Components\TextInput::make('registration_number')
                    ->label('Registration Number')
                    ->required()
                    ->maxLength(50),
                Forms\Components\TextInput::make('make')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('model')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('year')
                    ->numeric()
                    ->required(),
                Forms\Components\TextInput::make('capacity_liters')
                    ->label('Capacity (Liters)')
                    ->numeric()
                    ->required(),
                Forms\Components\Select::make('fuel_type')
                    ->options([
                        'diesel' => 'Diesel',
                        'cng'    => 'CNG',
                        'lpg'    => 'LPG',
                        'electric' => 'Electric',
                    ])
                    ->default('diesel')
                    ->required(),
                Forms\Components\Select::make('status')
                    ->options([
                        'active'      => 'Active',
                        'maintenance' => 'In Maintenance',
                        'retired'     => 'Retired',
                    ])
                    ->default('active')
                    ->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('registration_number')
                    ->label('Reg. Number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('make')
                    ->sortable(),
                Tables\Columns\TextColumn::make('model')
                    ->sortable(),
                Tables\Columns\TextColumn::make('capacity_liters')
                    ->label('Capacity (L)')
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('fuel_type')
                    ->colors(['info' => 'diesel', 'success' => 'cng', 'warning' => 'lpg']),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success' => 'active', 'warning' => 'maintenance', 'danger' => 'retired']),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active'      => 'Active',
                        'maintenance' => 'In Maintenance',
                        'retired'     => 'Retired',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVehicles::route('/'),
            'create' => Pages\CreateVehicle::route('/create'),
            'edit'   => Pages\EditVehicle::route('/{record}/edit'),
        ];
    }
}
