<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Auditable;
use App\Traits\Filterable;
use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasUuid, Auditable, Filterable, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'companies';

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'tax_number',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'created_by' => 'string',
            'updated_by' => 'string',
        ];
    }

    /**
     * Get the vendors for the company.
     *
     * @return HasMany
     */
    public function vendors(): HasMany
    {
        return $this->hasMany(\App\Modules\Vendor\Models\Vendor::class, 'company_id');
    }

    /**
     * Get the users for the company.
     *
     * @return HasMany
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'company_id');
    }

    /**
     * Get the addresses for the company.
     *
     * @return HasMany
     */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class, 'company_id');
    }

    /**
     * Get the settings for the company.
     *
     * @return HasMany
     */
    public function settings(): HasMany
    {
        return $this->hasMany(Setting::class, 'company_id');
    }

    /**
     * Scope a query to only include active companies.
     *
     * @param Builder $query
     * @return Builder
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }
}
