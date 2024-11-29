<?php

namespace App\Providers\Filament;

use App\Filament\Pages\Auth\RegisterPage;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use Kenepa\Banner\BannerPlugin;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->registration(RegisterPage::class)
            ->passwordReset()
            ->emailVerification()
            ->font('Poppins')
            ->favicon(asset('images/Kementask-2.png'))
            ->colors([
                'primary' => Color::Green,
            ])
            ->resources([
                config('filament.resources.path')
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
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
            ->plugins([
                \BezhanSalleh\FilamentShield\FilamentShieldPlugin::make(),
                BannerPlugin::make()
                    ->title('Pemberitahuan')
                    ->subheading('Atur Pemberitahuan Anda')
                    ->navigationLabel('Pemberitahuan'),
                FilamentEditProfilePlugin::make()
                    ->setTitle('Profil Saya')
                    ->setNavigationLabel('Profile Saya')
                    ->setNavigationGroup('PERMISSIONS')
                    ->setIcon('heroicon-o-user'),
                FilamentFullCalendarPlugin::make()
                    ->selectable()
                    ->editable(),
            ]);
    }
}