<?php

declare(strict_types=1);

namespace App\Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\SalesChannel;
use App\Traits\HasUuid;
use App\Traits\HasTenantScope;
use App\Traits\Auditable;
use App\Traits\Filterable;
use App\Models\User;
use App\Models\Address;
use App\Modules\Vendor\Models\Vendor;

class Order extends Model
{
    use SoftDeletes;
    use HasUuid, HasTenantScope, Auditable, Filterable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'status'                => \App\Modules\Order\Enums\OrderStatus::class,
            'channel'               => SalesChannel::class,
            'scheduled_delivery_at' => 'datetime',
            'delivered_at'          => 'datetime',
            'subtotal_amount'       => 'float',
            'delivery_fee'          => 'float',
            'tax_amount'            => 'float',
            'total_amount'          => 'float',
            'commission_amount'     => 'float',
            'commission_rate'       => 'float',
        ];
    }

    public function isDirectChannel(): bool
    {
        return $this->channel === SalesChannel::Direct;
    }

    public function isMarketplaceChannel(): bool
    {
        return $this->channel === SalesChannel::Marketplace;
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class, 'vendor_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function deliveryAddress(): BelongsTo
    {
        return $this->belongsTo(Address::class, 'delivery_address_id');
    }

    public function items(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderItem::class, 'order_id');
    }

    public function statusLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderStatusLog::class, 'order_id');
    }

    public function tracking(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(OrderTracking::class, 'order_id');
    }
}
