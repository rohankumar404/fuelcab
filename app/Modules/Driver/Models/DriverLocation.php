<?php

declare(strict_types=1);

namespace App\Modules\Driver\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\HasUuid;
class DriverLocation extends Model
{
    use HasUuid;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }
}
