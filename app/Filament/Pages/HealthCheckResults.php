<?php

namespace App\Filament\Pages;

use Illuminate\Contracts\Support\Htmlable;
use ShuvroRoy\FilamentSpatieLaravelHealth\Pages\HealthCheckResults as BaseHealthCheckResults;

class HealthCheckResults extends BaseHealthCheckResults
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';

    protected static ?int $navigationSort = 2;

    public function getHeading(): string | Htmlable
    {
        return 'Indicadores de la app';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Configuración';
    }
    public static function getNavigationLabel(): string
    {
        return 'Entorno';
    }
}
