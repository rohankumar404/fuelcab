<?php

declare(strict_types=1);

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait Filterable
{
    /**
     * Apply an array of filters to the query.
     *
     * @param  array<string, mixed>  $filters
     */
    public function scopeFilter(Builder $query, array $filters): Builder
    {
        foreach ($filters as $key => $value) {
            if (! is_null($value) && method_exists($this, 'filter'.ucfirst($key))) {
                $this->{'filter'.ucfirst($key)}($query, $value);
            }
        }

        return $query;
    }
}
