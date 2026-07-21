<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\SettlementResource\Pages;
use App\Models\Settlement;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class SettlementResource extends Resource
{
    protected static ?string $model = Settlement::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationGroup = 'Settlements';

    protected static ?string $navigationLabel = 'Settlements';

    protected static ?int $navigationSort = 7;

    /**
     * SECURITY: Always scope to this vendor's settlements only.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(auth()->check() && auth()->user()->vendor_id, function ($query) {
                $query->where('vendor_id', auth()->user()->vendor_id);
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Period')
                    ->dateTime('M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('gross_amount')
                    ->label('Gross Sales (₹)')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('commission_amount')
                    ->label('Commission (₹)')
                    ->money('INR')
                    ->color('danger'),

                Tables\Columns\TextColumn::make('adjustments')
                    ->label('Adjustments (₹)')
                    ->money('INR')
                    ->placeholder('—'),

                Tables\Columns\TextColumn::make('net_payable')
                    ->label('Net Settlement (₹)')
                    ->money('INR')
                    ->color('success')
                    ->weight(\Filament\Support\Enums\FontWeight::Bold)
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'processed',
                        'danger'  => 'failed',
                    ])
                    ->formatStateUsing(fn (string $state): string => ucfirst($state)),

                Tables\Columns\TextColumn::make('payout_reference')
                    ->label('Payout Ref')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'processed' => 'Processed',
                        'failed'    => 'Failed',
                    ]),
            ])
            ->actions([])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSettlements::route('/'),
        ];
    }
}
