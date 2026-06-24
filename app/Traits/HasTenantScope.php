<?php

declare(strict_types=1);

namespace App\Traits;

trait HasTenantScope
{
    /**
     * Boot the HasTenantScope trait — apply a global vendor_id scope.
     */
    protected static function bootHasTenantScope(): void
    {
        static::addGlobalScope('vendor', function ($builder): void {
            if (auth()->check() && auth()->user()->vendor_id) {
                $builder->where('vendor_id', auth()->user()->vendor_id);
            }
        });
    }
}
