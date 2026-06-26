<?php

namespace App\Filament\Vendor\Resources;

use App\Filament\Vendor\Resources\DriverDocumentResource\Pages;
use App\Filament\Vendor\Resources\DriverDocumentResource\RelationManagers;
use App\Models\DriverDocument;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DriverDocumentResource extends Resource
{
    protected static ?string $model = DriverDocument::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
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
            'index' => Pages\ListDriverDocuments::route('/'),
            'create' => Pages\CreateDriverDocument::route('/create'),
            'edit' => Pages\EditDriverDocument::route('/{record}/edit'),
        ];
    }
}
