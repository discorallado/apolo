<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
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
use Filament\Enums\ThemeMode;
use App\Filament\Pages\Auth\Login;
use App\Filament\Pages\Backups;
use App\Filament\Pages\Dashboard;
use App\Filament\Pages\HealthCheckResults;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Forms\Components\FileUpload;
use Filament\Navigation\NavigationGroup;
use Jeffgreco13\FilamentBreezy\BreezyCore;
use ShuvroRoy\FilamentSpatieLaravelHealth\FilamentSpatieLaravelHealthPlugin;
use Awcodes\FilamentQuickCreate\QuickCreatePlugin;
use Saade\FilamentFullCalendar\FilamentFullCalendarPlugin;
use ShuvroRoy\FilamentSpatieLaravelBackup\FilamentSpatieLaravelBackupPlugin;
use pxlrbt\FilamentSpotlight\SpotlightPlugin;
use TomatoPHP\FilamentNotes\FilamentNotesPlugin;
use TomatoPHP\FilamentNotes\Filament\Widgets\NotesWidget;

class ApoloPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('apolo')
            ->login(Login::class)
            ->path('')
            ->defaultThemeMode(ThemeMode::Dark)
            ->discoverClusters(in: app_path('Filament/Clusters'), for: 'App\\Filament\\Clusters')
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Dashboard::class,
            ])
            ->spa()
            // ->sidebarCollapsibleOnDesktop()
            // ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->maxContentWidth('full')
            ->navigationGroups([
                NavigationGroup::make()
                    ->label('Gestión')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('RRHH')
                    ->collapsed(),
                NavigationGroup::make()
                    ->label('Configuración')
                    ->collapsed(),
            ])
            ->databaseNotifications()
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
                // NotesWidget::class,
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
            // ->topNavigation()
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugins([
                BreezyCore::make()
                    ->myProfile(
                        hasAvatars: true, // Enables the avatar upload form component (default = false)
                    )
                    //   ->avatarUploadComponent(fn ($fileUpload) => $fileUpload->disableLabel()->disk('public'))
                    ->avatarUploadComponent(fn() => FileUpload::make('avatar_url')->avatar()),
                FilamentShieldPlugin::make(),
                FilamentSpatieLaravelHealthPlugin::make()
                    ->usingPage(HealthCheckResults::class),
                QuickCreatePlugin::make()
                    ->includes([
                        \App\Filament\Resources\Management\MovementResource::class,
                        \App\Filament\Resources\Management\ProyectResource::class,
                        \App\Filament\Resources\Management\PurchaseResource::class,
                        \App\Filament\Resources\Management\SaleResource::class,
                    ]),
                FilamentSpatieLaravelBackupPlugin::make()
                    ->usingPolingInterval(Backups::class)
                    // ->usingPolingInterval('60s')
                    ->usingPage(Backups::class),
                FilamentFullCalendarPlugin::make()
                    ->editable()
                    ->selectable(),
                SpotlightPlugin::make(),
                FilamentNotesPlugin::make()
                    ->useChecklist(),
                // ReportsPlugin::make(),

            ])
            // ->viteTheme('resources/css/filament/apolo/theme.css')
            ->viteTheme('resources/css/filament/admin/theme.css')
            ->font('Quicksand')
            ->colors([
                'primary' => Color::Blue,
                'indigo' => Color::Indigo,
            ])
            ->favicon(asset('favicon.ico'))
            ->sidebarWidth('14rem');
    }
}