<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\AddressResource\Pages;
use App\Models\Address;
use App\Models\User;
use App\Models\Company;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AddressResource extends Resource
{
    protected static ?string $model = Address::class;

    protected static ?string $navigationIcon = 'heroicon-o-map-pin';

    protected static ?string $navigationGroup = 'CUSTOMERS';

    protected static ?string $navigationLabel = 'Addresses';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Address Details')->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User')
                    ->options(User::pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),

                Forms\Components\Select::make('company_id')
                    ->label('Company')
                    ->options(Company::pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),

                Forms\Components\TextInput::make('address_line_1')
                    ->label('Address Line 1')
                    ->required()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('address_line_2')
                    ->label('Address Line 2')
                    ->nullable()
                    ->columnSpanFull(),

                Forms\Components\TextInput::make('city')
                    ->required(),

                Forms\Components\TextInput::make('state')
                    ->required(),

                Forms\Components\TextInput::make('postal_code')
                    ->label('Postal Code')
                    ->required(),

                Forms\Components\TextInput::make('country')
                    ->default('India')
                    ->required(),

                Forms\Components\TextInput::make('latitude')
                    ->numeric()
                    ->nullable(),

                Forms\Components\TextInput::make('longitude')
                    ->numeric()
                    ->nullable(),

                Forms\Components\Toggle::make('is_primary')
                    ->label('Primary Address')
                    ->default(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('User')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('company.name')
                    ->label('Company')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('address_line_1')
                    ->label('Address')
                    ->searchable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('city')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('state')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('postal_code')
                    ->label('Pincode')
                    ->searchable(),

                Tables\Columns\IconColumn::make('is_primary')
                    ->label('Primary')
                    ->boolean(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('city')
                    ->options(
                        Address::distinct()->pluck('city', 'city')->filter()->toArray()
                    ),
                Tables\Filters\SelectFilter::make('state')
                    ->options(
                        Address::distinct()->pluck('state', 'state')->filter()->toArray()
                    ),
                Tables\Filters\TernaryFilter::make('is_primary')
                    ->label('Primary Addresses Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // No delete — addresses are referenced by orders
            ])
            ->bulkActions([
                // No bulk delete — addresses are referenced by order delivery records
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListAddresses::route('/'),
            'create' => Pages\CreateAddress::route('/create'),
            'edit'   => Pages\EditAddress::route('/{record}/edit'),
        ];
    }
}
