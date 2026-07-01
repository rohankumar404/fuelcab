<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
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
        'status', // active, disabled, soon
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function inventory(): HasOne
    {
        return $this->hasOne(FuelInventory::class, 'product_id');
    }

    /**
     * Check if the product can be ordered.
     */
    public function isOrderingEnabled(): bool
    {
        return $this->is_active && $this->status === 'active';
    }

    /**
     * Check if the product is marked as Coming Soon.
     */
    public function isComingSoon(): bool
    {
        return $this->status === 'soon';
    }
}
