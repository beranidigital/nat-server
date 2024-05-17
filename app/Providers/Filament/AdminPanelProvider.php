<?php

namespace App\Providers\Filament;

use App\Filament\Pages\PoolDetail;
use App\Models\Pool\StateLog;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationGroup;
use Filament\Navigation\NavigationItem;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        $devices = StateLog::getDevices();
        // sort alphabetically
        asort($devices);
        foreach ($devices as $device => $friendly_name) {
            $panel->navigationItems([
                NavigationItem::make($friendly_name)
                    ->icon(PoolDetail::getNavigationIcon())
                    ->group(PoolDetail::getNavigationGroup())
                    ->url(fn() => PoolDetail::getUrl(['device' => $device]))
                    ->isActiveWhen(fn() => PoolDetail::getUrl(['device' => $device]) === request()->fullUrl())
            ]);
        }
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => '#374151',
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->navigationGroups([
                'details' => NavigationGroup::make()
                    ->label(fn() => __('Details'))
                    ->collapsed()
                    ->icon("heroicon-o-list-bullet"),
                'settings' => NavigationGroup::make()
                    ->label(fn() => __('Settings'))
                    ->collapsed()
                    ->icon("heroicon-o-cog"),
            ])
            ->plugins([
                \Hasnayeen\Themes\ThemesPlugin::make()->canViewThemesPage(fn() => false),
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make()

            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->navigationItems([
            ])
            ->databaseNotifications()
            ->widgets([
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
                \Hasnayeen\Themes\Http\Middleware\SetTheme::class

            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
