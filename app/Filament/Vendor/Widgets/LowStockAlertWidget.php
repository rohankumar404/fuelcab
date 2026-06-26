<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Widgets;

use App\Modules\Fuel\Models\Inventory;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class LowStockAlertWidget extends BaseWidget
{
    protected static ?string $heading = 'Low Stock Alerts';
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        $vendorId = auth()->user()?->vendor_id;

        return $table
            ->query(
                Inventory::query()
                    ->where('vendor_id', $vendorId)
                    ->whereColumn('current_stock', '<=', 'reorder_level')
                    ->latest()
            )
            ->columns([
                Tables\Columns\TextColumn::make('product.name')->label('Product'),
                Tables\Columns\TextColumn::make('current_stock')->label('Stock'),
                Tables\Columns\TextColumn::make('reorder_level')->label('Reorder At'),
                Tables\Columns\TextColumn::make('unit'),
            ]);
    }
}
