<?php

namespace App\Providers\Filament;

use App\Models\CompanyProfile;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    private const FRONT_FAVICON_PATH = 'img/favicon/favicon.svg';

    public function panel(Panel $panel): Panel
    {
        $companyProfile = $this->getCompanyProfile();
        $brandLogo = $this->getBrandLogoUrl($companyProfile?->logo);
        $darkModeBrandLogo = $this->getBrandLogoUrl($companyProfile?->logo_dark);

        $panel = $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->databaseNotifications()
            ->databaseNotificationsPolling('30s')
            ->brandName($this->getBrandName($companyProfile))
            ->favicon($this->getFrontFaviconUrl())
            ->colors([
                'primary' => Color::Orange,
            ])
            ->viteTheme('resources/css/filament/theme.css')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverResources(in: app_path('Filament/Restaurant/Resources'), for: 'App\\Filament\\Restaurant\\Resources')
            ->navigationGroups([
                'Menu a lístky',
                'Obsah',
                'GDPR',
                'Nastavení',
            ])
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->plugins([
                FilamentShieldPlugin::make()
                    ->navigationGroup('Nastavení'),
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);

        if ($brandLogo !== null) {
            $panel = $panel
                ->brandLogo($brandLogo)
                ->brandLogoHeight('2.5rem');
        }

        if ($darkModeBrandLogo !== null) {
            $panel = $panel->darkModeBrandLogo($darkModeBrandLogo);
        }

        return $panel;
    }

    private function getCompanyProfile(): ?CompanyProfile
    {
        if (! Schema::hasTable('company_profiles')) {
            return null;
        }

        return CompanyProfile::current();
    }

    private function getBrandName(?CompanyProfile $companyProfile): string
    {
        if (is_string($companyProfile?->company_name) && trim($companyProfile->company_name) !== '') {
            return $companyProfile->company_name;
        }

        return (string) config('app.name', 'U Sejmona pod hájkem');
    }

    private function getFrontFaviconUrl(): string
    {
        return asset(self::FRONT_FAVICON_PATH);
    }

    private function getBrandLogoUrl(?string $logoPath): ?string
    {
        if (! is_string($logoPath) || trim($logoPath) === '') {
            return null;
        }

        $logoPath = trim($logoPath);

        if (str_starts_with($logoPath, 'img/')) {
            return file_exists(public_path($logoPath)) ? asset($logoPath) : null;
        }

        if (! Storage::disk('public')->exists($logoPath)) {
            return null;
        }

        return asset(Storage::disk('public')->url($logoPath));
    }
}
