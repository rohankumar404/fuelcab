<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Resources;

use App\Enums\ListingStatus;
use App\Filament\Vendor\Resources\InventoryResource\Pages;
use App\Modules\Vendor\Models\VendorListing;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InventoryResource extends Resource
{
    protected static ?string $model = VendorListing::class;

    protected static ?string $navigationIcon = 'heroicon-o-archive-box';

    protected static ?string $navigationGroup = 'Inventory';

    protected static ?string $navigationLabel = 'Inventory';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'Inventory';

    protected static ?string $pluralModelLabel = 'Inventory';

    /**
     * SECURITY: Always scope to the authenticated vendor's listings.
     * vendor_id is resolved server-side from the authenticated user — never trusted from frontend.
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
            Forms\Components\Section::make('Stock Management')->schema([
                Forms\Components\Placeholder::make('listing_title')
                    ->label('Listing')
                    ->content(fn (?VendorListing $record): string => $record?->listing_title ?? '—'),

                Forms\Components\TextInput::make('available_quantity')
                    ->label('Available Quantity')
                    ->numeric()
                    ->required()
                    ->minValue(0),

                Forms\Components\TextInput::make('min_order_quantity')
                    ->label('Low Stock Threshold (Min Order Qty)')
                    ->numeric()
                    ->required()
                    ->helperText('Orders below this quantity indicate low stock'),

                Forms\Components\TextInput::make('max_order_quantity')
                    ->label('Maximum Order Quantity')
                    ->numeric()
                    ->nullable(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('listing_title')
                    ->label('Listing')
                    ->searchable()
                    ->sortable()
                    ->limit(40),

                Tables\Columns\TextColumn::make('marketplaceProduct.name')
                    ->label('Product')
                    ->sortable(),

                Tables\Columns\TextColumn::make('available_quantity')
                    ->label('Available')
                    ->sortable()
                    ->color(fn ($state) => $state < 100 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('min_order_quantity')
                    ->label('Low Stock Threshold')
                    ->sortable(),

                Tables\Columns\TextColumn::make('unit')
                    ->label('Unit'),

                Tables\Columns\BadgeColumn::make('approval_status')
                    ->label('Status')
                    ->formatStateUsing(fn ($state) => $state instanceof ListingStatus ? $state->label() : $state)
                    ->colors([
                        'secondary' => 'DRAFT',
                        'warning'   => 'PENDING_APPROVAL',
                        'success'   => 'APPROVED',
                        'danger'    => 'REJECTED',
                    ]),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('low_stock')
                    ->label('Low Stock Only')
                    ->query(fn (Builder $query) => $query->where('available_quantity', '<', 100))
                    ->toggle(),
            ])
            ->actions([
                Tables\Actions\Action::make('update_stock')
                    ->label('Update Stock')
                    ->icon('heroicon-o-pencil-square')
                    ->color('primary')
                    ->form([
                        Forms\Components\TextInput::make('available_quantity')
                            ->label('New Available Quantity')
                            ->numeric()
                            ->required()
                            ->minValue(0),
                        Forms\Components\TextInput::make('min_order_quantity')
                            ->label('Low Stock Threshold')
                            ->numeric()
                            ->required(),
                    ])
                    ->fillForm(fn (VendorListing $record) => [
                        'available_quantity' => $record->available_quantity,
                        'min_order_quantity' => $record->min_order_quantity,
                    ])
                    ->action(function (VendorListing $record, array $data): void {
                        // SECURITY: Re-verify ownership before mutating
                        if ($record->vendor_id !== auth()->user()->vendor_id) {
                            Notification::make()
                                ->title('Unauthorized action.')
                                ->danger()
                                ->send();
                            return;
                        }

                        $record->update([
                            'available_quantity' => $data['available_quantity'],
                            'min_order_quantity' => $data['min_order_quantity'],
                        ]);

                        Notification::make()
                            ->title('Stock updated successfully.')
                            ->success()
                            ->send();
                    }),
            ])
            ->bulkActions([]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInventories::route('/'),
        ];
    }
}
