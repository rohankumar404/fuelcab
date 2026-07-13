<?php

declare(strict_types=1);

namespace App\Modules\Order\Models;

use App\Enums\SalesChannel;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;
use App\Traits\Auditable;
use App\Modules\Vendor\Models\Vendor;

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
            'sales_channel'  => SalesChannel::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        // Correct module path
        return $this->belongsTo(\App\Modules\Fuel\Models\Product::class, 'product_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    /**
     * Whether this item was fulfilled by FuelCab Direct.
     */
    public function isDirectChannel(): bool
    {
        return $this->sales_channel === SalesChannel::Direct;
    }

    /**
     * Whether this item was fulfilled by a marketplace vendor.
     */
    public function isMarketplaceChannel(): bool
    {
        return $this->sales_channel === SalesChannel::Marketplace;
    }
}
