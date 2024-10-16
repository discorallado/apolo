<?php

namespace App\Filament\Resources\Management\ProyectResource\Widgets;

use App\Livewire\CutsomStat;
use App\Models\Management\Proyect;
use Carbon\Carbon;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class ProyectStatsWidget extends BaseWidget
{
    protected int | string | array $columnSpan = 3;

    protected function getStats(): array
    {
        $trendMonth = Trend::model(Proyect::class)
            ->dateColumn('created_at')
            ->between(
                start: now()->startOfMonth(),
                end: now()->endOfMonth(),
            )
            ->perDay()
            ->count();

        $trendLastMonth = Trend::model(Proyect::class)
            ->dateColumn('created_at')
            ->between(
                start: now()->subMonthNoOverflow()->startOfMonth(),
                end: now()->subMonthNoOverflow()->endOfMonth(),
            )
            ->perDay()
            ->count();

        $arrayMonth = $trendMonth->map(fn(TrendValue $value) => $value->aggregate)->toArray();

        $arrayLastMonth = $trendLastMonth->map(fn(TrendValue $value) => $value->aggregate)->toArray();

        $diff =  array_sum($arrayMonth) / max(array_sum($arrayLastMonth), 0.01);

        $promCargos = Proyect::avg('monto_proyectado');
        $activos = Proyect::where('estado', '=', '0')->count();

        return [
            CutsomStat::make('Proyectos  ' . Carbon::now()->format('F'), array_sum($arrayMonth))
                ->color('success')
                ->icon('heroicon-s-document')
                ->description('Equivale al ' . round($diff, 3) * 100 . '% del mes pasado')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($arrayMonth),
            Stat::make('Monto proyectado promedio', '$' . number_format($promCargos, 0, 0, '.'))
                ->icon('heroicon-s-banknotes'),
            Stat::make('Proyectos activos', $activos)
                ->icon('heroicon-s-banknotes')
                ->color('primary')
                ->description('Proyectos activos')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($arrayMonth),
        ];
    }
}
