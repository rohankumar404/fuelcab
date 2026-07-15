<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Enums\ListingStatus;
use App\Filament\SuperAdmin\Resources\VendorListingResource\Pages;
use App\Modules\Vendor\Models\VendorListing;
use App\Modules\Vendor\Services\VendorListingService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class VendorListingResource extends Resource
{
    protected static ?string $model = VendorListing::class;

    protected static ?string $navigationIcon = 'heroicon-o-queue-list';

    protected static ?string $navigationGroup = 'Marketplace';

    protected static ?string $navigationLabel = 'Vendor Listings';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([

                Forms\Components\Group::make()->schema([

                    Forms\Components\Section::make('Listing Info')->schema([
                        Forms\Components\TextInput::make('listing_title')
                            ->disabled(),
                        Forms\Components\TextInput::make('sku')
                            ->disabled(),
                        Forms\Components\Textarea::make('short_description')
                            ->disabled()
                            ->columnSpanFull(),
                        Forms\Components\Textarea::make('full_description')
                            ->disabled()
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('Quality Specifications')->schema([
                        Forms\Components\KeyValue::make('quality_specifications')
                            ->disabled(),
                    ]),

                ])->columnSpan(2),

                Forms\Components\Group::make()->schema([

                    Forms\Components\Section::make('Review')->schema([
                        Forms\Components\Select::make('approval_status')
                            ->options(ListingStatus::options())
                            ->disabled(),

                        Forms\Components\Textarea::make('rejection_reason')
                            ->disabled()
                            ->nullable(),

                        Forms\Components\Placeholder::make('vendor_name')
                            ->label('Vendor')
                            ->content(fn ($record) => $record?->vendor?->brand_name ?? '—'),

                        Forms\Components\Placeholder::make('product_name')
                            ->label('Product Master')
                            ->content(fn ($record) => $record?->marketplaceProduct?->name ?? '—'),

                        Forms\Components\Placeholder::make('reviewed_at')
                            ->label('Reviewed At')
                            ->content(fn ($record) => $record?->reviewed_at?->diffForHumans() ?? '—'),
                    ]),

                    Forms\Components\Section::make('Pricing')->schema([
                        Forms\Components\TextInput::make('base_price')->prefix('₹')->disabled(),
                        Forms\Components\TextInput::make('tax_rate')->suffix('%')->disabled(),
                        Forms\Components\Toggle::make('tax_inclusive')->disabled(),
                        Forms\Components\Toggle::make('is_featured')->label('Featured'),
                    ]),

                ])->columnSpan(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                Tables\Columns\TextColumn::make('listing_title')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('vendor.brand_name')
                    ->label('Vendor')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('marketplaceProduct.name')
                    ->label('Product Master')
                    ->sortable(),

                Tables\Columns\TextColumn::make('base_price')
                    ->money('INR')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('approval_status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state instanceof ListingStatus ? $state->label() : $state)
                    ->colors([
                        'secondary' => 'DRAFT',
                        'warning'   => 'PENDING_APPROVAL',
                        'success'   => 'APPROVED',
                        'danger'    => 'REJECTED',
                    ]),

                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->label('Featured'),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('approval_status')
                    ->options(ListingStatus::options()),

                Tables\Filters\SelectFilter::make('vendor_id')
                    ->label('Vendor')
                    ->relationship('vendor', 'brand_name')
                    ->searchable(),

                Tables\Filters\SelectFilter::make('marketplace_product_id')
                    ->label('Product Master')
                    ->relationship('marketplaceProduct', 'name')
                    ->searchable(),

                Tables\Filters\TernaryFilter::make('is_featured')->label('Featured'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),

                Tables\Actions\Action::make('approve')
                    ->label('Approve')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (VendorListing $record) => $record->approval_status === ListingStatus::PendingApproval)
                    ->action(function (VendorListing $record) {
                        app(VendorListingService::class)->approve($record, auth()->user());
                        Notification::make()->title('Listing approved.')->success()->send();
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('Reject')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (VendorListing $record) => $record->approval_status === ListingStatus::PendingApproval)
                    ->form([
                        Forms\Components\Textarea::make('reason')
                            ->label('Rejection Reason')
                            ->required()
                            ->minLength(10),
                    ])
                    ->action(function (VendorListing $record, array $data) {
                        app(VendorListingService::class)->reject($record, auth()->user(), $data['reason']);
                        Notification::make()->title('Listing rejected.')->warning()->send();
                    }),

                Tables\Actions\Action::make('suspend')
                    ->label('Suspend')
                    ->icon('heroicon-o-pause-circle')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn (VendorListing $record) => $record->approval_status === ListingStatus::Approved)
                    ->action(function (VendorListing $record) {
                        app(VendorListingService::class)->suspend($record);
                        Notification::make()->title('Listing suspended.')->warning()->send();
                    }),

                Tables\Actions\Action::make('feature')
                    ->label(fn (VendorListing $record) => $record->is_featured ? 'Unfeature' : 'Feature')
                    ->icon('heroicon-o-star')
                    ->color('gray')
                    ->visible(fn (VendorListing $record) => $record->approval_status === ListingStatus::Approved)
                    ->action(function (VendorListing $record) {
                        app(VendorListingService::class)->toggleFeatured($record);
                        Notification::make()->title('Featured status updated.')->success()->send();
                    }),

                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListVendorListings::route('/'),
            'view'   => Pages\ViewVendorListing::route('/{record}'),
        ];
    }
}
