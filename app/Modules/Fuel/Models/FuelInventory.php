<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;
use App\Traits\HasTenantScope;
use App\Traits\Auditable;

class FuelInventory extends Model
{
    use SoftDeletes;
    use HasUuid, HasTenantScope, Auditable;

    protected $table = 'inventories';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
