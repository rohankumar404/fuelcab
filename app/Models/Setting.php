<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Setting proxy model for Filament SuperAdmin panel.
 * Maps to the `settings` table (created by core/2024_01_01_000007_create_settings_table.php).
 */
class Setting extends Model
{
    use Auditable, HasUuid;
    use SoftDeletes;

    protected $table = 'settings';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }
}
