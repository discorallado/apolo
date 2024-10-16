<?php

namespace App\Filament\Resources\Management\PurchaseResource\Pages;

use App\Filament\Resources\Management\PurchaseResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchase extends CreateRecord
{
	protected static string $resource = PurchaseResource::class;


	protected function mutateFormDataBeforeCreate(array $data): array
	{
		$data['user_id'] = auth()->id();
		$data['periodo'] = date('m', strtotime($data['fecha_dcto']));
		$data['ano'] = date('y', strtotime($data['fecha_dcto']));
		if ($data['id_cliente'] === null) {
			$data['id_proyecto'] = null;
		}
		return $data;
	}
}
