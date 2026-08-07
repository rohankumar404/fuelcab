<?php

declare(strict_types=1);

namespace App\Modules\Notification\Providers;

use App\Modules\Notification\Channels\FcmChannel;
use App\Modules\Notification\Services\FcmService;
use Illuminate\Notifications\ChannelManager;
use Illuminate\Support\ServiceProvider;

class NotificationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(FcmService::class, function ($app) {
            return new FcmService();
        });
    }

    public function boot(): void
    {
        // Register custom notifications FCM channel
        $this->app->make(ChannelManager::class)->extend('fcm', function ($app) {
            return new FcmChannel();
        });
    }
}
