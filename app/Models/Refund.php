<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Traits\Auditable;

/**
 * Refund proxy model for Filament SuperAdmin panel.
 * Maps to the `refunds` table (created by payment/2024_01_05_000004_create_refunds_table.php).
 */
class Refund extends Model
{
    use SoftDeletes;
    use HasUuid, Auditable;

    protected $table = 'refunds';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'amount'       => 'decimal:2',
            'processed_at' => 'datetime',
        ];
    }
}
