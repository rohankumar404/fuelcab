<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\VendorDocumentResource\Pages;
use App\Modules\Vendor\Enums\DocumentStatus;
use App\Modules\Vendor\Models\VendorDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VendorDocumentResource extends Resource
{
    protected static ?string $model = VendorDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-check';

    protected static ?string $navigationGroup = 'VENDORS';

    protected static ?string $navigationLabel = 'Vendor Documents';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Document Details')->schema([
                Forms\Components\Placeholder::make('vendor')
                    ->label('Vendor')
                    ->content(fn ($record) => $record?->vendor?->brand_name ?? '—'),

                Forms\Components\TextInput::make('document_type')
                    ->label('Document Type')
                    ->disabled(),

                Forms\Components\Select::make('status')
                    ->options(collect(DocumentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()]))
                    ->required(),

                Forms\Components\Textarea::make('rejection_reason')
                    ->label('Rejection Reason')
                    ->nullable()
                    ->columnSpanFull(),

                Forms\Components\Textarea::make('internal_notes')
                    ->label('Internal Notes')
                    ->nullable()
                    ->columnSpanFull(),

                Forms\Components\DatePicker::make('expires_at')
                    ->label('Document Expiry')
                    ->nullable(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('vendor.brand_name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Document Type')
                    ->searchable()
                    ->badge()
                    ->sortable(),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'warning' => DocumentStatus::Pending->value,
                        'success' => DocumentStatus::Verified->value,
                        'danger'  => DocumentStatus::Rejected->value,
                    ])
                    ->formatStateUsing(fn ($state) => $state instanceof DocumentStatus ? $state->label() : $state),
                Tables\Columns\TextColumn::make('verified_at')
                    ->label('Verified At')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('verifier.name')
                    ->label('Verified By')
                    ->toggleable(),
                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date()
                    ->sortable()
                    ->toggleable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options(collect(DocumentStatus::cases())->mapWithKeys(fn ($s) => [$s->value => $s->label()])),
                Tables\Filters\SelectFilter::make('vendor_id')
                    ->label('Vendor')
                    ->relationship('vendor', 'brand_name')
                    ->searchable(),
                Tables\Filters\SelectFilter::make('document_type')
                    ->options([
                        'gst_certificate'   => 'GST Certificate',
                        'pan'               => 'PAN Card',
                        'bank_statement'    => 'Bank Statement',
                        'trade_license'     => 'Trade License',
                        'quality_cert'      => 'Quality Certificate',
                        'other'             => 'Other',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->label('Review'),
                Tables\Actions\Action::make('verify')
                    ->label('Verify')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (VendorDocument $record) => $record->status !== DocumentStatus::Verified)
                    ->action(function (VendorDocument $record): void {
                        $record->update([
                            'status'      => DocumentStatus::Verified,
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);
                        Notification::make()->title('Document verified.')->success()->send();
                    }),
                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (VendorDocument $record) => $record->status !== DocumentStatus::Rejected)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->rows(3),
                    ])
                    ->action(function (VendorDocument $record, array $data): void {
                        $record->update([
                            'status'           => DocumentStatus::Rejected,
                            'rejection_reason' => $data['reason'],
                        ]);
                        Notification::make()->title('Document rejected.')->danger()->send();
                    }),
            ])
            ->bulkActions([
                Tables\Actions\BulkAction::make('verify_all')
                    ->label('Verify Selected')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function ($records): void {
                        $records->each->update([
                            'status'      => DocumentStatus::Verified,
                            'verified_at' => now(),
                            'verified_by' => auth()->id(),
                        ]);
                        Notification::make()->title('Documents verified.')->success()->send();
                    }),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListVendorDocuments::route('/'),
            'edit'  => Pages\EditVendorDocument::route('/{record}/edit'),
        ];
    }
}
