<?php

namespace App\Filament\Resources\Management\SaleResource\Pages;

use App\Filament\Resources\Management\SaleResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSale extends CreateRecord
{
	protected static string $resource = SaleResource::class;


	protected function mutateFormDataBeforeCreate(array $data): array
	{
		$data['user_id'] = auth()->id();
		$data['periodo'] = date('m', strtotime($data['fecha_dcto']));
		$data['ano'] = date('y', strtotime($data['fecha_dcto']));

		return $data;
	}
}
