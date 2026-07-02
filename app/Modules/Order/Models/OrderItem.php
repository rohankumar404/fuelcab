<?php

declare(strict_types=1);

namespace App\Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;
use App\Traits\Auditable;

class OrderItem extends Model
{
    use SoftDeletes, HasUuid, Auditable;

    protected $table = 'order_items';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'quantity'       => 'float',
            'price_per_unit' => 'float',
            'total_price'    => 'float',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        // Maps to the Product model in product module or App\Modules\Product\Models\Product
        return $this->belongsTo(\App\Modules\Product\Models\Product::class, 'product_id');
    }
}
