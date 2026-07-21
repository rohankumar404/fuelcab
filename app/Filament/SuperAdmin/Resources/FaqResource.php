<?php

declare(strict_types=1);

namespace App\Filament\SuperAdmin\Resources;

use App\Filament\SuperAdmin\Resources\FaqResource\Pages;
use App\Models\Faq;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FaqResource extends Resource
{
    protected static ?string $model = Faq::class;

    protected static ?string $navigationIcon = 'heroicon-o-question-mark-circle';

    protected static ?string $navigationGroup = 'CONTENT';

    protected static ?string $navigationLabel = 'FAQs';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Section::make('FAQ Content')->schema([
                Forms\Components\TextInput::make('question')
                    ->required()
                    ->maxLength(500)
                    ->columnSpanFull(),

                Forms\Components\RichEditor::make('answer')
                    ->required()
                    ->toolbarButtons(['bold', 'italic', 'bulletList', 'orderedList', 'link'])
                    ->columnSpanFull(),

                Forms\Components\Select::make('category')
                    ->options([
                        'general'     => 'General',
                        'ordering'    => 'Ordering',
                        'payment'     => 'Payment',
                        'delivery'    => 'Delivery',
                        'marketplace' => 'Marketplace',
                        'vendor'      => 'Vendor',
                        'account'     => 'Account',
                    ])
                    ->nullable(),

                Forms\Components\TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),

                Forms\Components\Toggle::make('is_active')
                    ->label('Visible on website')
                    ->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                Tables\Columns\TextColumn::make('sort_order')
                    ->label('#')
                    ->sortable(),

                Tables\Columns\TextColumn::make('question')
                    ->searchable()
                    ->limit(60),

                Tables\Columns\BadgeColumn::make('category')
                    ->colors([
                        'gray'    => 'general',
                        'info'    => 'ordering',
                        'success' => 'payment',
                        'warning' => 'delivery',
                        'primary' => 'marketplace',
                        'danger'  => 'vendor',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Active'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label('Last Updated')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->options([
                        'general'     => 'General',
                        'ordering'    => 'Ordering',
                        'payment'     => 'Payment',
                        'delivery'    => 'Delivery',
                        'marketplace' => 'Marketplace',
                        'vendor'      => 'Vendor',
                        'account'     => 'Account',
                    ]),
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Active Only'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->requiresConfirmation(),
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
            'index'  => Pages\ListFaqs::route('/'),
            'create' => Pages\CreateFaq::route('/create'),
            'edit'   => Pages\EditFaq::route('/{record}/edit'),
        ];
    }
}
