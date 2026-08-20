<?php

declare(strict_types=1);

namespace App\Modules\User\Models;

use App\Traits\Auditable;
use App\Traits\Filterable;
use App\Traits\HasTenantScope;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Model
{
    use Auditable,Filterable,HasTenantScope,HasUuid;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }
}
