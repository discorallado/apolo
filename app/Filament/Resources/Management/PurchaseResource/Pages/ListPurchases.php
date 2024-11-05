<?php

namespace App\Filament\Resources\Management\PurchaseResource\Pages;

use App\Filament\Imports\Management\PurchaseImporter;
use App\Filament\Resources\Management\PurchaseResource;
use App\Filament\Resources\Management\PurchaseResource\Widgets\PurchaseStatsWidget;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPurchases extends ListRecords
{
	protected static string $resource = PurchaseResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Actions\CreateAction::make(),
			Actions\ImportAction::make()
			->label('Importar compras SII')
			->color('warning')
			->modalDescription(null)
			->importer(PurchaseImporter::class),
		];
	}

	public function getHeaderWidgetsColumns(): int | array
	{
		return 4;
	}

	protected function getHeaderWidgets(): array
	{
		return [
			PurchaseStatsWidget::class,
		];
	}
}
