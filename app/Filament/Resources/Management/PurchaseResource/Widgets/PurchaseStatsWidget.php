<?php

namespace App\Filament\Resources\Management\PurchaseResource\Widgets;

use App\Livewire\CutsomStat;
use App\Models\Management\Purchase;
use App\Models\Management\Sale;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Flowframe\Trend\Trend;
use Flowframe\Trend\TrendValue;

class PurchaseStatsWidget extends BaseWidget
{
	protected int | string | array $columnSpan = 'full';

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

		$count = Purchase::all()->count();

		$promCargos = Purchase::avg('total');
		$maxCargos = Purchase::max('total');
		$minCargos = Purchase::where('total', '>', '0')->min('total');

		// dd($minCargos);

		// $cargosMes = (int)Proyect::where('created_at', '>=', now()->subMonth())->sum('cargo');
		// $ingresosMes = (int)Proyect::where('created_at', '>=', now()->subMonth())->sum('ingreso');

		// $deuda = $cargosMes - $ingresosMes;

		return [
			CutsomStat::make('Compras este mes', array_sum($arrayMonth))
				->color('success')
				->icon('heroicon-s-document')
				->description('De un total acumulado de ' . $count)
				->descriptionIcon('heroicon-m-arrow-trending-up', 'before')
				->chart($arrayMonth),

			Stat::make('Monto promedio', '$' . number_format($promCargos, 0, 0, '.'))
				->icon('heroicon-s-banknotes')
				->description('Min: $' . number_format($minCargos, 0, 0, '.') . ' / Max: $' . number_format($maxCargos, 0, 0, '.')),

		];
	}
}
