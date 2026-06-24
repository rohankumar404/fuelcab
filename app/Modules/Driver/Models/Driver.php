<?php

declare(strict_types=1);

namespace App\Modules\Driver\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Traits\HasTenantScope;
use App\Traits\Auditable;
use App\Traits\Filterable;
class Driver extends Model
{
    use SoftDeletes;
    use HasUuid,HasTenantScope,Auditable,Filterable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }
}
