<?php

declare(strict_types=1);

namespace App\Providers;

use App\Http\Middleware\ApiVersionMiddleware;
use App\Http\Middleware\RequestSignature;
use App\Http\Middleware\RoleMiddleware;
use App\Http\Middleware\VendorScope;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerMigrationPaths();
        $this->registerRateLimiters();

        // Use custom UUID-based PersonalAccessToken model
        \Laravel\Sanctum\Sanctum::usePersonalAccessTokenModel(
            \App\Models\PersonalAccessToken::class
        );

        // Register policies explicitly
        \Illuminate\Support\Facades\Gate::policy(
            \App\Modules\Order\Models\Order::class,
            \App\Modules\Order\Policies\OrderPolicy::class
        );
        \Illuminate\Support\Facades\Gate::policy(
            \App\Modules\Fuel\Models\Product::class,
            \App\Modules\Fuel\Policies\ProductPolicy::class
        );
        \Illuminate\Support\Facades\Gate::policy(
            \App\Modules\Fuel\Models\MarketplaceProduct::class,
            \App\Modules\Fuel\Policies\MarketplaceProductPolicy::class
        );
        \Illuminate\Support\Facades\Gate::policy(
            \App\Modules\Vendor\Models\Vendor::class,
            \App\Modules\Vendor\Policies\VendorPolicy::class
        );
        \Illuminate\Support\Facades\Gate::policy(
            \App\Modules\Vendor\Models\VendorDocument::class,
            \App\Modules\Vendor\Policies\VendorDocumentPolicy::class
        );

        // Register custom notifications database channel
        $this->app->make(\Illuminate\Notifications\ChannelManager::class)->extend('database', function ($app) {
            return new \App\Modules\Notification\Channels\CustomDatabaseChannel();
        });

        // Implicitly grant "Super Admin" role all permissions
        // This is the Spatie standard practice for Laravel architectures
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            return $user->hasRole(\App\Enums\UserRole::SuperAdmin->value) ? true : null;
        });
    }

    /**
     * Register sub-directory migration paths so Laravel picks them up.
     */
    private function registerMigrationPaths(): void
    {
        $subDirs = ['core', 'driver', 'order', 'fuel', 'payment', 'notification'];

        foreach ($subDirs as $dir) {
            $path = database_path("migrations/{$dir}");
            if (is_dir($path)) {
                $this->loadMigrationsFrom($path);
            }
        }
    }

    /**
     * Configure the rate limiters for the application.
     */
    private function registerRateLimiters(): void
    {
        // Global API limiter
        RateLimiter::for('api', function (Request $request) {
            $limit = (int) config('fuelcab.api.rate_limits.global', 60);

            return Limit::perMinute($limit)->by(
                $request->user()?->id ?: $request->ip()
            );
        });

        // Strict limiter for auth/OTP endpoints
        RateLimiter::for('auth', function (Request $request) {
            $limit = (int) config('fuelcab.api.rate_limits.auth', 10);

            return Limit::perMinute($limit)->by($request->ip());
        });

        // OTP-specific limiter
        RateLimiter::for('otp', function (Request $request) {
            $limit = (int) config('fuelcab.api.rate_limits.otp', 5);

            return Limit::perMinute($limit)->by($request->ip());
        });

        // Webhook limiter
        RateLimiter::for('webhooks', function (Request $request) {
            $limit = (int) config('fuelcab.api.rate_limits.webhooks', 100);

            return Limit::perMinute($limit)->by($request->ip());
        });
    }
}
