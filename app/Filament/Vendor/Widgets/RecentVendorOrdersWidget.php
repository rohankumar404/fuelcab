<?php

declare(strict_types=1);

namespace App\Filament\Vendor\Widgets;

use App\Modules\Order\Models\Order;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentVendorOrdersWidget extends BaseWidget
{
    protected static ?string $heading = 'Recent Orders';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $vendorId = auth()->user()?->vendor_id;

        return $table
            ->query(Order::query()->where('vendor_id', $vendorId)->latest()->limit(15))
            ->columns([
                Tables\Columns\TextColumn::make('order_number')->searchable(),
                Tables\Columns\TextColumn::make('customer.name')->label('Customer'),
                Tables\Columns\TextColumn::make('driver.user.name')->label('Driver')->placeholder('—'),
                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'gray'    => 'pending',
                        'warning' => 'confirmed',
                        'info'    => 'en_route',
                        'success' => 'completed',
                        'danger'  => 'cancelled',
                    ]),
                Tables\Columns\TextColumn::make('total_amount')->money('INR'),
                Tables\Columns\TextColumn::make('created_at')->since(),
            ]);
    }
}
