<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\HasUuid;
use App\Traits\Auditable;

class Category extends Model
{
    use HasUuid, Auditable, SoftDeletes;

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
        'is_active'      => 'boolean',
        'display_order'  => 'integer',
    ];

    public function parent(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    public function children(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Category::class, 'parent_id');
    }

    public function products(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Fuel\Models\Product::class);
    }

    public function marketplaceProducts(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Modules\Fuel\Models\MarketplaceProduct::class);
    }
}
