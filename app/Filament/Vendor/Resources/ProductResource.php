<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\ProductResource\Pages;
use App\Filament\Vendor\Resources\ProductResource\RelationManagers;
use App\Modules\Fuel\Models\Product;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

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
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Product Details')->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        Forms\Components\TextInput::make('slug')->required()->maxLength(255),
                        Forms\Components\TextInput::make('sku')->label('SKU')->required()->maxLength(100),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name')
                            ->required(),
                        Forms\Components\Hidden::make('vendor_id')
                            ->default(fn () => auth()->user()?->vendor_id)
                            ->required(),
                        Forms\Components\TextInput::make('price_per_unit')->numeric()->prefix('₹')->required(),
                        Forms\Components\Select::make('unit_of_measure')
                            ->options(\App\Enums\UnitOfMeasure::class)
                            ->required(),
                        Forms\Components\Textarea::make('short_description')->rows(2)->nullable()->columnSpanFull(),
                        Forms\Components\Textarea::make('full_description')->rows(4)->nullable()->columnSpanFull(),
                    ])->columns(2),
                ])->columnSpan(2),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Status & Ordering')->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Forms\Components\Toggle::make('ordering_enabled')
                            ->label('Ordering Enabled')
                            ->default(true),
                        Forms\Components\Toggle::make('is_coming_soon')
                            ->label('Coming Soon')
                            ->default(false),
                        Forms\Components\Toggle::make('is_featured')
                            ->label('Featured')
                            ->default(false),
                        Forms\Components\TextInput::make('min_order_quantity')
                            ->numeric()
                            ->default(100.0)
                            ->required(),
                        Forms\Components\TextInput::make('max_order_quantity')
                            ->numeric()
                            ->nullable(),
                        Forms\Components\TextInput::make('display_order')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),

                    Forms\Components\Section::make('Design & Assets')->schema([
                        Forms\Components\TextInput::make('product_image')->url()->nullable(),
                        Forms\Components\TextInput::make('icon')->placeholder('droplet, wind, flame...')->nullable(),
                    ])->collapsed(),

                    Forms\Components\Section::make('SEO Metadata')->schema([
                        Forms\Components\TextInput::make('seo_title')->maxLength(255)->nullable(),
                        Forms\Components\Textarea::make('seo_description')->rows(2)->nullable(),
                    ])->collapsed(),
                ])->columnSpan(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('category.name')->label('Category')->sortable(),
                Tables\Columns\TextColumn::make('price_per_unit')->money('INR')->sortable(),
                Tables\Columns\TextColumn::make('unit_of_measure')->label('Unit')->sortable(),
                Tables\Columns\IconColumn::make('is_active')->boolean()->label('Active'),
                Tables\Columns\IconColumn::make('ordering_enabled')->boolean()->label('Ordering'),
                Tables\Columns\IconColumn::make('is_featured')->boolean()->label('Featured'),
                Tables\Columns\TextColumn::make('display_order')->sortable()->label('Sort Order'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')->label('Active Only'),
                Tables\Filters\TernaryFilter::make('is_featured')->label('Featured Only'),
                Tables\Filters\TernaryFilter::make('ordering_enabled')->label('Ordering Enabled'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
