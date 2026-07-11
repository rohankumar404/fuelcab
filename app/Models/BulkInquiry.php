<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;

class BulkInquiry extends Model
{
    use HasUuid;

    protected $table = 'bulk_inquiries';

    protected $fillable = [
        'user_id',
        'product_id',
        'quantity',
        'preferred_delivery_date',
        'status', // pending, responded, closed
        'message',
    ];

    protected $casts = [
        'preferred_delivery_date' => 'date',
        'quantity' => 'decimal:2',
    ];

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Modules\Fuel\Models\Product::class);
    }
}
