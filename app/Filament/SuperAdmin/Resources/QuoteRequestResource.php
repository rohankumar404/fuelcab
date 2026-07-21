<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\QuoteRequestResource\Pages;
use App\Models\BulkInquiry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class QuoteRequestResource extends Resource
{
    protected static ?string $model = BulkInquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationGroup = 'MARKETPLACE';

    protected static ?string $navigationLabel = 'Quote Requests';

    protected static ?int $navigationSort = 6;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Quote Request Details')->schema([
                Forms\Components\Placeholder::make('customer')
                    ->label('Requested By')
                    ->content(fn ($record) => $record?->user?->name . ' — ' . $record?->user?->email ?? '—'),

                Forms\Components\Placeholder::make('product')
                    ->label('Product')
                    ->content(fn ($record) => $record?->product?->name ?? '—'),

                Forms\Components\TextInput::make('quantity')
                    ->label('Requested Quantity')
                    ->disabled(),

                Forms\Components\Placeholder::make('preferred_delivery_date')
                    ->label('Preferred Delivery')
                    ->content(fn ($record) => $record?->preferred_delivery_date?->format('d M Y') ?? '—'),

                Forms\Components\Textarea::make('message')
                    ->label('Customer Message')
                    ->disabled()
                    ->columnSpanFull(),

                Forms\Components\Select::make('status')
                    ->options([
                        'pending'    => 'Pending',
                        'responded'  => 'Responded',
                        'closed'     => 'Closed',
                    ])
                    ->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Requester')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('product.name')
                    ->label('Product')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('quantity')
                    ->label('Qty Requested')
                    ->sortable(),
                Tables\Columns\TextColumn::make('preferred_delivery_date')
                    ->label('Preferred Delivery')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'responded',
                        'gray'    => 'closed',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Received')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'   => 'Pending',
                        'responded' => 'Responded',
                        'closed'    => 'Closed',
                    ]),
                Tables\Filters\Filter::make('received_today')
                    ->label('Received Today')
                    ->query(fn ($query) => $query->whereDate('created_at', today())),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->label('Update Status'),
                Tables\Actions\Action::make('mark_responded')
                    ->label('Mark Responded')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (BulkInquiry $record) => $record->status === 'pending')
                    ->action(function (BulkInquiry $record): void {
                        $record->update(['status' => 'responded']);
                        Notification::make()->title('Quote marked as responded.')->success()->send();
                    }),
                Tables\Actions\Action::make('close')
                    ->label('Close')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn (BulkInquiry $record) => $record->status !== 'closed')
                    ->action(function (BulkInquiry $record): void {
                        $record->update(['status' => 'closed']);
                        Notification::make()->title('Quote request closed.')->warning()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('mark_all_responded')
                    ->label('Mark Selected as Responded')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        $records->each->update(['status' => 'responded']);
                        Notification::make()->title('Selected quotes marked as responded.')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuoteRequests::route('/'),
            'edit'  => Pages\EditQuoteRequest::route('/{record}/edit'),
        ];
    }
}
