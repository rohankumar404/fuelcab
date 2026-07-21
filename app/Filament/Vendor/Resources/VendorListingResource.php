<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources;

use App\Enums\ListingStatus;
use App\Filament\Vendor\Resources\VendorListingResource\Pages;
use App\Modules\Fuel\Models\MarketplaceProduct;
use App\Modules\Vendor\Models\VendorListing;
use App\Modules\Vendor\Services\VendorListingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class VendorListingResource extends Resource
{
    protected static ?string $model = VendorListing::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';

    protected static ?string $navigationGroup = 'Listings';

    protected static ?string $navigationLabel = 'Listings';

    protected static ?int $navigationSort = 1;

    /**
     * Vendor panel: always scope to the authenticated vendor's listings.
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
            Forms\Components\Grid::make(3)->schema([

                // ── Left column (main content) ───────────────────────────────
                Forms\Components\Group::make()->schema([

                    Forms\Components\Section::make('Listing Details')->schema([
                        Forms\Components\Select::make('marketplace_product_id')
                            ->label('Marketplace Product')
                            ->options(
                                MarketplaceProduct::where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                            )
                            ->required()
                            ->searchable(),

                        Forms\Components\TextInput::make('listing_title')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),

                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('sku')
                            ->label('SKU')
                            ->nullable()
                            ->maxLength(100),

                        Forms\Components\Textarea::make('short_description')
                            ->rows(2)
                            ->nullable()
                            ->columnSpanFull(),

                        Forms\Components\RichEditor::make('full_description')
                            ->nullable()
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('Pricing & Tax')->schema([
                        Forms\Components\TextInput::make('base_price')
                            ->numeric()
                            ->prefix('₹')
                            ->required(),

                        Forms\Components\TextInput::make('tax_rate')
                            ->numeric()
                            ->suffix('%')
                            ->default(18)
                            ->required(),

                        Forms\Components\Toggle::make('tax_inclusive')
                            ->label('Price is Tax-Inclusive')
                            ->default(false),
                    ])->columns(3),

                    Forms\Components\Section::make('Order Quantities & Unit')->schema([
                        Forms\Components\Select::make('unit')
                            ->options(\App\Enums\UnitOfMeasure::class)
                            ->required(),

                        Forms\Components\TextInput::make('available_quantity')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('min_order_quantity')
                            ->numeric()
                            ->default(1)
                            ->required(),

                        Forms\Components\TextInput::make('max_order_quantity')
                            ->numeric()
                            ->nullable(),
                    ])->columns(4),

                    Forms\Components\Section::make('Quality Specifications')
                        ->description('Add flexible specs like GCV, Moisture, Ash Content, Sulphur, Density etc.')
                        ->schema([
                            Forms\Components\KeyValue::make('quality_specifications')
                                ->keyLabel('Specification')
                                ->valueLabel('Value / Range')
                                ->nullable()
                                ->columnSpanFull(),
                        ]),

                    Forms\Components\Section::make('Product Images')->schema([
                        Forms\Components\Repeater::make('product_images')
                            ->label('Image URLs')
                            ->schema([
                                Forms\Components\TextInput::make('url')
                                    ->url()
                                    ->required()
                                    ->placeholder('https://...'),
                            ])
                            ->addActionLabel('Add Image URL')
                            ->defaultItems(0)
                            ->nullable(),
                    ]),

                    Forms\Components\Section::make('Certificate Documents')->schema([
                        Forms\Components\Repeater::make('certificate_documents')
                            ->label('Document URLs')
                            ->schema([
                                Forms\Components\TextInput::make('url')
                                    ->url()
                                    ->required()
                                    ->placeholder('https://...'),
                            ])
                            ->addActionLabel('Add Document URL')
                            ->defaultItems(0)
                            ->nullable(),
                    ])->collapsed(),

                ])->columnSpan(2),

                // ── Right column (logistics + status) ────────────────────────
                Forms\Components\Group::make()->schema([

                    Forms\Components\Section::make('Approval Status')->schema([
                        Forms\Components\Placeholder::make('approval_status_display')
                            ->label('Current Status')
                            ->content(fn ($record) => $record?->approval_status?->label() ?? 'Draft'),

                        Forms\Components\Placeholder::make('rejection_reason_display')
                            ->label('Rejection Reason')
                            ->content(fn ($record) => $record?->rejection_reason ?? '—')
                            ->visible(fn ($record) => $record?->approval_status === ListingStatus::Rejected),
                    ]),

                    Forms\Components\Section::make('Logistics')->schema([
                        Forms\Components\TextInput::make('dispatch_location')
                            ->nullable()
                            ->placeholder('e.g. Surat, Gujarat'),

                        Forms\Components\TextInput::make('estimated_dispatch_hours')
                            ->numeric()
                            ->nullable()
                            ->suffix('hours')
                            ->placeholder('e.g. 48'),

                        Forms\Components\TagsInput::make('serviceable_locations')
                            ->label('Serviceable Locations')
                            ->placeholder('Add a city or state')
                            ->nullable(),
                    ]),

                    Forms\Components\Section::make('Visibility')->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),

                ])->columnSpan(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('listing_title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('marketplaceProduct.name')
                    ->label('Product')
                    ->sortable(),

                Tables\Columns\TextColumn::make('base_price')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\TextColumn::make('available_quantity')
                    ->label('Stock')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('approval_status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state instanceof ListingStatus ? $state->label() : $state)
                    ->colors([
                        'secondary' => 'DRAFT',
                        'warning'   => 'PENDING_APPROVAL',
                        'success'   => 'APPROVED',
                        'danger'    => 'REJECTED',
                        'warning'   => 'SUSPENDED',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('approval_status')
                    ->options(ListingStatus::options()),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->visible(fn (VendorListing $record) => $record->isEditable()),

                Tables\Actions\Action::make('submit')
                    ->label('Submit for Approval')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (VendorListing $record) => $record->approval_status->isSubmittable())
                    ->action(function (VendorListing $record) {
                        app(VendorListingService::class)->submit($record);
                        Notification::make()
                            ->title('Listing submitted for approval.')
                            ->success()
                            ->send();
                    }),

                Tables\Actions\Action::make('view_rejection')
                    ->label('View Rejection Reason')
                    ->icon('heroicon-o-exclamation-circle')
                    ->color('danger')
                    ->visible(fn (VendorListing $record) => $record->approval_status === ListingStatus::Rejected)
                    ->modalContent(fn (VendorListing $record) => view('filament.modals.rejection-reason', ['reason' => $record->rejection_reason]))
                    ->modalHeading('Rejection Reason')
                    ->modalSubmitAction(false),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVendorListings::route('/'),
            'create' => Pages\CreateVendorListing::route('/create'),
            'edit'   => Pages\EditVendorListing::route('/{record}/edit'),
        ];
    }
}
