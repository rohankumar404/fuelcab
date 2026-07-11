<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Traits\Auditable;
use App\Traits\Filterable;
class Vendor extends Model
{
    use SoftDeletes;
    use HasUuid,Auditable,Filterable;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_first_party' => 'boolean',
            'commission_rate' => 'decimal:2',
        ];
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Fuel\Models\Product::class);
    }

    public function settlements(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\Settlement::class);
    }
}
