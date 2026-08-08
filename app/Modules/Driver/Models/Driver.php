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
        return [
            'is_approved'    => 'boolean',
            'license_expiry' => 'date',
        ];
    }

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function vehicles(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'driver_vehicle')
            ->withPivot('is_active', 'assigned_at', 'unassigned_at')
            ->withTimestamps();
    }

    public function activeVehicle(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->vehicles()->wherePivot('is_active', true);
    }
}
