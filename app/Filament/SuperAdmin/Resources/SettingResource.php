<?php

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\SettingResource\Pages;
use App\Models\Company;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;

class SettingResource extends Resource
{
    protected static ?string $model = Setting::class;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'SYSTEM';

    protected static ?string $navigationLabel = 'Settings';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Setting Configurations')
                    ->schema([
                        Forms\Components\Select::make('company_id')
                            ->label('Company')
                            ->options(Company::pluck('name', 'id'))
                            ->searchable()
                            ->preload()
                            ->nullable()
                            ->helperText('Leave blank for system-wide configuration.'),

                        Forms\Components\TextInput::make('key')
                            ->required()
                            ->maxLength(100)
                            ->unique(ignoreRecord: true, modifyRuleUsing: function (Unique $rule, Forms\Get $get) {
                                return $rule->where('company_id', $get('company_id'));
                            })
                            ->helperText('Unique settings key (e.g. tax_rate, commission_rate).'),

                        Forms\Components\Select::make('cast_type')
                            ->label('Data Type')
                            ->options([
                                'string' => 'String',
                                'integer' => 'Integer',
                                'float' => 'Float',
                                'boolean' => 'Boolean',
                                'json' => 'JSON / Array',
                            ])
                            ->default('string')
                            ->required()
                            ->live(),

                        Forms\Components\Textarea::make('value')
                            ->nullable()
                            ->helperText(fn (Forms\Get $get) => match ($get('cast_type')) {
                                'boolean' => 'Enter "true", "false", "1", or "0".',
                                'json' => 'Enter a valid JSON string.',
                                'integer', 'float' => 'Enter a numeric value.',
                                default => 'Enter string value.',
                            })
                            ->columnSpanFull()
                            ->rows(4),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('company.name')
                    ->label('Company')
                    ->placeholder('System-wide')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('key')
                    ->sortable()
                    ->searchable(),

                Tables\Columns\TextColumn::make('value')
                    ->limit(50)
                    ->searchable(),

                Tables\Columns\TextColumn::make('cast_type')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'integer', 'float' => 'warning',
                        'boolean' => 'success',
                        'json' => 'danger',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('company_id')
                    ->label('Company Scope')
                    ->options(Company::pluck('name', 'id'))
                    ->placeholder('All Settings'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettings::route('/'),
            'create' => Pages\CreateSetting::route('/create'),
            'edit' => Pages\EditSetting::route('/{record}/edit'),
        ];
    }
}
