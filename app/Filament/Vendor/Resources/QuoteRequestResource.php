<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\QuoteRequestResource\Pages;
use App\Models\BulkInquiry;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class QuoteRequestResource extends Resource
{
    protected static ?string $model = BulkInquiry::class;

    protected static ?string $navigationIcon = 'heroicon-o-chat-bubble-left-ellipsis';

    protected static ?string $navigationGroup = 'Quote Requests';

    protected static ?string $navigationLabel = 'Quote Requests';

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'Quote Request';

    protected static ?string $pluralModelLabel = 'Quote Requests';

    /**
     * SECURITY: Always scope to inquiries linked to this vendor only.
     * vendor_id is resolved from the authenticated user — never from the frontend.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(auth()->check() && auth()->user()->vendor_id, function ($query) {
                $query->where('vendor_id', auth()->user()->vendor_id);
            })
            ->with(['user', 'listing', 'product']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Request Details')->schema([
                Forms\Components\Placeholder::make('customer')
                    ->label('Requested By')
                    ->content(fn (?BulkInquiry $record): string =>
                        $record?->user?->name . ' — ' . $record?->user?->email ?? '—'),

                Forms\Components\Placeholder::make('product_name')
                    ->label('Product / Listing')
                    ->content(fn (?BulkInquiry $record): string =>
                        $record?->listing?->listing_title ?? $record?->product?->name ?? '—'),

                Forms\Components\Placeholder::make('requested_qty')
                    ->label('Requested Quantity')
                    ->content(fn (?BulkInquiry $record): string =>
                        number_format((float) ($record?->quantity ?? 0), 2)),

                Forms\Components\Placeholder::make('preferred_delivery')
                    ->label('Preferred Delivery Date')
                    ->content(fn (?BulkInquiry $record): string =>
                        $record?->preferred_delivery_date?->format('d M Y') ?? '—'),

                Forms\Components\Textarea::make('message')
                    ->label('Customer Message')
                    ->disabled()
                    ->columnSpanFull(),
            ])->columns(2),

            Forms\Components\Section::make('Your Quotation')
                ->description('Fill the fields below to submit your quotation to the customer.')
                ->schema([
                    Forms\Components\TextInput::make('quoted_price')
                        ->label('Price per Unit (₹)')
                        ->prefix('₹')
                        ->numeric()
                        ->required()
                        ->minValue(0),

                    Forms\Components\TextInput::make('quoted_unit')
                        ->label('Unit')
                        ->placeholder('e.g. Metric Tonnes, Litres, Kg')
                        ->required(),

                    Forms\Components\TextInput::make('quoted_min_quantity')
                        ->label('Minimum Order Quantity')
                        ->numeric()
                        ->required()
                        ->minValue(1),

                    Forms\Components\DatePicker::make('validity_date')
                        ->label('Quote Validity Date')
                        ->required()
                        ->minDate(now()),

                    Forms\Components\TextInput::make('dispatch_time')
                        ->label('Estimated Dispatch Time')
                        ->placeholder('e.g. 3-5 business days')
                        ->required(),

                    Forms\Components\Textarea::make('terms')
                        ->label('Terms & Conditions')
                        ->rows(3)
                        ->nullable()
                        ->columnSpanFull(),

                    Forms\Components\Textarea::make('notes')
                        ->label('Additional Notes')
                        ->rows(2)
                        ->nullable()
                        ->columnSpanFull(),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Customer')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('listing.listing_title')
                    ->label('Listing / Product')
                    ->default(fn ($record) => $record->product?->name ?? '—')
                    ->limit(35),

                Tables\Columns\TextColumn::make('quantity')
                    ->label('Requested Qty')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'warning' => 'pending',
                        'success' => 'responded',
                        'gray'    => 'closed',
                    ]),

                Tables\Columns\TextColumn::make('quoted_price')
                    ->label('Quoted Price (₹)')
                    ->money('INR')
                    ->placeholder('—')
                    ->sortable(),

                Tables\Columns\TextColumn::make('validity_date')
                    ->label('Valid Until')
                    ->date()
                    ->placeholder('—'),

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
            ])
            ->actions([
                Tables\Actions\Action::make('submit_quotation')
                    ->label('Submit Quotation')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn (BulkInquiry $record): bool => $record->status === 'pending')
                    ->form([
                        Forms\Components\TextInput::make('quoted_price')
                            ->label('Price per Unit (₹)')
                            ->prefix('₹')
                            ->numeric()
                            ->required()
                            ->minValue(0),

                        Forms\Components\TextInput::make('quoted_unit')
                            ->label('Unit')
                            ->placeholder('e.g. Metric Tonnes, Litres')
                            ->required(),

                        Forms\Components\TextInput::make('quoted_min_quantity')
                            ->label('Minimum Order Quantity')
                            ->numeric()
                            ->required(),

                        Forms\Components\DatePicker::make('validity_date')
                            ->label('Quote Valid Until')
                            ->required()
                            ->minDate(now()),

                        Forms\Components\TextInput::make('dispatch_time')
                            ->label('Estimated Dispatch Time')
                            ->placeholder('e.g. 3-5 business days')
                            ->required(),

                        Forms\Components\Textarea::make('terms')
                            ->label('Terms & Conditions')
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('notes')
                            ->label('Additional Notes')
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),
                    ])
                    ->action(function (BulkInquiry $record, array $data): void {
                        // SECURITY: Re-verify ownership before mutating
                        if ($record->vendor_id !== auth()->user()->vendor_id) {
                            Notification::make()->title('Unauthorized action.')->danger()->send();
                            return;
                        }

                        $record->update(array_merge($data, ['status' => 'responded']));

                        Notification::make()
                            ->title('Quotation submitted successfully.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\ViewAction::make()
                    ->label('View'),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQuoteRequests::route('/'),
            'view'  => Pages\ViewQuoteRequest::route('/{record}'),
        ];
    }
}
