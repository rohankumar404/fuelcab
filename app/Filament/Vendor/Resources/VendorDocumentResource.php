<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\VendorDocumentResource\Pages;
use App\Modules\Vendor\Enums\DocumentStatus;
use App\Modules\Vendor\Models\VendorDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VendorDocumentResource extends Resource
{
    protected static ?string $model = VendorDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'Documents';

    protected static ?string $navigationLabel = 'Documents';

    protected static ?int $navigationSort = 6;

    /**
     * SECURITY: Always scope to the authenticated vendor's documents.
     * vendor_id is resolved server-side — never trusted from frontend.
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->when(auth()->check() && auth()->user()->vendor_id, function ($query) {
                $query->where('vendor_id', auth()->user()->vendor_id);
            });
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('Document Details')->schema([
                Forms\Components\Select::make('document_type')
                    ->label('Document Type')
                    ->options([
                        'gst_certificate'    => 'GST Certificate',
                        'pan_card'           => 'PAN Card',
                        'trade_license'      => 'Trade License',
                        'incorporation'      => 'Certificate of Incorporation',
                        'quality_cert'       => 'Quality Certification (ISO, BIS, etc.)',
                        'bank_statement'     => 'Bank Statement',
                        'cancelled_cheque'   => 'Cancelled Cheque',
                        'address_proof'      => 'Address Proof',
                        'other'              => 'Other',
                    ])
                    ->required()
                    ->searchable(),

                Forms\Components\TextInput::make('file_path')
                    ->label('Document URL / File Path')
                    ->url()
                    ->placeholder('https://storage.example.com/doc.pdf')
                    ->required()
                    ->helperText('Paste the URL of the uploaded document'),

                Forms\Components\DatePicker::make('expires_at')
                    ->label('Expiry Date (if applicable)')
                    ->nullable()
                    ->minDate(today()),
            ])->columns(2),

            Forms\Components\Section::make('Verification Status')->schema([
                Forms\Components\Placeholder::make('status_display')
                    ->label('Current Status')
                    ->content(fn (?VendorDocument $record): string =>
                        $record?->status?->label() ?? 'Pending Verification'),

                Forms\Components\Placeholder::make('verified_at_display')
                    ->label('Verified At')
                    ->content(fn (?VendorDocument $record): string =>
                        $record?->verified_at?->format('d M Y H:i') ?? '—'),

                Forms\Components\Placeholder::make('rejection_reason_display')
                    ->label('Rejection Reason')
                    ->content(fn (?VendorDocument $record): string =>
                        $record?->rejection_reason ?? '—')
                    ->visible(fn (?VendorDocument $record): bool =>
                        $record?->status === DocumentStatus::Rejected),
            ])->columns(3),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('document_type')
                    ->label('Document Type')
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'gst_certificate'  => 'GST Certificate',
                        'pan_card'         => 'PAN Card',
                        'trade_license'    => 'Trade License',
                        'incorporation'    => 'Certificate of Incorporation',
                        'quality_cert'     => 'Quality Certification',
                        'bank_statement'   => 'Bank Statement',
                        'cancelled_cheque' => 'Cancelled Cheque',
                        'address_proof'    => 'Address Proof',
                        default            => ucwords(str_replace('_', ' ', $state)),
                    })
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('file_path')
                    ->label('Document')
                    ->formatStateUsing(fn () => 'View Document')
                    ->url(fn (VendorDocument $record): string => $record->file_path)
                    ->openUrlInNewTab()
                    ->color('primary'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state instanceof DocumentStatus ? $state->label() : $state)
                    ->colors([
                        'warning' => fn ($state) => $state instanceof DocumentStatus
                            ? $state === DocumentStatus::Pending
                            : $state === 'pending',
                        'success' => fn ($state) => $state instanceof DocumentStatus
                            ? $state === DocumentStatus::Verified
                            : $state === 'verified',
                        'danger'  => fn ($state) => $state instanceof DocumentStatus
                            ? $state === DocumentStatus::Rejected
                            : $state === 'rejected',
                    ]),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label('Expires')
                    ->date()
                    ->placeholder('—')
                    ->color(fn (?string $state): string =>
                        $state && now()->gt($state) ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Uploaded')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending'  => 'Pending',
                        'verified' => 'Verified',
                        'rejected' => 'Rejected',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->label('Update')
                    ->visible(fn (VendorDocument $record): bool =>
                        $record->status !== DocumentStatus::Verified),
            ])
            ->bulkActions([])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVendorDocuments::route('/'),
            'create' => Pages\CreateVendorDocument::route('/create'),
            'edit'   => Pages\EditVendorDocument::route('/{record}/edit'),
        ];
    }
}
