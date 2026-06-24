<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Traits\HasTenantScope;
use App\Traits\Auditable;
class FuelType extends Model
{
    use SoftDeletes;
    use HasUuid,HasTenantScope,Auditable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }
}
