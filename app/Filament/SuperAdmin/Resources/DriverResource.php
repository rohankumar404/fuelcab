<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\DriverResource\Pages;
use App\Enums\DriverStatus;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class DriverResource extends Resource
{
    protected static ?string $model = \App\Modules\Driver\Models\Driver::class;
    protected static ?string $navigationIcon = 'heroicon-o-truck';
    protected static ?string $navigationGroup = 'DIRECT COMMERCE';
    protected static ?string $navigationLabel = 'Delivery Operations Drivers';
    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Driver Info')->schema([
                Forms\Components\Select::make('user_id')
                    ->label('User Account')
                    ->relationship('user', 'name')
                    ->searchable()->required(),
                Forms\Components\Select::make('status')
                    ->options(['offline' => 'Offline', 'available' => 'Available', 'on_trip' => 'On Trip', 'suspended' => 'Suspended'])
                    ->default('offline')->required(),
                Forms\Components\TextInput::make('license_number')->maxLength(100)->nullable(),
                Forms\Components\Toggle::make('is_approved')->label('Approved')->default(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('Name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('user.email')->label('Email')->toggleable(),
                Tables\Columns\TextColumn::make('vendor.business_name')->label('Vendor'),
                Tables\Columns\TextColumn::make('license_number')->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['gray' => 'offline', 'success' => 'available', 'info' => 'on_trip', 'danger' => 'suspended']),
                Tables\Columns\IconColumn::make('is_approved')->boolean()->label('Approved'),
                Tables\Columns\TextColumn::make('created_at')->dateTime()->sortable()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(['offline' => 'Offline', 'available' => 'Available', 'on_trip' => 'On Trip', 'suspended' => 'Suspended']),
                Tables\Filters\TernaryFilter::make('is_approved')->label('Approval Status'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(fn ($record) => $record->update(['is_approved' => true]))
                    ->visible(fn ($record) => ! $record->is_approved),
            ])
            ->bulkActions([Tables\Actions\BulkActionGroup::make([Tables\Actions\DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListDrivers::route('/'),
            'create' => Pages\CreateDriver::route('/create'),
            'edit'   => Pages\EditDriver::route('/{record}/edit'),
        ];
    }
}
