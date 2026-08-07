<?php

declare(strict_types=1);

namespace App\Modules\Location\Providers;

use App\Modules\Location\Services\GoogleMapsService;
use Illuminate\Support\ServiceProvider;

class LocationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register GoogleMapsService as a singleton — one instance, zero duplication
        $this->app->singleton(GoogleMapsService::class, fn () => new GoogleMapsService());
    }

    public function boot(): void
    {
        //
    }
}
