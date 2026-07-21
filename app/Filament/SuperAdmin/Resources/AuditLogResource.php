<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\AuditLogResource\Pages;
use App\Models\AuditLog;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class AuditLogResource extends Resource
{
    protected static ?string $model = AuditLog::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    protected static ?string $navigationGroup = 'SYSTEM';

    protected static ?string $navigationLabel = 'Audit Logs';

    protected static ?int $navigationSort = 5;

    public static function canCreate(): bool
    {
        return false; // Audit logs are system-generated, not manually created
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Audit Entry')->schema([
                Forms\Components\Placeholder::make('user')
                    ->label('Actor')
                    ->content(fn ($record) => ($record?->user_name ?? '—') . ($record?->user_id ? ' (' . $record->user_id . ')' : '')),

                Forms\Components\Placeholder::make('action')
                    ->content(fn ($record) => $record?->action ?? '—'),

                Forms\Components\Placeholder::make('model')
                    ->label('Affected Record')
                    ->content(fn ($record) => ($record?->model_type ?? '—') . ' #' . ($record?->model_id ?? '—')),

                Forms\Components\Placeholder::make('ip_address')
                    ->label('IP Address')
                    ->content(fn ($record) => $record?->ip_address ?? '—'),

                Forms\Components\Placeholder::make('created_at')
                    ->label('Timestamp')
                    ->content(fn ($record) => $record?->created_at?->format('d M Y, H:i:s') ?? '—'),

                Forms\Components\Placeholder::make('notes')
                    ->content(fn ($record) => $record?->notes ?? '—')
                    ->columnSpanFull(),

                Forms\Components\KeyValue::make('old_values')
                    ->label('Previous Values')
                    ->disabled()
                    ->columnSpanFull(),

                Forms\Components\KeyValue::make('new_values')
                    ->label('New Values')
                    ->disabled()
                    ->columnSpanFull(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user_name')
                    ->label('Actor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('action')
                    ->colors([
                        'success' => fn ($state) => in_array($state, ['created', 'approved', 'verified']),
                        'warning' => fn ($state) => in_array($state, ['updated', 'suspended']),
                        'danger'  => fn ($state) => in_array($state, ['deleted', 'rejected']),
                        'info'    => fn ($state) => in_array($state, ['login', 'logout']),
                    ]),

                Tables\Columns\TextColumn::make('model_type')
                    ->label('Model')
                    ->formatStateUsing(fn ($state) => class_basename($state))
                    ->toggleable(),

                Tables\Columns\TextColumn::make('model_id')
                    ->label('Record ID')
                    ->limit(12)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('action')
                    ->options([
                        'created'  => 'Created',
                        'updated'  => 'Updated',
                        'deleted'  => 'Deleted',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'suspended' => 'Suspended',
                        'login'    => 'Login',
                    ]),
                Tables\Filters\Filter::make('today')
                    ->label('Today Only')
                    ->query(fn ($query) => $query->whereDate('created_at', today())),
                Tables\Filters\Filter::make('this_week')
                    ->label('This Week')
                    ->query(fn ($query) => $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                // No edit, no delete — audit logs are immutable
            ])
            ->bulkActions([
                // No bulk actions on audit logs — they are immutable
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAuditLogs::route('/'),
            'view'  => Pages\ViewAuditLog::route('/{record}'),
        ];
    }
}
