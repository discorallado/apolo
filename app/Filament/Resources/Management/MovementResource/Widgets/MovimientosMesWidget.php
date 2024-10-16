<?php

namespace App\Filament\Resources\Management\MovementResource\Widgets;

use App\Livewire\CutsomStat;
use App\Models\Management\Movement;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;
use Illuminate\Support\Number;

class MovimientosMesWidget extends BaseWidget
{

    protected int | string | array $columnSpan = 4;

    protected function getStats(): array
    {
        $dateFrom = now()->startOfMonth();
        $dateTo = now()->endOfMonth();

        $trendMonth = Trend::model(Movement::class)
            ->dateColumn('fecha')
            ->between(
                start: $dateFrom,
                end: $dateTo,
            )
            ->perDay()
            ->count();

        $trendLastMonth = Trend::model(Movement::class)
            ->dateColumn('fecha')
            ->between(
                start: now()->subMonthNoOverflow()->startOfMonth(),
                end: now()->subMonthNoOverflow()->endOfMonth(),
            )
            ->perDay()
            ->count();

        $movementData = Trend::model(Movement::class)
            ->dateColumn('fecha')
            ->between(
                start: $dateFrom,
                end: $dateTo,
            )
            ->perDay()
            ->count();

        $cargosData = Trend::model(Movement::class)
            ->dateColumn('fecha')
            ->between(
                start: $dateFrom,
                end: $dateTo,
            )
            ->perDay()
            ->sum('cargo');

        $ingresosData = Trend::model(Movement::class)
            ->dateColumn('fecha')
            ->between(
                start: $dateFrom,
                end: $dateTo,
            )
            ->perDay()
            ->sum('ingreso');


        $arrayMonth = $trendMonth->map(fn (TrendValue $value) => $value->aggregate)->toArray();

        $arrayLastMonth = $trendLastMonth->map(fn (TrendValue $value) => $value->aggregate)->toArray();

        $diff =  array_sum($arrayLastMonth) > 0 ? array_sum($arrayMonth) / array_sum($arrayLastMonth) : array_sum($arrayLastMonth);




        $cargosMes = (int)Movement::whereBetween('fecha', [$dateFrom, $dateTo])->sum('cargo');
        $ingresosMes = (int)Movement::whereBetween('fecha', [$dateFrom, $dateTo])->sum('ingreso');

        $deuda = $cargosMes - $ingresosMes;

        return [

            CutsomStat::make('Movimientos este mes', array_sum($arrayMonth))
                ->color('success')
                ->description('Equivale al ' . round($diff, 3) * 100 . '% del mes pasado')
                ->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
                ->chart($arrayMonth),

            Stat::make('Cargos este mes', Number::currency($cargosMes, 'CLP', 'cl'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('info')
                ->chart(
                    $cargosData
                        ->map(fn (TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),

            Stat::make('Pagos este mes', Number::currency($ingresosMes, 'CLP', 'cl'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart(
                    $ingresosData
                        ->map(fn (TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),

            Stat::make('Deuda este mes', Number::currency($deuda, 'CLP', 'cl'))
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success')
                ->chart(
                    $movementData
                        ->map(fn (TrendValue $value) => $value->aggregate)
                        ->toArray()
                ),
        ];
    }
}
