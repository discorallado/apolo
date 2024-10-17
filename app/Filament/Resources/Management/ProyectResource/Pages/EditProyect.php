<?php

namespace App\Filament\Resources\Management\ProyectResource\Pages;

use App\Filament\Resources\Management\ProyectResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProyect extends EditRecord
{
    protected static string $resource = ProyectResource::class;

	protected function getHeaderActions(): array
	{
		return [
			Actions\DeleteAction::make(),
			Actions\ForceDeleteAction::make(),
			Actions\RestoreAction::make(),
		];
	}

	protected function mutateFormDataBeforeSave(array $data): array
	{
		// $data['periodo'] = date('m', strtotime($data['fecha_dcto']));
		// $data['ano'] = date('y', strtotime($data['fecha_dcto']));

		$data['id_proyecto'] = $data['proyect_data']['id'];
		// $data['id_cliente'] = $data['proyect_data']['id_cliente'];
		unset($data['proyect_data']);
		// dd($data);

		return $data;
	}
}
