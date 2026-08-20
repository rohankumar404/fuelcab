<?php

declare(strict_types=1);

namespace App\Modules\Vehicle\Models;

use App\Traits\Auditable;
use App\Traits\HasTenantScope;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vehicle extends Model
{
    use Auditable,HasTenantScope,HasUuid;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }
}
