<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;

class InventoryLog extends Model
{
    use HasUuid;

    protected $table = 'inventory_logs';

    public $timestamps = true;
    const UPDATED_AT = null; // logs are append-only

    protected $fillable = [
        'inventory_id',
        'product_id',
        'vendor_id',
        'type',
        'quantity_before',
        'quantity_changed',
        'quantity_after',
        'reference_type',
        'reference_id',
        'notes',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'quantity_before'  => 'float',
            'quantity_changed' => 'float',
            'quantity_after'   => 'float',
        ];
    }

    public function inventory(): BelongsTo
    {
        return $this->belongsTo(FuelInventory::class, 'inventory_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
