<?php

declare(strict_types=1);

namespace App\Providers;

use App\Enums\UserRole;
use App\Models\BulkInquiry;
use App\Models\PersonalAccessToken;
use App\Models\Settlement;
use App\Modules\Fuel\Models\MarketplaceProduct;
use App\Modules\Fuel\Models\Product;
use App\Modules\Fuel\Policies\MarketplaceProductPolicy;
use App\Modules\Fuel\Policies\ProductPolicy;
use App\Modules\Notification\Channels\CustomDatabaseChannel;
use App\Modules\Order\Models\Order;
use App\Modules\Order\Policies\OrderPolicy;
use App\Modules\Vendor\Models\Vendor;
use App\Modules\Vendor\Models\VendorDocument;
use App\Modules\Vendor\Models\VendorListing;
use App\Modules\Vendor\Policies\BulkInquiryPolicy;
use App\Modules\Vendor\Policies\SettlementPolicy;
use App\Modules\Vendor\Policies\VendorDocumentPolicy;
use App\Modules\Vendor\Policies\VendorListingPolicy;
use App\Modules\Vendor\Policies\VendorPolicy;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Laravel\Sanctum\Sanctum;

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
        Sanctum::usePersonalAccessTokenModel(
            PersonalAccessToken::class
        );

        // Register policies explicitly
        Gate::policy(
            Order::class,
            OrderPolicy::class
        );
        Gate::policy(
            Product::class,
            ProductPolicy::class
        );
        Gate::policy(
            MarketplaceProduct::class,
            MarketplaceProductPolicy::class
        );
        Gate::policy(
            Vendor::class,
            VendorPolicy::class
        );
        Gate::policy(
            VendorDocument::class,
            VendorDocumentPolicy::class
        );
        Gate::policy(
            VendorListing::class,
            VendorListingPolicy::class
        );
        Gate::policy(
            BulkInquiry::class,
            BulkInquiryPolicy::class
        );
        Gate::policy(
            Settlement::class,
            SettlementPolicy::class
        );

        // Register custom notifications database channel
        $this->app->make(ChannelManager::class)->extend('database', function ($app) {
            return new CustomDatabaseChannel;
        });

        // Implicitly grant "Super Admin" role all permissions
        // This is the Spatie standard practice for Laravel architectures
        Gate::before(function ($user, $ability) {
            return $user->hasRole(UserRole::SuperAdmin->value) ? true : null;
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
