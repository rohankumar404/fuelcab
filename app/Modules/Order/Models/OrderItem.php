<?php

declare(strict_types=1);

namespace App\Modules\Order\Models;

use App\Enums\SalesChannel;
use App\Modules\Fuel\Models\Product;
use App\Modules\Vendor\Models\Vendor;
use App\Traits\Auditable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use Auditable, HasUuid, SoftDeletes;

    protected $table = 'order_items';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'price_per_unit' => 'float',
            'total_price' => 'float',
            'sales_channel' => SalesChannel::class,
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function product(): BelongsTo
    {
        // Correct module path
        return $this->belongsTo(Product::class, 'product_id');
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
