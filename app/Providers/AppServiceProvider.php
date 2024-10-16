<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use App\Settings\GeneralSettings;
use Carbon\Carbon;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;

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
        //
        Carbon::setLocale('es_CL');
        setlocale(LC_ALL, 'es_CL', 'es', 'ES', 'es_CL.utf8');
        View::composer('*', function ($view) {
            $settings = app(GeneralSettings::class);
            $view->with('generalSettings', collect($settings));
        });

        FilamentView::registerRenderHook(
            'panels::scripts.after',
            fn(): string => Blade::render("
        <script>
            if(localStorage.getItem('theme') === null) {
                localStorage.setItem('theme', 'dark')
            }
        </script>"),
        );
        FilamentAsset::register([
            Js::make('main', __DIR__ . '/../../resources/js/main.js'),
        ]);
    }
}
