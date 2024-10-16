<?php

namespace App\Filament\Resources\Management\SaleResource\Pages;

use App\Filament\Resources\Management\SaleResource;
use App\Filament\Resources\Management\SaleResource\Widgets\SaleStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSales extends ListRecords
{
	protected static string $resource = SaleResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Actions\CreateAction::make(),
		];
	}

	public function getHeaderWidgetsColumns(): int | array
	{
		return 1;
	}

	protected function getHeaderWidgets(): array
	{
		return [
			SaleStatsWidget::class,
		];
	}
}
