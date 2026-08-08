<?php

declare(strict_types=1);

namespace App\Modules\Driver\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Traits\HasTenantScope;

class Vehicle extends Model
{
    use SoftDeletes;
    use HasUuid, HasTenantScope;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'year'            => 'integer',
            'capacity_liters' => 'float',
        ];
    }

    public function drivers(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Driver::class, 'driver_vehicle')
            ->withPivot('is_active', 'assigned_at', 'unassigned_at')
            ->withTimestamps();
    }
}
