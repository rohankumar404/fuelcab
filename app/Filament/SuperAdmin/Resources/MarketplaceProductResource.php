<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\MarketplaceProductResource\Pages;
use App\Modules\Fuel\Models\MarketplaceProduct;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class MarketplaceProductResource extends Resource
{
    protected static ?string $model = MarketplaceProduct::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office-2';

    protected static ?string $navigationGroup = 'MARKETPLACE';

    protected static ?string $navigationLabel = 'Product Master';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Approved Product Details')->schema([
                        Forms\Components\TextInput::make('name')
                            ->required()
                            ->maxLength(255)
                            ->reactive()
                            ->afterStateUpdated(fn ($state, callable $set) => $set('slug', \Illuminate\Support\Str::slug($state))),
                        Forms\Components\TextInput::make('slug')
                            ->required()
                            ->maxLength(255),
                        Forms\Components\Select::make('category_id')
                            ->label('Category')
                            ->relationship('category', 'name', fn ($query) => $query->whereNotNull('parent_id')->orWhere('slug', 'ev'))
                            ->required(),
                        Forms\Components\Select::make('unit_of_measure')
                            ->options(\App\Enums\UnitOfMeasure::class)
                            ->required(),
                        Forms\Components\Textarea::make('description')
                            ->rows(3)
                            ->nullable()
                            ->columnSpanFull(),
                    ])->columns(2),

                    Forms\Components\Section::make('Product Specifications')->schema([
                        Forms\Components\KeyValue::make('specifications_schema')
                            ->label('Flexible Specifications')
                            ->keyLabel('Property')
                            ->valueLabel('Value Schema (e.g. Max 5%, Min 3800)')
                            ->placeholder('Add a specification property')
                            ->columnSpanFull(),
                    ]),
                ])->columnSpan(2),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Catalog Status')->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                        Forms\Components\Toggle::make('is_coming_soon')
                            ->label('Coming Soon')
                            ->default(false),
                        Forms\Components\Toggle::make('ordering_enabled')
                            ->label('Ordering Enabled')
                            ->default(true),
                        Forms\Components\TextInput::make('display_order')
                            ->numeric()
                            ->default(0)
                            ->required(),
                    ]),

                    Forms\Components\Section::make('Design & Media')->schema([
                        Forms\Components\TextInput::make('product_image')
                            ->url()
                            ->nullable(),
                    ]),

                    Forms\Components\Section::make('SEO Metadata')->schema([
                        Forms\Components\TextInput::make('seo_title')
                            ->maxLength(255)
                            ->nullable(),
                        Forms\Components\Textarea::make('seo_description')
                            ->rows(2)
                            ->nullable(),
                    ])->collapsed(),
                ])->columnSpan(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->label('Category')
                    ->sortable(),
                Tables\Columns\TextColumn::make('unit_of_measure')
                    ->label('Unit')
                    ->sortable(),
                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),
                Tables\Columns\IconColumn::make('is_coming_soon')
                    ->boolean()
                    ->label('Coming Soon'),
                Tables\Columns\IconColumn::make('ordering_enabled')
                    ->boolean()
                    ->label('Ordering'),
                Tables\Columns\TextColumn::make('display_order')
                    ->sortable()
                    ->label('Order'),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Only'),
                Tables\Filters\TernaryFilter::make('is_coming_soon')
                    ->label('Coming Soon Only'),
                Tables\Filters\TernaryFilter::make('ordering_enabled')
                    ->label('Ordering Enabled'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
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
            'index'  => Pages\ListMarketplaceProducts::route('/'),
            'create' => Pages\CreateMarketplaceProduct::route('/create'),
            'edit'   => Pages\EditMarketplaceProduct::route('/{record}/edit'),
        ];
    }
}
