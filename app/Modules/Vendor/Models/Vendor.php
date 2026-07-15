<?php

declare(strict_types=1);

namespace App\Modules\Vendor\Models;

use App\Models\User;
use App\Modules\Vendor\Enums\VendorStatus;
use App\Modules\Vendor\Enums\DocumentStatus;
use App\Traits\Auditable;
use App\Traits\Filterable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use SoftDeletes;
    use HasUuid, Auditable, Filterable;

    protected $table = 'vendors';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'is_first_party'      => 'boolean',
            'commission_rate'     => 'decimal:2',
            'latitude'            => 'float',
            'longitude'           => 'float',
            'status'              => VendorStatus::class,
            'verification_status' => DocumentStatus::class,
        ];
    }

    public function products(): HasMany
    {
        return $this->hasMany(\App\Modules\Fuel\Models\Product::class);
    }

    public function settlements(): HasMany
    {
        return $this->hasMany(\App\Models\Settlement::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(VendorDocument::class);
    }

    public function marketplaceProducts(): \Illuminate\Database\Eloquent\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            \App\Modules\Fuel\Models\MarketplaceProduct::class,
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
