<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Vendor\Models\Vendor;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Settlement extends Model
{
    use HasUuid;

    protected $table = 'settlements';

    protected $fillable = [
        'vendor_id',
        'gross_amount',
        'commission_amount',
        'adjustments',
        'net_payable',
        'status', // pending, processed, failed
        'payout_reference',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'adjustments' => 'decimal:2',
        'net_payable' => 'decimal:2',
    ];

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
