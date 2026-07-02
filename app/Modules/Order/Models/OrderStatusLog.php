<?php

declare(strict_types=1);

namespace App\Modules\Order\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;
use App\Models\User;

class OrderStatusLog extends Model
{
    use HasUuid;

    protected $table = 'order_status_logs';

    protected $fillable = [
        'order_id',
        'from_status',
        'to_status',
        'reason',
        'changed_by',
        'changed_at',
    ];

    protected function casts(): array
    {
        return [
            'from_status' => \App\Modules\Order\Enums\OrderStatus::class,
            'to_status' => \App\Modules\Order\Enums\OrderStatus::class,
            'changed_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
