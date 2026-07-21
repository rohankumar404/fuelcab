<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\BannerResource\Pages;
use App\Models\Banner;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static ?string $navigationIcon = 'heroicon-o-photo';

    protected static ?string $navigationGroup = 'CONTENT';

    protected static ?string $navigationLabel = 'Banners';

    protected static ?int $navigationSort = 4;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Banner Content')->schema([
                        Forms\Components\TextInput::make('title')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('subtitle')
                            ->nullable()
                            ->maxLength(255),

                        Forms\Components\FileUpload::make('image_path')
                            ->label('Banner Image')
                            ->image()
                            ->imageResizeMode('cover')
                            ->imageCropAspectRatio('16:9')
                            ->directory('banners')
                            ->nullable()
                            ->columnSpanFull(),

                        Forms\Components\TextInput::make('target_url')
                            ->label('Target URL')
                            ->url()
                            ->nullable(),
                    ])->columns(2),
                ])->columnSpan(2),

                Forms\Components\Group::make()->schema([
                    Forms\Components\Section::make('Display Settings')->schema([
                        Forms\Components\Select::make('placement')
                            ->options([
                                'homepage_hero'     => 'Homepage Hero',
                                'marketplace_hero'  => 'Marketplace Hero',
                                'sidebar'           => 'Sidebar',
                                'category_banner'   => 'Category Banner',
                                'product_banner'    => 'Product Banner',
                                'email_header'      => 'Email Header',
                            ])
                            ->default('homepage_hero')
                            ->required(),

                        Forms\Components\TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->required(),

                        Forms\Components\Toggle::make('is_active')
                            ->label('Active')
                            ->default(true),
                    ]),

                    Forms\Components\Section::make('Schedule')->schema([
                        Forms\Components\DateTimePicker::make('starts_at')
                            ->label('Goes Live At')
                            ->nullable(),

                        Forms\Components\DateTimePicker::make('ends_at')
                            ->label('Expires At')
                            ->nullable(),
                    ])->collapsed(),
                ])->columnSpan(1),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Image')
                    ->width(80)
                    ->height(45),

                Tables\Columns\TextColumn::make('title')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('placement')
                    ->colors([
                        'primary' => 'homepage_hero',
                        'success' => 'marketplace_hero',
                        'info'    => 'sidebar',
                        'warning' => 'category_banner',
                        'gray'    => 'email_header',
                    ])
                    ->formatStateUsing(fn ($state) => ucwords(str_replace('_', ' ', $state))),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label('Starts')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label('Ends')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('placement')
                    ->options([
                        'homepage_hero'    => 'Homepage Hero',
                        'marketplace_hero' => 'Marketplace Hero',
                        'sidebar'          => 'Sidebar',
                        'category_banner'  => 'Category Banner',
                        'email_header'     => 'Email Header',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->requiresConfirmation(),
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
            'index'  => Pages\ListBanners::route('/'),
            'create' => Pages\CreateBanner::route('/create'),
            'edit'   => Pages\EditBanner::route('/{record}/edit'),
        ];
    }
}
