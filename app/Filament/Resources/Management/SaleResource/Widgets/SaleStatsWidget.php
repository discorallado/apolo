<?php

namespace App\Filament\Resources\Management\SaleResource\Widgets;

use App\Livewire\CutsomStat;
use App\Models\Management\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class SaleStatsWidget extends BaseWidget
{
	protected int | string | array $columnSpan =  'full';

	protected function getStats(): array
	{
		$trendMonth = Trend::model(Sale::class)
			->dateColumn('fecha_dcto')
			->between(
				start: now()->startOfMonth(),
				end: now()->endOfMonth(),
			)
			->perDay()
			->count();

		$trendLastMonth = Trend::model(Sale::class)
			->dateColumn('fecha_dcto')
			->between(
				start: now()->subMonthNoOverflow()->startOfMonth(),
				end: now()->subMonthNoOverflow()->endOfMonth(),
			)
			->perDay()
			->count();

		$arrayMonth = $trendMonth->map(fn (TrendValue $value) => $value->aggregate)->toArray();

		$arrayLastMonth = $trendLastMonth->map(fn (TrendValue $value) => $value->aggregate)->toArray();

		// $diff =  array_sum($arrayLastMonth) > 0 ? (array_sum($arrayMonth) / array_sum($arrayLastMonth)) : array_sum($arrayMonth);

		$count = Sale::all()->count();

		$promCargos = Sale::avg('total');
		$maxCargos = Sale::max('total');
		$minCargos = Sale::min('total');

		$promCargos = Sale::avg('total');

		// dd($promCargos);

		// $cargosMes = (int)Proyect::where('created_at', '>=', now()->subMonth())->sum('cargo');
		// $ingresosMes = (int)Proyect::where('created_at', '>=', now()->subMonth())->sum('ingreso');

		// $deuda = $cargosMes - $ingresosMes;

		return [
			CutsomStat::make('Ventas este mes', array_sum($arrayMonth))
				->color('success')
				->icon('heroicon-s-document')
				->description('De un total acumulado de ' . $count)
				->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
				->chart($arrayMonth),

			Stat::make('Monto promedio', '$' . number_format($promCargos, 0, 0, '.'))
				->icon('heroicon-s-banknotes')
				->description($minCargos . ' - ' . $maxCargos),

			// Stat::make('Monto promedio', '$' . number_format($promCargos, 0, 0, '.'))
			//     ->icon('heroicon-s-banknotes')
			//     ->description($minCargos . ' - ' . $maxCargos),

			// Stat::make('Monto promedio', '$' . number_format($promCargos, 0, 0, '.'))
			//     ->icon('heroicon-s-banknotes')
			//     ->description($minCargos . ' - ' . $maxCargos),

			// Stat::make('Monto promedio', '$' . number_format($promCargos, 0, 0, '.'))
			//     ->icon('heroicon-s-banknotes')
			//     ->description($minCargos . ' - ' . $maxCargos),

			// Stat::make('Monto promedio', '$' . number_format($promCargos, 0, 0, '.'))
			//     ->icon('heroicon-s-banknotes')
			//     ->description($minCargos . ' - ' . $maxCargos),


		];
	}
}
