<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Enums\UserRole;
use App\Filament\SuperAdmin\Resources\OperationsUserResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OperationsUserResource extends Resource
{
    protected static ?string $model = \App\Models\User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user-circle';

    protected static ?string $navigationGroup = 'SYSTEM';

    protected static ?string $navigationLabel = 'Operations Users';

    protected static ?int $navigationSort = 1;

    protected static ?string $slug = 'operations-users';

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Scope to staff roles only — super_admin and operations_team.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereIn('role_type', [
            UserRole::SuperAdmin->value,
            UserRole::OperationsTeam->value,
        ]);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Staff Account Details')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\TextInput::make('phone')
                    ->tel()
                    ->nullable(),

                Forms\Components\TextInput::make('password')
                    ->password()
                    ->minLength(8)
                    ->required(fn ($context) => $context === 'create')
                    ->dehydrated(fn ($state) => filled($state))
                    ->helperText('Leave blank to keep the current password.'),
            ])->columns(2),

            Forms\Components\Section::make('Role & Access')->schema([
                Forms\Components\Select::make('role_type')
                    ->options([
                        UserRole::SuperAdmin->value     => UserRole::SuperAdmin->label(),
                        UserRole::OperationsTeam->value => UserRole::OperationsTeam->label(),
                    ])
                    ->required(),

                Forms\Components\Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'])
                    ->default('active')
                    ->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->toggleable(),

                Tables\Columns\BadgeColumn::make('role_type')
                    ->label('Role')
                    ->colors([
                        'danger'  => UserRole::SuperAdmin->value,
                        'info'    => UserRole::OperationsTeam->value,
                    ])
                    ->formatStateUsing(fn ($state) => is_string($state) ? $state : $state?->value),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success' => 'active', 'warning' => 'inactive', 'danger' => 'suspended']),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->boolean()
                    ->label('Email Verified'),

                Tables\Columns\TextColumn::make('last_login_at')
                    ->label('Last Login')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role_type')
                    ->label('Role')
                    ->options([
                        UserRole::SuperAdmin->value     => UserRole::SuperAdmin->label(),
                        UserRole::OperationsTeam->value => UserRole::OperationsTeam->label(),
                    ]),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended']),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'active')
                    ->action(function ($record): void {
                        $record->update(['status' => 'suspended']);
                        Notification::make()->title('User suspended.')->warning()->send();
                    }),
                Tables\Actions\Action::make('reactivate')
                    ->label('Reactivate')
                    ->icon('heroicon-o-arrow-path')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => $record->status === 'suspended')
                    ->action(function ($record): void {
                        $record->update(['status' => 'active']);
                        Notification::make()->title('User reactivated.')->success()->send();
                    }),
                // No delete — operations users may have audit history
            ])
            ->bulkActions([
                // No bulk delete for staff accounts
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListOperationsUsers::route('/'),
            'create' => Pages\CreateOperationsUser::route('/create'),
            'edit'   => Pages\EditOperationsUser::route('/{record}/edit'),
        ];
    }
}
