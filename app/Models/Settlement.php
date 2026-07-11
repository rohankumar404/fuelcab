<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class Settlement extends Model
{
    use HasUuid;

    protected $table = 'settlements';

    protected $fillable = [
        'vendor_id',
        'gross_amount',
        'commission_amount',
        'net_payable',
        'status', // pending, processed, failed
        'payout_reference',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'net_payable' => 'decimal:2',
    ];

    public function vendor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Vendor\Models\Vendor::class);
    }
}
