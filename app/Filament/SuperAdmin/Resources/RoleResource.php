<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Enums\UserRole;
use App\Filament\SuperAdmin\Resources\RoleResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

/**
 * RoleResource — manages platform roles and their associated permissions.
 *
 * Since FuelCab uses a native UserRole enum (not spatie/permission),
 * this resource provides a read-only reference view of all roles
 * and the users currently assigned to each role.
 */
class RoleResource extends Resource
{
    // We bind to User model and group/display by role
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-shield-check';

    protected static ?string $navigationGroup = 'SYSTEM';

    protected static ?string $navigationLabel = 'Roles & Permissions';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'roles-permissions';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('User Role Assignment')->schema([
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('email')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true),

                Forms\Components\Select::make('role_type')
                    ->label('Platform Role')
                    ->options(collect(UserRole::cases())->mapWithKeys(fn ($r) => [$r->value => $r->label()]))
                    ->required()
                    ->helperText('Role determines which panel and capabilities this user has access to.'),

                Forms\Components\Select::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'])
                    ->default('active')
                    ->required(),
            ])->columns(2),

            Forms\Components\Section::make('Role Capabilities Reference')->schema([
                Forms\Components\Placeholder::make('role_info')
                    ->label('')
                    ->content(function ($record) {
                        if (! $record) {
                            return 'Select a role to see its capabilities.';
                        }
                        $role = $record->role_type instanceof UserRole
                            ? $record->role_type
                            : UserRole::tryFrom($record->role_type);

                        if (! $role) {
                            return '—';
                        }

                        $abilities = implode(', ', $role->sanctumAbilities());

                        return "Role: {$role->label()} | Sanctum Abilities: {$abilities}";
                    })
                    ->columnSpanFull(),
            ])->collapsed(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('role_type')
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('email')
                    ->searchable()
                    ->copyable(),

                Tables\Columns\BadgeColumn::make('role_type')
                    ->label('Role')
                    ->colors([
                        'danger'  => UserRole::SuperAdmin->value,
                        'info'    => UserRole::OperationsTeam->value,
                        'success' => UserRole::VendorAdmin->value,
                        'warning' => UserRole::VendorStaff->value,
                        'gray'    => UserRole::Driver->value,
                        'primary' => UserRole::Customer->value,
                    ])
                    ->formatStateUsing(fn ($state) => is_string($state) ? $state : $state?->value),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success' => 'active', 'warning' => 'inactive', 'danger' => 'suspended']),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('role_type')
                    ->label('Role')
                    ->options(collect(UserRole::cases())->mapWithKeys(fn ($r) => [$r->value => $r->label()])),
                Tables\Filters\SelectFilter::make('status')
                    ->options(['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended']),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('Change Role'),
            ])
            ->bulkActions([
                // No bulk delete for users in this context
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListRoles::route('/'),
            'edit'  => Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
