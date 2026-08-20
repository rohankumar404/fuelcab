<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Payment\Models\Payment;
use App\Traits\Auditable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Refund proxy model for Filament SuperAdmin panel.
 * Maps to the `refunds` table (created by payment/2024_01_05_000004_create_refunds_table.php).
 */
class Refund extends Model
{
    use Auditable, HasUuid;
    use SoftDeletes;

    protected $table = 'refunds';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'amount' => 'float',
            'processed_at' => 'datetime',
        ];
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class, 'payment_id');
    }
}
