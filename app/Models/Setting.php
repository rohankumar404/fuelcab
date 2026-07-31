<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Traits\Auditable;

/**
 * Setting proxy model for Filament SuperAdmin panel.
 * Maps to the `settings` table (created by core/2024_01_01_000007_create_settings_table.php).
 */
class Setting extends Model
{
    use SoftDeletes;
    use HasUuid, Auditable;

    protected $table = 'settings';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }
}
