<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Traits\HasUuid;
use App\Traits\Auditable;
use App\Models\Category;
use App\Modules\Vendor\Models\Vendor;

class Product extends Model
{
    use HasUuid, Auditable, SoftDeletes;

    protected $table = 'products';

    protected $fillable = [
        'category_id',
        'vendor_id',
        'name',
        'slug',
        'sku',
        'description',
        'price_per_unit',
        'unit_of_measure',
        'is_active',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }
}
