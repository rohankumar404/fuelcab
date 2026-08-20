<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Models;

use App\Models\Settlement;
use App\Models\User;
use App\Modules\Fuel\Models\MarketplaceProduct;
use App\Modules\Fuel\Models\Product;
use App\Modules\Vendor\Enums\DocumentStatus;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Traits\Auditable;
use App\Traits\Filterable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use Auditable, Filterable, HasUuid;
    use SoftDeletes;

    protected $table = 'vendors';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_first_party' => 'boolean',
            'commission_rate' => 'decimal:2',
            'latitude' => 'float',
            'longitude' => 'float',
            'status' => VendorStatus::class,
            'verification_status' => DocumentStatus::class,
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VendorDocument::class);
    }

    public function marketplaceProducts(): BelongsToMany
    {
        return $this->belongsToMany(
            MarketplaceProduct::class,
            'vendor_marketplace_products',
            'vendor_id',
            'marketplace_product_id'
        )->withTimestamps();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function listings(): HasMany
    {
        return $this->hasMany(VendorListing::class);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', VendorStatus::Approved);
    }
}
