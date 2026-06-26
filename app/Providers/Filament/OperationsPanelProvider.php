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

class OperationsPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('operations')
            ->path('operations')
            ->login()
            ->brandName('FuelCab — Operations')
            ->colors([
                'primary' => Color::Blue,
                'danger'  => Color::Rose,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'info'    => Color::Sky,
            ])
            ->font('Inter', 'https://fonts.bunny.net/css?family=inter:100,200,300,400,500,600,700,800,900')
            ->darkMode(true)
            ->sidebarCollapsibleOnDesktop()
            ->navigationGroups([
                NavigationGroup::make('Dashboard')
                    ->icon('heroicon-o-home'),
                NavigationGroup::make('Order Management')
                    ->icon('heroicon-o-clipboard-document-list'),
                NavigationGroup::make('Vendor Management')
                    ->icon('heroicon-o-building-storefront')
                    ->collapsed(),
                NavigationGroup::make('Driver Management')
                    ->icon('heroicon-o-truck')
                    ->collapsed(),
                NavigationGroup::make('Payments')
                    ->icon('heroicon-o-banknotes')
                    ->collapsed(),
                NavigationGroup::make('Reports')
                    ->icon('heroicon-o-chart-bar')
                    ->collapsed(),
            ])
            ->discoverResources(
                in: app_path('Filament/Operations/Resources'),
                for: 'App\\Filament\\Operations\\Resources'
            )
            ->discoverPages(
                in: app_path('Filament/Operations/Pages'),
                for: 'App\\Filament\\Operations\\Pages'
            )
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(
                in: app_path('Filament/Operations/Widgets'),
                for: 'App\\Filament\\Operations\\Widgets'
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
     * Authorize panel access — only operations_team role allowed.
     */
    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasRole(UserRole::OperationsTeam->value);
    }
}
