<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Vendor\Models\VendorListing;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderSubscription extends Model
{
    use HasUuid;

    protected $table = 'order_subscriptions';

    protected $fillable = [
        'user_id',
        'vendor_listing_id',
        'quantity',
        'frequency',
        'status',
        'next_delivery_at',
    ];

    protected $casts = [
        'quantity' => 'float',
        'next_delivery_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function listing(): BelongsTo
    {
        return $this->belongsTo(VendorListing::class, 'vendor_listing_id');
    }
}
