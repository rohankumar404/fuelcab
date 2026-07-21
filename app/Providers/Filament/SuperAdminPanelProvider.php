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

class SuperAdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('super-admin')
            ->path('admin')
            ->login()
            ->brandName('FuelCab — Super Admin')
            ->brandLogo(null)
            ->favicon(null)
            ->colors([
                'primary' => Color::Amber,
                'danger'  => Color::Rose,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'info'    => Color::Sky,
            ])
            ->font('Inter', 'https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900')
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->sidebarWidth('17rem')

            // ── Global Search ──────────────────────────────────────────────
            ->globalSearch()
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            ->globalSearchDebounce('500ms')

            // ── Database notifications ─────────────────────────────────────
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')

            // ── Navigation Groups (exact order from spec) ──────────────────
            ->navigationGroups([
                NavigationGroup::make('DIRECT COMMERCE')
                    ->icon('heroicon-o-shopping-bag')
                    ->collapsible(true),
                NavigationGroup::make('MARKETPLACE')
                    ->icon('heroicon-o-building-storefront')
                    ->collapsible(true),
                NavigationGroup::make('VENDORS')
                    ->icon('heroicon-o-user-group')
                    ->collapsible(true),
                NavigationGroup::make('CUSTOMERS')
                    ->icon('heroicon-o-users')
                    ->collapsible(true),
                NavigationGroup::make('FINANCE')
                    ->icon('heroicon-o-banknotes')
                    ->collapsible(true),
                NavigationGroup::make('CONTENT')
                    ->icon('heroicon-o-document-text')
                    ->collapsible(true),
                NavigationGroup::make('SYSTEM')
                    ->icon('heroicon-o-cog-6-tooth')
                    ->collapsible(true),
            ])

            // ── Auto-discovery ─────────────────────────────────────────────
            ->discoverResources(
                in: app_path('Filament/SuperAdmin/Resources'),
                for: 'App\\Filament\\SuperAdmin\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/SuperAdmin/Pages'),
                for: 'App\\Filament\\SuperAdmin\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/SuperAdmin/Widgets'),
                for: 'App\\Filament\\SuperAdmin\\Widgets'
            )
            ->widgets([
                Widgets\AccountWidget::class,
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
     * Authorize panel access — only super_admin role allowed.
     */
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(UserRole::SuperAdmin->value);
    }
}
