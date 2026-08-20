<?php

declare(strict_types=1);

namespace App\Models;

use App\Modules\Fuel\Models\MarketplaceProduct;
use App\Modules\Fuel\Models\Product;
use App\Traits\Auditable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;

class Category extends Model
{
    use Auditable, HasUuid, SoftDeletes;

    protected $table = 'categories';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'type',
        'is_coming_soon',
        'image_path',
        'display_order',
        'is_active',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'is_coming_soon' => 'boolean',
        'is_active' => 'boolean',
        'display_order' => 'integer',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function marketplaceProducts(): HasMany
    {
        return $this->hasMany(MarketplaceProduct::class);
    }

    protected static function booted(): void
    {
        static::saved(function () {
            Cache::forget('marketplace_categories');
        });

        static::deleted(function () {
            Cache::forget('marketplace_categories');
        });
    }
}
