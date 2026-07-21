<?php

declare(strict_types=1);

namespace App\Providers\Filament;

use App\Enums\UserRole;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class VendorPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('vendor')
            ->path('vendor')
            ->login()
            ->brandName('FuelCab — Vendor Portal')
            ->colors([
                'primary' => Color::Emerald,
                'danger'  => Color::Rose,
                'success' => Color::Teal,
                'warning' => Color::Orange,
                'info'    => Color::Sky,
            ])
            ->font('Inter', 'https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900')
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Dashboard')
                    ->icon('heroicon-o-home'),
                NavigationGroup::make('Listings')
                    ->icon('heroicon-o-tag'),
                NavigationGroup::make('Inventory')
                    ->icon('heroicon-o-archive-box'),
                NavigationGroup::make('Orders')
                    ->icon('heroicon-o-clipboard-document-list'),
                NavigationGroup::make('Quote Requests')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis'),
                NavigationGroup::make('Documents')
                    ->icon('heroicon-o-document-text'),
                NavigationGroup::make('Settlements')
                    ->icon('heroicon-o-banknotes'),
                NavigationGroup::make('Analytics')
                    ->icon('heroicon-o-chart-bar'),
                NavigationGroup::make('Company Profile')
                    ->icon('heroicon-o-building-office-2'),
                NavigationGroup::make('Settings')
                    ->icon('heroicon-o-cog-6-tooth'),
            ])
            ->discoverResources(
                in: app_path('Filament/Vendor/Resources'),
                for: 'App\\Filament\\Vendor\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Vendor/Pages'),
                for: 'App\\Filament\\Vendor\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Vendor/Widgets'),
                for: 'App\\Filament\\Vendor\\Widgets'
            )
            ->widgets([
                \App\Filament\Vendor\Widgets\VendorStatsWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->authGuard('web');
    }

    /**
     * Authorize panel access — vendor_admin and vendor_staff roles allowed.
     */
    public static function canAccess(): bool
    {
        if (! auth()->check()) {
            return false;
        }

        return auth()->user()->hasAnyRole([
            UserRole::VendorAdmin->value,
            UserRole::VendorStaff->value,
        ]);
    }
}
