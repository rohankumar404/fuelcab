<?php

declare(strict_types=1);

namespace App\Modules\Driver\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class DriverLocation extends Model
{
    use HasUuid;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }
}
