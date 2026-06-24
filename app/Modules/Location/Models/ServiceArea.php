<?php

declare(strict_types=1);

namespace App\Modules\Location\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Traits\HasTenantScope;
class ServiceArea extends Model
{
    use SoftDeletes;
    use HasUuid,HasTenantScope;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }
}
