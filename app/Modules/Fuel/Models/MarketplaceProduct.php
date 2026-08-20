<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Models;

use App\Enums\UnitOfMeasure;
use App\Models\Category;
use App\Traits\Auditable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceProduct extends Model
{
    use Auditable, HasUuid, SoftDeletes;

    protected $table = 'marketplace_products';

    protected $fillable = [
        'category_id',
        'name',
        'slug',
        'description',
        'product_image',
        'unit_of_measure',
        'specifications_schema',
        'is_active',
        'is_coming_soon',
        'ordering_enabled',
        'display_order',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_coming_soon' => 'boolean',
        'ordering_enabled' => 'boolean',
        'display_order' => 'integer',
        'specifications_schema' => 'array',
        'unit_of_measure' => UnitOfMeasure::class,
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }
}
