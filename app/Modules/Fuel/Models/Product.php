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
        'short_description',
        'full_description',
        'product_image',
        'icon',
        'min_order_quantity',
        'max_order_quantity',
        'ordering_enabled',
        'is_coming_soon',
        'is_featured',
        'seo_title',
        'seo_description',
        'display_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'ordering_enabled' => 'boolean',
        'is_coming_soon' => 'boolean',
        'is_featured' => 'boolean',
        'min_order_quantity' => 'decimal:4',
        'max_order_quantity' => 'decimal:4',
        'price_per_unit' => 'decimal:4',
        'unit_of_measure' => \App\Enums\UnitOfMeasure::class,
        'display_order' => 'integer',
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
        return $this->is_active && $this->ordering_enabled;
    }

    /**
     * Check if the product is marked as Coming Soon.
     */
    public function isComingSoon(): bool
    {
        return $this->is_coming_soon || $this->status === 'soon';
    }

    /**
     * Scope to fetch FuelCab Direct products only.
     */
    public function scopeDirect($query)
    {
        return $query->whereHas('vendor', function ($q) {
            $q->where('is_first_party', true);
        });
    }

    /**
     * Scope to fetch featured products.
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to order products by display_order.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('display_order', 'asc');
    }
}
