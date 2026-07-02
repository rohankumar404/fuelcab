<?php

declare(strict_types=1);

namespace App\Modules\Checkout\Models;

use App\Models\Address;
use App\Models\User;
use App\Modules\Cart\Models\Cart;
use App\Modules\Vendor\Models\Vendor;
use App\Traits\Auditable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Checkout extends Model
{
    use HasUuid, SoftDeletes, Auditable;

    protected $table = 'checkouts';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'scheduled_delivery_at' => 'datetime',
            'subtotal_amount'       => 'float',
            'delivery_fee'          => 'float',
            'tax_amount'            => 'float',
            'total_amount'          => 'float',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    public function address(): BelongsTo
    {
        return $this->belongsTo(Address::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
