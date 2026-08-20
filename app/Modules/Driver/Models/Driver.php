<?php

declare(strict_types=1);

namespace App\Modules\Driver\Models;

use App\Models\User;
use App\Traits\Auditable;
use App\Traits\Filterable;
use App\Traits\HasTenantScope;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Driver extends Model
{
    use Auditable,Filterable,HasTenantScope,HasUuid;
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_approved' => 'boolean',
            'license_expiry' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function vehicles(): BelongsToMany
    {
        return $this->belongsToMany(Vehicle::class, 'driver_vehicle')
            ->withPivot('is_active', 'assigned_at', 'unassigned_at')
            ->withTimestamps();
    }

    public function activeVehicle(): BelongsToMany
    {
        return $this->vehicles()->wherePivot('is_active', true);
    }
}
