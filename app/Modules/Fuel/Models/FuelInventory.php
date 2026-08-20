<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Models;

use App\Traits\Auditable;
use App\Traits\HasTenantScope;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class FuelInventory extends Model
{
    use Auditable, HasTenantScope, HasUuid;
    use SoftDeletes;

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
